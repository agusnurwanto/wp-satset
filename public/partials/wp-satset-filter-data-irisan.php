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

$data = $wpdb->get_results("select * from data_batas_kecamatan order by id2012 ASC", ARRAY_A);
$kec = "<option value='-1'>Semua Kecamatan</option>";
foreach($data as $val){
    $kec .= "<option value='$val[id]'>($val[id2012]) $val[kecamatan]</option>";
}
$data = $wpdb->get_results("select * from data_batas_desa order by id2012 ASC", ARRAY_A);
$desa = "<option value='-1'>Semua Desa</option>";
foreach($data as $val){
    $desa .= "<option value='$val[id]'>($val[id2012]) $val[desa]</option>";
}
?>
<style type="text/css">
    .card {
        margin-top: 20px;
    }
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
        <select class="form-control">
            <?php echo $kec; ?>
        </select>
    </div>
    <div class="form-group">
        <label>Pilih Desa</label>
        <select class="form-control">
            <?php echo $desa; ?>
        </select>
    </div>
    <div class="row">
        <legend class="col-form-label col-sm-3 pt-0">Data Irisan :</legend>
        <div class="col-sm-4">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="" value="p3ke" checked>P3KE</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="" value="stunting" checked>Stunting</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="" value="tbc" checked>TBC</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="" value="rtlh" checked>RTLH</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="checkbox" name="" id="" value="dtks" checked>DTKS</label>
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
</div>
<script>
    jQuery(document).ready(function(){
        jQuery("#cari").click(function(){
            cari_data_satset(jQuery('#desa').val());
        });
        jQuery("#desa").keyup(function(event) {
            event.preventDefault();
            if (event.keyCode === 13) {
                jQuery("#cari").click();
            }
        });
    })

function cari_data_satset(desa) {
        if(''){
            return alert("Nama Desa Tidak Ditemukan!");
        }
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data:{
                'action': 'cari_data_satset',
                'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                'desa': desa
            },
            success: function(response) {
                if(response.status == 'error'){
                    alert(response.message);
                }else{
                    let html = '';
                    if(''){
                        response.data.desa.map(function(value, index){
                            html +='<tr>';
                                html +='<th scope="row">'+(index+1)+'</th>';
                                html +='<td>'+value.id_desa+'</td>';
                                html +='<td>'+value.desa+'</td>';
                                html +='<td>'+value.kecamatan+'</td>';
                                html +='<td>'+value.kab_kot+'</td>';
                                html +='<td>'+value.provinsi+'</td>';
                                html +='<td>'+value.area+'</td>';
                                html +='<td>'+value.perimeter+'</td>';
                                html +='<td>'+value.hectares+'</td>';
                                html +='<td>'+value.ukuran_kot+'</td>';
                                html +='<td>'+value.pemusatan+'</td>';
                                html +='<td>'+value.jumplah_pen+'</td>';
                                html +='<td>'+value.provno+'</td>';
                                html +='<td>'+value.kabkotno+'</td>';
                                html +='<td>'+value.kecno+'</td>';
                                html +='<td>'+value.desano+'</td>';
                                html +='<td>'+value.id2012+'</td>';
                        })
                        var pesan = ''
                            +'<h3 class="text-center">Data Desa</h3>'
                            +'<div class="wrap-table">'
                                +'<table class="table table-bordered">'
                                    +'<thead>'
                                        +'<tr>'
                                            +'<th class="text-center" style="width: 20px;">No</th>'
                                            +'<th class="text-center">Id Desa</th>'
                                            +'<th class="text-center">Desa</th>'
                                            +'<th class="text-center">Kecamatan</th>'
                                            +'<th class="text-center">Kabupaten / Kota</th>'
                                            +'<th class="text-center">Provinsi</th>'
                                            +'<th class="text-center"Aarea</th>'
                                            +'<th class="text-center">Perimeter</th>'
                                            +'<th class="text-center">Hectares</th>'
                                            +'<th class="text-center">Ukuran Kota</th>'
                                            +'<th class="text-center">Pemusatan</th>'
                                            +'<th class="text-center">Jumplah Penduduk</th>'
                                            +'<th class="text-center">Nomor Provinsi</th>'
                                            +'<th class="text-center">Nomor Kabupaten / Kota</th>'
                                            +'<th class="text-center">Nomor Kecamatan</th>'
                                            +'<th class="text-center">No Desa</th>'
                                            +'<th class="text-center">Id 2012/th>'
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
                     }
                if(''){
                alert('Data tidak ditemukan!');
            }else{
                jQuery('.isi-pesan .wrap-table .table').dataTable();
            }
            jQuery('#wrap-loading').hide();
        }
    });
}
</script>