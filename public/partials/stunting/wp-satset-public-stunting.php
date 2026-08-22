<?php
global $wpdb;

if (!defined('WPINC')) {
    die;
}
if (!empty($_GET) && !empty($_GET['tahun_anggaran'])) {
    $tahun_anggaran = $_GET['tahun_anggaran'];
} else {
    $tahun_anggaran = get_option('_crb_tahun_satset');
}
$center = $this->get_center();
$maps_all = $this->get_polygon();
$stunting_all = $this->get_stunting();

$stunting_all_desa = array();
foreach ($stunting_all as $data) {
    $index = $data['provinsi'] . '.' . $data['kabkot'] . '.' . $data['kecamatan'] . '.' . $data['desa'];
    if (empty($stunting_all_desa[$index])) {
        $stunting_all_desa[$index] = array();
    }
    if (empty($stunting_all_desa[$index][$data['posyandu']])) {
        $stunting_all_desa[$index][$data['posyandu']] = array();
    }
    $stunting_all_desa[$index][$data['posyandu']][] = $data;
}
// print_r($stunting_all_desa); die();

$total_all = 0;
$body =  '';
foreach($maps_all as $i => $desa){
    $maps_all[$i]['index'] = $i;
    $index = $desa['data']['provinsi'] . '.' . $desa['data']['kab_kot'] . '.' . $desa['data']['kecamatan'] . '.' . $desa['data']['desa'];
    $total_stunting = 0;
    $total_bb_per_u = array();
    $total_tb_per_u = array();
    $total_bb_per_tb = array();
    $total_naik_bb = array();
    $total_posyandu = array();
    if (!empty($stunting_all_desa[$index])) {
        foreach ($stunting_all_desa[$index] as $key => $posyandu) {
            $jml_per_posyandu = count($posyandu);
            $total_stunting += $jml_per_posyandu;
            $total_posyandu[] = $key . ': ' . $jml_per_posyandu;
            foreach ($posyandu as $anak) {
                if (empty($total_bb_per_u[$anak['bb_per_u']])) {
                    $total_bb_per_u[$anak['bb_per_u']] = 0;
                }
                $total_bb_per_u[$anak['bb_per_u']]++;
                if (empty($total_tb_per_u[$anak['tb_per_u']])) {
                    $total_tb_per_u[$anak['tb_per_u']] = 0;
                }
                $total_tb_per_u[$anak['tb_per_u']]++;
                if (empty($total_bb_per_tb[$anak['bb_per_tb']])) {
                    $total_bb_per_tb[$anak['bb_per_tb']] = 0;
                }
                $total_bb_per_tb[$anak['bb_per_tb']]++;
                if (empty($total_naik_bb[$anak['naik_berat_badan']])) {
                    $total_naik_bb[$anak['naik_berat_badan']] = 0;
                }
                $total_naik_bb[$anak['naik_berat_badan']]++;
            }
        }
        foreach ($total_bb_per_u as $key => $data) {
            $total_bb_per_u[$key] = $key . ': ' . $data;
        }
        foreach ($total_tb_per_u as $key => $data) {
            $total_tb_per_u[$key] = $key . ': ' . $data;
        }
        foreach ($total_bb_per_tb as $key => $data) {
            $total_bb_per_tb[$key] = $key . ': ' . $data;
        }
        foreach ($total_naik_bb as $key => $data) {
            $total_naik_bb[$key] = $key . ': ' . $data;
        }
    }
    if ($total_stunting <= 5) {
        $maps_all[$i]['color'] = '#0cbf00';
    } else if ($total_stunting <= 20) {
        $maps_all[$i]['color'] = '#fff70a';
    } else if ($total_stunting > 20) {
        $maps_all[$i]['color'] = '#ff0000';
    }
    $search = $this->getSearchLocation($desa['data']);
    $body .= "
        <tr>
            <td class='text-center'>" . $desa['data']['id2012'] . "</td>
            <td class='text-center'>" . $desa['data']['provinsi'] . "</td>
            <td class='text-center'>" . $desa['data']['kab_kot'] . "</td>
            <td class='text-center'>" . $desa['data']['kecamatan'] . "</td>
            <td class='text-center'>" . $desa['data']['desa'] . "</td>
            <td class='text-center'>" . $total_stunting . "</td>
            <td>" . implode('<br>', $total_posyandu) . "</td>
            <td>" . implode('<br>', $total_bb_per_u) . "</td>
            <td>" . implode('<br>', $total_tb_per_u) . "</td>
            <td>" . implode('<br>', $total_bb_per_tb) . "</td>
            <td>" . implode('<br>', $total_naik_bb) . "</td>
            <td class='text-center'><a style='margin-bottom: 5px;' onclick='cari_alamat(\"" . $search . "\"); return false;' href='#' class='btn btn-danger'>Map</a></td>
        </tr>
    ";
    $total_all += $total_stunting;
}

