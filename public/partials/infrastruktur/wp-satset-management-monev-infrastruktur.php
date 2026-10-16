<?php
if (!defined('WPINC') || !current_user_can('manage_options')) {
	die;
}

$infrastruktur_management_page = get_page_by_path('management-data-infrastruktur', OBJECT, 'page');
?>
<main class="satset-monev-infrastruktur-page">
	<header class="satset-monev-infrastruktur-header text-center">
		<?php if ($infrastruktur_management_page) : ?>
			<p><a href="<?php echo esc_url(get_permalink($infrastruktur_management_page)); ?>">&larr; Kembali ke Data Infrastruktur</a></p>
		<?php endif; ?>
		<h1>Management Monev Pekerjaan Infrastruktur</h1>
		<p>Kelola paket pekerjaan dan hubungkan dengan ruas pada Data Infrastruktur.</p>
	</header>

	<section aria-labelledby="satset-monev-infrastruktur-list-title">
		<div class="satset-monev-infrastruktur-toolbar">
			<div>
				<label for="satset-monev-infrastruktur-year">Tahun Anggaran</label>
				<select id="satset-monev-infrastruktur-year">
					<option value="">Semua Tahun</option>
				</select>
			</div>
			<div>
				<label for="satset-monev-infrastruktur-search">Cari paket</label>
				<input type="search" id="satset-monev-infrastruktur-search" placeholder="Cari program atau paket">
			</div>
			<button type="button" id="satset-monev-infrastruktur-add" class="btn btn-primary">Tambah Paket</button>
			<button type="button" id="satset-monev-infrastruktur-auto-map" class="btn btn-outline-primary">Mapping Otomatis Paket Pekerjaan</button>
		</div>
		<div id="satset-monev-infrastruktur-alert" class="alert" role="status" aria-live="polite" hidden></div>
		<datalist id="satset-monev-infrastruktur-options"></datalist>
		<div class="satset-monev-infrastruktur-table-wrap">
			<table id="satset-monev-infrastruktur-table" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Tahun</th>
						<th>Sub Kegiatan</th>
						<th>Paket Pekerjaan</th>
						<th>Data Infrastruktur</th>
						<th>Dibuat</th>
						<th>Aksi</th>
						<th>Data Paket</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</section>
</main>

<div id="satset-monev-infrastruktur-editor" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="satset-monev-infrastruktur-editor-title" aria-hidden="true">
	<div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
		<form id="satset-monev-infrastruktur-form" class="modal-content">
			<div class="modal-header">
				<h5 id="satset-monev-infrastruktur-editor-title" class="modal-title">Tambah Paket Pekerjaan</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div id="satset-monev-infrastruktur-editor-alert" class="alert alert-danger" role="alert" hidden></div>
				<input type="hidden" name="id">
				<div class="satset-monev-infrastruktur-form-grid">
					<p>
						<label for="satset-monev-year-input">Tahun Anggaran</label>
						<input type="number" id="satset-monev-year-input" name="tahun_anggaran" min="1900" max="2155" step="1" required>
					</p>
					<p>
						<label for="satset-monev-order-input">Urutan Paket</label>
						<input type="number" id="satset-monev-order-input" name="urutan_paket" min="0" step="1" required>
					</p>
					<p>
						<label for="satset-monev-program-code">Kode Program</label>
						<input type="text" id="satset-monev-program-code" name="kode_program" maxlength="100" required>
					</p>
					<p>
						<label for="satset-monev-program-name">Nama Program</label>
						<input type="text" id="satset-monev-program-name" name="nama_program" required>
					</p>
					<p class="satset-monev-infrastruktur-json-field">
						<label for="satset-monev-program-summary">Ringkasan Program (JSON, opsional)</label>
						<textarea id="satset-monev-program-summary" name="ringkasan_program" rows="3" placeholder='Contoh: {"target": 10}'></textarea>
					</p>
					<p>
						<label for="satset-monev-activity-code">Kode Kegiatan</label>
						<input type="text" id="satset-monev-activity-code" name="kode_kegiatan" maxlength="100" required>
					</p>
					<p>
						<label for="satset-monev-activity-name">Nama Kegiatan</label>
						<input type="text" id="satset-monev-activity-name" name="nama_kegiatan" required>
					</p>
					<p class="satset-monev-infrastruktur-json-field">
						<label for="satset-monev-activity-summary">Ringkasan Kegiatan (JSON, opsional)</label>
						<textarea id="satset-monev-activity-summary" name="ringkasan_kegiatan" rows="3" placeholder='Contoh: {"target": 10}'></textarea>
					</p>
					<p>
						<label for="satset-monev-subactivity-code">Kode Subkegiatan</label>
						<input type="text" id="satset-monev-subactivity-code" name="kode_sub_kegiatan" maxlength="100" required>
					</p>
					<p>
						<label for="satset-monev-subactivity-name">Nama Subkegiatan</label>
						<input type="text" id="satset-monev-subactivity-name" name="nama_sub_kegiatan" required>
					</p>
					<p class="satset-monev-infrastruktur-json-field">
						<label for="satset-monev-subactivity-summary">Ringkasan Subkegiatan (JSON, opsional)</label>
						<textarea id="satset-monev-subactivity-summary" name="ringkasan_sub_kegiatan" rows="3" placeholder='Contoh: {"target": 10}'></textarea>
					</p>
				</div>
				<p class="satset-monev-infrastruktur-package-field">
					<label for="satset-monev-package-json">Data Paket Pekerjaan (JSON)</label>
					<textarea id="satset-monev-package-json" name="data_paket" rows="14" spellcheck="false" required></textarea>
					<small>Semua field paket dapat diedit di sini. Pertahankan format JSON objek atau daftar agar field tambahan tidak hilang.</small>
				</p>
			</div>
			<div class="satset-monev-infrastruktur-form-actions">
				<button type="submit" id="satset-monev-infrastruktur-save" class="btn btn-primary">Simpan</button>
				<button type="button" id="satset-monev-infrastruktur-cancel" class="btn btn-secondary" data-dismiss="modal">Batal</button>
			</div>
		</form>
	</div>
</div>
