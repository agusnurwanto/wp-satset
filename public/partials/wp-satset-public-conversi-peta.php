<?php
$opsi1 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/desa_all_magetan/administrasi_kab_magetan_.shp',
    'type' => 'desa',
    'kode_daerah' => false
);
$opsi2 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/desa_no/Magetan_Desa.shp',
    'type' => 'desa',
    'kode_daerah' => true
);
$opsi3 = array(
    'file' => SATSET_PLUGIN_PATH.'public/media/kecamatan_no/Magetan_Kec.shp',
    'type' => 'kecamatan'
);
// $maps_all = $this->read_shapefile($opsi1);
$center = $this->get_center();

/**
 * Import GeoJSON boundary data for desa.
 * Reads the file public/media/GeoJson/Magetan_Administrasi_Desa.geojson
 * and upserts each feature into the data_batas_desa table.
 */
function import_geojson_desa() {
    $geojson_path = SATSET_PLUGIN_PATH . 'public/media/GeoJson/Magetan_Administrasi_Desa.geojson';
    if (!file_exists($geojson_path)) {
        return;
    }
    $content = file_get_contents($geojson_path);
    $geo = json_decode($content, true);
    if (empty($geo['features']) || !is_array($geo['features'])) {
        return;
    }
    global $wpdb;
    $all_data = array();
    $default_color = get_option('_crb_warna_p3ke_satset');
    foreach ($geo['features'] as $feature) {
        $props = $feature['properties'];
        $geom = $feature['geometry'];
        // Build coordinate array (lat,lng) from geometry.
        $coords = array();
        if ($geom['type'] === 'Polygon') {
            $coords[0] = array();
            foreach ($geom['coordinates'][0] as $pair) {
                // GeoJSON: [lng, lat, ...]
                $coords[0][] = array('lat' => $pair[1], 'lng' => $pair[0]);
            }
        } elseif ($geom['type'] === 'MultiPolygon') {
            foreach ($geom['coordinates'] as $i => $polygon) {
                $coords[$i] = array();
                foreach ($polygon[0] as $pair) {
                    $coords[$i][] = array('lat' => $pair[1], 'lng' => $pair[0]);
                }
            }
        }
        $polygon_json = json_encode($coords);
        // Prepare data for insertion / update.
        $id_wilayah = $props['KDEPUM'] ? str_replace('.', '', $props['KDEPUM']): null;
        $data = array(
            'desa'      => $props['NAMOBJ'] ?? null,
            'kecamatan' => $props['WADMKC'] ?? null,
            'kab_kot'   => $props['WADMKK'] ?? null,
            'provinsi'  => $props['WADMPR'] ?? null,
            'provno'    => $props['KDPPUM'] ?? null,
            'kabkotno'  => $props['KDPKAB'] ? str_replace('.', '', $props['KDPKAB']): null,
            'kecno'     => $props['KDCPUM'] ? str_replace('.', '', $props['KDCPUM']): null,
            'desano'    => $id_wilayah,
            'id_desa'   => $id_wilayah,
            'id2012'    => $id_wilayah,
            'polygon'   => $polygon_json,
        );
        $all_data[] = array(
            'coor' => $coords,
            'data' => $data,
            'html' => json_encode($data),
            'color' => $default_color
        );
        // Check if record already exists.
        $existing_id = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM data_batas_desa WHERE desa = %s AND kecamatan = %s AND kab_kot = %s AND provinsi = %s",
            $data['desa'], $data['kecamatan'], $data['kab_kot'], $data['provinsi']
        ));
        if ($existing_id) {
            $wpdb->update('data_batas_desa', $data, array('id' => $existing_id));
        } else {
            $wpdb->insert('data_batas_desa', $data);
        }
    }
    return $all_data;
}
$maps_all = import_geojson_desa();

?>
<h1 class="text-center">Conversi File SHP ke Google Maps</h1>
<div style="width: 95%; margin: 0 auto; height: 90vh; padding-bottom: 75px;">
    <div id="map-canvas" style="width: 100%; height: 100%;"></div>
</div>

<script type="text/javascript">
    window.maps_all = <?php echo json_encode($maps_all); ?>;
    window.maps_center = <?php echo json_encode($center); ?>;
</script>
<script async defer src="<?php echo $this->get_map_url(); ?>"></script>