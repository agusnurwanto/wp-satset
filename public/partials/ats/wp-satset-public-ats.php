<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$data_ats = $this->functions->generatePage(array(
    'nama_page' => 'Data Detail ATS', 
    'content' => '[data_detail_ats]',
    'show_header' => 1,
    'post_status' => 'private'
));

$center   = $this->get_center();
$maps_all = $this->get_polygon();

// Ambil data ATS
$ats_rows = $wpdb->get_results(
    "
    SELECT
        kdwil,
        TRIM(provinsi)          AS provinsi,
        TRIM(kab_kot)           AS kab_kot,
        TRIM(kecamatan)         AS kecamatan,
        TRIM(desa)              AS desa,
        status,
        jenis_kelamin,
        usia
    FROM data_ats_satset
    WHERE active = 1
    ",
    ARRAY_A
);

// Agregasi per kdwil
$ats_wilayah   = array();
$total_ats_all = 0;

foreach ($ats_rows as $row) {
    $kdwil = trim($row['kdwil']);
    if ($kdwil === '') continue;

    if (empty($ats_wilayah[$kdwil])) {
        $ats_wilayah[$kdwil] = array(
            'total'    => 0,
            'DO'       => 0,
            'LTM'      => 0,
            'BPB'      => 0,
            'usia_sampai_15' => 0,
            'usia_diatas_15' => 0,
            'L'        => 0,
            'P'        => 0,
            'provinsi' => trim($row['provinsi']),
            'kab_kot'  => trim($row['kab_kot']),
        );
    }

    $ats_wilayah[$kdwil]['total']++;
    $total_ats_all++;

    $st = strtoupper(trim($row['status']));
    if ($st === 'DO')  $ats_wilayah[$kdwil]['DO']++;
    if ($st === 'LTM') $ats_wilayah[$kdwil]['LTM']++;
    if ($st === 'BPB') $ats_wilayah[$kdwil]['BPB']++;

    $usia = (int) $row['usia'];
    if ($usia <= 15) $ats_wilayah[$kdwil]['usia_sampai_15']++;
    if ($usia > 15) $ats_wilayah[$kdwil]['usia_diatas_15']++;

    $jk = strtoupper(trim($row['jenis_kelamin']));
    if ($jk === 'L') $ats_wilayah[$kdwil]['L']++;
    if ($jk === 'P') $ats_wilayah[$kdwil]['P']++;
}

$total_ats_filtered = $total_ats_all;

// Update terakhir
$last_update = $wpdb->get_var("SELECT MAX(update_at) FROM data_ats_satset");
$bulan = array(
    1  => 'Januari',
    'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
);
$timestamp        = strtotime($last_update);
$last_update_text = date('d', $timestamp) . ' '
    . $bulan[(int) date('n', $timestamp)] . ' '
    . date('Y H:i', $timestamp) . ' WIB';

// Warna chart per status
$warna_status = array(
    'DO'  => '#FF1744',
    'LTM' => '#FF9100',
    'BPB' => '#00B0FF',
);

// Build tabel rows + warna peta
$body = '';

