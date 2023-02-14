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
    var lokasi_aset = new google.maps.LatLng(maps_center['lat'], maps_center['lng']);
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
        data.coor.map(function(coor, ii){
            new google.maps.Polygon({
                map: map,
                paths: coor,
                strokeColor: data.color,
                strokeOpacity: 0.8,
                strokeWeight: 2,
                fillColor: data.color,
                fillOpacity: 0.45,
                html: 'tes'
            });
        });
    })
}