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
    <h1 class="text-center" style="margin:3rem;">Manajemen Data Stunting</h1>
        <div style="margin-bottom: 25px;">
            <button class="btn btn-primary" onclick="tambah_data_stunting();"><i class="dashicons dashicons-plus"></i> Tambah Data Stunting</button>
        </div>
        <div class="wrap-table">
        <table id="management_data_table" cellpadding="2" cellspacing="0" style="font-family:\'Open Sans\',-apple-system,BlinkMacSystemFont,\'Segoe UI\',sans-serif; border-collapse: collapse; width:100%; overflow-wrap: break-word;" class="table table-bordered">
            <thead>
                <tr>
                    <th class="text-center">NIK</th>
                    <th class="text-center">Nama</th>
                    <th class="text-center">Jenis Kelamin</th>
                    <th class="text-center">Tanggal Lahir</th>
                    <th class="text-center">Berat Badan saat Lahir</th>
                    <th class="text-center">Tinggi Badan saat Lahir</th>
                    <th class="text-center">Nama Orang Tua</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kabupaten / Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Puskesmas</th>
                    <th class="text-center">Desa</th>
                    <th class="text-center">Posyandu</th>
                    <th class="text-center">Rt</th>
                    <th class="text-center">Rw</th>
                    <th class="text-center">Alamat</th>
                    <th class="text-center">Usia saat Ukur</th>
                    <th class="text-center">tanggal pengukuran</th>
                    <th class="text-center">Berat</th>
                    <th class="text-center">Tinggi</th>
                    <th class="text-center">Lingkar Lengan Atas</th>
                    <th class="text-center">Bb per Usia</th>
                    <th class="text-center">zs Bb per Usia</th>
                    <th class="text-center">tb per Usia</th>
                    <th class="text-center">zs tb per Usia</th>
                    <th class="text-center">Bb per tb</th>
                    <th class="text-center">zs Bb per tb</th>
                    <th class="text-center">Naik Berat Badan</th>
                    <th class="text-center">PMT diterima per Kg</th>
                    <th class="text-center">Jumlah Vitamin A</th>
                    <th class="text-center">KPSP</th>
                    <th class="text-center">KIA</th>
                    <th class="text-center" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        </div>
    </div>          
</div>

