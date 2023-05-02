<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Satset
 * @subpackage Wp_Satset/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Wp_Satset
 * @subpackage Wp_Satset/includes
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
class Wp_Satset {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Wp_Satset_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'WP_SATSET_VERSION' ) ) {
			$this->version = WP_SATSET_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'wp-satset';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Wp_Satset_Loader. Orchestrates the hooks of the plugin.
	 * - Wp_Satset_i18n. Defines internationalization functionality.
	 * - Wp_Satset_Admin. Defines all hooks for the admin area.
	 * - Wp_Satset_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-satset-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wp-satset-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wp-satset-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wp-satset-public.php';

		$this->loader = new Wp_Satset_Loader();

		// Functions tambahan
		require_once plugin_dir_path(dirname(__FILE__)) . 'includes/class-wp-satset-functions.php';

		$this->functions = new SATSET_Functions( $this->plugin_name, $this->version );

		$this->loader->add_action('template_redirect', $this->functions, 'allow_access_private_post', 0);

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Wp_Satset_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Wp_Satset_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Wp_Satset_Admin( $this->get_plugin_name(), $this->get_version(), $this->functions );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action('carbon_fields_register_fields', $plugin_admin, 'crb_attach_satset_options');
		$this->loader->add_action('wp_ajax_import_excel_p3ke',  $plugin_admin, 'import_excel_p3ke');
		$this->loader->add_action('wp_ajax_import_excel_stunting',  $plugin_admin, 'import_excel_stunting');
		$this->loader->add_action('wp_ajax_import_excel_tbc',  $plugin_admin, 'import_excel_tbc');
		$this->loader->add_action('wp_ajax_import_excel_rtlh',  $plugin_admin, 'import_excel_rtlh');
		$this->loader->add_action('wp_ajax_get_data_dtks',  $plugin_admin, 'get_data_dtks');
		$this->loader->add_action('wp_ajax_get_data_desa',  $plugin_admin, 'get_data_desa');
		$this->loader->add_action('wp_ajax_get_data_batas_kecamatan',  $plugin_admin, 'get_data_batas_kecamatan');
		

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Wp_Satset_Public( $this->get_plugin_name(), $this->get_version(), $this->functions );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		$this->loader->add_action('wp_ajax_cari_data_filter_satset', $plugin_public, 'cari_data_filter_satset');
		$this->loader->add_action('wp_ajax_cari_data_satset', $plugin_public, 'cari_data_satset');
		$this->loader->add_action('wp_ajax_get_datatable_p3ke', $plugin_public, 'get_datatable_p3ke');
		$this->loader->add_action('wp_ajax_get_datatable_stunting', $plugin_public, 'get_datatable_stunting');
		$this->loader->add_action('wp_ajax_get_datatable_tbc', $plugin_public, 'get_datatable_tbc');
		$this->loader->add_action('wp_ajax_get_datatable_rtlh', $plugin_public, 'get_datatable_rtlh');
		$this->loader->add_action('wp_ajax_get_datatable_batas_desa', $plugin_public, 'get_datatable_batas_desa');
		$this->loader->add_action('wp_ajax_get_datatable_batas_kecamatan', $plugin_public, 'get_datatable_batas_kecamatan');
		$this->loader->add_action('wp_ajax_get_data_p3ke_by_id', $plugin_public, 'get_data_p3ke_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_p3ke', $plugin_public, 'tambah_data_p3ke');
		$this->loader->add_action('wp_ajax_hapus_data_p3ke_by_id', $plugin_public, 'hapus_data_p3ke_by_id');
		$this->loader->add_action('wp_ajax_hapus_data_stunting_by_id', $plugin_public, 'hapus_data_stunting_by_id');
		$this->loader->add_action('wp_ajax_get_data_stunting_by_id', $plugin_public, 'get_data_stunting_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_stunting', $plugin_public, 'tambah_data_stunting');
		$this->loader->add_action('wp_ajax_hapus_data_tbc_by_id', $plugin_public, 'hapus_data_tbc_by_id');
		$this->loader->add_action('wp_ajax_get_data_tbc_by_id', $plugin_public, 'get_data_tbc_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_tbc', $plugin_public, 'tambah_data_tbc');
		$this->loader->add_action('wp_ajax_hapus_data_rtlh_by_id', $plugin_public, 'hapus_data_rtlh_by_id');
		$this->loader->add_action('wp_ajax_get_data_rtlh_by_id', $plugin_public, 'get_data_rtlh_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_rtlh', $plugin_public, 'tambah_data_rtlh');
		$this->loader->add_action('wp_ajax_hapus_data_batas_desa_by_id', $plugin_public, 'hapus_data_batas_desa_by_id');
		$this->loader->add_action('wp_ajax_get_data_batas_desa_by_id', $plugin_public, 'get_data_batas_desa_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_batas_desa', $plugin_public, 'tambah_data_batas_desa');
		$this->loader->add_action('wp_ajax_hapus_data_batas_kecamatan_by_id', $plugin_public, 'hapus_data_batas_kecamatan_by_id');
		$this->loader->add_action('wp_ajax_get_data_batas_kecamatan_by_id', $plugin_public, 'get_data_batas_kecamatan_by_id');
		$this->loader->add_action('wp_ajax_tambah_data_batas_kecamatan', $plugin_public, 'tambah_data_batas_kecamatan');

		add_shortcode('satset_homepage', array($plugin_public, 'satset_homepage'));
		add_shortcode('conversi_peta_satset', array($plugin_public, 'conversi_peta_satset'));
		add_shortcode('peta_satset_desa', array($plugin_public, 'peta_satset_desa'));
		add_shortcode('peta_satset_kecamatan', array($plugin_public, 'peta_satset_kecamatan'));
		add_shortcode('data_irisan_satset', array($plugin_public, 'data_irisan_satset'));
		add_shortcode('filter_data_irisan_satset', array($plugin_public, 'filter_data_irisan_satset'));
		add_shortcode('cek_nik_satset', array($plugin_public, 'cek_nik_satset'));
		add_shortcode('management_data_p3ke_satset', array($plugin_public, 'management_data_p3ke_satset'));
		add_shortcode('management_data_stunting_satset', array($plugin_public, 'management_data_stunting_satset'));
		add_shortcode('management_data_tbc_satset', array($plugin_public, 'management_data_tbc_satset'));
		add_shortcode('management_data_rtlh_satset', array($plugin_public, 'management_data_rtlh_satset'));
		add_shortcode('management_data_batas_desa_satset', array($plugin_public, 'management_data_batas_desa_satset'));
		add_shortcode('management_data_batas_kecamatan_satset', array($plugin_public, 'management_data_batas_kecamatan_satset'));
		add_shortcode('peta_satset', array($plugin_public, 'peta_satset'));
		add_shortcode('data_p3ke', array($plugin_public, 'data_p3ke'));
		add_shortcode('data_stunting', array($plugin_public, 'data_stunting'));
		add_shortcode('data_tbc', array($plugin_public, 'data_tbc'));
		add_shortcode('data_rtlh', array($plugin_public, 'data_rtlh'));
		add_shortcode('data_dtks', array($plugin_public, 'data_dtks'));
		add_shortcode('data_batas_desa', array($plugin_public, 'data_batas_desa'));
		add_shortcode('data_batas_kecamatan', array($plugin_public, 'data_batas_kecamatan'));

	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Wp_Satset_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
