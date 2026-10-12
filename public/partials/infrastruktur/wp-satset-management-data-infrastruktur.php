<?php
if (!defined('WPINC') || !current_user_can('manage_options')) {
	die;
}

?>
<main class="satset-infrastruktur-page">
	<header class="satset-infrastruktur-header text-center">
		<h1>Management Data Infrastruktur</h1>
		<p class="satset-infrastruktur-intro">Data hasil impor GeoJSON bersifat hanya-baca. Admin dapat menambahkan dan mengelola atribut custom tanpa mengubah data impor.</p>
	</header>

	<div class="satset-infrastruktur-map-wrap">
		<div id="satset-infrastruktur-map" aria-label="Peta data infrastruktur"></div>
		<div class="satset-infrastruktur-map-controls" aria-label="Kontrol peta">
			<select id="satset-infrastruktur-basemap">
				<option value="osm">OpenStreetMap</option>
				<option value="google-streets">Google Maps Streets</option>
				<option value="google-satellite">Google Maps Satellite</option>
				<option value="google-hybrid">Google Maps Hybrid</option>
				<option value="google-terrain">Google Maps Terrain</option>
			</select>
			<button type="button" id="satset-infrastruktur-my-location" class="btn btn-primary btn-sm">Lokasi saya</button>
		</div>
		<div id="satset-infrastruktur-map-status" class="satset-infrastruktur-map-status" role="status" aria-live="polite">Memuat data peta...</div>
	</div>
	<div id="satset-infrastruktur-region-legend" class="satset-infrastruktur-region-legend" role="list" aria-label="Keterangan warna garis berdasarkan wilayah"></div>

	<section aria-labelledby="satset-infrastruktur-list-title">
		<h2 id="satset-infrastruktur-list-title" class="text-center">Daftar Infrastruktur</h2>
		<div class="satset-infrastruktur-controls text-center">
			<div>
				<label for="satset-infrastruktur-year">Tahun Anggaran (Thn_Data)</label>
				<select id="satset-infrastruktur-year">
					<option value="">Semua Tahun</option>
				</select>
			</div>
		</div>
		<div id="satset-infrastruktur-page-alert" class="alert alert-danger satset-infrastruktur-modal-alert" role="alert"></div>
		<div class="satset-infrastruktur-table-wrap">
			<table id="satset-infrastruktur-table" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Nama Ruas</th>
						<th>Wilayah</th>
						<th>Provinsi</th>
						<th>Kabupaten / Kota</th>
						<th>Kecamatan</th>
						<th>Desa / Kelurahan</th>
						<th>Status</th>
						<th>Tahun Anggaran</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</section>
</main>

<div class="modal fade" id="satset-infrastruktur-edit-modal" tabindex="-1" role="dialog" aria-labelledby="satset-infrastruktur-edit-title" aria-hidden="true">
	<div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title" id="satset-infrastruktur-edit-title">Atribut Infrastruktur</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				<div id="satset-infrastruktur-edit-alert" class="alert alert-danger satset-infrastruktur-modal-alert" role="alert"></div>
				<h6 class="satset-infrastruktur-section-title">Atribut Hasil Impor (Hanya Baca)</h6>
				<div id="satset-infrastruktur-imported-fields" class="satset-infrastruktur-readonly-grid"></div>
				<h6 class="satset-infrastruktur-section-title">Atribut Custom</h6>
				<p>Atribut di bawah ini hanya tersimpan pada ruas ini. Atribut impor tidak dapat diubah dari halaman ini.</p>
				<div id="satset-infrastruktur-custom-fields" class="satset-infrastruktur-custom-grid"></div>
				<button type="button" id="satset-infrastruktur-add-custom" class="btn btn-outline-primary btn-sm">Tambah Atribut Custom</button>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
				<button type="button" id="satset-infrastruktur-save-custom" class="btn btn-primary">Simpan Atribut Custom</button>
			</div>
		</div>
	</div>
</div>
