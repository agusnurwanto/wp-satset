<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}
if (!empty($_GET) && !empty($_GET['tahun_anggaran'])) {
    $tahun_anggaran = $_GET['tahun_anggaran'];
} else {
    $tahun_anggaran = get_option('_crb_tahun_satset');
}

$data_dtsen = $this->functions->generatePage(array(
    'nama_page' => 'Data Detail DTSEN', 
    'content' => '[data_detail_dtsen]',
    'show_header' => 1,
    'post_status' => 'private'
));

$center = $this->get_center();
$maps_all = $this->get_polygon();
if (!empty($_GET['desil'])) {
    $desil = (int) $_GET['desil'];
} else {
    $desil = 1;
}

$dtsen = $wpdb->get_results(
    "
    SELECT
        id_wilayah,
        desil_nasional,
        TRIM(kecamatan) AS kecamatan,
        TRIM(kelurahan) AS kelurahan
    FROM data_dtsen_satset
    WHERE active = 1
    ",
    ARRAY_A
);

$dtsen_wilayah = array();
$desil_totals = array();

for ($i = 1; $i <= 10; $i++) {
    $desil_totals[$i] = 0;
}
$total_dtsen_all = 0;

foreach ($dtsen as $row) {
    $id_wilayah = trim($row['id_wilayah']);

    if ($id_wilayah === '') {
        continue;
    }
    $desil_row = (int) $row['desil_nasional'];

    if ($id_wilayah <= 0) {
        continue;
    }

    if (empty($dtsen_wilayah[$id_wilayah])) {
        $dtsen_wilayah[$id_wilayah] = array(
            'total' => 0
        );

        for ($i = 1; $i <= 10; $i++) {
            $dtsen_wilayah[$id_wilayah][$i] = 0;
        }
    }

    $dtsen_wilayah[$id_wilayah]['total']++;
    $total_dtsen_all++;

    if ($desil_row >= 1 && $desil_row <= 10) {
        $dtsen_wilayah[$id_wilayah][$desil_row]++;
        $desil_totals[$desil_row]++;
    }
}

$mapping_wilayah = array();

foreach ($dtsen as $row) {
    $key = strtolower(trim($row['kecamatan']))
        . '|'
        . strtolower(trim($row['kelurahan']));

    $mapping_wilayah[$key] = $row['id_wilayah'];
}

$selected_desil_total = !empty($desil_totals[$desil]) ? $desil_totals[$desil] : 0;
$desil_header = 'Total Desil ' . $desil;
$body = '';

