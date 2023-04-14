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
    <h1 class="text-center" style="margin:3rem;">Manajemen Data Batas Kecamatan</h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_batas_kecamatan();"><i class="dashicons dashicons-plus"></i> Tambah Data Batas Kecamatan</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0" style="font-family:\'Open Sans\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Provno</th>
                    <th class="text-center">Kabkotno</th>
                    <th class="text-center">Kecno</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kabupaten / Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>

<div class="modal fade mt-4" id="modalTambahDataBatasKecamatan" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataBatasKecamatanLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataBatasKecamatanLabel">Data Batas Kecamatan</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label for='provno' style='display:inline-block'>Provno</label>
                    <input type="text" id='provno' name="provno" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='kabkotno' style='display:inline-block'>kabkotno</label>
                    <input type="text" id='kabkotno' name="kabkotno" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='kecno' style='display:inline-block'>Kecno</label>
                    <input type="text" id='kecno' name="kecno" class="form-control" placeholder=''/>
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
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormBatasKecamatan()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>    
jQuery(document).ready(function(){
    get_data_batas_kecamatan();
});

function get_data_batas_kecamatan(){
    if(typeof databatas_kecamatan == 'undefined'){
        window.databatas_kecamatan = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_batas_kecamatan',
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
                    "data": 'provno',
                    className: "text-center"
                },
                {
                    "data": 'kabkotno',
                    className: "text-center"
                },
                {
                    "data": 'kecno',
                    className: "text-center"
                },
                {
                    "data": 'kecamatan',
                    className: "text-center"
                },
                {
                    "data": 'kabkot',
                    className: "text-center"
                },
                {
                    "data": 'provinsi',
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
    //     databatasecamatan.update();
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
//                     get_data_batas_kecamatan();
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
                    'action' : 'hapus_data_batas_kecamatan_by_id',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'id'     : id
                },
                dataType: 'json',
                success:function(response){
                    jQuery('#wrap-loading').hide();
                    if(response.status == 'success'){
                        get_data_batas_kecamatan(); 
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
            'action': 'get_data_batas_kecamatan_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#provno').val(res.data.provno);
                jQuery('#kabkotno').val(res.data.kabkotno);
                jQuery('#kecno').val(res.data.kecno);
                jQuery('#kecamatan').val(res.data.kecamatan);
                jQuery('#kabkot').val(res.data.kabkot);
                jQuery('#provinsi').val(res.data.provinsi);
                jQuery('#modalTambahDataBatasKecamatan').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_batas_kecamatan(){
    jQuery('#id_data').val('');
    jQuery('#provno').val('');
    jQuery('#kabkotno').val('');
    jQuery('#kecno').val('');
    jQuery('#provinsi').val('');
    jQuery('#kabkot').val('');
    jQuery('#kecamatan').val('');
    jQuery('#modalTambahDataBatasKecamatan').modal('show');
}

function submitTambahDataFormBatasKecamatan(){
    var id_data = jQuery('#id_data').val();
    var provno = jQuery('#provno').val();
    if(provno == ''){
        return alert('Data provno tidak boleh kosong!');
    }
    var kabkotno = jQuery('#kabkotno').val();
    if(kabkotno == ''){
        return alert('Data kabkotno tidak boleh kosong!');
    }
    var kecno = jQuery('#kecno').val();
    if(kecno == ''){
        return alert('Data kecno tidak boleh kosong!');
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

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_batas_kecamatan',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': id_data,
            'provno': provno,
            'kabkotno': kabkotno,
            'kecno': kecno,
            'provinsi': provinsi,
            'kabkot': kabkot,
            'kecamatan': kecamatan,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataBatasKecamatan').modal('hide');
            if(res.status == 'success'){
                get_data_batas_kecamatan();
            }else{
                jQuery('#wrap-loading').hide();
            }
        }
    });
}
</script>