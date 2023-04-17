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
    <h1 class="text-center" style="margin:3rem;">Manajemen Data TBC</h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_tbc();"><i class="dashicons dashicons-plus"></i> Tambah Data TBC</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0" style="font-family:\'Open Sans\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Tanggal Register</th>
                    <th class="text-center">No Register Kabupaten</th>
                    <th class="text-center">Nomer Register Fasyankes</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Umur</th>
                    <th class="text-center">Jenis Kelamin</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center">Pindahann dari Fasyankes</th>
                    <th class="text-center">Tindak Lanjut</th>
                    <th class="text-center">Tanggal Mulai Pengobatan</th>
                    <th class="text-center">Hasil Akhir Pengobatan</th>
                    <th class="text-center">Status Pengobatan</th>
                    <th class="text-center">Keterangan</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>

<div class="modal fade mt-4" id="modalTambahDataTBC" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataTBCLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataTBCLabel">Data TBC</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                <div class="form-group">
                    <label for='tanggal_register' style='display:inline-block'>Tanggal Register</label>
                    <input type='text' id='tanggal_register' name="tanggal_register" class="form-control" placeholder=''>
                </div> 
                    <label for='no_reg_fasyankes' style='display:inline-block'>No Register Fasyankes</label>
                    <input type='text' id='no_reg_fasyankes' name="no_reg_fasyankes" class="form-control" placeholder=''>
                </div>
                <div class="form-group">
                    <label for='no_reg_kabkot' style='display:inline-block'>No Register Kabupaten / Kot</label>
                    <input type="text" id='no_reg_kabkot' name="no_reg_kabkot" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='nik' style='display:inline-block'>Nik</label>
                    <input type="text" id='nik' name="nik" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='nama' style='display:inline-block'>Kabupaten / Kota</label>
                    <input type="text" id='nama' name="nama" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='umur' style='display:inline-block'>Umur</label>
                    <input type="text" id='umur' name="umur" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='jenis_kelamin' style='display:inline-block'>Jenis Kelamin</label>
                    <input type="text" id='jenis_kelamin' name="jenis_kelamin" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='alamat' style='display:inline-block'>Alamat</label>
                    <input type="text" id='alamat' name="alamat" class="form-control" placeholder=''/>
                <div class="form-group">
                <div class="form-group">
                    <label for='pindahan_dari_fasyankes' style='display:inline-block'>Pindahan dari Fasyankes</label>
                    <input type="text" id='pindahan_dari_fasyankes' name="pindahan_dari_fasyankes" class="form-control" placeholder=''/>
                </div>
                    <label for='tindak_lanjut' style='display:inline-block'>Tindak Lanjut</label>
                    <input type="text" id='tindak_lanjut' name="tindak_lanjut" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='tanggal_mulai_pengobatan' style='display:inline-block'>Tanggal Mulai Pengobatan</label>
                    <input type="text" id='tanggal_mulai_pengobatan' name="tanggal_mulai_pengobatan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='hasil_akhir_pengobatan' style='display:inline-block'>Hasil Akhir Pengobatan</label>
                    <input type="text" id='hasil_akhir_pengobatan' name="hasil_akhir_pengobatan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='status_pengobatan' style='display:inline-block'>Status Pengobatan</label>
                    <input type="text" id='status_pengobatan' name="status_pengobatan" class="form-control" placeholder=''/>
                </div> 

                <div class="form-group">
                    <label for='keterangan' style='display:inline-block'>Keterangan</label>
                    <input type="text" id='keterangan' name="keterangan" class="form-control" placeholder=''/>
                </div> 
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormTBC()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>    
jQuery(document).ready(function(){
    get_data_tbc();
});

