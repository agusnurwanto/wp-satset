<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$data_tbc = $this->functions->generatePage(array(
    'nama_page' => 'Data Detail TBC', 
    'content' => '[data_detail_tbc]',
    'show_header' => 1,
    'post_status' => 'private'
));

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
    $index = $data['kdwil'];
    if(empty($tbc_all_desa[$index])){
        $tbc_all_desa[$index] = array();
    }
    $tbc_all_desa[$index][] = $data;
}
// print_r($tbc_all_desa); die();

// Helper: kelompokkan umur per range 10 tahun
function tbc_group_umur($records){
    $groups = array();
    foreach($records as $orang){
        $umur = intval($orang['umur']);
        $low  = floor($umur / 10) * 10;
        $high = $low + 9;
        $label = $low.'-'.$high.' th';
        if(empty($groups[$low])) $groups[$low] = array('label' => $label, 'count' => 0);
        $groups[$low]['count']++;
    }
    ksort($groups);
    $result = array();
    foreach($groups as $g){
        $result[] = $g['label'].' : '.$g['count'].' orang';
    }
    return $result;
}

// Helper: kelompokkan tindak lanjut, beri label jika kosong
function tbc_group_tindak_lanjut($records){
    $groups = array();
    foreach($records as $orang){
        $tl = trim($orang['tindak_lanjut']);
        $label = ($tl === '' || $tl === null) ? '(Tidak Ada Tindak Lanjut)' : $tl;
        if(empty($groups[$label])) $groups[$label] = 0;
        $groups[$label]++;
    }
    ksort($groups);
    $result = array();
    foreach($groups as $label => $count){
        $result[] = $label.': '.$count;
    }
    return $result;
}

$total_all = 0;
$body =  '';
foreach($maps_all as $i => $desa){
    $maps_all[$i]['index'] = $i;
    $index = $desa['data']['id2012'];
    $total_tbc = 0;
    $total_umur = array();
    $total_tindak_lanjut = array();
    if(!empty($tbc_all_desa[$index])){
        $total_tbc = count($tbc_all_desa[$index]);
        $total_umur         = tbc_group_umur($tbc_all_desa[$index]);
        $total_tindak_lanjut = tbc_group_tindak_lanjut($tbc_all_desa[$index]);
    }
    if($total_tbc <= 5){
        $maps_all[$i]['color'] = '#0cbf00';
    }else if($total_tbc <= 20){
        $maps_all[$i]['color'] = '#fff70a';
    }else if($total_tbc > 20){
        $maps_all[$i]['color'] = '#ff0000';
    }
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
                $index,
                $data_tbc['url']);
            $nama_desa = "<a href='" . $detail_url . "' target='_blank' rel='noopener noreferrer'>" . $desa['data']['desa'] . "</a>";
        }
    }
    $body .= "
        <tr>
            <td class='text-center'>".$desa['data']['id2012']."</td>
            <td class='text-center'>".$desa['data']['provinsi']."</td>
            <td class='text-center'>".$desa['data']['kab_kot']."</td>
            <td class='text-center'>".$desa['data']['kecamatan']."</td>
            <td class='text-center'>".$nama_desa."</td>
            <td class='text-center'>".$total_tbc."</td>
            <td>".implode('<br>', $total_umur)."</td>
            <td>".implode('<br>', $total_tindak_lanjut)."</td>
            <td class='text-center'><a style='margin-bottom: 5px;' onclick='cari_alamat(\"".$search."\"); return false;' href='#' class='btn btn-danger'>Map</a></td>
        </tr>
    ";
    $total_all += $total_tbc;
    unset($tbc_all_desa[$index]);
}

// Data TBC luar wilayah Kab. Magetan (kdwil tidak cocok dengan id2012 manapun)
foreach($tbc_all_desa as $kdwil => $records){
    $total_tbc_luar = count($records);
    $total_umur_luar = array();
    $total_tindak_lanjut_luar = array();
    $total_umur_luar          = tbc_group_umur($records);
    $total_tindak_lanjut_luar = tbc_group_tindak_lanjut($records);
    $info = $records[0];
    $body .= "
        <tr>
            <td class='text-center'>".$kdwil."</td>
            <td class='text-center'>".$info['provinsi']."</td>
            <td class='text-center'>".$info['kabkot']."</td>
            <td class='text-center'>".$info['kecamatan']."</td>
            <td class='text-center'>".$info['desa']." (Di luar daerah)</td>
            <td class='text-center'>".$total_tbc_luar."</td>
            <td>".implode('<br>', $total_umur_luar)."</td>
            <td>".implode('<br>', $total_tindak_lanjut_luar)."</td>
            <td class='text-center'>-</td>
        </tr>
    ";
    $total_all += $total_tbc_luar;
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
<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px; padding-top: 50px;">
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
                    <th class='text-center'>Umur</th>
                    <th class='text-center'>Tindak Lanjut</th>
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