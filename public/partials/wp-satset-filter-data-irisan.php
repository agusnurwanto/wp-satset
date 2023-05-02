<?php
global $wpdb;

$login = false;
if(is_user_logged_in()){
    $current_user = wp_get_current_user();
    if($this->functions->user_has_role($current_user->ID, 'administrator')){
        $login = true;
    }
}
if($login == false){
    die('Anda tidak punya akses ke halaman ini!');
}

$data = $wpdb->get_results("select id2012, kecamatan, id from data_batas_kecamatan order by id2012 ASC", ARRAY_A);
$kec = "<option value='-1'>Semua Kecamatan</option>";
foreach($data as $val){
    $kec .= "<option value='$val[kecamatan]'>($val[id2012]) $val[kecamatan]</option>";
}
$data_desa = $wpdb->get_results("select desa, id2012, id, kecamatan from data_batas_desa order by id2012 ASC", ARRAY_A);
$desa = "<option value='-1'>Semua Desa</option>";
foreach($data_desa as $val){
    $desa .= "<option value='$val[desa]'>($val[id2012]) $val[desa]</option>";
}
?>
<style type="text/css">
    .card {
        margin-top: 20px;
    }
</style>
<style type="text/css">
    .wrap-pesan{
        padding: 10px; 
        width: 100%; 
        margin-top: 35px;
    }
    .isi-pesan .wrap-table{
        overflow: auto;
        max-height: 100vh; 
        width: 100%; 
    }
    .isi-pesan h3{
        margin-top: 35px;
    }
</style>
<div class="cetak">
    <div style="padding: 10px;">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center">Data Irisan</h1>
                <h2 class="text-center"><?php echo get_option("_crb_prov_satset"); ?><br><?php echo get_option("_crb_kab_satset"); ?></h2>
    </div>
</div>

<form id="formid" onsubmit="return false; " style="width: 500px; margin: auto;">
    <div class="form-group">
        <label>Pilih Kecamatan</label>
        <select class="form-control" id="kec">
            <?php echo $kec; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Pilih Desa</label>
        <select class="form-control" id="desa">
            <?php echo $desa; ?>
        </select>
    </div>
    <div class="row">
        <legend class="col-form-label col-sm-3 pt-0">Data Irisan :</legend>
        <div class="col-sm-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="p3ke" value="p3ke">P3KE</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="stunting" value="stunting">Stunting</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="tbc" value="tbc">TBC</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="rtlh" value="rtlh">RTLH</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="dtks" value="dtks">DTKS</label>
            </div>
        </div>    
    </div>
    <div class="form-group">
        <span class="btn btn-primary" type="button" onclick="return false" id="cari" style="margin-left: 5px;">Cari Data</span>
    </div>
</form>
<div class="wrap-pesan">
    <div id="pesan" class="isi-pesan">
    </div>
    <div id="pesan1" class="isi-pesan">
    </div>
    <div id="pesan2" class="isi-pesan">
    </div>
    <div id="pesan3" class="isi-pesan">
    </div>
    <div id="pesan4" class="isi-pesan">
    </div>