<div class="modal fade mt-4" id="modalTambahDataStunting" tabindex="-1" role="dialog" aria-labelledby="modalTambahDataStuntingLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTambahDataStuntingLabel">Data Stunting</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type='hidden' id='id_data' name="id_data" placeholder=''>
                <div class="form-group">
                    <label for='nik' style='display:inline-block'>NIK</label>
                    <input type='text' id='nik' name="nik" class="form-control" placeholder=''>
                </div> 
                <div class="form-group">
                    <label for='nama' style='display:inline-block'>Nama</label>
                    <input type='text' id='nama' name="nama" class="form-control" placeholder=''>
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
                    <label for='bb_lahir' style='display:inline-block'>Berat Badan saat Lahir</label>
                    <input type="text" id='bb_lahir' name="bb_lahir" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='tb_lahir' style='display:inline-block'>Tinggi Badan saat Lahir</label>
                    <input type="text" id='tb_lahir' name="tb_lahir" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='nama_ortu' style='display:inline-block'>Nama Orang Tua</label>
                    <input type='text' id='nama_ortu' name="nama_ortu" class="form-control" placeholder=''>
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
                    <label for='puskesmas' style='display:inline-block'>Puskesmas</label>
                    <input type="text" id='puskesmas' name="puskesmas" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='posyandu' style='display:inline-block'>Posyandu</label>
                    <input type="text" id='posyandu' name="posyandu" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='rt' style='display:inline-block'>RT</label>
                    <input type="text" id='rt' name="rt" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='rw' style='display:inline-block'>RW</label>
                    <input type="text" id='rw' name="rw" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='alamat' style='display:inline-block'>Alamat</label>
                    <input type="text" id='alamat' name="alamat" class="form-control" placeholder=''/>
                </div>
                <div class="form-group">
                    <label for='usia_saat_ukur' style='display:inline-block'>Usia saat Diukur</label>
                    <input type="text" id='usia_saat_ukur' name="usia_saat_ukur" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='tanggal_pengukuran' style='display:inline-block'>Tanggal Pengukuran</label>
                    <input type="text" id='tanggal_pengukuran' name="tanggal_pengukuran" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='berat' style='display:inline-block'>Berat</label>
                    <input type="text" id='berat' name="berat" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='tinggi' style='display:inline-block'>Tinggi</label>
                    <input type="text" id='tinggi' name="tinggi" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='lingkar_lengan_atas' style='display:inline-block'>Lingkar Lengan Atas</label>
                    <input type="text" id='lingkar_lengan_atas' name="lingkar_lengan_atas" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='bb_per_u' style='display:inline-block'>BB per U</label>
                    <input type="text" id='bb_per_u' name="bb_per_u" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='zs_bb_per_u' style='display:inline-block'>Zs Bb per U</label>
                    <input type="text" id='zs_bb_per_u' name="zs_bb_per_u" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='tb_per_u' style='display:inline-block'>Tb per U</label>
                    <input type="text" id='tb_per_u' name="tb_per_u" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='zs_tb_per_u' style='display:inline-block'>Zs Tb per U</label>
                    <input type="text" id='zs_tb_per_u' name="zs_tb_per_u" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='bb_per_tb' style='display:inline-block'>Bb per Tb</label>
                    <input type="text" id='bb_per_tb' name="bb_per_tb" class="form-control" placeholder=''/>
                </div>  
                <div class="form-group">
                    <label for='zs_bb_per_tb' style='display:inline-block'>Zs Bb per Tb</label>
                    <input type="text" id='zs_bb_per_tb' name="zs_bb_per_tb" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='naik_berat_badan' style='display:inline-block'>Naik Berat Badan</label>
                    <input type="text" id='naik_berat_badan' name="naik_berat_badan" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='pmt_diterima_per_kg' style='display:inline-block'>PMT diterima per Kg</label>
                    <input type="text" id='pmt_diterima_per_kg' name="pmt_diterima_per_kg" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='jml_vit_a' style='display:inline-block'>Jumlah Vitamin A</label>
                    <input type="text" id='jml_vit_a' name="jml_vit_a" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='kpsp' style='display:inline-block'>KPSP</label>
                    <input type="text" id='kpsp' name="kpsp" class="form-control" placeholder=''/>
                </div> 
                <div class="form-group">
                    <label for='kia' style='display:inline-block'>KIA</label>
                    <input type="text" id='kia' name="kia" class="form-control" placeholder=''/>
                </div> 
            </div> 
            <div class="modal-footer">
                <button class="btn btn-primary submitBtn" onclick="submitTambahDataFormStunting()">Simpan</button>
                <button type="submit" class="components-button btn btn-secondary" data-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<script>    
jQuery(document).ready(function(){
    get_data_stunting();
});

