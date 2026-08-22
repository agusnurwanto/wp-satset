<?php
if (!defined('WPINC')) {
    die;
}
    
global $wpdb;

$data_aids = $this->functions->generatePage(array(
    'nama_page' => 'Data Detail AIDS', 
    'content' => '[data_detail_aids]',
    'show_header' => 1,
    'post_status' => 'private'
));

if (!empty($_GET['tahun_anggaran'])) {
    $tahun_anggaran = $_GET['tahun_anggaran'];
} else {
    $tahun_anggaran = get_option('_crb_tahun_satset');
}

/* ---------------------------------------------------------------
 * Polygon peta & center
 * --------------------------------------------------------------- */
$center   = $this->get_center();
$maps_all = $this->get_polygon();

/* ---------------------------------------------------------------
 * Ambil semua data AIDS aktif untuk tahun anggaran yang dipilih
 * --------------------------------------------------------------- */
$aids_all = $wpdb->get_results($wpdb->prepare("
    SELECT
        a.provinsi_domisili,
        a.kabkot_domisili,
        a.kecamatan_domisili,
        a.desa_domisili,
        a.jenis_kelamin,
        a.kelompok_umur_terdiagnosis,
        a.nama_upk,
        a.kode_upk,
        m.kode_desa_satset
    FROM data_aids a
    LEFT JOIN mapping_aids m
        ON CONCAT_WS('-', a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili) = m.kode_desa_aids
    WHERE a.active = 1
        AND a.tahun_anggaran = %d
    ORDER BY a.provinsi_domisili, a.kabkot_domisili, a.kecamatan_domisili, a.desa_domisili
", $tahun_anggaran), ARRAY_A);

/* ---------------------------------------------------------------
 * Kelompokkan per desa:
 *   - $per_desa_mapped   : key = kode_desa_satset (id2012 polygon)
 *   - $per_desa_unmapped : key = gabungan domisili (belum mapping)
 * --------------------------------------------------------------- */
$per_desa_mapped   = array();
$per_desa_unmapped = array();

foreach ($aids_all as $row) {
    if (!empty($row['kode_desa_satset'])) {
        $key = $row['kode_desa_satset'];
        if (empty($per_desa_mapped[$key])) {
            $per_desa_mapped[$key] = array(
                'provinsi'    => $row['provinsi_domisili'],
                'kabkot'      => $row['kabkot_domisili'],
                'kecamatan'   => $row['kecamatan_domisili'],
                'desa'        => $row['desa_domisili'],
                'kode_satset' => $row['kode_desa_satset'],
                'pasien'      => array(),
            );
        }
        $per_desa_mapped[$key]['pasien'][] = $row;
    } else {
        $key = $row['provinsi_domisili'] . '-' . $row['kabkot_domisili']
             . '-' . $row['kecamatan_domisili'] . '-' . $row['desa_domisili'];
        if (empty($per_desa_unmapped[$key])) {
            $per_desa_unmapped[$key] = array(
                'provinsi'    => $row['provinsi_domisili'],
                'kabkot'      => $row['kabkot_domisili'],
                'kecamatan'   => $row['kecamatan_domisili'],
                'desa'        => $row['desa_domisili'],
                'kode_satset' => null,
                'pasien'      => array(),
            );
        }
        $per_desa_unmapped[$key]['pasien'][] = $row;
    }
}

/* ---------------------------------------------------------------
 * Helper functions
 * --------------------------------------------------------------- */
function aids_rekap_kelompok_umur(array $pasien) {
    $groups = array();
    foreach ($pasien as $p) {
        $kel   = trim((string) $p['kelompok_umur_terdiagnosis']);
        $label = ($kel === '') ? '(Tidak Tercatat)' : $kel;
        $groups[$label] = ($groups[$label] ?? 0) + 1;
    }
    ksort($groups);
    $hasil = array();
    foreach ($groups as $label => $jumlah) {
        $hasil[] = esc_html($label) . ': <strong>' . $jumlah . '</strong>';
    }
    return $hasil;
}

function aids_rekap_jenis_kelamin(array $pasien) {
    $lk = $pr = $lain = 0;
    foreach ($pasien as $p) {
        $jk = strtoupper(trim((string) $p['jenis_kelamin']));
        if (in_array($jk, array('L', 'LAKI-LAKI', 'LAKI', 'M', 'MALE')))       $lk++;
        elseif (in_array($jk, array('P', 'PEREMPUAN', 'F', 'FEMALE')))          $pr++;
        else                                                                      $lain++;
    }
    $hasil = array();
    if ($lk)   $hasil[] = 'Laki-laki: <strong>' . $lk . '</strong>';
    if ($pr)   $hasil[] = 'Perempuan: <strong>' . $pr . '</strong>';
    if ($lain) $hasil[] = 'Lainnya: <strong>' . $lain . '</strong>';
    return $hasil;
}

function aids_rekap_upk(array $pasien) {
    $groups = array();
    foreach ($pasien as $p) {
        $nama  = trim((string) $p['nama_upk']);
        $label = ($nama === '') ? '(Tidak Tercatat)' : $nama;
        $groups[$label] = ($groups[$label] ?? 0) + 1;
    }
    ksort($groups);
    $hasil = array();
    foreach ($groups as $label => $jumlah) {
        $hasil[] = esc_html($label) . ': <strong>' . $jumlah . '</strong>';
    }
    return $hasil;
}

/* ---------------------------------------------------------------
 * Warna chart jenis kelamin
 * --------------------------------------------------------------- */
$warna_jk = array(
    'Laki-laki' => '#00B0FF',
    'Perempuan' => '#FF4081',
    'Lainnya'   => '#9E9E9E',
);

/* ---------------------------------------------------------------
 * Build peta polygon + popup + baris tabel (desa termapping)
 * --------------------------------------------------------------- */
$body_mapped   = '';
$body_unmapped = '';
$total_all     = 0;

foreach ($maps_all as $i => $desa) {
    $id2012     = $desa['data']['id2012'];
    $total_pasien = 0;

    $chart_aids = array('label' => array(), 'data' => array(), 'color' => array(), 'type' => 'doughnut');

    if (!empty($per_desa_mapped[$id2012])) {
        $d            = $per_desa_mapped[$id2012];
        $pasien       = $d['pasien'];
        $total_pasien = count($pasien);

        // Chart breakdown jenis kelamin
        $lk = $pr = $lain = 0;
        foreach ($pasien as $p) {
            $jk = strtoupper(trim((string) $p['jenis_kelamin']));
            if (in_array($jk, array('L', 'LAKI-LAKI', 'LAKI', 'M', 'MALE')))      $lk++;
            elseif (in_array($jk, array('P', 'PEREMPUAN', 'F', 'FEMALE')))         $pr++;
            else                                                                     $lain++;
        }
        $jk_data = array(
            'Laki-laki' => $lk,
            'Perempuan' => $pr,
        );
        if ($lain) $jk_data['Lainnya'] = $lain;

        foreach ($jk_data as $label => $jml) {
            if ($jml <= 0) continue;
            $persen = round(($jml / $total_pasien) * 100, 1);
            $chart_aids['label'][] = str_pad($label, 12) . str_pad($jml . ' org', 10) . $persen . '%';
            $chart_aids['data'][]  = $jml;
            $chart_aids['color'][] = $warna_jk[$label] ?? '#9E9E9E';
        }

        $rekap_umur = aids_rekap_kelompok_umur($pasien);
        $rekap_jk   = aids_rekap_jenis_kelamin($pasien);
        $rekap_upk  = aids_rekap_upk($pasien);

        // Hapus dari unmapped agar tidak double
        unset($per_desa_unmapped[$id2012]);
    } else {
        $rekap_umur = array();
        $rekap_jk   = array();
        $rekap_upk  = array();
    }

    // Warna polygon
    if ($total_pasien === 0) {
        $maps_all[$i]['color'] = '#0cbf00';
    } elseif ($total_pasien <= 10) {
        $maps_all[$i]['color'] = '#fff70a';
    } else {
        $maps_all[$i]['color'] = '#ff0000';
    }

    $maps_all[$i]['index']         = $i;
    $maps_all[$i]['chart']         = $chart_aids;
    $maps_all[$i]['chart']['type'] = 'doughnut';
    $maps_all[$i]['html']          = '
        <div class="container counting-inner">
            <div class="row counting-box title-row">
                <div class="col-md-12 text-center animated">
                    <div style="max-width:500px; margin:auto;">
                        <h3>' . esc_html($desa['data']['desa']) . '</h3>
                        <p>
                            Kec. ' . esc_html($desa['data']['kecamatan']) . '<br>
                            Kab. ' . esc_html($desa['data']['kab_kot'])   . '<br>
                            Prov. ' . esc_html($desa['data']['provinsi'])  . '
                        </p>
                        <p><b>Total Pasien AIDS : ' . $this->number_format($total_pasien) . ' Orang</b></p>
                        ' . (
                            $total_pasien > 0
                            ? '<canvas id="chart-' . $i . '" height="300"></canvas>'
                            : '<div class="alert alert-success">Tidak ada data AIDS di desa ini.</div>'
                        ) . '
                    </div>
                </div>
            </div>
        </div>
    ';

    $search    = $this->getSearchLocation($desa['data']);
    $nama_desa = esc_html($desa['data']['desa']);
    $is_admin = false;
    
    if ( is_user_logged_in() ) {
        $user_id = um_user('ID');
        $user_meta = get_userdata($user_id);
        if (in_array("administrator", $user_meta->roles)) {
            $is_admin = true;
        }
        if (in_array("administrator", $user_meta->roles)){
            $detail_url = add_query_arg(
                'id_wilayah',
                $id2012,
                $data_aids['url']);
            $nama_desa = "<a href='" . $detail_url . "' target='_blank' rel='noopener noreferrer'>" . $desa['data']['desa'] . "</a>";
        }
    }
    $total_all += $total_pasien;

    $body_mapped .= "
        <tr>
            <td class='text-center'>" . esc_html($id2012) . "</td>
            <td class='text-center'>" . esc_html($desa['data']['provinsi'])  . "</td>
            <td class='text-center'>" . esc_html($desa['data']['kab_kot'])   . "</td>
            <td class='text-center'>" . esc_html($desa['data']['kecamatan']) . "</td>
            <td class='text-center'>{$nama_desa}</td>
            <td class='text-center'><strong>{$total_pasien}</strong></td>
            <td>" . implode('<br>', $rekap_umur) . "</td>
            <td>" . implode('<br>', $rekap_jk)   . "</td>
            <td>" . implode('<br>', $rekap_upk)  . "</td>
            <td class='text-center'>
                <a onclick='cari_alamat(\"" . esc_js($search) . "\"); return false;'
                   href='#' class='btn btn-danger btn-sm'>Map</a>
            </td>
        </tr>
    ";
}

/* ---------------------------------------------------------------
 * Baris tabel desa BELUM termapping
 * --------------------------------------------------------------- */
$total_unmapped = 0;
foreach ($per_desa_unmapped as $key => $d) {
    $pasien       = $d['pasien'];
    $total_pasien = count($pasien);
    $total_all   += $total_pasien;
    $total_unmapped += $total_pasien;

    $rekap_umur = aids_rekap_kelompok_umur($pasien);
    $rekap_jk   = aids_rekap_jenis_kelamin($pasien);
    $rekap_upk  = aids_rekap_upk($pasien);

    $search = trim($d['kecamatan'] . ' ' . $d['kabkot'] . ' ' . $d['provinsi']);

    $body_unmapped .= "
        <tr class='table-warning'>
            <td class='text-center'><span style='color:#dc3545;font-size:11px;'>Belum Termapping</span></td>
            <td class='text-center'>" . esc_html($d['provinsi'])  . "</td>
            <td class='text-center'>" . esc_html($d['kabkot'])    . "</td>
            <td class='text-center'>" . esc_html($d['kecamatan']) . "</td>
            <td class='text-center'>" . esc_html($d['desa'])      . "</td>
            <td class='text-center'><strong>{$total_pasien}</strong></td>
            <td>" . implode('<br>', $rekap_umur) . "</td>
            <td>" . implode('<br>', $rekap_jk)   . "</td>
            <td>" . implode('<br>', $rekap_upk)  . "</td>
            <td class='text-center'>
                <a onclick='cari_alamat(\"" . esc_js($search) . "\"); return false;'
                   href='#' class='btn btn-secondary btn-sm'>Map</a>
            </td>
        </tr>
    ";
}

/* ---------------------------------------------------------------
 * Dropdown tahun anggaran
 * --------------------------------------------------------------- */
$tahun_list = $wpdb->get_results('
    SELECT tahun_anggaran FROM satset_data_unit
    GROUP BY tahun_anggaran ORDER BY tahun_anggaran ASC
', ARRAY_A);

$select_tahun = '';
foreach ($tahun_list as $t) {
    $sel           = ($t['tahun_anggaran'] == $tahun_anggaran) ? 'selected' : '';
    $select_tahun .= "<option value='{$t['tahun_anggaran']}' {$sel}>{$t['tahun_anggaran']}</option>";
}
?>
<style type="text/css">
    #tbl-rekap-aids thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background-color: #343a40;
        color: #fff;
        white-space: nowrap;
    }
    #tbl-rekap-aids td { vertical-align: middle; }
    .wrap-rekap-aids   { overflow: auto; max-height: 80vh; width: 100%; }
    #tbl-rekap-aids .btn { padding: 3px 8px; font-size: 12px; }
</style>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@2"></script>
<script>Chart.register(ChartDataLabels);</script>

<h1 class="text-center" style="margin: 2rem 0 0.5rem;">
    Peta Sebaran AIDS/HIV<br>
    <small style="font-size:0.6em;"><?php echo $this->getNamaDaerah(); ?></small>
</h1>
<div id="wrap-action"></div>

<div class="text-center" style="margin: 20px 0;">
    <label for="tahun_anggaran">Tahun Anggaran : </label>
    <select style="width: 200px;" name="tahun_anggaran" id="tahun_anggaran">
        <?php echo $select_tahun; ?>
    </select>
    <button style="margin-left:10px; height:38px; width:75px;"
            onclick="submitTahunAids();"
            class="btn btn-sm btn-primary">Cari</button>
</div>

<div style="width: 95%; margin: 0 auto; min-height: 90vh; padding-bottom: 75px; padding-top: 20px;">

    <button id="btn-label-map" style="margin: 10px 0;"
            class="btn btn-sm btn-primary"
            onclick="toggleLabelMap();">Hidupkan Label</button>

    <div id="map-canvas" style="width: 100%; height: 420px;"></div>

    <h3 style="margin-top: 20px;">Keterangan Warna Peta</h3>
    <ol>
        <li>Warna <b style="color:#0cbf00;">hijau</b> — tidak ada data AIDS di desa tersebut.</li>
        <li>Warna <b style="color:#b8a800;">kuning</b> — jumlah pasien AIDS 1 sampai 10 orang.</li>
        <li>Warna <b style="color:#ff0000;">merah</b> — jumlah pasien AIDS lebih dari 10 orang.</li>
    </ol>

    <h2 class="text-center" style="margin-top: 30px;">
        Tabel Rekap Data AIDS/HIV<br>
        Total Pasien: <strong><?php echo $this->number_format($total_all); ?></strong> Orang
    </h2>

    <div class="wrap-rekap-aids">
        <table class="table table-bordered table-sm" id="tbl-rekap-aids">
            <thead>
                <tr>
                    <th class="text-center">Kode Desa</th>
                    <th class="text-center">Provinsi</th>
                    <th class="text-center">Kab/Kota</th>
                    <th class="text-center">Kecamatan</th>
                    <th class="text-center">Desa Domisili</th>
                    <th class="text-center">Total Pasien</th>
                    <th class="text-center">Kelompok Umur Terdiagnosis</th>
                    <th class="text-center">Jenis Kelamin</th>
                    <th class="text-center">Nama UPK</th>
                    <th class="text-center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($body_mapped || $body_unmapped):
                    echo $body_mapped;
                    echo $body_unmapped;
                else:
                ?>
                    <tr>
                        <td colspan="11" class="text-center" style="color:#999; padding:30px;">
                            Tidak ada data AIDS/HIV untuk tahun anggaran <?php echo esc_html($tahun_anggaran); ?>.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total_unmapped > 0): ?>
    <div style="margin-top:10px; padding:10px 15px; background:#fff3cd; border-left:4px solid #ffc107; border-radius:3px;">
        <strong>Catatan:</strong> Baris berwarna kuning menandakan desa domisili yang <strong>belum termapping</strong>
        ke kode desa SATSET. Silakan lakukan mapping di halaman <em>Mapping Desa AIDS</em> agar data dapat ditampilkan di peta.
    </div>
    <?php endif; ?>
</div>

<script type="text/javascript">
    window.maps_all    = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;

    jQuery(document).ready(function () {
        jQuery('#tbl-rekap-aids').DataTable({
            lengthMenu: [[20, 50, 100, -1], [20, 50, 100, 'All']],
            order: [[5, 'desc']],
            columnDefs: [{ orderable: false, targets: [6, 7, 8] }]
        });
    });

    window.labelMapAktif = false;

    function toggleLabelMap() {
        if (window.labelMapAktif) {
            map.setOptions({ styles: [{
                featureType: 'all', elementType: 'labels',
                stylers: [{ visibility: 'off' }]
            }]});
            jQuery('#btn-label-map').text('Hidupkan Label');
            window.labelMapAktif = false;
        } else {
            map.setOptions({ styles: [] });
            jQuery('#btn-label-map').text('Matikan Label');
            window.labelMapAktif = true;
        }
    }

    function submitTahunAids() {
        var tahun = jQuery('#tahun_anggaran').val();
        if (!tahun) { return alert('Tahun tidak boleh kosong!'); }
        location.href = window.location.href.split('?')[0] + '?tahun_anggaran=' + tahun;
    }
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>
