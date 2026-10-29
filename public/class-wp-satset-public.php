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
	private $functions;

	private function is_infrastruktur_map_page(){
		if (!is_singular()) {
			return false;
		}
		$post = get_queried_object();
		return is_object($post)
			&& isset($post->post_content)
			&& (
				has_shortcode($post->post_content, 'management_data_infrastruktur_satset')
				|| has_shortcode($post->post_content, 'data_infrastruktur_publik_satset')
			);
	}

	private function is_infrastruktur_public_page(){
		if (!is_singular()) {
			return false;
		}
		$post = get_queried_object();
		return is_object($post)
			&& isset($post->post_content)
			&& has_shortcode($post->post_content, 'data_infrastruktur_publik_satset');
	}

	private function is_monev_infrastruktur_management_page(){
		if (!is_singular()) {
			return false;
		}
		$post = get_queried_object();
		return is_object($post)
			&& isset($post->post_content)
			&& has_shortcode($post->post_content, 'management_monev_infrastruktur_satset');
	}

	private function is_leaflet_public_map_page(){
		if (!is_singular()) {
			return false;
		}
		$post = get_queried_object();
		if (!is_object($post) || !isset($post->post_content)) {
			return false;
		}

		$map_shortcodes = array(
			'peta_satset',
			'peta_satset_desa',
			'peta_satset_kecamatan',
			'data_irisan_satset',
			'data_p3ke',
			'data_stunting',
			'data_tbc',
			'data_aids',
			'data_malaria',
			'data_rtlh',
			'data_dtks',
			'data_dtsen',
			'data_ats',
		);
		foreach ($map_shortcodes as $shortcode) {
			if (has_shortcode($post->post_content, $shortcode)) {
				return true;
			}
		}
		return false;
	}

	private function has_custom_attributes_table(){
		global $wpdb;
		$table = 'data_custom_attributes';
		return (bool) $wpdb->get_var($wpdb->prepare(
			'SHOW TABLES LIKE %s',
			$wpdb->esc_like($table)
		));
	}

	private function get_infrastruktur_custom_attributes($entity_ids){
		global $wpdb;

		$entity_ids = array_values(array_unique(array_filter(array_map('absint', $entity_ids))));
		if (!$entity_ids) {
			return array();
		}
		$entity_ids_sql = implode(',', $entity_ids);
		$rows = $wpdb->get_results($wpdb->prepare(
			"SELECT entity_id, attribute_name, attribute_type, attribute_value
			FROM data_custom_attributes
			WHERE entity_type = %s AND entity_id IN ({$entity_ids_sql})
			ORDER BY id ASC",
			'infrastruktur'
		), ARRAY_A);
		if ($wpdb->last_error !== '') {
			return false;
		}

		$attributes_by_entity = array();
		$valid_types = array('text', 'url', 'image');
		foreach ($rows as $row) {
			$type = in_array($row['attribute_type'], $valid_types, true) ? $row['attribute_type'] : 'text';
			$attributes_by_entity[(int) $row['entity_id']][$row['attribute_name']] = array(
				'type' => $type,
				'value' => $row['attribute_value']
			);
		}
		return $attributes_by_entity;
	}

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
		if ($this->is_infrastruktur_map_page()) {
			wp_enqueue_style('satset-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
			wp_enqueue_style($this->plugin_name . 'infrastruktur', plugin_dir_url(__FILE__) . 'css/wp-satset-infrastruktur.css', array($this->plugin_name . 'bootstrap', 'satset-leaflet'), $this->version, 'all');
		}
		if ($this->is_monev_infrastruktur_management_page()) {
			wp_enqueue_style(
				$this->plugin_name . 'monev-infrastruktur',
				plugin_dir_url(__FILE__) . 'css/wp-satset-monev-infrastruktur.css',
				array($this->plugin_name . 'bootstrap'),
				filemtime(plugin_dir_path(__FILE__) . 'css/wp-satset-monev-infrastruktur.css'),
				'all'
			);
		}
		if ($this->is_leaflet_public_map_page()) {
			wp_enqueue_style('satset-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.css', array(), '1.9.4');
			wp_enqueue_style($this->plugin_name . 'leaflet-public-map', plugin_dir_url(__FILE__) . 'css/wp-satset-leaflet-public.css', array($this->plugin_name . 'bootstrap', 'satset-leaflet'), filemtime(plugin_dir_path(__FILE__) . 'css/wp-satset-leaflet-public.css'), 'all');
		}

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
			'api_key' => get_option(SATSET_APIKEY),
		    'url' => admin_url( 'admin-ajax.php' )
		));
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-satset-public.js', array( 'jquery' ), filemtime(plugin_dir_path(__FILE__) . 'js/wp-satset-public.js'), false );
		$leaflet_public_map_page = $this->is_leaflet_public_map_page();
		wp_localize_script($this->plugin_name, 'satsetPublicMap', array(
			'useLeaflet' => $leaflet_public_map_page,
		));
		if ($this->is_infrastruktur_map_page()) {
			wp_enqueue_script('satset-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
			$google_maps_api_key = trim((string) get_option('_crb_google_api_satset'));
			$infrastruktur_dependencies = array('jquery', $this->plugin_name . 'bootstrap', $this->plugin_name . 'datatables', 'satset-leaflet');
			if ($google_maps_api_key !== '') {
				wp_enqueue_script(
					'satset-google-maps',
					add_query_arg('key', $google_maps_api_key, 'https://maps.googleapis.com/maps/api/js'),
					array(),
					null,
					true
				);
				wp_enqueue_script(
					'satset-leaflet-google-mutant',
					'https://unpkg.com/leaflet.gridlayer.googlemutant@0.13.5/dist/Leaflet.GoogleMutant.js',
					array('satset-leaflet', 'satset-google-maps'),
					'0.13.5',
					true
				);
				$infrastruktur_dependencies[] = 'satset-leaflet-google-mutant';
			}
			wp_enqueue_script(
				$this->plugin_name . 'infrastruktur',
				plugin_dir_url(__FILE__) . 'js/wp-satset-infrastruktur.js',
				$infrastruktur_dependencies,
				$this->version,
				true
			);
			wp_localize_script($this->plugin_name . 'infrastruktur', 'satsetInfrastructure', array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('satset_infrastruktur_management'),
				'hasGoogleMapsApiKey' => $google_maps_api_key !== '',
				'isPublic' => $this->is_infrastruktur_public_page()
			));
		}
		if ($this->is_monev_infrastruktur_management_page()) {
			wp_enqueue_script(
				$this->plugin_name . 'monev-infrastruktur',
				plugin_dir_url(__FILE__) . 'js/wp-satset-monev-infrastruktur.js',
				array('jquery', $this->plugin_name . 'bootstrap', $this->plugin_name . 'datatables'),
				filemtime(plugin_dir_path(__FILE__) . 'js/wp-satset-monev-infrastruktur.js'),
				true
			);
			wp_localize_script($this->plugin_name . 'monev-infrastruktur', 'satsetMonevInfrastructure', array(
				'ajaxUrl' => admin_url('admin-ajax.php'),
				'nonce' => wp_create_nonce('satset_monev_infrastruktur_management')
			));
		}
		if ($leaflet_public_map_page) {
			wp_enqueue_script('satset-leaflet', 'https://unpkg.com/leaflet@1.9.4/dist/leaflet.js', array(), '1.9.4', true);
			$google_maps_api_key = trim((string) get_option('_crb_google_api_satset'));
			$leaflet_dependencies = array('jquery', $this->plugin_name . 'chart', 'satset-leaflet');
			if ($google_maps_api_key !== '') {
				wp_enqueue_script(
					'satset-google-maps',
					add_query_arg('key', $google_maps_api_key, 'https://maps.googleapis.com/maps/api/js'),
					array(),
					null,
					true
				);
				wp_enqueue_script(
					'satset-leaflet-google-mutant',
					'https://unpkg.com/leaflet.gridlayer.googlemutant@0.13.5/dist/Leaflet.GoogleMutant.js',
					array('satset-leaflet', 'satset-google-maps'),
					'0.13.5',
					true
				);
				$leaflet_dependencies[] = 'satset-leaflet-google-mutant';
			}
			wp_enqueue_script(
				$this->plugin_name . 'leaflet-public-map',
				plugin_dir_url(__FILE__) . 'js/wp-satset-leaflet-public.js',
				$leaflet_dependencies,
				filemtime(plugin_dir_path(__FILE__) . 'js/wp-satset-leaflet-public.js'),
				true
			);
			wp_localize_script($this->plugin_name . 'leaflet-public-map', 'satsetLeafletPublic', array(
				'hasGoogleMapsApiKey' => $google_maps_api_key !== '',
			));
		}
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
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/misc/wp-satset-public-homepage.php';
	}

	function data_p3ke(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/p3ke/wp-satset-public-p3ke.php';
	}

	function data_dtks(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/dtks/wp-satset-public-dtks.php';
	}

	function data_dtsen(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/dtsen/wp-satset-public-dtsen.php';
	}

	function data_ats(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/ats/wp-satset-public-ats.php';
	}

	function data_aids(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/aids/wp-satset-public-aids.php';
	}

	function data_detail_aids(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/aids/wp-satset-public-detail-aids.php';
	}

	function mapping_desa_aids(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/aids/wp-satset-public-mapping-desa-aids.php';
	}
	
	function data_detail_ats(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/ats/wp-satset-public-detail-ats.php';
	}

	function data_malaria(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/malaria/wp-satset-public-malaria.php';
	}

	function data_detail_malaria(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/malaria/wp-satset-public-detail-malaria.php';
	}

	function mapping_desa_malaria(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/malaria/wp-satset-public-mapping-desa-malaria.php';
	}
	
	function data_detail_tbc(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/tbc/wp-satset-public-detail-tbc.php';
	}
	
	function data_detail_dtsen(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/dtsen/wp-satset-public-detail-dtsen.php';
	}

	function data_detail_kk_dtsen(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/dtsen/wp-satset-public-detail-kk-dtsen.php';
	}

	function data_stunting(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/stunting/wp-satset-public-stunting.php';
	}

	function data_tbc(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/tbc/wp-satset-public-tbc.php';
	}

	function data_rtlh(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/rtlh/wp-satset-public-rtlh.php';
	}

	function peta_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/peta/wp-satset-public-peta.php';
	}

	function conversi_peta_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/peta/wp-satset-public-conversi-peta.php';
	}

	function peta_satset_desa(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/peta/wp-satset-public-peta-batas-desa.php';
	}

	function peta_satset_kecamatan(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/peta/wp-satset-public-peta-batas-kecamatan.php';
	}

	function cek_nik_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/misc/wp-satset-public-cek-nik.php';
	}

	function data_irisan_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/irisan/wp-satset-data-irisan.php';
	}

	function filter_data_irisan_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/irisan/wp-satset-filter-data-irisan.php';
	}

	function management_data_p3ke_satset($atts){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/p3ke/wp-satset-management-data-p3ke.php';
	}

	function management_data_p3ke_anggota_keluarga($atts){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/p3ke/wp-satset-management-data-p3ke-anggota-keluarga.php';
	}
	function management_data_stunting_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/stunting/wp-satset-management-data-stunting.php';
	}
	function management_data_infrastruktur_satset(){
		if(!current_user_can('manage_options')){
			return '<p>Anda tidak memiliki izin untuk mengelola data infrastruktur.</p>';
		}
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/infrastruktur/wp-satset-management-data-infrastruktur.php';
	}

	function management_monev_infrastruktur_satset(){
		if(!current_user_can('manage_options')){
			return '<p>Anda tidak memiliki izin untuk mengelola data Monev Infrastruktur.</p>';
		}
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/infrastruktur/wp-satset-management-monev-infrastruktur.php';
	}

	private function get_monev_infrastruktur_management_table(){
		check_ajax_referer('satset_monev_infrastruktur_management', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Anda tidak memiliki izin untuk mengelola data Monev Infrastruktur.'), 403);
		}

		global $wpdb;
		$table = 'data_monev_infrastruktur';
		$existing_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
		if ($existing_table !== $table) {
			wp_send_json_error(array('message' => 'Tabel data_monev_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options terlebih dahulu.'), 500);
		}
		$mapping_column = $wpdb->get_var("SHOW COLUMNS FROM {$table} LIKE 'infrastruktur_id'");
		if ($mapping_column === null) {
			if ($wpdb->query("ALTER TABLE {$table} ADD `infrastruktur_id` BIGINT(20) UNSIGNED DEFAULT NULL, ADD KEY `idx_monev_infrastruktur_id` (`infrastruktur_id`)") === false) {
				wp_send_json_error(array('message' => 'Kolom pemetaan Data Infrastruktur tidak dapat dibuat: ' . $wpdb->last_error), 500);
			}
		} elseif ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal memeriksa kolom pemetaan Data Infrastruktur: ' . $wpdb->last_error), 500);
		}
		return $table;
	}

	public function get_monev_infrastruktur_management(){
		$table = $this->get_monev_infrastruktur_management_table();
		global $wpdb;
		$columns = 'id, tahun_anggaran, kode_program, nama_program, ringkasan_program, '
			. 'kode_kegiatan, nama_kegiatan, ringkasan_kegiatan, kode_sub_kegiatan, '
			. 'nama_sub_kegiatan, ringkasan_sub_kegiatan, urutan_paket, data_paket, infrastruktur_id, created_at';
		$records = $wpdb->get_results(
			"SELECT {$columns} FROM {$table} ORDER BY tahun_anggaran DESC, kode_program ASC, kode_kegiatan ASC, kode_sub_kegiatan ASC, urutan_paket ASC, id ASC",
			ARRAY_A
		);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca data Monev Infrastruktur: ' . $wpdb->last_error), 500);
		}
		foreach ($records as $record) {
			$package = json_decode($record['data_paket']);
			if (json_last_error() !== JSON_ERROR_NONE || (!is_object($package) && !is_array($package))) {
				wp_send_json_error(array(
					'message' => sprintf('Data JSON paket pekerjaan dengan ID %d tidak valid dan tidak dapat ditampilkan.', (int) $record['id'])
				), 500);
			}
		}
		$infrastructure_table = 'data_infrastruktur';
		$existing_infrastructure_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($infrastructure_table)));
		if ($existing_infrastructure_table !== $infrastructure_table) {
			wp_send_json_error(array('message' => 'Tabel data_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}
		$infrastructure = $wpdb->get_results("SELECT id, nm_ruas FROM {$infrastructure_table} ORDER BY nm_ruas ASC", ARRAY_A);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca pilihan Data Infrastruktur: ' . $wpdb->last_error), 500);
		}
		wp_send_json_success(array('records' => $records, 'infrastructure' => $infrastructure));
	}

	private function get_monev_infrastruktur_mapping_tables() {
		$monev_table = $this->get_monev_infrastruktur_management_table();
		global $wpdb;
		$infrastructure_table = 'data_infrastruktur';
		$existing_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($infrastructure_table)));
		if ($existing_table !== $infrastructure_table) {
			wp_send_json_error(array('message' => 'Tabel data_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}
		return array($monev_table, $infrastructure_table);
	}

	private function get_monev_package_name($package_json) {
		$package = json_decode($package_json, true);
		if (json_last_error() !== JSON_ERROR_NONE || !is_array($package)) {
			return '';
		}
		foreach (array('nama', 'nama_paket', 'name') as $key) {
			if (isset($package[$key]) && is_scalar($package[$key])) {
				return trim((string) $package[$key]);
			}
		}
		return '';
	}

	public function map_monev_infrastruktur_management() {
		check_ajax_referer('satset_monev_infrastruktur_management', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Anda tidak memiliki izin untuk mengelola data Monev Infrastruktur.'), 403);
		}
		list($monev_table, $infrastructure_table) = $this->get_monev_infrastruktur_mapping_tables();
		global $wpdb;
		$id = isset($_POST['id']) && is_scalar($_POST['id']) ? trim((string) wp_unslash($_POST['id'])) : '';
		$infrastructure_id = isset($_POST['infrastruktur_id']) && is_scalar($_POST['infrastruktur_id'])
			? trim((string) wp_unslash($_POST['infrastruktur_id']))
			: '';
		if (!ctype_digit($id) || absint($id) < 1) {
			wp_send_json_error(array('message' => 'ID paket pekerjaan tidak valid.'), 400);
		}
		$record_exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$monev_table} WHERE id = %d", absint($id)));
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal memeriksa paket pekerjaan: ' . $wpdb->last_error), 500);
		}
		if (!$record_exists) {
			wp_send_json_error(array('message' => 'Paket pekerjaan yang akan dipetakan tidak ditemukan.'), 404);
		}
		$mapped_id = null;
		if ($infrastructure_id !== '') {
			if (!ctype_digit($infrastructure_id) || absint($infrastructure_id) < 1) {
				wp_send_json_error(array('message' => 'Pilihan Data Infrastruktur tidak valid.'), 400);
			}
			$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$infrastructure_table} WHERE id = %d", absint($infrastructure_id)));
			if ($wpdb->last_error !== '') {
				wp_send_json_error(array('message' => 'Gagal memeriksa pilihan Data Infrastruktur: ' . $wpdb->last_error), 500);
			}
			if (!$exists) {
				wp_send_json_error(array('message' => 'Data Infrastruktur yang dipilih tidak ditemukan.'), 404);
			}
			$mapped_id = absint($infrastructure_id);
		}
		$result = $wpdb->update(
			$monev_table,
			array('infrastruktur_id' => $mapped_id),
			array('id' => absint($id)),
			array('%d'),
			array('%d')
		);
		if ($result === false) {
			wp_send_json_error(array('message' => 'Gagal menyimpan pemetaan Data Infrastruktur: ' . $wpdb->last_error), 500);
		}
		wp_send_json_success(array('message' => 'Pemetaan Data Infrastruktur berhasil disimpan.'));
	}

	public function auto_map_monev_infrastruktur_management() {
		check_ajax_referer('satset_monev_infrastruktur_management', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Anda tidak memiliki izin untuk mengelola data Monev Infrastruktur.'), 403);
		}
		list($monev_table, $infrastructure_table) = $this->get_monev_infrastruktur_mapping_tables();
		global $wpdb;
		$infrastructure = $wpdb->get_results("SELECT id, nm_ruas FROM {$infrastructure_table}", ARRAY_A);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca Data Infrastruktur: ' . $wpdb->last_error), 500);
		}
		$infrastructure_by_name = array();
		foreach ($infrastructure as $item) {
			$road_name = trim((string) $item['nm_ruas']);
			if ($road_name !== '') {
				$infrastructure_by_name[$road_name] = (int) $item['id'];
			}
		}
		$records = $wpdb->get_results("SELECT id, data_paket, infrastruktur_id FROM {$monev_table}", ARRAY_A);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca paket pekerjaan untuk pemetaan: ' . $wpdb->last_error), 500);
		}
		$matches = array();
		$mapped_count = 0;
		foreach ($records as $record) {
			$name = $this->get_monev_package_name($record['data_paket']);
			$road_name = trim($name);
			if ($road_name !== '' && isset($infrastructure_by_name[$road_name])) {
				$infrastructure_id = $infrastructure_by_name[$road_name];
				$mapped_count++;
				if ((int) $record['infrastruktur_id'] !== $infrastructure_id) {
					$matches[(int) $record['id']] = $infrastructure_id;
				}
			}
		}
		if ($matches && $wpdb->query('START TRANSACTION') === false) {
			wp_send_json_error(array('message' => 'Transaksi pemetaan tidak dapat dimulai: ' . $wpdb->last_error), 500);
		}
		foreach ($matches as $record_id => $infrastructure_id) {
			if ($wpdb->update($monev_table, array('infrastruktur_id' => $infrastructure_id), array('id' => $record_id), array('%d'), array('%d')) === false) {
				$error_message = $wpdb->last_error;
				$wpdb->query('ROLLBACK');
				wp_send_json_error(array('message' => 'Pemetaan otomatis gagal; seluruh perubahan dibatalkan: ' . $error_message), 500);
			}
		}
		if ($matches && $wpdb->query('COMMIT') === false) {
			$error_message = $wpdb->last_error;
			$wpdb->query('ROLLBACK');
			wp_send_json_error(array('message' => 'Pemetaan otomatis gagal disimpan: ' . $error_message), 500);
		}
		wp_send_json_success(array(
			'message' => sprintf('%d paket cocok; %d pemetaan baru disimpan.', $mapped_count, count($matches)),
			'mapped' => $mapped_count,
			'updated' => count($matches)
		));
	}

	public function save_monev_infrastruktur_management(){
		$table = $this->get_monev_infrastruktur_management_table();
		global $wpdb;

		$get_post_value = function($key) {
			if (!isset($_POST[$key]) || !is_scalar($_POST[$key])) {
				return '';
			}
			return trim((string) wp_unslash($_POST[$key]));
		};
		$year_value = $get_post_value('tahun_anggaran');
		if (!preg_match('/^\d{4}$/', $year_value) || (int) $year_value < 1900 || (int) $year_value > 2155) {
			wp_send_json_error(array('message' => 'Tahun anggaran harus berupa tahun 4 digit yang valid.'), 400);
		}

		$text_fields = array(
			'kode_program' => array('Kode program', 100, true),
			'nama_program' => array('Nama program', null, true),
			'kode_kegiatan' => array('Kode kegiatan', 100, true),
			'nama_kegiatan' => array('Nama kegiatan', null, true),
			'kode_sub_kegiatan' => array('Kode subkegiatan', 100, true),
			'nama_sub_kegiatan' => array('Nama subkegiatan', null, true)
		);
		$values = array('tahun_anggaran' => (int) $year_value);
		foreach ($text_fields as $column => $field) {
			$value = sanitize_textarea_field($get_post_value($column));
			if ($field[2] && $value === '') {
				wp_send_json_error(array('message' => $field[0] . ' wajib diisi.'), 400);
			}
			if ($field[1] !== null && strlen($value) > $field[1]) {
				wp_send_json_error(array('message' => $field[0] . ' maksimal ' . $field[1] . ' karakter.'), 400);
			}
			$values[$column] = $value;
		}

		$summary_fields = array('ringkasan_program', 'ringkasan_kegiatan', 'ringkasan_sub_kegiatan');
		foreach ($summary_fields as $column) {
			$summary = $get_post_value($column);
			if ($summary === '') {
				$values[$column] = null;
				continue;
			}
			json_decode($summary);
			if (json_last_error() !== JSON_ERROR_NONE) {
				wp_send_json_error(array('message' => 'Ringkasan harus berupa JSON yang valid atau dikosongkan.'), 400);
			}
			$values[$column] = $summary;
		}

		$order_value = $get_post_value('urutan_paket');
		if (!preg_match('/^\d+$/', $order_value) || (float) $order_value > 4294967295) {
			wp_send_json_error(array('message' => 'Urutan paket harus berupa bilangan bulat positif yang valid.'), 400);
		}
		$values['urutan_paket'] = (int) $order_value;

		$package_raw = $get_post_value('data_paket');
		$package = json_decode($package_raw);
		if (
			json_last_error() !== JSON_ERROR_NONE
			|| (!is_object($package) && !is_array($package))
		) {
			wp_send_json_error(array('message' => 'Data paket harus berupa objek atau daftar JSON yang valid.'), 400);
		}
		$package_json = wp_json_encode($package);
		if ($package_json === false) {
			wp_send_json_error(array('message' => 'Data paket tidak dapat dikodekan sebagai JSON.'), 400);
		}
		$values['data_paket'] = $package_json;

		$id_value = $get_post_value('id');
		if ($id_value !== '' && (!ctype_digit($id_value) || absint($id_value) < 1)) {
			wp_send_json_error(array('message' => 'ID paket pekerjaan tidak valid.'), 400);
		}
		$id = $id_value === '' ? 0 : absint($id_value);
		$formats = array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s');

		if ($id > 0) {
			$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE id = %d", $id));
			if (!$exists) {
				wp_send_json_error(array('message' => 'Paket pekerjaan yang akan diubah tidak ditemukan.'), 404);
			}
			$result = $wpdb->update($table, $values, array('id' => $id), $formats, array('%d'));
			if ($result === false) {
				wp_send_json_error(array('message' => 'Gagal memperbarui data Monev Infrastruktur: ' . $wpdb->last_error), 500);
			}
			wp_send_json_success(array('id' => $id, 'message' => 'Data paket pekerjaan berhasil diperbarui.'));
		}

		if ($wpdb->insert($table, $values, $formats) === false) {
			wp_send_json_error(array('message' => 'Gagal menambahkan data Monev Infrastruktur: ' . $wpdb->last_error), 500);
		}
		wp_send_json_success(array('id' => (int) $wpdb->insert_id, 'message' => 'Data paket pekerjaan berhasil ditambahkan.'));
	}

	public function delete_monev_infrastruktur_management(){
		$table = $this->get_monev_infrastruktur_management_table();
		global $wpdb;
		$id_value = isset($_POST['id']) && is_scalar($_POST['id'])
			? trim((string) wp_unslash($_POST['id']))
			: '';
		if (!ctype_digit($id_value) || absint($id_value) < 1) {
			wp_send_json_error(array('message' => 'ID paket pekerjaan tidak valid.'), 400);
		}
		$result = $wpdb->delete($table, array('id' => absint($id_value)), array('%d'));
		if ($result === false) {
			wp_send_json_error(array('message' => 'Gagal menghapus data Monev Infrastruktur: ' . $wpdb->last_error), 500);
		}
		if ($result === 0) {
			wp_send_json_error(array('message' => 'Paket pekerjaan yang akan dihapus tidak ditemukan.'), 404);
		}
		wp_send_json_success(array('message' => 'Data paket pekerjaan berhasil dihapus.'));
	}

	function data_infrastruktur_publik_satset(){
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/infrastruktur/wp-satset-public-data-infrastruktur.php';
	}

	public function get_data_infrastruktur_management(){
		check_ajax_referer('satset_infrastruktur_management', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Anda tidak memiliki izin untuk melihat data infrastruktur.'), 403);
		}

		global $wpdb;
		$table = 'data_infrastruktur';
		$existing_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
		if ($existing_table !== $table) {
			wp_send_json_error(array('message' => 'Tabel data_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}
		if (!$this->has_custom_attributes_table()) {
			wp_send_json_error(array('message' => 'Tabel data_custom_attributes belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}

		$fields = $this->functions->get_data_infrastruktur_fields();
		$columns = array_merge(array('id'), array_keys($fields), array('geometry'));
		$column_sql = implode(', ', array_map(function($column) {
			return '`' . esc_sql($column) . '`';
		}, $columns));
		$rows = $wpdb->get_results("SELECT {$column_sql} FROM {$table} ORDER BY id ASC", ARRAY_A);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca data infrastruktur: ' . $wpdb->last_error), 500);
		}
		$custom_attributes_by_entity = $this->get_infrastruktur_custom_attributes(array_column($rows, 'id'));
		if ($custom_attributes_by_entity === false) {
			wp_send_json_error(array('message' => 'Gagal membaca atribut custom Infrastruktur: ' . $wpdb->last_error), 500);
		}

		$monev_table = 'data_monev_infrastruktur';
		$existing_monev_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($monev_table)));
		if ($existing_monev_table !== $monev_table) {
			wp_send_json_error(array('message' => 'Tabel data_monev_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}
		$mapping_column = $wpdb->get_var("SHOW COLUMNS FROM {$monev_table} LIKE 'infrastruktur_id'");
		if ($mapping_column === null) {
			if ($wpdb->last_error !== '') {
				wp_send_json_error(array('message' => 'Gagal memeriksa kolom pemetaan Data Infrastruktur: ' . $wpdb->last_error), 500);
			}
			wp_send_json_error(array('message' => 'Kolom pemetaan infrastruktur_id belum tersedia pada data Monev Infrastruktur.'), 500);
		}

		$infrastructure_ids = array_map('intval', array_column($rows, 'id'));
		$infrastructure_id_lookup = array_fill_keys($infrastructure_ids, true);
		$mapped_packages = array();
		$monev_records = $wpdb->get_results(
			"SELECT id, tahun_anggaran, data_paket, infrastruktur_id FROM {$monev_table} WHERE infrastruktur_id IS NOT NULL AND infrastruktur_id > 0 ORDER BY tahun_anggaran DESC, id ASC",
			ARRAY_A
		);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca paket Monev Infrastruktur yang terpetakan: ' . $wpdb->last_error), 500);
		}
		foreach ($monev_records as $monev_record) {
			$infrastructure_id = (int) $monev_record['infrastruktur_id'];
			if (!isset($infrastructure_id_lookup[$infrastructure_id])) {
				continue;
			}
			$package_data = json_decode($monev_record['data_paket'], true);
			if (json_last_error() !== JSON_ERROR_NONE || !is_array($package_data)) {
				wp_send_json_error(array(
					'message' => sprintf('Data JSON paket pekerjaan dengan ID %d tidak valid dan tidak dapat ditampilkan.', (int) $monev_record['id'])
				), 500);
			}
			$mapped_packages[$infrastructure_id][] = array(
				'tahun_anggaran' => (string) $monev_record['tahun_anggaran'],
				'data_paket' => $package_data
			);
		}

		$records = array();
		$years = array();
		$invalid_geometry_count = 0;
		foreach ($rows as $row) {
			$attributes = array();
			foreach ($fields as $column => $field) {
				$attributes[$column] = $row[$column];
			}

			$geometry = json_decode($row['geometry'], true);
			if (
				json_last_error() !== JSON_ERROR_NONE
				|| !is_array($geometry)
				|| empty($geometry['type'])
				|| !in_array($geometry['type'], array('LineString', 'MultiLineString'), true)
				|| empty($geometry['coordinates'])
				|| !is_array($geometry['coordinates'])
			) {
				$geometry = null;
				$invalid_geometry_count++;
			}

			$record_packages = isset($mapped_packages[(int) $row['id']])
				? $mapped_packages[(int) $row['id']]
				: array();
			$record_work_years = array();
			foreach ($record_packages as $package) {
				$work_year = $package['tahun_anggaran'];
				if ($work_year !== '' && !in_array($work_year, $record_work_years, true)) {
					$record_work_years[] = $work_year;
				}
				if ($work_year !== '' && !in_array($work_year, $years, true)) {
					$years[] = $work_year;
				}
			}

			$records[] = array(
				'id' => (int) $row['id'],
				'attributes' => $attributes,
				'work_years' => $record_work_years,
				'work_packages' => $record_packages,
				'custom_attribute' => isset($custom_attributes_by_entity[(int) $row['id']])
					? $custom_attributes_by_entity[(int) $row['id']]
					: array(),
				'geometry' => $geometry
			);
		}

		sort($years, SORT_NATURAL);
		wp_send_json_success(array(
			'fields' => array_map(function($field) {
				return $field['label'];
			}, $fields),
			'years' => $years,
			'records' => $records,
			'invalid_geometry_count' => $invalid_geometry_count
		));
	}

	public function get_data_infrastruktur_public(){
		global $wpdb;
		$table = 'data_infrastruktur';
		$existing_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
		if ($existing_table !== $table) {
			wp_send_json_error(array('message' => 'Tabel data_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}
		if (!$this->has_custom_attributes_table()) {
			wp_send_json_error(array('message' => 'Tabel data_custom_attributes belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}

		$fields = array(
			'nm_ruas' => 'Nama Ruas',
			'wilayah' => 'Wilayah',
			'kecamatan' => 'Kecamatan',
			'desa_kel' => 'Desa / Kelurahan',
			'status' => 'Status',
			'thn_data' => 'Tahun Data'
		);
		$filter_fields = array(
			'kab_kot',
			'propinsi',
			'fungsi',
			'panjang',
			'kon_baik',
			'kon_sdg',
			'kon_rgn',
			'kon_rusak'
		);
		$columns = array_merge(array('id'), array_keys($fields), $filter_fields, array('geometry'));
		$column_sql = implode(', ', array_map(function($column) {
			return '`' . esc_sql($column) . '`';
		}, $columns));
		$rows = $wpdb->get_results("SELECT {$column_sql} FROM {$table} ORDER BY id ASC", ARRAY_A);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca data infrastruktur: ' . $wpdb->last_error), 500);
		}
		$custom_attributes_by_entity = $this->get_infrastruktur_custom_attributes(array_column($rows, 'id'));
		if ($custom_attributes_by_entity === false) {
			wp_send_json_error(array('message' => 'Gagal membaca atribut custom Infrastruktur: ' . $wpdb->last_error), 500);
		}

		$monev_table = 'data_monev_infrastruktur';
		$existing_monev_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($monev_table)));
		if ($existing_monev_table !== $monev_table) {
			wp_send_json_error(array('message' => 'Tabel data_monev_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}
		$mapping_column = $wpdb->get_var("SHOW COLUMNS FROM {$monev_table} LIKE 'infrastruktur_id'");
		if ($mapping_column === null) {
			if ($wpdb->last_error !== '') {
				wp_send_json_error(array('message' => 'Gagal memeriksa kolom pemetaan Data Infrastruktur: ' . $wpdb->last_error), 500);
			}
			wp_send_json_error(array('message' => 'Kolom pemetaan infrastruktur_id belum tersedia pada data Monev Infrastruktur.'), 500);
		}

		$infrastructure_id_lookup = array_fill_keys(array_map('intval', array_column($rows, 'id')), true);
		$mapped_packages = array();
		$monev_records = $wpdb->get_results(
			"SELECT id, tahun_anggaran, data_paket, infrastruktur_id FROM {$monev_table} WHERE infrastruktur_id IS NOT NULL AND infrastruktur_id > 0 ORDER BY tahun_anggaran DESC, id ASC",
			ARRAY_A
		);
		if ($wpdb->last_error !== '') {
			wp_send_json_error(array('message' => 'Gagal membaca paket Monev Infrastruktur yang terpetakan: ' . $wpdb->last_error), 500);
		}
		foreach ($monev_records as $monev_record) {
			$infrastructure_id = (int) $monev_record['infrastruktur_id'];
			if (!isset($infrastructure_id_lookup[$infrastructure_id])) {
				continue;
			}
			$package_data = json_decode($monev_record['data_paket'], true);
			if (json_last_error() !== JSON_ERROR_NONE || !is_array($package_data)) {
				wp_send_json_error(array(
					'message' => sprintf('Data JSON paket pekerjaan dengan ID %d tidak valid dan tidak dapat ditampilkan.', (int) $monev_record['id'])
				), 500);
			}

			$package_name = '';
			foreach (array('nama', 'nama_paket', 'name') as $name_key) {
				if (isset($package_data[$name_key]) && is_scalar($package_data[$name_key])) {
					$package_name = trim((string) $package_data[$name_key]);
					if ($package_name !== '') {
						break;
					}
				}
			}
			$mapped_packages[$infrastructure_id][] = array(
				'tahun_anggaran' => (string) $monev_record['tahun_anggaran'],
				'nama_paket' => $package_name !== '' ? $package_name : 'Paket Pekerjaan'
			);
		}

		$records = array();
		$years = array();
		$invalid_geometry_count = 0;
		foreach ($rows as $row) {
			$attributes = array();
			foreach ($fields as $column => $label) {
				$attributes[$column] = $row[$column];
			}

			$geometry = json_decode($row['geometry'], true);
			if (
				json_last_error() !== JSON_ERROR_NONE
				|| !is_array($geometry)
				|| empty($geometry['type'])
				|| !in_array($geometry['type'], array('LineString', 'MultiLineString'), true)
				|| empty($geometry['coordinates'])
				|| !is_array($geometry['coordinates'])
			) {
				$geometry = null;
				$invalid_geometry_count++;
			}

			$record_packages = isset($mapped_packages[(int) $row['id']])
				? $mapped_packages[(int) $row['id']]
				: array();
			$record_work_years = array();
			foreach ($record_packages as $package) {
				$work_year = $package['tahun_anggaran'];
				if ($work_year !== '' && !in_array($work_year, $record_work_years, true)) {
					$record_work_years[] = $work_year;
				}
				if ($work_year !== '' && !in_array($work_year, $years, true)) {
					$years[] = $work_year;
				}
			}

			$records[] = array(
				'id' => (int) $row['id'],
				'attributes' => $attributes,
				'filter_attributes' => array_intersect_key($row, array_fill_keys($filter_fields, true)),
				'work_years' => $record_work_years,
				'work_packages' => $record_packages,
				'custom_attribute' => isset($custom_attributes_by_entity[(int) $row['id']])
					? $custom_attributes_by_entity[(int) $row['id']]
					: array(),
				'geometry' => $geometry
			);
		}

		sort($years, SORT_NATURAL);
		wp_send_json_success(array(
			'fields' => $fields,
			'years' => $years,
			'records' => $records,
			'invalid_geometry_count' => $invalid_geometry_count
		));
	}

	public function update_custom_data_infrastruktur(){
		check_ajax_referer('satset_infrastruktur_management', 'nonce');
		if (!current_user_can('manage_options')) {
			wp_send_json_error(array('message' => 'Anda tidak memiliki izin untuk mengubah atribut custom data infrastruktur.'), 403);
		}

		$id_value = isset($_POST['id']) && is_scalar($_POST['id'])
			? sanitize_text_field(wp_unslash($_POST['id']))
			: '';
		if (!ctype_digit($id_value) || absint($id_value) < 1) {
			wp_send_json_error(array('message' => 'ID data infrastruktur tidak valid.'), 400);
		}
		if (!isset($_POST['custom_attribute']) || !is_scalar($_POST['custom_attribute'])) {
			wp_send_json_error(array('message' => 'Format atribut custom tidak valid.'), 400);
		}

		$raw_custom_attributes = wp_unslash($_POST['custom_attribute']);
		$custom_attributes = json_decode($raw_custom_attributes);
		if (
			json_last_error() !== JSON_ERROR_NONE
			|| (!is_array($custom_attributes) && !is_object($custom_attributes))
		) {
			wp_send_json_error(array('message' => 'Atribut custom harus berupa JSON yang valid.'), 400);
		}
		if (count($custom_attributes) > 100) {
			wp_send_json_error(array('message' => 'Maksimal 100 atribut custom per ruas.'), 400);
		}

		$is_list = is_array($custom_attributes);
		$clean_attributes = array();
		$seen_names = array();
		foreach ($custom_attributes as $key => $attribute) {
			if ($is_list) {
				if (!is_object($attribute) || !isset($attribute->name) || !isset($attribute->value)) {
					wp_send_json_error(array('message' => 'Setiap atribut custom harus memiliki nama dan nilai.'), 400);
				}
				$name = $attribute->name;
				$type = isset($attribute->type) ? $attribute->type : 'text';
				$value = $attribute->value;
			} else {
				$name = (string) $key;
				$type = 'text';
				$value = $attribute;
			}
			if (!is_scalar($name) || !is_scalar($type) || !is_scalar($value)) {
				wp_send_json_error(array('message' => 'Nama, jenis, dan nilai atribut custom harus berupa teks.'), 400);
			}

			$name = trim(sanitize_text_field($name));
			$name_length = preg_match_all('/./us', $name, $name_characters);
			if ($name === '' || $name_length === false || $name_length > 100) {
				wp_send_json_error(array('message' => 'Nama atribut custom harus berisi 1-100 karakter.'), 400);
			}

			$name_key = function_exists('mb_strtolower') ? mb_strtolower($name, 'UTF-8') : strtolower($name);
			if (isset($seen_names[$name_key])) {
				wp_send_json_error(array('message' => 'Nama atribut custom tidak boleh duplikat.'), 400);
			}
			$seen_names[$name_key] = true;

			$type = sanitize_key($type);
			if (!in_array($type, array('text', 'url', 'image'), true)) {
				wp_send_json_error(array('message' => 'Jenis atribut custom tidak valid.'), 400);
			}

			$value = sanitize_textarea_field((string) $value);
			$value_length = preg_match_all('/./us', $value, $value_characters);
			if ($value_length === false || $value_length > 5000) {
				wp_send_json_error(array('message' => 'Nilai atribut custom maksimal 5.000 karakter.'), 400);
			}
			if ($type === 'url' || $type === 'image') {
				$sanitized_url = esc_url_raw($value, array('http', 'https'));
				if ($value !== '' && $sanitized_url === '') {
					wp_send_json_error(array('message' => 'Nilai jenis URL atau gambar harus berupa URL HTTP/HTTPS yang valid.'), 400);
				}
				$value = $sanitized_url;
			}
			$clean_attributes[$name] = array(
				'type' => $type,
				'value' => $value
			);
		}

		global $wpdb;
		$table = 'data_infrastruktur';
		$existing_table = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
		if ($existing_table !== $table) {
			wp_send_json_error(array('message' => 'Tabel data_infrastruktur belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}
		if (!$this->has_custom_attributes_table()) {
			wp_send_json_error(array('message' => 'Tabel data_custom_attributes belum tersedia. Jalankan SQL Migrate pada menu WP SATSET Options.'), 500);
		}

		$id = absint($id_value);
		$exists = $wpdb->get_var($wpdb->prepare("SELECT id FROM {$table} WHERE id = %d LIMIT 1", $id));
		if (!$exists) {
			wp_send_json_error(array('message' => 'Data infrastruktur tidak ditemukan.'), 404);
		}

		if ($wpdb->query('START TRANSACTION') === false) {
			wp_send_json_error(array('message' => 'Transaksi penyimpanan atribut tidak dapat dimulai: ' . $wpdb->last_error), 500);
		}
		$deleted = $wpdb->delete(
			'data_custom_attributes',
			array('entity_type' => 'infrastruktur', 'entity_id' => $id),
			array('%s', '%d')
		);
		if ($deleted === false) {
			$error_message = $wpdb->last_error;
			$wpdb->query('ROLLBACK');
			wp_send_json_error(array('message' => 'Gagal menghapus atribut lama: ' . $error_message), 500);
		}
		foreach ($clean_attributes as $name => $attribute) {
			$inserted = $wpdb->insert(
				'data_custom_attributes',
				array(
					'entity_type' => 'infrastruktur',
					'entity_id' => $id,
					'attribute_name' => $name,
					'attribute_type' => $attribute['type'],
					'attribute_value' => $attribute['value']
				),
				array('%s', '%d', '%s', '%s', '%s')
			);
			if ($inserted === false) {
				$error_message = $wpdb->last_error;
				$wpdb->query('ROLLBACK');
				wp_send_json_error(array('message' => 'Gagal menyimpan atribut custom: ' . $error_message), 500);
			}
		}
		if ($wpdb->query('COMMIT') === false) {
			$error_message = $wpdb->last_error;
			$wpdb->query('ROLLBACK');
			wp_send_json_error(array('message' => 'Atribut custom gagal disimpan: ' . $error_message), 500);
		}

		wp_send_json_success(array(
			'message' => 'Atribut custom berhasil disimpan.',
			'custom_attribute' => $clean_attributes
		));
	}

	function management_data_tbc_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/tbc/wp-satset-management-data-tbc.php';
	}
	function management_data_aids_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/aids/wp-satset-management-data-aids.php';
	}
	function management_data_malaria_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/malaria/wp-satset-management-data-malaria.php';
	}
	function management_data_rtlh_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/rtlh/wp-satset-management-data-rtlh.php';
	}
	function management_data_batas_desa_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/misc/wp-satset-management-data-batas-desa.php';
	}
	function management_data_batas_kecamatan_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/misc/wp-satset-management-data-batas-kecamatan.php';
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

	function get_p3ke() {
	    global $wpdb;
	    
	    $prov = get_option('_crb_prov_satset');
	    $kab = get_option('_crb_kab_satset');

	    $where = "provinsi='$prov'";
	    if (!empty($kab)) {
	        $where .= " AND kabkot='$kab'";
	    }

	    if (!empty($_GET['tahun_anggaran'])) {
	        $tahun_anggaran = $_GET['tahun_anggaran'];
	    } else {
	        $tahun_anggaran = get_option('_crb_tahun_satset'); 
	    }

	    $data = $wpdb->get_results(
	        $wpdb->prepare("
	            SELECT 
	                * 
	            FROM data_p3ke 
	            WHERE $where
	            AND tahun_anggaran=%d
	            AND active=1
	            ORDER BY provinsi, kabkot, kecamatan
	        ", $tahun_anggaran), 
	        ARRAY_A
	    );

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

	    if (!empty($_GET['tahun_anggaran'])) {
	        $tahun_anggaran = $_GET['tahun_anggaran'];
	    } else {
	        $tahun_anggaran = get_option('_crb_tahun_satset'); 
	    }
		$data = $wpdb->get_results(
	        $wpdb->prepare("
			SELECT 
				* 
			FROM data_stunting 
			WHERE $where
	            AND tahun_anggaran=%d
	            AND active=1
			ORDER BY provinsi, kabkot, kecamatan
		", $tahun_anggaran), 
	        ARRAY_A
	    );
		return $data;
	}

	function get_tbc(){
		global $wpdb;
		$where = " 1=1";
	    if (!empty($_GET['tahun_anggaran'])) {
	        $tahun_anggaran = $_GET['tahun_anggaran'];
	    } else {
	        $tahun_anggaran = get_option('_crb_tahun_satset'); 
	    }
		$data = $wpdb->get_results(
	        $wpdb->prepare("
			SELECT 
				* 
			FROM data_tbc 
			WHERE $where
	            AND tahun_anggaran=%d
	            AND active=1
			ORDER BY provinsi, kabkot, kecamatan
		", $tahun_anggaran), 
	        ARRAY_A
	    );
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

	    if (!empty($_GET['tahun_anggaran'])) {
	        $tahun_anggaran = $_GET['tahun_anggaran'];
	    } else {
	        $tahun_anggaran = get_option('_crb_tahun_satset'); 
	    }

	    $data = $wpdb->get_results(
	        $wpdb->prepare("
	            SELECT 
	                * 
	            FROM data_rtlh 
	            WHERE $where
	            AND tahun_anggaran=%d
	            AND active=1
	            ORDER BY provinsi, kabkot, kecamatan
	        ", $tahun_anggaran), 
	        ARRAY_A
	    );

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

	    if (!empty($_GET['tahun_anggaran'])) {
	        $tahun_anggaran = $_GET['tahun_anggaran'];
	    } else {
	        $tahun_anggaran = get_option('_crb_tahun_satset'); 
	    }
		$data = $wpdb->get_results(
	        $wpdb->prepare("
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
	            AND tahun_anggaran=%d
			GROUP BY provinsi, kabkot, kecamatan, desa, BLT, BLT_BBM, BPNT, PKH, PBI
			ORDER BY provinsi, kabkot, kecamatan
		", $tahun_anggaran), 
	        ARRAY_A
	    );
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
						AND tahun_anggaran=%d
				", '%'.$_POST['nik'].'%', '%'.$_POST['nik'].'%', $_POST['tahun_anggaran']));
				$data_stunting = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_stunting
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%', $_POST['tahun_anggaran']));
				$data_tbc = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_tbc
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%', $_POST['tahun_anggaran']));
				$data_rtlh = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_rtlh
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%', $_POST['tahun_anggaran']));
				$data_dtks = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_dtks_satset
					WHERE nik like %s
						OR nama like %s
				", '%' .$_POST['nik'].'%', '%'.$_POST['nik'].'%', $_POST['tahun_anggaran']));
				$data_batas_desa = $wpdb->get_results($wpdb->prepare("
					SELECT
						*
					FROM data_batas_desa
					WHERE desa like %s
				", '%' .$_POST['irisan'].'%', '%'.$_POST['irisan'].'%'));
				$ret['data']['p3ke'] = $data;
				$ret['data']['stunting'] = $data_stunting;
				$ret['data']['tbc'] = $data_tbc;
				$ret['data']['rtlh'] = $data_rtlh;
				$ret['data']['dtks'] = $data_dtks;
				$ret['data']['desa'] = $data_batas_desa;
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
public function cari_data_filter_satset(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data' => array()
		);
		$debug_queries = array();
		$get_debug_results = static function($query) use ($wpdb, &$debug_queries){
			$debug_queries[] = $query;
			return $wpdb->get_results($query);
		};
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$where = '';
				if(!empty($_POST['kec'])){
					$where .= $wpdb->prepare(" and kecamatan = %s", $_POST['kec']);
				}
				if(!empty($_POST['desa'])){
					$where .= $wpdb->prepare(" and desa = %s", $_POST['desa']);
				}
				if(!empty($_POST['tahun_anggaran'])){
					$where .= $wpdb->prepare(" and tahun_anggaran = %s", $_POST['tahun_anggaran']);
				}
				$where_dtsen = " AND d.active = 1";
				$where_dtsen_members = " AND member.active = 1";
				if(!empty($_POST['kec'])){
					$where_dtsen .= $wpdb->prepare(" AND TRIM(d.kecamatan) = %s", $_POST['kec']);
					$where_dtsen_members .= $wpdb->prepare(" AND TRIM(member.kecamatan) = %s", $_POST['kec']);
				}
				if(!empty($_POST['desa'])){
					$where_dtsen .= $wpdb->prepare(" AND TRIM(d.kelurahan) = %s", $_POST['desa']);
					$where_dtsen_members .= $wpdb->prepare(" AND TRIM(member.kelurahan) = %s", $_POST['desa']);
				}
				$sources = array(
					'p3ke' => array('table' => 'data_p3ke', 'where' => $where),
					'stunting' => array('table' => 'data_stunting', 'where' => $where),
					'tbc' => array('table' => 'data_tbc', 'where' => $where),
					'rtlh' => array('table' => 'data_rtlh', 'where' => $where),
					'dtks' => array('table' => 'data_dtks_satset', 'where' => $where)
				);
				$matching_nik_sources = array();
				if(!empty($_POST['p3ke']) || !empty($_POST['dtsen'])){
					foreach($sources as $source => $filter){
						if(empty($_POST[$source])){
							continue;
						}
						$matching_nik_sources[] = array(
							'query' => "
							SELECT nik
							FROM {$filter['table']}
							WHERE 1=1 {$filter['where']}
							AND nik IS NOT NULL
							AND nik <> ''"
						);
					}
				}
				if(!empty($matching_nik_sources)){
					$matching_nik_conditions = array();
					foreach($matching_nik_sources as $nik_source){
						$matching_nik_conditions[] = "nik IN ({$nik_source['query']})";
					}
					$where .= ' AND ' . implode(' AND ', $matching_nik_conditions);
				}
				$source_data = array(
					'p3ke' => array(),
					'stunting' => array(),
					'tbc' => array(),
					'rtlh' => array(),
					'dtks' => array()
				);
				foreach($sources as $source => $filter){
					if(empty($_POST[$source])){
						continue;
					}
					$source_data[$source] = $get_debug_results("
						SELECT *
						FROM {$filter['table']}
						WHERE 1=1 {$where}");
				}
				$has_matching_nik_sources = !empty($matching_nik_sources);
				$matched_niks = array();
				if($has_matching_nik_sources){
					foreach($source_data as $source_rows){
						foreach((array) $source_rows as $source_row){
							$nik = isset($source_row->nik) ? trim((string) $source_row->nik) : '';
							if($nik !== ''){
								$matched_niks[$nik] = $nik;
							}
						}
					}
					$matched_niks = array_values($matched_niks);
				}
				$data_dtsen = array();
				$data_dtsen_anggota = array();
				if(!empty($_POST['dtsen'])){
					$dtsen_nik = isset($_POST['dtsen_nik'])
						? sanitize_text_field(wp_unslash($_POST['dtsen_nik']))
						: '';
					$where_dtsen_data = $where_dtsen;
					if($dtsen_nik !== ''){
						$nik_like = '%' . $wpdb->esc_like($dtsen_nik) . '%';
						$where_dtsen_data .= $wpdb->prepare(" AND d.nik LIKE %s", $nik_like);
						$where_dtsen_members .= $wpdb->prepare(" AND member.nik LIKE %s", $nik_like);
					}
					if($has_matching_nik_sources){
						if(empty($matched_niks)){
							$where_dtsen_data .= " AND 1=0";
							$where_dtsen_members .= " AND 1=0";
						}else{
							$matched_nik_placeholders = implode(',', array_fill(0, count($matched_niks), '%s'));
							$matched_nik_condition = " IN ({$matched_nik_placeholders})";
							$where_dtsen_data .= $wpdb->prepare(" AND d.nik{$matched_nik_condition}", ...$matched_niks);
							$where_dtsen_members .= $wpdb->prepare(" AND member.nik{$matched_nik_condition}", ...$matched_niks);
						}
					}
					$data_dtsen = $get_debug_results("
						SELECT d.*
						FROM data_dtsen_satset d
						WHERE 1=1 {$where_dtsen_data}");
					if(!empty($wpdb->last_error)){
						$ret['status'] = 'error';
						$ret['message'] = 'Gagal mengambil data DTSEN.';
						$ret['data']['sql'] = $debug_queries;
						die(json_encode($ret));
					}
					$data_dtsen_anggota = $get_debug_results("
						SELECT member.*
						FROM data_dtsen_anggota_keluarga member
						WHERE 1=1 {$where_dtsen_members}
						ORDER BY member.nama ASC");
					if(!empty($wpdb->last_error)){
						$ret['status'] = 'error';
						$ret['message'] = 'Gagal mengambil data anggota keluarga DTSEN.';
						$ret['data']['sql'] = $debug_queries;
						die(json_encode($ret));
					}
				}
				$ret['data']['p3ke'] = $source_data['p3ke'];
				$ret['data']['stunting'] = $source_data['stunting'];
				$ret['data']['tbc'] = $source_data['tbc'];
				$ret['data']['rtlh'] = $source_data['rtlh'];
				$ret['data']['dtks'] = $source_data['dtks'];
				$ret['data']['dtsen'] = $data_dtsen;
				$ret['data']['dtsen_anggota'] = $data_dtsen_anggota;
				$ret['data']['sql'] = $debug_queries;
			}else{
				$ret['status']	= 'error';
				$ret['message']	= 'Api key tidak ditemukan!';
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
				if(empty($_POST['id_p3ke'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data id p3ke tidak boleh kosong!';
				} else if(empty($_POST['kode_kemendagri'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data kode kemendagri tidak boleh kosong!';
				} else if(empty($_POST['nik'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data nik tidak boleh kosong!';
				} else if(empty($_POST['padan_dukcapil'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data padan dukcapil tidak boleh kosong!';
				} else if(empty($_POST['kepala_keluarga'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data kepala keluarga tidak boleh kosong!';
				} else if(empty($_POST['jenis_kelamin'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis kelamin tidak boleh kosong!';
				} else if(empty($_POST['tanggal_lahir'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data tanggal lahir tidak boleh kosong!';
				} else if(empty($_POST['provinsi'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data provinsi tidak boleh kosong!';
				} else if(empty($_POST['kabkot'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data kabkot tidak boleh kosong!';
				} else if(empty($_POST['kecamatan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecamatan tidak boleh kosong!';
				} else if(empty($_POST['desa'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data desa tidak boleh kosong!';
				} else if(empty($_POST['alamat'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data alamat tidak boleh kosong!';
				} else if(empty($_POST['pekerjaan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data pekerjaan tidak boleh kosong!';
				} else if(empty($_POST['pendidikan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data pendidikan tidak boleh kosong!';
				} else if(empty($_POST['rumah'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data rumah tidak boleh kosong!';
				} else if(empty($_POST['punya_tabungan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data punya tabungan tidak boleh kosong!';
				} else if(empty($_POST['jenis_desil'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis desil tidak boleh kosong!';
				} else if(empty($_POST['jenis_atap'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis atap tidak boleh kosong!';
				} else if(empty($_POST['jenis_dinding'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis dinding tidak boleh kosong!';
				} else if(empty($_POST['jenis_lantai'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis lantai tidak boleh kosong!';
				} else if(empty($_POST['sumber_penerangan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data sumber penerangan tidak boleh kosong!';
				} else if(empty($_POST['bahan_bakar_memasak'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data bahan bakar memasak tidak boleh kosong!';
				} else if(empty($_POST['sumber_air_minum'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data sumber air minum tidak boleh kosong!';
				} else if(empty($_POST['fasilitas_bab'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data fasilitas bab tidak boleh kosong!';
				} else if(empty($_POST['penerima_bpnt'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima bpnt tidak boleh kosong!';
				} else if(empty($_POST['penerima_bpum'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima bpum tidak boleh kosong!';
				} else if(empty($_POST['penerima_bst'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima bst tidak boleh kosong!';
				} else if(empty($_POST['penerima_pkh'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima pkh tidak boleh kosong!';
				} else if(empty($_POST['penerima_sembako'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima sembako tidak boleh kosong!';
				} else if(empty($_POST['resiko_stunting'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data Resiko Stunting tidak boleh kosong!';
				} else if(empty($_POST['tahun_anggaran'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Tahun Anggaran tidak boleh kosong!';
				} else{
					$id_p3ke = $_POST['id_p3ke'];
					$kode_kemendagri = $_POST['kode_kemendagri'];
					$nik = $_POST['nik'];
					$padan_dukcapil = $_POST['padan_dukcapil'];
					$kepala_keluarga = $_POST['kepala_keluarga'];
					$jenis_kelamin = $_POST['jenis_kelamin'];
					$tanggal_lahir = $_POST['tanggal_lahir'];
					$provinsi = $_POST['provinsi'];
					$kabkot = $_POST['kabkot'];
					$kecamatan = $_POST['kecamatan'];
					$desa = $_POST['desa'];
					$alamat = $_POST['alamat'];
					$pekerjaan = $_POST['pekerjaan'];
					$pendidikan = $_POST['pendidikan'];
					$rumah = $_POST['rumah'];
					$punya_tabungan = $_POST['punya_tabungan'];
					$jenis_desil = $_POST['jenis_desil'];
					$jenis_atap = $_POST['jenis_atap'];
					$jenis_dinding = $_POST['jenis_dinding'];
					$jenis_lantai = $_POST['jenis_lantai'];
					$sumber_penerangan = $_POST['sumber_penerangan'];
					$bahan_bakar_memasak = $_POST['bahan_bakar_memasak'];
					$sumber_air_minum = $_POST['sumber_air_minum'];
					$fasilitas_bab = $_POST['fasilitas_bab'];
					$penerima_bpnt = $_POST['penerima_bpnt'];
					$penerima_bpum = $_POST['penerima_bpum'];
					$penerima_bst = $_POST['penerima_bst'];
					$penerima_pkh = $_POST['penerima_pkh'];
					$penerima_sembako = $_POST['penerima_sembako'];
					$resiko_stunting = $_POST['resiko_stunting'];
					$tahun_anggaran = $_POST['tahun_anggaran'];
					$data = array(
						'id_p3ke' => $id_p3ke,
			            'kode_kemendagri' => $kode_kemendagri,
			            'nik' => $nik,
			            'padan_dukcapil' => $padan_dukcapil,
			            'kepala_keluarga' => $kepala_keluarga,
			            'jenis_kelamin' => $jenis_kelamin,
			            'tanggal_lahir' => $tanggal_lahir,
			            'provinsi' => $provinsi,
			            'kabkot' => $kabkot,
			            'kecamatan' => $kecamatan,
			            'desa' => $desa,
			            'alamat' => $alamat,
			            'pekerjaan' => $pekerjaan,
			            'pendidikan' => $pendidikan,
			            'rumah' => $rumah,
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
			            'tahun_anggaran' => $tahun_anggaran,
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
								$ret['message'] = 'Gagal disimpan. Data p3ke dengan id_p3ke="'.$id_p3ke.'" sudah ada!';
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
	 					0	=> 'id_p3ke',
			            1	=> 'kode_kemendagri',
			            2	=> 'nik',
			            3	=> 'padan_dukcapil',
			            4	=> 'kepala_keluarga',
			            5	=> 'jenis_kelamin',
			            6	=> 'tanggal_lahir',
			            7	=> 'provinsi',
			            8	=> 'kabkot',
			            9	=> 'kecamatan',
			            10	=> 'desa',
			            11	=> 'alamat',
			            12	=> 'pekerjaan',
			            13	=> 'pendidikan',
			            14	=> 'rumah',
			            15	=> 'punya_tabungan',
			            16	=> 'jenis_desil',
			            17	=> 'jenis_atap',
			            18	=> 'jenis_dinding',
			            19	=> 'jenis_lantai',
			            20	=> 'sumber_penerangan',
			            21	=> 'bahan_bakar_memasak',
			            22	=> 'sumber_air_minum',
			            23	=> 'fasilitas_bab',
			            24	=> 'penerima_bpnt',
			            25	=> 'penerima_bpum',
			            26	=> 'penerima_bst',
			            27	=> 'penerima_pkh',
			            28	=> 'penerima_sembako',
			            29	=> 'resiko_stunting',
			            30	=> 'tahun_anggaran',
				 		31  => 'id'	 
	 			);
	 			$where = $sqlTot = $sqlRec = "";

	            if (empty($params['tahun_anggaran'])) {
	                die(json_encode(array(
	                    'status' => 'error',
	                    'message' => 'Parameter tahun anggaran diperlukan!'
	                )));
	            }

	            $tahun_anggaran = $params['tahun_anggaran'];

	            if (!empty($params['search']['value'])) {
	                $where .= " AND (nik LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%");
	                $where .= " OR alamat LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%");
	                $where .= ")";
	            }

	            $where .= " AND tahun_anggaran = " . $wpdb->prepare('%s', $tahun_anggaran);

	            $sql_tot = "SELECT count(id) as jml FROM `data_p3ke`";
	            $sql = "SELECT " . implode(', ', $columns) . " FROM `data_p3ke`";
	            $where_first = " WHERE 1=1 AND active = 1";
	            $sqlTot .= $sql_tot . $where_first;
	            $sqlRec .= $sql . $where_first;

	            if (isset($where) && $where != '') {
	                $sqlTot .= $where;
	                $sqlRec .= $where;
	            }
	            // print_r($sqlRec); die($wpdb->last_query);
	            $limit = '';
	            if ($params['length'] != -1) {
	                $limit = " LIMIT " . $wpdb->prepare('%d', $params['start']) . " ," . $wpdb->prepare('%d', $params['length']);
	            }
	            $sqlRec .= " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'] . $limit;

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
            if(empty($_POST['nik'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data nik tidak boleh kosong!';
            } else if(empty($_POST['nama'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data nama tidak boleh kosong!';
            } else if(empty($_POST['jenis_kelamin'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data jenis_kelamin tidak boleh kosong!';
            } else if(empty($_POST['tanggal_lahir'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data tanggal_lahir tidak boleh kosong!';
            } else if(empty($_POST['bb_lahir'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data bb_lahir tidak boleh kosong!';
            } else if(empty($_POST['tb_lahir'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data tb_lahir tidak boleh kosong!';
            } else if(empty($_POST['nama_ortu'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data nama_ortu tidak boleh kosong!';
            } else if(empty($_POST['provinsi'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data provinsi tidak boleh kosong!';
            } else if(empty($_POST['kabkot'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data kabkot tidak boleh kosong!';
            } else if(empty($_POST['kecamatan'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data kecamatan tidak boleh kosong!';
            } else if(empty($_POST['desa'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data desa tidak boleh kosong!';
            } else if(empty($_POST['puskesmas'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data puskesmas tidak boleh kosong!';
            } else if(empty($_POST['posyandu'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data posyandu tidak boleh kosong!';
            } else if(empty($_POST['rt'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data rt  tidak boleh kosong!';
            } else if(empty($_POST['rw'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data rw  tidak boleh kosong!';
            } else if(empty($_POST['alamat'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data alamat  tidak boleh kosong!';
            } else if(empty($_POST['usia_saat_ukur'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data usia_saat_ukur  tidak boleh kosong!';
            } else if(empty($_POST['tanggal_pengukuran'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data tanggal_pengukuran  tidak boleh kosong!';
            } else if(empty($_POST['berat'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data berat  tidak boleh kosong!';
            } else if(empty($_POST['tinggi'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data tinggi  tidak boleh kosong!';
            } else if(empty($_POST['lingkar_lengan_atas'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data lingkar_lengan_atas  tidak boleh kosong!';
            } else if(empty($_POST['bb_per_u'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data bb_per_u  tidak boleh kosong!';
            } else if(empty($_POST['zs_bb_per_u'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data zs_bb_per_u  tidak boleh kosong!';
            } else if(empty($_POST['tb_per_u'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data tb_per_u  tidak boleh kosong!';  // Added validation
            } else if(empty($_POST['bb_per_tb'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data bb_per_tb  tidak boleh kosong!';
            } else if(empty($_POST['zs_bb_per_tb'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data zs_bb_per_tb  tidak boleh kosong!';
            } else if(empty($_POST['naik_berat_badan'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data naik_berat_badan  tidak boleh kosong!';
            } else if(empty($_POST['pmt_diterima_per_kg'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data pmt_diterima_per_kg  tidak boleh kosong!';
            } else if(empty($_POST['jml_vit_a'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data jml_vit_a  tidak boleh kosong!';
            } else if(empty($_POST['kpsp'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data kpsp  tidak boleh kosong!';
            } else if(empty($_POST['kia'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data kia  tidak boleh kosong!';
            } else if(empty($_POST['tahun_anggaran'])){
                $ret['status'] = 'error';
                $ret['message'] = 'Data tahun_anggaran  tidak boleh kosong!';
            } else {
                $nik = $_POST['nik'];
                $nama = $_POST['nama'];
                $jenis_kelamin = $_POST['jenis_kelamin'];
                $tanggal_lahir = $_POST['tanggal_lahir'];
                $bb_lahir = $_POST['bb_lahir'];
                $tb_lahir = $_POST['tb_lahir'];
                $nama_ortu = $_POST['nama_ortu'];
                $provinsi = $_POST['provinsi'];
                $kabkot = $_POST['kabkot'];
                $kecamatan = $_POST['kecamatan'];
                $desa = $_POST['desa'];
                $puskesmas = $_POST['puskesmas'];
                $posyandu = $_POST['posyandu'];
                $rt = $_POST['rt'];
                $rw = $_POST['rw'];
                $alamat = $_POST['alamat'];
                $usia_saat_ukur = $_POST['usia_saat_ukur'];
                $tanggal_pengukuran = $_POST['tanggal_pengukuran'];
                $berat = $_POST['berat'];
                $tinggi = $_POST['tinggi'];
                $lingkar_lengan_atas = $_POST['lingkar_lengan_atas'];
                $bb_per_u = $_POST['bb_per_u'];
                $zs_bb_per_u = $_POST['zs_bb_per_u'];
                $tb_per_u = $_POST['tb_per_u'];  // Added tb_per_u to data array
                $bb_per_tb = $_POST['bb_per_tb'];
                $zs_bb_per_tb = $_POST['zs_bb_per_tb'];
                $naik_berat_badan = $_POST['naik_berat_badan'];
                $pmt_diterima_per_kg = $_POST['pmt_diterima_per_kg'];
                $jml_vit_a = $_POST['jml_vit_a'];
                $kpsp = $_POST['kpsp'];
                $kia = $_POST['kia'];
                $tahun_anggaran = $_POST['tahun_anggaran'];
                $data = array(
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
                    'desa' => $desa,
                    'puskesmas' => $puskesmas,
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
                    'tahun_anggaran' => $tahun_anggaran,
                    'active' => 1,
                    'update_at' => current_time('mysql')
                );
                if(!empty($_POST['id_data'])){
                    $wpdb->update('data_stunting', $data, array(
                        'id' => $_POST['id_data']
                    ));
                    $ret['message'] = 'Berhasil update data!';
                } else {
                    $cek_id = $wpdb->get_row($wpdb->prepare(
                        '
                        SELECT 
                            *
                        FROM data_stunting 
                        WHERE active = 1 
                            AND nik = %s
                            AND tahun_anggaran = %d
                    ', $nik, $tahun_anggaran
                    ), ARRAY_A);
                    if(empty($cek_id)){
                        $wpdb->insert('data_stunting', $data);
                    } else {
                        if($cek_id['active'] == 0){
                            $wpdb->update('data_stunting', $data, array(
                                'id' => $cek_id['id']
                            ));
                        }
                    }
                }
            }
        } else {
            $ret['status'] = 'error';
            $ret['message'] = 'Api key tidak ditemukan!';
        }
    } else {
        $ret['status'] = 'error';
        $ret['message'] = 'Format Salah!';
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
				 32	=> 'tahun_anggaran',
				 33 => 'id'	 			
				);
	 			$where = $sqlTot = $sqlRec = "";

	            if (empty($params['tahun_anggaran'])) {
	                die(json_encode(array(
	                    'status' => 'error',
	                    'message' => 'Parameter tahun anggaran diperlukan!'
	                )));
	            }

	            $tahun_anggaran = $params['tahun_anggaran'];
	            $where .= " AND tahun_anggaran = " . $wpdb->prepare('%s', $tahun_anggaran);

	 			// check search value exist
	 			if( !empty($params['search']['value']) ) {  
	 				$where .=" AND nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR nama LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR alamat LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 			}

	 			// getting total number records without any search
	 			$sql_tot = "SELECT count(id) as jml FROM `data_stunting`";
	 			$sql = "SELECT ".implode(', ', $columns)." FROM `data_stunting`";
	 			$where_first = " WHERE 1=1 AND active = 1";
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
				if(empty($_POST['no_reg_kabkot'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data no_reg_kabkot tidak boleh kosong!';
				} else if(empty($_POST['nik'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data nik tidak boleh kosong!';
				} else if(empty($_POST['nama'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data nama tidak boleh kosong!';
				} else if(empty($_POST['tahun_anggaran'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data tahun_anggaran tidak boleh kosong!';
				} else {
				    $keterangan = !empty($_POST['keterangan']) ? $_POST['keterangan'] : ''; // Tambahkan default value
				    $tanggal_register = $_POST['tanggal_register'];
				    $no_reg_fasyankes = $_POST['no_reg_fasyankes'];
				    $no_reg_kabkot = $_POST['no_reg_kabkot'];
				    $nik = $_POST['nik'];
				    $nama = $_POST['nama'];
				    $umur = $_POST['umur'];
				    $jenis_kelamin = $_POST['jenis_kelamin'];
				    $alamat = $_POST['alamat'];
				    $pindahan_dari_fasyankes = $_POST['pindahan_dari_fasyankes'];
				    $tindak_lanjut = $_POST['tindak_lanjut'];
				    $tanggal_mulai_pengobatan = $_POST['tanggal_mulai_pengobatan'];
				    $hasil_akhir_pengobatan = $_POST['hasil_akhir_pengobatan'];
				    $status_pengobatan = $_POST['status_pengobatan'];
				    $tahun_anggaran = $_POST['tahun_anggaran'];

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
				        'tahun_anggaran' => $tahun_anggaran,
				        'keterangan' => $keterangan,
				        'kdwil' => $no_reg_kabkot,
				        'active' => 1,
				        'update_at' => current_time('mysql') // Pastikan waktu sesuai kebutuhan
				    );

				    if (!empty($_POST['id_data'])) {
				        $wpdb->update('data_tbc', $data, array(
				            'id' => $_POST['id_data']
				        ));
				        $ret['message'] = 'Berhasil update data!';
				    } else {
				        // Perbaikan penggunaan prepare
				        $cek_id = $wpdb->get_row($wpdb->prepare('
				            SELECT
				                id,
				                active
				            FROM data_tbc
				            WHERE nik = %s
				              AND tahun_anggaran = %d
				        ', $nik, $tahun_anggaran), ARRAY_A);

				        if (empty($cek_id)) {
				            $wpdb->insert('data_tbc', $data);
				        } else {
				            if ($cek_id['active'] == 0) {
				                $wpdb->update('data_tbc', $data, array(
				                    'id' => $cek_id['id']
				                ));
				            } else {
				                $ret['status'] = 'error';
				                $ret['message'] = 'Gagal disimpan. Data tbc dengan nik="' . $nik . '" sudah ada!';
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
	 			  0 => 'tanggal_register',
	 			  1 => 'no_reg_fasyankes',
	 			  2 => 'no_reg_kabkot',
	 			  3 => 'nik',
	 			  4 => 'nama',
	 			  5 => 'umur',
	 			  6 => 'jenis_kelamin',
	 			  7 => 'alamat',
	 			  8 => 'pindahan_dari_fasyankes',
	 			  9 => 'tindak_lanjut',
	 			  10 => 'tanggal_mulai_pengobatan',
	 			  11 => 'hasil_akhir_pengobatan',
	 			  12 => 'status_pengobatan',
	 			  13 => 'keterangan',
	 			  14 => 'tahun_anggaran',
	 			  15 => 'id'
	 			);
	 			$where = $sqlTot = $sqlRec = "";

	            if (empty($params['tahun_anggaran'])) {
	                die(json_encode(array(
	                    'status' => 'error',
	                    'message' => 'Parameter tahun anggaran diperlukan!'
	                )));
	            }

	            $tahun_anggaran = $params['tahun_anggaran'];

	            $where .= " AND tahun_anggaran = " . $wpdb->prepare('%s', $tahun_anggaran);

	 			// check search value exist
	 			if( !empty($params['search']['value']) ) {
	 				$where .=" AND ( id_tbc LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");    
	 				$where .=" OR nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR nama LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR alamat LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 			}

	 			// getting total number records without any search
	 			$sql_tot = "SELECT count(id) as jml FROM `data_tbc`";
	 			$sql = "SELECT ".implode(', ', $columns)." FROM `data_tbc`";
	 			$where_first = " WHERE 1=1 AND active = 1";
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

public function tambah_data_rtlh() {
    global $wpdb;

    $ret = array(
        'status' => 'success',
        'message' => 'Berhasil simpan data!',
        'data' => array()
    );

    if (!empty($_POST)) {
        if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option(SATSET_APIKEY)) {
            if (empty($_POST['nik'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data nik tidak boleh kosong!';
            } else if (empty($_POST['nama'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data nama tidak boleh kosong!';
            } else if (empty($_POST['alamat'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data alamat tidak boleh kosong!';
            } else if (empty($_POST['provinsi'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data provinsi tidak boleh kosong!';
            } else if (empty($_POST['kabkot'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data kabkot tidak boleh kosong!';
            } else if (empty($_POST['kecamatan'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data kecamatan tidak boleh kosong!';
            } else if (empty($_POST['desa'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data desa tidak boleh kosong!';
            } else if (empty($_POST['rt'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data rt tidak boleh kosong!';
            } else if (empty($_POST['rw'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data rw tidak boleh kosong!';
            } else if (empty($_POST['nilai_bantuan'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data nilai_bantuan tidak boleh kosong!';
            } else if (empty($_POST['lpj'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data lpj tidak boleh kosong!';
            } else if (empty($_POST['tgl_lpj'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data tgl_lpj tidak boleh kosong!';
            } else if (empty($_POST['sumber_dana'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data sumber_dana tidak boleh kosong!';
            } else if (empty($_POST['tahun_anggaran'])) {
                $ret['status'] = 'error';
                $ret['message'] = 'Data tahun_anggaran tidak boleh kosong!';
            } else {
                $data = array(
                    'nik'            => $_POST['nik'],
                    'nama'           => $_POST['nama'],
                    'alamat'         => $_POST['alamat'],
                    'provinsi'       => $_POST['provinsi'],
                    'kabkot'         => $_POST['kabkot'],
                    'kecamatan'      => $_POST['kecamatan'],
                    'desa'           => $_POST['desa'],
                    'rt'             => $_POST['rt'],
                    'rw'             => $_POST['rw'],
                    'nilai_bantuan'  => $_POST['nilai_bantuan'],
                    'lpj'            => $_POST['lpj'],
                    'tgl_lpj'        => $_POST['tgl_lpj'],
                    'sumber_dana'    => $_POST['sumber_dana'],
                    'tahun_anggaran' => $_POST['tahun_anggaran'],
                    'active'         => 1,
                    'update_at'      => current_time('mysql')
                );

                if (!empty($_POST['id_data'])) {
                    $wpdb->update('data_rtlh', $data, array(
                        'id' => $_POST['id_data']
                    ));
                    $ret['message'] = 'Berhasil update data!';
                } else {
                    $cek_id = $wpdb->get_row($wpdb->prepare(
                        '
                        SELECT 
                        	*
                        FROM data_rtlh 
                        WHERE active = 1 
                        	AND nik = %s
                    ', $_POST['nik']
                    ), ARRAY_A);

                    if (empty($cek_id)) {
                        $wpdb->insert('data_rtlh', $data);
                    } else {
                        if ($cek_id['active'] == 0) {
                            $wpdb->update('data_rtlh', $data, array(
                                'id' => $cek_id['id']
                            ));
                        }
                    }
                }
            }
        } else {
            $ret['status'] = 'error';
            $ret['message'] = 'Api key tidak ditemukan!';
        }
    } else {
        $ret['status'] = 'error';
        $ret['message'] = 'Format Salah!';
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
	 			  13 => 'tahun_anggaran',
	 			  14 => 'id'
	 			);
	 			$where = $sqlTot = $sqlRec = "";

	            if (empty($params['tahun_anggaran'])) {
	                die(json_encode(array(
	                    'status' => 'error',
	                    'message' => 'Parameter tahun anggaran diperlukan!'
	                )));
	            }

	            $tahun_anggaran = $params['tahun_anggaran'];

	            if (!empty($params['search']['value'])) {
	                $where .= " AND (nik LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%");
	                $where .= " OR alamat LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%");
	                $where .= ")";
	            }

	            $where .= " AND tahun_anggaran = " . $wpdb->prepare('%s', $tahun_anggaran);
	 			// check search value exist
	 			if( !empty($params['search']['value']) ) {
	 				$where .=" AND ( id_rtlh LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");    
	 				$where .=" OR nik LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR nama LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 				$where .=" OR alamat LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 			}

	 			// getting total number records without any search
	 			$sql_tot = "SELECT count(id) as jml FROM `data_rtlh`";
	 			$sql = "SELECT ".implode(', ', $columns)." FROM `data_rtlh`";
	 			$where_first = " WHERE 1=1 AND active = 1";
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

public function get_data_batas_desa_by_id(){
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
					FROM data_batas_desa
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

public function hapus_data_batas_desa_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil hapus data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_batas_desa', array('active' => 0), array(
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

public function tambah_data_batas_desa(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if($ret['status'] != 'error' && !empty($_POST['id_desa'])){
					$id_desa = $_POST['id_desa'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data id_desa tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['desa'])){
					$desa = $_POST['desa'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data desa tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kecamatan'])){
					$kecamatan = $_POST['kecamatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecamatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kab_kot'])){
					$kab_kot = $_POST['kab_kot'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kab_kot tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['provinsi'])){
					$provinsi = $_POST['provinsi'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data provinsi tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['area'])){
					$area = $_POST['area'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data area tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['perimeter'])){
					$perimeter = $_POST['perimeter'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data perimeter tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['hectares'])){
					$hectares = $_POST['hectares'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data hectares tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['ukuran_kot'])){
					$ukuran_kot = $_POST['ukuran_kot'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data ukuran_kot tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['pemusatan'])){
					$pemusatan = $_POST['pemusatan'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data pemusatan tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['jumplah_pen'])){
					$jumplah_pen = $_POST['jumplah_pen'];
				// }else{
				// 	$ret['status'] = 'error';
				// 	$ret['message'] = 'Data jumplah_pen tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['provno'])){
					$provno = $_POST['provno'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data provno tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kabkotno'])){
					$kabkotno = $_POST['kabkotno'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kabkotno tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kecno'])){
					$kecno = $_POST['kecno'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecno tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['desano'])){
					$desano = $_POST['desano'];
				// }else{
				// 	$ret['status'] = 'error';
				// 	$ret['message'] = 'Data desano tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['id2012'])){
					$id2012 = $_POST['id2012'];
				// }else{
				// 	$ret['status'] = 'error';
				// 	$ret['message'] = 'Data id2012 tidak boleh kosong!';
				}
				if($ret['status'] != 'error'){
					$data = array(
						'id_desa' => $id_desa,
						'desa' => $desa,
						'kecamatan' => $kecamatan,
						'kab_kot' => $kab_kot,
						'provinsi' => $provinsi,
						'area' => $area,
						'perimeter' => $perimeter,
						'hectares' => $hectares,
						'ukuran_kot' => $ukuran_kot,
						'pemusatan' => $pemusatan,
						'jumplah_pen' => $jumplah_pen,
						'provno' => $provno,
						'kabkotno' => $kabkotno,
						'kecno' => $kecno,
						'desano' => $desano,
						'id2012' => $id2012,
						'active' => 1,
						'update_at' => current_time('mysql')
					);
					if(!empty($_POST['id_data'])){
						$wpdb->update('data_batas_desa', $data, array(
							'id' => $_POST['id_data']
						));
						$ret['message'] = 'Berhasil update data!';
					}else{
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT
								id,
								active
							FROM data_batas_desa
							WHERE id_batas_desa=%s
						', $id_batas_desa), ARRAY_A);
						if(empty($cek_id)){
							$wpdb->insert('data_batas_desa', $data);
						}else{
							if($cek_id['active'] == 0){
								$wpdb->update('data_batas_desa', $data, array(
									'id' => $cek_id['id']
								));
							}else{
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data batas_desa dengan id_batas_desa="'.$id_batas_desa.'" sudah ada!';
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
	
public function get_datatable_batas_desa(){
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
	 			  0 => 'id_desa',
	 			  1 => 'desa',
	 			  2 => 'kecamatan',
	 			  3 => 'kab_kot',
	 			  4 => 'provinsi',
	 			  5 => 'area',
	 			  6 => 'perimeter',
	 			  7 => 'hectares',
	 			  8 => 'ukuran_kot',
	 			  9 => 'pemusatan',
	 			  10 => 'jumplah_pen',
	 			  11 => 'provno',
	 			  12 => 'kabkotno',
	 			  13 => 'kecno',
	 			  14 => 'desano',
	 			  15 => 'id2012',
	 			  16 => 'id'
	 			);
	 			$where = $sqlTot = $sqlRec = "";

	 			// check search value exist
	 			if( !empty($params['search']['value']) ) {
	 				$where .=" AND ( id_batas_desa LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");  
	 				$where .=" OR perimeter LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 			}

	 			// getting total number records without any search
	 			$sql_tot = "SELECT count(id) as jml FROM `data_batas_desa`";
	 			$sql = "SELECT ".implode(', ', $columns)." FROM `data_batas_desa`";
	 			$where_first = " WHERE 1=1 AND active = 1";
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
public function get_data_batas_kecamatan_by_id(){
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
					FROM data_batas_kecamatan
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

public function hapus_data_batas_kecamatan_by_id(){
	global $wpdb;
	$ret = array(
		'status' => 'success',
		'message' => 'Berhasil hapus data!',
		'data' => array()
	);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_batas_kecamatan', array('active' => 0), array(
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

public function tambah_data_batas_kecamatan(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if($ret['status'] != 'error' && !empty($_POST['provno'])){
					$provno = $_POST['provno'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data provno tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kabkotno'])){
					$kabkotno = $_POST['kabkotno'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kabkotno tidak boleh kosong!';
				}
				if($ret['status'] != 'error' && !empty($_POST['kecno'])){
					$kecno = $_POST['kecno'];
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecno tidak boleh kosong!';
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
				if($ret['status'] != 'error'){
					$data = array(
						'provno' => $provno,
						'kabkotno' => $kabkotno,
						'kecno' => $kecno,
						'provinsi' => $provinsi,
						'kabkot' => $kabkot,
						'kecamatan' => $kecamatan,
						'active' => 1,
						'update_at' => current_time('mysql')
					);
					if(!empty($_POST['id_data'])){
						$wpdb->update('data_batas_kecamatan', $data, array(
							'id' => $_POST['id_data']
						));
						$ret['message'] = 'Berhasil update data!';
					}else{
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT
								id,
								active
							FROM data_batas_kecamatan
							WHERE id_batas_kecamatan=%s
						', $id_batas_kecamatan), ARRAY_A);
						if(empty($cek_id)){
							$wpdb->insert('data_batas_kecamatan', $data);
						}else{
							if($cek_id['active'] == 0){
								$wpdb->update('data_batas_kecamatan', $data, array(
									'id' => $cek_id['id']
								));
							}else{
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data batas_kecamatan dengan id_batas_kecamatan="'.$id_batas_kecamatan.'" sudah ada!';
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
	
public function get_datatable_batas_kecamatan(){
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
	 			 0 => 'provno',
	 			 1 => 'kabkotno',
	 			 2 => 'kecno',
	 			 3 => 'provinsi',
	 			 4 => 'kabkot',
	 			 5 => 'kecamatan',
	 			 6 => 'id'
	 			);
	 			$where = $sqlTot = $sqlRec = "";

	 			// check search value exist
	 			if( !empty($params['search']['value']) ) {
	 				$where .=" AND ( id_batas_kecamatan LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");  
	 				$where .=" OR perimeter LIKE ".$wpdb->prepare('%s', "%".$params['search']['value']."%");
	 			}

	 			// getting total number records without any search
	 			$sql_tot = "SELECT count(id) as jml FROM `data_batas_kecamatan`";
	 			$sql = "SELECT ".implode(', ', $columns)." FROM `data_batas_kecamatan`";
	 			$where_first = " WHERE 1=1 AND active = 1";
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
	public function get_data_p3ke_anggota_keluarga_by_id(){
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
					FROM data_p3ke_anggota_keluarga
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

	public function hapus_data_p3ke_anggota_keluarga_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil hapus data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_p3ke_anggota_keluarga', array('active' => 0), array(
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

	public function tambah_data_p3ke_anggota_keluarga(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if(empty($_POST['id_p3ke'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data ID P3KE tidak boleh kosong!';
				} else if(empty($_POST['kode_kemendagri'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data Kode Kemendagri tidak boleh kosong!';
				} else if(empty($_POST['nik'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data NIK tidak boleh kosong!';
				} else if(empty($_POST['padan_dukcapil'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data Padan Dukcapil tidak boleh kosong!';
				} else if(empty($_POST['hubungan_keluarga'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data Hubungan Keluarga tidak boleh kosong!';
				} else if(empty($_POST['jenis_kelamin'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_kelamin tidak boleh kosong!';
				} else if(empty($_POST['tanggal_lahir'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data tanggal_lahir tidak boleh kosong!';
				} else if(empty($_POST['provinsi'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data provinsi tidak boleh kosong!';
				} else if(empty($_POST['kabkot'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data kabkot tidak boleh kosong!';
				} else if(empty($_POST['kecamatan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data kecamatan tidak boleh kosong!';
				} else if(empty($_POST['desa'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data desa tidak boleh kosong!';
				} else if(empty($_POST['alamat'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data alamat tidak boleh kosong!';
				} else if(empty($_POST['pekerjaan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data pekerjaan tidak boleh kosong!';
				} else if(empty($_POST['pendidikan'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data pendidikan tidak boleh kosong!';
				} else if(empty($_POST['id_individu'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data id_individu tidak boleh kosong!';
				} else if(empty($_POST['nama'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data nama tidak boleh kosong!';
				} else if(empty($_POST['jenis_desil'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data jenis_desil tidak boleh kosong!';
				} else if(empty($_POST['status_kawin'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data status_kawin tidak boleh kosong!';
				} else if(empty($_POST['usia_dibawah_7'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data usia_dibawah_7 tidak boleh kosong!';
				} else if(empty($_POST['usia_7_12'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data usia_7_12 tidak boleh kosong!';
				} else if(empty($_POST['usia_13_15'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data usia_13_15 tidak boleh kosong!';
				} else if(empty($_POST['usia_16_18'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data usia_16_18 tidak boleh kosong!';
				} else if(empty($_POST['usia_19_21'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data usia_19_21 tidak boleh kosong!';
				} else if(empty($_POST['usia_22_59'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data usia_22_59 tidak boleh kosong!';
				} else if(empty($_POST['penerima_bpnt'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_bpnt tidak boleh kosong!';
				} else if(empty($_POST['penerima_bpum'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_bpum tidak boleh kosong!';
				} else if(empty($_POST['penerima_bst'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_bst tidak boleh kosong!';
				} else if(empty($_POST['penerima_pkh'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_pkh tidak boleh kosong!';
				} else if(empty($_POST['penerima_sembako'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data penerima_sembako tidak boleh kosong!';
				} else if(empty($_POST['resiko_stunting'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data Resiko Stunting tidak boleh kosong!';
				}else if(empty($_POST['tahun_anggaran'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data tahun anggaran tidak boleh kosong!';
				} else{
					$id_p3ke = $_POST['id_p3ke'];				
					$kode_kemendagri = $_POST['kode_kemendagri'];								
					$nik = $_POST['nik'];								
					$padan_dukcapil = $_POST['padan_dukcapil'];								
					$hubungan_keluarga = $_POST['hubungan_keluarga'];								
					$jenis_kelamin = $_POST['jenis_kelamin'];								
					$tanggal_lahir = $_POST['tanggal_lahir'];								
					$provinsi = $_POST['provinsi'];								
					$kabkot = $_POST['kabkot'];								
					$kecamatan = $_POST['kecamatan'];								
					$desa = $_POST['desa'];								
					$alamat = $_POST['alamat'];								
					$pekerjaan = $_POST['pekerjaan'];								
					$pendidikan = $_POST['pendidikan'];								
					$id_individu = $_POST['id_individu'];								
					$nama = $_POST['nama'];								
					$jenis_desil = $_POST['jenis_desil'];								
					$status_kawin = $_POST['status_kawin'];								
					$usia_dibawah_7 = $_POST['usia_dibawah_7'];								
					$usia_7_12 = $_POST['usia_7_12'];								
					$usia_13_15 = $_POST['usia_13_15'];								
					$usia_16_18 = $_POST['usia_16_18'];								
					$usia_19_21 = $_POST['usia_19_21'];								
					$usia_22_59 = $_POST['usia_22_59'];								
					$penerima_bpnt = $_POST['penerima_bpnt'];								
					$penerima_bpum = $_POST['penerima_bpum'];								
					$penerima_bst = $_POST['penerima_bst'];								
					$penerima_pkh = $_POST['penerima_pkh'];								
					$penerima_sembako = $_POST['penerima_sembako'];								
					$resiko_stunting = $_POST['resiko_stunting'];
					$tahun_anggaran = $_POST['tahun_anggaran'];
				
					$data = array(
						'id_p3ke' => $id_p3ke,
			            'kode_kemendagri' => $kode_kemendagri,
			            'nik' => $nik,
			            'padan_dukcapil' => $padan_dukcapil,
			            'hubungan_keluarga' => $hubungan_keluarga,
			            'jenis_kelamin' => $jenis_kelamin,
			            'tanggal_lahir' => $tanggal_lahir,
			            'provinsi' => $provinsi,
			            'kabkot' => $kabkot,
			            'kecamatan' => $kecamatan,
			            'desa' => $desa,
			            'alamat' => $alamat,
			            'pekerjaan' => $pekerjaan,
			            'pendidikan' => $pendidikan,
			            'id_individu' => $id_individu,
			            'nama' => $nama,
			            'jenis_desil' => $jenis_desil,
			            'status_kawin' => $status_kawin,
			            'usia_dibawah_7' => $usia_dibawah_7,
			            'usia_7_12' => $usia_7_12,
			            'usia_13_15' => $usia_13_15,
			            'usia_16_18' => $usia_16_18,
			            'usia_19_21' => $usia_19_21,
			            'usia_22_59' => $usia_22_59,
			            'usia_60_keatas' => $usia_60_keatas,
			            'penerima_bpnt' => $penerima_bpnt,
			            'penerima_bpum' => $penerima_bpum,
			            'penerima_bst' => $penerima_bst,
			            'penerima_pkh' => $penerima_pkh,
			            'penerima_sembako' => $penerima_sembako,
			            'resiko_stunting' => $resiko_stunting,
			            'tahun_anggaran' => $tahun_anggaran,
						'active' => 1,
						'update_at' => current_time('mysql')
					);
					if(!empty($_POST['id_data'])){
						$wpdb->update('data_p3ke_anggota_keluarga', $data, array(
							'id' => $_POST['id_data']
						));
						$ret['message'] = 'Berhasil update data!';
					}else{
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT
								id,
								active
							FROM data_p3ke_anggota_keluarga
							WHERE id_p3ke=%s
						', $id_p3ke), ARRAY_A);
						if(empty($cek_id)){
							$wpdb->insert('data_p3ke_anggota_keluarga', $data);
						}else{
							if($cek_id['active'] == 0){
								$wpdb->update('data_p3ke_anggota_keluarga', $data, array(
									'id' => $cek_id['id']
								));
							}else{
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data p3ke dengan id_p3ke="'.$id_p3ke.'" sudah ada!';
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
	
	public function get_datatable_p3ke_anggota_keluarga(){
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
	 					0	=> 'id_p3ke',
			            1	=> 'provinsi',
			            2	=> 'kabkot',
			            3	=> 'kecamatan',
			            4	=> 'desa',
			            5	=> 'kode_kemendagri',
			            6	=> 'jenis_desil',
			            7	=> 'alamat',
			            8	=> 'id_individu',
			            9	=> 'nama',
			           10 	=> 'nik',
			           11 	=> 'padan_dukcapil',
			           12 	=> 'jenis_kelamin',
			           13 	=> 'hubungan_keluarga',
			           14 	=> 'tanggal_lahir',
			           15 	=> 'status_kawin',
			           16 	=> 'pekerjaan',
			           17 	=> 'pendidikan',
			           18 	=> 'usia_dibawah_7',
			           19 	=> 'usia_7_12',
			           20 	=> 'usia_13_15',
			           21 	=> 'usia_16_18',
			           22 	=> 'usia_19_21',
			           23 	=> 'usia_22_59',
			           24 	=> 'usia_60_keatas',
			           25 	=> 'penerima_bpnt',
			           26 	=> 'penerima_bpum',
			           27 	=> 'penerima_bst',
			           28 	=> 'penerima_pkh',
			           29 	=> 'penerima_sembako',
			           30 	=> 'resiko_stunting',
				 	   31  => 'tahun_anggaran',	 
				 	   32  => 'id'	 
	 			);
	 			$where = $sqlTot = $sqlRec = "";

	            if (empty($params['tahun_anggaran'])) {
	                die(json_encode(array(
	                    'status' => 'error',
	                    'message' => 'Parameter tahun anggaran diperlukan!'
	                )));
	            }

	            $tahun_anggaran = $params['tahun_anggaran'];

	            if (!empty($params['search']['value'])) {
	                $where .= " AND (nik LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%");
	                $where .= " OR alamat LIKE " . $wpdb->prepare('%s', "%" . $params['search']['value'] . "%");
	                $where .= ")";
	            }

	            $where .= " AND tahun_anggaran = " . $wpdb->prepare('%s', $tahun_anggaran);

	            $sql_tot = "SELECT count(id) as jml FROM `data_p3ke_anggota_keluarga`";
	            $sql = "SELECT " . implode(', ', $columns) . " FROM `data_p3ke_anggota_keluarga`";
	            $where_first = " WHERE 1=1 AND active = 1";
	            $sqlTot .= $sql_tot . $where_first;
	            $sqlRec .= $sql . $where_first;

	            if (isset($where) && $where != '') {
	                $sqlTot .= $where;
	                $sqlRec .= $where;
	            }

	            $limit = '';
	            if ($params['length'] != -1) {
	                $limit = " LIMIT " . $wpdb->prepare('%d', $params['start']) . " ," . $wpdb->prepare('%d', $params['length']);
	            }
	            $sqlRec .= " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'] . $limit;

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

	public function get_table_kepala_keluarga() {
		global $wpdb;

		if (empty($_POST['api_key']) || $_POST['api_key'] != get_option(SATSET_APIKEY)) {
			wp_send_json(array(
				'status' => 'error',
				'message' => 'API Key tidak valid!'
			));
		}

		$id_wilayah = !empty($_POST['id_wilayah']) ? intval($_POST['id_wilayah']) : 0;

		if (empty($id_wilayah)) {
			wp_send_json(array(
				'status' => 'error',
				'message' => 'ID Wilayah tidak ditemukan!'
			));
		}

		$data = $wpdb->get_results(
			$wpdb->prepare("
				SELECT
					*
				FROM data_dtsen_satset
				WHERE active = 1
				AND id_wilayah = %d
				ORDER BY nama_kepala_keluarga ASC
			", $id_wilayah),
			ARRAY_A
		);

		$body = '';
		$no = 1;

		foreach ($data as $value) {

			$body .= '
			<tr>
				<td class="text-center">'.$no++.'</td>
				<td>'.$value['no_kk'].'</td>
				<td>'.$value['nik'].'</td>
				<td>'.$value['nama_kepala_keluarga'].'</td>
				<td>'.$value['alamat'].'</td>
				<td class="text-center">'.$value['rt'].'/'.$value['rw'].'</td>
				<td class="text-center">'.$value['desil_nasional'].'</td>
				<td class="text-center">'.$value['peringkat_nasional'].'</td>
				<td class="text-center">'.$value['peringkat_provinsi'].'</td>
				<td class="text-center">'.$value['peringkat_kab_kota'].'</td>
				<td class="text-center">'.$value['percentile_nasional'].'</td>
				<td class="text-center">'.$value['status_nonaktif'].'</td>
				<td class="text-center">'.$value['padan_bulan_ini'].'</td>
				<td class="text-center">'.$value['update_at'].'</td>
				<td class="text-center">
					<a href="'.home_url('/detail-dtsen-per-kepala-keluarga/?no_kk='.urlencode($value['no_kk'])).'" 
					target="_blank"
					class="btn btn-sm btn-primary">
						Detail
					</a>
				</td>
			</tr>';
		}

		wp_send_json(array(
			'status' => 'success',
			'data' => $body,
			'total' => count($data)
		));
	}

	public function get_table_anggota_keluarga_dtsen()
	{
		$nik = sanitize_text_field($_POST['nik'] ?? '');

		if(empty($nik)){
			wp_send_json([
				'status'=>'error',
				'message'=>'NIK kosong'
			]);
		}
		$url = trim(get_option('_crb_dtsen_satset_server'));
		$api_key = trim(get_option('_crb_dtsen_satset_api_key'));

		if(empty($url)){
			wp_send_json([
				'status'=>'error',
				'message'=>'URL server SIKS kosong!'
			]);
		}

		if(empty($api_key)){
			wp_send_json([
				'status'=>'error',
				'message'=>'API KEY SIKS kosong!'
			]);
		}

		$ret = $this->functions->curl_post([
			'url'=>$url,
			'data'=>[
				'action'=>'search_dtsen_by_nik',
				'api_key'=>$api_key,
				'nik'=>$nik
			]
		]);

		$data = json_decode($ret, true);

		if(empty($data['status']) || $data['status'] != 'ok'){

			wp_send_json([
				'status'=>'error',
				'message'=>'Data tidak ditemukan dari SIKS',
				'response'=>$data
			]);

		}

		wp_send_json([
			'status'=>'success',
			'data'=>$data
		]);

	}

public function get_data_aids_by_id(){
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
					FROM data_aids
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

public function hapus_data_aids_by_id(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil hapus data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$ret['data'] = $wpdb->update('data_aids', array('active' => 0), array(
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

public function tambah_data_aids(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil simpan data!',
			'data' => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if(empty($_POST['id_pasien'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data id_pasien tidak boleh kosong!';
				} else if(empty($_POST['nik'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data nik tidak boleh kosong!';
				} else if(empty($_POST['nama_pasien'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data nama_pasien tidak boleh kosong!';
				} else if(empty($_POST['tahun_anggaran'])){
					$ret['status'] = 'error';
					$ret['message'] = 'Data tahun_anggaran tidak boleh kosong!';
				} else {
					$data = array(
						'provinsi'                                      => !empty($_POST['provinsi']) ? $_POST['provinsi'] : '',
						'kabkot'                                        => !empty($_POST['kabkot']) ? $_POST['kabkot'] : '',
						'kecamatan'                                     => !empty($_POST['kecamatan']) ? $_POST['kecamatan'] : '',
						'kode_upk'                                      => !empty($_POST['kode_upk']) ? $_POST['kode_upk'] : '',
						'nama_upk'                                      => !empty($_POST['nama_upk']) ? $_POST['nama_upk'] : '',
						'id_pasien'                                     => $_POST['id_pasien'],
						'warga_negara'                                  => !empty($_POST['warga_negara']) ? $_POST['warga_negara'] : '',
						'nik'                                           => $_POST['nik'],
						'nama_pasien'                                   => $_POST['nama_pasien'],
						'tanggal_lahir'                                 => !empty($_POST['tanggal_lahir']) ? $_POST['tanggal_lahir'] : '',
						'jenis_kelamin'                                 => !empty($_POST['jenis_kelamin']) ? $_POST['jenis_kelamin'] : '',
						'no_telp'                                       => !empty($_POST['no_telp']) ? $_POST['no_telp'] : '',
						'umur_terdiagnosis'                             => !empty($_POST['umur_terdiagnosis']) ? $_POST['umur_terdiagnosis'] : '',
						'kelompok_umur_terdiagnosis'                    => !empty($_POST['kelompok_umur_terdiagnosis']) ? $_POST['kelompok_umur_terdiagnosis'] : '',
						'provinsi_pasien'                               => !empty($_POST['provinsi_pasien']) ? $_POST['provinsi_pasien'] : '',
						'kabkot_pasien'                                 => !empty($_POST['kabkot_pasien']) ? $_POST['kabkot_pasien'] : '',
						'kecamatan_pasien'                              => !empty($_POST['kecamatan_pasien']) ? $_POST['kecamatan_pasien'] : '',
						'desa_pasien'                                   => !empty($_POST['desa_pasien']) ? $_POST['desa_pasien'] : '',
						'alamat_pasien'                                 => !empty($_POST['alamat_pasien']) ? $_POST['alamat_pasien'] : '',
						'provinsi_domisili'                             => !empty($_POST['provinsi_domisili']) ? $_POST['provinsi_domisili'] : '',
						'kabkot_domisili'                               => !empty($_POST['kabkot_domisili']) ? $_POST['kabkot_domisili'] : '',
						'kecamatan_domisili'                            => !empty($_POST['kecamatan_domisili']) ? $_POST['kecamatan_domisili'] : '',
						'desa_domisili'                                 => !empty($_POST['desa_domisili']) ? $_POST['desa_domisili'] : '',
						'alamat_domisili'                               => !empty($_POST['alamat_domisili']) ? $_POST['alamat_domisili'] : '',
						'tanggal_register'                              => !empty($_POST['tanggal_register']) ? $_POST['tanggal_register'] : '',
						'no_rekam_medik'                                => !empty($_POST['no_rekam_medik']) ? $_POST['no_rekam_medik'] : '',
						'waktu_input_pertama'                           => !empty($_POST['waktu_input_pertama']) ? $_POST['waktu_input_pertama'] : '',
						'kel_populasi_lsl'                              => isset($_POST['kel_populasi_lsl']) && $_POST['kel_populasi_lsl'] !== '' ? (int)$_POST['kel_populasi_lsl'] : null,
						'pendampingan_komunitas'                        => isset($_POST['pendampingan_komunitas']) && $_POST['pendampingan_komunitas'] !== '' ? (int)$_POST['pendampingan_komunitas'] : null,
						'capaian_t_dan_t_layanan'                      => !empty($_POST['capaian_t_dan_t_layanan']) ? $_POST['capaian_t_dan_t_layanan'] : '',
						'konfirmasi_hiv_plus_tanggal_konfirmasi'        => !empty($_POST['konfirmasi_hiv_plus_tanggal_konfirmasi']) ? $_POST['konfirmasi_hiv_plus_tanggal_konfirmasi'] : '',
						'konfirmasi_hiv_plus_layanan'                   => !empty($_POST['konfirmasi_hiv_plus_layanan']) ? $_POST['konfirmasi_hiv_plus_layanan'] : '',
						'tahun_anggaran'                                => $_POST['tahun_anggaran'],
						'active'                                        => 1,
						'update_at'                                     => current_time('mysql'),
					);

					if(!empty($_POST['id_data'])){
						$wpdb->update('data_aids', $data, array('id' => $_POST['id_data']));
						$ret['message'] = 'Berhasil update data!';
					} else {
						$cek_id = $wpdb->get_row($wpdb->prepare('
							SELECT id, active FROM data_aids
							WHERE id_pasien = %s AND tahun_anggaran = %d
						', $_POST['id_pasien'], $_POST['tahun_anggaran']), ARRAY_A);

						if(empty($cek_id)){
							$wpdb->insert('data_aids', $data);
						} else {
							if($cek_id['active'] == 0){
								$wpdb->update('data_aids', $data, array('id' => $cek_id['id']));
							} else {
								$ret['status'] = 'error';
								$ret['message'] = 'Gagal disimpan. Data AIDS dengan id_pasien="'.$_POST['id_pasien'].'" sudah ada!';
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

public function get_datatable_aids(){
		global $wpdb;
		$ret = array(
			'status' => 'success',
			'message' => 'Berhasil get data!',
			'data'	=> array()
		);

		if(!empty($_POST)){
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				$params = $_REQUEST;
				$columns = array(
					0  => 'provinsi',
					1  => 'kabkot',
					2  => 'kecamatan',
					3  => 'kode_upk',
					4  => 'nama_upk',
					5  => 'id_pasien',
					6  => 'warga_negara',
					7  => 'nik',
					8  => 'nama_pasien',
					9  => 'tanggal_lahir',
					10 => 'jenis_kelamin',
					11 => 'no_telp',
					12 => 'umur_terdiagnosis',
					13 => 'kelompok_umur_terdiagnosis',
					14 => 'kategori_umur_terdiagnosis',
					15 => 'provinsi_pasien',
					16 => 'kabkot_pasien',
					17 => 'kecamatan_pasien',
					18 => 'desa_pasien',
					19 => 'alamat_pasien',
					20 => 'provinsi_domisili',
					21 => 'kabkot_domisili',
					22 => 'kecamatan_domisili',
					23 => 'desa_domisili',
					24 => 'alamat_domisili',
					25 => 'tanggal_register',
					26 => 'waktu_input_pertama',
					27 => 'no_rekam_medik',
					28 => 'kel_populasi_lsl',
					29 => 'konfirmasi_hiv_plus_tanggal_konfirmasi',
					30 => 'konfirmasi_hiv_plus_provinsi',
					31 => 'konfirmasi_hiv_plus_kabkot',
					32 => 'konfirmasi_hiv_plus_layanan',
					33 => 'akhir_followup_sblm_masuk_perawatan_meninggal',
					34 => 'pendampingan_komunitas',
					35 => 'akhir_followup_sblm_masuk_perawatan_dan_arv_meninggal',
					36 => 'capaian_t_dan_t_layanan',
					37 => 'capaian_t_dan_t_kabkot',
					38 => 'capaian_t_dan_t_provinsi',
					39 => 'tahun_anggaran',
					40 => 'id',
				);
				$where = $sqlTot = $sqlRec = "";

				if(empty($params['tahun_anggaran'])){
					die(json_encode(array(
						'status' => 'error',
						'message' => 'Parameter tahun anggaran diperlukan!'
					)));
				}

				$tahun_anggaran = $params['tahun_anggaran'];
				$where .= " AND tahun_anggaran = " . $wpdb->prepare('%s', $tahun_anggaran);

				if(!empty($params['search']['value'])){
					$where .= " AND (nik LIKE " . $wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .= " OR nama_pasien LIKE " . $wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .= " OR id_pasien LIKE " . $wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .= " OR no_rekam_medik LIKE " . $wpdb->prepare('%s', "%".$params['search']['value']."%");
					$where .= ")";
				}

				$sql_tot = "SELECT count(id) as jml FROM `data_aids`";
				$sql     = "SELECT ".implode(', ', $columns)." FROM `data_aids`";
				$where_first = " WHERE 1=1 AND active = 1";
				$sqlTot  = $sql_tot.$where_first.$where;
				$sqlRec  = $sql.$where_first.$where;

				$limit = '';
				if($params['length'] != -1){
					$limit = " LIMIT ".$wpdb->prepare('%d', $params['start'])." ,".$wpdb->prepare('%d', $params['length']);
				}
				$sqlRec .= " ORDER BY ".$columns[$params['order'][0]['column']]." ".$params['order'][0]['dir'].$limit;

				$queryTot     = $wpdb->get_results($sqlTot, ARRAY_A);
				$totalRecords = $queryTot[0]['jml'];
				$queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);

				foreach($queryRecords as $recKey => $recVal){
					$btn  = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$recVal['id'].'\'); return false;" href="#" title="Edit Data"><i class="dashicons dashicons-edit"></i></a>';
					$btn .= '<a class="btn btn-sm btn-danger" onclick="hapus_data(\''.$recVal['id'].'\'); return false;" href="#" title="Hapus Data"><i class="dashicons dashicons-trash"></i></a>';
					$queryRecords[$recKey]['aksi'] = $btn;
				}

				$json_data = array(
					"draw"            => intval($params['draw']),
					"recordsTotal"    => intval($totalRecords),
					"recordsFiltered" => intval($totalRecords),
					"data"            => $queryRecords,
					"sql"             => $sqlRec
				);
				die(json_encode($json_data));
			}else{
				$return = array('status' => 'error', 'message' => 'Api Key tidak sesuai!');
			}
		}else{
			$return = array('status' => 'error', 'message' => 'Format tidak sesuai!');
		}
		die(json_encode($return));
	}

public function mapping_kode_desa_aids(){
		global $wpdb;
		$ret = array(
			'status'  => 'success',
			'message' => 'Berhasil mapping data!',
			'data'    => array()
		);
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option( SATSET_APIKEY )) {
				if(empty($_POST['kode_desa_aids'])){
					$ret['status']  = 'error';
					$ret['message'] = 'Parameter kode_desa_aids tidak boleh kosong!';
				} else {
					$kode_desa_aids  = sanitize_text_field($_POST['kode_desa_aids']);
					$kode_desa_satset = isset($_POST['kode_desa_satset']) ? sanitize_text_field($_POST['kode_desa_satset']) : '';

					// Cek apakah sudah ada baris untuk kode_desa_aids ini
					$existing = $wpdb->get_var($wpdb->prepare(
						"SELECT id FROM mapping_aids WHERE kode_desa_aids = %s",
						$kode_desa_aids
					));

					if($existing){
						$wpdb->update(
							'mapping_aids',
							array('kode_desa_satset' => $kode_desa_satset),
							array('kode_desa_aids'  => $kode_desa_aids)
						);
						$ret['message'] = 'Mapping berhasil diperbarui!';
					} else {
						$wpdb->insert('mapping_aids', array(
							'kode_desa_aids'  => $kode_desa_aids,
							'kode_desa_satset' => $kode_desa_satset,
						));
						$ret['message'] = 'Mapping berhasil disimpan!';
					}
				}
			} else {
				$ret['status']  = 'error';
				$ret['message'] = 'Api key tidak ditemukan!';
			}
		} else {
			$ret['status']  = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	public function get_data_malaria_by_id(){
		global $wpdb;
		$ret = array('status' => 'success', 'message' => 'Berhasil get data!', 'data' => array());
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option(SATSET_APIKEY)){
				$ret['data'] = $wpdb->get_row($wpdb->prepare('SELECT * FROM data_malaria WHERE id=%d', $_POST['id']), ARRAY_A);
			} else {
				$ret['status'] = 'error'; $ret['message'] = 'Api key tidak ditemukan!';
			}
		} else {
			$ret['status'] = 'error'; $ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	public function hapus_data_malaria_by_id(){
		global $wpdb;
		$ret = array('status' => 'success', 'message' => 'Berhasil hapus data!', 'data' => array());
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option(SATSET_APIKEY)){
				$ret['data'] = $wpdb->update('data_malaria', array('active' => 0), array('id' => $_POST['id']));
			} else {
				$ret['status'] = 'error'; $ret['message'] = 'Api key tidak ditemukan!';
			}
		} else {
			$ret['status'] = 'error'; $ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	public function tambah_data_malaria(){
		global $wpdb;
		$ret = array('status' => 'success', 'message' => 'Berhasil simpan data!', 'data' => array());
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option(SATSET_APIKEY)){
				if(empty($_POST['nama'])){
					die(json_encode(array('status' => 'error', 'message' => 'Nama tidak boleh kosong!')));
				}
				if(empty($_POST['tahun_anggaran'])){
					die(json_encode(array('status' => 'error', 'message' => 'Tahun anggaran tidak boleh kosong!')));
				}
				$data = array(
					'provinsi'          => !empty($_POST['provinsi'])          ? $_POST['provinsi']          : '',
					'kabkot'            => !empty($_POST['kabkot'])            ? $_POST['kabkot']            : '',
					'kecamatan'         => !empty($_POST['kecamatan'])         ? $_POST['kecamatan']         : '',
					'desa'              => !empty($_POST['desa'])              ? $_POST['desa']              : '',
					'rt'                => !empty($_POST['rt'])                ? $_POST['rt']                : '',
					'rw'                => !empty($_POST['rw'])                ? $_POST['rw']                : '',
					'nik'               => !empty($_POST['nik'])               ? $_POST['nik']               : '',
					'nama'              => $_POST['nama'],
					'umur'              => !empty($_POST['umur'])              ? $_POST['umur']              : '',
					'tindak_lanjut'     => !empty($_POST['tindak_lanjut'])     ? $_POST['tindak_lanjut']     : '',
					'hasil_akhir'       => !empty($_POST['hasil_akhir'])       ? $_POST['hasil_akhir']       : '',
					'status_pengobatan' => !empty($_POST['status_pengobatan']) ? $_POST['status_pengobatan'] : '',
					'tahun_anggaran'    => $_POST['tahun_anggaran'],
					'active'            => 1,
					'update_at'         => current_time('mysql'),
				);
				if(!empty($_POST['id_data'])){
					$wpdb->update('data_malaria', $data, array('id' => $_POST['id_data']));
					$ret['message'] = 'Berhasil update data!';
				} else {
					$cek_id = $wpdb->get_var($wpdb->prepare(
						"SELECT id FROM data_malaria WHERE nama = %s AND kabkot = %s AND tahun_anggaran = %d",
						$_POST['nama'], !empty($_POST['kabkot']) ? $_POST['kabkot'] : '', $_POST['tahun_anggaran']
					));
					if(empty($cek_id)){
						$wpdb->insert('data_malaria', $data);
					} else {
						$ret['status']  = 'error';
						$ret['message'] = 'Data Malaria dengan nama tersebut sudah ada!';
					}
				}
			} else {
				$ret['status'] = 'error'; $ret['message'] = 'Api key tidak ditemukan!';
			}
		} else {
			$ret['status'] = 'error'; $ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	public function get_datatable_malaria(){
		global $wpdb;
		$ret = array('status' => 'success', 'message' => 'Berhasil get data!', 'data' => array());
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option(SATSET_APIKEY)){
				$params  = $_REQUEST;
				$columns = array(
					0  => 'provinsi',
					1  => 'kabkot',
					2  => 'kecamatan',
					3  => 'desa',
					4  => 'rt',
					5  => 'rw',
					6  => 'nik',
					7  => 'nama',
					8  => 'umur',
					9  => 'tindak_lanjut',
					10 => 'hasil_akhir',
					11 => 'status_pengobatan',
					12 => 'tahun_anggaran',
					13 => 'id',
				);
				if(empty($params['tahun_anggaran'])){
					die(json_encode(array('status' => 'error', 'message' => 'Parameter tahun anggaran diperlukan!')));
				}
				$tahun_anggaran = $params['tahun_anggaran'];
				$where = " AND tahun_anggaran = " . $wpdb->prepare('%s', $tahun_anggaran);
				if(!empty($params['search']['value'])){
					$s = $params['search']['value'];
					$where .= " AND (nik LIKE " . $wpdb->prepare('%s', "%$s%");
					$where .= " OR nama LIKE "  . $wpdb->prepare('%s', "%$s%") . ")";
				}
				$sql_tot = "SELECT count(id) as jml FROM `data_malaria`";
				$sql     = "SELECT " . implode(', ', $columns) . " FROM `data_malaria`";
				$where_first = " WHERE 1=1 AND active = 1";
				$sqlTot  = $sql_tot . $where_first . $where;
				$sqlRec  = $sql     . $where_first . $where;
				$limit   = '';
				if($params['length'] != -1){
					$limit = " LIMIT " . $wpdb->prepare('%d', $params['start']) . "," . $wpdb->prepare('%d', $params['length']);
				}
				$sqlRec .= " ORDER BY " . $columns[$params['order'][0]['column']] . " " . $params['order'][0]['dir'] . $limit;
				$totalRecords = $wpdb->get_results($sqlTot, ARRAY_A)[0]['jml'];
				$queryRecords = $wpdb->get_results($sqlRec, ARRAY_A);
				foreach($queryRecords as $k => $v){
					$btn  = '<a class="btn btn-sm btn-warning" onclick="edit_data(\''.$v['id'].'\'); return false;" href="#"><i class="dashicons dashicons-edit"></i></a>';
					$btn .= '<a class="btn btn-sm btn-danger"  onclick="hapus_data(\''.$v['id'].'\'); return false;" href="#"><i class="dashicons dashicons-trash"></i></a>';
					$queryRecords[$k]['aksi'] = $btn;
				}
				die(json_encode(array(
					'draw'            => intval($params['draw']),
					'recordsTotal'    => intval($totalRecords),
					'recordsFiltered' => intval($totalRecords),
					'data'            => $queryRecords,
				)));
			} else {
				$ret['status'] = 'error'; $ret['message'] = 'Api Key tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error'; $ret['message'] = 'Format tidak sesuai!';
		}
		die(json_encode($ret));
	}

	public function mapping_kode_desa_malaria(){
		global $wpdb;
		$ret = array('status' => 'success', 'message' => 'Berhasil mapping data!', 'data' => array());
		if(!empty($_POST)){
			if(!empty($_POST['api_key']) && $_POST['api_key'] == get_option(SATSET_APIKEY)){
				if(empty($_POST['kode_desa_malaria'])){
					$ret['status']  = 'error';
					$ret['message'] = 'Parameter kode_desa_malaria tidak boleh kosong!';
				} else {
					$kode_desa_malaria = sanitize_text_field($_POST['kode_desa_malaria']);
					$kode_desa_satset  = isset($_POST['kode_desa_satset']) ? sanitize_text_field($_POST['kode_desa_satset']) : '';
					$existing = $wpdb->get_var($wpdb->prepare(
						"SELECT id FROM mapping_malaria WHERE kode_desa_malaria = %s", $kode_desa_malaria
					));
					if($existing){
						$wpdb->update('mapping_malaria', array('kode_desa_satset' => $kode_desa_satset), array('kode_desa_malaria' => $kode_desa_malaria));
						$ret['message'] = 'Mapping berhasil diperbarui!';
					} else {
						$wpdb->insert('mapping_malaria', array('kode_desa_malaria' => $kode_desa_malaria, 'kode_desa_satset' => $kode_desa_satset));
						$ret['message'] = 'Mapping berhasil disimpan!';
					}
				}
			} else {
				$ret['status'] = 'error'; $ret['message'] = 'Api key tidak ditemukan!';
			}
		} else {
			$ret['status'] = 'error'; $ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

}