</div>
<script>
    var data_desa = <?php echo json_encode($data_desa); ?>;
    jQuery(document).ready(function(){
        jQuery("#cari").click(function(){
            cari_data_filter_satset(jQuery('#irisan').val());
        });
        jQuery("#irisan").keyup(function(event) {
            event.preventDefault();
            if (event.keyCode === 13) {
                jQuery("#cari").click();
            }
        });
        jQuery('#kec').on('change', function(){
            var kec = jQuery(this).val();
            if(kec != '-1'){
                var html = '<option value="-1">Semua Desa</option>';
                data_desa.map(function(b, i){
                    if(kec == b.kecamatan){
                        html += '<option value="'+b.desa+'">('+b.id2012+') '+b.desa+'</option>';
                    }
                });
                jQuery('#desa').html(html);
            }
        });
    })

    function cari_data_filter_satset(irisan) {
        var desa = jQuery('#desa').val();
        if(desa == '-1'){
            desa = '';
        }
        var kec = jQuery('#kec').val();
        if(kec == '-1'){
            kec = '';
        }
        var p3ke = '';
        if(jQuery('#p3ke').is(':checked')){
            p3ke = 1;
        }
        var stunting = '';
        if(jQuery('#stunting').is(':checked')){
            stunting = 1;
        }
        var tbc = '';
        if(jQuery('#tbc').is(':checked')){
            tbc = 1;
        }
        var rtlh = '';
        if(jQuery('#rtlh').is(':checked')){
            rtlh = 1;
        }
        var dtks = '';
        if(jQuery('#dtks').is(':checked')){
            dtks = 1;
        }
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data:{
                'action': 'cari_data_filter_satset',
                'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                'desa': desa,
                'kec': kec,
                'p3ke': p3ke,
                'stunting': stunting,
                'tbc': tbc,
                'rtlh': rtlh,
                'dtks': dtks
            },
           success: function(response) {
                if(response.status == 'error'){
                    alert(response.message);
                }else{
                    let html = '';
                    if(response.data.p3ke.length > 0){
                        response.data.p3ke.map(function(value, index){
                            html +='<tr>';
                                html +='<th scope="row">'+(index+1)+'</th>';
                                html +='<td>'+value.id_p3ke+'</td>';
                                html +='<td>'+value.kode_kemendagri+'</td>';
                                html +='<td>'+value.nik+'</td>';
                                html +='<td>'+value.padan_dukcapil+'</td>';
                                html +='<td>'+value.kepala_keluarga+'</td>';
                                html +='<td>'+value.jenis_kelamin+'</td>';
                                html +='<td>'+value.tanggal_lahir+'</td>';
                                html +='<td>'+value.provinsi+'</td>';
                                html +='<td>'+value.kabkot+'</td>';
                                html +='<td>'+value.kecamatan+'</td>';
                                html +='<td>'+value.desa+'</td>';
                                html +='<td>'+value.alamat+'</td>';
                                html +='<td>'+value.pekerjaan+'</td>';
                                html +='<td>'+value.pendidikan+'</td>';
                                html +='<td>'+value.rumah+'</td>';
                                html +='<td>'+value.punya_tabungan+'</td>';
                                html +='<td>'+value.jenis_desil+'</td>';
                                html +='<td>'+value.jenis_atap+'</td>';
                                html +='<td>'+value.jenis_dinding+'</td>';
                                html +='<td>'+value.jenis_lantai+'</td>';
                                html +='<td>'+value.sumber_penerangan+'</td>';   
                                html +='<td>'+value.bahan_bakar_memasak+'</td>';   
                                html +='<td>'+value.sumber_air_minum+'</td>';
                                html +='<td>'+value.fasilitas_bab+'</td>';
                                html +='<td>'+value.penerima_bpnt+'</td>';   
                                html +='<td>'+value.penerima_bpum+'</td>';   
                                html +='<td>'+value.penerima_bst+'</td>';
                                html +='<td>'+value.penerima_pkh+'</td>';
                                html +='<td>'+value.penerima_sembako+'</td>';
                                html +='<td>'+value.resiko_stunting+'</td>';
                            html +='</tr>';
                        })
                        var pesan = ''
                            +'<h3 class="text-center">Data P3KE</h3>'
                            +'<div class="wrap-table">'
                                +'<table class="table table-bordered">'
                                    +'<thead>'
                                        +'<tr>'
                                            +'<th class="text-center" style="width: 20px;">No</th>'
                                            +'<th class="text-center">Id P3KE</th>'
                                            +'<th class="text-center">Kode Kemendagri</th>'
                                            +'<th class="text-center">NIK</th>'
                                            +'<th class="text-center">Padan Dukcapil</th>'
                                            +'<th class="text-center">Kepala Keluarga</th>'
                                            +'<th class="text-center">Jenis Kelamin</th>'
                                            +'<th class="text-center">Tanggal Lahir</th>'
                                            +'<th class="text-center">Provinsi</th>'
                                            +'<th class="text-center">Kabupaten / Kota</th>'
                                            +'<th class="text-center">Kecamatan</th>'
                                            +'<th class="text-center">Desa</th>'
                                            +'<th class="text-center">Alamat</th>'
                                            +'<th class="text-center">Pekerjaan</th>'
                                            +'<th class="text-center">Pendidikan</th>'
                                            +'<th class="text-center">Rumah</th>'
                                            +'<th class="text-center">Punya Tabungan</th>'
                                            +'<th class="text-center">Jenis Desil</th>'
                                            +'<th class="text-center">Jenis Atap</th>'
                                            +'<th class="text-center">Jenis Dinding</th>'
                                            +'<th class="text-center">Jenis Lantai</th>'
                                            +'<th class="text-center">Sumber Penerangan</th>'   
                                            +'<th class="text-center">Bahan Bakar Memasak</th>'   
                                            +'<th class="text-center">Sumber Air Minum</th>'
                                            +'<th class="text-center">Fasilitas BAB</th>'
                                            +'<th class="text-center">Penerima Bpnt</th>'   
                                            +'<th class="text-center">Penerima Bpum</th>'   
                                            +'<th class="text-center">Penerima Bst</th>'
                                            +'<th class="text-center">Penerima Pkh</th>'
                                            +'<th class="text-center">Penerima Sembako</th>'
                                            +'<th class="text-center">Resiko Stunting</th>'
                                        +'</tr>'
                                    +'</thead>'
                                    +'<tbody>'
                                        +html
                                    +'</tbody>'
                                +'</table>'
                            +'</div>';
                        jQuery('#pesan').html(pesan);
                    }else{
                        jQuery('#pesan').html('');
                    }
                    let data_all = ''
                    if(response.data.stunting.length > 0){
                        response.data.stunting.map(function(value, index){
                            data_all +='<tr>';
                                data_all +='<th scope="row">'+(index+1)+'</th>';
                                data_all +='<td>'+value.nik+'</td>';
                                data_all +='<td>'+value.nama+'</td>';
                                data_all +='<td>'+value.jenis_kelamin+'</td>';
                                data_all +='<td>'+value.tanggal_lahir+'</td>';
                                data_all +='<td>'+value.bb_lahir+'</td>';
                                data_all +='<td>'+value.tb_lahir+'</td>';
                                data_all +='<td>'+value.nama_ortu+'</td>';
                                data_all +='<td>'+value.provinsi+'</td>';
                                data_all +='<td>'+value.kabkot+'</td>';
                                data_all +='<td>'+value.kecamatan+'</td>';
                                data_all +='<td>'+value.puskesmas+'</td>';
                                data_all +='<td>'+value.desa+'</td>';
                                data_all +='<td>'+value.posyandu+'</td>';
                                data_all +='<td>'+(value.rt != null ? value.rt : "-")+'</td>';
                                data_all +='<td>'+(value.rw != null ? value.rw: "-")+'</td>';
                                data_all +='<td>'+value.alamat+'</td>';
                                data_all +='<td>'+value.usia_saat_ukur+'</td>';
                                data_all +='<td>'+value.tanggal_pengukuran+'</td>';
                                data_all +='<td>'+value.berat+'</td>';
                                data_all +='<td>'+value.tinggi+'</td>';
                                data_all +='<td>'+(value.lingkar_lengan_atas != null ? value.lingkar_lengan_atas : "-")+'</td>';
                                // data_all +='<td>'+value.lingkar_lengan_atas+'</td>';
                                data_all +='<td>'+value.bb_per_u+'</td>';
                                data_all +='<td>'+value.zs_bb_per_u+'</td>';
                                data_all +='<td>'+value.tb_per_u+'</td>';
                                data_all +='<td>'+value.zs_tb_per_u+'</td>';
                                data_all +='<td>'+value.bb_per_tb+'</td>';
                                data_all +='<td>'+value.zs_bb_per_tb+'</td>';
                                data_all +='<td>'+value.naik_berat_badan+'</td>';
                                data_all +='<td>'+value.pmt_diterima_per_kg+'</td>';
                                data_all +='<td>'+(value.jml_vit_a != null ? value.jml_vit_a : "-")+'</td>';
                                // data_all +='<td>'+value.jml_vit_a+'</td>';
                                data_all +='<td>'+value.kpsp+'</td>';
                                data_all +='<td>'+value.kia+'</td>';
                            data_all +='</tr>';
                        })
                        var pesan1 = ''
                            +'<h3 class="text-center">Data Stunting</h3>'
                            +'<div class="wrap-table">'
                                +'<table class="table table-bordered">'
                                    +'<thead>'
                                        +'<tr>'
                                            +'<th class="text-center" style="width: 20px;">No</th>'
                                            +'<th class="text-center">NIK</th>'
                                            +'<th class="text-center">Nama</th>'
                                            +'<th class="text-center">Jenis Kelamin</th>'
                                            +'<th class="text-center">Tanggal Lahir</th>'
                                            +'<th class="text-center">Berat Badan Lahir</th>'
                                            +'<th class="text-center">Tinggi Badan Lahir</th>'
                                            +'<th class="text-center">Nama Orang Tua</th>'
                                            +'<th class="text-center">Provinsi</th>'
                                            +'<th class="text-center">Kabupaten / Kota</th>'
                                            +'<th class="text-center">Kecamatan</th>'
                                            +'<th class="text-center">Puskesmas</th>'
                                            +'<th class="text-center">Desa</th>'
                                            +'<th class="text-center">Posyandu</th>'
                                            +'<th class="text-center">Rt</th>'
                                            +'<th class="text-center">Rw</th>'
                                            +'<th class="text-center">Alamat</th>'
                                            +'<th class="text-center">Usia Saat Ukur</th>'
                                            +'<th class="text-center">tanggal pengukuran</th>'
                                            +'<th class="text-center">berat</th>'
                                            +'<th class="text-center">tinggi</th>'
                                            +'<th class="text-center">lingkar lengan atas</th>'
                                            +'<th class="text-center">bb per u</th>'
                                            +'<th class="text-center">zs bb per u</th>'
                                            +'<th class="text-center">tb per u</th>'
                                            +'<th class="text-center">zs tb per u</th>'
                                            +'<th class="text-center">bb per tb</th>'
                                            +'<th class="text-center">zs bb per tb</th>'
                                            +'<th class="text-center">naik berat badan</th>'
                                            +'<th class="text-center">pmt diterima per kg</th>'
                                            +'<th class="text-center">Jumlah Vitamin A</th>'
                                            +'<th class="text-center">kpsp</th>'
                                            +'<th class="text-center">kia</th>'
                                        +'</tr>'
                                    +'</thead>'
                                    +'<tbody>'
                                        +data_all
                                    +'</tbody>'
                                +'</table>'
                            +'</div>';
                        jQuery('#pesan1').html(pesan1);
                    }else{
                        jQuery('#pesan1').html('');
                    }
                    let data_tbc = '';
                    if(response.data.tbc.length > 0){
                        response.data.tbc.map(function(value, index){
                            data_tbc +='<tr>';
                                data_tbc +='<th scope="row">'+(index+1)+'</th>';
                                data_tbc +='<td>'+value.tanggal_register+'</td>';
                                data_tbc +='<td>'+value.no_reg_fasyankes+'</td>';
                                data_tbc +='<td>'+value.no_reg_kabkot+'</td>';
                                data_tbc +='<td>'+value.nik+'</td>';
                                data_tbc +='<td>'+value.nama+'</td>';
                                data_tbc +='<td>'+value.umur+'</td>';
                                data_tbc +='<td>'+value.jenis_kelamin+'</td>';
                                data_tbc +='<td>'+value.alamat+'</td>';
                                data_tbc +='<td>'+(value.pindahan_dari_fasyankes != null ? value.pindahan_dari_fasyankes : "-")+'</td>';
                                // data_tbc +='<td>'+value.pindahan_dari_fasyankes+'</td>';
                                data_tbc +='<td>'+value.tindak_lanjut+'</td>';
                                data_tbc +='<td>'+value.tanggal_mulai_pengobatan+'</td>';
                                data_tbc +='<td>'+(value.hasil_akhir_pengobatan != null ? value.hasil_akhir_pengobatan : "-")+'</td>';
                                // data_tbc +='<td>'+value.hasil_akhir_pengobatan+'</td>';
                                data_tbc +='<td>'+value.status_pengobatan+'</td>';
                                data_tbc +='<td>'+(value.keterangan != null ? value.keterangan : "-")+'</td>';
                                // data_tbc +='<td>'+value.keterangan+'</td>';
                            data_tbc +='</tr>';
                        })
                        var pesan2 = ''
                            +'<h3 class="text-center">Data TBC</h3>'
                            +'<div class="wrap-table">'
                                +'<table class="table table-bordered">'
                                    +'<thead>'
                                        +'<tr>'
                                            +'<th class="text-center" style="width: 20px;">No</th>'
                                            +'<th class="text-center">Tanggal Register</th>'
                                            +'<th class="text-center">No Register Fasyankes</th>'
                                            +'<th class="text-center">No Register Kabupaten/ Kota</th>'
                                            +'<th class="text-center">NIK</th>'
                                            +'<th class="text-center">Nama</th>'
                                            +'<th class="text-center">Umur</th>'
                                            +'<th class="text-center">Jenis Kelamin</th>'
                                            +'<th class="text-center">Alamat</th>'
                                            +'<th class="text-center">Pindahan dari Fasyankes</th>'
                                            +'<th class="text-center">Tindak Lanjut</th>'
                                            +'<th class="text-center">Tanggal Mulai Pengobatan</th>'
                                            +'<th class="text-center">Hasil Akhir Pengobatan</th>'
                                            +'<th class="text-center">Status Pengobatan</th>'
                                            +'<th class="text-center">Keterangan</th>'
                                        +'</tr>'
                                    +'</thead>'
                                    +'<tbody>'
                                        +data_tbc
                                    +'</tbody>'
                                +'</table>'
                            +'</div>';
                            if (pindahan_dari_fasyankes = null) {
                                    console.log("0")};
                        jQuery('#pesan2').html(pesan2);
                    }else{
                        jQuery('#pesan2').html('');
                    }
                    let data_rtlh = '';
                    if(response.data.rtlh.length > 0){
                        response.data.rtlh.map(function(value, index){
                            data_rtlh +='<tr>';
                                data_rtlh +='<th scope="row">'+(index+1)+'</th>';
                                data_rtlh +='<td>'+value.nama+'</td>';
                                data_rtlh +='<td>'+value.nik+'</td>';
                                data_rtlh +='<td>'+value.alamat+'</td>';
                                data_rtlh +='<td>'+value.provinsi+'</td>';
                                data_rtlh +='<td>'+value.kabkot+'</td>';
                                data_rtlh +='<td>'+value.kecamatan+'</td>';
                                data_rtlh +='<td>'+value.desa+'</td>';
                                data_rtlh +='<td>'+value.rw+'</td>';
                                data_rtlh +='<td>'+value.rt+'</td>';
                                data_rtlh +='<td>'+(value.nilai_bantuan != null ? value.nilai_bantuan : "-")+'</td>';
                                // data_rtlh +='<td>'+value.nilai_bantuan+'</td>';
                                data_rtlh +='<td>'+value.lpj+'</td>';
                                data_rtlh +='<td>'+value.tgl_lpj+'</td>';
                                data_rtlh +='<td>'+value.sumber_dana+'</td>';
                            data_rtlh +='</tr>';
                        })
                        var pesan3 = ''
                            +'<h3 class="text-center">Data RTLH</h3>'
                            +'<div class="wrap-table">'
                                +'<table class="table table-bordered">'
                                    +'<thead>'
                                        +'<tr>'
                                            +'<th class="text-center" style="width: 20px;">No</th>'
                                            +'<th class="text-center">Nama</th>'
                                            +'<th class="text-center">NIK</th>'
                                            +'<th class="text-center">Alamat</th>'
                                            +'<th class="text-center">Provinsi</th>'
                                            +'<th class="text-center">Kabupaten / Kota</th>'
                                            +'<th class="text-center">Kecamatan</th>'
                                            +'<th class="text-center">Desa</th>'
                                            +'<th class="text-center">Rt</th>'
                                            +'<th class="text-center">Rw</th>'
                                            +'<th class="text-center">Nilai Bantuan</th>'
                                            +'<th class="text-center">LPJ</th>'
                                            +'<th class="text-center">Tanggal LPJ</th>'
                                            +'<th class="text-center">Sumber Dana</th>'
                                        +'</tr>'
                                    +'</thead>'
                                    +'<tbody>'
                                        +data_rtlh
                                    +'</tbody>'
                                +'</table>'
                            +'</div>';
                        jQuery('#pesan3').html(pesan3);
                    }else{
                        jQuery('#pesan3').html('');
                    }
                    let data_dtks = '';
                    if(response.data.dtks.length > 0){
                        response.data.dtks.map(function(value, index){
                            data_dtks +='<tr>';
                                data_dtks +='<th scope="row">'+(index+1)+'</th>';
                                data_dtks +='<td>'+value.provinsi+'</td>';
                                data_dtks +='<td>'+value.kabkot+'</td>';
                                data_dtks +='<td>'+value.kecamatan+'</td>';
                                data_dtks +='<td>'+value.desa+'</td>';
                                data_dtks +='<td>'+value.desa_kelurahan+'</td>';
                                data_dtks +='<td>'+value.id_kec+'</td>';
                                data_dtks +='<td>'+value.id_desa+'</td>';
                                data_dtks +='<td>'+value.Alamat+'</td>';
                                data_dtks +='<td>'+value.BLT+'</td>';
                                data_dtks +='<td>'+value.BLT_BBM+'</td>';
                                data_dtks +='<td>'+value.BNPT_PPKM+'</td>';
                                data_dtks +='<td>'+value.BPNT+'</td>';
                                data_dtks +='<td>'+value.BST+'</td>';
                                data_dtks +='<td>'+value.FIRST_SK+'</td>';
                                data_dtks +='<td>'+value.NIK+'</td>';
                                data_dtks +='<td>'+value.NOKK+'</td>';
                                data_dtks +='<td>'+value.Nama+'</td>';
                                data_dtks +='<td>'+value.PBI+'</td>';
                                data_dtks +='<td>'+value.PKH+'</td>';
                                data_dtks +='<td>'+value.RUTILAHU+'</td>';
                                data_dtks +='<td>'+value.SEMBAKO_ADAPTIF+'</td>';
                                data_dtks +='<td>'+value.checkBtnHamil+'</td>';
                                data_dtks +='<td>'+value.checkBtnVerifMeninggal+'</td>';
                                data_dtks +='<td>'+value.counter+'</td>';
                                data_dtks +='<td>'+value.deleted_label+'</td>';
                                data_dtks +='<td>'+value.idsemesta+'</td>';
                                data_dtks +='<td>'+value.isAktifHamil+'</td>';
                                data_dtks +='<td>'+value.is_btn_dapodik+'</td>';
                                data_dtks +='<td>'+value.is_btn_hidupkan+'</td>';
                                data_dtks +='<td>'+value.is_btn_padankan+'</td>';
                                data_dtks +='<td>'+value.is_nonaktif+'</td>';
                                data_dtks +='<td>'+value.keterangan_disabilitas+'</td>';
                                data_dtks +='<td>'+value.keterangan_meninggal+'</td>';
                                data_dtks +='<td>'+value.masih_hidup_label+'</td>';
                                data_dtks +='<td>'+value.padankan_at+'</td>';
                                data_dtks +='<td>'+value.periode_blt+'</td>';
                                data_dtks +='<td>'+value.periode_blt_bbm+'</td>';
                                data_dtks +='<td>'+value.periode_bpnt+'</td>';
                                data_dtks +='<td>'+value.periode_bpnt_ppkm+'</td>';
                                data_dtks +='<td>'+value.periode_bst+'</td>';
                                data_dtks +='<td>'+value.periode_pbi+'</td>';
                                data_dtks +='<td>'+value.periode_pkh+'</td>';
                                data_dtks +='<td>'+value.periode_rutilahu+'</td>';
                                data_dtks +='<td>'+value.periode_sembako_adaptif+'</td>';
                                data_dtks +='<td>'+value.verifyid+'</td>';
                                data_dtks +='<td>'+value.active+'</td>';
                                data_dtks +='<td>'+value.update_at+'</td>';
                            data_dtks +='</tr>';
                        })
                        var pesan4 = ''
                            +'<h4 class="text-center">Data DTKS</h4>'
                            +'<div class="wrap-table">'
                                +'<table class="table table-bordered">'
                                    +'<thead>'
                                        +'<tr>'
                                            +'<th class="text-center" style="width: 20px;">No</th>'
                                            +'<th class="text-center">Id Kecamatan</th>'
                                            +'<th class="text-center">Id Desa</th>'
                                            +'<th class="text-center">NO KK</th>'
                                            +'<th class="text-center">NIK</th>'
                                            +'<th class="text-center">Nama</th>'
                                            +'<th class="text-center">Provinsi</th>'
                                            +'<th class="text-center">Kabupaten / Kota</th>'
                                            +'<th class="text-center">kecamatan</th>'
                                            +'<th class="text-center">Desa</th>'
                                            +'<th class="text-center">Desa Kelurahan</th>'
                                            +'<th class="text-center">Alamat</th>'
                                            +'<th class="text-center">BLT</th>'
                                            +'<th class="text-center">BLT BBM</th>'
                                            +'<th class="text-center">BNPT PPKM</th>'
                                            +'<th class="text-center">BPNT</th>'
                                            +'<th class="text-center">BST</th>'
                                            +'<th class="text-center">FIRST_SK</th>'
                                            +'<th class="text-center">PBI</th>'
                                            +'<th class="text-center">PKH</th>'
                                            +'<th class="text-center">RUTILAHU</th>'
                                            +'<th class="text-center">SEMBAKO ADAPTIF</th>'
                                            +'<th class="text-center">checkBtnHamil</th>'
                                            +'<th class="text-center">checkBtnVerifMeninggal</th>'
                                            +'<th class="text-center">counter</th>'
                                            +'<th class="text-center">Hapus Tabel</th>'
                                            +'<th class="text-center">idsemesta</th>'
                                            +'<th class="text-center">isAktifHamil</th>'
                                            +'<th class="text-center">is_btn_dapodik</th>'
                                            +'<th class="text-center">is_btn_hidupkan</th>'
                                            +'<th class="text-center">is_btn_padankan</th>'
                                            +'<th class="text-center">is_nonaktif</th>'
                                            +'<th class="text-center">Keterangan Disabilitas</th>'
                                            +'<th class="text-center">Keterangan Meninggal</th>'
                                            +'<th class="text-center">Masih Hidup</th>'
                                            +'<th class="text-center">padankan_at</th>'
                                            +'<th class="text-center">Periode BLT</th>'
                                            +'<th class="text-center">Periode BLT BBM</th>'
                                            +'<th class="text-center">Periode BPNT</th>'
                                            +'<th class="text-center">Periode BPNT PPKM</th>'
                                            +'<th class="text-center">Periode BST</th>'
                                            +'<th class="text-center">Periode PBI</th>'
                                            +'<th class="text-center">Periode PKH</th>'
                                            +'<th class="text-center">Periode Rutilahu</th>'
                                            +'<th class="text-center">Periode Sembako Adaptif</th>'
                                            +'<th class="text-center">verifyid</th>'
                                            +'<th class="text-center">active</th>'
                                            +'<th class="text-center">update_at</th>'
                                        +'</tr>'
                                    +'</thead>'
                                    +'<tbody>'
                                        +data_dtks
                                    +'</tbody>'
                                +'</table>'
                            +'</div>';
                        jQuery('#pesan4').html(pesan4);
                    }else{
                        jQuery('#pesan4').html('');
                    }
                }
                if(
                    response.data.p3ke.length == 0
                    && response.data.rtlh.length == 0
                    && response.data.stunting.length == 0
                    && response.data.tbc.length == 0
                    && response.data.dtks.length == 0
                ){
                    alert('Data tidak ditemukan!');
                }else{
                    jQuery('.isi-pesan .wrap-table .table').dataTable();
                }
                jQuery('#wrap-loading').hide();
            }
        });
}
</script>