<style type="text/css">
    .wrap-table{
        overflow: auto;
        max-height: 100vh; 
        width: 100%; 
    }
</style>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<div class="cetak">
	<div style="padding: 10px;margin:0 0 3rem 0;">
		<input type="hidden" value="<?php echo get_option( '_crb_api_key_extension' ); ?>" id="api_key">
	<h1 class="text-center" style="margin:3rem;">Manajemen Data P3KE</h1>
		<div style="margin-bottom: 25px;">
			<button class="btn btn-primary" onclick="tambah_data();">Tambah Data P3KE</button>
		</div>
        <div class="wrap-table">
		<table id="management_data_table" cellpadding="2" cellspacing="0" style="font-family:\'Open Sans\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
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
                    <th class="text-center">Kepala Keluarga</th>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Padan Dukcapil</th>
                    <th class="text-center">Jenis Kelamin</th>
                    <th class="text-center">Tanggal Lahir</th>
                    <th class="text-center">Pekerjaan</th>
                    <th class="text-center">Pendidikan</th>
                    <th class="text-center">Rumah</th>
                    <th class="text-center">Punya Tabungan</th>
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
                <h5 class="modal-title" id="modalTambahDataP3KELabel">Penambahan Data P3KEn</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div>
                    <label for='id_p3ke' style='display:inline-block'>Id P3KE</label>
                    <input type='text' id='id_p3ke' name="id_p3ke" style='display:block;width:100%;' placeholder=''>
                </div> 
                <div>
                    <label for='kode_kemendagri' style='display:inline-block'>Kode Kemendagri</label>
                    <input type='text' id='kode_kemendagri' name="kode_kemendagri" style='display:block;width:100%;' placeholder=''>
                </div>
                <div>
                    <label for='tambah_nik' style='display:inline-block'>Tambah NIK</label>
                    <input type='text' id='tambah_nik' name="tambah_nik" style='display:block;width:100%;' placeholder=''>
                </div>
                <div>
                    <label for='tambah_padan_dukcapil' style='display:inline-block'>Tambah Data Padan Dukcapil</label>
                    <input type="text" id='tambah_padan_dukcapil' name="tambah_padan_dukcapil" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='kepala_keluarga' style='display:inline-block'>Kepala Keluarga</label>
                    <input type="text" id='kepala_keluarga' name="kepala_keluarga" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='Jenis_kelamin' style='display:inline-block'>Jenis Kelamin</label>
                    <input type="text" id='Jenis_kelamin' name="Jenis_kelamin" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='tanggal_lahir' style='display:inline-block'>Tanggal Lahir</label>
                    <input type="text" id='tanggal_lahir' name="tanggal_lahir" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='provinsi' style='display:inline-block'>Provinsi</label>
                    <input type="text" id='provinsi' name="provinsi" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='kabkot' style='display:inline-block'>Kabupaten / Kota</label>
                    <input type="text" id='kabkot' name="kabkot" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='kecamatan' style='display:inline-block'>Kecamatan</label>
                    <input type="text" id='kecamatan' name="kecamatan" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='desa' style='display:inline-block'>Desa</label>
                    <input type="text" id='desa' name="desa" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='alamat' style='display:inline-block'>Alamat</label>
                    <input type="text" id='alamat' name="alamat" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='rumah' style='display:inline-block'>Rumah</label>
                    <input type="text" id='rumah' name="rumah" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='pekerjaan' style='display:inline-block'>Pekerjaan</label>
                    <input type="text" id='pekerjaan' name="pekerjaan" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='pendidikan' style='display:inline-block'>Pendidikan</label>
                    <input type="text" id='pendidikan' name="pendidikan" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='punya_tabungan' style='display:inline-block'>Tabungan</label>
                    <input type="text" id='punya_tabungan' name="punya_tabungan" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='jenis_desil' style='display:inline-block'>Jenis Desil</label>
                    <input type="text" id='jenis_desil' name="jenis_desil" style='display:block;width:100%;' placeholder=''/>
                </div>
                <div>
                    <label for='jenis_atap' style='display:inline-block'>Jenis Atap</label>
                    <input type="text" id='jenis_atap' name="jenis_atap" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='jenis_dinding' style='display:inline-block'>Jenis Dinding</label>
                    <input type="text" id='jenis_dinding' name="jenis_dinding" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='jenis_lantai' style='display:inline-block'>Jenis Lantai</label>
                    <input type="text" id='jenis_lantai' name="jenis_lantai" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='sumber_penerangan' style='display:inline-block'>Sumber Penerangan</label>
                    <input type="text" id='sumber_penerangan' name="sumber_penerangan" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='bahan_bakar_memasak' style='display:inline-block'>Bahan Bakar Memasak</label>
                    <input type="text" id='bahan_bakar_memasak' name="bahan_bakar_memasak" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='sumber_air_minum' style='display:inline-block'>Sumber Air Minum</label>
                    <input type="text" id='sumber_air_minum' name="sumber_air_minum" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='fasilitas_bab' style='display:inline-block'>Fasilitas BAB</label>
                    <input type="text" id='fasilitas_bab' name="fasilitas_bab" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='penerima_bpnt' style='display:inline-block'>Penerima BPNT</label>
                    <input type="text" id='penerima_bpnt' name="penerima_bpnt" style='display:block;width:100%;' placeholder=''/>
                </div> 
                <div>
                    <label for='penerima_bpum' style='display:inline-block'>Penerima BPUM</label>
                    <input type="text" id='penerima_bpum' name="penerima_bpum" style='display:block;width:100%;' placeholder=''/>
                </div>  
                <div>
                    <label for='penerima_bst' style='display:inline-block'>Penerima BST</label>
                    <input type="text" id='penerima_bst' name="penerima_bst" style='display:block;width:100%;' placeholder=''/>
                </div>  
                <div>
                    <label for='penerima_pkh' style='display:inline-block'>Penerima PKH</label>
                    <input type="text" id='penerima_pkh' name="penerima_pkh" style='display:block;width:100%;' placeholder=''/>
                </div>  
                <div>
                    <label for='penerima_sembako' style='display:inline-block'>Penerima Sembako</label>
                    <input type="text" id='penerima_sembako' name="penerima_sembako" style='display:block;width:100%;' placeholder=''/>
                </div>  
                <div>
                    <label for='resiko_stunting' style='display:inline-block'>Resiko Stunting</label>
                    <input type="text" id='resiko_stunting' name="resiko_stunting" style='display:block;width:100%;' placeholder=''/>
                </div> 
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormP3KE()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/dataTables.buttons.min.js"></script> 
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/buttons/1.3.1/js/buttons.html5.min.js"></script>
<script>    
jQuery(document).ready(function(){
    get_data_p3ke();
});

