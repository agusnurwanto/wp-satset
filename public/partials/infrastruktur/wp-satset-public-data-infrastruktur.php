<?php
if (!defined('WPINC')) {
	die;
}
?>
<main class="satset-infrastruktur-page">
	<header class="satset-infrastruktur-header text-center">
		<h1>Data Infrastruktur</h1>
		<p class="satset-infrastruktur-intro">Peta dan ringkasan data infrastruktur untuk publik.</p>
	</header>

	<div class="satset-infrastruktur-map-wrap">
		<div id="satset-infrastruktur-map" aria-label="Peta data infrastruktur"></div>
		<div class="satset-infrastruktur-map-controls" aria-label="Kontrol peta">
			<select id="satset-infrastruktur-basemap" aria-label="Pilih peta dasar">
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
				<label for="satset-infrastruktur-year">Tahun Data</label>
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
						<th>Kecamatan</th>
						<th>Desa / Kelurahan</th>
						<th>Status</th>
						<th>Tahun Data</th>
						<th>Peta</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
	</section>
</main>
