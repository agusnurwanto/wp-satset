<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

if (!empty($_GET) && !empty($_GET['tahun_anggaran'])) {
    $tahun_anggaran = $_GET['tahun_anggaran'];
} else {
    $tahun_anggaran = get_option('_crb_tahun_satset');
}
$tahun = $wpdb->get_results('
    SELECT 
        tahun_anggaran 
    from satset_data_unit
    group by tahun_anggaran 
    order by tahun_anggaran ASC
', ARRAY_A);
$select_tahun = "";
foreach($tahun as $tahun_value){
    $select = $tahun_value['tahun_anggaran'] == $tahun_anggaran ? 'selected' : '';
    $select_tahun .= "<option value='".$tahun_value['tahun_anggaran']."' ".$select.">".$tahun_value['tahun_anggaran']."</option>";
}
?>
<style type="text/css">
    .wrap-table {
        overflow: auto;
        max-height: 80vh;
        width: 100%;
    }
    #management_data_table_aids thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #343a40;
        color: #fff;
        white-space: nowrap;
    }
    #management_data_table_aids td {
        white-space: nowrap;
    }
</style>
<div class="cetak">
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option( '_crb_api_key_extension' ); ?>" id="api_key">
        <h1 class="text-center" style="margin:3rem;">Manajemen Data AIDS/HIV</h1>
        <div id="wrap-action"></div>
        <div class="text-center" style="margin-top: 30px;">
            <label style="margin-left: 10px;" for="tahun_anggaran_filter">Tahun Anggaran : </label>
            <select style="width: 400px;" name="tahun_anggaran_filter" id="tahun_anggaran_filter">
                <?php echo $select_tahun; ?>
            </select>
            <button style="margin-left: 10px; height: 45px; width: 75px;" onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
        </div>
        <div style="margin-bottom: 25px; margin-top: 20px;">
            <button class="btn btn-primary" onclick="tambah_data_aids();"><i class="dashicons dashicons-plus"></i> Tambah Data AIDS</button>
        </div>
        <div class="wrap-table">
            <table id="management_data_table_aids" class="table table-bordered">
                <thead>
                    <tr>
                        <th class="text-center">Provinsi</th>
                        <th class="text-center">Kab/Kota</th>
                        <th class="text-center">Kecamatan</th>
                        <th class="text-center">Kode UPK</th>
                        <th class="text-center">Nama UPK</th>
                        <th class="text-center">ID Pasien</th>
                        <th class="text-center">Warga Negara</th>
                        <th class="text-center">NIK</th>
                        <th class="text-center">Nama Pasien</th>
                        <th class="text-center">Tanggal Lahir</th>
                        <th class="text-center">Jenis Kelamin</th>
                        <th class="text-center">No. Telp</th>
                        <th class="text-center">Umur Terdiagnosis</th>
                        <th class="text-center">Kelompok Umur Terdiagnosis</th>
                        <th class="text-center">Kategori Umur Terdiagnosis</th>
                        <th class="text-center">Provinsi Pasien</th>
                        <th class="text-center">Kab/Kota Pasien</th>
                        <th class="text-center">Kecamatan Pasien</th>
                        <th class="text-center">Desa Pasien</th>
                        <th class="text-center">Alamat Pasien</th>
                        <th class="text-center">Provinsi Domisili</th>
                        <th class="text-center">Kab/Kota Domisili</th>
                        <th class="text-center">Kecamatan Domisili</th>
                        <th class="text-center">Desa Domisili</th>
                        <th class="text-center">Alamat Domisili</th>
                        <th class="text-center">Tanggal Register</th>
                        <th class="text-center">Waktu Input Pertama</th>
                        <th class="text-center">No. Rekam Medik</th>
                        <th class="text-center">Kel. Populasi LSL</th>
                        <th class="text-center">Konfirmasi HIV+ Tgl</th>
                        <th class="text-center">Konfirmasi HIV+ Provinsi</th>
                        <th class="text-center">Konfirmasi HIV+ Kab/Kota</th>
                        <th class="text-center">Konfirmasi HIV+ Layanan</th>
                        <th class="text-center">Followup Sblm Perawatan Meninggal</th>
                        <th class="text-center">Pendampingan Komunitas</th>
                        <th class="text-center">Followup Sblm Perawatan &amp; ARV Meninggal</th>
                        <th class="text-center">Capaian T&amp;T Layanan</th>
                        <th class="text-center">Capaian T&amp;T Kab/Kota</th>
                        <th class="text-center">Capaian T&amp;T Provinsi</th>
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

