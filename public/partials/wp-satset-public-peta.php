<?php
$center = $this->get_center();
$maps_all = $this->get_polygon();
foreach($maps_all as $i => $desa){
    $maps_all[$i]['index'] = $i;
}
?>
<h1 class="text-center">Peta Satu Data<br><?php echo $this->getNamaDaerah(); ?></h1>
<div style="width: 95%; margin: 0 auto; height: 90vh; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 100%;"></div>
</div>

<script type="text/javascript">
    window.maps_all = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>