foreach ($maps_all as $i => $desa) {
    $key = strtolower(trim($desa['data']['kecamatan']))
        . '|'
        . strtolower(trim($desa['data']['desa']));

    $id_wilayah = $mapping_wilayah[$key] ?? '';

    $total_dtsen = 0;
    $total_desil = 0;
    $chart_dtsen = array(
        'label' => array(),
        'data' => array(),
        'color' => array()
    );

    $warna_desil = array(
        '#FF1744', // desil 1
        '#FF9100', // desil 2   
        '#00E676', // desil 3
        '#FFEA00', // desil 4
        '#00B0FF', // desil 5
        '#D500F9', // desil 6
        '#F59E0B', // Desil 7
        '#F97316', // Desil 8
        '#EF4444', // Desil 9
        '#991B1B'  // Desil 10
    );
    if ($id_wilayah !== '' && !empty($dtsen_wilayah[$id_wilayah])) {
        $total_dtsen = (int) $dtsen_wilayah[$id_wilayah]['total'];
        $total_desil = isset($dtsen_wilayah[$id_wilayah][$desil])
            ? (int) $dtsen_wilayah[$id_wilayah][$desil]
            : 0;
    }
    for ($x = 1; $x <= 10; $x++) {
        $jumlah = (int) ($dtsen_wilayah[$id_wilayah][$x] ?? 0);

        if ($jumlah <= 0) {
            continue;
        }

        $persen = $total_dtsen > 0
            ? round(($jumlah / $total_dtsen) * 100, 2)
            : 0;

        $chart_dtsen['label'][] =
            str_pad('Desil '.$x, 10).
            str_pad($this->number_format($jumlah).' org', 12).
            $persen.'%';
        $chart_dtsen['data'][] = $jumlah;
        $chart_dtsen['color'][] = $warna_desil[$x - 1];
    }

    if ($desil >= 1 && $desil <= 4) {
        if ($total_desil < 100) {
            $maps_all[$i]['color'] = '#0cbf00';
        } else if ($total_desil <= 200) {
            $maps_all[$i]['color'] = '#fff70a';
        } else {
            $maps_all[$i]['color'] = '#ff0000';
        }
    } else {
        $maps_all[$i]['color'] = '#0cbf00';
    }

    $maps_all[$i]['index'] = $i;
    $maps_all[$i]['chart'] = $chart_dtsen;
    $maps_all[$i]['chart']['type'] = 'doughnut';
    $maps_all[$i]['html'] = '
        <div class="container counting-inner">
            <div class="row counting-box title-row">
                <div class="col-md-12 text-center animated">
                    <div style="max-width:500px; margin:auto;">
                        <h3>'.$desa['data']['desa'].'</h3>
                        <p>
                            Kec. '.$desa['data']['kecamatan'].'<br>
                            Kab. '.$desa['data']['kab_kot'].'<br>
                            Prov. '.$desa['data']['provinsi'].'
                        </p>

                        <p><b>Total DTSEN : '.$this->number_format($total_dtsen).' Keluarga</b></p>

                        '.(
                            $total_dtsen > 0
                            ? '<canvas id="chart-'.$maps_all[$i]['index'].'" height="300"></canvas>'
                            : '<div class="alert alert-warning">Data DTSEN tidak tersedia.</div>'
                        ).'

                    </div>
                </div>
            </div>
        </div>
    ';

    $search = $this->getSearchLocation($desa['data']);

    $nama_desa = $desa['data']['desa'];
    $is_admin = false;
    $user_id = um_user('ID');
    $user_meta = get_userdata($user_id);
    if (in_array("administrator", $user_meta->roles)) {
        $is_admin = true;
    }
    if (in_array("administrator", $user_meta->roles)){
        $detail_url = add_query_arg(
            'id_wilayah',
            $id_wilayah,
            $data_dtsen['url']);
        $nama_desa = "<a href='" . $detail_url . "' target='_blank' rel='noopener noreferrer'>" . $desa['data']['desa'] . "</a>";
    }
    $body .= "
        <tr>
            <td class='text-center'>" . $id_wilayah . "</td>
            <td class='text-center'>" . $desa['data']['provinsi'] . "</td>
            <td class='text-center'>" . $desa['data']['kab_kot'] . "</td>
            <td class='text-center'>" . $desa['data']['kecamatan'] . "</td>
            <td class='text-center'>
                ".$nama_desa."
            </td>
            <td class='text-center'>" . $this->number_format($total_dtsen) . "</td>
            <td class='text-center'>" . $this->number_format($total_desil) . "</td>
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

