<?php
$opsi1 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/desa_all_magetan/administrasi_kab_magetan_.shp',
    'type' => 'desa',
    'kode_daerah' => false
);
$opsi2 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/desa_no/Magetan_Desa.shp',
    'type' => 'desa',
    'kode_daerah' => true
);
$opsi3 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/kecamatan_no/Magetan_Kec.shp',
    'type' => 'kecamatan'
);
$maps_all = $this->read_shapefile($opsi1);
$center = $this->get_center();
?>
<h1 class="text-center">Conversi File SHP ke Google Maps</h1>
<div style="width: 95%; margin: 0 auto; height: 90vh; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 100%;"></div>
</div>

<script type="text/javascript">
    window.maps_all = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>