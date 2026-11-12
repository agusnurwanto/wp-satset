<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$jumlah_minimum_publikasi = 5;
$years = $wpdb->get_col($wpdb->prepare("
    SELECT tahun_anggaran
    FROM data_aids
    WHERE active = 1 AND tahun_anggaran IS NOT NULL AND tahun_anggaran > 0
    GROUP BY tahun_anggaran
    HAVING COUNT(*) >= %d
    ORDER BY tahun_anggaran DESC
", $jumlah_minimum_publikasi));
if ($wpdb->last_error !== '' || !is_array($years)) {
    echo '<div class="alert alert-danger">Data progres AIDS tidak dapat dimuat. Silakan hubungi administrator.</div>';
    return;
}

$years = array_values(array_filter(array_map('intval', $years)));
$requested_year = isset($_GET['tahun_anggaran'])
    ? sanitize_text_field(wp_unslash($_GET['tahun_anggaran']))
    : '';
$default_year = (int) get_option('_crb_tahun_satset');
$tahun_anggaran = in_array((int) $requested_year, $years, true) && preg_match('/^\d{4}$/', $requested_year)
    ? (int) $requested_year
    : (in_array($default_year, $years, true) ? $default_year : (isset($years[0]) ? $years[0] : 0));

if (!$tahun_anggaran) {
    echo '<div class="alert alert-info">Belum ada data AIDS yang memenuhi ambang publikasi.</div>';
    return;
}

$rows = $wpdb->get_results($wpdb->prepare("
    SELECT
        kelompok_umur_terdiagnosis,
        nama_upk,
        kel_populasi_lsl,
        pendampingan_komunitas,
        capaian_t_dan_t_layanan
    FROM data_aids
    WHERE active = 1 AND tahun_anggaran = %d
", $tahun_anggaran), ARRAY_A);
if ($wpdb->last_error !== '' || !is_array($rows)) {
    echo '<div class="alert alert-danger">Data progres AIDS tidak dapat dimuat. Silakan hubungi administrator.</div>';
    return;
}

$center = $this->get_center();
$maps_all = $this->get_polygon();
$map_rows = $wpdb->get_results($wpdb->prepare("
    SELECT
        a.provinsi_domisili,
        a.kabkot_domisili,
        a.kecamatan_domisili,
        a.desa_domisili,
        m.kode_desa_satset
    FROM data_aids a
    LEFT JOIN (
        SELECT kode_desa_aids, MAX(kode_desa_satset) AS kode_desa_satset
        FROM mapping_aids
        GROUP BY kode_desa_aids
    ) m
        ON CONCAT_WS('-', a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili) = m.kode_desa_aids
    WHERE a.active = 1 AND a.tahun_anggaran = %d
", $tahun_anggaran), ARRAY_A);
if ($wpdb->last_error !== '' || !is_array($map_rows)) {
    echo '<div class="alert alert-danger">Peta progres AIDS tidak dapat dimuat. Silakan hubungi administrator.</div>';
    return;
}

$mapped_aids_counts = array();
$areas_by_location = array();
foreach ($map_rows as $map_row) {
    $desa_satset = trim((string) $map_row['kode_desa_satset']);
    $area_location = array(
        trim((string) $map_row['provinsi_domisili']),
        trim((string) $map_row['kabkot_domisili']),
        trim((string) $map_row['kecamatan_domisili']),
        trim((string) $map_row['desa_domisili']),
    );
    $area_location = array_values(array_filter($area_location, 'strlen'));
    $area_name = implode(', ', array_reverse($area_location));
    if ($desa_satset !== '') {
        $mapped_aids_counts[$desa_satset] = isset($mapped_aids_counts[$desa_satset])
            ? $mapped_aids_counts[$desa_satset] + 1
            : 1;
        $area_key = 'mapped:' . $desa_satset;
    } else {
        $area_key = 'unmapped:' . implode('-', $area_location);
    }
    if (!isset($areas_by_location[$area_key])) {
        $areas_by_location[$area_key] = array(
            'name' => $area_name,
            'location' => $area_location,
            'total' => 0,
        );
    }
    $areas_by_location[$area_key]['total']++;
}

$registration_rows = $wpdb->get_results("
    SELECT id_pasien, tanggal_register
    FROM data_aids
    WHERE active = 1
", ARRAY_A);
if ($wpdb->last_error !== '' || !is_array($registration_rows)) {
    echo '<div class="alert alert-danger">Tren pendaftaran AIDS tidak dapat dimuat. Silakan hubungi administrator.</div>';
    return;
}

$total_pasien = count($rows);
$tt_counts = array('Ya' => 0, 'Tidak' => 0, 'Tidak diketahui' => 0);
$pendampingan_counts = array('Ya' => 0, 'Tidak' => 0, 'Tidak diketahui' => 0);
$umur_counts = array();
$upk_counts = array();
$lsl_counts = array('Ya' => 0, 'Tidak' => 0, 'Tidak diketahui' => 0);
$registrasi_tahunan = array();
$registrasi_pertama_per_pasien = array();

$normalisasi_boolean = function ($value) {
    if ($value === null || trim((string) $value) === '') {
        return 'Tidak diketahui';
    }
    $value = strtolower(trim((string) $value));
    if (in_array($value, array('1', 'ya', 'yes', 'y', 'true'), true)) {
        return 'Ya';
    }
    if (in_array($value, array('0', 'tidak', 'no', 'n', 'false'), true)) {
        return 'Tidak';
    }
    return 'Tidak diketahui';
};

$parse_date = function ($value) {
    $value = trim((string) $value);
    if ($value === '') {
        return null;
    }

    $candidates = array();
    if (preg_match('/^\d{4}-\d{2}-\d{2}(?:[ T].*)?$/', $value)) {
        $candidates[] = array('!Y-m-d', substr($value, 0, 10));
    } elseif (preg_match('/^\d{1,2}\/\d{1,2}\/\d{4}$/', $value)) {
        $candidates[] = array('!d/m/Y', $value);
    } elseif (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $value)) {
        $candidates[] = array('!d-m-Y', $value);
    }

    foreach ($candidates as $candidate) {
        $date = DateTimeImmutable::createFromFormat($candidate[0], $candidate[1]);
        $errors = DateTimeImmutable::getLastErrors();
        if ($date && ($errors === false || (!$errors['warning_count'] && !$errors['error_count']))) {
            return $date;
        }
    }
    return null;
};

foreach ($rows as $row) {
    $tt = $normalisasi_boolean(isset($row['capaian_t_dan_t_layanan']) ? $row['capaian_t_dan_t_layanan'] : null);
    $pendampingan = $normalisasi_boolean(isset($row['pendampingan_komunitas']) ? $row['pendampingan_komunitas'] : null);
    $lsl = $normalisasi_boolean(isset($row['kel_populasi_lsl']) ? $row['kel_populasi_lsl'] : null);
    $tt_counts[$tt]++;
    $pendampingan_counts[$pendampingan]++;
    $lsl_counts[$lsl]++;

    $umur = trim((string) (isset($row['kelompok_umur_terdiagnosis']) ? $row['kelompok_umur_terdiagnosis'] : ''));
    if ($umur === '') {
        $umur = 'Tidak tercatat';
    }
    if (!isset($umur_counts[$umur])) {
        $umur_counts[$umur] = 0;
    }
    $umur_counts[$umur]++;

    $upk = trim((string) (isset($row['nama_upk']) ? $row['nama_upk'] : ''));
    if ($upk === '') {
        $upk = 'UPK tidak tercatat';
    }
    if (!isset($upk_counts[$upk])) {
        $upk_counts[$upk] = 0;
    }
    $upk_counts[$upk]++;

}

foreach ($registration_rows as $row) {
    $patient_id = trim((string) (isset($row['id_pasien']) ? $row['id_pasien'] : ''));
    if ($patient_id === '') {
        continue;
    }
    $date = $parse_date(isset($row['tanggal_register']) ? $row['tanggal_register'] : '');
    if ($date) {
        $patient_key = 'id:' . strtolower($patient_id);
        if (
            !isset($registrasi_pertama_per_pasien[$patient_key])
            || $date->getTimestamp() < $registrasi_pertama_per_pasien[$patient_key]->getTimestamp()
        ) {
            $registrasi_pertama_per_pasien[$patient_key] = $date;
        }
    }
}
foreach ($registrasi_pertama_per_pasien as $date) {
    $year = $date->format('Y');
    if (!isset($registrasi_tahunan[$year])) {
        $registrasi_tahunan[$year] = 0;
    }
    $registrasi_tahunan[$year]++;
}

$contains_small_group = function ($counts) use ($jumlah_minimum_publikasi) {
    foreach ($counts as $count) {
        if ($count > 0 && $count < $jumlah_minimum_publikasi) {
            return true;
        }
    }
    return false;
};

$status_kpi_aman = !$contains_small_group($tt_counts);
$pendampingan_kpi_aman = !$contains_small_group($pendampingan_counts);
$ai_metrics = array();
if (!$contains_small_group($tt_counts)) {
    $ai_metrics['capaian_tt'] = $tt_counts;
}
if (!$contains_small_group($pendampingan_counts)) {
    $ai_metrics['pendampingan'] = $pendampingan_counts;
}
if (!$contains_small_group($lsl_counts)) {
    $ai_metrics['populasi_lsl'] = $lsl_counts;
}
$ai_areas = array_values(array_filter($areas_by_location, function ($area) use ($jumlah_minimum_publikasi) {
    return $area['total'] >= $jumlah_minimum_publikasi;
}));
usort($ai_areas, function ($left, $right) {
    return $right['total'] - $left['total'];
});
$ai_areas = array_slice($ai_areas, 0, 10);
$ai_summary = array_merge(array('total' => $total_pasien), $ai_metrics);

foreach ($maps_all as $index => $desa) {
    $kode_desa = isset($desa['data']['id2012']) ? (string) $desa['data']['id2012'] : '';
    $total_desa = isset($mapped_aids_counts[$kode_desa]) ? $mapped_aids_counts[$kode_desa] : 0;
    if ($total_desa === 0) {
        $color = '#0cbf00';
    } elseif ($total_desa <= 10) {
        $color = '#fff70a';
    } else {
        $color = '#ff0000';
    }

    $desa_name = isset($desa['data']['desa']) ? $desa['data']['desa'] : '';
    $maps_all[$index]['index'] = $index;
    $maps_all[$index]['color'] = $color;
    $maps_all[$index]['html'] = '<div class="satset-aids-progress-popup">'
        . '<strong>' . esc_html($desa_name) . '</strong>'
        . '<div>Total pasien AIDS/HIV: <strong>' . esc_html(number_format_i18n($total_desa)) . '</strong> orang</div>'
        . ($total_desa === 0 ? '<div>Tidak ada data AIDS di desa ini.</div>' : '')
        . '</div>';
    $maps_all[$index]['tooltip'] = esc_html($desa_name) . ' | Total pasien AIDS/HIV: ' . number_format_i18n($total_desa);
}

$village_table_body = '';
foreach ($maps_all as $index => $desa) {
    $desa_data = isset($desa['data']) && is_array($desa['data']) ? $desa['data'] : array();
    $kode_desa = isset($desa_data['id2012']) ? (string) $desa_data['id2012'] : '';
    $total_desa = isset($mapped_aids_counts[$kode_desa]) ? $mapped_aids_counts[$kode_desa] : 0;
    $village_table_body .= '<tr>'
        . '<td class="text-center">' . esc_html($kode_desa) . '</td>'
        . '<td>' . esc_html(isset($desa_data['provinsi']) ? $desa_data['provinsi'] : '') . '</td>'
        . '<td>' . esc_html(isset($desa_data['kab_kot']) ? $desa_data['kab_kot'] : '') . '</td>'
        . '<td>' . esc_html(isset($desa_data['kecamatan']) ? $desa_data['kecamatan'] : '') . '</td>'
        . '<td>' . esc_html(isset($desa_data['desa']) ? $desa_data['desa'] : '') . '</td>'
        . '<td class="text-center" data-order="' . (int) $total_desa . '"><strong>' . esc_html(number_format_i18n($total_desa)) . '</strong></td>'
        . '<td class="text-center"><button type="button" class="btn btn-sm btn-primary" onclick="window.satsetLeafletFocusRecord(' . (int) $index . ');">Lihat di peta</button></td>'
        . '</tr>';
}
foreach ($areas_by_location as $area_key => $area) {
    if (strpos($area_key, 'unmapped:') !== 0) {
        continue;
    }
    $location = isset($area['location']) ? $area['location'] : array();
    $village_table_body .= '<tr class="table-warning">'
        . '<td class="text-center">-</td>'
        . '<td>' . esc_html(isset($location[0]) ? $location[0] : '') . '</td>'
        . '<td>' . esc_html(isset($location[1]) ? $location[1] : '') . '</td>'
        . '<td>' . esc_html(isset($location[2]) ? $location[2] : '') . '</td>'
        . '<td>' . esc_html(isset($location[3]) ? $location[3] : '') . ' <span class="satset-aids-progress-muted">(belum termapping)</span></td>'
        . '<td class="text-center" data-order="' . (int) $area['total'] . '"><strong>' . esc_html(number_format_i18n($area['total'])) . '</strong></td>'
        . '<td class="text-center">-</td>'
        . '</tr>';
}

$chart_bars = function ($title, $description, $counts) use ($contains_small_group, $jumlah_minimum_publikasi) {
    $html = '<section class="satset-aids-progress-card"><h3>' . esc_html($title) . '</h3>';
    if (!$counts) {
        return $html . '<p class="satset-aids-progress-muted">Belum ada data yang dapat ditampilkan.</p></section>';
    }
    if ($contains_small_group($counts)) {
        return $html . '<p class="satset-aids-progress-muted">Rincian disamarkan untuk menjaga kerahasiaan kelompok dengan jumlah di bawah '
            . (int) $jumlah_minimum_publikasi . '.</p></section>';
    }

    $maximum = max($counts);
    $html .= '<p class="satset-aids-progress-muted">' . esc_html($description) . '</p><div class="satset-aids-progress-bars">';
    foreach ($counts as $label => $count) {
        $width = $maximum > 0 ? round(($count / $maximum) * 100, 1) : 0;
        $html .= '<div class="satset-aids-progress-bar-row">'
            . '<div class="satset-aids-progress-bar-label">' . esc_html($label) . '</div>'
            . '<div class="satset-aids-progress-bar-track"><span style="width:' . esc_attr($width) . '%"></span></div>'
            . '<div class="satset-aids-progress-bar-value">' . esc_html(number_format_i18n($count)) . '</div>'
            . '</div>';
    }
    return $html . '</div></section>';
};

$upk_table = function ($counts) use ($contains_small_group, $jumlah_minimum_publikasi) {
    $html = '<section class="satset-aids-progress-card"><h3>Rekap agregat per UPK</h3>';
    if (!$counts) {
        return $html . '<p class="satset-aids-progress-muted">Belum ada data UPK yang dapat ditampilkan.</p></section>';
    }
    if ($contains_small_group($counts)) {
        return $html . '<p class="satset-aids-progress-muted">Tabel disamarkan karena terdapat kelompok di bawah '
            . (int) $jumlah_minimum_publikasi . '.</p></section>';
    }

    $html .= '<div class="table-responsive"><table class="table table-bordered table-sm">'
        . '<thead><tr><th>Fasilitas kesehatan (UPK)</th><th class="text-right">Total agregat</th></tr></thead><tbody>';
    foreach ($counts as $label => $count) {
        $html .= '<tr><td>' . esc_html($label) . '</td><td class="text-right">' . esc_html(number_format_i18n($count)) . '</td></tr>';
    }
    return $html . '</tbody></table></div></section>';
};

$format_kpi = function ($count, $is_safe) use ($total_pasien, $jumlah_minimum_publikasi) {
    if ($total_pasien > 0 && $total_pasien < $jumlah_minimum_publikasi) {
        return 'Disamarkan (&lt; ' . (int) $jumlah_minimum_publikasi . ')';
    }
    if (!$is_safe) {
        return 'Disamarkan';
    }
    $percentage = $total_pasien > 0 ? round(($count / $total_pasien) * 100, 1) : 0;
    return esc_html(number_format_i18n($count) . ' / ' . number_format_i18n($total_pasien) . ' (' . number_format_i18n($percentage, 1) . '%)');
};

ksort($umur_counts, SORT_NATURAL | SORT_FLAG_CASE);
ksort($upk_counts, SORT_NATURAL | SORT_FLAG_CASE);
ksort($registrasi_tahunan, SORT_NATURAL);

?>
<style>
    .satset-aids-progress { max-width: 1240px; margin: 24px auto 60px; padding: 0 16px; }
    .satset-aids-progress h1, .satset-aids-progress h2, .satset-aids-progress h3 { color: #263238; }
    .satset-aids-progress-header { text-align: center; margin-bottom: 24px; }
    .satset-aids-progress-filter { display: flex; justify-content: center; align-items: center; gap: 10px; flex-wrap: wrap; margin: 20px 0; }
    .satset-aids-progress-kpis, .satset-aids-progress-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(230px, 1fr)); gap: 16px; margin: 20px 0; }
    .satset-aids-progress-card { min-width: 0; padding: 18px; border: 1px solid #d5dce2; border-radius: 8px; background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,.06); }
    .satset-aids-progress-card h3 { margin: 0 0 10px; font-size: 18px; }
    .satset-aids-progress-kpi { font-size: 25px; font-weight: 700; color: #155e75; overflow-wrap: anywhere; }
    .satset-aids-progress-muted { color: #52606d; font-size: 13px; }
    .satset-aids-progress-note { padding: 14px 18px; margin: 16px 0; border-left: 4px solid #0e7490; background: #ecfeff; color: #164e63; }
    .satset-aids-progress-bars { display: grid; gap: 12px; }
    .satset-aids-progress-bar-row { display: grid; grid-template-columns: minmax(100px, 1.2fr) minmax(80px, 2fr) 70px; align-items: center; gap: 10px; }
    .satset-aids-progress-bar-label { overflow-wrap: anywhere; }
    .satset-aids-progress-bar-track { height: 14px; overflow: hidden; border-radius: 8px; background: #e2e8f0; }
    .satset-aids-progress-bar-track span { display: block; height: 100%; border-radius: inherit; background: #0e7490; }
    .satset-aids-progress-bar-value { text-align: right; font-weight: 600; }
    .satset-aids-progress .table-responsive { overflow-x: auto; }
    .satset-aids-progress-map { width: 100%; height: 70vh; min-height: 420px; margin: 20px 0; }
    .satset-aids-progress-legend { display: flex; flex-wrap: wrap; gap: 12px 20px; margin: 12px 0 24px; }
    .satset-aids-progress-legend span { display: inline-flex; align-items: center; gap: 7px; }
    .satset-aids-progress-legend i { width: 16px; height: 16px; display: inline-block; border: 1px solid #667085; }
    .satset-aids-progress-villages { margin: 20px 0 30px; overflow-x: auto; }
    .satset-aids-progress-villages table { width: 100%; }
    .satset-aids-progress-villages th { white-space: nowrap; }
    .satset-aids-progress-ai-button { margin: 8px 0 24px; padding: 12px 24px; font-size: 16px; }
    .satset-aids-progress-ai-analysis h3 { margin-top: 20px; }
    @media (max-width: 600px) {
        .satset-aids-progress-bar-row { grid-template-columns: minmax(90px, 1fr) minmax(50px, 1fr) 54px; gap: 6px; font-size: 13px; }
    }
</style>

<main class="satset-aids-progress">
    <header class="satset-aids-progress-header">
        <h1>Progress Intervensi AIDS/HIV</h1>
        <h2><?php echo $this->getNamaDaerah(); ?></h2>
    </header>

    <form class="satset-aids-progress-filter" method="get" action="<?php echo esc_url(get_permalink()); ?>">
        <label for="satset-aids-progress-year">Tahun anggaran:</label>
        <select id="satset-aids-progress-year" name="tahun_anggaran">
            <?php foreach ($years as $year) : ?>
                <option value="<?php echo esc_attr($year); ?>" <?php selected($year, $tahun_anggaran); ?>>
                    <?php echo esc_html($year); ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="btn btn-primary">Tampilkan</button>
    </form>

    <h2>Peta sebaran AIDS/HIV</h2>
    <div id="map-canvas" class="satset-aids-progress-map" aria-label="Peta sebaran pasien AIDS/HIV"></div>
    <div class="satset-aids-progress-legend" aria-label="Keterangan warna peta">
        <span><i style="background:#0cbf00"></i> Hijau: tidak ada pasien.</span>
        <span><i style="background:#fff70a"></i> Kuning: 1–10 pasien.</span>
        <span><i style="background:#ff0000"></i> Merah: lebih dari 10 pasien.</span>
    </div>

    <h2>Data AIDS/HIV per desa</h2>
    <div class="satset-aids-progress-villages">
        <table class="table table-bordered table-sm" id="satset-aids-progress-villages-table">
            <thead>
                <tr>
                    <th class="text-center">Kode Desa</th>
                    <th>Provinsi</th>
                    <th>Kabupaten/Kota</th>
                    <th>Kecamatan</th>
                    <th>Desa</th>
                    <th class="text-center">Total Pasien</th>
                    <th class="text-center">Peta</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($village_table_body !== '') : ?>
                    <?php echo $village_table_body; ?>
                <?php else : ?>
                    <tr><td colspan="7" class="text-center">Belum ada data desa untuk ditampilkan.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="satset-aids-progress-note">
        Ringkasan hanya menampilkan data agregat. Rincian yang memiliki kelompok berjumlah 1–4 disamarkan, termasuk rincian pendampingnya untuk mencegah penghitungan balik. Data yang tersedia belum mencakup catatan pengambilan ARV berkala atau viral load, sehingga retensi terapi dan supresi virus tidak diukur di halaman ini.
    </div>

    <?php if ($total_pasien > 0 && $total_pasien < $jumlah_minimum_publikasi) : ?>
        <div class="alert alert-info">Data agregat disamarkan karena jumlah keseluruhan berada di bawah ambang publikasi.</div>
    <?php else : ?>
        <section class="satset-aids-progress-kpis" aria-label="Ringkasan indikator">
            <div class="satset-aids-progress-card">
                <h3>Total pasien terdaftar</h3>
                <div class="satset-aids-progress-kpi">
                    <?php echo esc_html(number_format_i18n($total_pasien)); ?>
                </div>
                <p class="satset-aids-progress-muted">Data aktif tahun anggaran <?php echo esc_html($tahun_anggaran); ?></p>
            </div>
            <div class="satset-aids-progress-card">
                <h3>Capaian T&amp;T layanan</h3>
                <div class="satset-aids-progress-kpi"><?php echo $format_kpi($tt_counts['Ya'], $status_kpi_aman); ?></div>
                <p class="satset-aids-progress-muted">Jumlah dengan nilai layanan “Ya”; bukan indikator kepatuhan ARV.</p>
            </div>
            <div class="satset-aids-progress-card">
                <h3>Pendampingan komunitas</h3>
                <div class="satset-aids-progress-kpi"><?php echo $format_kpi($pendampingan_counts['Ya'], $pendampingan_kpi_aman); ?></div>
                <p class="satset-aids-progress-muted">Jumlah dengan pendampingan komunitas tercatat “Ya”.</p>
            </div>
            <div class="satset-aids-progress-card">
                <h3>Fasilitas layanan</h3>
                <div class="satset-aids-progress-kpi"><?php echo esc_html(number_format_i18n(count($upk_counts))); ?></div>
                <p class="satset-aids-progress-muted">Jumlah UPK berbeda yang tercatat; distribusi disajikan di bawah jika aman.</p>
            </div>
        </section>

        <div style="text-align:center;">
            <button type="button" class="btn btn-primary satset-aids-progress-ai-button" id="satset-aids-progress-ai-button">
                Analisis AI
            </button>
        </div>

        <h2>Ringkasan indikator layanan</h2>
        <section class="satset-aids-progress-grid">
            <?php
            echo $chart_bars('Capaian T&T layanan', 'Data layanan T&T yang tercatat', $tt_counts);
            echo $chart_bars('Pendampingan komunitas', 'Status pendampingan yang tercatat', $pendampingan_counts);
            ?>
        </section>

        <h2>Distribusi agregat</h2>
        <section class="satset-aids-progress-grid">
            <?php
            echo $chart_bars('Distribusi fasilitas kesehatan (UPK)', 'Jumlah pasien per UPK; rincian kecil disamarkan', $upk_counts);
            echo $chart_bars('Kelompok umur saat terdiagnosis', 'Kelompok umur yang tercatat', $umur_counts);
            echo $chart_bars('Indikator populasi LSL', 'Nilai agregat yang tercatat; tidak ditampilkan per pasien', $lsl_counts);
            ?>
        </section>
        <?php echo $upk_table($upk_counts); ?>

        <h2>Tren pendaftaran lintas tahun</h2>
        <section class="satset-aids-progress-grid">
            <?php
            echo $chart_bars('Pendaftaran per tahun', 'Pasien unik dengan ID pasien dan tanggal_register valid; deduplikasi lintas tahun anggaran. Identitas pasien tidak ditampilkan.', $registrasi_tahunan);
            ?>
        </section>
    <?php endif; ?>
</main>
<div id="satset-aids-progress-ai-modal" aria-hidden="true" style="display:none;position:fixed;inset:0;z-index:100000;background:rgba(15,23,42,.65);padding:24px;overflow:auto;">
    <section role="dialog" aria-modal="true" aria-labelledby="satset-aids-progress-ai-title" style="position:relative;max-width:900px;margin:5vh auto;padding:24px;background:#fff;border-radius:12px;box-shadow:0 16px 50px rgba(0,0,0,.3);">
        <button type="button" id="satset-aids-progress-ai-close" aria-label="Tutup popup" style="position:absolute;right:14px;top:10px;border:0;background:transparent;font-size:28px;cursor:pointer;">&times;</button>
        <h2 id="satset-aids-progress-ai-title" style="margin:0 40px 8px 0;">Analisis AI Progress AIDS/HIV</h2>
        <p id="satset-aids-progress-ai-scope" class="satset-aids-progress-muted"></p>
        <div id="satset-aids-progress-ai-loading" role="status" aria-live="polite" style="display:none;">
            <p>Menunggu hasil analisis AI...</p>
            <div style="height:8px;overflow:hidden;border-radius:8px;background:#e5e7eb;"><div style="height:100%;width:35%;border-radius:8px;background:#2563eb;animation:satset-aids-ai-progress 1.2s ease-in-out infinite alternate;"></div></div>
        </div>
        <p id="satset-aids-progress-ai-error" role="alert" style="display:none;color:#b91c1c;"></p>
        <div id="satset-aids-progress-ai-result" style="display:none;">
            <div id="satset-aids-progress-ai-infographic" style="width:100%;overflow:auto;margin:20px 0;"></div>
            <div style="text-align:center;">
                <button type="button" id="satset-aids-progress-ai-show-areas" class="btn btn-default" style="display:none;margin:0 auto 20px;">Lihat lokasi lainnya</button>
            </div>
            <div id="satset-aids-progress-ai-analysis" class="satset-aids-progress-ai-analysis" style="line-height:1.7;border-top:1px solid #e5e7eb;padding-top:16px;"></div>
            <details style="margin-top:20px;">
                <summary>Prompt yang dikirim ke AI (data sensitif telah disamarkan)</summary>
                <pre id="satset-aids-progress-ai-prompt" style="white-space:pre-wrap;overflow-wrap:anywhere;max-height:300px;overflow:auto;padding:12px;background:#f8fafc;border:1px solid #e2e8f0;border-radius:6px;"></pre>
            </details>
        </div>
    </section>
</div>
<style>
    @keyframes satset-aids-ai-progress {
        from { transform: translateX(-20%); }
        to { transform: translateX(190%); }
    }
    #satset-aids-progress-ai-analysis h3 {
        margin: 22px 0 12px;
        padding: 10px 14px;
        border-left: 4px solid #2563eb;
        border-radius: 6px;
        background: #eff6ff;
    }
    #satset-aids-progress-ai-analysis .satset-ai-section-risk {
        border-color: #ea580c;
        background: #fff7ed;
    }
    #satset-aids-progress-ai-analysis .satset-ai-section-action {
        border-color: #15803d;
        background: #f0fdf4;
    }
    #satset-aids-progress-ai-analysis .satset-ai-section-action + ul,
    #satset-aids-progress-ai-analysis .satset-ai-section-action + ol {
        padding-left: 0;
        list-style: none;
    }
    #satset-aids-progress-ai-analysis .satset-ai-section-action + ul li,
    #satset-aids-progress-ai-analysis .satset-ai-section-action + ol li {
        margin: 8px 0;
        padding: 12px 14px;
        border: 1px solid #bbf7d0;
        border-radius: 8px;
        background: #f0fdf4;
    }
    #satset-aids-progress-ai-analysis .satset-ai-section-action + ul li::before,
    #satset-aids-progress-ai-analysis .satset-ai-section-action + ol li::before {
        content: "✓";
        margin-right: 8px;
        color: #15803d;
        font-weight: 700;
    }
</style>
<script type="text/javascript">
    window.maps_all = <?php echo wp_json_encode($maps_all, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    window.maps_center = <?php echo wp_json_encode($center); ?>;
    window.satsetAidsAiConfig = {
        ajaxUrl: <?php echo wp_json_encode(admin_url('admin-ajax.php')); ?>,
        nonce: <?php echo wp_json_encode(wp_create_nonce('satset_analyze_aids_progress')); ?>,
        year: <?php echo wp_json_encode((string) $tahun_anggaran); ?>,
        areaName: <?php echo wp_json_encode(trim(preg_replace('/\s+/', ' ', wp_strip_all_tags(str_replace('<br>', ' ', $this->getNamaDaerah()))))); ?>,
        summary: <?php echo wp_json_encode($ai_summary, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>,
        areas: <?php echo wp_json_encode($ai_areas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>
    };
    jQuery(function () {
        var villageTable = jQuery('#satset-aids-progress-villages-table');
        if (villageTable.length && jQuery.fn.DataTable) {
            villageTable.DataTable({
                lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'Semua']],
                order: [[5, 'desc']],
                columnDefs: [{ orderable: false, targets: [6] }]
            });
        }
    });

    (function () {
        var button = document.getElementById('satset-aids-progress-ai-button');
        var modal = document.getElementById('satset-aids-progress-ai-modal');
        var closeButton = document.getElementById('satset-aids-progress-ai-close');
        var loading = document.getElementById('satset-aids-progress-ai-loading');
        var error = document.getElementById('satset-aids-progress-ai-error');
        var result = document.getElementById('satset-aids-progress-ai-result');
        var scope = document.getElementById('satset-aids-progress-ai-scope');
        var infographic = document.getElementById('satset-aids-progress-ai-infographic');
        var analysis = document.getElementById('satset-aids-progress-ai-analysis');
        var promptPreview = document.getElementById('satset-aids-progress-ai-prompt');
        var showAreasButton = document.getElementById('satset-aids-progress-ai-show-areas');
        var currentAreas = [];
        var currentSummary = {};
        var showAllAreas = false;
        if (!button || !modal || !closeButton || !loading || !error || !result || !scope || !infographic || !analysis || !promptPreview || !showAreasButton) {
            return;
        }

        function closeModal() {
            modal.style.display = 'none';
            modal.setAttribute('aria-hidden', 'true');
        }

        function renderMarkdown(markdown) {
            var escapeHtml = function (text) {
                return text.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
            };
            var lines = markdown.split(/\r?\n/);
            var output = [];
            var paragraph = [];
            var listTag = '';
            var firstHeading = true;
            function inline(text) {
                return escapeHtml(text)
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
                var heading = line.match(/^#{1,3}\s+(.+)$/);
                var bullet = line.match(/^\s*[-*]\s+(.+)$/);
                var ordered = line.match(/^\s*\d+[.)]\s+(.+)$/);
                if (!line.trim()) {
                    flushParagraph();
                    closeList();
                } else if (heading) {
                    flushParagraph();
                    closeList();
                    var normalizedHeading = heading[1].trim().toLowerCase();
                    var repeatedMainTitle = firstHeading && (
                        /^#{1}\s/.test(line)
                        || /^(analisis|ikhtisar|ringkasan|progress|progres)\b.*(aids|hiv|penanganan)?/.test(normalizedHeading)
                    );
                    if (!repeatedMainTitle) {
                        var sectionClass = /kesenjangan|risiko/.test(normalizedHeading)
                            ? 'satset-ai-section-risk'
                            : /rekomendasi|tindak lanjut/.test(normalizedHeading)
                                ? 'satset-ai-section-action'
                                : 'satset-ai-section';
                        output.push('<h3 class="' + sectionClass + '">' + inline(heading[1]) + '</h3>');
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

        function createInfographic(data) {
            var summary = data.summary || {};
            currentAreas = data.areas || [];
            var areas = showAllAreas ? currentAreas : currentAreas.slice(0, 5);
            var rowHeight = 38;
            var metricEntries = Object.keys(summary).filter(function (key) {
                return key !== 'total' && summary[key] && summary[key].counts;
            });
            var metricHeight = metricEntries.length ? 54 : 0;
            var firstAreaY = 218 + metricHeight;
            var height = firstAreaY + (areas.length * rowHeight) + 24;
            var svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
            svg.setAttribute('xmlns', 'http://www.w3.org/2000/svg');
            svg.setAttribute('viewBox', '0 0 760 ' + height);
            svg.setAttribute('role', 'img');
            svg.setAttribute('aria-label', 'Ringkasan agregat progress AIDS/HIV tahun ' + data.year);
            appendSvgElement(svg, 'rect', { x: 0, y: 0, width: 760, height: height, rx: 14, fill: '#f8fafc' });
            appendSvgElement(svg, 'text', { x: 28, y: 38, fill: '#0f172a', 'font-size': 22, 'font-weight': 700 }, 'Progress Intervensi AIDS/HIV ' + data.year);
            appendSvgElement(svg, 'text', { x: 28, y: 64, fill: '#475569', 'font-size': 14 }, data.area);
            appendSvgElement(svg, 'text', { x: 28, y: 108, fill: '#0f172a', 'font-size': 17, 'font-weight': 700 }, 'Total pasien terdaftar: ' + Number(summary.total || 0).toLocaleString('id-ID'));
            appendSvgElement(svg, 'rect', { x: 28, y: 122, width: 704, height: 14, rx: 7, fill: '#e2e8f0' });
            appendSvgElement(svg, 'rect', { x: 28, y: 122, width: 704, height: 14, rx: 7, fill: '#0e7490' });

            var metricsY = 164;
            metricEntries.forEach(function (key, index) {
                var metric = summary[key];
                var counts = metric.counts;
                var text = metric.label + ': Ya ' + counts.Ya + ' | Tidak ' + counts.Tidak + ' | Tidak diketahui ' + counts['Tidak diketahui'];
                appendSvgElement(svg, 'text', { x: 28, y: metricsY + (index * 22), fill: '#334155', 'font-size': 13 }, text);
            });
            appendSvgElement(svg, 'text', { x: 28, y: firstAreaY - 18, fill: '#0f172a', 'font-size': 15, 'font-weight': 700 }, 'Lokasi dengan jumlah pasien terbanyak');

            var maxAreaTotal = areas.length ? Math.max.apply(null, areas.map(function (area) { return Number(area.total) || 0; })) : 0;
            areas.forEach(function (area, index) {
                var y = firstAreaY + (index * rowHeight);
                var total = Number(area.total) || 0;
                var barWidth = maxAreaTotal > 0 ? 704 * total / maxAreaTotal : 0;
                appendSvgElement(svg, 'text', { x: 28, y: y, fill: '#334155', 'font-size': 13 }, area.name + ' (' + total.toLocaleString('id-ID') + ')');
                appendSvgElement(svg, 'rect', { x: 28, y: y + 7, width: 704, height: 10, rx: 5, fill: '#e2e8f0' });
                appendSvgElement(svg, 'rect', { x: 28, y: y + 7, width: barWidth, height: 10, rx: 5, fill: '#0e7490' });
            });
            infographic.replaceChildren(svg);
            showAreasButton.style.display = currentAreas.length > 5 ? 'inline-block' : 'none';
            showAreasButton.textContent = showAllAreas ? 'Tampilkan lebih sedikit lokasi' : 'Lihat ' + (currentAreas.length - 5) + ' lokasi lainnya';
        }

        button.addEventListener('click', function () {
            modal.style.display = 'block';
            modal.setAttribute('aria-hidden', 'false');
            loading.style.display = 'block';
            error.style.display = 'none';
            result.style.display = 'none';
            analysis.textContent = '';
            infographic.replaceChildren();
            promptPreview.textContent = '';
            button.disabled = true;

            var body = new URLSearchParams();
            body.append('action', 'satset_analyze_aids_progress');
            body.append('nonce', window.satsetAidsAiConfig.nonce);
            body.append('analysis_data', JSON.stringify({
                year: window.satsetAidsAiConfig.year,
                area_name: window.satsetAidsAiConfig.areaName,
                summary: window.satsetAidsAiConfig.summary,
                areas: window.satsetAidsAiConfig.areas
            }));
            fetch(window.satsetAidsAiConfig.ajaxUrl, {
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
                currentSummary = data.summary;
                currentAreas = data.areas || [];
                showAllAreas = false;
                createInfographic(data);
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
        showAreasButton.addEventListener('click', function () {
            showAllAreas = !showAllAreas;
            createInfographic({
                summary: currentSummary,
                areas: currentAreas,
                year: window.satsetAidsAiConfig.year,
                area: window.satsetAidsAiConfig.areaName
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
