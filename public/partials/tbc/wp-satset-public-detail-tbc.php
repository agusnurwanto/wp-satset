<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$id_wilayah = !empty($_GET['id_wilayah']) ? sanitize_text_field($_GET['id_wilayah']) : '';

if (empty($id_wilayah)) {
    echo '<div class="alert alert-danger">ID Wilayah tidak ditemukan.</div>';
    return;
}

if (!empty($_GET['tahun_anggaran'])) {
    $tahun_anggaran = intval($_GET['tahun_anggaran']);
} else {
    $tahun_anggaran = intval(get_option('_crb_tahun_satset'));
}

// Ambil info header wilayah dari satu baris TBC
$info_wilayah = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT provinsi, kabkot, kecamatan, desa
         FROM data_tbc
         WHERE kdwil = %s AND tahun_anggaran = %d AND active = 1
         LIMIT 1",
        $id_wilayah,
        $tahun_anggaran
    ),
    ARRAY_A
);

if (empty($info_wilayah)) {
    echo '<div class="alert alert-warning">Data TBC untuk wilayah ini tidak ditemukan.</div>';
    return;
}

// Ambil semua record TBC untuk wilayah & tahun ini
$rows = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT
            id,
            tanggal_register,
            no_reg_kabkot,
            no_reg_fasyankes,
            nik,
            nama,
            umur,
            jenis_kelamin,
            alamat,
            pindahan_dari_fasyankes,
            tindak_lanjut,
            tanggal_mulai_pengobatan,
            hasil_akhir_pengobatan,
            status_pengobatan,
            keterangan,
            update_at
        FROM data_tbc
        WHERE kdwil = %s AND tahun_anggaran = %d AND active = 1
        ORDER BY nama ASC",
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
    $jk = strtoupper(trim($r['jenis_kelamin']));
    $jk_label = ($jk === 'L') ? 'Laki-laki' : (($jk === 'P') ? 'Perempuan' : esc_html($r['jenis_kelamin']));

    $tindak_lanjut  = trim($r['tindak_lanjut'])  !== '' ? esc_html($r['tindak_lanjut'])  : '<em class="text-muted">(Tidak Ada)</em>';
    $hasil_akhir    = trim($r['hasil_akhir_pengobatan']) !== '' ? esc_html($r['hasil_akhir_pengobatan']) : '-';
    $status_pengob  = trim($r['status_pengobatan'])      !== '' ? esc_html($r['status_pengobatan'])      : '-';
    $pindahan       = trim($r['pindahan_dari_fasyankes']) !== '' ? esc_html($r['pindahan_dari_fasyankes']): '-';
    $keterangan     = trim($r['keterangan'])              !== '' ? esc_html($r['keterangan'])              : '-';

    $body .= "
        <tr>
            <td class='text-center'>" . $no++ . "</td>
            <td class='text-center'>" . esc_html($r['tanggal_register']) . "</td>
            <td class='text-center'>" . esc_html($r['no_reg_kabkot']) . "</td>
            <td class='text-center'>" . esc_html($r['no_reg_fasyankes']) . "</td>
            <td>" . esc_html($r['nik']) . "</td>
            <td>" . esc_html($r['nama']) . "</td>
            <td class='text-center'>" . (int)$r['umur'] . "</td>
            <td class='text-center'>" . $jk_label . "</td>
            <td>" . esc_html($r['alamat']) . "</td>
            <td>" . $pindahan . "</td>
            <td>" . $tindak_lanjut . "</td>
            <td class='text-center'>" . esc_html($r['tanggal_mulai_pengobatan']) . "</td>
            <td class='text-center'>" . $hasil_akhir . "</td>
            <td class='text-center'>" . $status_pengob . "</td>
            <td>" . $keterangan . "</td>
            <td class='text-center'>" . esc_html($r['update_at']) . "</td>
        </tr>
    ";
}
?>
<style>
    .wp-satset-detail-tbc table {
        border: 2px solid #343a40;
    }
    .wp-satset-detail-tbc table th,
    .wp-satset-detail-tbc table td {
        border: 1px solid #343a40 !important;
        vertical-align: middle;
    }
    .table-responsive {
        overflow-x: auto;
        max-height: 75vh;
        overflow-y: auto;
    }
    #tbc-detail-table thead th {
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
        Data Detail TBC<br>
        <?php echo esc_html($info_wilayah['desa']); ?><br>
        <?php echo esc_html($info_wilayah['kecamatan']); ?><br>
        <?php echo $this->getNamaDaerah(); ?><br>
        <small style="font-size: 28px; font-weight: normal;">
            Tahun Anggaran: <b><?php echo $tahun_anggaran; ?></b>
            &nbsp;&mdash;&nbsp;
            Total: <b><?php echo $this->number_format($total); ?> Orang</b>
        </small>
    </h1>

    <div class="wp-satset-detail-tbc">
        <div class="table-responsive">
            <table id="tbc-detail-table" class="table table-striped table-bordered mb-0" style="width: 100%;">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th class="text-center">Tgl. Register</th>
                        <th class="text-center">No. Reg Kab</th>
                        <th class="text-center">No. Reg Fasyankes</th>
                        <th>NIK</th>
                        <th>Nama</th>
                        <th class="text-center">Umur</th>
                        <th class="text-center">Jenis Kelamin</th>
                        <th>Alamat</th>
                        <th>Pindahan dari Fasyankes</th>
                        <th>Tindak Lanjut</th>
                        <th class="text-center">Tgl. Mulai Pengobatan</th>
                        <th class="text-center">Hasil Akhir Pengobatan</th>
                        <th class="text-center">Status Pengobatan</th>
                        <th>Keterangan</th>
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
    jQuery('#tbc-detail-table').dataTable({
        lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "Semua"]],
        order: [[5, 'asc']],
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
