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
    unset($tbc_all_desa[$kdwil]);
}

foreach ($tbc_all_desa as $kdwil => $records) {
    $counts = $get_progress_counts($records);
    $info = reset($records);
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
}

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
<script type="text/javascript">
    window.maps_all = <?php echo wp_json_encode($maps_all, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT); ?>;
    window.maps_center = <?php echo wp_json_encode($center); ?>;
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
</script>
