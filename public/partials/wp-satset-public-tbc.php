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
$tbc_all = $this->get_tbc();

$tbc_all_desa = array();
foreach($tbc_all as $data){
    $index = $data['provinsi'].'.'.$data['kabkot'].'.'.$data['kecamatan'].'.'.$data['desa'];
    if(empty($tbc_all_desa[$index])){
        $tbc_all_desa[$index] = array();
    }
    $tbc_all_desa[$index][] = $data;
}
// print_r($tbc_all_desa); die();

$total_all = 0;
$body =  '';
foreach($maps_all as $i => $desa){
    $maps_all[$i]['index'] = $i;
    $index = $desa['data']['provinsi'].'.'.$desa['data']['kab_kot'].'.'.$desa['data']['kecamatan'].'.'.$desa['data']['desa'];
    $total_tbc = 0;
    $total_jk = array();
    $total_umur = array();
    $total_tindak_lanjut = array();
    $total_hasil_akhir = array();
    $total_status_pengobatan = array();
    if(!empty($tbc_all_desa[$index])){
        $total_tbc = count($tbc_all_desa[$index]);
        foreach($tbc_all_desa[$index] as $orang){
            if(empty($total_jk[$orang['jenis_kelamin']])){
                $total_jk[$orang['jenis_kelamin']] = 0;
            }
            $total_jk[$orang['jenis_kelamin']]++;
            if(empty($total_umur[$orang['umur']])){
                $total_umur[$orang['umur']] = 0;
            }
            $total_umur[$orang['umur']]++;
            if(empty($total_tindak_lanjut[$orang['tindak_lanjut']])){
                $total_tindak_lanjut[$orang['tindak_lanjut']] = 0;
            }
            $total_tindak_lanjut[$orang['tindak_lanjut']]++;
            if(empty($total_hasil_akhir[$orang['hasil_akhir_pengobatan']])){
                $total_hasil_akhir[$orang['hasil_akhir_pengobatan']] = 0;
            }
            $total_hasil_akhir[$orang['hasil_akhir_pengobatan']]++;
            if(empty($total_status_pengobatan[$orang['status_pengobatan']])){
                $total_status_pengobatan[$orang['status_pengobatan']] = 0;
            }
            $total_status_pengobatan[$orang['status_pengobatan']]++;
        }
        foreach($total_jk as $key => $data){
            $total_jk[$key] = $key.': '.$data;
        }
        foreach($total_umur as $key => $data){
            $total_umur[$key] = $key.': '.$data;
        }
        foreach($total_tindak_lanjut as $key => $data){
            $total_tindak_lanjut[$key] = $key.': '.$data;
        }
        foreach($total_hasil_akhir as $key => $data){
            $total_hasil_akhir[$key] = $key.': '.$data;
        }
        foreach($total_status_pengobatan as $key => $data){
            $total_status_pengobatan[$key] = $key.': '.$data;
        }
    }
    if($total_tbc <= 5){
        $maps_all[$i]['color'] = '#0cbf00';
    }else if($total_tbc <= 20){
        $maps_all[$i]['color'] = '#fff70a';
    }else if($total_tbc > 20){
        $maps_all[$i]['color'] = '#ff0000';
    }
    $search = $this->getSearchLocation($desa['data']);
    $body .= "
        <tr>
            <td class='text-center'>".$desa['data']['id2012']."</td>
            <td class='text-center'>".$desa['data']['provinsi']."</td>
            <td class='text-center'>".$desa['data']['kab_kot']."</td>
            <td class='text-center'>".$desa['data']['kecamatan']."</td>
            <td class='text-center'>".$desa['data']['desa']."</td>
            <td class='text-center'>".$total_tbc."</td>
            <td>".implode('<br>', $total_jk)."</td>
            <td>".implode('<br>', $total_umur)."</td>
            <td>".implode('<br>', $total_tindak_lanjut)."</td>
            <td>".implode('<br>', $total_hasil_akhir)."</td>
            <td>".implode('<br>', $total_status_pengobatan)."</td>
            <td class='text-center'><a style='margin-bottom: 5px;' onclick='cari_alamat(\"".$search."\"); return false;' href='#' class='btn btn-danger'>Map</a></td>
        </tr>
    ";
    $total_all += $total_tbc;
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
<h1 class="text-center">Peta Sebaran TBC<br><?php echo $this->getNamaDaerah(); ?></h1>
<div id="wrap-action"></div>
    <div class="text-center" style="margin-top: 30px;">
        <label style="margin-left: 10px;" for="tahun_anggaran">Tahun Anggaran : </label>
        <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
            <?php echo $select_tahun; ?>
        </select>
        <button style="margin-left: 10px; height: 45px; width: 75px;"onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
    </div>
</div>
<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 400px;"></div>
    <h3 style="margin-top: 20px;">Keterangan</h3>
    <ol>
        <li>Warna hijau berarti jumlah TBC antara 0 sampai 5 orang</li>
        <li>Warna kuning berarti jumlah TBC antara 6 sampai 20 orang</li>
        <li>Warna merah berarti jumlah TBC diatas 20 orang</li>
    </ol>
    <h2 class="text-center">Tabel Data TBC<br>Total <?php echo $this->number_format($total_all); ?> Orang</h1>
    <div style="width: 100%; overflow: auto; height: 100vh;">
        <table class="table table-bordered" id="table-data">
            <thead>
                <tr>
                    <th class='text-center'>Kode Desa</th>
                    <th class='text-center'>Provinsi</th>
                    <th class='text-center'>Kabupaten/Kota</th>
                    <th class='text-center'>Kecamatan</th>
                    <th class='text-center'>Desa</th>
                    <th class='text-center'>Total TBC</th>
                    <th class='text-center'>Jenis Kelamin</th>
                    <th class='text-center'>Umur</th>
                    <th class='text-center'>Tindak Lanjut</th>
                    <th class='text-center'>Hasil Akhir</th>
                    <th class='text-center'>Status Pengobatan</th>
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