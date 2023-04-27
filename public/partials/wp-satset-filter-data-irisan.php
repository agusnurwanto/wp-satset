<?php
global $wpdb;

$login = false;
if(is_user_logged_in()){
    $current_user = wp_get_current_user();
    if($this->functions->user_has_role($current_user->ID, 'administrator')){
        $login = true;
    }
}

if(true == $login){
    $data_irisan_admin = $this->functions->generatePage(array(
        'nama_page' => 'Data Disabilitas Admin', 
        'content' => '[data_irisan_admin]',
        'show_header' => 1,
        'no_key' => 1,
    ));
    $link_data_admin = $data_irisan_admin['url'];
}else{
    $link_data_admin = '';
}

function link_detail($link_admin, $jenis){
    return "<a target='_blank' href='".$link_admin."?".$jenis['key']."=".$jenis['value']."'>".$jenis['label']."</a>";
}

function generateRandomColor($k){
    $color = array('#f44336', '#9c27b0', '#2196f3', '#009688', '#4caf50', '#cddc39', '#ff9800', '#795548', '#9e9e9e', '#607d8b');
    return $color[$k%10];
}
$data = $wpdb->get_results("select * from data_irisan", ARRAY_A);
$total_data = count($data);
$p3ke = array() ;
$stunting = array() ;
$rtlh = array() ;
$tbc = array() ;
$dtks = array() ;
$batas_kecamatan = array() ;
$batas_desa = array();
foreach($data as $k => $v){
    
    if(empty($batas_desa[$v["desa"]])){
        $batas_desa[$v["desa"]] = array();
    }
    $batas_desa[$v["desa"]][] = $v;
    

}
ksort($batas_desa);


// Data Batas Desa
$chart_desa = array(
    'label' => array(),
    'data'  => array(),
    'color' => array()
);
$total_desa = 0;
$body_desa = "";
$no = 0;
foreach($batas_desa as $k => $v){
    if($k == 'Tidak diketahui'){
        $jenis = $k;
    }
    $no++;
    $jumlah = count($v);
    $total_desa += $jumlah;
    $nama_desa = $k;
    if(empty($nama_desa)){
        $nama_desa = 'Tidak diketahui';
    }
    $chart_desa['label'][] = $nama_desa;
    $chart_desa['data'][] = $jumlah;
    $chart_desa['color'][] = generateRandomColor($no);
    if(true == $login){
        $nama_desa = link_detail($link_data_admin, array('key' => 'desa', 'value' => $nama_desa, 'label' => $nama_desa ));
    }
    $body_desa .= "
        <tr>
            <td style='text-transform: uppercase;'>$nama_desa</td>
            <td class='text-right'>$jumlah</td>
        </tr>
    ";
}
?>
<style type="text/css">
    .card {
        margin-top: 20px;
    }
</style>
<div class="cetak">
    <div style="padding: 10px;">
        <div class="row">
            <div class="col-md-12">
                <h1 class="text-center">Data Batas Desa</h1>
                <h2 class="text-center"><?php echo get_option("_crb_prov_satset"); ?><br><?php echo get_option("_crb_kab_satset"); ?></h2>
    </div>
        </div><!-- 
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <h2 class="text-center text-white">Data Batas Desa</h2>
                    </div> -->
                    <div class="card-body">
                        <div class="container counting-inner">
                            <div class="row counting-box title-row" style="margin-bottom: 1px;">
                                <div class="col-md-12 text-center animated" data-animation="fadeInBottom"
                                    data-animation-delay="200">
                                    <div style="width: 10%; margin:auto;">
                                        <canvas id="chart_per_desa"></canvas>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="text-center">Desa</th>
                                    <th class="text-center" style="width: 10px;">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php echo $body_desa; ?>
                            </tbody>
                            <tfoot>
                                <th class="text-center">Total</th>
                                <th class="text-right"><?php echo $total_desa; ?></th>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</script>