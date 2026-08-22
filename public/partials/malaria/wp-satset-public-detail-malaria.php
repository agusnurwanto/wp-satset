<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$id_wilayah     = !empty($_GET['id_wilayah']) ? sanitize_text_field($_GET['id_wilayah']) : '';
$tahun_anggaran = !empty($_GET['tahun_anggaran'])
    ? (int) $_GET['tahun_anggaran']
    : (int) get_option('_crb_tahun_satset');

if (empty($id_wilayah)) {
    echo '<div class="alert alert-danger">ID Wilayah tidak ditemukan.</div>';
    return;
}

// Info header wilayah
$info_wilayah = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT
            a.provinsi  AS provinsi,
            a.kabkot    AS kab_kot,
            a.kecamatan AS kecamatan,
            a.desa      AS desa
        FROM data_malaria a
        INNER JOIN mapping_malaria m
            ON CONCAT_WS('-', a.provinsi, a.kabkot, a.kecamatan, a.desa) = m.kode_desa_malaria
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
    echo '<div class="alert alert-warning">Data Malaria untuk wilayah ini tidak ditemukan.</div>';
    return;
}

// Semua pasien Malaria untuk wilayah & tahun ini
$rows = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT
            a.id,
            a.nik,
            a.nama,
            a.umur,
            a.rt,
            a.rw,
            a.desa,
            a.kecamatan,
            a.kabkot,
            a.provinsi,
            a.tindak_lanjut,
            a.hasil_akhir,
            a.status_pengobatan,
            a.update_at
        FROM data_malaria a
        INNER JOIN mapping_malaria m
            ON CONCAT_WS('-', a.provinsi, a.kabkot, a.kecamatan, a.desa) = m.kode_desa_malaria
        WHERE m.kode_desa_satset = %s
            AND a.active = 1
            AND a.tahun_anggaran = %d
        ORDER BY a.nama ASC",
        $id_wilayah,
        $tahun_anggaran
    ),
    ARRAY_A
);

$total = count($rows);

$body = '';
$no   = 1;
foreach ($rows as $r) {
    $body .= "
        <tr>
            <td class='text-center'>" . $no++ . "</td>
            <td>" . esc_html($r['nik']) . "</td>
            <td>" . esc_html($r['nama']) . "</td>
            <td class='text-center'>" . esc_html($r['umur']) . "</td>
            <td class='text-center'>" . esc_html($r['rt']) . "/" . esc_html($r['rw']) . "</td>
            <td>" . esc_html($r['desa']) . "</td>
            <td>" . esc_html($r['kecamatan']) . "</td>
            <td>" . esc_html($r['kabkot']) . "</td>
            <td>" . esc_html($r['tindak_lanjut']) . "</td>
            <td>" . esc_html($r['hasil_akhir']) . "</td>
            <td>" . esc_html($r['status_pengobatan']) . "</td>
            <td class='text-center'>" . esc_html($r['update_at']) . "</td>
        </tr>
    ";
}
?>
<style>
    .wp-satset-detail-malaria table {
        border: 2px solid #343a40;
    }
    .wp-satset-detail-malaria table th,
    .wp-satset-detail-malaria table td {
        border: 1px solid #343a40 !important;
        vertical-align: middle;
    }
    .table-responsive {
        overflow-x: auto;
        max-height: 75vh;
        overflow-y: auto;
    }
    #malaria-detail-table thead th {
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
        Data Detail Malaria<br>
        <?php echo esc_html($info_wilayah['desa']); ?><br>
        <?php echo esc_html($info_wilayah['kecamatan']); ?><br>
        <?php echo $this->getNamaDaerah(); ?><br>
        <small style="font-size:30px; font-weight:normal;">
            Tahun Anggaran: <b><?php echo esc_html($tahun_anggaran); ?></b> &mdash;
            Total: <b><?php echo $this->number_format($total); ?> Pasien</b>
        </small>
    </h1>

    <div class="wp-satset-detail-malaria">
        <div class="table-responsive">
            <table id="malaria-detail-table" class="table table-striped table-bordered mb-0" style="width:100%;">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th class="text-center">Umur</th>
                        <th class="text-center">RT/RW</th>
                        <th>Desa</th>
                        <th>Kecamatan</th>
                        <th>Kab/Kota</th>
                        <th>Tindak Lanjut</th>
                        <th>Hasil Akhir</th>
                        <th>Status Pengobatan</th>
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
    jQuery('#malaria-detail-table').dataTable({
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Semua"]],
        order: [[2, 'asc']],
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
