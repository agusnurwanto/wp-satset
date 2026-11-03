<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$tahun_anggaran = !empty($_GET['tahun_anggaran'])
    ? sanitize_text_field(wp_unslash($_GET['tahun_anggaran']))
    : get_option('_crb_tahun_satset');
$center = $this->get_center();
$maps_all = $this->get_polygon();
$tbc_all = $this->get_tbc();
$tbc_all_desa = array();
$total_tindak_lanjut = array();

foreach ($tbc_all as $record) {
    $kdwil = isset($record['kdwil']) ? $record['kdwil'] : '';
    if (!isset($tbc_all_desa[$kdwil])) {
        $tbc_all_desa[$kdwil] = array();
    }
    $tbc_all_desa[$kdwil][] = $record;

    $tindak_lanjut = isset($record['tindak_lanjut']) ? trim((string) $record['tindak_lanjut']) : '';
    $label = $tindak_lanjut !== '' ? $tindak_lanjut : '(Tidak Ada Tindak Lanjut)';
    if (!isset($total_tindak_lanjut[$label])) {
        $total_tindak_lanjut[$label] = 0;
    }
    $total_tindak_lanjut[$label]++;
}
ksort($total_tindak_lanjut);

$warna_tindak_lanjut_lain = array(
    '#795548', '#673ab7', '#009688', '#e91e63', '#3f51b5',
    '#ff9800', '#607d8b', '#8bc34a', '#9c27b0', '#00acc1',
    '#f4511e', '#5c6bc0',
);
$jenis_tindak_lanjut_lain = array();
foreach ($total_tindak_lanjut as $label => $count) {
    $normalized = strtolower($label);
    if (
        $label !== '(Tidak Ada Tindak Lanjut)'
        && strpos($normalized, 'akan diobati') === false
        && strpos($normalized, 'dirujuk') === false
    ) {
        $jenis_tindak_lanjut_lain[] = $label;
    }
}
$warna_per_tindak_lanjut = array();
foreach ($jenis_tindak_lanjut_lain as $index => $label) {
    $warna_per_tindak_lanjut[$label] = $warna_tindak_lanjut_lain[$index % count($warna_tindak_lanjut_lain)];
}

$get_progress_counts = function ($records) {
    $counts = array(
        'total' => count($records),
        'akan_diobati' => 0,
        'dirujuk' => 0,
        'progress' => 0,
        'tindak_lanjut' => array(),
    );
    foreach ($records as $record) {
        $tindak_lanjut = isset($record['tindak_lanjut']) ? trim((string) $record['tindak_lanjut']) : '';
        $key = $tindak_lanjut !== '' ? $tindak_lanjut : '(Tidak Ada Tindak Lanjut)';
        if (!isset($counts['tindak_lanjut'][$key])) {
            $counts['tindak_lanjut'][$key] = 0;
        }
        $counts['tindak_lanjut'][$key]++;

        $normalized = strtolower($tindak_lanjut);
        $is_akan_diobati = strpos($normalized, 'akan diobati') !== false;
        $is_dirujuk = strpos($normalized, 'dirujuk') !== false;
        if ($is_akan_diobati) {
            $counts['akan_diobati']++;
        }
        if ($is_dirujuk) {
            $counts['dirujuk']++;
        }
        if ($is_akan_diobati || $is_dirujuk) {
            $counts['progress']++;
        }
    }
    ksort($counts['tindak_lanjut']);
    return $counts;
};

