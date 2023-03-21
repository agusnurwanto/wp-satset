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
<div style="padding: 10px; margin: auto; overflow: auto;" id="pesan1">
</div>
<div style="padding: 10px; margin: auto; overflow: auto;" id="pesan2">
</div>
<div style="padding: 10px; margin: auto; overflow: auto;" id="pesan3">
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
                    if(response.data.p3ke.length > 0){
                        html +='<h4 class="text-center">Data P3KE</h4>';
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
                    }
                    let data_all = ''
                    if(response.data.stunting.length > 0){
                        data_all +='<h4 class="text-center">Data Stunting</h4>';
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
                                data_all +='<td>'+value.rt+'</td>';
                                data_all +='<td>'+value.rw+'</td>';
                                data_all +='<td>'+value.alamat+'</td>';
                                data_all +='<td>'+value.usia_saat_ukur+'</td>';
                                data_all +='<td>'+value.tanggal_pengukuran+'</td>';
                                data_all +='<td>'+value.berat+'</td>';
                                data_all +='<td>'+value.tinggi+'</td>';
                                data_all +='<td>'+value.lingkar_lengan_atas+'</td>';
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
                            +'</table>';
                        jQuery('#pesan1').html(pesan1);
                    }
                    let data_tbc = '';
                    if(response.data.tbc.length > 0){
                        data_tbc +='<h4 class="text-center">Data TBC</h4>';
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
                            +'</table>';
                            if (pindahan_dari_fasyankes = null) {
                                    console.log("0")};
                        jQuery('#pesan2').html(pesan2);
                    }
                    let data_rtlh = '';
                    if(response.data.rtlh.length > 0){
                        data_rtlh +='<h4 class="text-center">Data RTLH</h4>';
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
                            +'</table>';
                        jQuery('#pesan3').html(pesan3);
                    }
                }
                if(
                    response.data.p3ke.length == 0
                    && response.data.rtlh.length == 0
                    && response.data.stunting.length == 0
                    && response.data.tbc.length == 0
                ){
                    alert('Data tidak ditemukan!');
                }
                jQuery('#wrap-loading').hide();
            }
        });
    }

</script>

