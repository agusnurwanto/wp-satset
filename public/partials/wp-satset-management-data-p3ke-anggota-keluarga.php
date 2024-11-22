<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}

if (!empty($_GET) && !empty($_GET['tahun_anggaran'])) {
    $tahun_anggaran = $_GET['tahun_anggaran'];
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
    .wrap-table{
        overflow: auto;
        max-height: 100vh; 
        width: 100%; 
    }
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<div class="cetak">
    <div style="padding: 10px;margin:0 0 3rem 0;">
        <input type="hidden" value="<?php echo get_option( '_crb_api_key_extension' ); ?>" id="api_key">
        <h1 class="text-center" style="margin:3rem;">Manajemen Data Anggota Keluarga P3KE <br>Tahun Anggaran <?php echo $tahun_anggaran; ?></h1>
        <div id="wrap-action"></div>
            <div class="text-center" style="margin-top: 30px;">
                <label style="margin-left: 10px;" for="tahun_anggaran">Tahun Anggaran : </label>
                <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
                    <?php echo $select_tahun; ?>
                </select>
                <button style="margin-left: 10px; height: 45px; width: 75px;"onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
            </div>
        </div>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_p3ke_anggota_keluarga();"><i class="dashicons dashicons-plus"></i> Tambah Data</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Id P3KE</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kabupaten / Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Desa</th>
                    <th class="text-center">Kode Kemendagri</th>
                    <th class="text-center">Jenis Desil</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center">ID Individu</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Padan Dukcapil</th>
                    <th class="text-center">Jenis Kelamin</th>
                    <th class="text-center">Hubungan Keluarga</th>
                    <th class="text-center">Tanggal Lahir</th>
                    <th class="text-center">Status Kawin</th>
                    <th class="text-center">Pekerjaan</th>
                    <th class="text-center">Pendidikan</th>
                    <th class="text-center">Jumlah Usia Dibawah 7 Tahun</th>
                    <th class="text-center">Jumlah Usia 7 - 12 Tahun</th>
                    <th class="text-center">Jumlah Usia 13 - 15 Tahun</th>
                    <th class="text-center">Jumlah Usia 16 - 18 Tahun</th>
                    <th class="text-center">Jumlah Usia 19 - 21 Tahun</th>
                    <th class="text-center">Jumlah Usia 22 - 59 Tahun</th>
                    <th class="text-center">Jumlah Usia 60 Tahun Keatas</th>
                    <th class="text-center">Penerima Bpnt</th>   
                    <th class="text-center">Penerima Bpum</th>   
                    <th class="text-center">Penerima Bst</th>
                    <th class="text-center">Penerima Pkh</th>
                    <th class="text-center">Penerima Sembako</th>
                    <th class="text-center">Resiko Stunting</th>
                    <th class="text-center">Tahun Anggaran</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>

<div class="modal fade mt-4" id="modalTambahDataP3KEAnggotaKeluarga" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataP3KEAnggotaKeluargaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataP3KEAnggotaKeluargaLabel">Data P3KE Anggota Keluarga</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label for='id_p3ke' style='display:inline-block'>Id P3KE</label>
                    <input type='text' id='id_p3ke' name="id_p3ke" class="form-control" placeholder=''>
                </div> 
                <div class="form-group">
                    <label for='provinsi' style='display:inline-block'>Provinsi</label>
                    <input type="text" id='provinsi' name="provinsi" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='kabkot' style='display:inline-block'>Kabupaten / Kota</label>
                    <input type="text" id='kabkot' name="kabkot" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='kecamatan' style='display:inline-block'>Kecamatan</label>
                    <input type="text" id='kecamatan' name="kecamatan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='desa' style='display:inline-block'>Desa</label>
                    <input type="text" id='desa' name="desa" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='kode_kemendagri' style='display:inline-block'>Kode Kemendagri</label>
                    <input type='text' id='kode_kemendagri' name="kode_kemendagri" class="form-control" placeholder=''>
                </div>
                <div class="form-group">
                    <label for='jenis_desil' style='display:inline-block'>Jenis Desil</label>
                    <input type="text" id='jenis_desil' name="jenis_desil" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='alamat' style='display:inline-block'>Alamat</label>
                    <input type="text" id='alamat' name="alamat" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='id_individu' style='display:inline-block'>ID Individu</label>
                    <input type="text" id='id_individu' name="id_individu" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='nama' style='display:inline-block'>Nama</label>
                    <input type="text" id='nama' name="nama" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='nik' style='display:inline-block'>NIK</label>
                    <input type='text' id='nik' name="nik" class="form-control" placeholder=''>
                </div>
                <div class="form-group">
                    <label for='padan_dukcapil' style='display:inline-block'>Padan Dukcapil</label>
                    <input type="text" id='padan_dukcapil' name="padan_dukcapil" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='hubungan_keluarga' style='display:inline-block'>Hubungan Keluarga</label>
                    <input type="text" id='hubungan_keluarga' name="hubungan_keluarga" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='jenis_kelamin' style='display:inline-block'>Jenis Kelamin</label>
                    <input type="text" id='jenis_kelamin' name="jenis_kelamin" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='tanggal_lahir' style='display:inline-block'>Tanggal Lahir</label>
                    <input type="text" id='tanggal_lahir' name="tanggal_lahir" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='status_kawin' style='display:inline-block'>Status Kawin</label>
                    <input type="text" id='status_kawin' name="status_kawin" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='pekerjaan' style='display:inline-block'>Pekerjaan</label>
                    <input type="text" id='pekerjaan' name="pekerjaan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='pendidikan' style='display:inline-block'>Pendidikan</label>
                    <input type="text" id='pendidikan' name="pendidikan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='usia_dibawah_7' style='display:inline-block'>Jumlah Usia Dibawah 7 Tahun</label>
                    <input type="text" id='usia_dibawah_7' name="usia_dibawah_7" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='usia_7_12' style='display:inline-block'>Jumlah Usia 7 - 12 Tahun</label>
                    <input type="text" id='usia_7_12' name="usia_7_12" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='usia_13_15' style='display:inline-block'>Jumlah Usia 13 - 15 Tahun</label>
                    <input type="text" id='usia_13_15' name="usia_13_15" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='usia_16_18' style='display:inline-block'>Jumlah Usia 16 - 18 Tahun</label>
                    <input type="text" id='usia_16_18' name="usia_16_18" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='usia_19_21' style='display:inline-block'>Jumlah Usia 19 - 21 Tahun</label>
                    <input type="text" id='usia_19_21' name="usia_19_21" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='usia_22_59' style='display:inline-block'>Jumlah Usia 22 - 59 Tahun</label>
                    <input type="text" id='usia_22_59' name="usia_22_59" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='usia_60_keatas' style='display:inline-block'>Jumlah Usia 60 Tahun Keatas</label>
                    <input type="text" id='usia_60_keatas' name="usia_60_keatas" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='penerima_bpnt' style='display:inline-block'>Penerima BPNT</label>
                    <input type="text" id='penerima_bpnt' name="penerima_bpnt" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='penerima_bpum' style='display:inline-block'>Penerima BPUM</label>
                    <input type="text" id='penerima_bpum' name="penerima_bpum" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='penerima_bst' style='display:inline-block'>Penerima BST</label>
                    <input type="text" id='penerima_bst' name="penerima_bst" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='penerima_pkh' style='display:inline-block'>Penerima PKH</label>
                    <input type="text" id='penerima_pkh' name="penerima_pkh" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='penerima_sembako' style='display:inline-block'>Penerima Sembako</label>
                    <input type="text" id='penerima_sembako' name="penerima_sembako" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='resiko_stunting' style='display:inline-block'>Resiko Stunting</label>
                    <input type="text" id='resiko_stunting' name="resiko_stunting" class="form-control" placeholder=''/>
                </div>  
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormP3KE()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>    
jQuery(document).ready(function(){
    get_data_p3ke_anggota_keluarga();
});

function get_data_p3ke_anggota_keluarga (){
    if(typeof datap3keanggotakeluarga == 'undefined'){
        window.datap3keanggotakeluarga = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_p3ke_anggota_keluarga',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'tahun_anggaran': '<?php echo $tahun_anggaran; ?>',
                }
            },
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
            order: [[0, 'asc']],
            "drawCallback": function( settings ){
                jQuery("#wrap-loading").hide();
            },
            "columns": [
                {
                    "data": 'id_p3ke',
                    className: "text-center"
                },
                {
                    "data": 'provinsi',
                    className: "text-center"
                },
                {
                    "data": 'kabkot',
                    className: "text-center"
                },
                {
                    "data": 'kecamatan',
                    className: "text-center"
                },
                {
                    "data": 'desa',
                    className: "text-center"
                },
                {
                    "data": 'kode_kemendagri',
                    className: "text-center"
                },
                {
                    "data": 'jenis_desil',
                    className: "text-center"
                },
                {
                    "data": 'alamat',
                    className: "text-center"
                },
                {
                    "data": 'id_individu',
                    className: "text-center"
                },
                {
                    "data": 'nama',
                    className: "text-center"
                },
                {
                    "data": 'nik',
                    className: "text-center"
                },
                {
                    "data": 'padan_dukcapil',
                    className: "text-center"
                },
                {
                    "data": 'jenis_kelamin',
                    className: "text-center"
                },
                {
                    "data": 'hubungan_keluarga',
                    className: "text-center"
                },
                {
                    "data": 'tanggal_lahir',
                    className: "text-center"
                },
                {
                    "data": 'status_kawin',
                    className: "text-center"
                },
                {
                    "data": 'pekerjaan',
                    className: "text-center"
                },
                {
                    "data": 'pendidikan',
                    className: "text-center"
                },
                {
                    "data": 'usia_dibawah_7',
                    className: "text-center"
                },
                {
                    "data": 'usia_7_12',
                    className: "text-center"
                },
                {
                    "data": 'usia_13_15',
                    className: "text-center"
                },
                {
                    "data": 'usia_16_18',
                    className: "text-center"
                },
                {
                    "data": 'usia_19_21',
                    className: "text-center"
                },
                {
                    "data": 'usia_22_59',
                    className: "text-center"
                },
                {
                    "data": 'usia_60_keatas',
                    className: "text-center"
                },
                {
                    "data": 'penerima_bpnt',
                    className: "text-center"
                },
                {
                    "data": 'penerima_bpum',
                    className: "text-center"
                },
                {
                    "data": 'penerima_bst',
                    className: "text-center"
                },
                {
                    "data": 'penerima_pkh',
                    className: "text-center"
                },
                {
                    "data": 'penerima_sembako',
                    className: "text-center"
                },
                {
                    "data": 'resiko_stunting',
                    className: "text-center"
                },
                {
                    "data": 'tahun_anggaran',
                    className: "text-center"
                },
                {
                    "data": 'aksi',
                    className: "text-center"
                }
            ]
        });
    }else{
        datap3keanggotakeluarga.draw();
    }
}