<!-- Modal Tambah / Edit Data AIDS -->
<div class="modal fade mt-4" id="modalTambahDataAIDS" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataAIDSLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataAIDSLabel">Data AIDS/HIV</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="aids_id_data" name="aids_id_data">

                <div class="form-group">
                    <label>Tahun Anggaran</label>
                    <input type="text" id="aids_tahun_anggaran" name="aids_tahun_anggaran" class="form-control" value="<?php echo $tahun_anggaran; ?>" disabled>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Provinsi</label>
                        <input type="text" id="aids_provinsi" name="aids_provinsi" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Kabupaten / Kota</label>
                        <input type="text" id="aids_kabkot" name="aids_kabkot" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Kecamatan</label>
                        <input type="text" id="aids_kecamatan" name="aids_kecamatan" class="form-control" placeholder="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Kode UPK</label>
                        <input type="text" id="aids_kode_upk" name="aids_kode_upk" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-8 form-group">
                        <label>Nama UPK</label>
                        <input type="text" id="aids_nama_upk" name="aids_nama_upk" class="form-control" placeholder="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>ID Pasien</label>
                        <input type="text" id="aids_id_pasien" name="aids_id_pasien" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Warga Negara</label>
                        <input type="text" id="aids_warga_negara" name="aids_warga_negara" class="form-control" placeholder="WNI / WNA">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>NIK</label>
                        <input type="text" id="aids_nik" name="aids_nik" class="form-control" placeholder="">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Nama Pasien</label>
                        <input type="text" id="aids_nama_pasien" name="aids_nama_pasien" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Tanggal Lahir</label>
                        <input type="text" id="aids_tanggal_lahir" name="aids_tanggal_lahir" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Jenis Kelamin</label>
                        <select id="aids_jenis_kelamin" name="aids_jenis_kelamin" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="L">Laki-laki</option>
                            <option value="P">Perempuan</option>
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>No. Telepon</label>
                        <input type="text" id="aids_no_telp" name="aids_no_telp" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Umur Terdiagnosis</label>
                        <input type="text" id="aids_umur_terdiagnosis" name="aids_umur_terdiagnosis" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Kelompok Umur Terdiagnosis</label>
                        <input type="text" id="aids_kelompok_umur_terdiagnosis" name="aids_kelompok_umur_terdiagnosis" class="form-control" placeholder="">
                    </div>
                </div>

                <hr><h6>Alamat KTP Pasien</h6>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Provinsi Pasien</label>
                        <input type="text" id="aids_provinsi_pasien" name="aids_provinsi_pasien" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Kab/Kota Pasien</label>
                        <input type="text" id="aids_kabkot_pasien" name="aids_kabkot_pasien" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Kecamatan Pasien</label>
                        <input type="text" id="aids_kecamatan_pasien" name="aids_kecamatan_pasien" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Desa Pasien</label>
                        <input type="text" id="aids_desa_pasien" name="aids_desa_pasien" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Pasien</label>
                    <input type="text" id="aids_alamat_pasien" name="aids_alamat_pasien" class="form-control" placeholder="">
                </div>

                <hr><h6>Alamat Domisili</h6>
                <div class="row">
                    <div class="col-md-3 form-group">
                        <label>Provinsi Domisili</label>
                        <input type="text" id="aids_provinsi_domisili" name="aids_provinsi_domisili" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Kab/Kota Domisili</label>
                        <input type="text" id="aids_kabkot_domisili" name="aids_kabkot_domisili" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Kecamatan Domisili</label>
                        <input type="text" id="aids_kecamatan_domisili" name="aids_kecamatan_domisili" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-3 form-group">
                        <label>Desa Domisili</label>
                        <input type="text" id="aids_desa_domisili" name="aids_desa_domisili" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="form-group">
                    <label>Alamat Domisili</label>
                    <input type="text" id="aids_alamat_domisili" name="aids_alamat_domisili" class="form-control" placeholder="">
                </div>

                <hr><h6>Data Klinis</h6>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Tanggal Register</label>
                        <input type="text" id="aids_tanggal_register" name="aids_tanggal_register" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>No. Rekam Medik</label>
                        <input type="text" id="aids_no_rekam_medik" name="aids_no_rekam_medik" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Waktu Input Pertama</label>
                        <input type="text" id="aids_waktu_input_pertama" name="aids_waktu_input_pertama" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Kelompok Populasi LSL</label>
                        <select id="aids_kel_populasi_lsl" name="aids_kel_populasi_lsl" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Pendampingan Komunitas</label>
                        <select id="aids_pendampingan_komunitas" name="aids_pendampingan_komunitas" class="form-control">
                            <option value="">-- Pilih --</option>
                            <option value="1">Ya</option>
                            <option value="0">Tidak</option>
                        </select>
                    </div>
                    <div class="col-md-4 form-group">
                        <label>Capaian T&amp;T Layanan</label>
                        <input type="text" id="aids_capaian_t_dan_t_layanan" name="aids_capaian_t_dan_t_layanan" class="form-control" placeholder="">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Tanggal Konfirmasi HIV+</label>
                        <input type="text" id="aids_konfirmasi_hiv_plus_tanggal_konfirmasi" name="aids_konfirmasi_hiv_plus_tanggal_konfirmasi" class="form-control" placeholder="">
                    </div>
                    <div class="col-md-6 form-group">
                        <label>Layanan Konfirmasi HIV+</label>
                        <input type="text" id="aids_konfirmasi_hiv_plus_layanan" name="aids_konfirmasi_hiv_plus_layanan" class="form-control" placeholder="">
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormAIDS()">Simpan</button>
                <button type="button" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function(){
    get_data_aids();
});

