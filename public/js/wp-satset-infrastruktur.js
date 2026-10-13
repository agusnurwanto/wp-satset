(function ($) {
	'use strict';

	var map;
	var table;
	var records = [];
	var importedFields = {};
	var featureLayers = new Map();
	var regionColors = new Map();
	var baseLayers = {};
	var currentBaseLayer;
	var activeRecord = null;

	function escapeHtml(value) {
		return String(value === null || value === undefined ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function displayValue(value) {
		if (value === null || value === undefined || value === '') {
			return '—';
		}
		return String(value);
	}

	function showPageAlert(message, type) {
		var alert = $('#satset-infrastruktur-page-alert');
		alert.removeClass('alert-danger alert-success alert-warning')
			.addClass('alert-' + (type || 'danger'))
			.text(message)
			.toggle(!!message);
	}

	function showModalAlert(message) {
		$('#satset-infrastruktur-edit-alert').text(message).toggle(!!message);
	}

	function showMapStatus(message, duration) {
		var status = $('#satset-infrastruktur-map-status');
		status.text(message).prop('hidden', !message);
		if (message && duration) {
			window.setTimeout(function () {
				if (status.text() === message) {
					status.prop('hidden', true);
				}
			}, duration);
		}
	}

	function changeBaseLayer(layerName) {
		if (layerName === 'osm') {
			if (currentBaseLayer && map.hasLayer(currentBaseLayer)) {
				map.removeLayer(currentBaseLayer);
			}
			currentBaseLayer = baseLayers.osm;
			currentBaseLayer.addTo(map);
			showPageAlert('');
			showMapStatus('', 0);
			return;
		}

		if (!satsetInfrastructure.hasGoogleMapsApiKey) {
			$('#satset-infrastruktur-basemap').val('osm');
			changeBaseLayer('osm');
			showPageAlert('Google Maps API key belum dikonfigurasi. Atur Google Maps APIKEY di WP SATSET Options; OpenStreetMap tetap aktif.');
			return;
		}
		if (!baseLayers[layerName]) {
			$('#satset-infrastruktur-basemap').val('osm');
			changeBaseLayer('osm');
			showPageAlert('Layer Google Maps gagal dimuat. Pastikan API key dan library Google Maps tersedia.');
			return;
		}

		if (currentBaseLayer && map.hasLayer(currentBaseLayer)) {
			map.removeLayer(currentBaseLayer);
		}
		currentBaseLayer = baseLayers[layerName];
		currentBaseLayer.addTo(map);
		showPageAlert('');
		showMapStatus('', 0);
	}

	function addGoogleBaseLayers() {
		if (!satsetInfrastructure.hasGoogleMapsApiKey) {
			return;
		}
		if (!L.gridLayer || typeof L.gridLayer.googleMutant !== 'function') {
			showPageAlert('Integrasi Google Maps untuk Leaflet gagal dimuat. OpenStreetMap tetap dapat digunakan.');
			return;
		}

		try {
			baseLayers['google-streets'] = L.gridLayer.googleMutant({ type: 'roadmap', maxZoom: 21 });
			baseLayers['google-satellite'] = L.gridLayer.googleMutant({ type: 'satellite', maxZoom: 21 });
			baseLayers['google-hybrid'] = L.gridLayer.googleMutant({ type: 'hybrid', maxZoom: 21 });
			baseLayers['google-terrain'] = L.gridLayer.googleMutant({ type: 'terrain', maxZoom: 21 });
		} catch (error) {
			showPageAlert('Layer Google Maps gagal disiapkan. OpenStreetMap tetap dapat digunakan.');
		}
	}

	function appendDetailTable(container, title, attributes, labels) {
		var section = document.createElement('section');
		var heading = document.createElement('h6');
		heading.textContent = title;
		section.appendChild(heading);

		var tableElement = document.createElement('table');
		var body = document.createElement('tbody');
		Object.keys(attributes || {}).forEach(function (key) {
			var row = document.createElement('tr');
			var label = document.createElement('th');
			var value = document.createElement('td');
			label.textContent = labels && labels[key] ? labels[key] : key;
			value.textContent = displayValue(attributes[key]);
			row.appendChild(label);
			row.appendChild(value);
			body.appendChild(row);
		});
		if (!Object.keys(attributes || {}).length) {
			var emptyRow = document.createElement('tr');
			var emptyCell = document.createElement('td');
			emptyCell.colSpan = 2;
			emptyCell.textContent = 'Belum ada atribut.';
			emptyRow.appendChild(emptyCell);
			body.appendChild(emptyRow);
		}
		tableElement.appendChild(body);
		section.appendChild(tableElement);
		container.appendChild(section);
	}

	function getRegionLabel(record) {
		var value = record.attributes && record.attributes.wilayah;
		var label = value === null || value === undefined ? '' : String(value).trim();
		return label || 'Tidak diketahui';
	}

	function buildRegionColors() {
		var regions = Array.from(new Set(records.map(getRegionLabel))).sort();
		regionColors.clear();
		regions.forEach(function (region, index) {
			regionColors.set(region, 'hsl(' + (Math.round(index * 137.508) % 360) + ', 68%, 42%)');
		});
	}

	function renderRegionLegend(visibleRecords) {
		var legend = document.getElementById('satset-infrastruktur-region-legend');
		if (!legend) {
			return;
		}

		legend.replaceChildren();
		var heading = document.createElement('strong');
		heading.className = 'satset-infrastruktur-region-legend-title';
		heading.textContent = 'Warna garis berdasarkan wilayah:';
		heading.setAttribute('role', 'presentation');
		legend.appendChild(heading);

		var regions = Array.from(new Set(visibleRecords.map(getRegionLabel))).sort();
		regions.forEach(function (region) {
			var item = document.createElement('span');
			item.className = 'satset-infrastruktur-region-legend-item';
			item.setAttribute('role', 'listitem');

			var swatch = document.createElement('span');
			swatch.className = 'satset-infrastruktur-region-legend-swatch';
			swatch.style.borderColor = regionColors.get(region) || '#6b7280';
			swatch.setAttribute('aria-hidden', 'true');

			var label = document.createElement('span');
			label.textContent = region;
			item.appendChild(swatch);
			item.appendChild(label);
			legend.appendChild(item);
		});
	}

	function createPopupContent(record) {
		var popup = document.createElement('div');
		popup.className = 'satset-infrastruktur-popup';
		var title = document.createElement('h4');
		title.textContent = displayValue(record.attributes.nm_ruas);
		popup.appendChild(title);
		if (satsetInfrastructure.isPublic) {
			appendDetailTable(popup, 'Informasi Infrastruktur', record.attributes, importedFields);
		} else {
			appendDetailTable(popup, 'Atribut Hasil Impor', record.attributes, importedFields);
			appendDetailTable(popup, 'Atribut Custom', record.custom_attribute, {});
		}
		return popup;
	}

	function getLineMidpoint(layer) {
		var linePaths = [];

		function collectPaths(latLngs) {
			var path = [];
			latLngs.forEach(function (item) {
				if (item && typeof item.lat === 'number' && typeof item.lng === 'number') {
					path.push(item);
					return;
				}
				if (path.length > 1) {
					linePaths.push(path);
				}
				path = [];
				if (Array.isArray(item)) {
					collectPaths(item);
				}
			});
			if (path.length > 1) {
				linePaths.push(path);
			}
		}

		function collectLayerPaths(currentLayer) {
			if (currentLayer instanceof L.Polyline) {
				collectPaths(currentLayer.getLatLngs());
				return;
			}
			if (typeof currentLayer.eachLayer === 'function') {
				currentLayer.eachLayer(collectLayerPaths);
			}
		}

		collectLayerPaths(layer);
		var totalLength = 0;
		var projectedPaths = linePaths.map(function (path) {
			var points = path.map(function (latLng) {
				return map.latLngToLayerPoint(latLng);
			});
			for (var i = 1; i < points.length; i++) {
				totalLength += points[i - 1].distanceTo(points[i]);
			}
			return points;
		});

		if (totalLength > 0) {
			var remainingLength = totalLength / 2;
			for (var pathIndex = 0; pathIndex < projectedPaths.length; pathIndex++) {
				var points = projectedPaths[pathIndex];
				for (var pointIndex = 1; pointIndex < points.length; pointIndex++) {
					var start = points[pointIndex - 1];
					var end = points[pointIndex];
					var segmentLength = start.distanceTo(end);
					if (remainingLength <= segmentLength) {
						var ratio = remainingLength / segmentLength;
						return map.layerPointToLatLng(L.point(
							start.x + (end.x - start.x) * ratio,
							start.y + (end.y - start.y) * ratio
						));
					}
					remainingLength -= segmentLength;
				}
			}
		}

		return layer.getBounds().getCenter();
	}

	function renderMap(year) {
		if (!map) {
			return;
		}

		map.closePopup();
		featureLayers.forEach(function (layer) {
			map.removeLayer(layer);
		});
		featureLayers.clear();

		var visibleRecords = records.filter(function (record) {
			return (!year || String(record.attributes.thn_data || '') === year) && record.geometry;
		});
		renderRegionLegend(visibleRecords);
		var bounds = [];
		visibleRecords.forEach(function (record) {
			var layer = L.geoJSON({
				type: 'Feature',
				properties: {},
				geometry: record.geometry
			}, {
				style: {
					color: regionColors.get(getRegionLabel(record)) || '#6b7280',
					weight: 4,
					opacity: 0.82
				},
				onEachFeature: function (feature, path) {
					path.on('click', function (event) {
						L.popup({ maxWidth: 420 })
							.setLatLng(event.latlng)
							.setContent(createPopupContent(record))
							.openOn(map);
					});
				}
			}).addTo(map);

			var featureBounds = layer.getBounds();
			if (featureBounds.isValid()) {
				featureLayers.set(record.id, layer);
				bounds.push(featureBounds);
			}
		});

		if (!visibleRecords.length) {
			showMapStatus('Tidak ada geometri untuk filter tahun ini.', 0);
		} else {
			showMapStatus('', 0);
		}

		if (bounds.length) {
			var combinedBounds = bounds[0];
			for (var i = 1; i < bounds.length; i++) {
				combinedBounds.extend(bounds[i]);
			}
			map.fitBounds(combinedBounds, { padding: [24, 24], maxZoom: 15 });
		}
	}

	function addCustomField(name, value) {
		var container = document.getElementById('satset-infrastruktur-custom-fields');
		var field = document.createElement('div');
		field.className = 'satset-infrastruktur-custom-field';

		var nameLabel = document.createElement('label');
		nameLabel.textContent = 'Nama atribut';
		var nameInput = document.createElement('input');
		nameInput.type = 'text';
		nameInput.maxLength = 100;
		nameInput.className = 'satset-custom-name';
		nameInput.value = name || '';
		nameInput.required = true;

		var valueLabel = document.createElement('label');
		valueLabel.textContent = 'Nilai';
		var valueInput = document.createElement('textarea');
		valueInput.maxLength = 5000;
		valueInput.className = 'satset-custom-value';
		valueInput.value = value || '';

		var removeButton = document.createElement('button');
		removeButton.type = 'button';
		removeButton.className = 'btn btn-outline-danger btn-sm satset-remove-custom';
		removeButton.setAttribute('aria-label', 'Hapus atribut custom');
		removeButton.textContent = '×';
		removeButton.addEventListener('click', function () {
			field.remove();
		});

		field.appendChild(nameLabel);
		field.appendChild(nameInput);
		field.appendChild(valueLabel);
		field.appendChild(valueInput);
		field.appendChild(removeButton);
		container.appendChild(field);
	}

	function openEditModal(record) {
		activeRecord = record;
		showModalAlert('');
		var importedContainer = document.getElementById('satset-infrastruktur-imported-fields');
		var customContainer = document.getElementById('satset-infrastruktur-custom-fields');
		importedContainer.replaceChildren();
		customContainer.replaceChildren();

		Object.keys(importedFields).forEach(function (key) {
			var field = document.createElement('div');
			field.className = 'satset-infrastruktur-readonly-field';
			var label = document.createElement('label');
			label.textContent = importedFields[key];
			var value = document.createElement('div');
			value.className = 'satset-infrastruktur-readonly-value';
			value.textContent = displayValue(record.attributes[key]);
			field.appendChild(label);
			field.appendChild(value);
			importedContainer.appendChild(field);
		});

		Object.keys(record.custom_attribute || {}).forEach(function (name) {
			addCustomField(name, record.custom_attribute[name]);
		});
		$('#satset-infrastruktur-edit-modal').modal('show');
	}

	function focusRecordOnMap(id) {
		var layer = featureLayers.get(id);
		var record = records.find(function (item) {
			return item.id === id;
		});
		if (!layer || !record) {
			var selectedYear = $('#satset-infrastruktur-year').val();
			if (selectedYear && record && String(record.attributes.thn_data || '') !== selectedYear) {
				showPageAlert('Ruas ini berada di luar filter tahun aktif. Pilih tahun "' + displayValue(record.attributes.thn_data) + '" untuk menampilkannya di peta.', 'warning');
			} else {
				showPageAlert('Geometri data ini tidak tersedia untuk ditampilkan di peta.', 'warning');
			}
			return;
		}

		showPageAlert('');
		document.getElementById('satset-infrastruktur-map').scrollIntoView({ behavior: 'smooth', block: 'center' });
		window.setTimeout(function () {
			map.invalidateSize();
			map.once('moveend', function () {
				L.popup({ maxWidth: 420 })
					.setLatLng(getLineMidpoint(layer))
					.setContent(createPopupContent(record))
					.openOn(map);
			});
			map.fitBounds(layer.getBounds(), { padding: [32, 32], maxZoom: 16 });
		}, 250);
	}

	function initializeTable() {
		var columns = [
			{ data: 'attributes.nm_ruas', render: escapeHtml },
			{ data: 'attributes.wilayah', render: escapeHtml }
		];
		if (satsetInfrastructure.isPublic) {
			columns.push(
				{ data: 'attributes.kecamatan', render: escapeHtml },
				{ data: 'attributes.desa_kel', render: escapeHtml },
				{ data: 'attributes.status', render: escapeHtml },
				{ data: 'attributes.thn_data', render: escapeHtml },
				{
					data: null,
					orderable: false,
					searchable: false,
					render: function (data, type, record) {
						if (type !== 'display') {
							return '';
						}
						return '<div class="satset-infrastruktur-actions"><button type="button" class="btn btn-danger btn-sm satset-infrastruktur-map-focus" data-record-id="' + Number(record.id) + '">Map</button></div>';
					}
				}
			);
		} else {
			columns.push(
				{ data: 'attributes.propinsi', render: escapeHtml },
				{ data: 'attributes.kab_kot', render: escapeHtml },
				{ data: 'attributes.kecamatan', render: escapeHtml },
				{ data: 'attributes.desa_kel', render: escapeHtml },
				{ data: 'attributes.status', render: escapeHtml },
				{ data: 'attributes.thn_data', render: escapeHtml },
				{
					data: null,
					orderable: false,
					searchable: false,
					render: function (data, type, record) {
						if (type !== 'display') {
							return '';
						}
						return '<div class="satset-infrastruktur-actions">' +
							'<button type="button" class="btn btn-danger btn-sm satset-infrastruktur-map-focus" data-record-id="' + Number(record.id) + '">Map</button>' +
							'<button type="button" class="btn btn-primary btn-sm satset-infrastruktur-edit" data-record-id="' + Number(record.id) + '">Edit</button>' +
							'</div>';
					}
				}
			);
		}

		table = $('#satset-infrastruktur-table').DataTable({
			data: records,
			pageLength: 25,
			lengthMenu: [[25, 50, 100, -1], [25, 50, 100, 'Semua']],
			order: [[0, 'asc']],
			autoWidth: false,
			language: {
				emptyTable: 'Belum ada data infrastruktur.',
				info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ data',
				infoEmpty: 'Tidak ada data untuk ditampilkan',
				infoFiltered: '(disaring dari _MAX_ total data)',
				lengthMenu: 'Tampilkan _MENU_ data',
				loadingRecords: 'Memuat data...',
				processing: 'Memproses...',
				search: 'Cari:',
				zeroRecords: 'Data tidak ditemukan',
				paginate: {
					first: 'Pertama',
					last: 'Terakhir',
					next: 'Berikutnya',
					previous: 'Sebelumnya'
				}
			},
			columns: columns
		});

		$.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
			if (!settings.nTable || settings.nTable.id !== 'satset-infrastruktur-table') {
				return true;
			}
			var year = $('#satset-infrastruktur-year').val();
			if (!year) {
				return true;
			}
			var row = table.row(dataIndex).data();
			return row && String(row.attributes.thn_data || '') === year;
		});
	}

	function loadData() {
		var requestData = {
			action: satsetInfrastructure.isPublic ? 'satset_get_public_infrastruktur' : 'satset_get_data_infrastruktur'
		};
		if (!satsetInfrastructure.isPublic) {
			requestData.nonce = satsetInfrastructure.nonce;
		}
		$.ajax({
			url: satsetInfrastructure.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: requestData
		}).done(function (response) {
			if (!response || !response.success || !response.data) {
				showPageAlert(response && response.data && response.data.message ? response.data.message : 'Data infrastruktur gagal dimuat.');
				$('#satset-infrastruktur-map-status').text('Data peta gagal dimuat.').show();
				return;
			}

			records = response.data.records || [];
			importedFields = response.data.fields || {};
			buildRegionColors();
			(response.data.years || []).forEach(function (year) {
				var option = document.createElement('option');
				option.value = year;
				option.textContent = year;
				document.getElementById('satset-infrastruktur-year').appendChild(option);
			});

			var warnings = [];
			if (response.data.invalid_geometry_count > 0) {
				warnings.push(response.data.invalid_geometry_count + ' data tidak memiliki geometri GeoJSON valid dan tidak ditampilkan pada peta.');
			}
			if (response.data.invalid_custom_attribute_count > 0) {
				warnings.push(response.data.invalid_custom_attribute_count + ' data memiliki JSON atribut custom yang tidak valid.');
			}
			if (warnings.length) {
				showPageAlert(warnings.join(' '), 'warning');
			}
			initializeTable();
			renderMap('');
		}).fail(function (xhr) {
			var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
				? xhr.responseJSON.data.message
				: 'Data infrastruktur gagal dimuat. Periksa koneksi lalu muat ulang halaman.';
			showPageAlert(message);
			$('#satset-infrastruktur-map-status').text('Data peta gagal dimuat.').show();
		});
	}

	$(function () {
		if (!window.L) {
			showPageAlert('Library peta Leaflet gagal dimuat. Periksa koneksi lalu muat ulang halaman.');
			$('#satset-infrastruktur-map-status').text('Library peta Leaflet gagal dimuat.').show();
			return;
		}

		map = L.map('satset-infrastruktur-map', { 
			preferCanvas: true,
			attributionControl: false 
		}).setView([-2.5, 118], 5);
		baseLayers.osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19
		});
		currentBaseLayer = baseLayers.osm.addTo(map);
		addGoogleBaseLayers();

		$('#satset-infrastruktur-basemap').on('change', function () {
			changeBaseLayer(this.value);
		});

		$('#satset-infrastruktur-my-location').on('click', function () {
			if (!navigator.geolocation) {
				showMapStatus('Lokasi memerlukan browser yang mendukung geolocation dan koneksi aman (HTTPS).', 7000);
				return;
			}

			var locationButton = $(this);
			locationButton.prop('disabled', true).text('Mencari lokasi...');
			showMapStatus('Meminta izin dan mencari lokasi Anda...', 0);
			navigator.geolocation.getCurrentPosition(function (position) {
				var location = L.latLng(position.coords.latitude, position.coords.longitude);
				map.setView(location, Math.max(map.getZoom(), 15), { animate: true });
				locationButton.prop('disabled', false).text('Lokasi saya');
				showMapStatus('Peta dipusatkan ke lokasi Anda.', 5000);
			}, function (error) {
				var message = 'Lokasi tidak dapat diperoleh.';
				if (error.code === 1) {
					message = 'Izin lokasi ditolak. Izinkan akses lokasi di browser untuk memusatkan peta ke lokasi Anda.';
				} else if (error.code === 2) {
					message = 'Lokasi Anda tidak tersedia. Periksa layanan lokasi perangkat.';
				} else if (error.code === 3) {
					message = 'Pencarian lokasi melewati batas waktu. Coba kembali.';
				}
				locationButton.prop('disabled', false).text('Lokasi saya');
				showMapStatus(message, 8000);
			}, {
				enableHighAccuracy: true,
				timeout: 15000,
				maximumAge: 60000
			});
		});

		$('#satset-infrastruktur-year').on('change', function () {
			if (table) {
				table.draw();
			}
			renderMap(this.value);
		});

		$('#satset-infrastruktur-table').on('click', '.satset-infrastruktur-map-focus', function () {
			focusRecordOnMap(Number($(this).attr('data-record-id')));
		});
		$('#satset-infrastruktur-table').on('click', '.satset-infrastruktur-edit', function () {
			var id = Number($(this).attr('data-record-id'));
			var record = records.find(function (item) {
				return item.id === id;
			});
			if (record) {
				openEditModal(record);
			}
		});

		$('#satset-infrastruktur-add-custom').on('click', function () {
			addCustomField('', '');
		});

		$('#satset-infrastruktur-save-custom').on('click', function () {
			if (!activeRecord) {
				return;
			}

			var customAttributes = Object.create(null);
			var invalidName = false;
			document.querySelectorAll('#satset-infrastruktur-custom-fields .satset-infrastruktur-custom-field').forEach(function (field) {
				var nameInput = field.querySelector('.satset-custom-name');
				var valueInput = field.querySelector('.satset-custom-value');
				var name = nameInput.value.trim();
				if (!name) {
					invalidName = true;
					nameInput.focus();
					return;
				}
				if (Object.prototype.hasOwnProperty.call(customAttributes, name)) {
					invalidName = true;
					nameInput.focus();
					return;
				}
				customAttributes[name] = valueInput.value;
			});
			if (invalidName) {
				showModalAlert('Isi nama setiap atribut custom dan pastikan tidak ada nama duplikat.');
				return;
			}

			var saveButton = $('#satset-infrastruktur-save-custom');
			saveButton.prop('disabled', true).text('Menyimpan...');
			showModalAlert('');
			$.ajax({
				url: satsetInfrastructure.ajaxUrl,
				method: 'POST',
				dataType: 'json',
				data: {
					action: 'satset_update_custom_infrastruktur',
					nonce: satsetInfrastructure.nonce,
					id: activeRecord.id,
					custom_attribute: JSON.stringify(customAttributes)
				}
			}).done(function (response) {
				if (!response || !response.success) {
					showModalAlert(response && response.data && response.data.message ? response.data.message : 'Atribut custom gagal disimpan.');
					return;
				}
				activeRecord.custom_attribute = response.data.custom_attribute || {};
				var featureLayer = featureLayers.get(activeRecord.id);
				if (featureLayer && featureLayer.getPopup()) {
					featureLayer.setPopupContent(createPopupContent(activeRecord));
				}
				$('#satset-infrastruktur-edit-modal').modal('hide');
				showPageAlert(response.data.message, 'success');
			}).fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
					? xhr.responseJSON.data.message
					: 'Atribut custom gagal disimpan. Periksa koneksi lalu coba kembali.';
				showModalAlert(message);
			}).always(function () {
				saveButton.prop('disabled', false).text('Simpan Atribut Custom');
			});
		});

		map.whenReady(function () {
			loadData();
			window.setTimeout(function () {
				map.invalidateSize();
			}, 150);
		});
	});
})(jQuery);