function get_data_stunting(){
    if(typeof datastunting == 'undefined'){
        window.datastunting = jQuery('#management_data_table').on('preXhr.dt', function(e, settings, data){
            jQuery("#wrap-loading").show();
        }).DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'post',
                dataType: 'json',
                data:{
                    'action': 'get_datatable_stunting',
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
                    "data": 'nik',
                    className: "text-center"
                },
                {
                    "data": 'nama',
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
                    "data": 'bb_lahir',
                    className: "text-center"
                },
                {
                    "data": 'tb_lahir',
                    className: "text-center"
                },
                {
                    "data": 'nama_ortu',
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
                    "data": 'puskesmas',
                    className: "text-center"
                },
                {
                    "data": 'desa',
                    className: "text-center"
                },
                {
                    "data": 'posyandu',
                    className: "text-center"
                },
                {
                    "data": 'rt',
                    className: "text-center"
                },
                {
                    "data": 'rw',
                    className: "text-center"
                },
                {
                    "data": 'alamat',
                    className: "text-center"
                },
                {
                    "data": 'usia_saat_ukur',
                    className: "text-center"
                },
                {
                    "data": 'tanggal_pengukuran',
                    className: "text-center"
                },
                {
                    "data": 'berat',
                    className: "text-center"
                },
                {
                    "data": 'tinggi',
                    className: "text-center"
                },
                {
                    "data": 'lingkar_lengan_atas',
                    className: "text-center"
                },
                {
                    "data": 'bb_per_u',
                    className: "text-center"
                },
                {
                    "data": 'tb_per_u',
                    className: "text-center"
                },
                {
                    "data": 'zs_bb_per_u',
                    className: "text-center"
                },
                {
                    "data": 'zs_tb_per_u',
                    className: "text-center"
                },
                {
                    "data": 'bb_per_tb',
                    className: "text-center"
                },
                {
                    "data": 'zs_bb_per_tb',
                    className: "text-center"
                },
                {
                    "data": 'naik_berat_badan',
                    className: "text-center"
                },
                {
                    "data": 'pmt_diterima_per_kg',
                    className: "text-center"
                },{
                    "data": 'jml_vit_a',
                    className: "text-center"
                },
                {
                    "data": 'kpsp',
                    className: "text-center"
                },
                {
                    "data": 'kia',
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
    //     datastunting.update();
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
//                     get_data_stunting();
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
                    'action' : 'hapus_data_stunting_by_id',
                    'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                    'id'     : id
                },
                dataType: 'json',
                success:function(response){
                    jQuery('#wrap-loading').hide();
                    if(response.status == 'success'){
                        get_data_stunting(); 
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
            'action': 'get_data_stunting_by_id',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id': _id,
        },
        success: function(res){
            if(res.status == 'success'){
                jQuery('#id_data').val(res.data.id);
                jQuery('#nik').val(res.data.nik);
                jQuery('#nama').val(res.data.nama);
                jQuery('#nama_ortu').val(res.data.nama_ortu);
                jQuery('#tb_lahir').val(res.data.tb_lahir);
                jQuery('#jenis_kelamin').val(res.data.jenis_kelamin);
                jQuery('#tanggal_lahir').val(res.data.tanggal_lahir);
                jQuery('#provinsi').val(res.data.provinsi);
                jQuery('#kabkot').val(res.data.kabkot);
                jQuery('#kecamatan').val(res.data.kecamatan);
                jQuery('#desa').val(res.data.desa);
                jQuery('#alamat').val(res.data.alamat);
                jQuery('#rt').val(res.data.rt);
                jQuery('#puskesmas').val(res.data.puskesmas);
                jQuery('#posyandu').val(res.data.posyandu);
                jQuery('#rw').val(res.data.rw);
                jQuery('#bb_lahir').val(res.data.bb_lahir);
                jQuery('#usia_saat_ukur').val(res.data.usia_saat_ukur);
                jQuery('#tanggal_pengukuran').val(res.data.tanggal_pengukuran);
                jQuery('#berat').val(res.data.berat);
                jQuery('#tinggi').val(res.data.tinggi);
                jQuery('#lingkar_lengan_atas').val(res.data.lingkar_lengan_atas);
                jQuery('#bb_per_u').val(res.data.bb_per_u);
                jQuery('#tb_per_u').val(res.data.tb_per_u);
                jQuery('#zs_bb_per_u').val(res.data.zs_bb_per_u);
                jQuery('#zs_tb_per_u').val(res.data.zs_tb_per_u);
                jQuery('#bb_per_tb').val(res.data.bb_per_tb);
                jQuery('#zs_bb_per_tb').val(res.data.zs_bb_per_tb);
                jQuery('#naik_berat_badan').val(res.data.naik_berat_badan);
                jQuery('#pmt_diterima_per_kg').val(res.data.pmt_diterima_per_kg);
                jQuery('#jml_vit_a').val(res.data.jml_vit_a);
                jQuery('#kpsp').val(res.data.kpsp);
                jQuery('#kia').val(res.data.kia);
            }else{
                alert(res.message);
            }
            jQuery('#wrap-loading').hide();
        }
    });
}

