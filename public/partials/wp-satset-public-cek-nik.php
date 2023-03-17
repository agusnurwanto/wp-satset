<h1 class="text-center">Cek NIK (Nomor Induk Kependudukan)</h1><br>
<h2 class="text-center"><?php echo get_option("_crb_prov_satset"); ?><br><?php echo get_option("_crb_kab_satset"); ?></h2>
<form id="formid" style="width: 500px; margin: auto;" class="text-center">
    <div class="form-group">
        <label for="nik">Masukan NIK / Nama</label>
        <div class="input-group">
            <input type="text" class="form-control" id="nik" placeholder="xxxxxxxxxxx">
            <div class="input-group-append">
                <span class="btn btn-primary" type="button" id="cari" style="display: flex; align-items: center;">Cari Data</span>
            </div>
        </div>
    </div>
</form>

<div style="padding: 10px; margin: auto; overflow: auto;" id="pesan">

</div>
<script>
    jQuery(document).ready(function(){
        jQuery("#cari").click(function(){
            cari_data_satset(jQuery('#nik').val());
        });
    })

    function cari_data_satset(nik) {
        if(nik.length < 3){
            return alert("Minimal 3 karakter!");
        }
        jQuery('#wrap-loading').show();
        jQuery.ajax({
            method: 'post',
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            dataType: 'json',
            data:{
                'action': 'cari_data_satset',
                'api_key': '<?php echo get_option( SATSET_APIKEY ); ?>',
                'nik': nik
            },
            success: function(response) {
                if(response.status == 'error'){
                    alert(response.message);
                }else{
                    let html = '';
                    if(response.data.length > 0){
                        response.data.map(function(value, index){
                            html +='<tr>';
                                html +='<th scope="row">'+(index+1)+'</th>';
                                html +='<td>'+value.nik+'</td>';
                                html +='<td>'+value.kepala_keluarga+'</td>';
                                html +='<td>'+value.provinsi+'</td>';
                                html +='<td>'+value.kabkot+'</td>';
                                html +='<td>'+value.kecamatan+'</td>';
                                html +='<td>'+value.desa+'</td>';
                                html +='<td>'+value.alamat+'</td>';
                            html +='</tr>';
                        })
                        var pesan = ''
                            +'<table class="table table-bordered">'
                                +'<thead>'
                                    +'<tr>'
                                        +'<th class="text-center" style="width: 20px;">No</th>'
                                        +'<th class="text-center">NIK</th>'
                                        +'<th class="text-center">Kepala Keluarga</th>'
                                        +'<th class="text-center">Provinsi</th>'
                                        +'<th class="text-center">Kabupaten / Kota</th>'
                                        +'<th class="text-center">Kecamatan</th>'
                                        +'<th class="text-center">Desa</th>'
                                        +'<th class="text-center">Alamat</th>'
                                    +'</tr>'
                                +'</thead>'
                                +'<tbody>'
                                    +html
                                +'</tbody>'
                            +'</table>';
                        jQuery('#pesan').html(pesan);
                    }else{
                        alert('Data tidak ditemukan!');
                    }
                }
                jQuery('#wrap-loading').hide();
            }
        });
    }

</script>

