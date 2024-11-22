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

$body =  '';
foreach($maps_all as $i => $desa){
    $search = $this->getSearchLocation($desa['data']);
    $maps_all[$i]['index'] = $i;
    $body .= "
        <tr>
            <td class='text-center'>".$desa['data']['id2012']."</td>
            <td class='text-center'>".$desa['data']['provinsi']."</td>
            <td class='text-center'>".$desa['data']['kab_kot']."</td>
            <td class='text-center'>".$desa['data']['kecamatan']."</td>
            <td class='text-center'>".$desa['data']['desa']."</td>
            <td>Luas dalam hectare: ".$desa["data"]['hectares']."</td>
            <td class='text-center'><a style='margin-bottom: 5px;' onclick='cari_alamat(\"".$search."\"); return false;' href='#' class='btn btn-danger'>Map</a></td>
        </tr>
    ";
}
?>
<h1 class="text-center">Peta Batas Desa/Kelurahan<br><?php echo $this->getNamaDaerah(); ?></h1>
<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 400px;"></div>
    <h2 class="text-center">Tabel Data</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th class='text-center'>Kode Desa</th>
                <th class='text-center'>Provinsi</th>
                <th class='text-center'>Kabupaten/Kota</th>
                <th class='text-center'>Kecamatan</th>
                <th class='text-center'>Desa</th>
                <th class='text-center'>Keterangan</th>
                <th class='text-center'>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php echo $body; ?>
        </tbody>
    </table>
</div>
<script type="text/javascript">
    window.maps_all = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>