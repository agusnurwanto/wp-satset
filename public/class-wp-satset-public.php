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
		return $api_googlemap;
	}

	function peta_satset(){
		// untuk disable render shortcode di halaman edit page/post
		if(!empty($_GET) && !empty($_GET['post'])){
			return '';
		}
		require_once plugin_dir_path(dirname(__FILE__)) . 'public/partials/wp-satset-public-peta.php';
	}

}