$total_progress = 0;
$total_pasien = 0;
$total_akan_diobati = 0;
$total_dirujuk = 0;
$total_tanpa_tindak_lanjut = 0;
$ai_areas = array();
$body = '';
foreach ($maps_all as $i => $desa) {
    $kdwil = isset($desa['data']['id2012']) ? $desa['data']['id2012'] : '';
    $records = isset($tbc_all_desa[$kdwil]) ? $tbc_all_desa[$kdwil] : array();
    $counts = $get_progress_counts($records);
    $progress_rate = $counts['total'] > 0 ? ($counts['progress'] / $counts['total']) : 0;
    $dominant_follow_up = '';
    $patients_without_follow_up = isset($counts['tindak_lanjut']['(Tidak Ada Tindak Lanjut)'])
        ? $counts['tindak_lanjut']['(Tidak Ada Tindak Lanjut)']
        : 0;
    if ($counts['total'] > 0) {
        $area_location = array(
            isset($desa['data']['provinsi']) ? trim((string) $desa['data']['provinsi']) : '',
            isset($desa['data']['kab_kot']) ? trim((string) $desa['data']['kab_kot']) : '',
            isset($desa['data']['kecamatan']) ? trim((string) $desa['data']['kecamatan']) : '',
            isset($desa['data']['desa']) ? trim((string) $desa['data']['desa']) : '',
        );
        $area_location = array_values(array_filter($area_location, 'strlen'));
        $ai_areas[] = array(
            'name' => implode(', ', array_reverse($area_location)),
            'total' => $counts['total'],
            'akan_diobati' => $counts['akan_diobati'],
            'dirujuk' => $counts['dirujuk'],
            'progress' => $counts['progress'],
            'tanpa_tindak_lanjut' => $patients_without_follow_up,
            'persentase_progress' => $counts['total'] > 0 ? round($progress_rate * 100, 1) : 0,
        );
    }

    if ($counts['total'] === 0) {
        $color = '#263238';
    } elseif ($patients_without_follow_up > 0) {
        $color = '#d7191c';
    } elseif ($progress_rate >= 1) {
        $color = '#006400';
    } elseif ($progress_rate >= 0.8) {
        $color = '#138808';
    } elseif ($progress_rate >= 0.6) {
        $color = '#32a852';
    } elseif ($progress_rate >= 0.4) {
        $color = '#8bc34a';
    } elseif ($progress_rate >= 0.2) {
        $color = '#cddc39';
    } elseif ($progress_rate > 0) {
        $color = '#fff176';
    } else {
        $other_follow_ups = array();
        foreach ($counts['tindak_lanjut'] as $label => $count) {
            if (isset($warna_per_tindak_lanjut[$label])) {
                $other_follow_ups[$label] = $count;
            }
        }
        if ($other_follow_ups) {
            arsort($other_follow_ups);
            $dominant_follow_up = key($other_follow_ups);
            $color = $warna_per_tindak_lanjut[$dominant_follow_up];
        } else {
            $color = '#b0bec5';
        }
    }

    $maps_all[$i]['index'] = $i;
    $maps_all[$i]['color'] = $color;
    $desa_name = isset($desa['data']['desa']) ? $desa['data']['desa'] : '';
    $follow_up_popup = '';
    if ($counts['tindak_lanjut']) {
        $follow_up_items = '';
        foreach ($counts['tindak_lanjut'] as $label => $count) {
            $follow_up_items .= '<li>' . esc_html($label) . ': <strong>' . $count . ' pasien</strong></li>';
        }
        $follow_up_popup = '<div class="satset-tbc-popup-section"><strong>Rincian tindak lanjut</strong><ul>' . $follow_up_items . '</ul></div>';
    }
    $progress_percentage = $counts['total'] > 0 ? round($progress_rate * 100, 1) : 0;
    $maps_all[$i]['html'] = '<div class="satset-tbc-popup">'
        . '<strong class="satset-tbc-popup-title">' . esc_html($desa_name) . '</strong>'
        . '<div>Total pasien TBC: <strong>' . $counts['total'] . '</strong></div>'
        . ($counts['total'] === 0
            ? '<div>Tidak ada pasien; tidak diperlukan tindak lanjut.</div>'
            : '<div>Akan Diobati: <strong>' . $counts['akan_diobati'] . ' pasien</strong></div>'
                . '<div>Dirujuk: <strong>' . $counts['dirujuk'] . ' pasien</strong></div>'
                . '<div>Belum ada tindak lanjut: <strong>' . $patients_without_follow_up . ' pasien</strong></div>'
                . '<div>Total progres utama: <strong>' . $counts['progress'] . ' dari ' . $counts['total'] . ' pasien (' . $progress_percentage . '%)</strong></div>')
        . $follow_up_popup
        . '</div>';
    $maps_all[$i]['tooltip'] = esc_html($desa_name) . ' | Pasien TBC: ' . $counts['total']
        . ' | Belum ada tindak lanjut: ' . $patients_without_follow_up
        . ' | Progres: ' . $counts['progress'] . ' (' . $progress_percentage . '%)';

    $follow_up = array();
    foreach ($counts['tindak_lanjut'] as $label => $count) {
        $follow_up[] = esc_html($label) . ': ' . $count;
    }
    $follow_up_html = implode('<br>', $follow_up);
    $body .= '<tr>'
        . '<td class="text-center">' . esc_html($kdwil) . '</td>'
        . '<td class="text-center">' . esc_html(isset($desa['data']['provinsi']) ? $desa['data']['provinsi'] : '') . '</td>'
        . '<td class="text-center">' . esc_html(isset($desa['data']['kab_kot']) ? $desa['data']['kab_kot'] : '') . '</td>'
        . '<td class="text-center">' . esc_html(isset($desa['data']['kecamatan']) ? $desa['data']['kecamatan'] : '') . '</td>'
        . '<td class="text-center">' . esc_html(isset($desa['data']['desa']) ? $desa['data']['desa'] : '') . '</td>'
        . '<td class="text-center">' . $counts['total'] . '</td>'
        . '<td class="text-center">' . $counts['akan_diobati'] . '</td>'
        . '<td class="text-center">' . $counts['dirujuk'] . '</td>'
        . '<td class="text-center">' . $counts['progress'] . '</td>'
        . '<td>' . $follow_up_html . '</td>'
        . '<td class="text-center"><button type="button" class="btn btn-sm btn-primary" onclick="window.satsetLeafletFocusRecord(' . (int) $i . ');">Map</button></td>'
        . '</tr>';

    $total_progress += $counts['progress'];
    $total_pasien += $counts['total'];
    $total_akan_diobati += $counts['akan_diobati'];
    $total_dirujuk += $counts['dirujuk'];
    $total_tanpa_tindak_lanjut += $patients_without_follow_up;
    unset($tbc_all_desa[$kdwil]);
}