//show tambah data
function tambah_data_stunting(){
    jQuery('#id_data').val('');
    jQuery('#nik').val('');
    jQuery('#nama').val('');
    jQuery('#jenis_kelamin').val('');
    jQuery('#tanggal_lahir').val('');
    jQuery('#bb_lahir').val('');
    jQuery('#tb_lahir').val('');
    jQuery('#nama_ortu').val('');
    jQuery('#provinsi').val('');
    jQuery('#kabkot').val('');
    jQuery('#kecamatan').val('');
    jQuery('#desa').val('');
    jQuery('#puskesmas').val('');
    jQuery('#posyandu').val('');
    jQuery('#rt').val('');
    jQuery('#rw').val('');
    jQuery('#alamat').val('');
    jQuery('#usia_saat_ukur').val('');
    jQuery('#tanggal_pengukuran').val('');
    jQuery('#berat').val('');
    jQuery('#jenis_lantai').val('');
    jQuery('#tinggi').val('');
    jQuery('#lingkar_lengan_atas').val('');
    jQuery('#bb_per_u').val('');
    jQuery('#zs_bb_per_u').val('');
    jQuery('#tb_per_u').val('');
    jQuery('#zs_tb_per_u').val('');
    jQuery('#bb_per_tb').val('');
    jQuery('#zs_bb_per_tb').val('');
    jQuery('#naik_berat_badan').val('');
    jQuery('#jml_vit_a').val('');
    jQuery('#kpsp').val('');
    jQuery('#kia').val('');
    jQuery('#modalTambahDataStunting').modal('show');
}

