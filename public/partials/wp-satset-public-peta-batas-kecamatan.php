<?php
$center = $this->get_center();
$maps_all = $this->get_polygon(array(
    'type' => 'kecamatan'
));

$body =  '';
foreach($maps_all as $desa){
    $body .= "
        <tr>
            <td class='text-center'>".$desa['data']['id2012']."</td>
            <td class='text-center'>".$desa['data']['provinsi']."</td>
            <td class='text-center'>".$desa['data']['kabkot']."</td>
            <td class='text-center'>".$desa['data']['kecamatan']."</td>
            <td class='text-center'></td>
        </tr>
    ";
}
?>
<h1 class="text-center">Peta Batas Kecamatan<br><?php echo $this->getNamaDaerah(); ?></h1>
<div style="width: 95%; margin: 0 auto; height: 90vh; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 100%;"></div>
    <h2 class="text-center">Tabel Data</h1>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th class='text-center'>Kode Desa</th>
                <th class='text-center'>Provinsi</th>
                <th class='text-center'>Kabupaten/Kota</th>
                <th class='text-center'>Kecamatan</th>
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
        });
    }
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>