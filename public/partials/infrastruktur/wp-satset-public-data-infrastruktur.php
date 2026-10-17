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
				<label for="satset-infrastruktur-year">Tahun Pekerjaan</label>
				<select id="satset-infrastruktur-year">
					<option value="">Semua Tahun</option>
				</select>
			</div>
			<div>
				<label for="satset-infrastruktur-function">Fungsi Jalan</label>
				<select id="satset-infrastruktur-function">
					<option value="">Semua Fungsi Jalan</option>
				</select>
			</div>
			<div>
				<label for="satset-infrastruktur-length-group">Kelompok Panjang Jalan</label>
				<select id="satset-infrastruktur-length-group">
					<option value="">Semua Panjang</option>
					<option value="under-1">Kurang dari 1 km</option>
					<option value="1-to-5">1–5 km</option>
					<option value="over-5">Lebih dari 5 km</option>
				</select>
			</div>
			<div>
				<label for="satset-infrastruktur-good-percentage">Persentase Baik + Sedang</label>
				<select id="satset-infrastruktur-good-percentage">
					<option value="">Semua Persentase</option>
					<option value="under-50">Kurang dari 50%</option>
					<option value="50-to-80">50–80%</option>
					<option value="over-80">Lebih dari 80%</option>
				</select>
			</div>
		</div>
		<div id="satset-infrastruktur-page-alert" class="alert alert-danger satset-infrastruktur-modal-alert" role="alert"></div>
		<div class="satset-infrastruktur-table-wrap">
			<table id="satset-infrastruktur-table" class="table table-striped table-bordered">
				<thead>
					<tr>
						<th>Nama Ruas</th>
						<th>Wilayah - Status</th>
						<th>Kabupaten / Kota, Provinsi</th>
						<th>Kecamatan</th>
						<th>Desa / Kelurahan</th>
						<th>Fungsi Jalan</th>
						<th>Panjang (Km)</th>
						<th>Kondisi Jalan</th>
						<th>Tahun Pekerjaan</th>
						<th>Aksi</th>
					</tr>
				</thead>
				<tbody></tbody>
			</table>
		</div>
		<aside class="satset-infrastruktur-function-legend" aria-labelledby="satset-infrastruktur-function-legend-title">
			<h3 id="satset-infrastruktur-function-legend-title">Keterangan Singkatan</h3>
			<div class="satset-infrastruktur-function-legend-grid">
				<dl>
					<dt>PKN</dt><dd>Pusat Kegiatan Nasional</dd>
					<dt>PKW</dt><dd>Pusat Kegiatan Wilayah</dd>
					<dt>PKL</dt><dd>Pusat Kegiatan Lokal</dd>
					<dt>PKLing</dt><dd>Pusat Kegiatan Lingkungan</dd>
					<dt>PKSN</dt><dd>Pusat Kawasan Strategis Nasional</dd>
					<dt>KSN</dt><dd>Kawasan Strategis Nasional</dd>
					<dt>KSP</dt><dd>Kawasan Strategis Provinsi</dd>
					<dt>KSK</dt><dd>Kawasan Strategis Kabupaten</dd>
					<dt>BU/UP</dt><dd>Bandar Udara Utama/Pengumpul</dd>
					<dt>PL</dt><dd>Pelabuhan Utama/Pengumpul</dd>
					<dt>JAP</dt><dd>Jalan Arteri Primer</dd>
					<dt>JKP</dt><dd>Jalan Kolektor Primer</dd>
					<dt>JLP</dt><dd>Jalan Lokal Primer</dd>
					<dt>Jling.P</dt><dd>Jalan Lingkungan Primer</dd>
					<dt>JNS</dt><dd>Jalan Strategis Nasional</dd>
					<dt>JSP</dt><dd>Jalan Strategis Provinsi</dd>
					<dt>JSK</dt><dd>Jalan Strategis Kabupaten</dd>
					<dt>DR</dt><dd>Daerah Rawan</dd>
					<dt>APN</dt><dd>Aset Penting Negara</dd>
					<dt>PN</dt><dd>Pertahanan dan Keamanan</dd>
				</dl>
				<div>
					<h4>Jenis Jalan Kolektor Primer</h4>
					<dl>
						<dt>JKP-1</dt><dd>JKP yang menghubungkan antar ibu kota provinsi</dd>
						<dt>JKP-2</dt><dd>JKP yang menghubungkan ibu kota provinsi dengan ibu kota kabupaten/kota</dd>
						<dt>JKP-3</dt><dd>JKP yang menghubungkan antar ibu kota kabupaten/kota</dd>
						<dt>JKP-4</dt><dd>JKP yang menghubungkan ibu kota kabupaten/kota dengan ibu kota kecamatan</dd>
					</dl>
				</div>
			</div>
		</aside>
	</section>
</main>
