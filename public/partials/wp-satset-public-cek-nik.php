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
                    if(response.data.dtks.length > 0){
                        html +='<h4 class="text-center">Data P3KE</h4>';
                        response.data.map(function(value, index){
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