function submitTambahDataFormStunting(){
    var id_data = jQuery('#id_data').val();
    var nik = jQuery('#nik').val();
    if(nik == ''){
        return alert('Data nik tidak boleh kosong!');
    }
    var nama = jQuery('#nama').val();
    if(nama == ''){
        return alert('Data nama tidak boleh kosong!');
    }
    var tanggal_lahir = jQuery('#tanggal_lahir').val();
    if(tanggal_lahir == ''){
        return alert('Data tanggal_lahir tidak boleh kosong!');
    }
    var jenis_kelamin = jQuery('#jenis_kelamin').val();
    if(jenis_kelamin == ''){
        return alert('Data jenis_kelamin tidak boleh kosong!');
    }
    var bb_lahir = jQuery('#bb_lahir').val();
    if(bb_lahir == ''){
        return alert('Data bb_lahir tidak boleh kosong!');
    }
    var tb_lahir = jQuery('#tb_lahir').val();
    if(tb_lahir == ''){
        return alert('Data tb_lahir tidak boleh kosong!');
    }
    var nama_ortu = jQuery('#nama_ortu').val();
    if(nama_ortu == ''){
        return alert('Data nama_ortu tidak boleh kosong!');
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
    var puskesmas = jQuery('#puskesmas').val();
    if(puskesmas == ''){
        return alert('Data puskesmas tidak boleh kosong!');
    }
    var posyandu = jQuery('#posyandu').val();
    if(posyandu == ''){
        return alert('Data posyandu tidak boleh kosong!');
    }
    var rt = jQuery('#rt').val();
    if(rt == ''){
        return alert('Data rt tidak boleh kosong!');
    }
    var rw = jQuery('#rw').val();
    if(rw == ''){
        return alert('Data rw tidak boleh kosong!');
    }
    var alamat = jQuery('#alamat').val();
    if(alamat == ''){
        return alert('Data alamat tidak boleh kosong!');
    }
    var usia_saat_ukur = jQuery('#usia_saat_ukur').val();
    if(usia_saat_ukur == ''){
        return alert('Data usia_saat_ukur tidak boleh kosong!');
    }
    var tanggal_pengukuran = jQuery('#tanggal_pengukuran').val();
    if(tanggal_pengukuran == ''){
        return alert('Data tanggal_pengukuran tidak boleh kosong!');
    }
    var berat = jQuery('#berat').val();
    if(berat == ''){
        return alert('Data berat tidak boleh kosong!');
    }
    var jenis_lantai = jQuery('#jenis_lantai').val();
    if(jenis_lantai == ''){
        return alert('Data jenis_lantai tidak boleh kosong!');
    }
    var tinggi = jQuery('#tinggi').val();
    if(tinggi == ''){
        return alert('Data tinggi tidak boleh kosong!');
    }
    var lingkar_lengan_atas = jQuery('#lingkar_lengan_atas').val();
    if(lingkar_lengan_atas == ''){
        return alert('Data lingkar_lengan_atas tidak boleh kosong!');
    }
    var bb_per_u = jQuery('#bb_per_u').val();
    if(bb_per_u == ''){
        return alert('Data bb_per_u tidak boleh kosong!');
    }
    var zs_bb_per_u = jQuery('#zs_bb_per_u').val();
    if(zs_bb_per_u == ''){
        return alert('Data zs_bb_per_u tidak boleh kosong!');
    }
    var tb_per_u = jQuery('#tb_per_u').val();
    if(tb_per_u == ''){
        return alert('Data tb_per_u tidak boleh kosong!');
    }
    var zs_tb_per_u = jQuery('#zs_tb_per_u').val();
    if(zs_tb_per_u == ''){
        return alert('Data zs_tb_per_u tidak boleh kosong!');
    }
    var bb_per_tb = jQuery('#bb_per_tb').val();
    if(bb_per_tb == ''){
        return alert('Data bb_per_tb tidak boleh kosong!');
    }
    var zs_bb_per_tb = jQuery('#zs_bb_per_tb').val();
    if(zs_bb_per_tb == ''){
        return alert('Data zs_bb_per_tb tidak boleh kosong!');
    }
    var naik_berat_badan = jQuery('#naik_berat_badan').val();
    if(naik_berat_badan == ''){
        return alert('Data naik_berat_badan tidak boleh kosong!');
    }
    var jml_vit_a = jQuery('#jml_vit_a').val();
    if(jml_vit_a == ''){
        return alert('Data jml_vit_a tidak boleh kosong!');
    }
    var kpsp = jQuery('#kpsp').val();
    if(kpsp == ''){
        return alert('Data kpsp tidak boleh kosong!');
    }
    var kia = jQuery('#kia').val();
    if(kia == ''){
        return alert('Data kia tidak boleh kosong!');
    }

    jQuery('#wrap-loading').show();
    jQuery.ajax({
        method: 'post',
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        dataType: 'json',
        data:{
            'action': 'tambah_data_stunting',
            'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
            'id_data': id_data,
            'nik': nik,
            'nama': nama,
            'jenis_kelamin': jenis_kelamin,
            'tanggal_lahir': tanggal_lahir,
            'bb_lahir': bb_lahir,
            'tb_lahir': tb_lahir,
            'nama_ortu': nama_ortu,
            'provinsi': provinsi,
            'kabkot': kabkot,
            'kecamatan': kecamatan,
            'desa': desa,
            'puskesmas': puskesmas,
            'posyandu': posyandu,
            'rt': rt,
            'rw': rw,
            'alamat': alamat,
            'usia_saat_ukur': usia_saat_ukur,
            'tanggal_pengukuran': tanggal_pengukuran,
            'berat': berat,
            'jenis_lantai': jenis_lantai,
            'tinggi': tinggi,
            'lingkar_lengan_atas': lingkar_lengan_atas,
            'bb_per_u': bb_per_u,
            'zs_bb_per_u': zs_bb_per_u,
            'tb_per_u': tb_per_u,
            'zs_tb_per_u': zs_tb_per_u,
            'bb_per_tb': bb_per_tb,
            'zs_bb_per_tb': zs_bb_per_tb,
            'naik_berat_badan': naik_berat_badan,
            'jml_vit_a': jml_vit_a,
            'kpsp': kpsp,
            'kia': kia,
        },
        success: function(res){
            alert(res.message);
            jQuery('#modalTambahDataStunting').modal('hide');
            if(res.status == 'success'){
                get_data_stunting();
            }else{
                jQuery('#wrap-loading').hide();
            }
        }
    });
}
</script>