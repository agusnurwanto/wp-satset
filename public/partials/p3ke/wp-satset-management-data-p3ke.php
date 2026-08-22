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
        <h1 class="text-center" style="margin:3rem;">Manajemen Data P3KE<br>Tahun Anggaran <?php echo $tahun_anggaran; ?></h1>
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
            <button class="btn btn-primary" onclick="tambah_data_p3ke();"><i class="dashicons dashicons-plus"></i> Tambah Data P3KE</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Id P3KE</th>
                    <th class="text-center">Kode Kemendagri</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Padan Dukcapil</th>
                    <th class="text-center">Kepala Keluarga</th>
                    <th class="text-center">Jenis Kelamin</th>
                    <th class="text-center">Tanggal Lahir</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kabupaten / Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Desa</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center">Pekerjaan</th>
                    <th class="text-center">Pendidikan</th>
                    <th class="text-center">Rumah</th>
                    <th class="text-center">Punya Tabungan</th>
                    <th class="text-center">Jenis Desil</th>
                    <th class="text-center">Jenis Atap</th>
                    <th class="text-center">Jenis Dinding</th>
                    <th class="text-center">Jenis Lantai</th>
                    <th class="text-center">Sumber Penerangan</th>   
                    <th class="text-center">Bahan Bakar Memasak</th>   
                    <th class="text-center">Sumber Air Minum</th>
                    <th class="text-center">Fasilitas BAB</th>
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

<div class="modal fade mt-4" id="modalTambahDataP3KE" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataP3KELabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataP3KELabel">Data P3KE</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label for='tahun_anggaran' style='display:inline-block'>Tahun Anggaran</label>
                    <input type='text' id='tahun_anggaran' name="tahun_anggaran" class="form-control" value ="<?php echo $tahun_anggaran; ?>" disabled>
                </div> 
                <div class="form-group">
                    <label for='id_p3ke' style='display:inline-block'>Id P3KE</label>
                    <input type='text' id='id_p3ke' name="id_p3ke" class="form-control" placeholder=''>
                </div> 
                <div class="form-group">
                    <label for='kode_kemendagri' style='display:inline-block'>Kode Kemendagri</label>
                    <input type='text' id='kode_kemendagri' name="kode_kemendagri" class="form-control" placeholder=''>
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
                    <label for='kepala_keluarga' style='display:inline-block'>Kepala Keluarga</label>
                    <input type="text" id='kepala_keluarga' name="kepala_keluarga" class="form-control" placeholder=''/>
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
                    <label for='alamat' style='display:inline-block'>Alamat</label>
                    <input type="text" id='alamat' name="alamat" class="form-control" placeholder=''/>
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
                    <label for='rumah' style='display:inline-block'>Rumah</label>
                    <input type="text" id='rumah' name="rumah" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='punya_tabungan' style='display:inline-block'>Tabungan</label>
                    <input type="text" id='punya_tabungan' name="punya_tabungan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='jenis_desil' style='display:inline-block'>Jenis Desil</label>
                    <input type="text" id='jenis_desil' name="jenis_desil" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='jenis_atap' style='display:inline-block'>Jenis Atap</label>
                    <input type="text" id='jenis_atap' name="jenis_atap" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='jenis_dinding' style='display:inline-block'>Jenis Dinding</label>
                    <input type="text" id='jenis_dinding' name="jenis_dinding" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='jenis_lantai' style='display:inline-block'>Jenis Lantai</label>
                    <input type="text" id='jenis_lantai' name="jenis_lantai" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='sumber_penerangan' style='display:inline-block'>Sumber Penerangan</label>
                    <input type="text" id='sumber_penerangan' name="sumber_penerangan" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='bahan_bakar_memasak' style='display:inline-block'>Bahan Bakar Memasak</label>
                    <input type="text" id='bahan_bakar_memasak' name="bahan_bakar_memasak" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='sumber_air_minum' style='display:inline-block'>Sumber Air Minum</label>
                    <input type="text" id='sumber_air_minum' name="sumber_air_minum" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='fasilitas_bab' style='display:inline-block'>Fasilitas BAB</label>
                    <input type="text" id='fasilitas_bab' name="fasilitas_bab" class="form-control" placeholder=''/>
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
    get_data_p3ke();
});

