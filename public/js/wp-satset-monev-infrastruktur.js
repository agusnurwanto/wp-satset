(function ($) {
	'use strict';

	var records = [];
	var $page;
	var $form;
	var $editor;
	var $alert;
	var $editorAlert;
	var table;
	var infrastructure = [];
	var activeRequests = 0;

	function escapeHtml(value) {
		return String(value === null || value === undefined ? '' : value)
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/"/g, '&quot;')
			.replace(/'/g, '&#039;');
	}

	function showAlert($target, message, isError) {
		$target.removeClass('alert-danger alert-success')
			.addClass(isError ? 'alert-danger' : 'alert-success')
			.text(message)
			.prop('hidden', !message);
	}

	function request(action, data) {
		activeRequests++;
		jQuery('#wrap-loading').show();
		return $.ajax({
			url: satsetMonevInfrastructure.ajaxUrl,
			method: 'POST',
			dataType: 'json',
			data: $.extend({
				action: action,
				nonce: satsetMonevInfrastructure.nonce
			}, data || {})
		}).always(function () {
			activeRequests = Math.max(0, activeRequests - 1);
			if (activeRequests === 0) {
				jQuery('#wrap-loading').hide();
			}
		});
	}

	function packageName(record) {
		try {
			var packageData = JSON.parse(record.data_paket);
			if (!Array.isArray(packageData) && packageData && typeof packageData === 'object') {
				return packageData.nama || packageData.nama_paket || packageData.name || '(Tanpa nama paket)';
			}
		} catch (error) {
			return '(JSON paket tidak valid)';
		}
		return '(Paket pekerjaan)';
	}

	function packageDisplay(record) {
		try {
			var packageData = JSON.parse(record.data_paket);
			if (!packageData || typeof packageData !== 'object') {
				return '<strong>' + escapeHtml(packageName(record)) + '</strong>';
			}
			var details = Array.isArray(packageData) ? packageData : Object.keys(packageData).map(function (key) {
				return { key: key, value: packageData[key] };
			});
			if (Array.isArray(packageData)) {
				details = packageData.map(function (value, index) {
					return { key: String(index + 1), value: value };
				});
			}
			var rows = details.map(function (detail) {
				var key = detail.key;
				var value = detail.value;
				var label = key.replace(/_/g, ' ').replace(/\b\w/g, function (character) {
					return character.toUpperCase();
				});
				if (['nama', 'nama_paket', 'name'].indexOf(key) !== -1) {
					label = 'Nama Paket';
				}
				var displayValue = value !== null && typeof value === 'object'
					? flattenPackageValue(value)
					: String(value === null || value === undefined ? '' : value);
				return '<tr><th scope="row">' + escapeHtml(label) + '</th><td>' + escapeHtml(displayValue) + '</td></tr>';
			});
			return '<details class="satset-monev-infrastruktur-package-details">' +
				'<summary><strong>' + escapeHtml(packageName(record)) + '</strong><span>Lihat detail paket</span></summary>' +
				'<table class="satset-monev-infrastruktur-package-table"><tbody>' +
				(rows.length ? rows.join('') : '<tr><td>' + escapeHtml(packageName(record)) + '</td></tr>') +
				'</tbody></table></details>';
		} catch (error) {
			return '<strong>' + escapeHtml(packageName(record)) + '</strong>';
		}
	}

	function flattenPackageValue(value) {
		if (Array.isArray(value)) {
			return value.map(flattenPackageValue).join(', ');
		}
		if (value && typeof value === 'object') {
			return Object.keys(value).map(function (key) {
				return key.replace(/_/g, ' ') + ': ' + flattenPackageValue(value[key]);
			}).join('; ');
		}
		return String(value === null || value === undefined ? '' : value);
	}

	function infrastructureName(id) {
		var item = infrastructure.find(function (entry) {
			return String(entry.id) === String(id);
		});
		return item ? String(item.nm_ruas || '') : '';
	}

	function normalizeName(value) {
		return String(value || '').trim().replace(/\s+/g, ' ').toLocaleLowerCase();
	}

	function updateYearFilter() {
		var $select = $('#satset-monev-infrastruktur-year');
		var selectedYear = $select.val();
		var years = [];
		records.forEach(function (record) {
			var year = String(record.tahun_anggaran);
			if (years.indexOf(year) === -1) {
				years.push(year);
			}
		});
		years.sort(function (left, right) {
			return Number(right) - Number(left);
		});
		$select.empty().append($('<option>').val('').text('Semua Tahun'));
		years.forEach(function (year) {
			$select.append($('<option>').val(year).text(year));
		});
		$select.val(years.indexOf(selectedYear) !== -1 ? selectedYear : '');
	}

	function renderRecords() {
		var rows = records.map(function (record) {
			var mappedName = infrastructureName(record.infrastruktur_id);
			return [
				escapeHtml(record.tahun_anggaran),
				'<div class="satset-monev-infrastruktur-hierarchy">' +
					'<div><strong>' + escapeHtml(record.kode_program) + '</strong><br>' + escapeHtml(record.nama_program) + '</div>' +
					'<div><strong>' + escapeHtml(record.kode_kegiatan) + '</strong><br>' + escapeHtml(record.nama_kegiatan) + '</div>' +
					'<div><strong>' + escapeHtml(record.kode_sub_kegiatan) + '</strong><br>' + escapeHtml(record.nama_sub_kegiatan) + '</div>' +
				'</div>',
				packageDisplay(record),
				'<input type="search" class="satset-monev-infrastruktur-mapping" list="satset-monev-infrastruktur-options" ' +
					'data-record-id="' + Number(record.id) + '" value="' + escapeHtml(mappedName) + '" ' +
					'aria-label="Data Infrastruktur untuk ' + escapeHtml(packageName(record)) + '" placeholder="Cari nama ruas">',
				escapeHtml(record.created_at),
				'<div class="satset-monev-infrastruktur-row-actions">' +
					'<button type="button" class="btn btn-sm btn-primary satset-monev-infrastruktur-edit" data-record-id="' + Number(record.id) + '">Edit</button>' +
					'<button type="button" class="btn btn-sm btn-danger satset-monev-infrastruktur-delete" data-record-id="' + Number(record.id) + '">Hapus</button>' +
				'</div>',
				String(record.data_paket || '')
			];
		});

		table.clear().rows.add(rows).draw();
		table.column(0).search($('#satset-monev-infrastruktur-year').val(), false, true);
		table.search($('#satset-monev-infrastruktur-search').val().trim());
		table.draw();
	}

	function loadRecords(successMessage) {
		showAlert($alert, 'Memuat data Monev...', false);
		request('satset_get_monev_infrastruktur').done(function (response) {
			if (!response || !response.success || !response.data || !Array.isArray(response.data.records)) {
				showAlert($alert, response && response.data && response.data.message ? response.data.message : 'Data Monev Infrastruktur gagal dimuat.', true);
				table.clear().draw();
				return;
			}
			records = response.data.records;
			infrastructure = Array.isArray(response.data.infrastructure) ? response.data.infrastructure : [];
			var $options = $('#satset-monev-infrastruktur-options').empty();
			infrastructure.forEach(function (item) {
				$options.append($('<option>').attr('value', item.nm_ruas));
			});
			showAlert($alert, successMessage || '', false);
			updateYearFilter();
			renderRecords();
		}).fail(function (xhr) {
			var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
				? xhr.responseJSON.data.message
				: 'Data Monev Infrastruktur gagal dimuat. Periksa koneksi lalu muat ulang halaman.';
			showAlert($alert, message, true);
			table.clear().draw();
		});
	}

	function setField(name, value) {
		$form.find('[name="' + name + '"]').val(value === null || value === undefined ? '' : value);
	}

	function openEditor(record) {
		$form[0].reset();
		setField('id', record ? record.id : '');
		setField('tahun_anggaran', record ? record.tahun_anggaran : new Date().getFullYear());
		setField('urutan_paket', record ? record.urutan_paket : 0);
		[
			'kode_program', 'nama_program', 'ringkasan_program',
			'kode_kegiatan', 'nama_kegiatan', 'ringkasan_kegiatan',
			'kode_sub_kegiatan', 'nama_sub_kegiatan', 'ringkasan_sub_kegiatan'
		].forEach(function (field) {
			setField(field, record ? record[field] : '');
		});

		var packageJson = '{\n  "nama": ""\n}';
		if (record) {
			try {
				packageJson = JSON.stringify(JSON.parse(record.data_paket), null, 2);
			} catch (error) {
				showAlert($editorAlert, 'Data JSON paket pada record ini tidak valid.', true);
			}
		}
		setField('data_paket', packageJson);
		$('#satset-monev-infrastruktur-editor-title').text(record ? 'Edit Paket Pekerjaan' : 'Tambah Paket Pekerjaan');
		showAlert($editorAlert, '', true);
		$editor.modal('show');
	}

	function closeEditor() {
		$editor.modal('hide');
		$form[0].reset();
		showAlert($editorAlert, '', true);
	}

	function getFormData() {
		var formData = {};
		$form.serializeArray().forEach(function (field) {
			formData[field.name] = field.value;
		});
		var packageData;
		try {
			packageData = JSON.parse(formData.data_paket);
		} catch (error) {
			throw new Error('Data paket harus berupa JSON yang valid.');
		}
		if (!packageData || typeof packageData !== 'object') {
			throw new Error('Data paket harus berupa objek atau daftar JSON.');
		}
		formData.data_paket = JSON.stringify(packageData);
		return formData;
	}

	$(function () {
		$page = $('.satset-monev-infrastruktur-page');
		if (!$page.length) {
			return;
		}
		$form = $('#satset-monev-infrastruktur-form');
		$editor = $('#satset-monev-infrastruktur-editor');
		$alert = $('#satset-monev-infrastruktur-alert');
		$editorAlert = $('#satset-monev-infrastruktur-editor-alert');
		table = $('#satset-monev-infrastruktur-table').DataTable({
			autoWidth: false,
			pageLength: 25,
			order: [[0, 'desc']],
			columnDefs: [
				{ targets: [5, 3], orderable: false, searchable: false },
				{
					targets: 6,
					visible: false,
					searchable: true,
					render: function (data, type) {
						return type === 'display' ? escapeHtml(data) : data;
					}
				}
			],
			language: {
				emptyTable: 'Tidak ada data paket pekerjaan untuk ditampilkan.',
				info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ paket',
				infoEmpty: 'Menampilkan 0 sampai 0 dari 0 paket',
				infoFiltered: '(disaring dari _MAX_ total paket)',
				lengthMenu: 'Tampilkan _MENU_ paket',
				search: 'Cari:',
				zeroRecords: 'Tidak ada paket yang cocok.',
				paginate: {
					first: 'Pertama',
					last: 'Terakhir',
					next: 'Berikutnya',
					previous: 'Sebelumnya'
				}
			}
		});

		$('#satset-monev-infrastruktur-table').on('change', '.satset-monev-infrastruktur-mapping', function () {
			var $input = $(this);
			var id = Number($input.attr('data-record-id'));
			var record = records.find(function (item) {
				return Number(item.id) === id;
			});
			if (!record) {
				return;
			}
			var enteredName = $input.val();
			var matched = infrastructure.find(function (item) {
				return normalizeName(item.nm_ruas) === normalizeName(enteredName);
			});
			if (enteredName && !matched) {
				$input.val(infrastructureName(record.infrastruktur_id));
				showAlert($alert, 'Pilih nama ruas dari daftar Data Infrastruktur.', true);
				return;
			}
			var mappedId = matched ? matched.id : '';
			$input.prop('disabled', true);
			request('satset_map_monev_infrastruktur', {
				id: id,
				infrastruktur_id: mappedId
			}).done(function (response) {
				if (!response || !response.success) {
					$input.val(infrastructureName(record.infrastruktur_id));
					showAlert($alert, response && response.data && response.data.message ? response.data.message : 'Pemetaan Data Infrastruktur gagal disimpan.', true);
					return;
				}
				record.infrastruktur_id = mappedId;
				showAlert($alert, response.data.message || 'Pemetaan Data Infrastruktur berhasil disimpan.', false);
			}).fail(function (xhr) {
				$input.val(infrastructureName(record.infrastruktur_id));
				var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
					? xhr.responseJSON.data.message
					: 'Pemetaan Data Infrastruktur gagal disimpan.';
				showAlert($alert, message, true);
			}).always(function () {
				$input.prop('disabled', false);
			});
		});

		$('#satset-monev-infrastruktur-add').on('click', function () {
			openEditor(null);
		});
		$('#satset-monev-infrastruktur-auto-map').on('click', function () {
			var $button = $(this);
			$button.prop('disabled', true).text('Memetakan...');
			request('satset_auto_map_monev_infrastruktur').done(function (response) {
				if (!response || !response.success) {
					showAlert($alert, response && response.data && response.data.message ? response.data.message : 'Pemetaan otomatis gagal.', true);
					return;
				}
				loadRecords(response.data.message || 'Pemetaan otomatis selesai.');
			}).fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
					? xhr.responseJSON.data.message
					: 'Pemetaan otomatis gagal.';
				showAlert($alert, message, true);
			}).always(function () {
				$button.prop('disabled', false).text('Mapping Otomatis Paket Pekerjaan');
			});
		});
		$('#satset-monev-infrastruktur-year').on('change', renderRecords);
		$('#satset-monev-infrastruktur-search').on('input', function () {
			table.search($(this).val().trim()).draw();
		});
		$editor.on('hidden.bs.modal', function () {
			$form[0].reset();
			showAlert($editorAlert, '', true);
		});

		$('#satset-monev-infrastruktur-table').on('click', '.satset-monev-infrastruktur-edit', function () {
			var id = Number($(this).attr('data-record-id'));
			var record = records.find(function (item) {
				return Number(item.id) === id;
			});
			if (record) {
				openEditor(record);
			}
		});

		$('#satset-monev-infrastruktur-table').on('click', '.satset-monev-infrastruktur-delete', function () {
			var id = Number($(this).attr('data-record-id'));
			var record = records.find(function (item) {
				return Number(item.id) === id;
			});
			if (!record || !window.confirm('Hapus paket "' + packageName(record) + '"? Tindakan ini tidak dapat dibatalkan.')) {
				return;
			}
			request('satset_delete_monev_infrastruktur', { id: id }).done(function (response) {
				if (!response || !response.success) {
					showAlert($alert, response && response.data && response.data.message ? response.data.message : 'Paket pekerjaan gagal dihapus.', true);
					return;
				}
				loadRecords(response.data.message || 'Data paket pekerjaan berhasil dihapus.');
			}).fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
					? xhr.responseJSON.data.message
					: 'Paket pekerjaan gagal dihapus.';
				showAlert($alert, message, true);
			});
		});

		$form.on('submit', function (event) {
			event.preventDefault();
			if (!$form[0].reportValidity()) {
				return;
			}
			var formData;
			try {
				formData = getFormData();
			} catch (error) {
				showAlert($editorAlert, error.message, true);
				return;
			}
			var $save = $('#satset-monev-infrastruktur-save');
			$save.prop('disabled', true).text('Menyimpan...');
			showAlert($editorAlert, '', true);
			request('satset_save_monev_infrastruktur', formData).done(function (response) {
				if (!response || !response.success) {
					showAlert($editorAlert, response && response.data && response.data.message ? response.data.message : 'Data paket pekerjaan gagal disimpan.', true);
					return;
				}
				closeEditor();
				loadRecords(response.data.message || 'Data paket pekerjaan berhasil disimpan.');
			}).fail(function (xhr) {
				var message = xhr.responseJSON && xhr.responseJSON.data && xhr.responseJSON.data.message
					? xhr.responseJSON.data.message
					: 'Data paket pekerjaan gagal disimpan.';
				showAlert($editorAlert, message, true);
			}).always(function () {
				$save.prop('disabled', false).text('Simpan');
			});
		});

		loadRecords();
	});
})(jQuery);
