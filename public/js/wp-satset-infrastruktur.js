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
	var customFieldId = 0;
	var customAttributeNames = [];

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

	function getWorkYears(record) {
		return Array.isArray(record.work_years) ? record.work_years.map(String) : [];
	}

	function getWorkYearLabel(record) {
		var years = getWorkYears(record);
		return years.length ? years.join(', ') : '-';
	}

	function matchesWorkYear(record, year) {
		return !year || getWorkYears(record).indexOf(year) !== -1;
	}

	function parseNumericValue(value) {
		if (typeof value === 'number') {
			return Number.isFinite(value) ? value : null;
		}
		if (value === null || value === undefined) {
			return null;
		}

		var normalized = String(value).trim().replace(/[^\d.,+-]/g, '');
		if (!normalized) {
			return null;
		}
		var commaIndex = normalized.lastIndexOf(',');
		var dotIndex = normalized.lastIndexOf('.');
		if (commaIndex !== -1 && dotIndex !== -1) {
			var decimalSeparator = commaIndex > dotIndex ? ',' : '.';
			var thousandsSeparator = decimalSeparator === ',' ? '.' : ',';
			normalized = normalized.split(thousandsSeparator).join('');
			if (decimalSeparator === ',') {
				normalized = normalized.replace(',', '.');
			}
		} else if (commaIndex !== -1) {
			normalized = (normalized.match(/,/g) || []).length > 1
				? normalized.split(',').join('')
				: normalized.replace(',', '.');
		} else if ((normalized.match(/\./g) || []).length > 1) {
			normalized = normalized.split('.').join('');
		}
		var numericValue = Number(normalized);
		return Number.isFinite(numericValue) ? numericValue : null;
	}

	function getFilterAttributes(record) {
		return record.filter_attributes || record.attributes || {};
	}

	function getRoadLength(record) {
		return parseNumericValue(getFilterAttributes(record).panjang);
	}

	function getGoodConditionPercentage(record) {
		var attributes = getFilterAttributes(record);
		var roadLength = getRoadLength(record);
		var goodLength = parseNumericValue(attributes.kon_baik);
		var fairLength = parseNumericValue(attributes.kon_sdg);
		if (roadLength === null || roadLength <= 0 || goodLength === null || fairLength === null) {
			return null;
		}
		return (goodLength + fairLength) / roadLength * 100;
	}

	function matchesLengthGroup(record, group) {
		if (!group) {
			return true;
		}
		var length = getRoadLength(record);
		if (length === null) {
			return false;
		}
		if (group === 'under-1') {
			return length < 1;
		}
		if (group === '1-to-5') {
			return length >= 1 && length <= 5;
		}
		return length > 5;
	}

	function matchesGoodPercentageGroup(record, group) {
		if (!group) {
			return true;
		}
		var percentage = getGoodConditionPercentage(record);
		if (percentage === null) {
			return false;
		}
		if (group === 'under-50') {
			return percentage < 50;
		}
		if (group === '50-to-80') {
			return percentage >= 50 && percentage <= 80;
		}
		return percentage > 80;
	}

	function matchesManagementFilters(record) {
		var selectedFunction = $('#satset-infrastruktur-function').val() || '';
		var selectedLengthGroup = $('#satset-infrastruktur-length-group').val() || '';
		var selectedGoodPercentageGroup = $('#satset-infrastruktur-good-percentage').val() || '';
		var roadFunction = String(getFilterAttributes(record).fungsi || '').trim();
		return (!selectedFunction || roadFunction === selectedFunction)
			&& matchesLengthGroup(record, selectedLengthGroup)
			&& matchesGoodPercentageGroup(record, selectedGoodPercentageGroup);
	}

	function getRoadFunctionLabel(roadFunction) {
		var functionNames = {
			JAP: 'Jalan Arteri Primer',
			JKP: 'Jalan Kolektor Primer',
			JLP: 'Jalan Lokal Primer',
			'JLING.P': 'Jalan Lingkungan Primer',
			JNS: 'Jalan Strategis Nasional',
			JSP: 'Jalan Strategis Provinsi',
			JSK: 'Jalan Strategis Kabupaten',
			DR: 'Daerah Rawan',
			APN: 'Aset Penting Negara',
			PN: 'Pertahanan dan Keamanan',
			PKN: 'Pusat Kegiatan Nasional',
			PKW: 'Pusat Kegiatan Wilayah',
			PKL: 'Pusat Kegiatan Lokal',
			PKLING: 'Pusat Kegiatan Lingkungan',
			PKSN: 'Pusat Kawasan Strategis Nasional',
			KSN: 'Kawasan Strategis Nasional',
			KSP: 'Kawasan Strategis Provinsi',
			KSK: 'Kawasan Strategis Kabupaten',
			'BU/UP': 'Bandar Udara Utama/Pengumpul',
			PL: 'Pelabuhan Utama/Pengumpul',
			'JKP-1': 'JKP yang menghubungkan antar ibu kota provinsi',
			'JKP-2': 'JKP yang menghubungkan ibu kota provinsi dengan ibu kota kabupaten/kota',
			'JKP-3': 'JKP yang menghubungkan antar ibu kota kabupaten/kota',
			'JKP-4': 'JKP yang menghubungkan ibu kota kabupaten/kota dengan ibu kota kecamatan',
			JAS: 'Jalan Arteri Sekunder'
		};
		var code = String(roadFunction || '').trim();
		var name = functionNames[code.toUpperCase()];
		return name ? code + ' (' + name + ')' : code;
	}

	function formatLocation(record) {
		var attributes = getFilterAttributes(record);
		var district = String(attributes.kab_kot || '').trim();
		var province = String(attributes.propinsi || '').trim();
		if (district && province) {
			return district + ', ' + province;
		}
		return district || province || '-';
	}

	function formatRegionAndStatus(record) {
		var attributes = record.attributes || {};
		var region = String(attributes.wilayah || '').trim();
		var status = String(attributes.status || '').trim();
		if (region && !/^wilayah(?:\s|$)/i.test(region)) {
			region = 'Wilayah ' + region;
		}
		if (region && status) {
			return region + ' - ' + status;
		}
		return region || status || '-';
	}

	function formatRoadLength(record) {
		var length = getRoadLength(record);
		return length === null ? '-' : length.toLocaleString('id-ID', { maximumFractionDigits: 2 }) + ' km';
	}

	function getRoadConditionLines(record) {
		var percentage = getGoodConditionPercentage(record);
		var attributes = getFilterAttributes(record);
		return [
			'Persentase Baik + Sedang: ' + (percentage === null ? '-' : percentage.toLocaleString('id-ID', { maximumFractionDigits: 1 }) + '%'),
			'Baik: ' + displayValue(attributes.kon_baik),
			'Sedang: ' + displayValue(attributes.kon_sdg),
			'Ringan: ' + displayValue(attributes.kon_rgn),
			'Rusak Berat: ' + displayValue(attributes.kon_rusak)
		];
	}

	function renderRoadCondition(record, type) {
		var percentage = getGoodConditionPercentage(record);
		if (type !== 'display') {
			return percentage === null ? '' : percentage;
		}
		var lines = getRoadConditionLines(record).map(function (line, index) {
			return (index ? '<hr>' : '') + '<div>' +
				(index === 0 ? '<strong>' + escapeHtml(line) + '</strong>' : escapeHtml(line)) +
				'</div>';
		});
		return '<div class="satset-infrastruktur-condition-cell">' + lines.join('') + '</div>';
	}

	function populateManagementFilters() {
		var functionSelect = document.getElementById('satset-infrastruktur-function');
		var roadFunctions = Array.from(new Set(records.map(function (record) {
			return String(getFilterAttributes(record).fungsi || '').trim();
		}).filter(Boolean))).sort(function (left, right) {
			return left.localeCompare(right, 'id');
		});
		roadFunctions.forEach(function (roadFunction) {
			var option = document.createElement('option');
			option.value = roadFunction;
			option.textContent = getRoadFunctionLabel(roadFunction);
			functionSelect.appendChild(option);
		});
	}

	function formatPackageKey(key) {
		var normalizedKey = String(key).toLowerCase();
		if (['nama', 'nama_paket', 'name'].indexOf(normalizedKey) !== -1) {
			return 'Nama Paket';
		}
		if (['penyedia', 'nama_penyedia', 'vendor'].indexOf(normalizedKey) !== -1) {
			return 'Penyedia';
		}
		return String(key)
			.replace(/([a-z0-9])([A-Z])/g, '$1 $2')
			.replace(/[_-]+/g, ' ')
			.replace(/\b\w/g, function (character) {
				return character.toUpperCase();
			});
	}

	function getPackageName(packageRecord) {
		if (packageRecord.nama_paket !== undefined && packageRecord.nama_paket !== null && packageRecord.nama_paket !== '') {
			return String(packageRecord.nama_paket);
		}
		var packageData = packageRecord.data_paket;
		if (packageData && typeof packageData === 'object' && !Array.isArray(packageData)) {
			for (var keyIndex = 0; keyIndex < 3; keyIndex++) {
				var key = ['nama', 'nama_paket', 'name'][keyIndex];
				if (packageData[key] !== undefined && packageData[key] !== null && packageData[key] !== '') {
					return String(packageData[key]);
				}
			}
		}
		return 'Paket Pekerjaan';
	}

	function flattenPackageValue(value) {
		if (Array.isArray(value)) {
			return value.map(flattenPackageValue).join(', ');
		}
		if (value && typeof value === 'object') {
			return Object.keys(value).map(function (key) {
				return formatPackageKey(key) + ': ' + flattenPackageValue(value[key]);
			}).join('; ');
		}
		return displayValue(value);
	}

	function appendPackageTable(container, packageRecord) {
		var tableElement = document.createElement('table');
		var body = document.createElement('tbody');
		var yearRow = document.createElement('tr');
		var yearLabel = document.createElement('th');
		var yearValue = document.createElement('td');
		yearLabel.textContent = 'Tahun Pekerjaan';
		yearValue.textContent = displayValue(packageRecord.tahun_anggaran);
		yearRow.appendChild(yearLabel);
		yearRow.appendChild(yearValue);
		body.appendChild(yearRow);

		var packageData = packageRecord.data_paket;
		var details = Array.isArray(packageData)
			? packageData.map(function (value, index) {
				return { key: 'Data Paket ' + (index + 1), value: value };
			})
			: Object.keys(packageData || {}).map(function (key) {
				return { key: formatPackageKey(key), value: packageData[key] };
			});
		details.forEach(function (detail) {
			var row = document.createElement('tr');
			var label = document.createElement('th');
			var value = document.createElement('td');
			label.textContent = detail.key;
			value.textContent = flattenPackageValue(detail.value);
			row.appendChild(label);
			row.appendChild(value);
			body.appendChild(row);
		});
		if (!details.length) {
			var emptyRow = document.createElement('tr');
			var emptyCell = document.createElement('td');
			emptyCell.colSpan = 2;
			emptyCell.textContent = 'Tidak ada detail paket.';
			emptyRow.appendChild(emptyCell);
			body.appendChild(emptyRow);
		}
		tableElement.appendChild(body);
		container.appendChild(tableElement);
	}

	function appendWorkPackages(container, packages, expandable) {
		var packageList = Array.isArray(packages) ? packages : [];
		var wrapper = document.createElement(expandable ? 'details' : 'div');
		wrapper.className = 'satset-infrastruktur-work-package-list';
		if (expandable) {
			var summary = document.createElement('summary');
			summary.textContent = 'Paket Pekerjaan (' + packageList.length + ')';
			wrapper.appendChild(summary);
		}
		if (!packageList.length) {
			var emptyMessage = document.createElement('p');
			emptyMessage.textContent = 'Belum ada paket pekerjaan yang dipetakan.';
			wrapper.appendChild(emptyMessage);
		}
		packageList.forEach(function (packageRecord) {
			var packageCard = document.createElement('div');
			packageCard.className = 'satset-infrastruktur-work-package';
			var heading = document.createElement('h6');
			heading.textContent = getPackageName(packageRecord) + ' (' + displayValue(packageRecord.tahun_anggaran) + ')';
			packageCard.appendChild(heading);
			appendPackageTable(packageCard, packageRecord);
			wrapper.appendChild(packageCard);
		});
		container.appendChild(wrapper);
	}

	function renderWorkPackageSummary(record, type) {
		var packages = Array.isArray(record.work_packages) ? record.work_packages : [];
		if (!packages.length) {
			return type === 'display' ? '—' : '';
		}
		if (type !== 'display') {
			return packages.map(function (packageRecord) {
				return displayValue(packageRecord.tahun_anggaran) + ' - ' + getPackageName(packageRecord);
			}).join(' ');
		}
		return packages.map(function (packageRecord) {
			return '<div>' + escapeHtml(displayValue(packageRecord.tahun_anggaran)) + ' - ' +
				escapeHtml(getPackageName(packageRecord)) + '</div>';
		}).join('');
	}

	function refreshCustomAttributeNameOptions() {
		var optionList = document.getElementById('satset-infrastruktur-custom-name-options');
		if (!optionList) {
			return;
		}

		var namesByKey = Object.create(null);
		records.forEach(function (record) {
			Object.keys(record.custom_attribute || {}).forEach(function (name) {
				var key = name.toLowerCase();
				if (!Object.prototype.hasOwnProperty.call(namesByKey, key)) {
					namesByKey[key] = name;
				}
			});
		});
		customAttributeNames = Object.keys(namesByKey).map(function (key) {
			return namesByKey[key];
		}).sort(function (left, right) {
			return left.localeCompare(right);
		});

		optionList.replaceChildren();
		customAttributeNames.forEach(function (name) {
			var option = document.createElement('option');
			option.value = name;
			optionList.appendChild(option);
		});
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

	function appendDetailTable(container, title, attributes, labels, headingLink) {
		var section = document.createElement('section');
		var heading = document.createElement('h6');
		if (headingLink) {
			heading.appendChild(headingLink);
			section.appendChild(heading);
		} else if (title) {
			heading.textContent = title;
			section.appendChild(heading);
		}

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

	function getSafeHttpUrl(value) {
		try {
			var url = new URL(String(value), window.location.href);
			return url.protocol === 'http:' || url.protocol === 'https:' ? url.href : '';
		} catch (error) {
			return '';
		}
	}

	function appendCustomDetailTable(container, attributes) {
		var section = document.createElement('section');
		var heading = document.createElement('h6');
		heading.textContent = 'Atribut Custom';
		section.appendChild(heading);

		var tableElement = document.createElement('table');
		var body = document.createElement('tbody');
		Object.keys(attributes || {}).forEach(function (key) {
			var attribute = attributes[key];
			if (!attribute || typeof attribute !== 'object') {
				attribute = { type: 'text', value: attribute };
			}
			var type = ['url', 'image'].indexOf(attribute.type) !== -1 ? attribute.type : 'text';
			var rawValue = attribute.value;
			var row = document.createElement('tr');
			var label = document.createElement('th');
			var value = document.createElement('td');
			label.textContent = key;

			if (type === 'url' && rawValue) {
				var safeUrl = getSafeHttpUrl(rawValue);
				if (safeUrl) {
					var link = document.createElement('a');
					link.href = safeUrl;
					link.target = '_blank';
					link.rel = 'noopener noreferrer';
					link.textContent = String(rawValue);
					value.appendChild(link);
				} else {
					value.textContent = displayValue(rawValue);
				}
			} else if (type === 'image' && rawValue) {
				var imageUrl = getSafeHttpUrl(rawValue);
				if (imageUrl) {
					var image = document.createElement('img');
					image.src = imageUrl;
					image.alt = key;
					image.loading = 'lazy';
					image.className = 'satset-infrastruktur-custom-image';
					value.appendChild(image);
				} else {
					value.textContent = displayValue(rawValue);
				}
			} else {
				value.textContent = displayValue(rawValue);
			}
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

	function createStreetViewLink(latLng) {
		if (
			!latLng ||
			!Number.isFinite(latLng.lat) ||
			!Number.isFinite(latLng.lng) ||
			latLng.lat < -90 ||
			latLng.lat > 90 ||
			latLng.lng < -180 ||
			latLng.lng > 180
		) {
			return null;
		}

		var link = document.createElement('a');
		link.className = 'satset-infrastruktur-street-view-link';
		link.href = 'https://www.google.com/maps/@?api=1&map_action=pano&viewpoint=' +
			encodeURIComponent(latLng.lat + ',' + latLng.lng);
		link.target = '_blank';
		link.rel = 'noopener noreferrer';
		link.textContent = 'Google Street View';
		return link;
	}

	function createPopupContent(record, latLng) {
		var popup = document.createElement('div');
		popup.className = 'satset-infrastruktur-popup';
		var streetViewLink = createStreetViewLink(latLng);
		var title = document.createElement('h4');
		title.textContent = displayValue(record.attributes.nm_ruas);
		popup.appendChild(title);
		if (satsetInfrastructure.isPublic) {
			var publicDetails = {
				'wilayah_status': formatRegionAndStatus(record),
				'kabupaten_provinsi': formatLocation(record),
				'kecamatan': displayValue(record.attributes.kecamatan),
				'desa_kelurahan': displayValue(record.attributes.desa_kel),
				'fungsi': displayValue(getFilterAttributes(record).fungsi),
				'panjang': formatRoadLength(record),
				'kondisi': getRoadConditionLines(record).join('\n'),
				'tahun_pekerjaan': getWorkYearLabel(record),
				'paket_pekerjaan': renderWorkPackageSummary(record, 'text')
			};
			var publicDetailLabels = {
				'wilayah_status': 'Wilayah - Status',
				'kabupaten_provinsi': 'Kabupaten / Kota, Provinsi',
				'kecamatan': 'Kecamatan',
				'desa_kelurahan': 'Desa / Kelurahan',
				'fungsi': 'Fungsi Jalan',
				'panjang': 'Panjang (Km)',
				'kondisi': 'Kondisi Jalan',
				'tahun_pekerjaan': 'Tahun Pekerjaan',
				'paket_pekerjaan': 'Paket Pekerjaan'
			};
			appendDetailTable(popup, null, publicDetails, publicDetailLabels, streetViewLink);
		} else {
			appendDetailTable(popup, null, record.attributes, importedFields, streetViewLink);
			appendWorkPackages(popup, record.work_packages, true);
			appendCustomDetailTable(popup, record.custom_attribute);
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
			return matchesWorkYear(record, year) && matchesManagementFilters(record) && record.geometry;
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
							.setContent(createPopupContent(record, event.latlng))
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
			showMapStatus('Tidak ada geometri untuk filter yang dipilih.', 0);
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

	function addCustomField(name, attribute) {
		attribute = attribute && typeof attribute === 'object' ? attribute : { type: 'text', value: attribute || '' };
		var container = document.getElementById('satset-infrastruktur-custom-fields');
		var field = document.createElement('div');
		field.className = 'satset-infrastruktur-custom-field';

		var nameLabel = document.createElement('label');
		nameLabel.textContent = 'Nama atribut';
		var nameInput = document.createElement('input');
		nameInput.type = 'text';
		nameInput.maxLength = 100;
		nameInput.className = 'satset-custom-name';
		nameInput.setAttribute('list', 'satset-infrastruktur-custom-name-options');
		nameInput.value = name || '';
		nameInput.required = true;

		var typeFieldset = document.createElement('fieldset');
		typeFieldset.className = 'satset-custom-type';
		var typeLegend = document.createElement('legend');
		typeLegend.textContent = 'Jenis atribut';
		typeFieldset.appendChild(typeLegend);
		var typeOptions = document.createElement('div');
		typeOptions.className = 'satset-custom-type-options';
		var fieldId = ++customFieldId;
		[
			{ value: 'text', label: 'Teks biasa' },
			{ value: 'url', label: 'Link URL' },
			{ value: 'image', label: 'Gambar' }
		].forEach(function (option) {
			var optionLabel = document.createElement('label');
			var typeInput = document.createElement('input');
			typeInput.type = 'radio';
			typeInput.name = 'satset-custom-type-' + fieldId;
			typeInput.value = option.value;
			typeInput.className = 'satset-custom-type-value';
			typeInput.checked = (attribute.type || 'text') === option.value;
			optionLabel.appendChild(typeInput);
			optionLabel.appendChild(document.createTextNode(option.label));
			typeOptions.appendChild(optionLabel);
		});
		typeFieldset.appendChild(typeOptions);

		var valueLabel = document.createElement('label');
		valueLabel.textContent = 'Nilai';
		var valueInput = document.createElement('textarea');
		valueInput.maxLength = 5000;
		valueInput.className = 'satset-custom-value';
		valueInput.value = attribute.value || '';

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
		field.appendChild(typeFieldset);
		field.appendChild(valueLabel);
		field.appendChild(valueInput);
		field.appendChild(removeButton);
		container.appendChild(field);
	}

	function openEditModal(record) {
		activeRecord = record;
		showModalAlert('');
		var importedContainer = document.getElementById('satset-infrastruktur-imported-fields');
		var workPackagesContainer = document.getElementById('satset-infrastruktur-work-packages');
		var customContainer = document.getElementById('satset-infrastruktur-custom-fields');
		importedContainer.replaceChildren();
		workPackagesContainer.replaceChildren();
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

		appendWorkPackages(workPackagesContainer, record.work_packages, false);
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
			if (selectedYear && record && !matchesWorkYear(record, selectedYear)) {
				showPageAlert('Ruas ini berada di luar filter tahun aktif. Pilih tahun pekerjaan "' + getWorkYearLabel(record) + '" untuk menampilkannya di peta.', 'warning');
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
				var popupLatLng = getLineMidpoint(layer);
				L.popup({ maxWidth: 420 })
					.setLatLng(popupLatLng)
					.setContent(createPopupContent(record, popupLatLng))
					.openOn(map);
			});
			map.fitBounds(layer.getBounds(), { padding: [32, 32], maxZoom: 16 });
		}, 250);
	}

	function initializeTable() {
		var columns = [{ data: 'attributes.nm_ruas', render: escapeHtml }];
		if (satsetInfrastructure.isPublic) {
			columns.push(
				{
					data: null,
					render: function (data, type, record) {
						return escapeHtml(formatRegionAndStatus(record));
					}
				},
				{
					data: null,
					render: function (data, type, record) {
						return escapeHtml(formatLocation(record));
					}
				},
				{ data: 'attributes.kecamatan', render: escapeHtml },
				{ data: 'attributes.desa_kel', render: escapeHtml },
				{
					data: null,
					render: function (data, type, record) {
						return escapeHtml(getFilterAttributes(record).fungsi || '');
					}
				},
				{
					data: null,
					render: function (data, type, record) {
						var length = getRoadLength(record);
						if (type !== 'display') {
							return length === null ? '' : length;
						}
						return escapeHtml(formatRoadLength(record));
					}
				},
				{
					data: null,
					render: function (data, type, record) {
						return renderRoadCondition(record, type);
					}
				},
				{
					data: null,
					render: function (data, type, record) {
						return escapeHtml(getWorkYearLabel(record));
					}
				},
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
							'</div>';
					}
				}
			);
		} else {
			columns.push(
				{
					data: null,
					render: function (data, type, record) {
						return escapeHtml(formatRegionAndStatus(record));
					}
				},
				{
					data: null,
					render: function (data, type, record) {
						return escapeHtml(formatLocation(record));
					}
				},
				{ data: 'attributes.kecamatan', render: escapeHtml },
				{ data: 'attributes.desa_kel', render: escapeHtml },
				{ data: 'attributes.fungsi', render: escapeHtml },
				{
					data: null,
					render: function (data, type, record) {
						var length = getRoadLength(record);
						if (type !== 'display') {
							return length === null ? '' : length;
						}
						return escapeHtml(formatRoadLength(record));
					}
				},
				{
					data: null,
					render: function (data, type, record) {
						return renderRoadCondition(record, type);
					}
				},
				{
					data: null,
					render: function (data, type, record) {
						return escapeHtml(getWorkYearLabel(record));
					}
				},
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
			var row = table.row(dataIndex).data();
			return row && matchesWorkYear(row, year) && matchesManagementFilters(row);
		});
	}

	function loadData() {
		var requestData = {
			action: satsetInfrastructure.isPublic ? 'satset_get_public_infrastruktur' : 'satset_get_data_infrastruktur'
		};
		$('#warp-loading').show();
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
			refreshCustomAttributeNameOptions();
			buildRegionColors();
			(response.data.years || []).forEach(function (year) {
				var option = document.createElement('option');
				option.value = year;
				option.textContent = year;
				document.getElementById('satset-infrastruktur-year').appendChild(option);
			});
			populateManagementFilters();

			var warnings = [];
			if (response.data.invalid_geometry_count > 0) {
				warnings.push(response.data.invalid_geometry_count + ' data tidak memiliki geometri GeoJSON valid dan tidak ditampilkan pada peta.');
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
		}).always(function () {
			$('#warp-loading').hide();
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
		$('#satset-infrastruktur-function, #satset-infrastruktur-length-group, #satset-infrastruktur-good-percentage').on('change', function () {
			if (table) {
				table.draw();
				renderMap($('#satset-infrastruktur-year').val());
			}
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

			var customAttributes = [];
			var invalidName = false;
			document.querySelectorAll('#satset-infrastruktur-custom-fields .satset-infrastruktur-custom-field').forEach(function (field) {
				var nameInput = field.querySelector('.satset-custom-name');
				var valueInput = field.querySelector('.satset-custom-value');
				var typeInput = field.querySelector('.satset-custom-type-value:checked');
				var name = nameInput.value.trim();
				if (!name) {
					invalidName = true;
					nameInput.focus();
					return;
				}
				if (customAttributes.some(function (attribute) {
					return attribute.name.toLowerCase() === name.toLowerCase();
				})) {
					invalidName = true;
					nameInput.focus();
					return;
				}
				customAttributes.push({
					name: name,
					type: typeInput ? typeInput.value : 'text',
					value: valueInput.value
				});
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
				refreshCustomAttributeNameOptions();
				var featureLayer = featureLayers.get(activeRecord.id);
				var featurePopup = featureLayer && featureLayer.getPopup();
				if (featurePopup) {
					featureLayer.setPopupContent(createPopupContent(activeRecord, featurePopup.getLatLng()));
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