function get_data_p3ke(){
    jQuery("#wrap-loading").show();
    if(typeof dataP3KE == 'undefined'){
        window.dataP3KE = jQuery('#management_data_table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_p3ke',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                }
            },
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, "All"]],
            order: [[0, 'asc']],
            "initComplete":function( settings, json){
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
                    "data": 'kepala_keluarga',
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
                    "data": 'tanggal_lahir',
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
                    "data": 'aksi',
                    className: "text-center"
                }
            ]
        });
    }else{
        dataP3KE.update();
    }
}

// get tambah_data
function get_tambah_data(){
    jQuery("#wrap-loading").show();
    globalThis.penambahanDataP3KE = jQuery('#management_data_table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'get_data_p3ke1',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
        }
        },
        "initComplete":function( settings, json){
            jQuery("#wrap-loading").hide();
        },
        "columns": [
            { 
                "data": "nik",
                className: "text-center"
            },
            { 
                "data": "padan_dukcapil_p3ke",
                className: "text-center"
            }
        ]
    });
}

//show tambah data
function tambah_data(){
    jQuery("#modalTambahDataP3KE .modal-title").html("Penambahan Data P3KE");
    jQuery("#modalTambahDataP3KE .submitBtn")
        .attr("onclick", 'submitTambahDataFormP3KE()')
        .attr("disabled", false)
        .text("Simpan");
    jQuery('#modalTambahDataP3KE').modal('show');
    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'get_data_p3ke1',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
        }

    });
    jQuery('#wrap-loading').hide();
}

//show submit tambah data
// function submitTambahDataFormP3KE(){
//         jQuery("#wrap-loading").show()
//         let nik = jQuery('#tambah_nik').val()
//         let padan_dukcapil_p3ke = jQuery("#tambah_padan_dukcapil").val()
//         if(nik.trim() == '' || padan_dukcapil_p3ke == ''){
//             jQuery("#wrap-loading").hide()
//             alert("Ada yang kosong, Harap diisi semua")
//             return false
//         }else{
//             jQuery.ajax({
//                 url: '<?php echo admin_url('admin-ajax.php'); ?>',
//             dataType: 'json',
//             data:{
//                 'action': 'get_data_p3ke1',
//                 'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
//                 ''
//                 data:{
//                     'action'                : 'submit_add_schedule',
//                     'api_key'               : jQuery("#api_key").val(),
//                     // 'get_nik'               : nik,
//                     // 'get_padan_dukcapil'    : padan_dukcapil_p3ke,
//                 }
//             },
//                 beforeSend: function() {
//                     jQuery('.submitBtn').attr('disabled','disabled')
//                 },
//                 success: function(response){
//                     jQuery('#modalTambahDataP3KE').modal('hide')
//                     jQuery('#wrap-loading').hide()
//                     if(response.status == 'success'){
//                         alert('Data berhasil ditambahkan')
//                         penjadwalanTable.ajax.reload()
//                     }else{
//                         alert(response.message)
//                     }
//                     jQuery('#tambah_nik').val('')
//                     jQuery("#padan_dukcapil_p3ke").val('')
//                 }
//             })
//         }
//         jQuery('#modalTambahDataP3KE').modal('hide');
//     }

</script>