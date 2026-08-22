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
$center = $this->get_center();
$maps_all = $this->get_polygon();
$p3ke_all = $this->get_p3ke();
$dtks_all = $this->get_dtks();

$p3ke_all_desa = array();
foreach($p3ke_all as $data){
    $index = $data['provinsi'].'.'.$data['kabkot'].'.'.$data['kecamatan'].'.'.$data['desa'];
    if(empty($p3ke_all_desa[$index])){
        $p3ke_all_desa[$index] = array();
    }
    if(empty($p3ke_all_desa[$index]['desil_'.$data['jenis_desil']])){
        $p3ke_all_desa[$index]['desil_'.$data['jenis_desil']] = array();
    }
    $p3ke_all_desa[$index]['desil_'.$data['jenis_desil']][] = $data;
}
// print_r($p3ke_all); die();

$dtks_all_desa = array();
foreach($dtks_all as $data){
    $index = $data['provinsi'].'.'.$data['kabkot'].'.'.$data['kecamatan'].'.'.$data['desa'];
    if(empty($dtks_all_desa[$index])){
        $dtks_all_desa[$index] = array();
    }
    $dtks_all_desa[$index][] = $data;
}
// print_r($dtks_all); die();

$total_all_dtks = 0;
$total_all = 0;
$body =  '';
foreach($maps_all as $i => $desa){
    $index = $desa['data']['provinsi'].'.'.$desa['data']['kab_kot'].'.'.$desa['data']['kecamatan'].'.'.$desa['data']['desa'];
    $total_p3ke = 0;
    $ket_desil = array();
    $resiko_stunting = 0;
    $bpnt = 0;
    $bpum = 0;
    $bst = 0;
    $pkh = 0;
    $sembako = 0;
    if(!empty($p3ke_all_desa[$index])){
        foreach($p3ke_all_desa[$index] as $key => $desil){
            $jml_desil = count($desil);
            $total_p3ke += $jml_desil;
            $ket_desil[] = ucwords(str_replace('_', '', $key)).': '.$jml_desil;
            foreach($desil as $keluarga){
                $resiko_stunting += $keluarga['resiko_stunting'];
                if($keluarga['penerima_bpnt'] == 'Ya'){
                    $bpnt++;
                }
                if($keluarga['penerima_bpum'] == 'Ya'){
                    $bpum++;
                }
                if($keluarga['penerima_bst'] == 'Ya'){
                    $bst++;
                }
                if($keluarga['penerima_pkh'] == 'Ya'){
                    $pkh++;
                }
                if($keluarga['penerima_sembako'] == 'Ya'){
                    $sembako++;
                }
            }
        }
    }
    $total_all += $total_p3ke;

    $total_dtks = 0;
    $total_blt = array();
    $total_blt_bbm = array();
    $total_bpnt = array();
    $total_pkh = array();
    $total_pbi = array();
    $total_blt_angka = 0;
    $total_blt_bbm_angka = 0;
    $total_bpnt_angka = 0;
    $total_pkh_angka = 0;
    $total_pbi_angka = 0;
    if(!empty($dtks_all_desa[$index])){
        foreach($dtks_all_desa[$index] as $orang){
            $total_dtks += $orang['jml'];
            if($orang['BLT'] != 'TIDAK'){
                if(empty($total_blt[$orang['BLT']])){
                    $total_blt[$orang['BLT']] = 0;
                }
                $total_blt[$orang['BLT']] += $orang['jml'];
                $total_blt_angka += $orang['jml'];
            }
            if($orang['BLT_BBM'] != 'TIDAK'){
                if(empty($total_blt_bbm[$orang['BLT_BBM']])){
                    $total_blt_bbm[$orang['BLT_BBM']] = 0;
                }
                $total_blt_bbm[$orang['BLT_BBM']] += $orang['jml'];
                $total_blt_bbm_angka += $orang['jml'];
            }
            if($orang['BPNT'] != 'TIDAK'){
                if(empty($total_bpnt[$orang['BPNT']])){
                    $total_bpnt[$orang['BPNT']] = 0;
                }
                $total_bpnt[$orang['BPNT']] += $orang['jml'];
                $total_bpnt_angka += $orang['jml'];
            }
            if($orang['PKH'] != 'TIDAK'){
                if(empty($total_pkh[$orang['PKH']])){
                    $total_pkh[$orang['PKH']] = 0;
                }
                $total_pkh[$orang['PKH']] += $orang['jml'];
                $total_pkh_angka += $orang['jml'];
            }
            if($orang['PBI'] != 'TIDAK'){
                if(empty($total_pbi[$orang['PBI']])){
                    $total_pbi[$orang['PBI']] = 0;
                }
                $total_pbi[$orang['PBI']] += $orang['jml'];
                $total_pbi_angka += $orang['jml'];
            }
        }
        foreach($total_blt as $key => $data){
            $total_blt[$key] = $key.': '.$data;
        }
        foreach($total_blt_bbm as $key => $data){
            $total_blt_bbm[$key] = $key.': '.$data;
        }
        foreach($total_bpnt as $key => $data){
            $total_bpnt[$key] = $key.': '.$data;
        }
        foreach($total_pkh as $key => $data){
            $total_pkh[$key] = $key.': '.$data;
        }
        foreach($total_pbi as $key => $data){
            $total_pbi[$key] = $key.': '.$data;
        }
    }
    $total_all_dtks += $total_dtks;

    if($total_p3ke <= 50){
        $maps_all[$i]['color'] = '#0cbf00';
    }else if($total_p3ke <= 150){
        $maps_all[$i]['color'] = '#fff70a';
    }else if($total_p3ke > 150){
        $maps_all[$i]['color'] = '#ff0000';
    }
    $chart = array(
        'label' => array('BPNT', 'BPUM', 'BST', 'PKH', 'SEMBAKO', 'STUNTING'),
        'data' => array($bpnt, $bpum, $bst, $pkh, $sembako, $resiko_stunting),
        'color' => array('#4deeea', '#74ee15', '#ffe700', '#f000ff', '#001eff', '#dd9944')
    );
    $maps_all[$i]['chart'] = $chart;
    $chart_dtks = array(
        'label' => array('BLT', 'BLT BBM', 'BPNT', 'PKH', 'PBI'),
        'data' => array($total_blt_angka, $total_blt_bbm_angka, $total_bpnt_angka, $total_pkh_angka, $total_pbi_angka),
        'color' => array('#4deeea', '#74ee15', '#ffe700', '#f000ff', '#001eff')
    );
    $maps_all[$i]['chart_dtks'] = $chart_dtks;
    $maps_all[$i]['index'] = $i;
    $maps_all[$i]['html'] = '
        <div class="container counting-inner">
            <div class="row counting-box title-row">
                <div class="col-md-12 text-center animated">
                    <div style="max-width: 500px; margin:auto;">
                        <h3>'.$desa['data']['desa'].' Kec. '.$desa['data']['kecamatan'].'</h3>
                        <h4>Jumlah P3KE '.$total_p3ke.' keluarga</h4>
                        <canvas id="chart-'.$i.'"></canvas>
                        <hr>
                        <h4>Jumlah DTKS '.$total_dtks.' orang</h4>
                        <canvas id="chart-dtks-'.$i.'"></canvas>
                    </div>
                </div>
            </div>
        </div>
    ';
    $search = $this->getSearchLocation($desa['data']);
    $body .= "
        <tr>
            <td class='text-center'>".$desa['data']['id2012']."</td>
            <td class='text-center'>".$desa['data']['provinsi']."</td>
            <td class='text-center'>".$desa['data']['kab_kot']."</td>
            <td class='text-center'>".$desa['data']['kecamatan']."</td>
            <td class='text-center'>".$desa['data']['desa']."</td>
            <td class='text-center'>".$total_p3ke."</td>
            <td>".implode('<br>', $ket_desil)."</td>
            <td class='text-center'>".$resiko_stunting."</td>
            <td class='text-center'>".$bpnt."</td>
            <td class='text-center'>".$bpum."</td>
            <td class='text-center'>".$bst."</td>
            <td class='text-center'>".$pkh."</td>
            <td class='text-center'>".$sembako."</td>
            <td class='text-center'>".$total_dtks."</td>
            <td>".implode('<hr>', $total_blt)."</td>
            <td>".implode('<hr>', $total_blt_bbm)."</td>
            <td>".implode('<hr>', $total_bpnt)."</td>
            <td>".implode('<hr>', $total_pkh)."</td>
            <td>".implode('<hr>', $total_pbi)."</td>
            <td class='text-center'><a style='margin-bottom: 5px;' onclick='cari_alamat(\"".$search."\"); return false;' href='#' class='btn btn-danger'>Map</a></td>
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
?>
<h1 class="text-center">Peta Data Terpadu dan Terintegrasi<br><?php echo $this->getNamaDaerah(); ?></h1>
<div id="wrap-action"></div>
    <div class="text-center" style="margin-top: 30px;">
        <label style="margin-left: 10px;" for="tahun_anggaran">Tahun Anggaran : </label>
        <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
            <?php echo $select_tahun; ?>
        </select>
        <button style="margin-left: 10px; height: 45px; width: 75px;"onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
    </div>
</div>
<div style="width: 95%; margin: 0 auto; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 90vh;"></div>
    <h3 style="margin-top: 20px;">Keterangan</h3>
    <ol>
        <li>Warna hijau berarti jumlah P3KE antara 0 sampai 50 keluarga</li>
        <li>Warna kuning berarti jumlah P3KE antara 51 sampai 150 keluarga</li>
        <li>Warna merah berarti jumlah P3KE diatas 150 keluarga</li>
    </ol>
    <h2 class="text-center">Tabel Data Irisan<br>P3KE (<?php echo $this->number_format($total_all); ?> Keluarga)<br>DTKS (<?php echo $this->number_format($total_all_dtks); ?> Penerima)</h1>
    <div style="width: 100%; overflow: auto; height: 100vh;">
        <table class="table table-bordered" id="table-data" style="width:3000px;">
            <thead>
                <tr>
                    <th class='text-center' rowspan="2">Kode Desa</th>
                    <th class='text-center' rowspan="2">Provinsi</th>
                    <th class='text-center' rowspan="2">Kabupaten/Kota</th>
                    <th class='text-center' rowspan="2">Kecamatan</th>
                    <th class='text-center' rowspan="2">Desa</th>
                    <th class='text-center' colspan="8">P3KE</th>
                    <th class='text-center' colspan="6">DTKS</th>
                    <th class='text-center' rowspan="2">Aksi</th>
                </tr>
                <tr>
                    <th class='text-center'>Jumlah P3KE</th>
                    <th class='text-center'>Keterangan</th>
                    <th class='text-center'>Resiko Stunting</th>
                    <th class='text-center'>Penerima BPNT</th>
                    <th class='text-center'>Penerima BPUM</th>
                    <th class='text-center'>Penerima BST</th>
                    <th class='text-center'>Penerima PKH</th>
                    <th class='text-center'>Penerima SEMBAKO</th>
                    <th class='text-center'>Total DTKS</th>
                    <th class='text-center'>BLT</th>
                    <th class='text-center' style="width:300px;">BLT BBM</th>
                    <th class='text-center' style="width:300px;">BPNT</th>
                    <th class='text-center' style="width:300px;">PKH</th>
                    <th class='text-center' style="width:300px;">PBI</th>
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
    function sumbitTahun(){
        var tahun_anggaran = jQuery('#tahun_anggaran').val();
        if(tahun_anggaran == ''){
            return alert('Tahun tidak boleh kosong!');
        }
        var url = window.location.href;
        url = url.split('?')[0]+'?tahun_anggaran='+tahun_anggaran;
        location.href = url;
    }
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>

<!-- untuk edit datalabels chart.js -->
<script src="<?php echo SATSET_PLUGIN_URL . '/public/js/chart-plugin-datalabels.min.js'; ?>"></script>