<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Satset
 * @subpackage Wp_Satset/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Wp_Satset
 * @subpackage Wp_Satset/public
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */

require_once(SATSET_PLUGIN_PATH.'Shapefile/ShapefileAutoloader.php');

Shapefile\ShapefileAutoloader::register();
use Shapefile\Shapefile;
use Shapefile\ShapefileException;
use Shapefile\ShapefileReader;

require_once(SATSET_PLUGIN_PATH."proj4php/vendor/autoload.php");

use proj4php\Proj4php;
use proj4php\Proj;
use proj4php\Point;

class Wp_Satset_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $functions ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->functions = $functions;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Satset_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Satset_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

    	wp_enqueue_style('dashicons');
		wp_enqueue_style($this->plugin_name . 'bootstrap', plugin_dir_url(__FILE__) . 'css/bootstrap.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . 'select2', plugin_dir_url(__FILE__) . 'css/select2.min.css', array(), $this->version, 'all');
		wp_enqueue_style($this->plugin_name . 'datatables', plugin_dir_url(__FILE__) . 'css/jquery.dataTables.min.css', array(), $this->version, 'all');
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-satset-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wp_Satset_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wp_Satset_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name . 'bootstrap', plugin_dir_url(__FILE__) . 'js/bootstrap.bundle.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'select2', plugin_dir_url(__FILE__) . 'js/select2.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'datatables', plugin_dir_url(__FILE__) . 'js/jquery.dataTables.min.js', array('jquery'), $this->version, false);
		wp_enqueue_script($this->plugin_name . 'chart', plugin_dir_url(__FILE__) . 'js/chart.min.js', array('jquery'), $this->version, false);
		wp_localize_script( $this->plugin_name, 'ajax', array(
		    'url' => admin_url( 'admin-ajax.php' )
		));
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-satset-public.js', array( 'jquery' ), $this->version, false );

	}

	function get_center(){
		$center_map_default = get_option('_crb_google_map_center_satset');
		$ret = array(
			'lat' => 0,
			'lng' => 0
		);
		if(!empty($center_map_default)){
			$center_map_default = explode(',', $center_map_default);
			$ret['lat'] = $center_map_default[0];
			$ret['lng'] = $center_map_default[1];
		}
		return $ret;
	}

	function get_map_url(){
		$api_googlemap = get_option( '_crb_google_api_satset' );
		$api_googlemap = "https://maps.googleapis.com/maps/api/js?key=$api_googlemap&callback=initMap&libraries=places&libraries=drawing";
		// $api_googlemap = "https://maps.googleapis.com/maps/api/js?key=$api_googlemap&callback=initMap&libraries=places&libraries=drawing&v=beta";
		return $api_googlemap;
	}

	function satset_homepage(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-homepage.php';
	}

	function data_p3ke(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-p3ke.php';
	}

	function data_dtks(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-dtks.php';
	}

	function data_stunting(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-stunting.php';
	}

	function data_tbc(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-tbc.php';
	}

	function data_rtlh(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-rtlh.php';
	}

	function peta_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-peta.php';
	}

	function conversi_peta_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-conversi-peta.php';
	}

	function peta_satset_desa(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-peta-batas-desa.php';
	}

	function peta_satset_kecamatan(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-peta-batas-kecamatan.php';
	}

	function cek_nik_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-cek-nik.php';
	}

	function data_irisan_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-data-irisan.php';
	}

	function management_data_p3ke_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-management-data-p3ke.php';
	}
	function management_data_stunting_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-management-data-stunting.php';
	}
	function management_data_tbc_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-management-data-tbc.php';
	}
	function management_data_rtlh_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-management-data-rtlh.php';
	}

	function get_polygon($options = array( 'type' => 'desa' )){
		global $wpdb;

		$default_color = get_option('_crb_warna_p3ke_satset');
		$prov = get_option('_crb_prov_satset');
		$where = " provinsi='$prov'";
		$kab = get_option('_crb_kab_satset');
		if($options['type'] == 'desa'){
			if(!empty($kab)){
				$where .= " and kab_kot='$kab'";
			}
			$data = $wpdb->get_results("
				SELECT 
					* 
				FROM data_batas_desa 
				WHERE $where
				ORDER BY provinsi, kab_kot, kecamatan, desa
			", ARRAY_A);
		}else if($options['type'] == 'kecamatan'){
			if(!empty($kab)){
				$where .= " and kabkot='$kab'";
			}
			$data = $wpdb->get_results("
				SELECT 
					* 
				FROM data_batas_kecamatan 
				WHERE $where
				ORDER BY provinsi, kabkot, kecamatan
			", ARRAY_A);
		}
		$new_data = array();
		foreach($data as $val){
			$coordinate = json_decode($val['polygon'], true);
			if(!empty($coordinate)){
				unset($val['polygon']);
				$new_data[] = array(
					'coor' => $coordinate,
					'data' => $val,
					'html' => json_encode($val),
					'color' => $default_color
				);
			}
		}

		// SELECT * FROM data_batas_desa WHERE provinsi='JAWA TIMUR' and kab_kot='MAGETAN' and desa IN ('KAUMAN','PATIHAN', 'ALASTUWO') order by desa;
		return $new_data;
	}

	function read_shapefile($params = array()){
		global $wpdb;
		$default_color = get_option('_crb_warna_p3ke_satset');
		$file_shp = $params['file'];
		$file_prj = trim(file_get_contents(str_replace('.shp', '.prj', $file_shp)));

	    // Open Shapefile
	    $Shapefile = new ShapefileReader($file_shp);
	    
		// Initialise Proj4
		$proj4 = new Proj4php();

		// Create two different projections.
		$projL93    = new Proj($file_prj, $proj4);
		$projWGS84  = new Proj('EPSG:4326', $proj4);

	    $data = array();
	    // Read all the records
	    while ($Geometry = $Shapefile->fetchRecord()) {
	        if (
	        	$Geometry->isDeleted()
	        	// || !empty($data)
	        ) {
	            continue;
	        }

	        $data_meta = $Geometry->getDataArray();
			$data_map = $Geometry->getArray();
	        // print_r($data_meta);
	        // print_r($data_map);
	        $points = array();
	        if(!empty($data_map['rings'])){
	        	foreach($data_map['rings'] as $ring){
	        		$points[] = $ring;
	        	}
	        }else if(!empty($data_map['parts'])){
	        	foreach($data_map['parts'] as $parts){
		        	foreach($parts['rings'] as $ring){
		        		$points[] = $ring;
		        	}
		        }
	        }

			if(empty($points)){
				continue;
			}

	        $data_meta = $Geometry->getDataArray();
			$coordinate = array();
			foreach($points as $i => $coor1){
				$coordinate[$i] = array();
				foreach($coor1['points'] as $coor){
					$pointSrc = new Point($coor['x'], $coor['y'], $projL93);
					$pointDest = $proj4->transform($projWGS84, $pointSrc);
					$coordinate[$i][] = array(
						'lat' => $pointDest->y,
						'lng' => $pointDest->x
					);
				}
			}

			// print_r($data_meta); die();
			foreach($data_meta as $i => $meta){
				$data_meta[$i] = trim($meta);
			}

	        if(!empty($data_meta['DESA'])){
				$data_meta['DESA'] = strtoupper($data_meta['DESA']);
			}
	        if(!empty($data_meta['KECAMATAN'])){
				$data_meta['KECAMATAN'] = str_replace('KEC.', '', strtoupper($data_meta['KECAMATAN']));
			}
	        if(!empty($data_meta['KABKOT'])){
				$data_meta['KABKOT'] = strtoupper($data_meta['KABKOT']);
			}else{
				$data_meta['KABKOT'] = 'MAGETAN';
			}
	        if(!empty($data_meta['PROVINSI'])){
				$data_meta['PROVINSI'] = strtoupper($data_meta['PROVINSI']);
			}else{
				$data_meta['PROVINSI'] = 'JAWA TIMUR';
			}

	        $data[] =  array(
				'coor' => $coordinate,
				'data' => $data_meta,
				'html' => json_encode($data_meta),
				'color' => $default_color
			);

		    // input data kecamatan
	        if($params['type'] == 'kecamatan'){
				$cek_id = $wpdb->get_var($wpdb->prepare("
					SELECT
						id
					FROM data_batas_kecamatan
					WHERE kecamatan = %s
						AND kabkot = %s
						AND provinsi = %s
				", $data_meta['KECAMATAN'], $data_meta['KABKOT'], $data_meta['PROVINSI']));
				$opsi = array(
				    'provno' => $data_meta['PROVNO'],
				    'kabkotno' => $data_meta['KABKOTNO'],
				    'kecno' => $data_meta['KECNO'],
				    'provinsi' => $data_meta['PROVINSI'],
				    'kabkot' => $data_meta['KABKOT'],
				    'kecamatan' => $data_meta['KECAMATAN'],
				    'id2012' => $data_meta['ID2012'],
				    'polygon' => json_encode($coordinate),
				);
				if(empty($cek_id)){
					$cek_id = $wpdb->insert('data_batas_kecamatan', $opsi);
				}else{
					$wpdb->update('data_batas_kecamatan', $opsi, array(
						'id' => $cek_id
					));
				}
		    // input data desa
			}else if($params['type'] == 'desa'){
				// echo $wpdb->last_query; die();
				if(true == $params['kode_daerah']){
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT
							id
						FROM data_batas_desa
						WHERE desa = %s
							AND kecamatan = %s
							AND kab_kot = %s
							AND provinsi = %s
					", $data_meta['DESA'], $data_meta['KECAMATAN'], $data_meta['KABKOT'], $data_meta['PROVINSI']));
					$opsi = array(
					    'provno' => $data_meta['PROVNO'],
					    'kabkotno' => $data_meta['KABKOTNO'],
					    'kecno' => $data_meta['KECNO'],
					    'desano' => $data_meta['DESANO'],
					    'id2012' => $data_meta['ID2012']
					);
					if(!empty($cek_id)){
						$wpdb->update('data_batas_desa', $opsi, array(
							'id' => $cek_id
						));
					}
				}else{
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT
							id
						FROM data_batas_desa
						WHERE desa = %s
							AND id_desa = %s
							AND kecamatan = %s
							AND kab_kot = %s
							AND provinsi = %s
					", $data_meta['DESA'], $data_meta['ID'], $data_meta['KECAMATAN'], $data_meta['KABKOT'], $data_meta['PROVINSI']));
					$opsi = array(
					    'id_desa' => $data_meta['ID'],
					    'desa' => $data_meta['DESA'],
					    'kecamatan' => $data_meta['KECAMATAN'],
					    'kab_kot' => $data_meta['KABKOT'],
					    'provinsi' => $data_meta['PROVINSI'],
					    'area' => $data_meta['AREA'],
					    'perimeter' => $data_meta['PERIMETER'],
					    'hectares' => $data_meta['HECTARES'],
					    'ukuran_kot' => $data_meta['UKURAN_KOT'],
					    'pemusatan' => $data_meta['PEMUSATAN'],
					    'jumplah_pen' => $data_meta['JUMLAH_PEN'],
					    'polygon' => json_encode($coordinate)
					);
					if(empty($cek_id)){
						$cek_id = $wpdb->insert('data_batas_desa', $opsi);
					}else{
						$wpdb->update('data_batas_desa', $opsi, array(
							'id' => $cek_id
						));
					}
				}
			}
	    }
	    return $data;
	}

public function getNamaDaerah($value=''){
		$prov = get_option('_crb_prov_satset');
		$ret = "Provinsi $prov";
		$kab = get_option('_crb_kab_satset');
		if(!empty($kab)){
			$ret = "Kabupaten $kab<br>$ret";
		}
		return $ret;
	}

	function getSearchLocation($data = array()){
		$text = '';
		if(!empty($data['desa'])){
			$text .= ' '.$data['desa'];
		}
		if(!empty($data['kecamatan'])){
			if(
				empty($data['desa'])
				|| (
					!empty($data['desa']) 
					&& $data['kecamatan'] != $data['desa']
				)
			){
				$text .= ' '.$data['kecamatan'];
			}
		}
		if(!empty($data['kab_kot'])){
			if(
				empty($data['kecamatan'])
				|| (
					!empty($data['kecamatan']) 
					&& $data['kab_kot'] != $data['kecamatan']
				)
			){
				$text .= ' '.$data['kab_kot'];
			}
		}
		if(!empty($data['kabkot'])){
			if(
				empty($data['kecamatan'])
				|| (
					!empty($data['kecamatan']) 
					&& $data['kabkot'] != $data['kecamatan']
				)
			){
				$text .= ' '.$data['kabkot'];
			}
		}
		if(!empty($data['provinsi'])){
			$text .= ' '.$data['provinsi'];
		}
		return $text;
	}

	function get_p3ke(){
		global $wpdb;
		$prov = get_option('_crb_prov_satset');
		$where = " provinsi='$prov'";
		$kab = get_option('_crb_kab_satset');
		if(!empty($kab)){
			$where .= " and kabkot='$kab'";
		}
		$data = $wpdb->get_results("
			SELECT 
				* 
			FROM data_p3ke 
			WHERE $where
			ORDER BY provinsi, kabkot, kecamatan
		", ARRAY_A);
		return $data;
	}

	function get_stunting(){
		global $wpdb;
		$prov = get_option('_crb_prov_satset');
		$where = " provinsi='$prov'";
		$kab = get_option('_crb_kab_satset');
		if(!empty($kab)){
			$where .= " and kabkot='$kab'";
		}
		$data = $wpdb->get_results("
			SELECT 
				* 
			FROM data_stunting 
			WHERE $where
			ORDER BY provinsi, kabkot, kecamatan
		", ARRAY_A);
		return $data;
	}

	function get_tbc(){
		global $wpdb;
		$prov = get_option('_crb_prov_satset');
		$where = " provinsi='$prov'";
		$kab = get_option('_crb_kab_satset');
		if(!empty($kab)){
			$where .= " and kabkot='$kab'";
		}
		$data = $wpdb->get_results("
			SELECT 
				* 
			FROM data_tbc 
			WHERE $where
			ORDER BY provinsi, kabkot, kecamatan
		", ARRAY_A);
		return $data;
	}

	function get_rtlh(){
		global $wpdb;
		$prov = get_option('_crb_prov_satset');
		$where = " provinsi='$prov'";
		$kab = get_option('_crb_kab_satset');
		if(!empty($kab)){
			$where .= " and kabkot='$kab'";
		}
		$data = $wpdb->get_results("
			SELECT 
				* 
			FROM data_rtlh 
			WHERE $where
			ORDER BY provinsi, kabkot, kecamatan
		", ARRAY_A);
		return $data;
	}

	function get_dtks(){
		global $wpdb;
		$prov = get_option('_crb_prov_satset');
		$where = " provinsi='$prov'";
		$kab = get_option('_crb_kab_satset');
		if(!empty($kab)){
			$where .= " and kabkot='$kab'";
		}
		$data = $wpdb->get_results("
			SELECT 
				provinsi, 
				kabkot, 
				kecamatan, 
				desa,
				BLT, 
				BLT_BBM, 
				BPNT, 
				PKH, 
				PBI,
				COUNT(BLT) as jml
			FROM data_dtks_satset 
			WHERE $where
				AND is_nonaktif is null
				AND active=1
			GROUP BY provinsi, kabkot, kecamatan, desa, BLT, BLT_BBM, BPNT, PKH, PBI
			ORDER BY provinsi, kabkot, kecamatan
		", ARRAY_A);
		return $data;
	}

	function number_format($number){
		return number_format($number, 0,",",".");
	}

public function cari_data_satset(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data' => array()
		);
		if(strlen($_POST['nik']) >=3){
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$data = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_p3ke
					WHERE nik like %s
						OR kepala_keluarga like %s
				", '%'.$_POST['nik'].'%', '%'.$_POST['nik'].'%'));
				$data_stunting = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_stunting
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%'));
				$data_tbc = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_tbc
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%'));
				$data_rtlh = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_rtlh
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%'));
				$data_dtks = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_dtks
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%'));
				$ret['data']['p3ke'] = $data;
				$ret['data']['stunting'] = $data_stunting;
				$ret['data']['tbc'] = $data_tbc;
				$ret['data']['rtlh'] = $data_rtlh;
				$ret['data']['dtks'] = $data_dtks;
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function get_data_p3ke_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->get_row($wpdb->prepare('
					SELECT 
						*
					FROM data_p3ke
					WHERE id=%d
				', $_POST['id']), ARRAY_A);
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function hapus_data_p3ke_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil hapus data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_p3ke', array('active' => 0), array(
					'id' => $_POST['id']
				));
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function tambah_data_p3ke(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if($ret['status'] != 'error' && !empty($_POST['id_p3ke'])){
					$id_p3ke = $_POST['id_p3ke'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data id_p3ke tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kode_kemendagri'])){
					$kode_kemendagri = $_POST['kode_kemendagri'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kode_kemendagri tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tambah_nik'])){
					$tambah_nik = $_POST['tambah_nik'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tambah_nik tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tambah_padan_dukcapil'])){
					$tambah_padan_dukcapil = $_POST['tambah_padan_dukcapil'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tambah_padan_dukcapil tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kepala_keluarga'])){
					$kepala_keluarga = $_POST['kepala_keluarga'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kepala_keluarga tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jenis_kelamin'])){
					$jenis_kelamin = $_POST['jenis_kelamin'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_kelamin tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tanggal_lahir'])){
					$tanggal_lahir = $_POST['tanggal_lahir'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tanggal_lahir tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['provinsi'])){
					$provinsi = $_POST['provinsi'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data provinsi tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kabkot'])){
					$kabkot = $_POST['kabkot'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kabkot tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kecamatan'])){
					$kecamatan = $_POST['kecamatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecamatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['desa'])){
					$desa = $_POST['desa'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data desa tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['alamat'])){
					$alamat = $_POST['alamat'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data alamat tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['rumah'])){
					$rumah = $_POST['rumah'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data rumah tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['pekerjaan'])){
					$pekerjaan = $_POST['pekerjaan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data pekerjaan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['pendidikan'])){
					$pendidikan = $_POST['pendidikan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data pendidikan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['punya_tabungan'])){
					$punya_tabungan = $_POST['punya_tabungan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data punya_tabungan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jenis_desil'])){
					$jenis_desil = $_POST['jenis_desil'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_desil tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jenis_atap'])){
					$jenis_atap = $_POST['jenis_atap'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_atap tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jenis_dinding'])){
					$jenis_dinding = $_POST['jenis_dinding'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_dinding tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jenis_lantai'])){
					$jenis_lantai = $_POST['jenis_lantai'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_lantai tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['sumber_penerangan'])){
					$sumber_penerangan = $_POST['sumber_penerangan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data sumber_penerangan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['bahan_bakar_memasak'])){
					$bahan_bakar_memasak = $_POST['bahan_bakar_memasak'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data bahan_bakar_memasak tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['sumber_air_minum'])){
					$sumber_air_minum = $_POST['sumber_air_minum'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data sumber_air_minum tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['fasilitas_bab'])){
					$fasilitas_bab = $_POST['fasilitas_bab'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data fasilitas_bab tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['penerima_bpnt'])){
					$penerima_bpnt = $_POST['penerima_bpnt'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_bpnt tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['penerima_bpum'])){
					$penerima_bpum = $_POST['penerima_bpum'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_bpum tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['penerima_bst'])){
					$penerima_bst = $_POST['penerima_bst'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_bst tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['penerima_pkh'])){
					$penerima_pkh = $_POST['penerima_pkh'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_pkh tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['penerima_sembako'])){
					$penerima_sembako = $_POST['penerima_sembako'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_sembako tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['resiko_stunting'])){
					$resiko_stunting = $_POST['resiko_stunting'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data resiko_stunting tidak boleh kosong!';
				}
				if($ret['status'] != 'error'){
					$data = array(
						'id_p3ke' => $id_p3ke,
						'kode_kemendagri' => $kode_kemendagri,
						'tambah_nik' => $tambah_nik,
						'tambah_padan_dukcapil' => $tambah_padan_dukcapil,
						'kepala_keluarga' => $kepala_keluarga,
						'jenis_kelamin' => $jenis_kelamin,
						'tanggal_lahir' => $tanggal_lahir,
						'provinsi' => $provinsi,
						'kabkot' => $kabkot,
						'kecamatan' => $kecamatan,
						'desa' => $desa,
						'alamat' => $alamat,
						'rumah' => $rumah,
						'pekerjaan' => $pekerjaan,
						'pendidikan' => $pendidikan,
						'punya_tabungan' => $punya_tabungan,
						'jenis_desil' => $jenis_desil,
						'jenis_atap' => $jenis_atap,
						'jenis_dinding' => $jenis_dinding,
						'jenis_lantai' => $jenis_lantai,
						'sumber_penerangan' => $sumber_penerangan,
						'bahan_bakar_memasak' => $bahan_bakar_memasak,
						'sumber_air_minum' => $sumber_air_minum,
						'fasilitas_bab' => $fasilitas_bab,
						'penerima_bpnt' => $penerima_bpnt,
						'penerima_bpum' => $penerima_bpum,
						'penerima_bst' => $penerima_bst,
						'penerima_pkh' => $penerima_pkh,
						'penerima_sembako' => $penerima_sembako,
						'resiko_stunting' => $resiko_stunting,
						'active' => 1,
						'update_at' => current_time('mysql')
					);
					if(!empty($_POST['id_data'])){
						$wpdb->update('data_p3ke', $data, array(
							'id' => $_POST['id_data']
						));
						$ret['message'] = 'Berhasil update data!';
					}else{
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT
								id,
								active
							FROM data_p3ke
							WHERE id_p3ke=%s
						', $id_p3ke), ARRAY_A);
						if(empty($cek_id)){
							$wpdb->insert('data_p3ke', $data);
						}else{
							if($cek_id['active'] == 0){
								$wpdb->update('data_p3ke', $data, array(
									'id' => $cek_id['id']
								));
							}else{
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data P3KE dengan id_p3ke="'.$id_p3ke.'" sudah ada!';
							}
						}
					}
				}
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function get_datatable_p3ke(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data'	=> array()
		);

		if(!empty($_POST)){
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$user_id = um_user( 'ID' );
				$user_meta = get_userdata($user_id);
				$params = $columns = $totalRecords = $data = array();
				$params = $_REQUEST;
				$columns = array( 
				   0 => 'id_p3ke',
				   1 => 'provinsi',
				   2 => 'kabkot',
				   3 => 'kecamatan',
				   4 => 'desa',
				   5 => 'kode_kemendagri',
				   6 => 'jenis_desil',
				   7 => 'alamat',
				   8 => 'kepala_keluarga',
				   9 => 'nik',
				   10 => 'padan_dukcapil',
				   11 => 'jenis_kelamin',
				   12 => 'tanggal_lahir',
				   13 => 'pekerjaan',
				   14 => 'pendidikan',
				   15 => 'rumah',
				   16 => 'punya_tabungan',
				   17 => 'jenis_atap',
				   18 => 'jenis_dinding',
				   19 => 'jenis_lantai',
				   20 => 'sumber_penerangan',
				   21 => 'bahan_bakar_memasak',
				   22 => 'sumber_air_minum',
				   23 => 'fasilitas_bab',
				   24 => 'penerima_bpnt',
				   25 => 'penerima_bpum',
				   26 => 'penerima_bst',
				   27 => 'penerima_pkh',
				   28 => 'penerima_sembako',
				   29 => 'resiko_stunting',
				   30 => 'id'
				);
				$where = $sqlTot = $sqlRec = "";

				// check search value exist
				if( !empty($params['search']['value']) ) {
					$where .=" AND ( id_p3ke LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");    
					$where .=" OR nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .=" OR kepala_keluarga LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .=" OR alamat LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .=" OR rumah LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%")." )";
				}

				// getting total number records without any search
				$sql_tot = "SELECT count(id) as jml FROM `data_p3ke`";
				$sql = "SELECT ".implode(', ', $columns)." FROM `data_p3ke`";
				$where_first = " WHERE 1=1";
				$sqlTot .= $sql_tot.$where_first;
				$sqlRec .= $sql.$where_first;
				if(isset($where) && $where != '') {
					$sqlTot .= $where;
					$sqlRec .= $where;
				}

				$limit = '';
				if($params['length'] != -1){
					$limit = "  LIMIT ".$wpdb->prepare('%d', $params['start'])." ,".$wpdb->prepare('%d', $params['length']);
				}
			 	$sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir'].$limit;

				$queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
				$totalRecords = $queryTot[0]['jml'];
				$queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

				foreach($queryRecords as $recKey => $recVal){
					$btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
					$btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-trash"></i></a>';
					$queryRecords[$recKey]['aksi'] = $btn;
				}

				$json_data = array(
					"draw"            => intval( $params['draw'] ),   
					"recordsTotal"    => intval( $totalRecords ),  
					"recordsFiltered" => intval($totalRecords),
					"data"            => $queryRecords
				);

				die(json_encode($json_data));
			}else{
				$return = array(
					'status' => 'error',
					'message'	=> 'Api Key tidak sesuai!'
				);
			}
		}else{
			$return = array(
				'status' => 'error',
				'message'	=> 'Format tidak sesuai!'
			);
		}
		die(json_encode($return));
	}

public function get_data_stunting_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->get_row($wpdb->prepare('
					SELECT 
						*
					FROM data_stunting
					WHERE id=%d
				', $_POST['id']), ARRAY_A);
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function hapus_data_stunting_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil hapus data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_stunting', array('active' => 0), array(
					'id' => $_POST['id']
				));
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}
	
public function tambah_data_stunting(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if($ret['status'] != 'error' && !empty($_POST['nik'])){
					$nik = $_POST['nik'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data NIK tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['nama'])){
					$nama = $_POST['nama'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data Nama tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jenis_kelamin'])){
					$jenis_kelamin = $_POST['jenis_kelamin'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_kelamin tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tanggal_lahir'])){
					$tanggal_lahir = $_POST['tanggal_lahir'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tanggal_lahir tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['bb_lahir'])){
					$bb_lahir = $_POST['bb_lahir'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data bb_lahir tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tb_lahir'])){
					$tb_lahir = $_POST['tb_lahir'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tb_lahir tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['nama_ortu'])){
					$nama_ortu = $_POST['nama_ortu'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data nama_ortu tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['provinsi'])){
					$provinsi = $_POST['provinsi'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data provinsi tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kabkot'])){
					$kabkot = $_POST['kabkot'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kabkot tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kecamatan'])){
					$kecamatan = $_POST['kecamatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecamatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['puskesmas'])){
					$puskesmas = $_POST['puskesmas'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data puskesmas tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['desa'])){
					$desa = $_POST['desa'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data desa tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['posyandu'])){
					$posyandu = $_POST['posyandu'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data posyandu tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['rt'])){
					$rt = $_POST['rt'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data rt tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['rw'])){
					$rw = $_POST['rw'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data rw tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['alamat'])){
					$alamat = $_POST['alamat'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data alamat tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['usia_saat_ukur'])){
					$usia_saat_ukur = $_POST['usia_saat_ukur'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data usia_saat_ukur tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tanggal_pengukuran'])){
					$tanggal_pengukuran = $_POST['tanggal_pengukuran'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tanggal_pengukuran tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['berat'])){
					$berat = $_POST['berat'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data berat tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tinggi'])){
					$tinggi = $_POST['tinggi'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tinggi tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['lingkar_lengan_atas'])){
					$lingkar_lengan_atas = $_POST['lingkar_lengan_atas'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data lingkar_lengan_atas tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['bb_per_u'])){
					$bb_per_u = $_POST['bb_per_u'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data bb_per_u tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['zs_bb_per_u'])){
					$zs_bb_per_u = $_POST['zs_bb_per_u'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data zs_bb_per_u tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tb_per_u'])){
					$tb_per_u = $_POST['tb_per_u'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tb_per_u tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['zs_tb_per_u'])){
					$zs_tb_per_u = $_POST['zs_tb_per_u'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data zs_tb_per_u tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['bb_per_tb'])){
					$bb_per_tb = $_POST['bb_per_tb'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data bb_per_tb tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['zs_bb_per_tb'])){
					$zs_bb_per_tb = $_POST['zs_bb_per_tb'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data zs_bb_per_tb tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['naik_berat_badan'])){
					$naik_berat_badan = $_POST['naik_berat_badan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data naik_berat_badan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['pmt_diterima_per_kg'])){
					$pmt_diterima_per_kg = $_POST['pmt_diterima_per_kg'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data pmt_diterima_per_kg tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jml_vit_a'])){
					$jml_vit_a = $_POST['jml_vit_a'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jml_vit_a tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kpsp'])){
					$kpsp = $_POST['kpsp'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kpsp tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kia'])){
					$kia = $_POST['kia'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kia tidak boleh kosong!';
				}

				if($ret['status'] != 'error'){
					$data = array(
						'id_stunting' => $id_stunting,
						'nik' => $nik,
						'nama' => $nama,
						'jenis_kelamin' => $jenis_kelamin,
						'tanggal_lahir' => $tanggal_lahir,
						'bb_lahir' => $bb_lahir,
						'tb_lahir' => $tb_lahir,
						'nama_ortu' => $nama_ortu,
						'provinsi' => $provinsi,
						'kabkot' => $kabkot,
						'kecamatan' => $kecamatan,
						'puskesmas' => $puskesmas,
						'desa' => $desa,
						'posyandu' => $posyandu,
						'rt' => $rt,
						'rw' => $rw,
						'alamat' => $alamat,
						'usia_saat_ukur' => $usia_saat_ukur,
						'tanggal_pengukuran' => $tanggal_pengukuran,
						'berat' => $berat,
						'tinggi' => $tinggi,
						'lingkar_lengan_atas' => $lingkar_lengan_atas,
						'bb_per_u' => $bb_per_u,
						'zs_bb_per_u' => $zs_bb_per_u,
						'tb_per_u' => $tb_per_u,
						'zs_tb_per_u' => $zs_tb_per_u,
						'bb_per_tb' => $bb_per_tb,
						'zs_bb_per_tb' => $zs_bb_per_tb,
						'naik_berat_badan' => $naik_berat_badan,
						'pmt_diterima_per_kg' => $pmt_diterima_per_kg,
						'jml_vit_a' => $jml_vit_a,
						'kpsp' => $kpsp,
						'kia' => $kia,
						'active' => 1,
						'update_at' => current_time('mysql')
					);
					if(!empty($_POST['id_data'])){
						$wpdb->update('data_stunting', $data, array(
							'id' => $_POST['id_data']
						));
						$ret['message'] = 'Berhasil update data!';
					}else{
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT
								id,
								active
							FROM data_stunting
							WHERE id_stunting=%s
						', $id_stunting), ARRAY_A);
						if(empty($cek_id)){
							$wpdb->insert('data_stunting', $data);
						}else{
							if($cek_id['active'] == 0){
								$wpdb->update('data_stunting', $data, array(
									'id' => $cek_id['id']
								));
							}else{
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data stunting dengan id_stunting="'.$id_stunting.'" sudah ada!';
							}
						}
					}
				}
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function get_datatable_stunting(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data'	=> array()
		);

		if(!empty($_POST)){
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$user_id = um_user( 'ID' );
				$user_meta = get_userdata($user_id);
				$params = $columns = $totalRecords = $data = array();
				$params = $_REQUEST;
				$columns = array( 
				  0 => 'nik',
				  1 => 'nama',
				  2 => 'jenis_kelamin',
				  3 => 'tanggal_lahir',
				  4 => 'provinsi',
				  5 => 'kabkot',
				  6 => 'kecamatan',
				  7 => 'desa',
				  8 => 'bb_lahir',
				  9 => 'tb_lahir',
				 10	=> 'nama_ortu',
				 11 => 'rt',
				 12 => 'rw',
				 13 => 'alamat',
				 14 => 'puskesmas',
				 15 => 'posyandu',
				 16 => 'usia_saat_ukur',
				 17 => 'tanggal_pengukuran',
				 18 => 'berat',
				 19 => 'tinggi',
				 20 => 'lingkar_lengan_atas',
				 21 => 'bb_per_u',
				 22 => 'tb_per_u',
				 23 => 'zs_bb_per_u',
				 24 => 'zs_tb_per_u',
				 25 => 'bb_per_tb',
				 26 => 'zs_bb_per_tb',
				 27 => 'naik_berat_badan',
				 28 => 'pmt_diterima_per_kg',
				 29 => 'jml_vit_a',
				 30 => 'kpsp',
				 31	=> 'kia',
				 32 => 'id'
				);
				$where = $sqlTot = $sqlRec = "";

				// check search value exist
				if( !empty($params['search']['value']) ) {
					$where .=" AND ( id_stunting LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");    
					$where .=" OR nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .=" OR nama LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .=" OR alamat LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
				}

				// getting total number records without any search
				$sql_tot = "SELECT count(id) as jml FROM `data_stunting`";
				$sql = "SELECT ".implode(', ', $columns)." FROM `data_stunting`";
				$where_first = " WHERE 1=1";
				$sqlTot .= $sql_tot.$where_first;
				$sqlRec .= $sql.$where_first;
				if(isset($where) && $where != '') {
					$sqlTot .= $where;
					$sqlRec .= $where;
				}

				$limit = '';
				if($params['length'] != -1){
					$limit = "  LIMIT ".$wpdb->prepare('%d', $params['start'])." ,".$wpdb->prepare('%d', $params['length']);
				}
			 	$sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir'].$limit;

				$queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
				$totalRecords = $queryTot[0]['jml'];
				$queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

				foreach($queryRecords as $recKey => $recVal){
					$btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
					$btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-trash"></i></a>';
					$queryRecords[$recKey]['aksi'] = $btn;
				}

				$json_data = array(
					"draw"            => intval( $params['draw'] ),   
					"recordsTotal"    => intval( $totalRecords ),  
					"recordsFiltered" => intval($totalRecords),
					"data"            => $queryRecords,
					"sql"            => $sqlRec
				);

				die(json_encode($json_data));
			}else{
				$return = array(
					'status' => 'error',
					'message'	=> 'Api Key tidak sesuai!'
				);
			}
		}else{
			$return = array(
				'status' => 'error',
				'message'	=> 'Format tidak sesuai!'
			);
		}
		die(json_encode($return));
	}	

public function get_data_tbc_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->get_row($wpdb->prepare('
					SELECT 
						*
					FROM data_tbc
					WHERE id=%d
				', $_POST['id']), ARRAY_A);
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function hapus_data_tbc_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil hapus data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_tbc', array('active' => 0), array(
					'id' => $_POST['id']
				));
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function tambah_data_tbc(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if($ret['status'] != 'error' && !empty($_POST['tanggal_register'])){
					$tanggal_register = $_POST['tanggal_register'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tanggal_register tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['no_reg_fasyankes'])){
					$no_reg_fasyankes = $_POST['no_reg_fasyankes'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data no_reg_fasyankes tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['no_reg_kabkot'])){
					$no_reg_kabkot = $_POST['no_reg_kabkot'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data no_reg_kabkot tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['nik'])){
					$nik = $_POST['nik'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data nik tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['nama'])){
					$nama = $_POST['nama'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data nama tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['umur'])){
					$umur = $_POST['umur'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data umur tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jenis_kelamin'])){
					$jenis_kelamin = $_POST['jenis_kelamin'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_kelamin tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['alamat'])){
					$alamat = $_POST['alamat'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data alamat tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['pindahan_dari_fasyankes'])){
					$pindahan_dari_fasyankes = $_POST['pindahan_dari_fasyankes'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data pindahan_dari_fasyankes tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tindak_lanjut'])){
					$tindak_lanjut = $_POST['tindak_lanjut'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tindak_lanjut tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tanggal_mulai_pengobatan'])){
					$tanggal_mulai_pengobatan = $_POST['tanggal_mulai_pengobatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tanggal_mulai_pengobatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['hasil_akhir_pengobatan'])){
					$hasil_akhir_pengobatan = $_POST['hasil_akhir_pengobatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data hasil_akhir_pengobatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['status_pengobatan'])){
					$status_pengobatan = $_POST['status_pengobatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data status_pengobatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['keterangan'])){
					$keterangan = $_POST['keterangan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data keterangan tidak boleh kosong!';
				}
				if($ret['status'] != 'error'){
					$data = array(
						'tanggal_register' => $tanggal_register,
						'no_reg_fasyankes' => $no_reg_fasyankes,
						'no_reg_kabkot' => $no_reg_kabkot,
						'nik' => $nik,
						'nama' => $nama,
						'umur' => $umur,
						'jenis_kelamin' => $jenis_kelamin,
						'alamat' => $alamat,
						'pindahan_dari_fasyankes' => $pindahan_dari_fasyankes,
						'tindak_lanjut' => $tindak_lanjut,
						'tanggal_mulai_pengobatan' => $tanggal_mulai_pengobatan,
						'hasil_akhir_pengobatan' => $hasil_akhir_pengobatan,
						'status_pengobatan' => $status_pengobatan,
						'keterangan' => $keterangan,
						'active' => 1,
						'update_at' => current_time('mysql')
					);
					if(!empty($_POST['id_data'])){
						$wpdb->update('data_tbc', $data, array(
							'id' => $_POST['id_data']
						));
						$ret['message'] = 'Berhasil update data!';
					}else{
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT
								id,
								active
							FROM data_tbc
							WHERE id_tbc=%s
						', $id_tbc), ARRAY_A);
						if(empty($cek_id)){
							$wpdb->insestatus_pengobatan('data_tbc', $data);
						}else{
							if($cek_id['active'] == 0){
								$wpdb->update('data_tbc', $data, array(
									'id' => $cek_id['id']
								));
							}else{
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data tbc dengan id_tbc="'.$id_tbc.'" sudah ada!';
							}
						}
					}
				}
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}
	
public function get_datatable_tbc(){
	 	global $wpdb;
	 	$ret = array(
	 		'status' => 'success',
	 		'message' => 'Berhasil get data!',
	 		'data'	=> array()
	 	);

	 	if(!empty($_POST)){
	 		if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
	 			$user_id = um_user( 'ID' );
	 			$user_meta = get_userdata($user_id);
	 			$params = $columns = $totalRecords = $data = array();
	 			$params = $_REQUEST;
	 			$columns = array( 
	 				0 =>'tanggal_register',
					1 =>'no_reg_fasyankes',
					2 =>'no_reg_kabkot',
					3 =>'nik',
					4 =>'nama',
					5 =>'umur',
					6 =>'jenis_kelamin',
					7 =>'alamat',
					8 =>'pindahan_dari_fasyankes',
				    9 =>'tindak_lanjut',
				   10 =>'tanggal_mulai_pengobatan',
				   11 =>'hasil_akhir_pengobatan',
				   12 =>'status_pengobatan',
				   13 =>'keterangan',
				   14 =>'id',
	 			);

	 			$where = $sqlTot = $sqlRec = "";

	 			// check search value exist
	 			if( !empty($params['search']['value']) ) {
	 				$where .=" AND ( id_tbc LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");    
	 				$where .=" OR nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR nama LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR alamat LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR rumah LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%")." )";
	 			}

	 			// getting total number records without any search
	 			$sql_tot = "SELECT count(id) as jml FROM `data_tbc`";
	 			$sql = "SELECT ".implode(', ', $columns)." FROM `data_tbc`";
	 			$where_first = " WHERE 1=1";
	 			$sqlTot .= $sql_tot.$where_first;
	 			$sqlRec .= $sql.$where_first;
	 			if(isset($where) && $where != '') {
	 				$sqlTot .= $where;
	 				$sqlRec .= $where;
	 			}

	 			$limit = '';
	 			if($params['length'] != -1){
	 				$limit = "  LIMIT ".$wpdb->prepare('%d', $params['start'])." ,".$wpdb->prepare('%d', $params['length']);
	 			}
	 		 	$sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir'].$limit;

	 			$queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
	 			$totalRecords = $queryTot[0]['jml'];
	 			$queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

	 			foreach($queryRecords as $recKey => $recVal){
	 				$btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
					$btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-trash"></i></a>';
					$queryRecords[$recKey]['aksi'] = $btn;
				}

				$json_data = array(
					"draw"            => intval( $params['draw'] ),   
					"recordsTotal"    => intval( $totalRecords ),  
					"recordsFiltered" => intval($totalRecords),
					"data"            => $queryRecords,
					"sql"             => $sqlRec
				);

				die(json_encode($json_data));
			}else{
				$return = array(
					'status' => 'error',
					'message'	=> 'Api Key tidak sesuai!'
				);
			}
		}else{
			$return = array(
				'status' => 'error',
				'message'	=> 'Format tidak sesuai!'
			);
		}
		die(json_encode($return));
	}

public function get_data_rtlh_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->get_row($wpdb->prepare('
					SELECT 
						*
					FROM data_rtlh
					WHERE id=%d
				', $_POST['id']), ARRAY_A);
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function hapus_data_rtlh_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil hapus data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_rtlh', array('active' => 0), array(
					'id' => $_POST['id']
				));
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}

public function tambah_data_rtlh(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if($ret['status'] != 'error' && !empty($_POST['nik'])){
					$nik = $_POST['nik'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data nik tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['nama'])){
					$nama = $_POST['nama'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data nama tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['alamat'])){
					$alamat = $_POST['alamat'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data alamat tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['provinsi'])){
					$provinsi = $_POST['provinsi'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data provinsi tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kabkot'])){
					$kabkot = $_POST['kabkot'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kabkot tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kecamatan'])){
					$kecamatan = $_POST['kecamatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecamatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['desa'])){
					$desa = $_POST['desa'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data desa tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['rt'])){
					$rt = $_POST['rt'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data rt tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['rw'])){
					$rw = $_POST['rw'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data rw tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['nilai_bantuan'])){
					$nilai_bantuan = $_POST['nilai_bantuan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data nilai_bantuan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['lpj'])){
					$lpj = $_POST['lpj'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data lpj tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['tgl_lpj'])){
					$tgl_lpj = $_POST['tgl_lpj'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data tgl_lpj tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['sumber_dana'])){
					$sumber_dana = $_POST['sumber_dana'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data sumber_dana tidak boleh kosong!';
				}
				if($ret['status'] != 'error'){
					$data = array(
						'nik' => $nik,
						'nama' => $nama,
						'alamat' => $alamat,
						'provinsi' => $provinsi,
						'kabkot' => $kabkot,
						'kecamatan' => $kecamatan,
						'desa' => $desa,
						'rt' => $rt,
						'rw' => $rw,
						'nilai_bantuan' => $nilai_bantuan,
						'lpj' => $lpj,
						'tgl_lpj' => $tgl_lpj,
						'sumber_dana' => $sumber_dana,
						'active' => 1,
						'update_at' => current_time('mysql')
					);
					if(!empty($_POST['id_data'])){
						$wpdb->update('data_rtlh', $data, array(
							'id' => $_POST['id_data']
						));
						$ret['message'] = 'Berhasil update data!';
					}else{
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT
								id,
								active
							FROM data_rtlh
							WHERE id_rtlh=%s
						', $id_rtlh), ARRAY_A);
						if(empty($cek_id)){
							$wpdb->insert('data_rtlh', $data);
						}else{
							if($cek_id['active'] == 0){
								$wpdb->update('data_rtlh', $data, array(
									'id' => $cek_id['id']
								));
							}else{
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data rtlh dengan id_rtlh="'.$id_rtlh.'" sudah ada!';
							}
						}
					}
				}
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
			}
		}else{
			$ret['status']	= 'error';
			$ret['message']	= 'Format Salah!';
		}

		die(json_encode($ret));
	}
	
public function get_datatable_rtlh(){
	 	global $wpdb;
	 	$ret = array(
	 		'status' => 'success',
	 		'message' => 'Berhasil get data!',
	 		'data'	=> array()
	 	);

	 	if(!empty($_POST)){
	 		if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
	 			$user_id = um_user( 'ID' );
	 			$user_meta = get_userdata($user_id);
	 			$params = $columns = $totalRecords = $data = array();
	 			$params = $_REQUEST;
	 			$columns = array( 
	 			  0 => 'nik',
	 			  1 => 'nama',
	 			  2 => 'alamat',
	 			  3 => 'provinsi',
	 			  4 => 'kabkot',
	 			  5 => 'kecamatan',
	 			  6 => 'rt',
	 			  7 => 'rw',
	 			  8 => 'desa',
	 			  9 => 'nilai_bantuan',
	 			  10 => 'lpj',
	 			  11 => 'tgl_lpj',
	 			  12 => 'sumber_dana',
	 			  13 => 'id'
	 			);
	 			$where = $sqlTot = $sqlRec = "";

	 			// check search value exist
	 			if( !empty($params['search']['value']) ) {
	 				$where .=" AND ( id_rtlh LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");    
	 				$where .=" OR nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR nama LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR alamat LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR rumah LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%")." )";
	 			}

	 			// getting total number records without any search
	 			$sql_tot = "SELECT count(id) as jml FROM `data_rtlh`";
	 			$sql = "SELECT ".implode(', ', $columns)." FROM `data_rtlh`";
	 			$where_first = " WHERE 1=1";
	 			$sqlTot .= $sql_tot.$where_first;
	 			$sqlRec .= $sql.$where_first;
	 			if(isset($where) && $where != '') {
	 				$sqlTot .= $where;
	 				$sqlRec .= $where;
	 			}

	 			$limit = '';
	 			if($params['length'] != -1){
	 				$limit = "  LIMIT ".$wpdb->prepare('%d', $params['start'])." ,".$wpdb->prepare('%d', $params['length']);
	 			}
	 		 	$sqlRec .=  " ORDER BY ". $columns[$params['order'][0]['column']]."   ".$params['order'][0]['dir'].$limit;

	 			$queryTot = $wpdb->get_results($sqlTot, ARRAY_A);
	 			$totalRecords = $queryTot[0]['jml'];
	 			$queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

	 			foreach($queryRecords as $recKey => $recVal){
	 				$btn = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
					$btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-trash"></i></a>';
					$queryRecords[$recKey]['aksi'] = $btn;
				}

				$json_data = array(
					"draw"            => intval( $params['draw'] ),   
					"recordsTotal"    => intval( $totalRecords ),  
					"recordsFiltered" => intval($totalRecords),
					"data"            => $queryRecords,
					"sql"             => $sqlRec
				);

				die(json_encode($json_data));
			}else{
				$return = array(
					'status' => 'error',
					'message'	=> 'Api Key tidak sesuai!'
				);
			}
		}else{
			$return = array(
				'status' => 'error',
				'message'	=> 'Format tidak sesuai!'
			);
		}
		die(json_encode($return));
	}
}