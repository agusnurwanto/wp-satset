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
    <h1 class="text-center" style="margin:3rem;">Manajemen Data Batas Desa</h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_batas_desa();"><i class="dashicons dashicons-plus"></i> Tambah Data Batas Desa</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0" style="font-family:\'Open Sans\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">Id Desa</th>
                    <th class="text-center">Desa</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Kabupaten / Kota</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Area</th>
                    <th class="text-center">Perimeter</th>
                    <th class="text-center">Hectares</th>
                    <th class="text-center">Ukuran Kota</th>
                    <th class="text-center">Pemusatan</th>
                    <th class="text-center">Jumlah Penduduk</th>
                    <th class="text-center">Provno</th>
                    <th class="text-center">Kabkotno</th>
                    <th class="text-center">Kecno</th>
                    <th class="text-center">Desano</th>
                    <th class="text-center">Id 2012</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>

<div class="modal fade mt-4" id="modalTambahDataBatasDesa" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataBatasDesaLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataBatasDesaLabel">Data Batas Desa</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label for='id_desa' style='display:inline-block'>Id Desa</label>
                    <input type='text' id='id_desa' name="id_desa" class="form-control" placeholder=''>
                </div>
                <div class="form-group">
                    <label for='desa' style='display:inline-block'>Desa</label>
                    <input type="text" id='desa' name="desa" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='kecamatan' style='display:inline-block'>Kecamatan</label>
                    <input type="text" id='kecamatan' name="kecamatan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='kab_kot' style='display:inline-block'>Kabupaten / Kota</label>
                    <input type="text" id='kab_kot' name="kab_kot" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='provinsi' style='display:inline-block'>Provinsi</label>
                    <input type="text" id='provinsi' name="provinsi" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='area' style='display:inline-block'>Area</label>
                    <input type='text' id='area' name="area" class="form-control" placeholder=''>
                </div> 
                <div class="form-group">
                    <label for='perimeter' style='display:inline-block'>Perimeter</label>
                    <input type="text" id='perimeter' name="perimeter" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='hectares' style='display:inline-block'>Hectares</label>
                    <input type="text" id='hectares' name="hectares" class="form-control" placeholder=''/>
                <div class="form-group">
                <div class="form-group">
                    <label for='ukuran_kot' style='display:inline-block'>Ukuran Kota</label>
                    <input type="text" id='ukuran_kot' name="ukuran_kot" class="form-control" placeholder=''/>
                </div>
                    <label for='pemusatan' style='display:inline-block'>Pemusatan</label>
                    <input type="text" id='pemusatan' name="pemusatan" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='jumplah_pen' style='display:inline-block'>Jumlah Penduduk</label>
                    <input type="text" id='jumplah_pen' name="jumplah_pen" class="form-control" placeholder=''/>
                </div>
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
                    <label for='desano' style='display:inline-block'>desano</label>
                    <input type="text" id='desano' name="desano" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='id2012' style='display:inline-block'>id2012</label>
                    <input type="text" id='id2012' name="id2012" class="form-control" placeholder=''/>
                </div> 
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormBatasDesa()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>    
jQuery(document).ready(function(){
    get_data_batas_desa();
});

