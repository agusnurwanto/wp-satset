<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$id_wilayah = !empty($_GET['id_wilayah']) ? sanitize_text_field($_GET['id_wilayah']) : '';
$tahun_anggaran = !empty($_GET['tahun_anggaran'])
    ? (int) $_GET['tahun_anggaran']
    : (int) get_option('_crb_tahun_satset');

if (empty($id_wilayah)) {
    echo '<div class="alert alert-danger">ID Wilayah tidak ditemukan.</div>';
    return;
}

// Ambil satu baris untuk info header wilayah
$info_wilayah = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT
            a.provinsi_domisili  AS provinsi,
            a.kabkot_domisili    AS kab_kot,
            a.kecamatan_domisili AS kecamatan,
            a.desa_domisili      AS desa
        FROM data_aids a
        INNER JOIN mapping_aids m
            ON CONCAT_WS('-', a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili) = m.kode_desa_aids
        WHERE m.kode_desa_satset = %s
            AND a.active = 1
            AND a.tahun_anggaran = %d
        LIMIT 1",
        $id_wilayah,
        $tahun_anggaran
    ),
    ARRAY_A
);

if (empty($info_wilayah)) {
    echo '<div class="alert alert-warning">Data AIDS untuk wilayah ini tidak ditemukan.</div>';
    return;
}

// Ambil semua pasien AIDS untuk wilayah & tahun anggaran ini
$rows = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT
            a.id,
            a.id_pasien,
            a.nik,
            a.nama_pasien,
            a.jenis_kelamin,
            a.tanggal_lahir,
            a.umur_terdiagnosis,
            a.kelompok_umur_terdiagnosis,
            a.warga_negara,
            a.no_telp,
            a.provinsi_pasien,
            a.kabkot_pasien,
            a.kecamatan_pasien,
            a.desa_pasien,
            a.alamat_pasien,
            a.provinsi_domisili,
            a.kabkot_domisili,
            a.kecamatan_domisili,
            a.desa_domisili,
            a.alamat_domisili,
            a.kode_upk,
            a.nama_upk,
            a.no_rekam_medik,
            a.tanggal_register,
            a.kel_populasi_lsl,
            a.konfirmasi_hiv_plus_tanggal_konfirmasi,
            a.konfirmasi_hiv_plus_layanan,
            a.pendampingan_komunitas,
            a.capaian_t_dan_t_layanan,
            a.update_at
        FROM data_aids a
        INNER JOIN mapping_aids m
            ON CONCAT_WS('-', a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili) = m.kode_desa_aids
        WHERE m.kode_desa_satset = %s
            AND a.active = 1
            AND a.tahun_anggaran = %d
        ORDER BY a.nama_pasien ASC",
        $id_wilayah,
        $tahun_anggaran
    ),
    ARRAY_A
);

$total = count($rows);