$tahun = $wpdb->get_results('
    SELECT 
        tahun_anggaran 
    from satset_data_unit
    group by tahun_anggaran 
    order by tahun_anggaran ASC
', ARRAY_A);
$select_tahun = "";
foreach($tahun as $tahun_value){
    $select = $tahun_value['tahun_anggaran'] == $tahun_anggaran ? 'selected' : '';
    $select_tahun .= "<option value='".$tahun_value['tahun_anggaran']."' ".$select.">".$tahun_value['tahun_anggaran']."</option>";
}
?>
<h1 class="text-center">Peta Sebaran Stunting<br><?php echo $this->getNamaDaerah(); ?></h1>
<div id="wrap-action"></div>
    <div class="text-center" style="margin-top: 30px;">
        <label style="margin-left: 10px;" for="tahun_anggaran">Tahun Anggaran : </label>
        <select style="width: 400px;" name="tahun_anggaran" id="tahun_anggaran">
            <?php echo $select_tahun; ?>
        </select>
        <button style="margin-left: 10px; height: 45px; width: 75px;"onclick="sumbitTahun();" class="btn btn-sm btn-primary">Cari</button>
    </div>
</div>
<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px; padding-top: 50px;">
    <div id="map-canvas" style="width: 100%; height: 400px;"></div>
    <h3 style="margin-top: 20px;">Keterangan</h3>
    <ol>
        <li>Warna hijau berarti jumlah stunting antara 0 sampai 5 anak</li>
        <li>Warna kuning berarti jumlah stunting antara 6 sampai 20 anak</li>
        <li>Warna merah berarti jumlah stunting diatas 20 anak</li>
    </ol>
    <h2 class="text-center">Tabel Data Stunting<br>Total <?php echo $this->number_format($total_all); ?> Anak</h1>
        <div style="width: 100%; overflow: auto; height: 100vh;">
            <table class="table table-bordered" id="table-data">
                <thead>
                    <tr>
                        <th class='text-center'>Kode Desa</th>
                        <th class='text-center'>Provinsi</th>
                        <th class='text-center'>Kabupaten/Kota</th>
                        <th class='text-center'>Kecamatan</th>
                        <th class='text-center'>Desa</th>
                        <th class='text-center'>Total Stunting</th>
                        <th class='text-center'>Posyandu</th>
                        <th class='text-center'>Berat Badan / Usia</th>
                        <th class='text-center'>Tinggi Badan / Usia</th>
                        <th class='text-center'>Berat Badan / Tinggi Badan</th>
                        <th class='text-center'>Naik Berat Badan</th>
                        <th class='text-center'>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php echo $body; ?>
                </tbody>
            </table>
        </div>
</div>
<script type="text/javascript">
    window.maps_all = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;
    jQuery('#table-data').dataTable({
        lengthMenu: [
            [20, 50, 100, -1],
            [20, 50, 100, "All"]
        ],
        order: [
            [5, 'desc']
        ]
    });
    function sumbitTahun(){
        var tahun_anggaran = jQuery('#tahun_anggaran').val();
        if(tahun_anggaran == ''){
            return alert('Tahun tidak boleh kosong!');
        }
        var url = window.location.href;
        url = url.split('?')[0]+'?tahun_anggaran='+tahun_anggaran;
        location.href = url;
    }
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>