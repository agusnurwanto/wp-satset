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
    <h1 class="text-center" style="margin:3rem;">Manajemen Data RTLH <br>Tahun Anggaran <?php echo $tahun_anggaran; ?></h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_rtlh();"><i class="dashicons dashicons-plus"></i> Tambah Data RTLH</button>
        </div>
        <div id="wrap-action"></div>
            <div class="text-center" style="margin-top: 30px;">
                <label style="margin-left: 10px;" for="tahun_anggaran">Tahun Anggaran : </label>
                <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
                    <?php echo $select_tahun; ?>
                </select>
                <button style="margin-left: 10px; height: 45px; width: 75px;"onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
            </div>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Nama</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kabupaten / Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Desa</th>
                    <th class="text-center">Rw</th>
                    <th class="text-center">Rt</th>
                    <th class="text-center">Nilai Bantuan</th>
                    <th class="text-center">LPJ</th>
                    <th class="text-center">Tanggal LPJ</th>
                    <th class="text-center">Sumber Dana</th>
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

<div class="modal fade mt-4" id="modalTambahDataRTLH" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataRTLHLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataRTLHLabel">Data RTLH</h5>
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
                    <label for='nama' style='display:inline-block'>Nama</label>
                    <input type='text' id='nama' name="nama" class="form-control" placeholder=''>
                </div>
                <div class="form-group">
                    <label for='nik' style='display:inline-block'>NIK</label>
                    <input type='text' id='nik' name="nik" class="form-control" placeholder=''>
                </div> 
                <div class="form-group">
                    <label for='alamat' style='display:inline-block'>Alamat</label>
                    <input type="text" id='alamat' name="alamat" class="form-control" placeholder=''/>
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
                    <label for='rw' style='display:inline-block'>RW</label>
                    <input type="text" id='rw' name="rw" class="form-control" placeholder=''/>
                <div class="form-group">
                <div class="form-group">
                    <label for='rt' style='display:inline-block'>RT</label>
                    <input type="text" id='rt' name="rt" class="form-control" placeholder=''/>
                </div>
                    <label for='nilai_bantuan' style='display:inline-block'>Nilai Bantuan</label>
                    <input type="text" id='nilai_bantuan' name="nilai_bantuan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='lpj' style='display:inline-block'>LPJ</label>
                    <input type="text" id='lpj' name="lpj" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='tgl_lpj' style='display:inline-block'>Tanggal LPJ</label>
                    <input type="text" id='tgl_lpj' name="tgl_lpj" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='sumber_dana' style='display:inline-block'>Sumber Dana</label>
                    <input type="text" id='sumber_dana' name="sumber_dana" class="form-control" placeholder=''/>
                </div> 
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormRTLH()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>    
jQuery(document).ready(function(){
    get_data_rtlh();
});

function get_data_rtlh(){
    if(typeof datartlh == 'undefined'){
        window.datartlh = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_rtlh',
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
                    "data": 'nama',
                    className: "text-center"
                },
                {
                    "data": 'nik',
                    className: "text-center"
                },
                {
                    "data": 'alamat',
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
                    "data": 'rw',
                    className: "text-center"
                },
                {
                    "data": 'rt',
                    className: "text-center"
                },
                {
                    "data": 'nilai_bantuan',
                    className: "text-center"
                },
                {
                    "data": 'lpj',
                    className: "text-center"
                },
                {
                    "data": 'tgl_lpj',
                    className: "text-center"
                },
                {
                    "data": 'sumber_dana',
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
    }
    else{
        datartlh.draw();
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
                    'action' : 'hapus_data_rtlh_by_id',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'id'     : id
                },
                dataType: 'json',
                success:function(response){
                    jQuery('#wrap-loading').hide();
                    if(response.status == 'success'){
                        get_data_rtlh(); 
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
            'action': 'get_data_rtlh_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#nama').val(res.data.nama);
                jQuery('#nik').val(res.data.nik);
                jQuery('#alamat').val(res.data.alamat);
                jQuery('#provinsi').val(res.data.provinsi);
                jQuery('#kabkot').val(res.data.kabkot);
                jQuery('#kecamatan').val(res.data.kecamatan);
                jQuery('#desa').val(res.data.desa);
                jQuery('#rw').val(res.data.rw);
                jQuery('#rt').val(res.data.rt);
                jQuery('#nilai_bantuan').val(res.data.nilai_bantuan);
                jQuery('#lpj').val(res.data.lpj);
                jQuery('#tgl_lpj').val(res.data.tgl_lpj);
                jQuery('#sumber_dana').val(res.data.sumber_dana);
                jQuery('#tahun_anggaran').val(res.data.tahun_anggaran);
                jQuery('#modalTambahDataRTLH').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_rtlh(){
    jQuery('#id_data').val('');
    jQuery('#nik').val('');
    jQuery('#nama').val('');
    jQuery('#alamat').val('');
    jQuery('#provinsi').val('');
    jQuery('#kabkot').val('');
    jQuery('#kecamatan').val('');
    jQuery('#desa').val('');
    jQuery('#rt').val('');
    jQuery('#rw').val('');
    jQuery('#nilai_bantuan').val('');
    jQuery('#lpj').val('');
    jQuery('#tgl_lpj').val('');
    jQuery('#sumber_dana').val('');
    jQuery('#tahun_anggaran').val('');
    jQuery('#modalTambahDataRTLH').modal('show');
}

function submitTambahDataFormRTLH(){
    var id_data = jQuery('#id_data').val();
    var nik = jQuery('#nik').val();
    if(nik == ''){
        return alert('Data nik tidak boleh kosong!');
    }
    var nama = jQuery('#nama').val();
    if(nama == ''){
        return alert('Data nama tidak boleh kosong!');
    }
    var alamat = jQuery('#alamat').val();
    if(alamat == ''){
        return alert('Data alamat tidak boleh kosong!');
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
    var rt = jQuery('#rt').val();
    if(rt == ''){
        return alert('Data rt tidak boleh kosong!');
    }
    var rw = jQuery('#rw').val();
    if(rw == ''){
        return alert('Data rw tidak boleh kosong!');
    }
    var nilai_bantuan = jQuery('#nilai_bantuan').val();
    if(nilai_bantuan == ''){
        return alert('Data nilai_bantuan tidak boleh kosong!');
    }
    var lpj = jQuery('#lpj').val();
    if(lpj == ''){
        return alert('Data lpj tidak boleh kosong!');
    }
    var tgl_lpj = jQuery('#tgl_lpj').val();
    if(tgl_lpj == ''){
        return alert('Data tgl_lpj tidak boleh kosong!');
    }
    var sumber_dana = jQuery('#sumber_dana').val();
    if(sumber_dana == ''){
        return alert('Data sumber_dana tidak boleh kosong!');
    }
    var tahun_anggaran = jQuery('#tahun_anggaran').val();
    if(tahun_anggaran == ''){
        return alert('Data tahun anggaran tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_rtlh',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': id_data,
            'nik': nik,
            'nama': nama,
            'alamat': alamat,
            'provinsi': provinsi,
            'kabkot': kabkot,
            'kecamatan': kecamatan,
            'desa': desa,
            'rt': rt,
            'rw': rw,
            'nilai_bantuan': nilai_bantuan,
            'lpj': lpj,
            'tgl_lpj': tgl_lpj,
            'sumber_dana': sumber_dana,
            'tahun_anggaran': <?php echo $tahun_anggaran; ?>,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataRTLH').modal('hide');
            if(res.status == 'success'){
                get_data_rtlh();
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