// Build baris tabel
$body = '';
$no   = 1;
foreach ($rows as $r) {
    $jk       = strtoupper(trim((string) $r['jenis_kelamin']));
    $jk_label = in_array($jk, array('L', 'LAKI-LAKI', 'LAKI', 'M', 'MALE'))
                    ? 'Laki-laki'
                    : (in_array($jk, array('P', 'PEREMPUAN', 'F', 'FEMALE'))
                        ? 'Perempuan'
                        : esc_html($r['jenis_kelamin']));

    $lsl  = ($r['kel_populasi_lsl'] === null || $r['kel_populasi_lsl'] === '')
                ? '-' : ($r['kel_populasi_lsl'] ? 'Ya' : 'Tidak');
    $damp = ($r['pendampingan_komunitas'] === null || $r['pendampingan_komunitas'] === '')
                ? '-' : ($r['pendampingan_komunitas'] ? 'Ya' : 'Tidak');

    $body .= "
        <tr>
            <td class='text-center'>" . $no++ . "</td>
            <td>" . esc_html($r['id_pasien']) . "</td>
            <td>" . esc_html($r['nik']) . "</td>
            <td>" . esc_html($r['nama_pasien']) . "</td>
            <td class='text-center'>" . $jk_label . "</td>
            <td class='text-center'>" . esc_html($r['tanggal_lahir']) . "</td>
            <td class='text-center'>" . esc_html($r['umur_terdiagnosis']) . "</td>
            <td>" . esc_html($r['kelompok_umur_terdiagnosis']) . "</td>
            <td>" . esc_html($r['warga_negara']) . "</td>
            <td>" . esc_html($r['no_telp']) . "</td>
            <td>" . esc_html($r['alamat_domisili']) . "</td>
            <td>" . esc_html($r['kode_upk']) . "</td>
            <td>" . esc_html($r['nama_upk']) . "</td>
            <td>" . esc_html($r['no_rekam_medik']) . "</td>
            <td class='text-center'>" . esc_html($r['tanggal_register']) . "</td>
            <td class='text-center'>" . $lsl . "</td>
            <td>" . esc_html($r['konfirmasi_hiv_plus_tanggal_konfirmasi']) . "</td>
            <td>" . esc_html($r['konfirmasi_hiv_plus_layanan']) . "</td>
            <td class='text-center'>" . $damp . "</td>
            <td>" . esc_html($r['capaian_t_dan_t_layanan']) . "</td>
            <td class='text-center'>" . esc_html($r['update_at']) . "</td>
        </tr>
    ";
}
?>
<style>
    .wp-satset-detail-aids table {
        border: 2px solid #343a40;
    }
    .wp-satset-detail-aids table th,
    .wp-satset-detail-aids table td {
        border: 1px solid #343a40 !important;
        vertical-align: middle;
    }
    .table-responsive {
        overflow-x: auto;
        max-height: 75vh;
        overflow-y: auto;
    }
    #aids-detail-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #343a40;
        color: #fff;
        white-space: nowrap;
    }
</style>

<div style="width: 99%; margin: 0 auto; padding-top: 30px; padding-bottom: 50px;">
    <h1 class="text-center">
        Data Detail AIDS/HIV<br>
        <?php echo esc_html($info_wilayah['desa']); ?><br>
        <?php echo esc_html($info_wilayah['kecamatan']); ?><br>
        <?php echo $this->getNamaDaerah(); ?><br>
        <small style="font-size:30px; font-weight:normal;">
            Tahun Anggaran: <b><?php echo esc_html($tahun_anggaran); ?></b> &mdash;
            Total: <b><?php echo $this->number_format($total); ?> Pasien</b>
        </small>
    </h1>

    <div class="wp-satset-detail-aids">
        <div class="table-responsive">
            <table id="aids-detail-table" class="table table-striped table-bordered mb-0" style="width:100%;">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>ID Pasien</th>
                        <th>NIK</th>
                        <th>Nama Pasien</th>
                        <th class="text-center">Jenis Kelamin</th>
                        <th class="text-center">Tgl Lahir</th>
                        <th class="text-center">Umur Terdiagnosis</th>
                        <th>Kelompok Umur</th>
                        <th>Warga Negara</th>
                        <th>No. Telp</th>
                        <th>Alamat Domisili</th>
                        <th>Kode UPK</th>
                        <th>Nama UPK</th>
                        <th>No. Rekam Medik</th>
                        <th class="text-center">Tgl Register</th>
                        <th class="text-center">Kel. Populasi LSL</th>
                        <th>Konfirmasi HIV+ Tgl</th>
                        <th>Konfirmasi HIV+ Layanan</th>
                        <th class="text-center">Pendampingan Komunitas</th>
                        <th>Capaian T&amp;T Layanan</th>
                        <th class="text-center">Update Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $body; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function () {
    jQuery('#aids-detail-table').dataTable({
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Semua"]],
        order: [[3, 'asc']],
        language: {
            search:         "Cari:",
            lengthMenu:     "Tampilkan _MENU_ data per halaman",
            zeroRecords:    "Data tidak ditemukan",
            info:           "Menampilkan halaman _PAGE_ dari _PAGES_",
            infoEmpty:      "Tidak ada data yang tersedia",
            infoFiltered:   "(difilter dari total _MAX_ data)",
            paginate: {
                first:    "Pertama",
                last:     "Terakhir",
                next:     "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });
});
</script>
