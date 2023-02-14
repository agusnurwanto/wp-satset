jQuery(function($){
		
	var FEED = window.FEED || {};	
	
	//------ TWEETS ------//
		
	FEED.TWEET = function() {					
		$('.tweets_feed').twittie({
			template: 
			'<div class="tweet_user">'+
				'<span class="icon-twitter"><i class="fa fa-twitter"></i></span>'+
				'<span class="username">{{screen_name}}</span>'+
			'</div>'+
			'<div class="tweet_text">'+
				'{{tweet}}'+
			'</div>'+
			'<div class="tweet_time">'+
				'<a href="{{url}}">{{date}}</a>'+
			'</div>'
			,
		}, function(){
			$(".tweets_feed").owlCarousel({
	
				pagination : true,
				navigation : true,
				autoPlay: true,
				singleItem: true
			});
		});
	}
	
	$(document).ready(function(){
			// FEED.TWEET();
	});

});

if(typeof chart_jenis_aset == 'undefined'){
	chart_jenis_aset = {
		label : [
	        'Tanah', 
	        'Peralatan dan Mesin', 
	        'Gedung dan Bangunan', 
	        'Jalan, Jaringan dan Irigrasi', 
	        'Aset Tetap Lainya', 
	        'Kontruksi Dalam Pengerjaan'
        ],
		data : [10, 23, 345, 3, 6, 0],
		color : [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
        	'rgba(255, 159, 64, 1)'
        ],
	}
}else{
	chart_jenis_aset.data.map(function(b, i){
		chart_jenis_aset.data[i] = +b;
	})
}
window.pieChart = new Chart(document.getElementById('chart_per_jenis_aset'), {
    type: 'bar',
    data: {
        labels: chart_jenis_aset.label,
        datasets: [
            {
                label: 'Total Nilai Per Jenis Aset',
                data: chart_jenis_aset.data,
                backgroundColor: chart_jenis_aset.color
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
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
            	backgroundColor: 'rgba(0, 0, 0, 0.8)',
            	boxPadding: 5
            },
        },
        animation: {
		  	onProgress: function() {
			    const ctx = this.ctx;
			    ctx.textAlign = 'center';
			    ctx.textBaseline = 'bottom';

			    let dataSum = 0;
			    if(this._sortedMetasets.length > 0 && this._sortedMetasets[0].data.length > 0) {
			      	const dataset = this._sortedMetasets[0].data[0].$context.dataset;
			      	dataSum = dataset.data.reduce((p, c) => p + c, 0);
			    }
			    if(dataSum <= 0) return;

			    this._sortedMetasets.forEach(meta => {
			      	meta.data.forEach(metaData => {
				        const dataset = metaData.$context.dataset;
				        const datasetIndex = metaData.$context.dataIndex;
				        const value = dataset.data[datasetIndex];
				        const percent = (Math.round(value / dataSum * 1000) / 10) + '%';
				        const mid_radius = metaData.innerRadius + (metaData.outerRadius - metaData.innerRadius) * 0.7;
				        const start_angle = metaData.startAngle;
				        const end_angle = metaData.endAngle;
				        if(start_angle === end_angle) return; // hidden
				        const mid_angle = start_angle + (end_angle - start_angle) / 2;

				        const x = mid_radius * Math.cos(mid_angle);
				        const y = mid_radius * Math.sin(mid_angle);

				        ctx.fillStyle = '#fff';
				        ctx.fillText(percent, metaData.x + x, metaData.y + y + 20);
			      	});
			    });
		  	}
		}
    }
});

var data_skpd = [];
var new_data = [];
var labels = [];
data_skpd.map(function(b, i){
	if(i < 30){ return; }
	var data = {};
	for(var n in b){
		labels.push(n.substring(0, 50));
		new_data.push(b[n]);
	}
});
// console.log('new_data', new_data);
window.pieChart2 = new Chart(document.getElementById('chart_per_unit'), {
    type: 'bar',
    data: {
    	labels: labels,
        datasets: [
            {
                label: 'Nilai Aset',
                data: new_data,
                backgroundColor: [
                    'rgba(255, 99, 132, 1)'
                ]
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
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
            	backgroundColor: 'rgba(0, 0, 0, 0.8)',
            	boxPadding: 5
            },
        },
        animation: {
		  	onProgress: function() {
			    const ctx = this.ctx;
			    ctx.textAlign = 'center';
			    ctx.textBaseline = 'bottom';

			    let dataSum = 0;
			    if(this._sortedMetasets.length > 0 && this._sortedMetasets[0].data.length > 0) {
			      	const dataset = this._sortedMetasets[0].data[0].$context.dataset;
			      	dataSum = dataset.data.reduce((p, c) => p + c, 0);
			    }
			    if(dataSum <= 0) return;

			    this._sortedMetasets.forEach(meta => {
			      	meta.data.forEach(metaData => {
				        const dataset = metaData.$context.dataset;
				        const datasetIndex = metaData.$context.dataIndex;
				        const value = dataset.data[datasetIndex];
				        const percent = (Math.round(value / dataSum * 1000) / 10) + '%';
				        const mid_radius = metaData.innerRadius + (metaData.outerRadius - metaData.innerRadius) * 0.7;
				        const start_angle = metaData.startAngle;
				        const end_angle = metaData.endAngle;
				        if(start_angle === end_angle) return; // hidden
				        const mid_angle = start_angle + (end_angle - start_angle) / 2;

				        const x = mid_radius * Math.cos(mid_angle);
				        const y = mid_radius * Math.sin(mid_angle);

				        ctx.fillStyle = '#fff';
				        ctx.fillText(formatRupiah(value), metaData.x + x, metaData.y + y);
				        ctx.fillText(percent, metaData.x + x, metaData.y + y + 20);
			      	});
			    });
		  	}
		}
    }
});