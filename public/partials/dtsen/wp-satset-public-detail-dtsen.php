<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$id_wilayah = !empty($_GET['id_wilayah']) ? intval($_GET['id_wilayah']) : 0;

if (empty($id_wilayah)) {
    echo '<div class="alert alert-danger">ID Wilayah tidak ditemukan.</div>';
    return;
}

$data_wilayah = $wpdb->get_row(
    $wpdb->prepare(
        "
        SELECT *
        FROM data_dtsen_satset
        WHERE id_wilayah = %d
        LIMIT 1
        ",
        $id_wilayah
    ), ARRAY_A
);
?>
<style>
    .wp-satset-detail-dtsen table {
        border: 2px solid #343a40;
    }

    .wp-satset-detail-dtsen table th,
    .wp-satset-detail-dtsen table td {
        border: 1px solid #343a40 !important;
    }

    .table-responsive{
        overflow-x: auto;
        max-height: 70vh;
        overflow-y: auto;
    }
    #satset-table-kepala-keluarga thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #f8f9fa; /* Bootstrap light background */
    }
</style>
<div style="width:99%; margin:0 auto; padding-top:30px; padding-bottom:50px;">
    <h1 class="text-center">Data Detail DTSEN 
    <br>Desa <?php echo esc_html($data_wilayah['kelurahan']); ?>
    <br>Kecamatan <?php echo esc_html($data_wilayah['kecamatan']); ?>
    <br><?php echo $this->getNamaDaerah(); ?>
    <br><span id="total-keluarga"></span></h1>
    <div class="wp-satset-detail-dtsen">
        <div class="card mb-4">
            <div class="card-header" style="font-weight: bold;">
                Data Kepala Keluarga
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table id="satset-table-kepala-keluarga" class="table table-striped table-bordered mb-0" style="width:100%;">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>No. KK</th>
                                <th>NIK</th>
                                <th>Nama Kepala Keluarga</th>
                                <th>Alamat</th>
                                <th>RT/RW</th>
                                <th>Desil</th>
                                <th>Peringkat Nasional</th>
                                <th>Peringkat Provinsi</th>
                                <th>Peringkat Kab/Kota</th>
                                <th>Percentile Nasional</th>
                                <th>Status Nonaktif</th>
                                <th>Padan Bulan Ini</th>
                                <th>Update Terakhir</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<script>

jQuery(document).ready(function() {
    getTableKepalaKeluarga();
});

function getTableKepalaKeluarga() {

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'get_table_kepala_keluarga',
            api_key: '<?php echo get_option(SATSET_APIKEY); ?>',
            id_wilayah: <?php echo $id_wilayah; ?>
        },
        dataType: 'json',
        success: function(response) {
            jQuery('#wrap-loading').hide();
            console.log(response);

            if (response.status === 'success') {
                if (response.total !== undefined) {
                    jQuery('#total-keluarga').html('<b>Total: ' + response.total + ' Keluarga</b>');
                }

                // Hancurkan DataTable jika sudah ada sebelumnya agar bisa di-reinit
                if (jQuery.fn.DataTable.isDataTable('#satset-table-kepala-keluarga')) {
                    jQuery('#satset-table-kepala-keluarga').DataTable().destroy();
                }

                // Masukkan data ke dalam tbody
                jQuery('#satset-table-kepala-keluarga tbody').html(response.data);

                // Inisialisasi DataTable untuk fitur pencarian, sorting, dan pagination di sisi klien
                jQuery('#satset-table-kepala-keluarga').DataTable({
                    "pageLength": 10,
                    "ordering": true,
                    "searching": true,
                    "language": {
                        "search": "Cari:",
                        "lengthMenu": "Tampilkan _MENU_ data per halaman",
                        "zeroRecords": "Data tidak ditemukan",
                        "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                        "infoEmpty": "Tidak ada data yang tersedia",
                        "infoFiltered": "(difilter dari total _MAX_ data)",
                        "paginate": {
                            "first": "Pertama",
                            "last": "Terakhir",
                            "next": "Selanjutnya",
                            "previous": "Sebelumnya"
                        }
                    }
                });
            } else {
                alert(response.message);
            }
        },
        error: function(xhr) {
            jQuery('#wrap-loading').hide();
            console.error(xhr.responseText);
            alert('Terjadi kesalahan saat memuat data kepala keluarga!');
        }
    });
}
</script>