<?php
$opsi1 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/desa_all_magetan/administrasi_kab_magetan_.shp',
    'type' => 'desa',
    'kode_daerah' => false
);
$opsi = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/desa_no/Magetan_Desa.shp',
    'type' => 'desa',
    'kode_daerah' => true
);
$opsi3 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/kecamatan_no/Magetan_Kec.shp',
    'type' => 'kecamatan'
);
$maps_all = $this->read_shapefile($opsi);
$center = $this->get_center();
?>
<h1 class="text-center">Conversi File SHP ke Google Maps</h1>
<div style="width: 95%; margin: 0 auto; height: 90vh; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 100%;"></div>
</div>

<script async defer src="<?php echo $this->get_map_url(); ?>"></script>
<script type="text/javascript">
    window.maps_all = <?php echo json_encode($maps_all); ?>;
    function cari_alamat() {
        var alamat = jQuery('#cari-alamat-input').val();
        geocoder = new google.maps.Geocoder();
        geocoder.geocode( { 'address': alamat}, function(results, status) {
            if (status == 'OK') {
                console.log('results', results);
                map.setCenter(results[0].geometry.location);
            } else {
                alert('Geocode was not successful for the following reason: ' + status);
            }
        });
    }

    jQuery(document).ready(function(){
        var search = ''
            +'<div class="input-group" style="margin-bottom: 5px; display: block;">'
                +'<div class="input-group-prepend">'
                    +'<input class="form-control" id="cari-alamat-input" type="text" placeholder="Kotak pencarian alamat">'
                    +'<button class="btn btn-success" id="cari-alamat" type="button"><i class="dashicons dashicons-search"></i></button>'
                +'</div>'
            +'</div>';
        jQuery("#map-canvas").before(search);
        jQuery("#cari-alamat").on('click', function(){
            cari_alamat();
        });
        jQuery("#cari-alamat-input").on('keyup', function (e) {
            if (e.key === 'Enter' || e.keyCode === 13) {
                cari_alamat();
            }
        });
    });

    function initMap() {
        // Lokasi Center Map
        var lokasi_aset = new google.maps.LatLng(<?php echo $center['lat']; ?>, <?php echo $center['lng']; ?>);
        // Setting Map
        var mapOptions = {
            zoom: 13,
            center: lokasi_aset,
            mapTypeId: google.maps.MapTypeId.HYBRID
        };
        // Membuat Map
        window.map = new google.maps.Map(document.getElementById("map-canvas"), mapOptions);

        // Membuat Shape
        maps_all.map(function(data, i){
            console.log(data.coor);
            new google.maps.Polygon({
                map: map,
                paths: data.coor,
                strokeColor: data.color,
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: data.color,
                fillOpacity: 0.45,
                html: 'tes'
            });
        })
    }
</script>