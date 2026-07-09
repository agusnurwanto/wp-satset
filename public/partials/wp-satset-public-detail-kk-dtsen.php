<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

$no_kk = !empty($_GET['no_kk']) ? sanitize_text_field($_GET['no_kk']) : '';

if (empty($no_kk)) {
    echo '<div class="alert alert-danger">No KK tidak ditemukan.</div>';
    return;
}

$data = $wpdb->get_row(
    $wpdb->prepare(
        "
        SELECT *
        FROM data_dtsen_satset
        WHERE no_kk = %s
        AND active = 1
        LIMIT 1
        ",
        $no_kk
    ),
    ARRAY_A
);

$data_anggota = $wpdb->get_results(
    $wpdb->prepare(
        "
        SELECT *
        FROM data_dtsen_anggota_keluarga
        WHERE no_kk = %s
        AND active = 1
        ORDER BY nama ASC
        ",
        $no_kk
    ),
    ARRAY_A
);


if (!$data) {
    echo '<div class="alert alert-warning">Data KK tidak ditemukan.</div>';
    return;
}

?>

<div id="hasil-tabel"></div>

<script>
        jQuery(document).ready(function() {
        getTableAnggotaKeluargaDtsen();
    });

    function getTableAnggotaKeluargaDtsen() {

        jQuery('#wrap-loading').show();

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            dataType: 'json',
            data: {
                action: 'get_table_anggota_keluarga_dtsen',
                api_key: '<?php echo get_option(SATSET_APIKEY); ?>',
                nik: '<?php echo esc_js($data['nik']); ?>'
            },
            success:function(response){
                jQuery('#wrap-loading').hide();

                console.log(response);

                if(response.status != 'success'){
                    alert(response.message);
                    return;
                }

                var data = response.data;

                jQuery('#hasil-tabel').html('');

                renderTable('1. Data Kepala Keluarga (data_dtsen)',                         'table-data-dtsen',             data.data_dtsen);
                renderTable('2. Anggota Keluarga (data_dtsen_anggota_keluarga)',            'table-data-anggota',           data.data_dtsen_anggota_keluarga);
                renderTable('3. Riwayat Bansos FOTO',                                       'table-riwayat-foto',           data.data_riwayat_bansos_foto);
                renderTable('4. Aset Keluarga / Rumah (data_dtsen_aset_keluarga)',          'table-aset-keluarga',          data.data_dtsen_aset_keluarga);
                renderTable('5. Aset Bergerak (data_dtsen_aset_bergerak)',                  'table-aset-bergerak',          data.data_dtsen_aset_bergerak);
                renderTable('6. Riwayat Bansos PKH',                                        'table-riwayat-pkh',            data.data_riwayat_bansos_pkh);
                renderTable('7. Detail Bansos PKH',                                         'table-detail-pkh',             data.data_detail_bansos_pkh);
                renderTable('8. Riwayat Bansos BPNT',                                       'table-riwayat-bpnt',           data.data_riwayat_bansos_bpnt);
                renderTable('9. Riwayat Bansos PBI',                                        'table-riwayat-pbi',            data.data_riwayat_bansos_pbi);
                renderTable('10. Detail Bansos PBI',                                        'table-detail-pbi',             data.data_detail_bansos_pbi);
                renderTable('11. Riwayat Bansos PLTS/KESRA',                                'table-riwayat-plts-kesra',     data.data_riwayat_bansos_plts_kesra);
                renderTable('12. Riwayat Bansos YAPI',                                      'table-riwayat-yapi',           data.data_riwayat_bansos_yapi);
                renderTable('13. Riwayat Bansos ATENSI',                                    'table-riwayat-atensi',         data.data_riwayat_bansos_atensi);
                renderTable('14. Usulan DTSEN (data_usulan_dtsen)',                         'table-usulan-dtsen',           data.data_usulan_dtsen);
                renderTable('15. Anggota Usulan DTSEN (data_usulan_dtsen_anggota_keluarga)','table-usulan-anggota',         data.data_usulan_dtsen_anggota_keluarga);
                renderTable('16. Rekap Jumlah DTSEN',                                       'table-rekap-jumlah-dtsen',     data.data_rekap_jumlah_dtsen);

            },
            error:function(xhr){

                jQuery('#wrap-loading').hide();

                console.log(xhr.responseText);

            }
        });

    }
    function renderTable(title, tableId, rows){

        let html = `
            <div class="card mt-3">
                <div class="card-header">
                    <h4>${title}</h4>
                </div>
                <div class="card-body">

                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover table-sm w-100" id="${tableId}">
                            <thead></thead>
                            <tbody></tbody>
                        </table>
                    </div>

                </div>
            </div>
        `;

        jQuery('#hasil-tabel').append(html);

        if(!rows || rows.length == 0){

            jQuery('#'+tableId+' tbody').html(
                '<tr><td colspan="100%" class="text-center">Tidak ada data</td></tr>'
            );

            return;
        }

        let columns = Object.keys(rows[0]).filter(function(col){
            return col != 'active';
        });

        let head = '<tr>';

        columns.forEach(function(col){
            head += '<th>'+col.replace(/_/g,' ')+'</th>';
        });

        head += '</tr>';

        jQuery('#'+tableId+' thead').html(head);

        let body='';

        rows.forEach(function(row){

            body += '<tr>';

            columns.forEach(function(col){

                let value = row[col];

                if(col == 'url_foto'){

                    if(value){

                        value = `
                            <a href="${value}" target="_blank">
                                <img src="${value}" style="max-width:120px">
                            </a>
                        `;

                    }else{

                        value='-';

                    }

                }else{

                    if(value === null || value === '' || value === undefined){
                        value='-';
                    }

                }

                body += '<td>'+value+'</td>';

            });

            body += '</tr>';

        });

        jQuery('#'+tableId+' tbody').html(body);

    }
</script>
