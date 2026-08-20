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

// Ambil data wilayah dari satu baris ATS untuk info header
$info_wilayah = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT
            TRIM(provinsi)  AS provinsi,
            TRIM(kab_kot)   AS kab_kot,
            TRIM(kecamatan) AS kecamatan,
            TRIM(desa)      AS desa
        FROM data_ats_satset
        WHERE kdwil = %s AND active = 1
        LIMIT 1",
        $id_wilayah
    ),
    ARRAY_A
);

if (empty($info_wilayah)) {
    echo '<div class="alert alert-warning">Data ATS untuk wilayah ini tidak ditemukan.</div>';
    return;
}

// Ambil semua data ATS untuk wilayah ini
$rows = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT
            id,
            nisn,
            nama,
            jenis_kelamin,
            usia,
            nama_ayah,
            nama_ibu,
            alamat,
            npsn,
            nama_sekolah,
            tingkat_pendidikan,
            status,
            alasan_verifikasi,
            keterangan,
            update_at
        FROM data_ats_satset
        WHERE kdwil = %s AND active = 1
        ORDER BY nama ASC",
        $id_wilayah
    ),
    ARRAY_A
);

$total = count($rows);

// Label badge warna per status
$badge_status = array(
    'DO'  => 'danger',
    'LTM' => 'warning',
    'BPB' => 'info',
);

// Build baris tabel
$body = '';
$no   = 1;
foreach ($rows as $r) {
    $status = strtoupper(trim($r['status']));
    $badge  = isset($badge_status[$status]) ? $badge_status[$status] : 'secondary';
    $jk     = strtoupper(trim($r['jenis_kelamin']));
    $jk_label = ($jk === 'L') ? 'Laki-laki' : (($jk === 'P') ? 'Perempuan' : esc_html($r['jenis_kelamin']));

    $body .= "
        <tr>
            <td class='text-center'>" . $no++ . "</td>
            <td>" . esc_html($r['nisn']) . "</td>
            <td>" . esc_html($r['nama']) . "</td>
            <td class='text-center'>" . $jk_label . "</td>
            <td class='text-center'>" . (int) $r['usia'] . "</td>
            <td>" . esc_html($r['nama_ayah']) . "</td>
            <td>" . esc_html($r['nama_ibu']) . "</td>
            <td>" . esc_html($r['alamat']) . "</td>
            <td>" . esc_html($r['nama_sekolah']) . "</td>
            <td>" . esc_html($r['tingkat_pendidikan']) . "</td>
            <td class='text-center'>
                <span class='badge badge-" . $badge . "'>" . esc_html($r['status']) . "</span>
            </td>
            <td>" . esc_html($r['alasan_verifikasi']) . "</td>
            <td>" . esc_html($r['keterangan']) . "</td>
            <td class='text-center'>" . esc_html($r['update_at']) . "</td>
        </tr>
    ";
}
?>
<style>
    .wp-satset-detail-ats table {
        border: 2px solid #343a40;
    }
    .wp-satset-detail-ats table th,
    .wp-satset-detail-ats table td {
        border: 1px solid #343a40 !important;
        vertical-align: middle;
    }
    .table-responsive {
        overflow-x: auto;
        max-height: 75vh;
        overflow-y: auto;
    }
    #ats-detail-table thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #343a40;
        color: #fff;
        white-space: nowrap;
    }
    .badge-danger  { background-color: #dc3545; color: #fff; padding: 3px 7px; border-radius: 4px; }
    .badge-warning { background-color: #ffc107; color: #212529; padding: 3px 7px; border-radius: 4px; }
    .badge-info    { background-color: #17a2b8; color: #fff; padding: 3px 7px; border-radius: 4px; }
    .badge-secondary { background-color: #6c757d; color: #fff; padding: 3px 7px; border-radius: 4px; }
</style>

<div style="width: 99%; margin: 0 auto; padding-top: 30px; padding-bottom: 50px;">
    <h1 class="text-center">
        Data Detail ATS (Anak Tidak Sekolah)<br>
        <?php echo esc_html($info_wilayah['desa']); ?><br>
        <?php echo esc_html($info_wilayah['kecamatan']); ?><br>
        <?php echo $this->getNamaDaerah(); ?><br>
        <small style="font-size:30px; font-weight:normal;">
            Total: <b><?php echo $this->number_format($total); ?> Anak</b>
        </small>
    </h1>

    <div style="margin-bottom: 15px;" class="text-center">
        <span class="badge badge-danger">DO</span> Drop Out &nbsp;
        <span class="badge badge-warning">LTM</span> Lama Tidak Masuk &nbsp;
        <span class="badge badge-info">BPB</span> Belum Pernah Bersekolah
    </div>

    <div class="wp-satset-detail-ats">
        <div class="table-responsive">
            <table id="ats-detail-table" class="table table-striped table-bordered mb-0" style="width:100%;">
                <thead>
                    <tr>
                        <th class="text-center">No</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th class="text-center">Jenis Kelamin</th>
                        <th class="text-center">Usia</th>
                        <th>Nama Ayah</th>
                        <th>Nama Ibu</th>
                        <th>Alamat</th>
                        <th>Nama Sekolah</th>
                        <th>Tingkat Pendidikan</th>
                        <th class="text-center">Status</th>
                        <th>Alasan Verifikasi</th>
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
    jQuery('#ats-detail-table').dataTable({
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
