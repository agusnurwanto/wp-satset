<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$years = $wpdb->get_col("
    SELECT tahun_anggaran
    FROM data_malaria
    WHERE active = 1 AND tahun_anggaran IS NOT NULL AND tahun_anggaran > 0
    GROUP BY tahun_anggaran
    ORDER BY tahun_anggaran DESC
");
if ($wpdb->last_error !== '' || !is_array($years)) {
    echo '<div class="alert alert-danger">Data progres Malaria tidak dapat dimuat. Silakan hubungi administrator.</div>';
    return;
}

$years = array_values(array_filter(array_map('intval', $years)));
$requested_year = isset($_GET['tahun_anggaran'])
    ? sanitize_text_field(wp_unslash($_GET['tahun_anggaran']))
    : '';
$default_year = (int) get_option('_crb_tahun_satset');
$tahun_anggaran = preg_match('/^\d{4}$/', $requested_year)
    && in_array((int) $requested_year, $years, true)
    ? (int) $requested_year
    : (in_array($default_year, $years, true) ? $default_year : (isset($years[0]) ? $years[0] : 0));

if (!$tahun_anggaran) {
    echo '<div class="alert alert-info">Belum ada data Malaria untuk ditampilkan.</div>';
    return;
}

$rows = $wpdb->get_results($wpdb->prepare("
    SELECT
        a.provinsi,
        a.kabkot,
        a.kecamatan,
        a.desa,
        a.tindak_lanjut,
        a.hasil_akhir,
        a.status_pengobatan,
        m.kode_desa_satset
    FROM data_malaria a
    LEFT JOIN (
        SELECT kode_desa_malaria, MAX(kode_desa_satset) AS kode_desa_satset
        FROM mapping_malaria
        GROUP BY kode_desa_malaria
    ) m
        ON CONCAT_WS('-', a.provinsi, a.kabkot, a.kecamatan, a.desa) = m.kode_desa_malaria
    WHERE a.active = 1 AND a.tahun_anggaran = %d
", $tahun_anggaran), ARRAY_A);
if ($wpdb->last_error !== '' || !is_array($rows)) {
    echo '<div class="alert alert-danger">Data progres Malaria tidak dapat dimuat. Silakan hubungi administrator.</div>';
    return;
}

$new_counts = function () {
    return array(
        'total' => 0,
        'dalam_pengobatan' => 0,
        'selesai' => 0,
        'meninggal' => 0,
        'status_lain' => 0,
        'status_tidak_tercatat' => 0,
        'status_detail' => array(),
        'tindak_lanjut' => array(),
        'hasil_akhir' => array(),
    );
};
$add_label_count = function (&$counts, $field, $value) {
    $label = trim((string) $value);
    if ($label === '') {
        $label = 'Tidak tercatat';
    }
    if (!isset($counts[$field][$label])) {
        $counts[$field][$label] = 0;
    }
    $counts[$field][$label]++;
};
$mapped_counts = array();
$unmapped_counts = array();
$ai_location_counts = array();
$total_counts = $new_counts();

foreach ($rows as $row) {
    $code = trim((string) $row['kode_desa_satset']);
    $location_fields = array(
        trim((string) $row['provinsi']),
        trim((string) $row['kabkot']),
        trim((string) $row['kecamatan']),
        trim((string) $row['desa']),
    );
    $location = array_values(array_filter($location_fields, 'strlen'));
    $location_name = implode(', ', array_reverse($location));
    $ai_location_key = serialize($location_fields);
    if (!isset($ai_location_counts[$ai_location_key])) {
        $ai_location_counts[$ai_location_key] = array(
            'name' => $location_name !== '' ? $location_name : 'Lokasi tidak tercatat',
            'total' => 0,
            'status_pengobatan' => array(
                'Dalam pengobatan' => 0,
                'Selesai' => 0,
                'Meninggal' => 0,
                'Status lain' => 0,
                'Tidak tercatat' => 0,
            ),
        );
    }
    $ai_location_counts[$ai_location_key]['total']++;

    if ($code !== '') {
        if (!isset($mapped_counts[$code])) {
            $mapped_counts[$code] = $new_counts();
        }
        $counts_key = $code;
        $counts = &$mapped_counts[$counts_key];
    } else {
        $location_key = implode('-', $location);
        if (!isset($unmapped_counts[$location_key])) {
            $unmapped_counts[$location_key] = array(
                'name' => $location_name,
                'location' => $location_fields,
                'counts' => $new_counts(),
            );
        }
        $counts = &$unmapped_counts[$location_key]['counts'];
    }

    $counts['total']++;
    $total_counts['total']++;
    $status = trim((string) $row['status_pengobatan']);
    $normalized_status = strtolower($status);
    if ($status === '') {
        $counts['status_tidak_tercatat']++;
        $total_counts['status_tidak_tercatat']++;
        $ai_status_label = 'Tidak tercatat';
    } elseif (strpos($normalized_status, 'meninggal') !== false) {
        $counts['meninggal']++;
        $total_counts['meninggal']++;
        $ai_status_label = 'Meninggal';
    } elseif (
        strpos($normalized_status, 'belum selesai') === false
        && (
            $normalized_status === 'selesai'
            || strpos($normalized_status, 'selesai pengobatan') !== false
            || strpos($normalized_status, 'pengobatan selesai') !== false
        )
    ) {
        $counts['selesai']++;
        $total_counts['selesai']++;
        $ai_status_label = 'Selesai';
    } elseif (
        strpos($normalized_status, 'dalam pengobatan') !== false
        || strpos($normalized_status, 'sedang diobati') !== false
    ) {
        $counts['dalam_pengobatan']++;
        $total_counts['dalam_pengobatan']++;
        $ai_status_label = 'Dalam pengobatan';
    } else {
        $counts['status_lain']++;
        $total_counts['status_lain']++;
        $ai_status_label = 'Status lain';
    }
    $ai_location_counts[$ai_location_key]['status_pengobatan'][$ai_status_label]++;

    $add_label_count($counts, 'status_detail', $row['status_pengobatan']);
    $add_label_count($total_counts, 'status_detail', $row['status_pengobatan']);
    $add_label_count($counts, 'tindak_lanjut', $row['tindak_lanjut']);
    $add_label_count($total_counts, 'tindak_lanjut', $row['tindak_lanjut']);
    $add_label_count($counts, 'hasil_akhir', $row['hasil_akhir']);
    $add_label_count($total_counts, 'hasil_akhir', $row['hasil_akhir']);
    unset($counts);
}

$progress_count = $total_counts['dalam_pengobatan'] + $total_counts['selesai'];
$progress_percent = $total_counts['total'] > 0
    ? round(($progress_count / $total_counts['total']) * 100, 1)
    : 0;
$format_count = function ($count) {
    return esc_html(number_format_i18n((int) $count));
};
$format_status_details = function ($status_details) use ($format_count) {
    if (!$status_details) {
        return '<div>Tidak ada status tercatat.</div>';
    }
    ksort($status_details, SORT_NATURAL | SORT_FLAG_CASE);
    $html = '';
    foreach ($status_details as $label => $count) {
        $html .= '<div>' . esc_html($label) . ': <strong>' . $format_count($count) . '</strong></div>';
    }
    return $html;
};
$format_breakdown = function ($breakdown) {
    if (!$breakdown) {
        return 'Belum ada data.';
    }
    ksort($breakdown, SORT_NATURAL | SORT_FLAG_CASE);
    $items = array();
    foreach ($breakdown as $label => $count) {
        $items[] = '<li>' . esc_html($label) . ': <strong>' . esc_html(number_format_i18n($count)) . '</strong></li>';
    }
    return '<ul>' . implode('', $items) . '</ul>';
};

$status_counts = $total_counts['status_detail'];
$ai_areas = array_values($ai_location_counts);
usort($ai_areas, function ($left, $right) {
    return $right['total'] - $left['total'];
});
if ($total_counts['total'] > 100) {
    $ai_areas = array_slice(array_values(array_filter($ai_areas, function ($area) {
        return $area['total'] >= 5;
    })), 0, 50);
}
$center = $this->get_center();
$maps_all = $this->get_polygon();
$village_table_body = '';
foreach ($maps_all as $index => $village) {
    $data = isset($village['data']) ? $village['data'] : array();
    $code = isset($data['id2012']) ? (string) $data['id2012'] : '';
    $counts = isset($mapped_counts[$code]) ? $mapped_counts[$code] : $new_counts();
    $village_progress = $counts['dalam_pengobatan'] + $counts['selesai'];
    $village_percent = $counts['total'] > 0
        ? round(($village_progress / $counts['total']) * 100, 1)
        : 0;
    if ($counts['total'] === 0) {
        $color = '#b0bec5';
    } elseif ($village_percent >= 80) {
        $color = '#138808';
    } elseif ($village_percent >= 50) {
        $color = '#8bc34a';
    } elseif ($village_percent > 0) {
        $color = '#fff176';
    } else {
        $color = '#d7191c';
    }

    $village_name = isset($data['desa']) ? $data['desa'] : '';
    $maps_all[$index]['index'] = $index;
    $maps_all[$index]['color'] = $color;
    $maps_all[$index]['html'] = '<div class="satset-malaria-progress-popup">'
        . '<strong>' . esc_html($village_name) . '</strong>'
        . '<div>Total kasus Malaria: <strong>' . esc_html(number_format_i18n($counts['total'])) . '</strong></div>'
        . '<div>Dalam pengobatan: <strong>' . esc_html(number_format_i18n($counts['dalam_pengobatan'])) . '</strong></div>'
        . '<div>Selesai pengobatan: <strong>' . esc_html(number_format_i18n($counts['selesai'])) . '</strong></div>'
        . '<div>Status lain/tidak tercatat: <strong>' . esc_html(number_format_i18n($counts['status_lain'] + $counts['status_tidak_tercatat'])) . '</strong></div>'
        . '<div>Progres pengobatan: <strong>' . esc_html($village_percent) . '%</strong></div>'
        . '</div>';
    $maps_all[$index]['tooltip'] = esc_html($village_name)
        . ' | Kasus: ' . $counts['total']
        . ' | Progres pengobatan: ' . $village_percent . '%';

    $status_html = $format_status_details($counts['status_detail']);
    $village_table_body .= '<tr>'
        . '<td class="text-center">' . esc_html($code) . '</td>'
        . '<td>' . esc_html(isset($data['provinsi']) ? $data['provinsi'] : '') . '</td>'
        . '<td>' . esc_html(isset($data['kab_kot']) ? $data['kab_kot'] : '') . '</td>'
        . '<td>' . esc_html(isset($data['kecamatan']) ? $data['kecamatan'] : '') . '</td>'
        . '<td>' . esc_html($village_name) . '</td>'
        . '<td class="text-center" data-order="' . (int) $counts['total'] . '"><strong>' . $format_count($counts['total']) . '</strong></td>'
        . '<td class="satset-malaria-progress-status">' . $status_html . '</td>'
        . '<td class="text-center">' . esc_html($village_percent) . '%</td>'
        . '<td class="text-center"><button type="button" class="btn btn-sm btn-primary" onclick="window.satsetLeafletFocusRecord(' . (int) $index . ');">Lihat di peta</button></td>'
        . '</tr>';
}

foreach ($unmapped_counts as $location_key => $unmapped) {
    $counts = $unmapped['counts'];
    $village_progress = $counts['dalam_pengobatan'] + $counts['selesai'];
    $village_percent = $counts['total'] > 0
        ? round(($village_progress / $counts['total']) * 100, 1)
        : 0;
    $status_html = $format_status_details($counts['status_detail']);
    $village_table_body .= '<tr>'
        . '<td class="text-center">-</td>'
        . '<td>' . esc_html(isset($unmapped['location'][0]) ? $unmapped['location'][0] : '') . '</td>'
        . '<td>' . esc_html(isset($unmapped['location'][1]) ? $unmapped['location'][1] : '') . '</td>'
        . '<td>' . esc_html(isset($unmapped['location'][2]) ? $unmapped['location'][2] : '') . '</td>'
        . '<td>' . esc_html((isset($unmapped['location'][3]) ? $unmapped['location'][3] : '') . ' (Belum dipetakan)') . '</td>'
        . '<td class="text-center" data-order="' . (int) $counts['total'] . '"><strong>' . $format_count($counts['total']) . '</strong></td>'
        . '<td class="satset-malaria-progress-status">' . $status_html . '</td>'
        . '<td class="text-center">' . esc_html($village_percent) . '%</td>'
        . '<td class="text-center">-</td>'
        . '</tr>';
}
?>
<style>
    .satset-malaria-progress { max-width: 1240px; margin: 24px auto 60px; padding: 0 16px; }
    .satset-malaria-progress-header { text-align: center; margin-bottom: 24px; }
    .satset-malaria-progress-filter { display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap; margin: 20px 0; }
    .satset-malaria-progress-kpis { display: grid; grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 16px; margin: 20px 0; }
    .satset-malaria-progress-card { padding: 18px; border: 1px solid #d5dce2; border-radius: 8px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .satset-malaria-progress-card h3 { margin: 0 0 10px; font-size: 18px; }
    .satset-malaria-progress-kpi { font-size: 25px; font-weight: 700; color: #155e75; }
    .satset-malaria-progress-map { width: 100%; height: 70vh; min-height: 420px; margin: 20px 0; }
    .satset-malaria-progress-legend { display: flex; flex-wrap: wrap; gap: 12px 20px; margin: 12px 0 24px; }
    .satset-malaria-progress-legend span { display: inline-flex; align-items: center; gap: 7px; }
    .satset-malaria-progress-legend i { width: 16px; height: 16px; display: inline-block; border: 1px solid #667085; }
    .satset-malaria-progress-table { margin: 20px 0 30px; overflow-x: auto; }
    .satset-malaria-progress-table table { width: 100%; }
    .satset-malaria-progress-table th { white-space: nowrap; }
    .satset-malaria-progress-status { min-width: 190px; }
    .satset-malaria-progress-breakdowns { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin: 20px 0; }
    .satset-malaria-progress-breakdowns ul { margin-bottom: 0; }
    .satset-malaria-progress-ai-button { margin: 8px 0 24px; padding: 12px 24px; font-size: 16px; }
    .satset-malaria-progress-note { padding: 14px 18px; margin: 16px 0; border-left: 4px solid #0e7490; background: #ecfeff; color: #164e63; }
    #satset-malaria-progress-ai-analysis h2,
    #satset-malaria-progress-ai-analysis h3 { margin: 22px 0 12px; padding: 10px 14px; border-left: 4px solid #2563eb; border-radius: 6px; background: #eff6ff; }
    #satset-malaria-progress-ai-analysis .satset-ai-section-risk { border-color: #ea580c; background: #fff7ed; }
    #satset-malaria-progress-ai-analysis .satset-ai-section-action { border-color: #15803d; background: #f0fdf4; }
    #satset-malaria-progress-ai-analysis .satset-ai-section-action + ul,
    #satset-malaria-progress-ai-analysis .satset-ai-section-action + ol { padding-left: 0; list-style: none; }
    #satset-malaria-progress-ai-analysis .satset-ai-section-action + ul li,
    #satset-malaria-progress-ai-analysis .satset-ai-section-action + ol li { margin: 8px 0; padding: 12px 14px; border: 1px solid #bbf7d0; border-radius: 8px; background: #f0fdf4; }
    #satset-malaria-progress-ai-analysis .satset-ai-section-action + ul li::before,
    #satset-malaria-progress-ai-analysis .satset-ai-section-action + ol li::before { content: "✓"; margin-right: 8px; color: #15803d; font-weight: 700; }
    #satset-malaria-progress-ai-analysis .satset-ai-section-risk + ul li,
    #satset-malaria-progress-ai-analysis .satset-ai-section-risk + ol li { padding: 7px 0; }
    @keyframes satset-malaria-ai-progress { from { transform: translateX(-20%); } to { transform: translateX(190%); } }
    @media (max-width: 600px) { .satset-malaria-progress-status { min-width: 170px; } }
</style>

<main class="satset-malaria-progress">
    <header class="satset-malaria-progress-header">
        <h1>Progress Intervensi Malaria</h1>
        <h2><?php echo $this->getNamaDaerah(); ?></h2>
    </header>

    <form class="satset-malaria-progress-filter" method="get" action="<?php echo esc_url(get_permalink()); ?>">
        <label for="satset-malaria-progress-year">Tahun anggaran:</label>
        <select id="satset-malaria-progress-year" name="tahun_anggaran">
            <?php foreach ($years as $year) : ?>
                <option value="<?php echo esc_attr($year); ?>" <?php selected($year, $tahun_anggaran); ?>>
                    <?php echo esc_html($year); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <h2>Peta progres pengobatan Malaria</h2>
    <div id="map-canvas" class="satset-malaria-progress-map" aria-label="Peta progres pengobatan Malaria"></div>
    <div class="satset-malaria-progress-legend" aria-label="Keterangan warna peta">
        <span><i style="background:#b0bec5"></i> Abu-abu: tidak ada kasus.</span>
        <span><i style="background:#d7191c"></i> Merah: ada kasus, belum ada progres yang tercatat.</span>
        <span><i style="background:#fff176"></i> Kuning: progres tercatat di bawah 50%.</span>
        <span><i style="background:#8bc34a"></i> Hijau muda: progres tercatat 50–79%.</span>
        <span><i style="background:#138808"></i> Hijau tua: progres tercatat minimal 80%.</span>
    </div>

    <h2>Ringkasan kasus per desa</h2>
    <div class="satset-malaria-progress-table">
        <table class="table table-bordered table-sm" id="satset-malaria-progress-table">
            <thead>
                <tr>
                    <th class="text-center">Kode Desa</th>
                    <th>Provinsi</th>
                    <th>Kabupaten/Kota</th>
                    <th>Kecamatan</th>
                    <th>Desa</th>
                    <th class="text-center">Total Kasus</th>
                    <th>Status Pengobatan (jumlah)</th>
                    <th class="text-center">Progres</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($village_table_body !== '') : ?>
                    <?php echo $village_table_body; ?>
                <?php else : ?>
                    <tr><td colspan="9" class="text-center">Belum ada data desa untuk ditampilkan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <h2>Ringkasan agregat Malaria</h2>
    <section class="satset-malaria-progress-kpis" aria-label="Ringkasan progres Malaria">
        <div class="satset-malaria-progress-card">
            <h3>Total kasus</h3>
            <div class="satset-malaria-progress-kpi"><?php echo $format_count($total_counts['total']); ?></div>
            <p>Data aktif tahun anggaran <?php echo esc_html($tahun_anggaran); ?></p>
        </div>
        <div class="satset-malaria-progress-card">
            <h3>Dalam pengobatan</h3>
            <div class="satset-malaria-progress-kpi"><?php echo $format_count($total_counts['dalam_pengobatan']); ?></div>
        </div>
        <div class="satset-malaria-progress-card">
            <h3>Selesai pengobatan</h3>
            <div class="satset-malaria-progress-kpi"><?php echo $format_count($total_counts['selesai']); ?></div>
        </div>
        <div class="satset-malaria-progress-card">
            <h3>Progres pengobatan</h3>
            <div class="satset-malaria-progress-kpi"><?php echo esc_html($progress_percent); ?>%</div>
            <p><?php echo $format_count($progress_count); ?> dari <?php echo $format_count($total_counts['total']); ?> kasus berstatus Dalam Pengobatan atau Selesai.</p>
        </div>
    </section>
    <section class="satset-malaria-progress-breakdowns" aria-label="Rincian agregat">
        <div class="satset-malaria-progress-card">
            <h3>Status pengobatan</h3>
            <?php echo $format_breakdown($status_counts); ?>
        </div>
        <div class="satset-malaria-progress-card">
            <h3>Distribusi tindak lanjut</h3>
            <?php echo $format_breakdown($total_counts['tindak_lanjut']); ?>
        </div>
        <div class="satset-malaria-progress-card">
            <h3>Distribusi hasil akhir</h3>
            <?php echo $format_breakdown($total_counts['hasil_akhir']); ?>
        </div>
    </section>
    <div style="text-align:center;">
        <button type="button" class="btn btn-primary satset-malaria-progress-ai-button" id="satset-malaria-progress-ai-button">Analisis AI</button>
    </div>
    <p class="satset-malaria-progress-note">Halaman ini hanya menampilkan ringkasan agregat. NIK dan nama pasien tidak ditampilkan. Persentase progres dihitung dari kasus dengan status pengobatan “Dalam Pengobatan” atau “Selesai”.</p>
</main>

<div id="satset-malaria-progress-ai-modal" aria-hidden="true" style="display:none;position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.65);padding:24px;overflow:auto;">
    <section role="dialog" aria-modal="true" aria-labelledby="satset-malaria-progress-ai-title" style="position:relative;max-width:900px;margin:5vh auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 16px 50px rgba(0,0,0,.3);">
        <button type="button" id="satset-malaria-progress-ai-close" aria-label="Tutup popup" style="position:absolute;right:14px;top:10px;border:0;background:transparent;font-size:28px;cursor:pointer;">&times;</button>
        <h2 id="satset-malaria-progress-ai-title" style="margin:0 40px 8px 0;">Analisis AI Progress Malaria</h2>
        <p id="satset-malaria-progress-ai-scope" style="color:#52606d;"></p>
        <div id="satset-malaria-progress-ai-loading" role="status" aria-live="polite" style="display:none;">
            <p>Menunggu hasil analisis AI...</p>
            <div style="height:8px;overflow:hidden;border-radius:8px;background:#e5e7eb;"><div style="height:100%;width:35%;border-radius:8px;background:#2563eb;animation:satset-malaria-ai-progress 1.2s ease-in-out infinite alternate;"></div></div>
        </div>
        <p id="satset-malaria-progress-ai-error" role="alert" style="display:none;color:#b91c1c;"></p>
        <div id="satset-malaria-progress-ai-result" style="display:none;">
            <div id="satset-malaria-progress-ai-infographic" style="width:100%;overflow:auto;margin:20px 0;"></div>
            <div id="satset-malaria-progress-ai-analysis" style="line-height:1.7;border-top:1px solid #e5e7eb;padding-top:16px;"></div>
            <details style="margin-top:20px;">
                <summary>Prompt yang dikirim ke AI (hanya data agregat)</summary>
                <pre id="satset-malaria-progress-ai-prompt" style="white-space:pre-wrap;overflow-wrap:anywhere;max-height:300px;overflow:auto;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;"></pre>
            </details>
        </div>
    </section>
</div>

<script>
    window.maps_all = <?php echo wp_json_encode($maps_all, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    window.maps_center = <?php echo wp_json_encode($center); ?>;
    window.satsetMalariaAiConfig = {
        ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo wp_json_encode(wp_create_nonce('satset_analyze_malaria_progress')); ?>,
        year: <?php echo wp_json_encode((string) $tahun_anggaran); ?>,
        areaName: <?php echo wp_json_encode(trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(str_replace('<br>', ' ', $this->getNamaDaerah()))))); ?>,
        summary: <?php echo wp_json_encode(array_merge(array('total' => $total_counts['total']), array(
            'dalam_pengobatan' => $total_counts['dalam_pengobatan'],
            'selesai' => $total_counts['selesai'],
            'meninggal' => $total_counts['meninggal'],
            'status_lain' => $total_counts['status_lain'],
            'status_tidak_tercatat' => $total_counts['status_tidak_tercatat'],
            'status_pengobatan' => array(
                'Dalam pengobatan' => $total_counts['dalam_pengobatan'],
                'Selesai' => $total_counts['selesai'],
                'Meninggal' => $total_counts['meninggal'],
                'Status lain' => $total_counts['status_lain'],
                'Tidak tercatat' => $total_counts['status_tidak_tercatat'],
            ),
        )), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
        areas: <?php echo wp_json_encode($ai_areas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
    };
    jQuery(function () {
        var villageTable = jQuery('#satset-malaria-progress-table');
        if (villageTable.length && jQuery.fn.DataTable) {
            villageTable.DataTable({
                lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'Semua']],
                order: [[5, 'desc']],
                columnDefs: [{ orderable: false, targets: [6, 8] }]
            });
        }
    });

    (function () {
        var button = document.getElementById('satset-malaria-progress-ai-button');
        var modal = document.getElementById('satset-malaria-progress-ai-modal');
        var closeButton = document.getElementById('satset-malaria-progress-ai-close');
        var loading = document.getElementById('satset-malaria-progress-ai-loading');
        var error = document.getElementById('satset-malaria-progress-ai-error');
        var result = document.getElementById('satset-malaria-progress-ai-result');
        var scope = document.getElementById('satset-malaria-progress-ai-scope');
        var infographic = document.getElementById('satset-malaria-progress-ai-infographic');
        var analysis = document.getElementById('satset-malaria-progress-ai-analysis');
        var promptPreview = document.getElementById('satset-malaria-progress-ai-prompt');
        if (!button || !modal || !closeButton || !loading || !error || !result || !scope || !infographic || !analysis || !promptPreview) {
            return;
        }

        function closeModal() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }

        function renderMarkdown(markdown) {
            function escapeHtml(text) {
                return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            }
            var lines = markdown.split(/\r?\n/);
            var output = [];
            var paragraph = [];
            var listTag = '';
            var firstHeading = true;
            function inline(text) {
                return escapeHtml(text).replace(/`([^`]+)`/g, '<code>$1</code>').replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>').replace(/\*([^*]+)\*/g, '<em>$1</em>');
            }
            function flush() {
                if (paragraph.length) {
                    output.push('<p>' + inline(paragraph.join(' ')) + '</p>');
                    paragraph = [];
                }
            }
            function closeList() {
                if (listTag) {
                    output.push('</' + listTag + '>');
                    listTag = '';
                }
            }
            lines.forEach(function (line) {
                var heading = line.match(/^(#{1,3})\s+(.+)$/);
                var bullet = line.match(/^\s*[-*]\s+(.+)$/);
                var numbered = line.match(/^\s*\d+[.)]\s+(.+)$/);
                if (!line.trim()) {
                    flush();
                    closeList();
                } else if (heading) {
                    flush();
                    closeList();
                    var normalizedHeading = heading[2].trim().toLowerCase();
                    var isRepeatedMainTitle = firstHeading && (
                        heading[1].length === 1
                        || /^(analisis|ikhtisar|ringkasan|progress|progres)\b.*(malaria|intervensi)?/.test(normalizedHeading)
                    );
                    if (!isRepeatedMainTitle) {
                        var level = Math.min(3, heading[1].length + 1);
                        var sectionTitle = heading[2].toLowerCase();
                        var sectionClass = /kesenjangan|risiko/.test(sectionTitle)
                            ? 'satset-ai-section-risk'
                            : /rekomendasi|tindak lanjut/.test(sectionTitle)
                                ? 'satset-ai-section-action'
                                : 'satset-ai-section';
                        output.push('<h' + level + ' class="' + sectionClass + '">' + inline(heading[2]) + '</h' + level + '>');
                    }
                    firstHeading = false;
                } else if (bullet || numbered) {
                    flush();
                    var nextTag = bullet ? 'ul' : 'ol';
                    if (listTag !== nextTag) {
                        closeList();
                        listTag = nextTag;
                        output.push('<' + listTag + '>');
                    }
                    output.push('<li>' + inline((bullet || numbered)[1]) + '</li>');
                } else {
                    closeList();
                    paragraph.push(line.trim());
                }
            });
            flush();
            closeList();
            return output.join('');
        }

        function renderInfographic(data) {
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            var summary = data.summary || {};
            var areas = data.areas || [];
            var height = 220 + areas.length * 38;
            svg.setAttribute('viewBox', '0 0 760 ' + height);
            svg.setAttribute('role', 'img');
            svg.setAttribute('aria-label', 'Ringkasan agregat progres Malaria tahun ' + data.year);
            function add(name, attributes, text) {
                var element = document.createElementNS('http://www.w3.org/2000/svg', name);
                Object.keys(attributes).forEach(function (key) { element.setAttribute(key, attributes[key]); });
                if (text !== undefined) { element.textContent = text; }
                svg.appendChild(element);
            }
            add('rect', { x: 0, y: 0, width: 760, height: height, rx: 14, fill: '#f8fafc' });
            add('text', { x: 28, y: 38, fill: '#0f172a', 'font-size': 22, 'font-weight': 700 }, 'Progress Intervensi Malaria ' + data.year);
            add('text', { x: 28, y: 64, fill: '#475569', 'font-size': 14 }, data.area);
            add('text', { x: 28, y: 108, fill: '#0f172a', 'font-size': 16, 'font-weight': 700 }, 'Total kasus: ' + Number(summary.total || 0).toLocaleString('id-ID'));
            add('text', { x: 28, y: 138, fill: '#334155', 'font-size': 14 }, 'Dalam pengobatan: ' + Number(summary.dalam_pengobatan || 0).toLocaleString('id-ID') + ' | Selesai: ' + Number(summary.selesai || 0).toLocaleString('id-ID') + ' | Meninggal: ' + Number(summary.meninggal || 0).toLocaleString('id-ID'));
            add('text', { x: 28, y: 178, fill: '#0f172a', 'font-size': 15, 'font-weight': 700 }, 'Lokasi dengan jumlah kasus terbanyak');
            var maxAreaTotal = areas.length ? Math.max.apply(null, areas.map(function (area) { return Number(area.total) || 0; })) : 0;
            areas.forEach(function (area, index) {
                var y = 205 + index * 38;
                var total = Number(area.total) || 0;
                add('text', { x: 28, y: y, fill: '#334155', 'font-size': 13 }, area.name + ' (' + total.toLocaleString('id-ID') + ')');
                add('rect', { x: 28, y: y + 7, width: 704, height: 10, rx: 5, fill: '#e2e8f0' });
                add('rect', { x: 28, y: y + 7, width: maxAreaTotal > 0 ? 704 * total / maxAreaTotal : 0, height: 10, rx: 5, fill: '#0e7490' });
            });
            infographic.replaceChildren(svg);
        }

        button.addEventListener('click', function () {
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            loading.style.display = 'block';
            error.style.display = 'none';
            result.style.display = 'none';
            infographic.replaceChildren();
            analysis.textContent = '';
            promptPreview.textContent = '';
            button.disabled = true;
            var body = new URLSearchParams();
            body.append('action', 'satset_analyze_malaria_progress');
            body.append('nonce', window.satsetMalariaAiConfig.nonce);
            body.append('analysis_data', JSON.stringify({
                year: window.satsetMalariaAiConfig.year,
                area_name: window.satsetMalariaAiConfig.areaName,
                summary: window.satsetMalariaAiConfig.summary,
                areas: window.satsetMalariaAiConfig.areas
            }));
            fetch(window.satsetMalariaAiConfig.ajaxUrl, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
                body: body.toString()
            }).then(function (response) {
                return response.json().then(function (payload) {
                    if (!response.ok || !payload.success) {
                        throw new Error(payload && payload.data && payload.data.message ? payload.data.message : 'Analisis AI gagal diproses.');
                    }
                    return payload.data;
                });
            }).then(function (data) {
                scope.textContent = data.area + ' | Tahun anggaran ' + data.year;
                analysis.innerHTML = renderMarkdown(data.analysis);
                promptPreview.textContent = data.prompt;
                renderInfographic(data);
                result.style.display = 'block';
            }).catch(function (requestError) {
                error.textContent = requestError.message || 'Terjadi kesalahan saat meminta analisis AI.';
                error.style.display = 'block';
            }).finally(function () {
                loading.style.display = 'none';
                button.disabled = false;
            });
        });
        closeButton.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) { closeModal(); }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.style.display !== 'none') { closeModal(); }
        });
    }());
</script>