foreach ($tbc_all_desa as $kdwil => $records) {
    $counts = $get_progress_counts($records);
    $info = reset($records);
    if ($counts['total'] > 0) {
        $area_location = array(
            isset($info['provinsi']) ? trim((string) $info['provinsi']) : '',
            isset($info['kabkot']) ? trim((string) $info['kabkot']) : '',
            isset($info['kecamatan']) ? trim((string) $info['kecamatan']) : '',
            isset($info['desa']) ? trim((string) $info['desa']) : '',
        );
        $area_location = array_values(array_filter($area_location, 'strlen'));
        $without_follow_up = isset($counts['tindak_lanjut']['(Tidak Ada Tindak Lanjut)'])
            ? $counts['tindak_lanjut']['(Tidak Ada Tindak Lanjut)']
            : 0;
        $ai_areas[] = array(
            'name' => implode(', ', array_reverse($area_location)),
            'total' => $counts['total'],
            'akan_diobati' => $counts['akan_diobati'],
            'dirujuk' => $counts['dirujuk'],
            'progress' => $counts['progress'],
            'tanpa_tindak_lanjut' => $without_follow_up,
            'persentase_progress' => round(($counts['progress'] / $counts['total']) * 100, 1),
        );
    }
    $follow_up = array();
    foreach ($counts['tindak_lanjut'] as $label => $count) {
        $follow_up[] = esc_html($label) . ': ' . $count;
    }
    $body .= '<tr>'
        . '<td class="text-center">' . esc_html($kdwil) . '</td>'
        . '<td class="text-center">' . esc_html(isset($info['provinsi']) ? $info['provinsi'] : '') . '</td>'
        . '<td class="text-center">' . esc_html(isset($info['kabkot']) ? $info['kabkot'] : '') . '</td>'
        . '<td class="text-center">' . esc_html(isset($info['kecamatan']) ? $info['kecamatan'] : '') . '</td>'
        . '<td class="text-center">' . esc_html(isset($info['desa']) ? $info['desa'] : '') . ' (Di luar daerah)</td>'
        . '<td class="text-center">' . $counts['total'] . '</td>'
        . '<td class="text-center">' . $counts['akan_diobati'] . '</td>'
        . '<td class="text-center">' . $counts['dirujuk'] . '</td>'
        . '<td class="text-center">' . $counts['progress'] . '</td>'
        . '<td>' . implode('<br>', $follow_up) . '</td>'
        . '<td class="text-center">-</td>'
        . '</tr>';
    $total_progress += $counts['progress'];
    $total_pasien += $counts['total'];
    $total_akan_diobati += $counts['akan_diobati'];
    $total_dirujuk += $counts['dirujuk'];
    $total_tanpa_tindak_lanjut += $without_follow_up;
}

