<?php
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
    if(!empty($dtks_all_desa[$index])){
        foreach($dtks_all_desa[$index] as $orang){
            $total_dtks += $orang['jml'];
            if($orang['BLT'] != 'TIDAK'){
                if(empty($total_blt[$orang['BLT']])){
                    $total_blt[$orang['BLT']] = 0;
                }
                $total_blt[$orang['BLT']] += $orang['jml'];
            }
            if($orang['BLT_BBM'] != 'TIDAK'){
                if(empty($total_blt_bbm[$orang['BLT_BBM']])){
                    $total_blt_bbm[$orang['BLT_BBM']] = 0;
                }
                $total_blt_bbm[$orang['BLT_BBM']] += $orang['jml'];
            }
            if($orang['BPNT'] != 'TIDAK'){
                if(empty($total_bpnt[$orang['BPNT']])){
                    $total_bpnt[$orang['BPNT']] = 0;
                }
                $total_bpnt[$orang['BPNT']] += $orang['jml'];
            }
            if($orang['PKH'] != 'TIDAK'){
                if(empty($total_pkh[$orang['PKH']])){
                    $total_pkh[$orang['PKH']] = 0;
                }
                $total_pkh[$orang['PKH']] += $orang['jml'];
            }
            if($orang['PBI'] != 'TIDAK'){
                if(empty($total_pbi[$orang['PBI']])){
                    $total_pbi[$orang['PBI']] = 0;
                }
                $total_pbi[$orang['PBI']] += $orang['jml'];
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
        'label' => array(array('BPNT'), array('BPUM'), array('BST'), array('PKH'), array('SEMBAKO'), array('STUNTING')),
        'data' => array(array($bpnt), array($bpum), array($bst), array($pkh), array($sembako), array($resiko_stunting)),
        'color' => array(array('#4deeea'), array('#74ee15'), array('#ffe700'), array('#f000ff'), array('#001eff'), array('#dd9944'))
    );
    $maps_all[$i]['chart'] = $chart;
    $maps_all[$i]['index'] = $i;
    $maps_all[$i]['html'] = '
        <div class="container counting-inner">
            <div class="row counting-box title-row">
                <div class="col-md-12 text-center animated">
                    <div style="max-width: 500px; margin:auto;">
                        <h4>'.$desa['data']['desa'].' Kec. '.$desa['data']['kecamatan'].'<br>Jumlah P3KE '.$total_p3ke.' keluarga</h4>
                        <canvas id="chart-'.$i.'"></canvas>
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
            <td class='text-center'><a style='margin-bottom: 5px;' onclick='cari_alamat(\"".$search."\"); return false;' href='#' class='btn btn-danger'>Map</a></td>
        </tr>
    ";
}
?>
<h1 class="text-center">Peta Data Terpadu dan Terintegrasi<br><?php echo $this->getNamaDaerah(); ?></h1>
<div style="width: 95%; margin: 0 auto; padding-bottom: 75px; overflow: auto;">
    <div id="map-canvas" style="width: 100%; height: 90vh;"></div>
    <h3 style="margin-top: 20px;">Keterangan</h3>
    <ol>
        <li>Warna hijau berarti jumlah P3KE antara 0 sampai 50 keluarga</li>
        <li>Warna kuning berarti jumlah P3KE antara 51 sampai 150 keluarga</li>
        <li>Warna merah berarti jumlah P3KE diatas 150 keluarga</li>
    </ol>
    <h2 class="text-center">Tabel Data Irisan<br>P3KE (<?php echo $this->number_format($total_all); ?> Keluarga)<br>DTKS (<?php echo $this->number_format($total_all_dtks); ?> Penerima)</h1>
    <div style="width: 100%; overflow: auto; height: 100vh;">
        <table class="table table-bordered" id="table-data">
            <thead>
                <tr>
                    <th class='text-center'>Kode Desa</th>
                    <th class='text-center'>Provinsi</th>
                    <th class='text-center'>Kabupaten/Kota</th>
                    <th class='text-center'>Kecamatan</th>
                    <th class='text-center'>Desa</th>
                    <th class='text-center'>Jumlah P3KE</th>
                    <th class='text-center'>Keterangan</th>
                    <th class='text-center'>Resiko Stunting</th>
                    <th class='text-center'>Penerima BPNT</th>
                    <th class='text-center'>Penerima BPUM</th>
                    <th class='text-center'>Penerima BST</th>
                    <th class='text-center'>Penerima PKH</th>
                    <th class='text-center'>Penerima SEMBAKO</th>
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
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>