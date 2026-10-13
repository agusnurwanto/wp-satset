(function ($, window, document) {
	'use strict';

	var map;
	var mapRecords = [];
	var baseLayers = {};
	var currentBaseLayer;
	var alertElement;
	var labelsVisible = false;
	var labelButton;
	var labelToggleEnabled = false;

	function showMapAlert(message, type) {
		if (!alertElement) {
			alertElement = document.createElement('div');
			alertElement.className = 'alert satset-public-map-alert';
			alertElement.setAttribute('role', 'alert');
			var mapElement = document.getElementById('map-canvas');
			if (mapElement) {
				mapElement.parentNode.insertBefore(alertElement, mapElement);
			}
		}
		alertElement.className = 'alert satset-public-map-alert alert-' + (type || 'warning');
		alertElement.textContent = message;
		alertElement.hidden = !message;
	}

	function normalizedText(value) {
		return String(value === null || value === undefined ? '' : value)
			.toLowerCase()
			.replace(/[^\p{L}\p{N}]+/gu, ' ')
			.trim()
			.replace(/\s+/g, ' ');
	}

	function getLocationText(data) {
		var fields = ['desa', 'kecamatan', 'kab_kot', 'kabkot', 'provinsi'];
		var values = [];
		fields.forEach(function (key) {
			var value = data && data[key] !== null && data[key] !== undefined ? String(data[key]).trim() : '';
			if (value && values.indexOf(value) === -1) {
				values.push(value);
			}
		});
		return values.join(' ');
	}

	function findLocalRecord(query) {
		var normalizedQuery = normalizedText(query);
		if (!normalizedQuery) {
			return [];
		}
		var tokens = normalizedQuery.split(' ');
		var exactMatches = mapRecords.filter(function (record) {
			return normalizedText(getLocationText(record.data)) === normalizedQuery;
		});
		if (exactMatches.length) {
			return exactMatches;
		}
		return mapRecords.filter(function (record) {
			var location = normalizedText(getLocationText(record.data));
			return tokens.every(function (token) {
				return location.indexOf(token) !== -1;
			});
		});
	}

	function chartOptions(data) {
		return {
			responsive: true,
			plugins: {
				legend: {
					position: 'bottom',
					labels: { font: { size: 16 } }
				},
				tooltip: {
					bodyFont: { size: 16 },
					backgroundColor: 'rgba(0, 0, 0, 0.8)',
					boxPadding: 5
				},
				datalabels: {
					formatter: function (value, context) {
						var values = context.chart.data.datasets[0].data;
						var sum = values.reduce(function (total, item) {
							return total + Number(item || 0);
						}, 0);
						return sum ? ((value / sum) * 100).toFixed(2) + '%' : '0%';
					},
					color: '#000'
				}
			}
		};
	}

	function renderChart(canvas, chartData, chartType) {
		if (!canvas || !chartData || !window.Chart) {
			return;
		}
		var plugins = window.ChartDataLabels ? [window.ChartDataLabels] : [];
		new window.Chart(canvas.getContext('2d'), {
			type: chartData.type || chartType || 'pie',
			data: {
				labels: chartData.label || [],
				datasets: [{
					label: '',
					data: chartData.data || [],
					backgroundColor: chartData.color || []
				}]
			},
			options: chartOptions(chartData),
			plugins: plugins
		});
	}

	function bindRecordPopup(layer, record) {
		var content = record.html || JSON.stringify(record.data || {});
		layer.bindPopup(content, { maxWidth: 540 });
		layer.on('popupopen', function (event) {
			if (!record.chart && !record.chart_dtks) {
				return;
			}
			var popup = event.popup.getElement();
			if (!popup) {
				return;
			}
			var index = record.index;
			renderChart(popup.querySelector('#chart-' + index), record.chart, 'pie');
			renderChart(popup.querySelector('#chart-dtks-' + index), record.chart_dtks, 'pie');
		});
	}

	function makePolygonStyle(record) {
		return {
			color: record.color || '#3388ff',
			weight: 2,
			opacity: 0.75,
			fillColor: record.color || '#3388ff',
			fillOpacity: 0.3
		};
	}

	function fallBackToOpenStreetMap(layer) {
		layer.on('tileerror', function () {
			if (currentBaseLayer !== layer) {
				return;
			}
			map.removeLayer(layer);
			currentBaseLayer = baseLayers.osm.addTo(map);
			var select = document.getElementById('satset-public-map-basemap');
			if (select) {
				select.value = 'osm';
			}
			showMapAlert('Layer Google Maps tidak dapat dimuat. OpenStreetMap kembali digunakan.', 'warning');
		});
	}

	function buildMap() {
		var mapElement = document.getElementById('map-canvas');
		if (!mapElement) {
			return;
		}
		if (mapElement.dataset.satsetLeafletInitialized === 'true') {
			return;
		}
		mapElement.classList.add('satset-public-leaflet-map');
		if (!window.L) {
			showMapAlert('Library peta Leaflet gagal dimuat. Periksa koneksi lalu muat ulang halaman.', 'danger');
			return;
		}
		mapElement.dataset.satsetLeafletInitialized = 'true';

		var center = window.maps_center || {};
		var lat = Number(center.lat);
		var lng = Number(center.lng);
		if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
			lat = -2.5;
			lng = 118;
		}

		map = window.map = window.L.map(mapElement, {
			preferCanvas: true,
			attributionControl: false
		}).setView([lat, lng], 13);
		baseLayers.osm = window.L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19
		});
		currentBaseLayer = baseLayers.osm.addTo(map);

		labelButton = document.getElementById('btn-label-map');
		labelToggleEnabled = !!labelButton;
		if (labelButton) {
			labelButton.hidden = true;
			labelButton.style.display = 'none';
		}

		if (window.satsetLeafletPublic && window.satsetLeafletPublic.hasGoogleMapsApiKey) {
			if (window.L.gridLayer && typeof window.L.gridLayer.googleMutant === 'function') {
				try {
					['google-streets', 'google-satellite', 'google-hybrid', 'google-terrain'].forEach(function (name) {
						baseLayers[name] = createGoogleBaseLayer(name);
					});
				} catch (error) {
					showMapAlert('Layer Google Maps gagal disiapkan. OpenStreetMap tetap tersedia.', 'warning');
				}
			} else {
				showMapAlert('Layer Google Maps gagal dimuat. OpenStreetMap tetap tersedia.', 'warning');
			}
		}

		addBaseLayerControl();
		updateLabelButton('osm');
		renderRecords();
	}

	function createGoogleBaseLayer(name) {
		var typeByName = {
			'google-streets': 'roadmap',
			'google-satellite': 'satellite',
			'google-hybrid': 'hybrid',
			'google-terrain': 'terrain'
		};
		var options = { type: typeByName[name], maxZoom: 21 };
		if (labelToggleEnabled && !labelsVisible) {
			options.styles = [{ elementType: 'labels', stylers: [{ visibility: 'off' }] }];
		}
		var layer = window.L.gridLayer.googleMutant(options);
		fallBackToOpenStreetMap(layer);
		return layer;
	}

	function isGoogleBasemap(name) {
		return name.indexOf('google-') === 0;
	}

	function updateLabelButton(name) {
		if (!labelButton) {
			return;
		}
		var visible = isGoogleBasemap(name) && !!baseLayers[name];
		labelButton.hidden = !visible;
		labelButton.style.display = visible ? '' : 'none';
		labelButton.textContent = labelsVisible ? 'Matikan Label' : 'Hidupkan Label';
	}

	function toggleLabels() {
		if (!labelButton || !currentBaseLayer) {
			return;
		}
		var select = document.getElementById('satset-public-map-basemap');
		var selectedName = select ? select.value : '';
		if (!isGoogleBasemap(selectedName) || !baseLayers[selectedName]) {
			updateLabelButton(selectedName);
			return;
		}

		labelsVisible = !labelsVisible;
		window.labelMapAktif = labelsVisible;
		var oldLayer = baseLayers[selectedName];
		if (map.hasLayer(oldLayer)) {
			map.removeLayer(oldLayer);
		}
		baseLayers[selectedName] = createGoogleBaseLayer(selectedName);
		currentBaseLayer = baseLayers[selectedName].addTo(map);
		updateLabelButton(selectedName);
	}

	window.satsetLeafletToggleLabels = toggleLabels;

	function addBaseLayerControl() {
		var control = window.L.control({ position: 'topleft' });
		control.onAdd = function () {
			var container = window.L.DomUtil.create('div', 'leaflet-control satset-public-leaflet-controls');
			var label = document.createElement('label');
			label.htmlFor = 'satset-public-map-basemap';
			label.textContent = 'Peta dasar';
			var select = document.createElement('select');
			select.id = 'satset-public-map-basemap';
			select.setAttribute('aria-label', 'Pilih peta dasar');

			[
				['osm', 'OpenStreetMap'],
				['google-streets', 'Google Maps'],
				['google-satellite', 'Google Satelit'],
				['google-hybrid', 'Google Hybrid'],
				['google-terrain', 'Google Terrain']
			].forEach(function (item) {
				if (item[0] !== 'osm' && !baseLayers[item[0]]) {
					return;
				}
				var option = document.createElement('option');
				option.value = item[0];
				option.textContent = item[1];
				select.appendChild(option);
			});

			window.L.DomEvent.disableClickPropagation(container);
			window.L.DomEvent.disableScrollPropagation(container);
			select.addEventListener('change', function () {
				if (labelToggleEnabled && isGoogleBasemap(select.value) && baseLayers[select.value]) {
					baseLayers[select.value] = createGoogleBaseLayer(select.value);
				}
				var nextLayer = baseLayers[select.value];
				if (!nextLayer) {
					showMapAlert('Layer peta dasar yang dipilih tidak tersedia. OpenStreetMap tetap aktif.', 'warning');
					select.value = 'osm';
					return;
				}
				if (currentBaseLayer && map.hasLayer(currentBaseLayer)) {
					map.removeLayer(currentBaseLayer);
				}
				currentBaseLayer = nextLayer.addTo(map);
				updateLabelButton(select.value);
				showMapAlert('', 'warning');
			});
			container.appendChild(label);
			container.appendChild(select);
			return container;
		};
		control.addTo(map);
	}

	function renderRecords() {
		var records = Array.isArray(window.maps_all) ? window.maps_all : [];
		mapRecords = records.map(function (record, index) {
			record.index = record.index === undefined ? index : record.index;
			record.layers = [];
			if (!Array.isArray(record.coor) || !record.coor.length) {
				return record;
			}
			try {
				var layer = window.L.polygon(record.coor, makePolygonStyle(record)).addTo(map);
				bindRecordPopup(layer, record);
				record.layers.push(layer);
			} catch (error) {
				showMapAlert('Sebagian geometri peta tidak valid dan tidak dapat ditampilkan.', 'warning');
			}
			return record;
		});
	}

	function focusRecords(records) {
		if (!records.length) {
			return false;
		}
		var layers = [];
		records.forEach(function (record) {
			if (record.layers) {
				layers = layers.concat(record.layers);
			}
		});
		if (!layers.length) {
			showMapAlert('Lokasi tidak ditemukan pada data peta.', 'warning');
			return false;
		}
		var bounds = window.L.latLngBounds([]);
		layers.forEach(function (layer) {
			bounds.extend(layer.getBounds());
		});
		if (!bounds.isValid()) {
			showMapAlert('Geometri lokasi ini tidak valid untuk ditampilkan.', 'warning');
			return false;
		}
		showMapAlert('');
		document.getElementById('map-canvas').scrollIntoView({ behavior: 'smooth', block: 'center' });
		window.setTimeout(function () {
			map.invalidateSize();
			map.fitBounds(bounds, { padding: [24, 24], maxZoom: records.length === 1 ? 15 : 12 });
			if (records.length === 1 && layers[0]) {
				layers[0].openPopup();
			}
		}, 200);
		return true;
	}

	function geocodeAddress(query) {
		var url = 'https://nominatim.openstreetmap.org/search?format=jsonv2&limit=1&q=' + encodeURIComponent(query);
		fetch(url, { headers: { Accept: 'application/json' } })
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Pencarian alamat gagal (' + response.status + ').');
				}
				return response.json();
			})
			.then(function (results) {
				if (!results.length) {
					showMapAlert('Alamat tidak ditemukan. Coba gunakan nama desa, kecamatan, atau kabupaten.', 'warning');
					return;
				}
				map.setView([Number(results[0].lat), Number(results[0].lon)], 15);
				showMapAlert('');
			})
			.catch(function () {
				showMapAlert('Pencarian alamat gagal dimuat. Periksa koneksi internet lalu coba kembali.', 'danger');
			});
	}

	window.satsetLeafletFocus = function (query) {
		if (!map) {
			return;
		}
		var records = findLocalRecord(query);
		if (records.length) {
			focusRecords(records);
			return;
		}
		if (query) {
			geocodeAddress(query);
		} else {
			showMapAlert('Masukkan nama lokasi yang ingin dicari.', 'warning');
		}
	};

	window.satsetLeafletSetCenter = function (lat, lng) {
		if (!map) {
			return;
		}
		lat = Number(lat);
		lng = Number(lng);
		if (!Number.isFinite(lat) || !Number.isFinite(lng)) {
			showMapAlert('Koordinat lokasi tidak valid.', 'warning');
			return;
		}
		map.setView([lat, lng], 15);
	};

	$(function () {
		var mapElement = document.getElementById('map-canvas');
		if (!mapElement) {
			return;
		}
		if (mapElement.dataset.satsetSearchInitialized === 'true') {
			return;
		}
		mapElement.dataset.satsetSearchInitialized = 'true';
		document.querySelectorAll('#cari-alamat-input, #cari-alamat').forEach(function (element) {
			var existingSearch = element.closest('.satset-public-map-search, .input-group');
			if (existingSearch) {
				existingSearch.remove();
			} else {
				element.remove();
			}
		});
		var search = document.createElement('div');
		search.className = 'input-group satset-public-map-search';
		search.style.cssText = 'margin-bottom:5px;display:flex;';
		search.innerHTML = '<input class="form-control" id="cari-alamat-input" type="text" placeholder="Kotak pencarian alamat" aria-label="Kotak pencarian alamat"><button class="btn btn-success" id="cari-alamat" type="button"><i class="dashicons dashicons-search" aria-hidden="true"></i><span class="screen-reader-text">Cari lokasi</span></button>';
		mapElement.parentNode.insertBefore(search, mapElement);
		$('#cari-alamat').on('click', function () {
			window.satsetLeafletFocus($('#cari-alamat-input').val());
		});
		$('#cari-alamat-input').on('keydown', function (event) {
			if (event.key === 'Enter') {
				event.preventDefault();
				window.satsetLeafletFocus(this.value);
			}
		});
		buildMap();
	});
}(jQuery, window, document));