usort($ai_areas, function ($left, $right) {
    if ($left['tanpa_tindak_lanjut'] !== $right['tanpa_tindak_lanjut']) {
        return $right['tanpa_tindak_lanjut'] - $left['tanpa_tindak_lanjut'];
    }
    if ($left['persentase_progress'] !== $right['persentase_progress']) {
        return $left['persentase_progress'] < $right['persentase_progress'] ? -1 : 1;
    }
    return $right['total'] - $left['total'];
});
$ai_areas = array_slice($ai_areas, 0, 10);
$ai_summary = array(
    'total' => $total_pasien,
    'akan_diobati' => $total_akan_diobati,
    'dirujuk' => $total_dirujuk,
    'progress' => $total_progress,
    'tanpa_tindak_lanjut' => $total_tanpa_tindak_lanjut,
);
$ai_summary['persentase_progress'] = $total_pasien > 0 ? round(($total_progress / $total_pasien) * 100, 1) : 0;

$tahun = $wpdb->get_results('
    SELECT tahun_anggaran
    FROM satset_data_unit
    GROUP BY tahun_anggaran
    ORDER BY tahun_anggaran ASC
', ARRAY_A);
$select_tahun = '';
foreach ($tahun as $tahun_value) {
    $year = $tahun_value['tahun_anggaran'];
    $selected = (string) $year === (string) $tahun_anggaran ? 'selected' : '';
    $select_tahun .= '<option value="' . esc_attr($year) . '" ' . $selected . '>' . esc_html($year) . '</option>';
}
?>
<h1 class="text-center">Progress Intervensi TBC<br><?php echo $this->getNamaDaerah(); ?></h1>
<div class="text-center" style="margin-top: 30px;">
    <label for="tahun_anggaran">Tahun Anggaran: </label>
    <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
        <?php echo $select_tahun; ?>
    </select>
    <button style="margin-left: 10px; height: 45px; width: 75px;" onclick="submitTahunProgressTbc();" class="btn btn-sm btn-primary">Cari</button>
</div>
<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px; padding-top: 50px;">
    <div id="map-canvas" style="width: 100%; height: 100vh;"></div>
    <h3 style="margin-top: 20px;">Keterangan Peta</h3>
    <ul>
        <li><span style="display:inline-block;width:16px;height:16px;background:#263238;"></span> Abu-abu gelap: tidak ada pasien TBC.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#d7191c;"></span> Merah: ada pasien yang belum memiliki tindak lanjut.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#b0bec5;"></span> Abu-abu terang: data tidak memiliki kategori tindak lanjut yang dapat dipetakan.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#fff176;"></span> Kuning muda: 1–19% pasien memiliki tindak lanjut Akan Diobati atau Dirujuk.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#cddc39;"></span> Hijau kekuningan: 20–39% pasien memiliki tindak lanjut Akan Diobati atau Dirujuk.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#8bc34a;"></span> Hijau muda: 40–59% pasien memiliki tindak lanjut Akan Diobati atau Dirujuk.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#32a852;"></span> Hijau: 60–79% pasien memiliki tindak lanjut Akan Diobati atau Dirujuk.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#138808;"></span> Hijau tua: 80–99% pasien memiliki tindak lanjut Akan Diobati atau Dirujuk.</li>
        <li><span style="display:inline-block;width:16px;height:16px;background:#006400;"></span> Hijau paling tua: seluruh pasien memiliki tindak lanjut Akan Diobati atau Dirujuk.</li>
        <?php foreach ($warna_per_tindak_lanjut as $label => $color) : ?>
            <li><span style="display:inline-block;width:16px;height:16px;background:<?php echo esc_attr($color); ?>;"></span> <?php echo esc_html($label); ?>: jenis tindak lanjut lainnya yang paling banyak di desa.</li>
        <?php endforeach; ?>
    </ul>
    <h2 class="text-center">Total Progress Tindak Lanjut: <?php echo esc_html($this->number_format($total_progress)); ?> dari <?php echo esc_html($this->number_format($total_pasien)); ?> pasien</h2>
    <div style="display:flex;flex-wrap:wrap;justify-content:center;gap:15px;margin:20px 0 35px;">
        <?php foreach ($total_tindak_lanjut as $label => $count) : ?>
            <div style="flex:1 1 180px;max-width:260px;padding:18px;border:1px solid #d5dce2;border-radius:8px;background:#f8fafb;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.08);">
                <div style="font-size:15px;font-weight:600;"><?php echo esc_html($label); ?></div>
                <div style="font-size:28px;font-weight:700;margin-top:8px;"><?php echo esc_html($this->number_format($count)); ?></div>
                <div>pasien</div>
            </div>
        <?php endforeach; ?>
    </div>
    <div style="text-align:center;margin:0 0 35px;">
        <button type="button" class="btn btn-primary" id="satset-tbc-ai-button" style="padding:12px 24px;font-size:16px;">
            Analisis AI
        </button>
    </div>
    <div style="width: 100%; overflow: auto; height: 100vh;">
        <table class="table table-bordered" id="table-data">
            <thead>
                <tr>
                    <th class="text-center">Kode Desa</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kabupaten/Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Desa</th>
                    <th class="text-center">Total Pasien TBC</th>
                    <th class="text-center">Akan Diobati</th>
                    <th class="text-center">Dirujuk</th>
                    <th class="text-center">Total Progress</th>
                    <th>Rincian Tindak Lanjut</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody><?php echo $body; ?></tbody>
        </table>
    </div>
</div>
<div id="satset-tbc-ai-modal" aria-hidden="true" style="display:none;position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.65);padding:24px;overflow:auto;">
    <section role="dialog" aria-modal="true" aria-labelledby="satset-tbc-ai-title" style="position:relative;max-width:900px;margin:3vh auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 16px 50px rgba(0,0,0,.3);">
        <button type="button" id="satset-tbc-ai-close" aria-label="Tutup popup" style="position:absolute;right:14px;top:10px;border:0;background:transparent;font-size:28px;cursor:pointer;">&times;</button>
        <h2 id="satset-tbc-ai-title" style="margin:0 40px 8px 0;">Analisis AI Progress TBC</h2>
        <p id="satset-tbc-ai-scope" style="color:#52606d;margin-top:0;"></p>
        <div id="satset-tbc-ai-loading" role="status" aria-live="polite">
            <p id="satset-tbc-ai-loading-text">Menyiapkan ringkasan data...</p>
            <div style="height:8px;overflow:hidden;border-radius:8px;background:#e5e7eb;">
                <div style="height:100%;width:35%;border-radius:8px;background:#2563eb;animation:satset-tbc-ai-progress 1.2s ease-in-out infinite alternate;"></div>
            </div>
        </div>
        <p id="satset-tbc-ai-error" role="alert" style="display:none;color:#b91c1c;"></p>
        <div id="satset-tbc-ai-result" style="display:none;">
            <div id="satset-tbc-ai-infographic" style="width:100%;overflow:auto;margin:20px 0;"></div>
            <div style="text-align:center;">
                <button type="button" id="satset-tbc-ai-show-areas" class="btn btn-default" style="display:none;margin:0 auto 20px;">Lihat lokasi lainnya</button>
            </div>
            <div id="satset-tbc-ai-analysis" style="line-height:1.7;border-top:1px solid #e5e7eb;padding-top:16px;"></div>
            <details style="margin-top:20px;">
                <summary>Prompt yang dikirim ke AI (nama lokasi disamarkan)</summary>
                <pre id="satset-tbc-ai-prompt" style="white-space:pre-wrap;overflow-wrap:anywhere;max-height:300px;overflow:auto;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;"></pre>
            </details>
        </div>
    </section>
</div>
<style>
    @keyframes satset-tbc-ai-progress {
        from { transform: translateX(-20%); }
        to { transform: translateX(190%); }
    }
    #satset-tbc-ai-analysis h2,
    #satset-tbc-ai-analysis h3 {
        margin: 22px 0 12px;
        padding: 10px 14px;
        border-left: 4px solid #2563eb;
        border-radius: 6px;
        background: #eff6ff;
    }
    #satset-tbc-ai-analysis .satset-ai-section-risk {
        border-color: #ea580c;
        background: #fff7ed;
    }
    #satset-tbc-ai-analysis .satset-ai-section-action {
        border-color: #15803d;
        background: #f0fdf4;
    }
    #satset-tbc-ai-analysis .satset-ai-section-action + ul,
    #satset-tbc-ai-analysis .satset-ai-section-action + ol {
        padding-left: 0;
        list-style: none;
    }
    #satset-tbc-ai-analysis .satset-ai-section-action + ul li,
    #satset-tbc-ai-analysis .satset-ai-section-action + ol li {
        margin: 8px 0;
        padding: 12px 14px;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        background: #f0fdf4;
    }
    #satset-tbc-ai-analysis .satset-ai-section-action + ul li::before,
    #satset-tbc-ai-analysis .satset-ai-section-action + ol li::before {
        content: "✓";
        margin-right: 8px;
        color: #15803d;
        font-weight: 700;
    }
    #satset-tbc-ai-analysis .satset-ai-section-risk + ul li,
    #satset-tbc-ai-analysis .satset-ai-section-risk + ol li {
        padding: 7px 0;
    }
