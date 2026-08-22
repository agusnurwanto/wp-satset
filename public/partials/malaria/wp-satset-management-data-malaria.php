<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

if (!empty($_GET['tahun_anggaran'])) {
    $tahun_anggaran = $_GET['tahun_anggaran'];
} else {
    $tahun_anggaran = get_option('_crb_tahun_satset');
}

$tahun = $wpdb->get_results('
    SELECT tahun_anggaran FROM satset_data_unit
    GROUP BY tahun_anggaran ORDER BY tahun_anggaran ASC
', ARRAY_A);

$select_tahun = '';
foreach ($tahun as $tahun_value) {
    $sel           = ($tahun_value['tahun_anggaran'] == $tahun_anggaran) ? 'selected' : '';
    $select_tahun .= "<option value='{$tahun_value['tahun_anggaran']}' {$sel}>{$tahun_value['tahun_anggaran']}</option>";
}
?>
<style type="text/css">
    .wrap-table {
        overflow: auto;
        max-height: 80vh;
        width: 100%;
    }
    #management_data_table_malaria thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #343a40;
        color: #fff;
        white-space: nowrap;
    }
    #management_data_table_malaria td {
        white-space: nowrap;
    }
</style>

<div class="cetak">
    <div style="padding: 10px; margin: 0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option('_crb_api_key_extension'); ?>" id="api_key">
        <h1 class="text-center" style="margin: 3rem;">Manajemen Data Malaria</h1>
        <div id="wrap-action"></div>
        <div class="text-center" style="margin-top: 30px;">
            <label style="margin-left: 10px;" for="tahun_anggaran_filter">Tahun Anggaran : </label>
            <select style="width: 400px;" name="tahun_anggaran_filter" id="tahun_anggaran_filter">
                <?php echo $select_tahun; ?>
            </select>
            <button style="margin-left: 10px; height: 45px; width: 75px;" onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
        </div>
        <div style="margin-bottom: 25px; margin-top: 20px;">
            <button class="btn btn-primary" onclick="tambah_data_malaria();"><i class="dashicons dashicons-plus"></i> Tambah Data Malaria</button>
        </div>
        <div class="wrap-table">
            <table id="management_data_table_malaria" class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Provinsi</th>
                        <th class="text-center">Kab/Kota</th>
                        <th class="text-center">Kecamatan</th>
                        <th class="text-center">Desa</th>
                        <th class="text-center">RT</th>
                        <th class="text-center">RW</th>
                        <th class="text-center">NIK</th>
                        <th class="text-center">Nama</th>
                        <th class="text-center">Umur</th>
                        <th class="text-center">Tindak Lanjut</th>
                        <th class="text-center">Hasil Akhir</th>
                        <th class="text-center">Status Pengobatan</th>
                        <th class="text-center">Tahun Anggaran</th>
                        <th class="text-center" style="min-width: 100px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah / Edit Data Malaria -->
<div class="modal fade mt-4" id="modalTambahDataMalaria" tabindex="-1" role="dialog" aria-labelledby="modalMalariaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalMalariaLabel">Data Malaria</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="malaria_id_data" name="malaria_id_data">

                <div class="form-group">
                    <label>Tahun Anggaran</label>
                    <input type="text" id="malaria_tahun_anggaran" class="form-control" value="<?php echo $tahun_anggaran; ?>" disabled>
                </div>

                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Provinsi</label>
                        <input type="text" id="malaria_provinsi" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Kab/Kota</label>
                        <input type="text" id="malaria_kabkot" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Kecamatan</label>
                        <input type="text" id="malaria_kecamatan" class="form-control">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Desa</label>
                        <input type="text" id="malaria_desa" class="form-control">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-2 form-group">
                        <label>RT</label>
                        <input type="text" id="malaria_rt" class="form-control">
                    </div>
                    <div class="col-md-2 form-group">
                        <label>RW</label>
                        <input type="text" id="malaria_rw" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>NIK</label>
                        <input type="text" id="malaria_nik" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Umur</label>
                        <input type="text" id="malaria_umur" class="form-control" placeholder="Tahun">
                    </div>
                </div>

                <div class="form-group">
                    <label>Nama</label>
                    <input type="text" id="malaria_nama" class="form-control">
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Tindak Lanjut</label>
                        <input type="text" id="malaria_tindak_lanjut" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Hasil Akhir</label>
                        <input type="text" id="malaria_hasil_akhir" class="form-control">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Status Pengobatan</label>
                        <input type="text" id="malaria_status_pengobatan" class="form-control">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormMalaria()">Simpan</button>
                <button type="button" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function () {
    get_data_malaria();
});

function get_data_malaria() {
    if (typeof datamalaria == 'undefined') {
        window.datamalaria = jQuery('#management_data_table_malaria').on('preXhr.dt', function () {
            jQuery('#wrap-loading').show();
        }).DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url:      '<?php echo admin_url('admin-ajax.php'); ?>',
                type:     'post',
                dataType: 'json',
                data: {
                    action:          'get_datatable_malaria',
                    api_key:         '<?php echo get_option(SATSET_APIKEY); ?>',
                    tahun_anggaran:  '<?php echo $tahun_anggaran; ?>'
                }
            },
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'All']],
            order: [[0, 'asc']],
            drawCallback: function () { jQuery('#wrap-loading').hide(); },
            columns: [
                { data: 'provinsi',          className: 'text-center' },
                { data: 'kabkot',            className: 'text-center' },
                { data: 'kecamatan',         className: 'text-center' },
                { data: 'desa',              className: 'text-center' },
                { data: 'rt',                className: 'text-center' },
                { data: 'rw',                className: 'text-center' },
                { data: 'nik',               className: 'text-center' },
                { data: 'nama',              className: 'text-center' },
                { data: 'umur',              className: 'text-center' },
                { data: 'tindak_lanjut',     className: 'text-center' },
                { data: 'hasil_akhir',       className: 'text-center' },
                { data: 'status_pengobatan', className: 'text-center' },
                { data: 'tahun_anggaran',    className: 'text-center' },
                { data: 'aksi',              className: 'text-center', orderable: false }
            ]
        });
    } else {
        datamalaria.draw();
    }
}

