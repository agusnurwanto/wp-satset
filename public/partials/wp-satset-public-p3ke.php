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
    if($total_p3ke <= 50){
        $maps_all[$i]['color'] = '#0cbf00';
    }else if($total_p3ke <= 150){
        $maps_all[$i]['color'] = '#fff70a';
    }else if($total_p3ke > 150){
        $maps_all[$i]['color'] = '#ff0000';
    }
    $maps_all[$i]['index'] = $i;
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
    $total_all += $total_p3ke;
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
<h1 class="text-center">Peta Sebaran P3KE (Pensasaran Percepatan Penghapusan Kemiskinan Ekstrem)<br><?php echo $this->getNamaDaerah(); ?></h1>
<div id="wrap-action"></div>
    <div class="text-center" style="margin-top: 30px;">
        <label style="margin-left: 10px;" for="tahun_anggaran">Tahun Anggaran : </label>
        <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
            <?php echo $select_tahun; ?>
        </select>
        <button style="margin-left: 10px; height: 45px; width: 75px;"onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
    </div>
</div>
<div style="width: 95%; margin: 0 auto; padding-bottom: 75px; padding-top: 75px; overflow: auto;">
    <div id="map-canvas" style="width: 100%; height: 90vh;"></div>
    <h3 style="margin-top: 20px;">Keterangan</h3>
    <ol>
        <li>Warna hijau berarti jumlah P3KE antara 0 sampai 50 keluarga</li>
        <li>Warna kuning berarti jumlah P3KE antara 51 sampai 150 keluarga</li>
        <li>Warna merah berarti jumlah P3KE diatas 150 keluarga</li>
    </ol>
    <h2 class="text-center">Tabel Data P3KE<br>Total <?php echo $this->number_format($total_all); ?> Keluarga</h1>
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
                    <th class='text-center'>Resiko Stuning</th>
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