function get_data_p3ke(){
    if(typeof datap3ke == 'undefined'){
        window.datap3ke = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_p3ke',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'tahun_anggaran' : '<?php echo $tahun_anggaran; ?>'
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
                    "data": 'kode_kemendagri',
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
                    "data": 'kepala_keluarga',
                    className: "text-center"
                },
                {
                    "data": 'jenis_kelamin',
                    className: "text-center"
                },
                {
                    "data": 'tanggal_lahir',
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
                    "data": 'alamat',
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
                    "data": 'rumah',
                    className: "text-center"
                },
                {
                    "data": 'punya_tabungan',
                    className: "text-center"
                },
                {
                    "data": 'jenis_desil',
                    className: "text-center"
                },
                {
                    "data": 'jenis_atap',
                    className: "text-center"
                },
                {
                    "data": 'jenis_dinding',
                    className: "text-center"
                },
                {
                    "data": 'jenis_lantai',
                    className: "text-center"
                },
                {
                    "data": 'sumber_penerangan',
                    className: "text-center"
                },
                {
                    "data": 'bahan_bakar_memasak',
                    className: "text-center"
                },
                {
                    "data": 'sumber_air_minum',
                    className: "text-center"
                },
                {
                    "data": 'fasilitas_bab',
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
        datap3ke.draw();
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
                    'action' : 'hapus_data_p3ke_by_id',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'id'     : id
                },
                dataType: 'json',
                success:function(response){
                    jQuery('#wrap-loading').hide();
                    if(response.status == 'success'){
                        get_data_p3ke(); 
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
            'action': 'get_data_p3ke_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#id_p3ke').val(res.data.id_p3ke);
                jQuery('#kode_kemendagri').val(res.data.kode_kemendagri);
                jQuery('#nik').val(res.data.nik);
                jQuery('#padan_dukcapil').val(res.data.padan_dukcapil);
                jQuery('#kepala_keluarga').val(res.data.kepala_keluarga);
                jQuery('#jenis_kelamin').val(res.data.jenis_kelamin);
                jQuery('#tanggal_lahir').val(res.data.tanggal_lahir);
                jQuery('#provinsi').val(res.data.provinsi);
                jQuery('#kabkot').val(res.data.kabkot);
                jQuery('#kecamatan').val(res.data.kecamatan);
                jQuery('#desa').val(res.data.desa);
                jQuery('#alamat').val(res.data.alamat);
                jQuery('#pekerjaan').val(res.data.pekerjaan);
                jQuery('#pendidikan').val(res.data.pendidikan);
                jQuery('#rumah').val(res.data.rumah);
                jQuery('#punya_tabungan').val(res.data.punya_tabungan);
                jQuery('#jenis_desil').val(res.data.jenis_desil);
                jQuery('#jenis_atap').val(res.data.jenis_atap);
                jQuery('#jenis_dinding').val(res.data.jenis_dinding);
                jQuery('#jenis_lantai').val(res.data.jenis_lantai);
                jQuery('#sumber_penerangan').val(res.data.sumber_penerangan);
                jQuery('#bahan_bakar_memasak').val(res.data.bahan_bakar_memasak);
                jQuery('#sumber_air_minum').val(res.data.sumber_air_minum);
                jQuery('#fasilitas_bab').val(res.data.fasilitas_bab);
                jQuery('#penerima_bpnt').val(res.data.penerima_bpnt);
                jQuery('#penerima_bpum').val(res.data.penerima_bpum);
                jQuery('#penerima_bst').val(res.data.penerima_bst);
                jQuery('#penerima_pkh').val(res.data.penerima_pkh);
                jQuery('#penerima_sembako').val(res.data.penerima_sembako);
                jQuery('#resiko_stunting').val(res.data.resiko_stunting);
                jQuery('#tahun_anggaran').val(res.data.tahun_anggaran);
                jQuery('#modalTambahDataP3KE').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_p3ke(){
    jQuery('#id_data').val('');
    jQuery('#id_p3ke').val('');
    jQuery('#kode_kemendagri').val('');
    jQuery('#nik').val('');
    jQuery('#padan_dukcapil').val('');
    jQuery('#kepala_keluarga').val('');
    jQuery('#jenis_kelamin').val('');
    jQuery('#tanggal_lahir').val('');
    jQuery('#provinsi').val('');
    jQuery('#kabkot').val('');
    jQuery('#kecamatan').val('');
    jQuery('#desa').val('');
    jQuery('#alamat').val('');
    jQuery('#pekerjaan').val('');
    jQuery('#pendidikan').val('');
    jQuery('#rumah').val('');
    jQuery('#punya_tabungan').val('');
    jQuery('#jenis_desil').val('');
    jQuery('#jenis_atap').val('');
    jQuery('#jenis_dinding').val('');
    jQuery('#jenis_lantai').val('');
    jQuery('#sumber_penerangan').val('');
    jQuery('#bahan_bakar_memasak').val('');
    jQuery('#sumber_air_minum').val('');
    jQuery('#fasilitas_bab').val('');
    jQuery('#penerima_bpnt').val('');
    jQuery('#penerima_bpum').val('');
    jQuery('#penerima_bst').val('');
    jQuery('#penerima_pkh').val('');
    jQuery('#penerima_sembako').val('');
    jQuery('#resiko_stunting').val('');
    jQuery('#tahun_anggaran').val('');
    jQuery('#modalTambahDataP3KE').modal('show');
}

function submitTambahDataFormP3KE(){
    var id_data = jQuery('#id_data').val();
    var id_p3ke = jQuery('#id_p3ke').val();
    if(id_p3ke == ''){
        return alert('Data id_p3ke tidak boleh kosong!');
    }
    var kode_kemendagri = jQuery('#kode_kemendagri').val();
    if(kode_kemendagri == ''){
        return alert('Data kode_kemendagri tidak boleh kosong!');
    }
    var nik = jQuery('#nik').val();
    if(nik == ''){
        return alert('Data nik tidak boleh kosong!');
    }
    var padan_dukcapil = jQuery('#padan_dukcapil').val();
    if(padan_dukcapil == ''){
        return alert('Data padan_dukcapil tidak boleh kosong!');
    }
    var kepala_keluarga = jQuery('#kepala_keluarga').val();
    if(kepala_keluarga == ''){
        return alert('Data kepala_keluarga tidak boleh kosong!');
    }
    var jenis_kelamin = jQuery('#jenis_kelamin').val();
    if(jenis_kelamin == ''){
        return alert('Data jenis_kelamin tidak boleh kosong!');
    }
    var tanggal_lahir = jQuery('#tanggal_lahir').val();
    if(tanggal_lahir == ''){
        return alert('Data tanggal_lahir tidak boleh kosong!');
    }
    var provinsi = jQuery('#provinsi').val();
    if(provinsi == ''){
        return alert('Data provinsi tidak boleh kosong!');
    }
    var kabkot = jQuery('#kabkot').val();
    if(kabkot == ''){
        return alert('Data kabkot tidak boleh kosong!');
    }
    var kecamatan = jQuery('#kecamatan').val();
    if(kecamatan == ''){
        return alert('Data kecamatan tidak boleh kosong!');
    }
    var desa = jQuery('#desa').val();
    if(desa == ''){
        return alert('Data desa tidak boleh kosong!');
    }
    var alamat = jQuery('#alamat').val();
    if(alamat == ''){
        return alert('Data alamat tidak boleh kosong!');
    }
    var pekerjaan = jQuery('#pekerjaan').val();
    if(pekerjaan == ''){
        return alert('Data pekerjaan tidak boleh kosong!');
    }
    var pendidikan = jQuery('#pendidikan').val();
    if(pendidikan == ''){
        return alert('Data pendidikan tidak boleh kosong!');
    }
    var rumah = jQuery('#rumah').val();
    if(rumah == ''){
        return alert('Data rumah tidak boleh kosong!');
    }
    var punya_tabungan = jQuery('#punya_tabungan').val();
    if(punya_tabungan == ''){
        return alert('Data punya_tabungan tidak boleh kosong!');
    }
    var jenis_desil = jQuery('#jenis_desil').val();
    if(jenis_desil == ''){
        return alert('Data jenis_desil tidak boleh kosong!');
    }
    var jenis_atap = jQuery('#jenis_atap').val();
    if(jenis_atap == ''){
        return alert('Data jenis_atap tidak boleh kosong!');
    }
    var jenis_dinding = jQuery('#jenis_dinding').val();
    if(jenis_dinding == ''){
        return alert('Data jenis_dinding tidak boleh kosong!');
    }
    var jenis_lantai = jQuery('#jenis_lantai').val();
    if(jenis_lantai == ''){
        return alert('Data jenis_lantai tidak boleh kosong!');
    }
    var sumber_penerangan = jQuery('#sumber_penerangan').val();
    if(sumber_penerangan == ''){
        return alert('Data sumber_penerangan tidak boleh kosong!');
    }
    var bahan_bakar_memasak = jQuery('#bahan_bakar_memasak').val();
    if(bahan_bakar_memasak == ''){
        return alert('Data bahan_bakar_memasak tidak boleh kosong!');
    }
    var sumber_air_minum = jQuery('#sumber_air_minum').val();
    if(sumber_air_minum == ''){
        return alert('Data sumber_air_minum tidak boleh kosong!');
    }
    var fasilitas_bab = jQuery('#fasilitas_bab').val();
    if(fasilitas_bab == ''){
        return alert('Data fasilitas_bab tidak boleh kosong!');
    }
    var penerima_bpnt = jQuery('#penerima_bpnt').val();
    if(penerima_bpnt == ''){
        return alert('Data penerima_bpnt tidak boleh kosong!');
    }
    var penerima_bpum = jQuery('#penerima_bpum').val();
    if(penerima_bpum == ''){
        return alert('Data penerima_bpum tidak boleh kosong!');
    }
    var penerima_bst = jQuery('#penerima_bst').val();
    if(penerima_bst == ''){
        return alert('Data penerima_bst tidak boleh kosong!');
    }
    var penerima_pkh = jQuery('#penerima_pkh').val();
    if(penerima_pkh == ''){
        return alert('Data penerima_pkh tidak boleh kosong!');
    }
    var penerima_sembako = jQuery('#penerima_sembako').val();
    if(penerima_sembako == ''){
        return alert('Data penerima_sembako tidak boleh kosong!');
    }
    var resiko_stunting = jQuery('#resiko_stunting').val();
    if(resiko_stunting == ''){
        return alert('Data resiko_stunting tidak boleh kosong!');
    }
    var tahun_anggaran = jQuery('#tahun_anggaran').val();
    if(tahun_anggaran == ''){
        return alert('Data tahun_anggaran tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_p3ke',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': id_data,
            'id_p3ke': id_p3ke,
            'kode_kemendagri': kode_kemendagri,
            'nik': nik,
            'padan_dukcapil': padan_dukcapil,
            'kepala_keluarga': kepala_keluarga,
            'jenis_kelamin': jenis_kelamin,
            'tanggal_lahir': tanggal_lahir,
            'provinsi': provinsi,
            'kabkot': kabkot,
            'kecamatan': kecamatan,
            'desa': desa,
            'alamat': alamat,
            'pekerjaan': pekerjaan,
            'pendidikan': pendidikan,
            'rumah': rumah,
            'punya_tabungan': punya_tabungan,
            'jenis_desil': jenis_desil,
            'jenis_atap': jenis_atap,
            'jenis_dinding': jenis_dinding,
            'jenis_lantai': jenis_lantai,
            'sumber_penerangan': sumber_penerangan,
            'bahan_bakar_memasak': bahan_bakar_memasak,
            'sumber_air_minum': sumber_air_minum,
            'fasilitas_bab': fasilitas_bab,
            'penerima_bpnt': penerima_bpnt,
            'penerima_bpum': penerima_bpum,
            'penerima_bst': penerima_bst,
            'penerima_pkh': penerima_pkh,
            'penerima_sembako': penerima_sembako,
            'resiko_stunting': resiko_stunting,
            'tahun_anggaran': <?php echo $tahun_anggaran; ?>,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataP3KE').modal('hide');
            if(res.status == 'success'){
                get_data_p3ke();
                location.reload(); 
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