function get_data_tbc(){
    if(typeof datatbc == 'undefined'){
        window.datatbc = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_tbc',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                }
            },
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
            order: [[0, 'asc']],
            "drawCallback": function( settings ){
                jQuery("#wrap-loading").hide();
            },
            "columns": [
                {
                    "data": 'tanggal_register',
                    className: "text-center"
                },
                {
                    "data": 'no_reg_fasyankes',
                    className: "text-center"
                },
                {
                    "data": 'no_reg_kabkot',
                    className: "text-center"
                },
                {
                    "data": 'nik',
                    className: "text-center"
                },
                {
                    "data": 'nama',
                    className: "text-center"
                },
                {
                    "data": 'umur',
                    className: "text-center"
                },
                {
                    "data": 'jenis_kelamin',
                    className: "text-center"
                },
                {
                    "data": 'alamat',
                    className: "text-center"
                },
                {
                    "data": 'pindahan_dari_fasyankes',
                    className: "text-center"
                },
                {
                    "data": 'tindak_lanjut',
                    className: "text-center"
                },
                {
                    "data": 'tanggal_mulai_pengobatan',
                    className: "text-center"
                },
                {
                    "data": 'hasil_akhir_pengobatan',
                    className: "text-center"
                },
                {
                    "data": 'status_pengobatan',
                    className: "text-center"
                },
                {
                    "data": 'keterangan',
                    className: "text-center"
                },
                {
                    "data": 'aksi',
                    className: "text-center"
                }
            ]
        });
    }
    // else{
    //     datatbc.update();
    // }
}