function get_data_aids(){
    if(typeof dataaids == 'undefined'){
        window.dataaids = jQuery('#management_data_table_aids').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data: {
                    'action': 'get_datatable_aids',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'tahun_anggaran': '<?php echo $tahun_anggaran; ?>',
                }
            },
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
            order: [[0, 'asc']],
            "drawCallback": function(settings){
                jQuery("#wrap-loading").hide();
            },
            "columns": [
                { "data": 'provinsi',                                          className: "text-center" },
                { "data": 'kabkot',                                            className: "text-center" },
                { "data": 'kecamatan',                                         className: "text-center" },
                { "data": 'kode_upk',                                          className: "text-center" },
                { "data": 'nama_upk',                                          className: "text-center" },
                { "data": 'id_pasien',                                         className: "text-center" },
                { "data": 'warga_negara',                                      className: "text-center" },
                { "data": 'nik',                                               className: "text-center" },
                { "data": 'nama_pasien',                                       className: "text-center" },
                { "data": 'tanggal_lahir',                                     className: "text-center" },
                { "data": 'jenis_kelamin',                                     className: "text-center" },
                { "data": 'no_telp',                                           className: "text-center" },
                { "data": 'umur_terdiagnosis',                                 className: "text-center" },
                { "data": 'kelompok_umur_terdiagnosis',                        className: "text-center" },
                { "data": 'kategori_umur_terdiagnosis',                        className: "text-center" },
                { "data": 'provinsi_pasien',                                   className: "text-center" },
                { "data": 'kabkot_pasien',                                     className: "text-center" },
                { "data": 'kecamatan_pasien',                                  className: "text-center" },
                { "data": 'desa_pasien',                                       className: "text-center" },
                { "data": 'alamat_pasien',                                     className: "text-center" },
                { "data": 'provinsi_domisili',                                 className: "text-center" },
                { "data": 'kabkot_domisili',                                   className: "text-center" },
                { "data": 'kecamatan_domisili',                                className: "text-center" },
                { "data": 'desa_domisili',                                     className: "text-center" },
                { "data": 'alamat_domisili',                                   className: "text-center" },
                { "data": 'tanggal_register',                                  className: "text-center" },
                { "data": 'waktu_input_pertama',                               className: "text-center" },
                { "data": 'no_rekam_medik',                                    className: "text-center" },
                { "data": 'kel_populasi_lsl',                                  className: "text-center" },
                { "data": 'konfirmasi_hiv_plus_tanggal_konfirmasi',            className: "text-center" },
                { "data": 'konfirmasi_hiv_plus_provinsi',                      className: "text-center" },
                { "data": 'konfirmasi_hiv_plus_kabkot',                        className: "text-center" },
                { "data": 'konfirmasi_hiv_plus_layanan',                       className: "text-center" },
                { "data": 'akhir_followup_sblm_masuk_perawatan_meninggal',     className: "text-center" },
                { "data": 'pendampingan_komunitas',                            className: "text-center" },
                { "data": 'akhir_followup_sblm_masuk_perawatan_dan_arv_meninggal', className: "text-center" },
                { "data": 'capaian_t_dan_t_layanan',                          className: "text-center" },
                { "data": 'capaian_t_dan_t_kabkot',                           className: "text-center" },
                { "data": 'capaian_t_dan_t_provinsi',                         className: "text-center" },
                { "data": 'tahun_anggaran',                                    className: "text-center" },
                { "data": 'aksi',                                              className: "text-center", orderable: false }
            ]
        });
    } else {
        dataaids.draw();
    }
}