foreach ($maps_all as $i => $desa) {
    $kdwil      = $desa['data']['id2012'];
    $total_ats  = 0;
    $jumlah_do      = 0;
    $jumlah_ltm     = 0;
    $jumlah_bpb     = 0;
    $usia_sampai_15 = 0;
    $usia_diatas_15 = 0;
    $jumlah_l       = 0;
    $jumlah_p       = 0;
    $provinsi   = $desa['data']['provinsi'];
    $kab_kot    = $desa['data']['kab_kot'];

    $chart_ats = array('label' => array(), 'data' => array(), 'color' => array());

    if ($kdwil !== '' && !empty($ats_wilayah[$kdwil])) {
        $w          = $ats_wilayah[$kdwil];
        $total_ats  = (int) $w['total'];
        $jumlah_do    = (int) $w['DO'];
        $jumlah_ltm   = (int) $w['LTM'];
        $jumlah_bpb   = (int) $w['BPB'];
        $usia_sampai_15  = (int) $w['usia_sampai_15'];
        $usia_diatas_15 = (int) $w['usia_diatas_15'];
        $jumlah_l     = (int) $w['L'];
        $jumlah_p     = (int) $w['P'];
        $provinsi   = $w['provinsi'] ?: $provinsi;
        $kab_kot    = $w['kab_kot']  ?: $kab_kot;

        // Chart doughnut — breakdown DO/LTM/BPB
        $total_ref = (int) $w['total'];
        foreach (array('DO', 'LTM', 'BPB') as $st) {
            $jml = (int) $w[$st];
            if ($jml <= 0) continue;
            $persen = $total_ref > 0 ? round(($jml / $total_ref) * 100, 1) : 0;
            $chart_ats['label'][] = str_pad($st, 5) . str_pad($jml . ' anak', 10) . $persen . '%';
            $chart_ats['data'][]  = $jml;
            $chart_ats['color'][] = $warna_status[$st];
        }
    }

    // Warna peta berdasarkan total ATS
    if ($total_ats < 10) {
        $maps_all[$i]['color'] = '#0cbf00'; // hijau
    } elseif ($total_ats <= 30) {
        $maps_all[$i]['color'] = '#fff70a'; // kuning
    } else {
        $maps_all[$i]['color'] = '#ff0000'; // merah
    }

    $maps_all[$i]['index']         = $i;
    $maps_all[$i]['chart']         = $chart_ats;
    $maps_all[$i]['chart']['type'] = 'doughnut';
    $maps_all[$i]['html']          = '
        <div class="container counting-inner">
            <div class="row counting-box title-row">
                <div class="col-md-12 text-center animated">
                    <div style="max-width:500px; margin:auto;">
                        <h3>' . $desa['data']['desa'] . '</h3>
                        <p>
                            Kec. ' . $desa['data']['kecamatan'] . '<br>
                            Kab. ' . $desa['data']['kab_kot'] . '<br>
                            Prov. ' . $desa['data']['provinsi'] . '
                        </p>
                        <p><b>Total ATS : ' . $this->number_format($total_ats) . ' Anak</b></p>
                        ' . (
                            $total_ats > 0
                            ? '<canvas id="chart-' . $maps_all[$i]['index'] . '" height="300"></canvas>'
                            : '<div class="alert alert-warning">Data ATS tidak tersedia.</div>'
                        ) . '
                    </div>
                </div>
            </div>
        </div>
    ';

    $search = $this->getSearchLocation($desa['data']);

    $nama_desa = $desa['data']['desa'];
    $is_admin = false;
    
    if ( is_user_logged_in() ) {
        $user_id = um_user('ID');
        $user_meta = get_userdata($user_id);
        if (in_array("administrator", $user_meta->roles)) {
            $is_admin = true;
        }
        if (in_array("administrator", $user_meta->roles)){
            $detail_url = add_query_arg(
                'id_wilayah',
                $kdwil,
                $data_ats['url']);
            $nama_desa = "<a href='" . $detail_url . "' target='_blank' rel='noopener noreferrer'>" . $desa['data']['desa'] . "</a>";
        }
    }

    $body .= "
        <tr>
            <td class='text-center'>" . $kdwil . "</td>
            <td class='text-center'>" . $provinsi . "</td>
            <td class='text-center'>" . $kab_kot . "</td>
            <td class='text-center'>" . $desa['data']['kecamatan'] . "</td>
            <td class='text-center'>" .  $nama_desa . "</td>
            <td class='text-center'>" . $this->number_format($total_ats) . "</td>
            <td class='text-center'>" . $this->number_format($jumlah_do) . "</td>
            <td class='text-center'>" . $this->number_format($jumlah_ltm) . "</td>
            <td class='text-center'>" . $this->number_format($jumlah_bpb) . "</td>
            <td class='text-center'>" . $this->number_format($usia_sampai_15) . "</td>
            <td class='text-center'>" . $this->number_format($usia_diatas_15) . "</td>
            <td class='text-center'>" . $this->number_format($jumlah_l) . "</td>
            <td class='text-center'>" . $this->number_format($jumlah_p) . "</td>
            <td class='text-center'>
                <a
                    onclick='cari_alamat(\"" . $search . "\"); return false;'
                    href='#'
                    class='btn btn-danger btn-sm'>
                    Map
                </a>
            </td>
        </tr>
    ";
}
?>
<style>
    #table-data th:last-child,
    #table-data td:last-child {
        width: 70px !important;
    }
    #table-data .btn {
        padding: 4px 8px;
        font-size: 12px;
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>Chart.register(ChartDataLabels);</script>

<h1 class="text-center">Peta Sebaran ATS (Anak Tidak Sekolah)<br><?php echo $this->getNamaDaerah(); ?></h1>
<div id="wrap-action"></div>

<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px; padding-top: 30px;">
    <button
        id="btn-label-map"
        style="margin: 15px 0;"
        class="btn btn-sm btn-primary"
        onclick="toggleLabelMap();">
        Hidupkan Label
    </button>
    <div id="map-canvas" style="width: 100%; height: 400px;"></div>

    <h3 style="margin-top: 20px;">Keterangan Warna Peta</h3>
    <ol>
        <li>Warna <b style="color:#0cbf00;">hijau</b> — jumlah ATS kurang dari 10 anak.</li>
        <li>Warna <b style="color:#b8a800;">kuning</b> — jumlah ATS antara 10 sampai 30 anak.</li>
        <li>Warna <b style="color:#ff0000;">merah</b> — jumlah ATS lebih dari 30 anak.</li>
    </ol>

    <h2 class="text-center">
        Tabel Data ATS<br>
        Total ATS: <b><?php echo $this->number_format($total_ats_filtered); ?> Anak</b>
        <p class="text-center" style="margin-top:10px; margin-bottom:20px;">
            Update Terakhir: <b><?php echo $last_update_text; ?></b>
        </p>
    </h2>

    <div style="width: 100%; overflow: auto; height: 100vh;">
        <table class="table table-bordered" id="table-data">
            <thead>
                <tr>
                    <th class="text-center">Id Wilayah</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kabupaten/Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Desa</th>
                    <th class="text-center">Total ATS</th>
                    <th class="text-center">DO</th>
                    <th class="text-center">LTM</th>
                    <th class="text-center">BPB</th>
                    <th class="text-center">Usia <= 15th</th>
                    <th class="text-center">Usia > 15th</th>
                    <th class="text-center">Laki-Laki</th>
                    <th class="text-center">Perempuan</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $body; ?>
            </tbody>
        </table>
    </div>
</div>

<script type="text/javascript">
    window.maps_all    = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;

    jQuery('#table-data').dataTable({
        lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
        order: [[5, 'desc']]  // kolom ke-5 = Total ATS
    });

    window.labelMapAktif = false;

    function toggleLabelMap() {
        if (window.labelMapAktif) {
            map.setOptions({
                styles: [{
                    featureType: "all",
                    elementType: "labels",
                    stylers: [{ visibility: "off" }]
                }]
            });
            jQuery('#btn-label-map').text('Hidupkan Label');
            window.labelMapAktif = false;
        } else {
            map.setOptions({ styles: [] });
            jQuery('#btn-label-map').text('Matikan Label');
            window.labelMapAktif = true;
        }
    }
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>