</style>
<script type="text/javascript">
    window.maps_all = <?php echo wp_json_encode($maps_all, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    window.maps_center = <?php echo wp_json_encode($center); ?>;
    window.satsetTbcAiConfig = {
        ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo wp_json_encode(wp_create_nonce('satset_analyze_tbc_progress')); ?>,
        year: <?php echo wp_json_encode((string) $tahun_anggaran); ?>,
        areaName: <?php echo wp_json_encode(trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(str_replace('<br>', ' ', $this->getNamaDaerah()))))); ?>,
        summary: <?php echo wp_json_encode($ai_summary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
        areas: <?php echo wp_json_encode($ai_areas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
    };
    jQuery('#table-data').dataTable({
        lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
        order: [[8, 'desc']]
    });
    function submitTahunProgressTbc() {
        var tahunAnggaran = jQuery('#tahun_anggaran').val();
        if (!tahunAnggaran) {
            return alert('Tahun tidak boleh kosong!');
        }
        window.location.href = window.location.href.split('?')[0] + '?tahun_anggaran=' + encodeURIComponent(tahunAnggaran);
    }

    (function () {
        var button = document.getElementById('satset-tbc-ai-button');
        var modal = document.getElementById('satset-tbc-ai-modal');
        var closeButton = document.getElementById('satset-tbc-ai-close');
        var loading = document.getElementById('satset-tbc-ai-loading');
        var loadingText = document.getElementById('satset-tbc-ai-loading-text');
        var error = document.getElementById('satset-tbc-ai-error');
        var result = document.getElementById('satset-tbc-ai-result');
        var infographic = document.getElementById('satset-tbc-ai-infographic');
        var analysis = document.getElementById('satset-tbc-ai-analysis');
        var promptPreview = document.getElementById('satset-tbc-ai-prompt');
        var showAreasButton = document.getElementById('satset-tbc-ai-show-areas');
        var currentAreas = [];
        var currentSummary = {};
        var showAllAreas = false;

        function closeModal() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }

        function appendSvgElement(svg, name, attributes, text) {
            var element = document.createElementNS('http://www.w3.org/2000/svg', name);
            Object.keys(attributes).forEach(function (key) {
                element.setAttribute(key, attributes[key]);
            });
            if (text !== undefined) {
                element.textContent = text;
            }
            svg.appendChild(element);
            return element;
        }

        function escapeMarkdownHtml(value) {
            return value.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
        }

        function renderMarkdown(markdown) {
            var lines = markdown.split(/\r?\n/);
            var output = [];
            var paragraph = [];
            var listTag = '';
            var firstHeading = true;

            function inline(text) {
                return escapeMarkdownHtml(text)
                    .replace(/`([^`]+)`/g, '<code>$1</code>')
                    .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*([^*]+)\*/g, '<em>$1</em>');
            }

            function closeList() {
                if (listTag) {
                    output.push('</' + listTag + '>');
                    listTag = '';
                }
            }

            function flushParagraph() {
                if (paragraph.length) {
                    output.push('<p>' + inline(paragraph.join(' ')) + '</p>');
                    paragraph = [];
                }
            }

            lines.forEach(function (line) {
                var heading = line.match(/^(#{1,3})\s+(.+)$/);
                var bullet = line.match(/^\s*[-*]\s+(.+)$/);
                var ordered = line.match(/^\s*\d+[.)]\s+(.+)$/);
                if (!line.trim()) {
                    flushParagraph();
                    closeList();
                } else if (heading) {
                    flushParagraph();
                    closeList();
                    var normalizedHeading = heading[2].trim().toLowerCase();
                    var isRepeatedMainTitle = firstHeading && (
                        heading[1].length === 1
                        || /^(analisis|ikhtisar|ringkasan|progress|progres)\b.*(tbc|penanganan)?/.test(normalizedHeading)
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
                } else if (bullet || ordered) {
                    flushParagraph();
                    var nextListTag = bullet ? 'ul' : 'ol';
                    if (listTag !== nextListTag) {
                        closeList();
                        listTag = nextListTag;
                        output.push('<' + listTag + '>');
                    }
                    output.push('<li>' + inline((bullet || ordered)[1]) + '</li>');
                } else {
                    closeList();
                    paragraph.push(line.trim());
                }
            });
            flushParagraph();
            closeList();
            return output.join('');
        }

        function createInfographic(data) {
            var summary = data.summary;
            currentAreas = data.areas || [];
            var areas = showAllAreas ? currentAreas : currentAreas.slice(0, 5);
            var rowHeight = 38;
            var height = 200 + (areas.length * rowHeight);
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svg.setAttribute('viewBox', '0 0 760 ' + height);
            svg.setAttribute('role', 'img');
            svg.setAttribute('aria-label', 'Infografis progress tindak lanjut TBC tahun ' + data.year);
            appendSvgElement(svg, 'rect', { x: 0, y: 0, width: 760, height: height, rx: 14, fill: '#f8fafc' });
            appendSvgElement(svg, 'text', { x: 28, y: 38, fill: '#0f172a', 'font-size': 22, 'font-weight': 700 }, 'Progress Tindak Lanjut TBC ' + data.year);
            appendSvgElement(svg, 'text', { x: 28, y: 64, fill: '#475569', 'font-size': 14 }, data.area);

            var percentage = Number(summary.persentase_progress) || 0;
            appendSvgElement(svg, 'text', { x: 28, y: 105, fill: '#0f172a', 'font-size': 16, 'font-weight': 600 }, 'Progress utama: ' + summary.progress + ' dari ' + summary.total + ' pasien (' + percentage + '%)');
            appendSvgElement(svg, 'rect', { x: 28, y: 118, width: 704, height: 16, rx: 8, fill: '#e2e8f0' });
            appendSvgElement(svg, 'rect', { x: 28, y: 118, width: 704 * Math.min(100, Math.max(0, percentage)) / 100, height: 16, rx: 8, fill: '#15803d' });
            appendSvgElement(svg, 'text', { x: 28, y: 160, fill: '#334155', 'font-size': 14 }, 'Akan diobati: ' + summary.akan_diobati + '  |  Dirujuk: ' + summary.dirujuk + '  |  Belum ada tindak lanjut: ' + summary.tanpa_tindak_lanjut);
            appendSvgElement(svg, 'text', { x: 28, y: 190, fill: '#0f172a', 'font-size': 15, 'font-weight': 700 }, 'Lokasi prioritas untuk perhatian');

            areas.forEach(function (area, index) {
                var y = 218 + (index * rowHeight);
                var areaPercentage = Number(area.persentase_progress) || 0;
                appendSvgElement(svg, 'text', { x: 28, y: y, fill: '#334155', 'font-size': 13 }, area.name + ' (' + area.progress + '/' + area.total + ', ' + areaPercentage + '%)');
                appendSvgElement(svg, 'rect', { x: 28, y: y + 7, width: 704, height: 10, rx: 5, fill: '#e2e8f0' });
                appendSvgElement(svg, 'rect', { x: 28, y: y + 7, width: 704 * Math.min(100, Math.max(0, areaPercentage)) / 100, height: 10, rx: 5, fill: areaPercentage === 0 || area.tanpa_tindak_lanjut > 0 ? '#dc2626' : '#2563eb' });
            });

            infographic.replaceChildren(svg);
            showAreasButton.style.display = currentAreas.length > 5 ? 'inline-block' : 'none';
            showAreasButton.textContent = showAllAreas ? 'Tampilkan lebih sedikit lokasi' : 'Lihat ' + (currentAreas.length - 5) + ' lokasi lainnya';
        }

        button.addEventListener('click', function () {
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            loading.style.display = 'block';
            loadingText.textContent = 'Menyiapkan ringkasan data agregat...';
            error.style.display = 'none';
            result.style.display = 'none';
            button.disabled = true;

            var loadingTimer = window.setTimeout(function () {
                loadingText.textContent = 'Menunggu hasil analisis AI...';
            }, 2500);
            var body = new URLSearchParams();
            body.append('action', 'satset_analyze_tbc_progress');
            body.append('nonce', window.satsetTbcAiConfig.nonce);
            body.append('analysis_data', JSON.stringify({
                year: window.satsetTbcAiConfig.year,
                area_name: window.satsetTbcAiConfig.areaName,
                summary: window.satsetTbcAiConfig.summary,
                areas: window.satsetTbcAiConfig.areas
            }));

            fetch(window.satsetTbcAiConfig.ajaxUrl, {
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
                loading.style.display = 'none';
                document.getElementById('satset-tbc-ai-scope').textContent = data.area + ' | Tahun anggaran ' + data.year;
                analysis.innerHTML = renderMarkdown(data.analysis);
                promptPreview.textContent = data.prompt;
                showAllAreas = false;
                currentSummary = data.summary;
                createInfographic(data);
                result.style.display = 'block';
            }).catch(function (requestError) {
                loading.style.display = 'none';
                error.textContent = requestError.message || 'Terjadi kesalahan saat meminta analisis AI.';
                error.style.display = 'block';
            }).finally(function () {
                window.clearTimeout(loadingTimer);
                button.disabled = false;
            });
        });

        closeButton.addEventListener('click', closeModal);
        showAreasButton.addEventListener('click', function () {
            showAllAreas = !showAllAreas;
            createInfographic({
                summary: currentSummary,
                areas: currentAreas,
                year: window.satsetTbcAiConfig.year,
                area: window.satsetTbcAiConfig.areaName
            });
        });
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.style.display !== 'none') {
                closeModal();
            }
        });
    }());
</script>