function hapus_data(id){
        let confirmDelete = confirm("Apakah anda yakin akan menghapus data ini?");
        if(confirmDelete){
            jQuery('#wrap-loading').show();
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type:'post',
                data:{
                    'action' : 'hapus_data_p3ke_anggota_keluarga_by_id',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'id'     : id
                },
                dataType: 'json',
                success:function(response){
                    jQuery('#wrap-loading').hide();
                    if(response.status == 'success'){
                        get_data_p3ke_anggota_keluarga(); 
                    }else{
                        alert(`GAGAL! \n${response.message}`);
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
        data:{
            'action': 'get_data_p3ke_anggota_keluarga_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#id_p3ke').val(res.data.id_p3ke);
                jQuery('#provinsi').val(res.data.provinsi);
                jQuery('#kabkot').val(res.data.kabkot);
                jQuery('#kecamatan').val(res.data.kecamatan);
                jQuery('#desa').val(res.data.desa);
                jQuery('#kode_kemendagri').val(res.data.kode_kemendagri);
                jQuery('#jenis_desil').val(res.data.jenis_desil);
                jQuery('#alamat').val(res.data.alamat);
                jQuery('#id_individu').val(res.data.id_individu);
                jQuery('#nama').val(res.data.nama);
                jQuery('#nik').val(res.data.nik);
                jQuery('#padan_dukcapil').val(res.data.padan_dukcapil);
                jQuery('#jenis_kelamin').val(res.data.jenis_kelamin);
                jQuery('#hubungan_keluarga').val(res.data.hubungan_keluarga);
                jQuery('#tanggal_lahir').val(res.data.tanggal_lahir);
                jQuery('#status_kawin').val(res.data.status_kawin);
                jQuery('#pekerjaan').val(res.data.pekerjaan);
                jQuery('#pendidikan').val(res.data.pendidikan);
                jQuery('#usia_dibawah_7').val(res.data.usia_dibawah_7);
                jQuery('#usia_7_12').val(res.data.usia_7_12);
                jQuery('#usia_13_15').val(res.data.usia_13_15);
                jQuery('#usia_16_18').val(res.data.usia_16_18);
                jQuery('#usia_19_21').val(res.data.usia_19_21);
                jQuery('#usia_22_59').val(res.data.usia_22_59);
                jQuery('#usia_60_keatas').val(res.data.usia_60_keatas);
                jQuery('#fasilitas_bab').val(res.data.fasilitas_bab);
                jQuery('#penerima_bpnt').val(res.data.penerima_bpnt);
                jQuery('#penerima_bpum').val(res.data.penerima_bpum);
                jQuery('#penerima_bst').val(res.data.penerima_bst);
                jQuery('#penerima_pkh').val(res.data.penerima_pkh);
                jQuery('#penerima_sembako').val(res.data.penerima_sembako);
                jQuery('#resiko_stunting').val(res.data.resiko_stunting);
                jQuery('#modalTambahDataP3KEAnggotaKeluarga').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_p3ke_anggota_keluarga(){
    jQuery('#id_data').val('');
    jQuery('#id_p3ke').val('');
    jQuery('#provinsi').val('');
    jQuery('#kabkot').val('');
    jQuery('#kecamatan').val('');
    jQuery('#desa').val('');
    jQuery('#kode_kemendagri').val('');
    jQuery('#jenis_desil').val('');
    jQuery('#alamat').val('');
    jQuery('#id_individu').val('');
    jQuery('#nama').val('');
    jQuery('#nik').val('');
    jQuery('#padan_dukcapil').val('');
    jQuery('#jenis_kelamin').val('');
    jQuery('#hubungan_keluarga').val('');
    jQuery('#tanggal_lahir').val('');
    jQuery('#status_kawin').val('');
    jQuery('#pekerjaan').val('');
    jQuery('#pendidikan').val('');
    jQuery('#usia_dibawah_7').val('');
    jQuery('#usia_7_12').val('');
    jQuery('#usia_13_15').val('');
    jQuery('#usia_16_18').val('');
    jQuery('#usia_19_21').val('');
    jQuery('#usia_22_59').val('');
    jQuery('#usia_60_keatas').val('');
    jQuery('#fasilitas_bab').val('');
    jQuery('#penerima_bpnt').val('');
    jQuery('#penerima_bpum').val('');
    jQuery('#penerima_bst').val('');
    jQuery('#penerima_pkh').val('');
    jQuery('#penerima_sembako').val('');
    jQuery('#resiko_stunting').val('');
    jQuery('#modalTambahDataP3KEAnggotaKeluarga').modal('show');
}

function submitTambahDataFormP3KE(){
    var id_data = jQuery('#id_data').val();
    var id_p3ke = jQuery('#id_p3ke').val();
    if(id_p3ke == ''){
        return alert('Data ID P3KE tidak boleh kosong!');
    }
    var kode_kemendagri = jQuery('#kode_kemendagri').val();
    if(kode_kemendagri == ''){
        return alert('Data Kode Kemendagri tidak boleh kosong!');
    }
    var nik = jQuery('#nik').val();
    if(nik == ''){
        return alert('Data NIK tidak boleh kosong!');
    }
    var padan_dukcapil = jQuery('#padan_dukcapil').val();
    if(padan_dukcapil == ''){
        return alert('Data Padan Dukcapil tidak boleh kosong!');
    }
    var hubungan_keluarga = jQuery('#hubungan_keluarga').val();
    if(hubungan_keluarga == ''){
        return alert('Data Hubungan Keluarga tidak boleh kosong!');
    }
    var jenis_kelamin = jQuery('#jenis_kelamin').val();
    if(jenis_kelamin == ''){
        return alert('Data Jenis Kelamin tidak boleh kosong!');
    }
    var tanggal_lahir = jQuery('#tanggal_lahir').val();
    if(tanggal_lahir == ''){
        return alert('Data Tanggal Lahir tidak boleh kosong!');
    }
    var provinsi = jQuery('#provinsi').val();
    if(provinsi == ''){
        return alert('Data Provinsi tidak boleh kosong!');
    }
    var kabkot = jQuery('#kabkot').val();
    if(kabkot == ''){
        return alert('Data Kabkot tidak boleh kosong!');
    }
    var kecamatan = jQuery('#kecamatan').val();
    if(kecamatan == ''){
        return alert('Data Kecamatan tidak boleh kosong!');
    }
    var desa = jQuery('#desa').val();
    if(desa == ''){
        return alert('Data Desa tidak boleh kosong!');
    }
    var alamat = jQuery('#alamat').val();
    if(alamat == ''){
        return alert('Data Alamat tidak boleh kosong!');
    }
    var pekerjaan = jQuery('#pekerjaan').val();
    if(pekerjaan == ''){
        return alert('Data Pekerjaan tidak boleh kosong!');
    }
    var pendidikan = jQuery('#pendidikan').val();
    if(pendidikan == ''){
        return alert('Data Pendidikan tidak boleh kosong!');
    }
    var usia_dibawah_7 = jQuery('#usia_dibawah_7').val();
    if(usia_dibawah_7 == ''){
        return alert('Data Usia Dibawah 7 tidak boleh kosong!');
    }
    var usia_7_12 = jQuery('#usia_7_12').val();
    if(usia_7_12 == ''){
        return alert('Data Usia 7 - 12 tidak boleh kosong!');
    }
    var jenis_desil = jQuery('#jenis_desil').val();
    if(jenis_desil == ''){
        return alert('Data jenis_desil tidak boleh kosong!');
    }
    var usia_13_15 = jQuery('#usia_13_15').val();
    if(usia_13_15 == ''){
        return alert('Data Usia 13 - 15 tidak boleh kosong!');
    }
    var usia_16_18 = jQuery('#usia_16_18').val();
    if(usia_16_18 == ''){
        return alert('Data Usia 16 - 18 tidak boleh kosong!');
    }
    var usia_19_21 = jQuery('#usia_19_21').val();
    if(usia_19_21 == ''){
        return alert('Data Usia 19 - 21 tidak boleh kosong!');
    }
    var usia_22_59 = jQuery('#usia_22_59').val();
    if(usia_22_59 == ''){
        return alert('Data Usia 22 - 59 tidak boleh kosong!');
    }
    var usia_60_keatas = jQuery('#usia_60_keatas').val();
    if(usia_60_keatas == ''){
        return alert('Data Usia 60 keatas tidak boleh kosong!');
    }
    var penerima_bpnt = jQuery('#penerima_bpnt').val();
    if(penerima_bpnt == ''){
        return alert('Data Penerima bpnt tidak boleh kosong!');
    }
    var penerima_bpum = jQuery('#penerima_bpum').val();
    if(penerima_bpum == ''){
        return alert('Data Penerima bpum tidak boleh kosong!');
    }
    var penerima_bst = jQuery('#penerima_bst').val();
    if(penerima_bst == ''){
        return alert('Data Penerima bst tidak boleh kosong!');
    }
    var penerima_pkh = jQuery('#penerima_pkh').val();
    if(penerima_pkh == ''){
        return alert('Data Penerima pkh tidak boleh kosong!');
    }
    var penerima_sembako = jQuery('#penerima_sembako').val();
    if(penerima_sembako == ''){
        return alert('Data Penerima sembako tidak boleh kosong!');
    }
    var resiko_stunting = jQuery('#resiko_stunting').val();
    if(resiko_stunting == ''){
        return alert('Data resiko stunting tidak boleh kosong!');
    }
    var nama = jQuery('#nama').val();
    if(nama == ''){
        return alert('Data Nama tidak boleh kosong!');
    }
    var id_individu = jQuery('#id_individu').val();
    if(id_individu == ''){
        return alert('Data ID Individu tidak boleh kosong!');
    }
    var status_kawin = jQuery('#status_kawin').val();
    if(status_kawin == ''){
        return alert('Data Status Kawin tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_p3ke_anggota_keluarga',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': id_data,
            'id_p3ke': id_p3ke,
            'kode_kemendagri': kode_kemendagri,
            'nik': nik,
            'padan_dukcapil': padan_dukcapil,
            'hubungan_keluarga': hubungan_keluarga,
            'jenis_kelamin': jenis_kelamin,
            'tanggal_lahir': tanggal_lahir,
            'provinsi': provinsi,
            'kabkot': kabkot,
            'kecamatan': kecamatan,
            'desa': desa,
            'alamat': alamat,
            'pekerjaan': pekerjaan,
            'pendidikan': pendidikan,
            'usia_dibawah_7': usia_dibawah_7,
            'usia_7_12': usia_7_12,
            'jenis_desil': jenis_desil,
            'usia_13_15': usia_13_15,
            'usia_16_18': usia_16_18,
            'usia_19_21': usia_19_21,
            'usia_22_59': usia_22_59,
            'usia_60_keatas': usia_60_keatas,
            'penerima_bpnt': penerima_bpnt,
            'penerima_bpum': penerima_bpum,
            'penerima_bst': penerima_bst,
            'penerima_pkh': penerima_pkh,
            'penerima_sembako': penerima_sembako,
            'resiko_stunting': resiko_stunting,
            'id_individu': id_individu,
            'nama': nama,
            'status_kawin': status_kawin,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataP3KEAnggotaKeluarga').modal('hide');
            if(res.status == 'success'){
                get_data_p3ke_anggota_keluarga();
            }else{
                jQuery('#wrap-loading').hide();
            }
        }
    });
}

function sumbitTahun(){
    var tahun_anggaran = jQuery('#tahun_anggaran').val();
    if(tahun_anggaran == ''){
        return alert('Tahun tidak boleh kosong!');
    }
    var url = window.location.href;
    url = url.split('?')[0]+'?tahun_anggaran='+tahun_anggaran;
    location.href = url;
}
</script>