function hapus_data(id){
    var confirmDelete = confirm("Apakah anda yakin akan menghapus data ini?");
    if(confirmDelete){
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'post',
            data: {
                'action': 'hapus_data_aids_by_id',
                'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                'id': id
            },
            dataType: 'json',
            success: function(response){
                jQuery('#wrap-loading').hide();
                if(response.status == 'success'){
                    get_data_aids();
                } else {
                    alert('GAGAL! \n' + response.message);
                }
            }
        });
    }
}

function edit_data(_id){
    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data: {
            'action': 'get_data_aids_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                var d = res.data;
                jQuery('#aids_id_data').val(d.id);
                jQuery('#aids_tahun_anggaran').val(d.tahun_anggaran);
                jQuery('#aids_provinsi').val(d.provinsi);
                jQuery('#aids_kabkot').val(d.kabkot);
                jQuery('#aids_kecamatan').val(d.kecamatan);
                jQuery('#aids_kode_upk').val(d.kode_upk);
                jQuery('#aids_nama_upk').val(d.nama_upk);
                jQuery('#aids_id_pasien').val(d.id_pasien);
                jQuery('#aids_warga_negara').val(d.warga_negara);
                jQuery('#aids_nik').val(d.nik);
                jQuery('#aids_nama_pasien').val(d.nama_pasien);
                jQuery('#aids_tanggal_lahir').val(d.tanggal_lahir);
                jQuery('#aids_jenis_kelamin').val(d.jenis_kelamin);
                jQuery('#aids_no_telp').val(d.no_telp);
                jQuery('#aids_umur_terdiagnosis').val(d.umur_terdiagnosis);
                jQuery('#aids_kelompok_umur_terdiagnosis').val(d.kelompok_umur_terdiagnosis);
                jQuery('#aids_provinsi_pasien').val(d.provinsi_pasien);
                jQuery('#aids_kabkot_pasien').val(d.kabkot_pasien);
                jQuery('#aids_kecamatan_pasien').val(d.kecamatan_pasien);
                jQuery('#aids_desa_pasien').val(d.desa_pasien);
                jQuery('#aids_alamat_pasien').val(d.alamat_pasien);
                jQuery('#aids_provinsi_domisili').val(d.provinsi_domisili);
                jQuery('#aids_kabkot_domisili').val(d.kabkot_domisili);
                jQuery('#aids_kecamatan_domisili').val(d.kecamatan_domisili);
                jQuery('#aids_desa_domisili').val(d.desa_domisili);
                jQuery('#aids_alamat_domisili').val(d.alamat_domisili);
                jQuery('#aids_tanggal_register').val(d.tanggal_register);
                jQuery('#aids_no_rekam_medik').val(d.no_rekam_medik);
                jQuery('#aids_waktu_input_pertama').val(d.waktu_input_pertama);
                jQuery('#aids_kel_populasi_lsl').val(d.kel_populasi_lsl);
                jQuery('#aids_pendampingan_komunitas').val(d.pendampingan_komunitas);
                jQuery('#aids_capaian_t_dan_t_layanan').val(d.capaian_t_dan_t_layanan);
                jQuery('#aids_konfirmasi_hiv_plus_tanggal_konfirmasi').val(d.konfirmasi_hiv_plus_tanggal_konfirmasi);
                jQuery('#aids_konfirmasi_hiv_plus_layanan').val(d.konfirmasi_hiv_plus_layanan);
                jQuery('#modalTambahDataAIDS').modal('show');
            } else {
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

function tambah_data_aids(){
    jQuery('#aids_id_data').val('');
    jQuery('#aids_tahun_anggaran').val('<?php echo $tahun_anggaran; ?>');
    jQuery('#aids_provinsi').val('');
    jQuery('#aids_kabkot').val('');
    jQuery('#aids_kecamatan').val('');
    jQuery('#aids_kode_upk').val('');
    jQuery('#aids_nama_upk').val('');
    jQuery('#aids_id_pasien').val('');
    jQuery('#aids_warga_negara').val('');
    jQuery('#aids_nik').val('');
    jQuery('#aids_nama_pasien').val('');
    jQuery('#aids_tanggal_lahir').val('');
    jQuery('#aids_jenis_kelamin').val('');
    jQuery('#aids_no_telp').val('');
    jQuery('#aids_umur_terdiagnosis').val('');
    jQuery('#aids_kelompok_umur_terdiagnosis').val('');
    jQuery('#aids_provinsi_pasien').val('');
    jQuery('#aids_kabkot_pasien').val('');
    jQuery('#aids_kecamatan_pasien').val('');
    jQuery('#aids_desa_pasien').val('');
    jQuery('#aids_alamat_pasien').val('');
    jQuery('#aids_provinsi_domisili').val('');
    jQuery('#aids_kabkot_domisili').val('');
    jQuery('#aids_kecamatan_domisili').val('');
    jQuery('#aids_desa_domisili').val('');
    jQuery('#aids_alamat_domisili').val('');
    jQuery('#aids_tanggal_register').val('');
    jQuery('#aids_no_rekam_medik').val('');
    jQuery('#aids_waktu_input_pertama').val('');
    jQuery('#aids_kel_populasi_lsl').val('');
    jQuery('#aids_pendampingan_komunitas').val('');
    jQuery('#aids_capaian_t_dan_t_layanan').val('');
    jQuery('#aids_konfirmasi_hiv_plus_tanggal_konfirmasi').val('');
    jQuery('#aids_konfirmasi_hiv_plus_layanan').val('');
    jQuery('#modalTambahDataAIDS').modal('show');
}

function submitTambahDataFormAIDS(){
    var id_pasien = jQuery('#aids_id_pasien').val();
    if(id_pasien == ''){
        return alert('ID Pasien tidak boleh kosong!');
    }
    var nik = jQuery('#aids_nik').val();
    if(nik == ''){
        return alert('NIK tidak boleh kosong!');
    }
    var nama_pasien = jQuery('#aids_nama_pasien').val();
    if(nama_pasien == ''){
        return alert('Nama Pasien tidak boleh kosong!');
    }
    var tahun_anggaran = jQuery('#aids_tahun_anggaran').val();
    if(tahun_anggaran == ''){
        return alert('Tahun Anggaran tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data: {
            'action': 'tambah_data_aids',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': jQuery('#aids_id_data').val(),
            'tahun_anggaran': tahun_anggaran,
            'provinsi': jQuery('#aids_provinsi').val(),
            'kabkot': jQuery('#aids_kabkot').val(),
            'kecamatan': jQuery('#aids_kecamatan').val(),
            'kode_upk': jQuery('#aids_kode_upk').val(),
            'nama_upk': jQuery('#aids_nama_upk').val(),
            'id_pasien': id_pasien,
            'warga_negara': jQuery('#aids_warga_negara').val(),
            'nik': nik,
            'nama_pasien': nama_pasien,
            'tanggal_lahir': jQuery('#aids_tanggal_lahir').val(),
            'jenis_kelamin': jQuery('#aids_jenis_kelamin').val(),
            'no_telp': jQuery('#aids_no_telp').val(),
            'umur_terdiagnosis': jQuery('#aids_umur_terdiagnosis').val(),
            'kelompok_umur_terdiagnosis': jQuery('#aids_kelompok_umur_terdiagnosis').val(),
            'provinsi_pasien': jQuery('#aids_provinsi_pasien').val(),
            'kabkot_pasien': jQuery('#aids_kabkot_pasien').val(),
            'kecamatan_pasien': jQuery('#aids_kecamatan_pasien').val(),
            'desa_pasien': jQuery('#aids_desa_pasien').val(),
            'alamat_pasien': jQuery('#aids_alamat_pasien').val(),
            'provinsi_domisili': jQuery('#aids_provinsi_domisili').val(),
            'kabkot_domisili': jQuery('#aids_kabkot_domisili').val(),
            'kecamatan_domisili': jQuery('#aids_kecamatan_domisili').val(),
            'desa_domisili': jQuery('#aids_desa_domisili').val(),
            'alamat_domisili': jQuery('#aids_alamat_domisili').val(),
            'tanggal_register': jQuery('#aids_tanggal_register').val(),
            'no_rekam_medik': jQuery('#aids_no_rekam_medik').val(),
            'waktu_input_pertama': jQuery('#aids_waktu_input_pertama').val(),
            'kel_populasi_lsl': jQuery('#aids_kel_populasi_lsl').val(),
            'pendampingan_komunitas': jQuery('#aids_pendampingan_komunitas').val(),
            'capaian_t_dan_t_layanan': jQuery('#aids_capaian_t_dan_t_layanan').val(),
            'konfirmasi_hiv_plus_tanggal_konfirmasi': jQuery('#aids_konfirmasi_hiv_plus_tanggal_konfirmasi').val(),
            'konfirmasi_hiv_plus_layanan': jQuery('#aids_konfirmasi_hiv_plus_layanan').val(),
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataAIDS').modal('hide');
            if(res.status == 'success'){
                get_data_aids();
            } else {
                jQuery('#wrap-loading').hide();
            }
        }
    });
}

function sumbitTahun(){
    var tahun_anggaran = jQuery('#tahun_anggaran_filter').val();
    if(tahun_anggaran == ''){
        return alert('Tahun tidak boleh kosong!');
    }
    var url = window.location.href;
    url = url.split('?')[0] + '?tahun_anggaran=' + tahun_anggaran;
    location.href = url;
}
</script>
