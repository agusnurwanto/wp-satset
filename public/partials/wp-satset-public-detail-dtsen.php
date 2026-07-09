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

$nama_desa = $wpdb->get_var(
    $wpdb->prepare(
        "
        SELECT kelurahan
        FROM data_dtsen_satset
        WHERE id_wilayah = %d
        LIMIT 1
        ",
        $id_wilayah
    )
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

    .table-wrapper{
        height: 70vh;
        overflow-y: auto;
    }

    .table-responsive{
        overflow-x: auto;
    }
</style>
<div style="width:99%; margin:0 auto; padding-top:30px; padding-bottom:50px;">
    <h1 class="text-center">Data Detail DTSEN 
    <br>Desa <?php echo esc_html($nama_desa); ?>
    <br><?php echo $this->getNamaDaerah(); ?></h1>
    <div class="wp-satset-detail-dtsen">
        <div class="card mb-4">
            <div class="card-header" style="font-weight: bold;">
                Data Kepala Keluarga
            </div>

            <div class="card-body p-0">
                <div class="table-wrapper">
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
                jQuery('#satset-table-kepala-keluarga tbody').html(response.data);
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