function get_data_batas_desa(){
    if(typeof databatas_desa == 'undefined'){
        window.databatas_desa = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_batas_desa',
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
                    "data": 'id_desa',
                    className: "text-center"
                },
                {
                    "data": 'desa',
                    className: "text-center"
                },
                {
                    "data": 'kecamatan',
                    className: "text-center"
                },
                {
                    "data": 'kab_kot',
                    className: "text-center"
                },
                {
                    "data": 'provinsi',
                    className: "text-center"
                },
                {
                    "data": 'area',
                    className: "text-center"
                },
                {
                    "data": 'perimeter',
                    className: "text-center"
                },
                {
                    "data": 'hectares',
                    className: "text-center"
                },
                {
                    "data": 'ukuran_kot',
                    className: "text-center"
                },
                {
                    "data": 'pemusatan',
                    className: "text-center"
                },
                {
                    "data": 'jumplah_pen',
                    className: "text-center"
                },
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
                    "data": 'desano',
                    className: "text-center"
                },
                {
                    "data": 'id2012',
                    className: "text-center"
                },
                {
                    "data": 'aksi',
                    className: "text-center"
                }
            ]
        });
    }else{
        databatas_desa.draw();
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
                    'action' : 'hapus_data_batas_desa_by_id',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'id'     : id
                },
                dataType: 'json',
                success:function(response){
                    jQuery('#wrap-loading').hide();
                    if(response.status == 'success'){
                        get_data_batas_desa(); 
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
            'action': 'get_data_batas_desa_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#id_desa').val(res.data.id_desa);
                jQuery('#desa').val(res.data.desa);
                jQuery('#kecamatan').val(res.data.kecamatan);
                jQuery('#kab_kot').val(res.data.kab_kot);
                jQuery('#provinsi').val(res.data.provinsi);
                jQuery('#area').val(res.data.area);
                jQuery('#perimeter').val(res.data.perimeter);
                jQuery('#hectares').val(res.data.hectares);
                jQuery('#ukuran_kot').val(res.data.ukuran_kot);
                jQuery('#pemusatan').val(res.data.pemusatan);
                jQuery('#jumplah_pen').val(res.data.jumplah_pen);
                jQuery('#provno').val(res.data.provno);
                jQuery('#kabkotno').val(res.data.kabkotno);
                jQuery('#kecno').val(res.data.kecno);
                jQuery('#desano').val(res.data.desano);
                jQuery('#id2012').val(res.data.id2012);
                jQuery('#modalTambahDataBatasDesa').modal('show');
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_batas_desa(){
    jQuery('#id_data').val('');
    jQuery('#id_desa').val('');
    jQuery('#desa').val('');
    jQuery('#kecamatan').val('');
    jQuery('#kab_kot').val('');
    jQuery('#provinsi').val('');
    jQuery('#area').val('');
    jQuery('#perimeter').val('');
    jQuery('#ukuran_kot').val('');
    jQuery('#hectares').val('');
    jQuery('#pemusatan').val('');
    jQuery('#jumplah_pen').val('');
    jQuery('#provno').val('');
    jQuery('#kabkotno').val('');
    jQuery('#kecno').val('');
    jQuery('#desano').val('');
    jQuery('#id2012').val('');
    jQuery('#modalTambahDataBatasDesa').modal('show');
}

function submitTambahDataFormBatasDesa(){
    var id_data = jQuery('#id_data').val();
    var id_desa = jQuery('#id_desa').val();
    if(id_desa == ''){
        return alert('Data id_desa tidak boleh kosong!');
    }
    var desa = jQuery('#desa').val();
    if(desa == ''){
        return alert('Data desa tidak boleh kosong!');
    }
    var kecamatan = jQuery('#kecamatan').val();
    if(kecamatan == ''){
        return alert('Data kecamatan tidak boleh kosong!');
    }
    var kab_kot = jQuery('#kab_kot').val();
    if(kab_kot == ''){
        return alert('Data kab_kot tidak boleh kosong!');
    }
    var provinsi = jQuery('#provinsi').val();
    if(provinsi == ''){
        return alert('Data provinsi tidak boleh kosong!');
    }
    var area = jQuery('#area').val();
    if(area == ''){
        return alert('Data area tidak boleh kosong!');
    }
    var perimeter = jQuery('#perimeter').val();
    if(perimeter == ''){
        return alert('Data perimeter tidak boleh kosong!');
    }
    var ukuran_kot = jQuery('#ukuran_kot').val();
    if(ukuran_kot == ''){
        return alert('Data ukuran_kot tidak boleh kosong!');
    }
    var hectares = jQuery('#hectares').val();
    if(hectares == ''){
        return alert('Data hectares tidak boleh kosong!');
    }
    var pemusatan = jQuery('#pemusatan').val();
    if(pemusatan == ''){
        return alert('Data pemusatan tidak boleh kosong!');
    }
    var jumplah_pen = jQuery('#jumplah_pen').val();
    var provno = jQuery('#provno').val();
    if(provno == ''){
        return alert('Data provno tidak boleh kosong!');
    }
    var kabkotno = jQuery('#kabkotno').val();
    if(kabkotno == ''){
        return alert('Data kabkotno tidak boleh kosong!');
    }var kecno = jQuery('#kecno').val();
    if(kecno == ''){
        return alert('Data kecno tidak boleh kosong!');
    }var desano = jQuery('#desano').val();
    if(desano == ''){
        return alert('Data desano tidak boleh kosong!');
    }var id2012 = jQuery('#id2012').val();
    if(id2012 == ''){
        return alert('Data id2012 tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_batas_desa',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': id_data,
            'id_desa': id_desa,
            'desa': desa,
            'kecamatan': kecamatan,
            'kab_kot': kab_kot,
            'provinsi': provinsi,
            'area': area,
            'perimeter': perimeter,
            'ukuran_kot': ukuran_kot,
            'hectares': hectares,
            'pemusatan': pemusatan,
            'jumplah_pen': jumplah_pen,
            'provno': provno,
            'kabkotno': kabkotno,
            'kecno': kecno,
            'desano': desano,
            'id2012': id2012,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataBatasDesa').modal('hide');
            if(res.status == 'success'){
                get_data_batas_desa();
            }else{
                jQuery('#wrap-loading').hide();
            }
        }
    });
}
</script>