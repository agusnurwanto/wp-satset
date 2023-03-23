function cari_alamat(text) {
    if(text){
        var alamat = text;
    }else{
        var alamat = jQuery('#cari-alamat-input').val();
    }
    geocoder = new google.maps.Geocoder();
    geocoder.geocode( { 'address': alamat}, function(results, status) {
        if (status == 'OK') {
            console.log('results', results);
            map.setCenter(results[0].geometry.location);
            map.setZoom(15);
            jQuery([document.documentElement, document.body]).animate({
                scrollTop: jQuery("#map-canvas").offset().top
            }, 500);
        } else {
            alert('Geocode was not successful for the following reason: ' + status);
        }
    });
}

function setCenter(lng, ltd){
    var lokasi_aset = new google.maps.LatLng(lng, ltd);
    map.setCenter(lokasi_aset);
    map.setZoom(15);
    jQuery([document.documentElement, document.body]).animate({
        scrollTop: jQuery("#map-canvas").offset().top
    }, 500);
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
    window.chartWindow = {};
    window.chartRenderWindow = {};
    window.chartWindowDtks = {};
    window.chartRenderWindowDtks = {};
    window.infoWindow = {};

    // Membuat Shape
    maps_all.map(function(data, i){
        // console.log(data.coor);
        data.coor.map(function(coor, ii){
            var bidang1 = new google.maps.Polygon({
                map: map,
                paths: coor,
                strokeColor: data.color,
                strokeOpacity: 0.5,
                strokeWeight: 2,
                fillColor: data.color,
                fillOpacity: 0.3
            });
            var index = data.index;
            chartWindow[index] = data.chart;
            chartWindowDtks[index] = data.chart_dtks;

            // Menampilkan Informasi Data
            var contentString = data.html;
            infoWindow[index] = new google.maps.InfoWindow({
                content: contentString
            });
            google.maps.event.addListener(bidang1, 'click', function(event) {
                infoWindow[index].setPosition(event.latLng);
                infoWindow[index].open(map);

                var id = "chart-"+index;
                if(!chartRenderWindow[id]){
                    console.log('index, chartWindow[index]', index, chartWindow[index]);

                    // menampilkan chart
                    setTimeout(function(){
                        chartRenderWindow[id] = new Chart(document.getElementById(id).getContext('2d'), {
                            type: "pie",
                            data: {
                                labels: chartWindow[index].label,
                                datasets: [
                                    {
                                        label: "",
                                        data: chartWindow[index].data,
                                        backgroundColor: chartWindow[index].color
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: "bottom",
                                        labels: {
                                            font: {
                                                size: 16
                                            }
                                        }
                                    },
                                    tooltip: {
                                        bodyFont: {
                                            size: 16
                                        },
                                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                                        boxPadding: 5
                                    },
                                    datalabels: {
                                        formatter: (value, ctx) => {
                                            let sum = 0;
                                            let dataArr = ctx.chart.data.datasets[0].data;
                                            dataArr.map(data => {
                                                sum += data;
                                            });
                                            let percentage = ((value / sum) * 100).toFixed(2)+"%";
                                            console.log('percentage, dataArr',value, percentage, dataArr);
                                            return percentage;
                                        },
                                        color: '#000',
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    }, 500);
                }else{
                    chartRenderWindow[id].update();
                }

                var id_dtks = "chart-dtks-"+index;
                if(!chartRenderWindowDtks[id_dtks]){
                    console.log('index, chartWindow[index]', index, chartRenderWindowDtks[index]);

                    // menampilkan chart
                    setTimeout(function(){
                        chartRenderWindowDtks[id_dtks] = new Chart(document.getElementById(id_dtks).getContext('2d'), {
                            type: "pie",
                            data: {
                                labels: chartWindowDtks[index].label,
                                datasets: [
                                    {
                                        label: "",
                                        data: chartWindowDtks[index].data,
                                        backgroundColor: chartWindowDtks[index].color
                                    }
                                ]
                            },
                            options: {
                                responsive: true,
                                plugins: {
                                    legend: {
                                        position: "bottom",
                                        labels: {
                                            font: {
                                                size: 16
                                            }
                                        }
                                    },
                                    tooltip: {
                                        bodyFont: {
                                            size: 16
                                        },
                                        backgroundColor: "rgba(0, 0, 0, 0.8)",
                                        boxPadding: 5
                                    },
                                    datalabels: {
                                        formatter: (value, ctx) => {
                                            let sum = 0;
                                            let dataArr = ctx.chart.data.datasets[0].data;
                                            dataArr.map(data => {
                                                sum += data;
                                            });
                                            let percentage = ((value / sum) * 100).toFixed(2)+"%";
                                            console.log('percentage, dataArr',value, percentage, dataArr);
                                            return percentage;
                                        },
                                        color: '#000',
                                    }
                                }
                            },
                            plugins: [ChartDataLabels]
                        });
                    }, 500);
                }else{
                    chartRenderWindowDtks[id_dtks].update();
                }
            });
        });
    })
}