function hapus_data(id) {
    if (!confirm('Apakah anda yakin akan menghapus data ini?')) return;
    jQuery('#wrap-loading').show();
    jQuery.ajax({
        url:      '<?php echo admin_url('admin-ajax.php'); ?>',
        type:     'post',
        dataType: 'json',
        data: {
            action:  'hapus_data_malaria_by_id',
            api_key: '<?php echo get_option(SATSET_APIKEY); ?>',
            id:      id
        },
        success: function (res) {
            jQuery('#wrap-loading').hide();
            if (res.status === 'success') {
                get_data_malaria();
            } else {
                alert('GAGAL!\n' + res.message);
            }
        }
    });
}

function edit_data(_id) {
    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method:   'post',
        url:      '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data: {
            action:  'get_data_malaria_by_id',
            api_key: '<?php echo get_option(SATSET_APIKEY); ?>',
            id:      _id
        },
        success: function (res) {
            jQuery('#wrap-loading').hide();
            if (res.status === 'success') {
                var d = res.data;
                jQuery('#malaria_id_data').val(d.id);
                jQuery('#malaria_tahun_anggaran').val(d.tahun_anggaran);
                jQuery('#malaria_provinsi').val(d.provinsi);
                jQuery('#malaria_kabkot').val(d.kabkot);
                jQuery('#malaria_kecamatan').val(d.kecamatan);
                jQuery('#malaria_desa').val(d.desa);
                jQuery('#malaria_rt').val(d.rt);
                jQuery('#malaria_rw').val(d.rw);
                jQuery('#malaria_nik').val(d.nik);
                jQuery('#malaria_nama').val(d.nama);
                jQuery('#malaria_umur').val(d.umur);
                jQuery('#malaria_tindak_lanjut').val(d.tindak_lanjut);
                jQuery('#malaria_hasil_akhir').val(d.hasil_akhir);
                jQuery('#malaria_status_pengobatan').val(d.status_pengobatan);
                jQuery('#modalTambahDataMalaria').modal('show');
            } else {
                alert(res.message);
            }
        }
    });
}

function tambah_data_malaria() {
    jQuery('#malaria_id_data').val('');
    jQuery('#malaria_tahun_anggaran').val('<?php echo $tahun_anggaran; ?>');
    jQuery('#malaria_provinsi').val('');
    jQuery('#malaria_kabkot').val('');
    jQuery('#malaria_kecamatan').val('');
    jQuery('#malaria_desa').val('');
    jQuery('#malaria_rt').val('');
    jQuery('#malaria_rw').val('');
    jQuery('#malaria_nik').val('');
    jQuery('#malaria_nama').val('');
    jQuery('#malaria_umur').val('');
    jQuery('#malaria_tindak_lanjut').val('');
    jQuery('#malaria_hasil_akhir').val('');
    jQuery('#malaria_status_pengobatan').val('');
    jQuery('#modalTambahDataMalaria').modal('show');
}

function submitTambahDataFormMalaria() {
    var nama = jQuery('#malaria_nama').val();
    if (!nama) { return alert('Nama tidak boleh kosong!'); }
    var tahun_anggaran = jQuery('#malaria_tahun_anggaran').val();
    if (!tahun_anggaran) { return alert('Tahun Anggaran tidak boleh kosong!'); }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method:   'post',
        url:      '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data: {
            action:             'tambah_data_malaria',
            api_key:            '<?php echo get_option(SATSET_APIKEY); ?>',
            id_data:            jQuery('#malaria_id_data').val(),
            tahun_anggaran:     tahun_anggaran,
            provinsi:           jQuery('#malaria_provinsi').val(),
            kabkot:             jQuery('#malaria_kabkot').val(),
            kecamatan:          jQuery('#malaria_kecamatan').val(),
            desa:               jQuery('#malaria_desa').val(),
            rt:                 jQuery('#malaria_rt').val(),
            rw:                 jQuery('#malaria_rw').val(),
            nik:                jQuery('#malaria_nik').val(),
            nama:               nama,
            umur:               jQuery('#malaria_umur').val(),
            tindak_lanjut:      jQuery('#malaria_tindak_lanjut').val(),
            hasil_akhir:        jQuery('#malaria_hasil_akhir').val(),
            status_pengobatan:  jQuery('#malaria_status_pengobatan').val()
        },
        success: function (res) {
            alert(res.message);
            jQuery('#modalTambahDataMalaria').modal('hide');
            if (res.status === 'success') {
                get_data_malaria();
            } else {
                jQuery('#wrap-loading').hide();
            }
        }
    });
}

function sumbitTahun() {
    var tahun = jQuery('#tahun_anggaran_filter').val();
    if (!tahun) { return alert('Tahun tidak boleh kosong!'); }
    location.href = window.location.href.split('?')[0] + '?tahun_anggaran=' + tahun;
}
</script>