$tahun = $wpdb->get_results('
        SELECT 
            tahun_anggaran 
        from satset_data_unit
        group by tahun_anggaran 
        order by tahun_anggaran ASC
    ', ARRAY_A);
    $select_tahun = "";
foreach($tahun as $tahun_value){
    $select = $tahun_value['tahun_anggaran'] == $tahun_anggaran ? 'selected' : '';
    $select_tahun .= "<option value='".$tahun_value['tahun_anggaran']."' ".$select.">".$tahun_value['tahun_anggaran']."</option>";
}

$last_update = $wpdb->get_var("
    SELECT MAX(update_at)
    FROM data_dtsen_satset
");
$bulan = array(
    1 => 'Januari',
    'Februari',
    'Maret',
    'April',
    'Mei',
    'Juni',
    'Juli',
    'Agustus',
    'September',
    'Oktober',
    'November',
    'Desember'
);

$timestamp = strtotime($last_update);

$last_update_text =
    date('d', $timestamp).' '.
    $bulan[(int)date('n', $timestamp)].' '.
    date('Y H:i', $timestamp).' WIB';
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
<script>
    Chart.register(ChartDataLabels);
</script>
<h1 class="text-center">Peta Sebaran DTSEN (Data Tunggal Sosial Ekonomi Nasional )<br><?php echo $this->getNamaDaerah(); ?></h1>
<div id="wrap-action"></div>
    <div class="text-center" style="margin-top: 30px;">
        <div class="text-center" style="margin-top: 30px;">
            <div>
                <label style="margin-left: 10px;" for="tahun_anggaran">
                    Tahun Anggaran :
                </label>
                <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
                    <?php echo $select_tahun; ?>
                </select>

                <button
                    style="margin-left: 10px; height: 45px; width: 75px;"
                    onclick="sumbitTahun();"
                    class="btn btn-sm btn-primary">
                    Cari
                </button>
            </div>

            <div style="margin-top: 15px;">
                <label style="margin-right: 20px;">
                    <input
                        type="radio"
                        name="desil"
                        value="1"
                        onchange="submitDesilFilter(this.value)"
                        <?php echo ($desil == 1 ? 'checked' : ''); ?>
                    >
                    Desil 1
                </label>

                <label style="margin-right: 20px;">
                    <input
                        type="radio"
                        name="desil"
                        value="2"
                        onchange="submitDesilFilter(this.value)"
                        <?php echo ($desil == 2 ? 'checked' : ''); ?>
                    >
                    Desil 2
                </label>

                <label style="margin-right: 20px;">
                    <input
                        type="radio"
                        name="desil"
                        value="3"
                        onchange="submitDesilFilter(this.value)"
                        <?php echo ($desil == 3 ? 'checked' : ''); ?>
                    >
                    Desil 3
                </label>

                <label>
                    <input
                        type="radio"
                        name="desil"
                        value="4"
                        onchange="submitDesilFilter(this.value)"
                        <?php echo ($desil == 4 ? 'checked' : ''); ?>
                    >
                    Desil 4
                </label>
            </div>
        </div>
    </div>
</div>
<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px; padding-top: 50px;">
    <button
        id="btn-label-map"
        style="margin: 15px 0;"
        class="btn btn-sm btn-primary"
        onclick="toggleLabelMap();">
        Hidupkan Label
    </button>
    <div id="map-canvas" style="width: 100%; height: 400px;"></div>
    <h3 style="margin-top: 20px;">Keterangan</h3>
    <ol>
        <li>Warna hijau berarti jumlah DTSEN kurang dari 100 Keluarga.</li>
        <li>Warna kuning berarti jumlah DTSEN antara 100 sampai 200 Keluarga.</li>
        <li>Warna merah berarti jumlah DTSEN lebih dari 200 Keluarga.</li>
    </ol>
    <h2 class="text-center">
        Tabel Data DTSEN<br>
        Total DTSEN: <b><?php echo $this->number_format($total_dtsen_all); ?> Keluarga</b><br>
        <?php echo $desil_header; ?>: <b><?php echo $this->number_format($selected_desil_total); ?> Keluarga</b>
        <p class="text-center" style="margin-top:10px; margin-bottom:20px;">
            Update Terakhir: <b><?php echo $last_update_text; ?></b>
        </p>
    </h2>
    <div style="width: 100%; overflow: auto; height: 100vh;">
        <table class="table table-bordered" id="table-data">
            <thead>
                <tr>
                    <th class='text-center'>Id Wilayah</th>
                    <th class='text-center'>Provinsi</th>
                    <th class='text-center'>Kabupaten/Kota</th>
                    <th class='text-center'>Kecamatan</th>
                    <th class='text-center'>Desa</th>
                    <th class='text-center'>Total DTSEN</th>
                    <th class='text-center'><?php echo $desil_header; ?></th>
                    <th class='text-center'>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php echo $body; ?>
            </tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
    window.maps_all = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;
    jQuery('#table-data').dataTable({
        lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
        order: [[5, 'desc']]
    });
    function submitDesilFilter(selectedDesil){
        var params = new URLSearchParams(window.location.search);
        params.set('desil', selectedDesil);

        var tahunAnggaran = jQuery('#tahun_anggaran').val();
        if (tahunAnggaran) {
            params.set('tahun_anggaran', tahunAnggaran);
        }

        window.location.href = window.location.pathname + '?' + params.toString();
    }

    function sumbitTahun(){
        var tahun_anggaran = jQuery('#tahun_anggaran').val();
        if(tahun_anggaran == ''){
            return alert('Tahun tidak boleh kosong!');
        }

        var params = new URLSearchParams(window.location.search);
        params.set('tahun_anggaran', tahun_anggaran);

        var selectedDesil = jQuery('input[name="desil"]:checked').val();
        if (selectedDesil) {
            params.set('desil', selectedDesil);
        }

        location.href = window.location.pathname + '?' + params.toString();
    }

    window.labelMapAktif = false;

    function toggleLabelMap() {
        if (window.labelMapAktif) {
            map.setOptions({
                styles: [
                    {
                        featureType: "all",
                        elementType: "labels",
                        stylers: [
                            { visibility: "off" }
                        ]
                    }
                ]
            });

            jQuery('#btn-label-map').text('Hidupkan Label');
            window.labelMapAktif = false;
        } else {
            map.setOptions({
                styles: []
            });

            jQuery('#btn-label-map').text('Matikan Label');
            window.labelMapAktif = true;
        }
    }
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>