// function hapus_data(_id){
//     if(confirm('Apakah anda yakin untuk menghapus data ini?')){
//         jQuery('#wrap-loading').show();
//         jQuery.ajax({
//             method: 'post',
//             url: '<?php echo admin_url('admin-ajax.php'); ?>',
//             dataType: 'json',
//             data:{
//                 'action': 'hapus_data_p3ke_by_id',
//                 'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
//                 'id': _id
//             },
//             success: function(res){
//                 alert(res.message);
//                 if(res.status == 'success'){
//                     get_data_tbc();
//                 }else{
//                     jQuery('#wrap-loading').hide();
//                 }
//             }
//         })
//     }
// }
function hapus_data(id){
        let confirmDelete = confirm("Apakah anda yakin akan menghapus data ini?");
        if(confirmDelete){
            jQuery('#wrap-loading').show();
            jQuery.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type:'post',
                data:{
                    'action' : 'hapus_data_tbc_by_id',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'id'     : id
                },
                dataType: 'json',
                success:function(response){
                    jQuery('#wrap-loading').hide();
                    if(response.status == 'success'){
                        get_data_tbc(); 
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
            'action': 'get_data_tbc_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#tanggal_register').val(res.data.tanggal_register);
                jQuery('#no_reg_fasyankes').val(res.data.no_reg_fasyankes);
                jQuery('#no_reg_kabkot').val(res.data.no_reg_kabkot);
                jQuery('#nik').val(res.data.nik);
                jQuery('#nama').val(res.data.nama);
                jQuery('#umur').val(res.data.umur);
                jQuery('#alamat').val(res.data.alamat);
                jQuery('#jenis_kelamin').val(res.data.jenis_kelamin);
                jQuery('#pindahan_dari_fasyankes').val(res.data.pindahan_dari_fasyankes);
                jQuery('#tindak_lanjut').val(res.data.tindak_lanjut);
                jQuery('#tanggal_mulai_pengobatan').val(res.data.tanggal_mulai_pengobatan);
                jQuery('#hasil_akhir_pengobatan').val(res.data.hasil_akhir_pengobatan);
                jQuery('#status_pengobatan').val(res.data.status_pengobatan);
                jQuery('#keterangan').val(res.data.keterangan);
                jQuery('#modalTambahDataTBC').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_tbc(){
    jQuery('#id_data').val('');
    jQuery('#tanggal_register').val('');
    jQuery('#no_reg_fasyankes').val('');
    jQuery('#no_reg_kabkot').val('');
    jQuery('#nik').val('');
    jQuery('#nama').val('');
    jQuery('#umur').val('');
    jQuery('#jenis_kelamin').val('');
    jQuery('#alamat').val('');
    jQuery('#pindahan_dari_fasyankes').val('');
    jQuery('#tindak_lanjut').val('');
    jQuery('#tanggal_mulai_pengobatan').val('');
    jQuery('#hasil_akhir_pengobatan').val('');
    jQuery('#status_pengobatan').val('');
    jQuery('#keterangan').val('');
    jQuery('#modalTambahDataTBC').modal('show');
}

function submitTambahDataFormTBC(){
    var id_data = jQuery('#id_data').val();
    var tanggal_register = jQuery('#tanggal_register').val();
    if(tanggal_register == ''){
        return alert('Data tanggal_register tidak boleh kosong!');
    }
    var no_reg_fasyankes = jQuery('#no_reg_fasyankes').val();
    if(no_reg_fasyankes == ''){
        return alert('Data no_reg_fasyankes tidak boleh kosong!');
    }
    var no_reg_kabkot = jQuery('#no_reg_kabkot').val();
    if(no_reg_kabkot == ''){
        return alert('Data no_reg_kabkot tidak boleh kosong!');
    }
    var nik = jQuery('#nik').val();
    if(nik == ''){
        return alert('Data nik tidak boleh kosong!');
    }
    var nama = jQuery('#nama').val();
    if(nama == ''){
        return alert('Data nama tidak boleh kosong!');
    }
    var umur = jQuery('#umur').val();
    if(umur == ''){
        return alert('Data umur tidak boleh kosong!');
    }
    var jenis_kelamin = jQuery('#jenis_kelamin').val();
    if(jenis_kelamin == ''){
        return alert('Data jenis_kelamin tidak boleh kosong!');
    }
    var alamat = jQuery('#alamat').val();
    if(alamat == ''){
        return alert('Data alamat tidak boleh kosong!');
    }
    var pindahan_dari_fasyankes = jQuery('#pindahan_dari_fasyankes').val();
    if(pindahan_dari_fasyankes == ''){
        return alert('Data pindahan_dari_fasyankes tidak boleh kosong!');
    }
    var tindak_lanjut = jQuery('#tindak_lanjut').val();
    if(tindak_lanjut == ''){
        return alert('Data tindak_lanjut tidak boleh kosong!');
    }
    var tanggal_mulai_pengobatan = jQuery('#tanggal_mulai_pengobatan').val();
    if(tanggal_mulai_pengobatan == ''){
        return alert('Data tanggal_mulai_pengobatan tidak boleh kosong!');
    }
    var hasil_akhir_pengobatan = jQuery('#hasil_akhir_pengobatan').val();
    if(hasil_akhir_pengobatan == ''){
        return alert('Data hasil_akhir_pengobatan tidak boleh kosong!');
    }
    var status_pengobatan = jQuery('#status_pengobatan').val();
    if(status_pengobatan == ''){
        return alert('Data status_pengobatan tidak boleh kosong!');
    }
    var keterangan = jQuery('#keterangan').val();

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_tbc',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': id_data,
            'tanggal_register': tanggal_register,
            'no_reg_fasyankes': no_reg_fasyankes,
            'no_reg_kabkot': no_reg_kabkot,
            'nik': nik,
            'nama': nama,
            'umur': umur,
            'jenis_kelamin': jenis_kelamin,
            'alamat': alamat,
            'pindahan_dari_fasyankes': pindahan_dari_fasyankes,
            'tindak_lanjut': tindak_lanjut,
            'tanggal_mulai_pengobatan': tanggal_mulai_pengobatan,
            'hasil_akhir_pengobatan': hasil_akhir_pengobatan,
            'status_pengobatan': status_pengobatan,
            'keterangan': keterangan,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataTBC').modal('hide');
            if(res.status == 'success'){
                get_data_tbc();
            }else{
                jQuery('#wrap-loading').hide();
            }
        }
    });
}
</script>