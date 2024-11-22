<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://github.com/agusnurwanto
 * @since      1.0.0
 *
 * @package    Wp_Satset
 * @subpackage Wp_Satset/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wp_Satset
 * @subpackage Wp_Satset/admin
 * @author     Agus Nurwanto <agusnurwantomuslim@gmail.com>
 */
use Carbon_Fields\Container;
use Carbon_Fields\Field;

class Wp_Satset_Admin {

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

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version, $functions ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->functions = $functions;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/wp-satset-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name.'jszip', plugin_dir_url( __FILE__ ) . 'js/jszip.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name.'xlsx', plugin_dir_url( __FILE__ ) . 'js/xlsx.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-satset-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script($this->plugin_name, 'satset', array(
			'api_key' => get_option(SATSET_APIKEY)
		));

	}

	public function get_ajax_field($options = array('type' => null))
	{
		$ret = array();
		$hide_sidebar = Field::make('html', 'crb_hide_sidebar')
			->set_html('
				<style>
					.postbox-container { display: none; }
					#poststuff #post-body.columns-2 { margin: 0 !important; }
				</style>
        		<div id="satset_load_ajax_carbon" data-type="' . $options['type'] . '"></div>
        	');
		$ret[] = $hide_sidebar;
		return $ret;
	}

	public function satset_load_ajax_carbon()
	{
		global $wpdb;
		$ret = array(
			'status'    => 'success',
			'message'   => ''
		);

		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option('_crb_apikey_satset')) {
				if (
					!empty($_POST['type'])
				) {
					$tahun = $wpdb->get_results(
						$wpdb->prepare("
							SELECT 
								tahun_anggaran 
							FROM satset_data_unit 
							GROUP BY tahun_anggaran
							ORDER BY tahun_anggaran DESC
						"),
						ARRAY_A
					);
					foreach ($tahun as $tahun_item) {
						if (!empty($_POST['type']) && $_POST['type'] == 'data_p3ke') {
						    $p3ke = $this->functions->generatePage(array(
						        'nama_page' => 'Management Data P3KE',
						        'content' => '[management_data_p3ke_satset]',
						        'show_header' => 1,
						        'no_key' => 1,
						        'post_status' => 'private'
						    ));
						    $p3ke_anggota_keluarga = $this->functions->generatePage(array(
						        'nama_page' => 'Management Data P3KE Anggota Keluarga',
						        'content' => '[management_data_p3ke_anggota_keluarga]',
						        'show_header' => 1,
						        'no_key' => 1,
						        'post_status' => 'private'
						    ));

						    $body_pemda = '
						    <div class="accordion">
						        <h3 class="satset-header-tahun" tahun="' . $tahun_item['tahun_anggaran'] . '">Tahun Anggaran ' . $tahun_item['tahun_anggaran'] . '</h3>
						        <div class="satset-body-tahun" tahun="' . $tahun_item['tahun_anggaran'] . '">
						            <ul style="margin-left: 20px;">
						                <li><a target="_blank" href="' . $p3ke['url'] . '?tahun_anggaran='.$tahun_item['tahun_anggaran'].'">' . $p3ke['title'] . '</a></li>
						                <li><a target="_blank" href="' . $p3ke_anggota_keluarga['url'] . '?tahun_anggaran='.$tahun_item['tahun_anggaran'].'">' . $p3ke_anggota_keluarga['title'] . '</a></li>
						            </ul>
						        </div>
						    </div>';

						    $ret['message'] .= $body_pemda;
						}
					}
				}
			}
		}
		die(json_encode($ret));
	}

	function crb_attach_satset_options(){
		global $wpdb;

		$satset_homepage = $this->functions->generatePage(array(
			'nama_page' => 'Beranda / Homepage SATSET', 
			'content' => '[satset_homepage]',
        	'show_header' => 0,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$peta_satset = $this->functions->generatePage(array(
			'nama_page' => 'Peta Data Terpadu', 
			'content' => '[peta_satset]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$peta_batas_desa = $this->functions->generatePage(array(
			'nama_page' => 'Peta Batas Desa', 
			'content' => '[peta_satset_desa]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$peta_batas_kecamatan = $this->functions->generatePage(array(
			'nama_page' => 'Peta Batas Kecamatan', 
			'content' => '[peta_satset_kecamatan]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$conversi_peta_satset = $this->functions->generatePage(array(
			'nama_page' => 'Conversi Data SHP ke GEOJSON', 
			'content' => '[conversi_peta_satset]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$data_p3ke = $this->functions->generatePage(array(
			'nama_page' => 'Data P3KE', 
			'content' => '[data_p3ke]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));


		$data_stunting = $this->functions->generatePage(array(
			'nama_page' => 'Data Stunting', 
			'content' => '[data_stunting]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$data_tbc = $this->functions->generatePage(array(
			'nama_page' => 'Data TBC', 
			'content' => '[data_tbc]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$data_rtlh = $this->functions->generatePage(array(
			'nama_page' => 'Data RTLH', 
			'content' => '[data_rtlh]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$data_dtks = $this->functions->generatePage(array(
			'nama_page' => 'Data DTKS', 
			'content' => '[data_dtks]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$data_batas_desa = $this->functions->generatePage(array(
			'nama_page' => 'Data Desa', 
			'content' => '[data_batas_desa]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$data_batas_kecamatan = $this->functions->generatePage(array(
			'nama_page' => 'Data Kecamatan', 
			'content' => '[data_batas_kecamatan]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$petunjuk_penggunaan = $this->functions->generatePage(array(
			'nama_page' => 'Petunjuk Penggunaan SATSET',
			'content' => '[petunjuk_penggunaan_satset]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$dokumentasi_sistem = $this->functions->generatePage(array(
			'nama_page' => 'Dokumentasi Sistem SATSET',
			'content' => '[dokumentasi_sistem_satset]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$tanggapan_publik = $this->functions->generatePage(array(
			'nama_page' => 'Tanggapan Publik SATSET',
			'content' => '[tanggapan_publik_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'publish'
		));

		$cek_nik = $this->functions->generatePage(array(
			'nama_page' => 'Cek NIK SATSET',
			'content' => '[cek_nik_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));

		$data_irisan = $this->functions->generatePage(array(
			'nama_page' => 'Data Irisan',
			'content' => '[data_irisan_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'publish'
		));

		$filter_data_irisan = $this->functions->generatePage(array(
			'nama_page' => 'Filter Data Irisan SATSET',
			'content' => '[filter_data_irisan_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'publish'
		));

		$management_data_p3ke = $this->functions->generatePage(array(
			'nama_page' => 'Management Data P3KE',
			'content' => '[management_data_p3ke_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));
		$management_data_p3ke_anggota_keluarga = $this->functions->generatePage(array(
			'nama_page' => 'Management Data P3KE Anggota Keluarga', 
			'content' => '[management_data_p3ke_anggota_keluarga]',
        	'show_header' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));
		$management_data_stunting = $this->functions->generatePage(array(
			'nama_page' => 'Management Data Stunting',
			'content' => '[management_data_stunting_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));
		$management_data_tbc = $this->functions->generatePage(array(
			'nama_page' => 'Management Data TBC',
			'content' => '[management_data_tbc_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));
		$management_data_rtlh = $this->functions->generatePage(array(
			'nama_page' => 'Management Data RTLH',
			'content' => '[management_data_rtlh_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));
		$management_data_batas_desa = $this->functions->generatePage(array(
			'nama_page' => 'Management Data Desa',
			'content' => '[management_data_batas_desa_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));
		$management_data_batas_kecamatan = $this->functions->generatePage(array(
			'nama_page' => 'Management Data Kecamatan',
			'content' => '[management_data_batas_kecamatan_satset]',
			'show_header' => 1,
			'no_key' => 1,
			'post_status' => 'private'
		));

		$basic_options_container = Container::make( 'theme_options', __( 'WP SATSET Options' ) )
			->set_page_menu_position( 4 )
	        ->add_fields( array(
				Field::make( 'html', 'crb_satset_halaman_terkait' )
		        	->set_html( '
					<h5>HALAMAN TERKAIT</h5>
	            	<ol>
	            		<li><a target="_blank" href="'.$satset_homepage['url'].'">'.$satset_homepage['title'].'</a></li>
	            		<li><a target="_blank" href="'.$conversi_peta_satset['url'].'">'.$conversi_peta_satset['title'].'</a></li>
	            		<li><a target="_blank" href="'.$peta_satset['url'].'">'.$peta_satset['title'].'</a></li>
	            		<li><a target="_blank" href="'.$peta_batas_desa['url'].'">'.$peta_batas_desa['title'].'</a></li>
	            		<li><a target="_blank" href="'.$peta_batas_kecamatan['url'].'">'.$peta_batas_kecamatan['title'].'</a></li>
	            		<li><a target="_blank" href="'.$cek_nik['url'].'">'.$cek_nik['title'].'</a></li>
	            		<li><a target="_blank" href="'.$data_irisan['url'].'">'.$data_irisan['title'].'</a></li>
	            		<li><a target="_blank" href="'.$filter_data_irisan['url'].'">'.$filter_data_irisan['title'].'</a></li>
	            		<li><a target="_blank" href="'.$data_p3ke['url'].'">'.$data_p3ke['title'].'</a></li>
	            		<li><a target="_blank" href="'.$data_stunting['url'].'">'.$data_stunting['title'].'</a></li>
	            		<li><a target="_blank" href="'.$data_tbc['url'].'">'.$data_tbc['title'].'</a></li>
	            		<li><a target="_blank" href="'.$data_rtlh['url'].'">'.$data_rtlh['title'].'</a></li>
	            		<li><a target="_blank" href="'.$data_dtks['url'].'">'.$data_dtks['title'].'</a></li>
	            		<li><a target="_blank" href="'.$petunjuk_penggunaan['url'].'">'.$petunjuk_penggunaan['title'].'</a></li>
	            		<li><a target="_blank" href="'.$dokumentasi_sistem['url'].'">'.$dokumentasi_sistem['title'].'</a></li>
	            		<li><a target="_blank" href="'.$tanggapan_publik['url'].'">'.$tanggapan_publik['title'].'</a></li>
	            	</ol>
		        	' ),
	            Field::make( 'text', 'crb_apikey_satset', 'API KEY' )
	            	->set_default_value($this->functions->generateRandomString())
	            	->set_help_text('Wajib diisi. API KEY digunakan untuk integrasi data.'),
	            Field::make( 'text', 'crb_prov_satset', 'Nama Provinsi' )
	            	->set_default_value( 'JAWA TIMUR' )
            		->set_required( true )
	            	->set_help_text('Nama provinsi dalam huruf besar dan tanpa awalan.'),
	            Field::make( 'text', 'crb_kab_satset', 'Nama Kabupaten/Kota' )
	            	->set_default_value( 'MAGETAN' )
	            	->set_help_text('Nama kabupaten dalam huruf besar dan tanpa awalan.'),
            	Field::make( 'html', 'crb_satset_sql_migrate' )
	            	->set_html( '<a onclick="sql_migrate_satset(); return false" href="javascript:void(0);" class="button button-primary">SQL Migrate</a>' )
	            	->set_help_text('Tombol ini untuk melakukan perbaikan struktur tabel database.'),
				Field::make('text', 'crb_tahun_satset', 'Tahun Anggaran SATSET')
					->set_default_value(date('Y'))
					->set_help_text('Tahun anggaran diatas digunakan untuk default filter tahun.'),

				Field::make('text', 'crb_url_server_satset', 'URL Server WP-SIPD')
					->set_default_value(admin_url('admin-ajax.php'))
					->set_required(true),
				Field::make('text', 'crb_apikey_wpsipd', 'API KEY WP-SIPD')
					->set_default_value($this->functions->generateRandomString())
					->set_help_text('Wajib diisi. API KEY digunakan untuk integrasi data.'),
				Field::make('text', 'crb_tahun_wpsipd', 'Tahun Anggaran WP-SIPD')
					->set_default_value(date('Y'))
					->set_help_text('Wajib diisi.'),
				Field::make('html', 'crb_html_data_unit')
					->set_html('<a href="#" class="button button-primary" onclick="get_data_unit_wpsipd_satset(); return false;">Tarik Data Unit dari WP SIPD</a>')
					->set_help_text('Tombol untuk menarik data Unit dari WP SIPD.'),
				Field::make('html', 'crb_generate_user')
					->set_html('<a id="generate_user_satset" onclick="return false;" href="#" class="button button-primary button-large">Generate User By DB Lokal</a>')
					->set_help_text('Data user active yang ada di table data unit akan digenerate menjadi user wordpress.'),

            ) );

		Container::make( 'theme_options', __( 'Tampilan Beranda' ) )
			->set_page_parent( $basic_options_container )
			->add_tab( __( 'Logo' ), array(
		        Field::make( 'image', 'crb_satset_menu_logo_dashboard', __( 'Gambar Logo' ) )
		        	->set_value_type('url')
        			->set_default_value('https://via.placeholder.com/135x25'),
		        Field::make( 'textarea', 'crb_satset_judul_header', __( 'Judul' ) )
		        	->set_default_value('Satu Data Terpadu Stanting, Kemiskinan Ekstrim, ATM, DTKS dan RTLH'),
		        Field::make( 'text', 'crb_satset_menu_video_loading', __( 'Video Loading' ) ),
		        Field::make( 'text', 'crb_satset_lama_loading', __( 'Lama Loading' ) )
        			->set_default_value('10000')
            		->set_attribute('type', 'number')
        			->set_help_text('Lama waktu untuk menghilangkan gambar atau video intro. Satuan dalam mili detik.'),
		    	Field::make( 'complex', 'crb_satset_background_beranda', 'Background Beranda' )
		    		->add_fields( 'beranda', array(
				        Field::make( 'image', 'gambar', 'Gambar' )
		        			->set_value_type('url')
		        			->set_default_value('https://via.placeholder.com/1200x900')
		        		) ),
		    ) )
			->add_tab( __( 'Icon & Menu' ), array(
		        Field::make( 'image', 'crb_satset_menu_logo_1', __( 'Gambar Menu 1' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_1', __( 'Text Menu 1' ) )
        			->set_default_value('Peta Data'),
		        Field::make( 'text', 'crb_satset_menu_url_1', __( 'URL Menu 1' ) )
        			->set_default_value($peta_satset['url']),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_1', __( 'Keterangan Menu 1' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_2', __( 'Gambar Menu 2' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_2', __( 'Text Menu 2' ) )
        			->set_default_value('Batas Kecamatan'),
		        Field::make( 'text', 'crb_satset_menu_url_2', __( 'URL Menu 2' ) )
        			->set_default_value($peta_batas_kecamatan['url']),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_2', __( 'Keterangan Menu 2' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_3', __( 'Gambar Menu 3' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_3', __( 'Text Menu 3' ) )
        			->set_default_value('Batas Desa/Kelurahan'),
		        Field::make( 'text', 'crb_satset_menu_url_3', __( 'URL Menu 3' ) )
        			->set_default_value($peta_batas_desa['url']),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_3', __( 'Keterangan Menu 3' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_4', __( 'Gambar Menu 4' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_4', __( 'Text Menu 4' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_4', __( 'URL Menu 4' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_4', __( 'Keterangan Menu 4' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_5', __( 'Gambar Menu 5' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_5', __( 'Text Menu 5' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_5', __( 'URL Menu 5' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_5', __( 'Keterangan Menu 5' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_6', __( 'Gambar Menu 6' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_6', __( 'Text Menu 6' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_6', __( 'URL Menu 6' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_6', __( 'Keterangan Menu 6' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_7', __( 'Gambar Menu 7' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_7', __( 'Text Menu 7' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_7', __( 'URL Menu 7' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_7', __( 'Keterangan Menu 7' ) )
        			->set_default_value('keterangan'.'?sertifikat=1'),
		        Field::make( 'image', 'crb_satset_menu_logo_8', __( 'Gambar Menu 8' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_8', __( 'Text Menu 8' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_8', __( 'URL Menu 8' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_8', __( 'Keterangan Menu 8' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_9', __( 'Gambar Menu 9' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_9', __( 'Text Menu 9' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_9', __( 'URL Menu 9' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_9', __( 'Keterangan Menu 9' ) )
        			->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_10', __( 'Gambar Menu 10' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_10', __( 'Text Menu 10' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_10', __( 'URL Menu 10' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_10', __( 'Keterangan Menu 10' ) )
		        	->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_11', __( 'Gambar Menu 11' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_11', __( 'Text Menu 11' ) )
        			->set_default_value('Petunjuk Penggunaan'),
		        Field::make( 'text', 'crb_satset_menu_url_11', __( 'URL Menu 11' ) )
        			->set_default_value($petunjuk_penggunaan['url']),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_11', __( 'Keterangan Menu 11' ) )
		        	->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_0', __( 'Gambar Menu Lainya' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_0', __( 'Text Menu Lainya' ) )
        			->set_default_value('Lainnya'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_0', __( 'Keterangan Menu Lainya' ) )
		        	->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_12', __( 'Gambar Menu 12' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_12', __( 'Text Menu 12' ) )
        			->set_default_value('Dokumentasi Sistem'),
		        Field::make( 'text', 'crb_satset_menu_url_12', __( 'URL Menu 12' ) )
        			->set_default_value($dokumentasi_sistem['url']),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_12', __( 'Keterangan Menu 12' ) )
		        	->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_13', __( 'Gambar Menu 13' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_13', __( 'Text Menu 13' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_13', __( 'URL Menu 13' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_13', __( 'Keterangan Menu 13' ) )
		        	->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_14', __( 'Gambar Menu 14' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_14', __( 'Text Menu 14' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_14', __( 'URL Menu 14' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_14', __( 'Keterangan Menu 14' ) )
		        	->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_15', __( 'Gambar Menu 15' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_15', __( 'Text Menu 15' ) )
        			->set_default_value('...'),
		        Field::make( 'text', 'crb_satset_menu_url_15', __( 'URL Menu 15' ) )
        			->set_default_value('#'),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_15', __( 'Keterangan Menu 15' ) )
		        	->set_default_value('keterangan'),
		        Field::make( 'image', 'crb_satset_menu_logo_16', __( 'Gambar Menu 16' ) )
		        	->set_value_type('url'),
		        Field::make( 'text', 'crb_satset_menu_text_16', __( 'Text Menu 16' ) )
        			->set_default_value('Tanggapan Publik'),
		        Field::make( 'text', 'crb_satset_menu_url_16', __( 'URL Menu 16' ) )
        			->set_default_value($tanggapan_publik['url']),
		        Field::make( 'rich_text', 'crb_satset_menu_keterangan_16', __( 'Keterangan Menu 16' ) )
		        	->set_default_value('keterangan')
		    ) );

		Container::make( 'theme_options', __( 'Google Maps' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
	        	Field::make( 'map', 'crb_google_map_center_satset', 'Lokasi default Google Maps' ),
	        	Field::make( 'text', 'crb_google_map_id', 'ID google map' )
	        		->set_default_value('118b4b0052053d3a')
	        		->set_help_text('Referensi untuk untuk membuat ID Google Maps <a href="https://youtu.be/tAR63GBwk90" target="blank">https://youtu.be/tAR63GBwk90</a>'),
	        	Field::make( 'text', 'crb_google_api_satset', 'Google Maps APIKEY' )
	        		->set_default_value('AIzaSyDBrDSUIMFDIleLOFUUXf1wFVum9ae3lJ0')
	        		->set_help_text('Referensi untuk menampilkan google map <a href="https://developers.google.com/maps/documentation/javascript/examples/map-simple" target="blank">https://developers.google.com/maps/documentation/javascript/examples/map-simple</a>. Referensi untuk manajemen layer di Google Maps <a href="https://youtu.be/tAR63GBwk90" target="blank">https://youtu.be/tAR63GBwk90</a>'),
	        	Field::make( 'color', 'crb_warna_p3ke_satset', 'Warna garis P3KE' )
	        		->set_default_value('#00cc00'),
	        	Field::make( 'image', 'crb_icon_p3ke_satset', 'Icon keluarga P3KE' )
	        		->set_value_type('url')
        			->set_default_value(SATSET_PLUGIN_URL.'public/images/lokasi.png'),
	        	Field::make( 'color', 'crb_warna_stanting_satset', 'Warna garis stanting' )
	        		->set_default_value('#CC0003'),
	        	Field::make( 'image', 'crb_icon_stanting_satset', 'Icon anak stanting' )
	        		->set_value_type('url')
        			->set_default_value(SATSET_PLUGIN_URL.'public/images/lokasi.png'),
	        	Field::make( 'color', 'crb_warna_dtks_satset', 'Warna garis DTKS' )
	        		->set_default_value('#005ACC'),
	        	Field::make( 'image', 'crb_icon_dtks_satset', 'Icon dtks' )
	        		->set_value_type('url')
        			->set_default_value(SATSET_PLUGIN_URL.'public/images/lokasi.png'),
	        	Field::make( 'image', 'crb_icon_desa_satset', 'Icon desa' )
	        		->set_value_type('url')
        			->set_default_value(SATSET_PLUGIN_URL.'public/images/lokasi.png'),
	        	Field::make( 'image', 'crb_icon_kecamatan_satset', 'Icon kecamatan' )
	        		->set_value_type('url')
        			->set_default_value(SATSET_PLUGIN_URL.'public/images/lokasi.png')
	        ) );

		Container::make( 'theme_options', __( 'Data P3KE' ) )
		    ->set_page_parent( $basic_options_container )
		    ->add_fields($this->get_ajax_field(array('type' => 'data_p3ke')) )

		   ->add_fields(array( Field::make( 'html', 'crb_p3ke_upload_html' )
		            ->set_html( '<h3>Import EXCEL data P3KE</h3>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePickedSatset(event);"><br>Contoh format file excel untuk <b>Kepala Keluarga P3KE</b> bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_p3ke.xlsx">download di sini</a>.<br>Contoh format file excel <b>Anggota Keluarga P3KE</b> bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_p3ke_keluarga.xlsx">download di sini</a>.<br>Sheet file excel yang akan diimport harus diberi nama <b>data</b>. Untuk kolom nilai angka ditulis tanpa tanda titik.' ),
		        Field::make( 'html', 'crb_p3ke_satset' )
		            ->set_html( 'Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea>' ),
		        Field::make( 'html', 'crb_p3ke_save_button' )
		            ->set_html( '<a onclick="import_excel_p3ke(); return false" href="javascript:void(0);" class="button button-primary">Import P3KE Kepala Keluarga</a><a style="margin-left: 20px;" onclick="import_excel_p3ke(1); return false" href="javascript:void(0);" class="button button-primary">Import P3KE Anggota Keluarga</a>' )));	

		// Container::make( 'theme_options', __( 'Data P3KE' ) )
		// 	->set_page_parent( $basic_options_container )
		// 	->add_fields( array(
		//     	Field::make( 'html', 'crb_p3ke_hide_sidebar' )
		//         	->set_html( '
		//         		<style>
		//         			.postbox-container { display: none; }
		//         			#poststuff #post-body.columns-2 { margin: 0 !important; }
		//         		</style>
		//         	' ), 
		// 	Field::make( 'html', 'crb_satset_halaman_terkait_p3ke' )
		//         	->set_html( '
		// 			<h5>HALAMAN TERKAIT</h5>
	    //         	<ol>
	    //         		<li><a target="_blank" href="'.$management_data_p3ke['url'].'">'.$management_data_p3ke['title'].'</a></li>
	    //         		<li><a target="_blank" href="'.$management_data_p3ke_anggota_keluarga['url'].'">'.$management_data_p3ke_anggota_keluarga['title'].'</a></li>
	    //         	</ol>
		//         	' ),
		//         Field::make( 'html', 'crb_p3ke_upload_html' )
	    //         	->set_html( '<h3>Import EXCEL data P3KE</h3>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePickedSatset(event);"><br>Contoh format file excel untuk <b>Kepala Keluarga P3KE</b> bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_p3ke.xlsx">download di sini</a>.<br>Contoh format file excel <b>Anggota Keluarga P3KE</b> bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_p3ke_keluarga.xlsx">download di sini</a>.<br>Sheet file excel yang akan diimport harus diberi nama <b>data</b>. Untuk kolom nilai angka ditulis tanpa tanda titik.' ),
		//         Field::make( 'html', 'crb_p3ke_satset' )
	    //         	->set_html( 'Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea>' ),
		//         Field::make( 'html', 'crb_p3ke_save_button' )
	    //         	->set_html( '<a onclick="import_excel_p3ke(); return false" href="javascript:void(0);" class="button button-primary">Import P3KE Kepala Keluarga</a><a style="margin-left: 20px;" onclick="import_excel_p3ke(1); return false" href="javascript:void(0);" class="button button-primary">Import P3KE Anggota Keluarga</a>' )
	    //     ) );

		Container::make( 'theme_options', __( 'Data Stunting' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
		    	Field::make( 'html', 'crb_stunting_hide_sidebar' )
		        	->set_html( '
		        		<style>
		        			.postbox-container { display: none; }
		        			#poststuff #post-body.columns-2 { margin: 0 !important; }
		        		</style>
		        	' ),
		        Field::make( 'html', 'crb_satset_halaman_terkait_p3ke' )
		        	->set_html( '
					<h5>HALAMAN TERKAIT</h5>
	            	<ol>
	            		<li><a target="_blank" href="'.$management_data_stunting['url'].'">'.$management_data_stunting['title'].'</a></li>
	            	</ol>
		        	' ),
		        Field::make( 'html', 'crb_stunting_upload_html' )
	            	->set_html( '<h3>Import EXCEL data stunting</h3>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePickedSatset(event);"><br>Contoh format file excel bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_stunting.xlsx">download di sini</a>. Sheet file excel yang akan diimport harus diberi nama <b>data</b>. Untuk kolom nilai angka ditulis tanpa tanda titik.' ),
		        Field::make( 'html', 'crb_stunting_satset' )
	            	->set_html( 'Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea>' ),
		        Field::make( 'html', 'crb_stunting_save_button' )
	            	->set_html( '<a onclick="import_excel_stunting(); return false" href="javascript:void(0);" class="button button-primary">Import WP</a>' )
	        ) );

		Container::make( 'theme_options', __( 'Data TBC' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
		    	Field::make( 'html', 'crb_tbc_hide_sidebar' )
		        	->set_html( '
		        		<style>
		        			.postbox-container { display: none; }
		        			#poststuff #post-body.columns-2 { margin: 0 !important; }
		        		</style>
		        	' ),
		        Field::make( 'html', 'crb_satset_halaman_terkait_tbc' )
		        	->set_html( '
					<h5>HALAMAN TERKAIT</h5>
	            	<ol>
	            		<li><a target="_blank" href="'.$management_data_tbc['url'].'">'.$management_data_tbc['title'].'</a></li>
	            	</ol>
		        	' ),
		        Field::make( 'html', 'crb_tbc_upload_html' )
	            	->set_html( '<h3>Import EXCEL data tbc</h3>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePickedSatset(event);"><br>Contoh format file excel bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_tbc.xlsx">download di sini</a>. Sheet file excel yang akan diimport harus diberi nama <b>data</b>. Untuk kolom nilai angka ditulis tanpa tanda titik.' ),
		        Field::make( 'html', 'crb_tbc_satset' )
	            	->set_html( 'Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea>' ),
		        Field::make( 'html', 'crb_tbc_save_button' )
	            	->set_html( '<a onclick="import_excel_tbc(); return false" href="javascript:void(0);" class="button button-primary">Import WP</a>' )
	        ) );

		Container::make( 'theme_options', __( 'Data RTLH' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
		    	Field::make( 'html', 'crb_rtlh_hide_sidebar' )
		        	->set_html( '
		        		<style>
		        			.postbox-container { display: none; }
		        			#poststuff #post-body.columns-2 { margin: 0 !important; }
		        		</style>
		        	' ),
		        Field::make( 'html', 'crb_satset_halaman_terkait_rtlh' )
		        	->set_html( '
					<h5>HALAMAN TERKAIT</h5>
	            	<ol>
	            		<li><a target="_blank" href="'.$management_data_rtlh['url'].'">'.$management_data_rtlh['title'].'</a></li>
	            	</ol>
		        	' ),
		        Field::make( 'html', 'crb_rtlh_upload_html' )
	            	->set_html( '<h3>Import EXCEL data rtlh</h3>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePickedSatset(event);"><br>Contoh format file excel bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_rtlh.xlsx">download di sini</a>. Sheet file excel yang akan diimport harus diberi nama <b>data</b>. Untuk kolom nilai angka ditulis tanpa tanda titik.' ),
		        Field::make( 'html', 'crb_rtlh_satset' )
	            	->set_html( 'Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea>' ),
		        Field::make( 'html', 'crb_rtlh_save_button' )
	            	->set_html( '<a onclick="import_excel_rtlh(); return false" href="javascript:void(0);" class="button button-primary">Import WP</a>' )
	        ) );

		Container::make( 'theme_options', __( 'Data DTKS' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
		        Field::make( 'text', 'crb_dtks_satset_server', 'Alamat server WP-SIKS' )
		        	->set_default_value(site_url().'/wp-admin/admin-ajax.php'),
		        Field::make( 'text', 'crb_dtks_satset_api_key', 'API KEY WP-SIKS' ),
		        Field::make( 'html', 'crb_dtks_save_button' )
	            	->set_html( '<div id="pilih-desa"></div><div style="text-align: center; margin: 10px;"><a onclick="get_data_dtks(); return false" href="javascript:void(0);" class="button button-primary">Singkronisasi Data</a></div>' )
	        ) );
		Container::make( 'theme_options', __( 'Data Desa' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
			Field::make( 'html', 'crb_satset_halaman_terkait_desa' )
		        	->set_html( '
					<h5>HALAMAN TERKAIT</h5>
	            	<ol>
	            		<li><a target="_blank" href="'.$management_data_batas_desa['url'].'">'.$management_data_batas_desa['title'].'</a></li>
	            	</ol>
		        	' )
	        ) );
	    Container::make( 'theme_options', __( 'Data Kecamatan' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
			Field::make( 'html', 'crb_satset_halaman_terkait_kecamatan' )
		        	->set_html( '
					<h5>HALAMAN TERKAIT</h5>
	            	<ol>
	            		<li><a target="_blank" href="'.$management_data_batas_kecamatan['url'].'">'.$management_data_batas_kecamatan['title'].'</a></li>
	            	</ol>
		        	' )
	        ) );
	}

	function import_excel_p3ke(){ 
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil import excel!'
		);

		if (!empty($_POST)) {
			$table_data = 'data_p3ke';
			if($_POST['tipe_data'] == 1){
				$table_data = 'data_p3ke_anggota_keluarga';
			}
			// Jika update_active dan page = 1, set semua data active = 0
			if (!empty($_POST['update_active']) && $_POST['page'] == 1) {
				$wpdb->update($table_data, array('active' => 0));
			}
			
			$ret['data'] = array(
				'insert' => 0, 
				'update' => 0,
				'error' => array()
			);

			foreach ($_POST['data'] as $k => $data) {
				$newData = array();
				foreach ($data as $kk => $vv) {
					$newData[trim(preg_replace('/\s+/', ' ', $kk))] = trim(preg_replace('/\s+/', ' ', $vv));
				}

				// Logika untuk kepala keluarga (tipe_data = 0)
				if ($_POST['tipe_data'] == 0) {
					$data_db = array(
						'id_p3ke' => $newData['id_p3ke'],
						'provinsi' => $newData['provinsi'],
						'kabkot' => $newData['kabkot'],
						'kecamatan' => $newData['kecamatan'],
						'desa' => $newData['desa'],
						'kode_kemendagri' => $newData['kode_kemendagri'],
						'jenis_desil' => $newData['jenis_desil'],
						'alamat' => $newData['alamat'],
						'kepala_keluarga' => $newData['kepala_keluarga'],
						'nik' => $newData['nik'],
						'padan_dukcapil' => $newData['padan_dukcapil'],
						'jenis_kelamin' => $newData['jenis_kelamin'],
						'tanggal_lahir' => $newData['tanggal_lahir'],
						'pekerjaan' => $newData['pekerjaan'],
						'pendidikan' => $newData['pendidikan'],
						'rumah' => $newData['rumah'],
						'punya_tabungan' => $newData['punya_tabungan'],
						'jenis_atap' => $newData['jenis_atap'],
						'jenis_dinding' => $newData['jenis_dinding'],
						'jenis_lantai' => $newData['jenis_lantai'],
						'sumber_penerangan' => $newData['sumber_penerangan'],
						'bahan_bakar_memasak' => $newData['bahan_bakar_memasak'],
						'sumber_air_minum' => $newData['sumber_air_minum'],
						'fasilitas_bab' => $newData['fasilitas_bab'],
						'penerima_bpnt' => $newData['penerima_bpnt'],
						'penerima_bpum' => $newData['penerima_bpum'],
						'penerima_bst' => $newData['penerima_bst'],
						'penerima_pkh' => $newData['penerima_pkh'],
						'penerima_sembako' => $newData['penerima_sembako'],
						'resiko_stunting' => $newData['resiko_stunting'],
						'active' => 1,
						'update_at' => current_time('mysql'),
						'tahun_anggaran' => $newData['tahun_anggaran']
					);
				} 
				// Logika untuk anggota keluarga (tipe_data = 1)
				else {
					$data_db = array(
						'id_p3ke' => $newData['id_p3ke'],
						'provinsi' => $newData['provinsi'],
						'kabkot' => $newData['kabkot'],
						'kecamatan' => $newData['kecamatan'],
						'desa' => $newData['desa'],
						'kode_kemendagri' => $newData['kode_kemendagri'],
						'jenis_desil' => $newData['jenis_desil'],
						'alamat' => $newData['alamat'],
						'id_individu' => $newData['id_individu'],
						'nama' => $newData['nama'],
						'nik' => $newData['nik'],
						'padan_dukcapil' => $newData['padan_dukcapil'],
						'jenis_kelamin' => $newData['jenis_kelamin'],
						'hubungan_keluarga' => $newData['hubungan_keluarga'],
						'tanggal_lahir' => $newData['tanggal_lahir'],
						'status_kawin' => $newData['status_kawin'],
						'pekerjaan' => $newData['pekerjaan'],
						'pendidikan' => $newData['pendidikan'],
						'usia_dibawah_7' => $newData['usia_dibawah_7'],
						'usia_7_12' => $newData['usia_7_12'],
						'usia_13_15' => $newData['usia_13_15'],
						'usia_16_18' => $newData['usia_16_18'],
						'usia_19_21' => $newData['usia_19_21'],
						'usia_22_59' => $newData['usia_22_59'],
						'usia_60_keatas' => $newData['usia_60_keatas'],
						'penerima_bpnt' => $newData['penerima_bpnt'],
						'penerima_bpum' => $newData['penerima_bpum'],
						'penerima_bst' => $newData['penerima_bst'],
						'penerima_pkh' => $newData['penerima_pkh'],
						'penerima_sembako' => $newData['penerima_sembako'],
						'resiko_stunting' => $newData['resiko_stunting'],
						'active' => 1,
						'update_at' => current_time('mysql'),
						'tahun_anggaran' => $newData['tahun_anggaran']
					);
				}

				if (empty($newData['nik'])) {
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT id FROM $table_data 
						WHERE kode_kemendagri = %s AND id_p3ke = %s AND nik IS NULL",
						$newData['kode_kemendagri'], $newData['id_p3ke']
					));
				} else {
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT id FROM $table_data 
						WHERE kode_kemendagri = %s AND id_p3ke = %s AND nik = %s",
						$newData['kode_kemendagri'], $newData['id_p3ke'], $newData['nik']
					));
				}

				if (empty($cek_id)) {
					$wpdb->insert($table_data, $data_db);
					$ret['data']['insert']++;
				} else {
					$wpdb->update($table_data, $data_db, array('id' => $cek_id));
					$ret['data']['update']++;
				}

				if (!empty($wpdb->last_error)) {
					$ret['data']['error'][] = array($wpdb->last_error, $data_db);
				};
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}

		die(json_encode($ret));
	}


	function import_excel_stunting(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil import excel!'
		);
		if (!empty($_POST)) {
			$ret['data'] = array(
				'insert' => 0, 
				'update' => 0,
				'error' => array()
			);
			foreach ($_POST['data'] as $k => $data) {
				$newData = array();
				foreach($data as $kk => $vv){
					$newData[trim(preg_replace('/\s+/', ' ', $kk))] = trim(preg_replace('/\s+/', ' ', $vv));
				}
				$data_db = array(
					'nik' => $newData['nik'],
				    'nama' => $newData['nama'],
				    'jenis_kelamin' => $newData['jenis_kelamin'],
				    'tanggal_lahir' => $newData['tanggal_lahir'],
				    'bb_lahir' => $newData['bb_lahir'],
				    'tb_lahir' => $newData['tb_lahir'],
				    'nama_ortu' => $newData['nama_ortu'],
				    'provinsi' => $newData['provinsi'],
				    'kabkot' => $newData['kabkot'],
				    'kecamatan' => $newData['kecamatan'],
				    'puskesmas' => $newData['puskesmas'],
				    'desa' => $newData['desa'],
				    'posyandu' => $newData['posyandu'],
				    'rt' => $newData['rt'],
				    'rw' => $newData['rw'],
				    'alamat' => $newData['alamat'],
				    'usia_saat_ukur' => $newData['usia_saat_ukur'],
				    'tanggal_pengukuran' => $newData['tanggal_pengukuran'],
				    'berat' => $newData['berat'],
				    'tinggi' => $newData['tinggi'],
				    'lingkar_lengan_atas' => $newData['lingkar_lengan_atas'],
				    'bb_per_u' => $newData['bb_per_u'],
				    'zs_bb_per_u' => $newData['zs_bb_per_u'],
				    'tb_per_u' => $newData['tb_per_u'],
				    'zs_tb_per_u' => $newData['zs_tb_per_u'],
				    'bb_per_tb' => $newData['bb_per_tb'],
				    'zs_bb_per_tb' => $newData['zs_bb_per_tb'],
				    'naik_berat_badan' => $newData['naik_berat_badan'],
				    'pmt_diterima_per_kg' => $newData['pmt_diterima_per_kg'],
				    'jml_vit_a' => $newData['jml_vit_a'],
				    'kpsp' => $newData['kpsp'],
				    'kia' => $newData['kia'],
				);
				$wpdb->last_error = "";
				if(empty($newData['nik'])){
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_stunting 
						where nama=%s 
							and provinsi=%s
							and kabkot=%s
							and kecamatan=%s
							and desa=%s
							and nik is null"
						, $newData['nama'], $newData['provinsi'], $newData['kabkot'], $newData['kecamatan'], $newData['desa']));
				}else{
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_stunting 
						where nama=%s 
							and provinsi=%s
							and kabkot=%s
							and kecamatan=%s
							and desa=%s
							and nik=%s"
						, $newData['nama'], $newData['provinsi'], $newData['kabkot'], $newData['kecamatan'], $newData['desa'], $newData['nik']));
				}
				if(empty($cek_id)){
					$wpdb->insert("data_stunting", $data_db);
					$ret['data']['insert']++;
				}else{
					$wpdb->update("data_stunting", $data_db, array(
						"id" => $cek_id
					));
					$ret['data']['update']++;
				}
				if(!empty($wpdb->last_error)){
					$ret['data']['error'][] = array($wpdb->last_error, $data_db);
				};

			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	function import_excel_tbc(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil import excel!'
		);
		if (!empty($_POST)) {
			$ret['data'] = array(
				'insert' => 0, 
				'update' => 0,
				'error' => array()
			);
			foreach ($_POST['data'] as $k => $data) {
				$newData = array();
				foreach($data as $kk => $vv){
					$newData[trim(preg_replace('/\s+/', ' ', $kk))] = trim(preg_replace('/\s+/', ' ', $vv));
				}
				$alamat = explode(', ', $newData['alamat']);
				foreach($alamat as $i => $v){
					$alamat[$i] = str_replace(
						array('KEC. ', 'KAB. '),
						array('', ''),
						strtoupper($v)
					);
				}
				$jml = count($alamat);
				$provinsi = '';
				$kabkot = '';
				$kecamatan = '';
				$desa = '';
				if($jml >= 4){
					$provinsi = $alamat[$jml-1];
					$kabkot = $alamat[$jml-2];
					$kecamatan = $alamat[$jml-3];
					$desa = $alamat[$jml-4];
				}
				$data_db = array(
				    'provinsi' => $provinsi,
				    'kabkot' => $kabkot,
				    'kecamatan' => $kecamatan,
				    'desa' => $desa,
				    'tanggal_register' => $newData['tanggal_register'],
				    'no_reg_fasyankes' => $newData['no_reg_fasyankes'],
				    'no_reg_kabkot' => $newData['no_reg_kabkot'],
				    'nik' => $newData['nik'],
				    'nama' => $newData['nama'],
				    'umur' => $newData['umur'],
				    'jenis_kelamin' => $newData['jenis_kelamin'],
				    'alamat' => $newData['alamat'],
				    'pindahan_dari_fasyankes' => $newData['pindahan_dari_fasyankes'],
				    'tindak_lanjut' => $newData['tindak_lanjut'],
				    'tanggal_mulai_pengobatan' => $newData['tanggal_mulai_pengobatan'],
				    'hasil_akhir_pengobatan' => $newData['hasil_akhir_pengobatan'],
				    'status_pengobatan' => $newData['status_pengobatan'],
				    'keterangan' => $newData['keterangan'],
				);
				// print_r($data_db); die();
				$wpdb->last_error = "";
				if(empty($newData['nik'])){
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_tbc 
						where nama=%s 
							and provinsi=%s
							and kabkot=%s
							and kecamatan=%s
							and desa=%s
							and nik is null"
						, $newData['nama'], $newData['provinsi'], $newData['kabkot'], $newData['kecamatan'], $newData['desa']));
				}else{
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_tbc 
						where nama=%s 
							and provinsi=%s
							and kabkot=%s
							and kecamatan=%s
							and desa=%s
							and nik=%s"
						, $newData['nama'], $newData['provinsi'], $newData['kabkot'], $newData['kecamatan'], $newData['desa'], $newData['nik']));
				}
				if(empty($cek_id)){
					$wpdb->insert("data_tbc", $data_db);
					$ret['data']['insert']++;
				}else{
					$wpdb->update("data_tbc", $data_db, array(
						"id" => $cek_id
					));
					$ret['data']['update']++;
				}
				if(!empty($wpdb->last_error)){
					$ret['data']['error'][] = array($wpdb->last_error, $data_db);
				};

			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	function import_excel_rtlh(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil import excel!'
		);
		if (!empty($_POST)) {
			$ret['data'] = array(
				'insert' => 0, 
				'update' => 0,
				'error' => array()
			);
			foreach ($_POST['data'] as $k => $data) {
				$newData = array();
				foreach($data as $kk => $vv){
					$newData[trim(preg_replace('/\s+/', ' ', $kk))] = trim(preg_replace('/\s+/', ' ', $vv));
				}
				$data_db = array(
				    'provinsi' => $newData['provinsi'],
				    'kabkot' => $newData['kabkot'],
				    'kecamatan' => $newData['kecamatan'],
				    'desa' => $newData['desa'],
				    'nik' => $newData['nik'],
				    'nama' => $newData['nama'],
				    'alamat' => $newData['alamat'],
				    'rw' => $newData['rw'],
				    'rt' => $newData['rt'],
				    'nilai_bantuan' => $newData['nilai_bantuan'],
				    'lpj' => $newData['lpj'],
				    'tgl_lpj' => $newData['tgl_lpj'],
				    'sumber_dana' => $newData['sumber_dana'],
				    'tahun_anggaran' => $newData['tahun_anggaran'],
				);
				// print_r($data_db); die();
				$wpdb->last_error = "";
				if(empty($newData['nik'])){
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_rtlh 
						where nama=%s 
							and provinsi=%s
							and kabkot=%s
							and kecamatan=%s
							and desa=%s
							and tahun_anggaran=%s
							and nik is null"
						, $newData['nama'], $newData['provinsi'], $newData['kabkot'], $newData['kecamatan'], $newData['desa'], $newData['tahun_anggaran']));
				}else{
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_rtlh 
						where nama=%s 
							and provinsi=%s
							and kabkot=%s
							and kecamatan=%s
							and desa=%s
							and tahun_anggaran=%s
							and nik=%s"
						, $newData['nama'], $newData['provinsi'], $newData['kabkot'], $newData['kecamatan'], $newData['desa'], $newData['tahun_anggaran'], $newData['nik']));
				}
				if(empty($cek_id)){
					$wpdb->insert("data_rtlh", $data_db);
					$ret['data']['insert']++;
				}else{
					$wpdb->update("data_rtlh", $data_db, array(
						"id" => $cek_id
					));
					$ret['data']['update']++;
				}
				if(!empty($wpdb->last_error)){
					$ret['data']['error'][] = array($wpdb->last_error, $data_db);
				};

			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	function get_data_desa(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil singkronisasi data DTKS dari WP-SIKS!'
		);
		if (!empty($_POST)) {
			$prov = get_option('_crb_prov_satset');
			$where = " provinsi='$prov'";
			$kab = get_option('_crb_kab_satset');
			if(!empty($kab)){
				$where .= " and kab_kot='$kab'";
			}
			$data_desa = $wpdb->get_results("
				SELECT 
					provno,
					kabkotno,
					kecno,
					desano,
					provinsi,
					kab_kot,
					kecamatan,
					desa 
				FROM data_batas_desa 
				WHERE $where
					AND kecno is not null
			", ARRAY_A);
			$data_kec = array();

			foreach($data_desa as $desa){
				if(empty($data_kec[$desa['kecamatan']])){
					$data_kec[$desa['kecamatan']] = array(
						'kec' => $desa,
						'desa' => array()
					);
				}
				$data_kec[$desa['kecamatan']]['desa'][] = $desa;
			}
			$ret['data'] = $data_kec;
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	function sql_migrate_satset(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil menjalankan SQL migrate!'
		);
		
		$file = 'table.sql';
		$ret['value'] = $file.' (tgl: '.date('Y-m-d H:i:s').')';
		$path = SATSET_PLUGIN_PATH.'/'.$file;
		if(file_exists($path)){
			$sql = file_get_contents($path);
			$ret['sql'] = $sql;
			require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			$wpdb->hide_errors();
			$rows_affected = dbDelta($sql);
			if(empty($rows_affected)){
				$ret['status'] = 'error';
				$ret['message'] = $wpdb->last_error;
			}else{
				$ret['message'] = implode(' | ', $rows_affected);
			}
			if($ret['status'] == 'success'){
				$ret['version'] = $this->version;
				update_option('_last_update_sql_migrate_satset', $ret['value']);
				update_option('_wp_sipd_db_version_satset', $this->version);
			}

			if(
				!empty($_POST) 
				&& !empty($_POST['migrate'])
				&& $_POST['migrate'] == 1
			){
				$path = SATSET_PLUGIN_PATH.'/migrate/data_batas_desa.sql';
				$sql = file_get_contents($path);
				$wpdb->hide_errors();
				$res = $wpdb->query($sql);
				if(empty($res)){
					$ret['status_desa'] = 'error';
					$ret['message_desa'] = $wpdb->last_error;
				}else{
					$ret['message_desa'] = $res;
				}
				$ret['sql_desa'] = $sql;

				$path = SATSET_PLUGIN_PATH.'/migrate/data_batas_kecamatan.sql';
				$sql = file_get_contents($path);
				$wpdb->hide_errors();
				$res = $wpdb->query($sql);
				if(empty($res)){
					$ret['status_kecamatan'] = 'error';
					$ret['message_kecamatan'] = $wpdb->last_error;
				}else{
					$ret['message_kecamatan'] = $res;
				}
				$ret['sql_kec'] = $sql;
			}
		}else{
			$ret['status'] = 'error';
			$ret['message'] = 'File '.$path.' tidak ditemukan!';
		}
		die(json_encode($ret));
	}

	public function get_data_dtks(){
		global $wpdb;
		$ret = array(
			'status'	=> 'success',
			'message'	=> 'Berhasil singkronisasi data DTKS dari WP-SIKS!'
		);
		if (!empty($_POST)) {
			$url = get_option('_crb_dtks_satset_server');
			$api_key = get_option('_crb_dtks_satset_api_key');
			if(empty($url)){
				$ret['status'] = 'error';
				$ret['message'] = 'Url server WP-SIKS tidak boleh kosong!';
			}else if(empty($api_key)){
				$ret['status'] = 'error';
				$ret['message'] = 'API KEY WP-SIKS tidak boleh kosong!';
			}
			if($ret['status'] != 'error'){
				$id_desa = $_POST['desa'];
				$ret_dtks = $this->functions->curl_post(array(
					'url' => $url,
					'data' => array(
						'action' => 'get_data_dtks',
						'kecamatan' => $_POST['desa']['kecamatan'],
						'desa' => $_POST['desa']['desa_kelurahan'],
						'api_key' => $api_key
					)
				));
				$dtks = json_decode($ret_dtks, true);
				if($dtks['status'] == 'success'){
					foreach($dtks['data'] as $orang){
						$cek_id = $wpdb->get_var($wpdb->prepare("
							SELECT
								id
							FROM data_dtks_satset
							WHERE id_desa = %s
								AND Nama = %s
								AND verifyid = %s
						", $orang['id_desa'], $orang['Nama'], $orang['verifyid']));

						$opsi = array(
							'provinsi' => $_POST['desa']['provinsi'],
							'kabkot' => $_POST['desa']['kabkot'],
							'kecamatan' => strtoupper($orang['kecamatan']),
							'desa' => strtoupper($orang['desa_kelurahan']),
							'desa_kelurahan' => $orang['desa_kelurahan'],
							'id_kec' => $orang['id_kec'],
							'id_desa' => $orang['id_desa'],
							'Alamat' => $orang['Alamat'],
							'BLT' => $orang['BLT'],
							'BLT_BBM' => $orang['BLT_BBM'],
							'BNPT_PPKM' => $orang['BNPT_PPKM'],
							'BPNT' => $orang['BPNT'],
							'BST' => $orang['BST'],
							'FIRST_SK' => $orang['FIRST_SK'],
							'NIK' => $orang['NIK'],
							'NOKK' => $orang['NOKK'],
							'Nama' => $orang['Nama'],
							'PBI' => $orang['PBI'],
							'PKH' => $orang['PKH'],
							'RUTILAHU' => $orang['RUTILAHU'],
							'SEMBAKO_ADAPTIF' => $orang['SEMBAKO_ADAPTIF'],
							'checkBtnHamil' => $orang['checkBtnHamil'],
							'checkBtnVerifMeninggal' => $orang['checkBtnVerifMeninggal'],
							'counter' => $orang['counter'],
							'deleted_label' => $orang['deleted_label'],
							'idsemesta' => $orang['idsemesta'],
							'isAktifHamil' => $orang['isAktifHamil'],
							'is_btn_dapodik' => $orang['is_btn_dapodik'],
							'is_btn_hidupkan' => $orang['is_btn_hidupkan'],
							'is_btn_padankan' => $orang['is_btn_padankan'],
							'is_nonaktif' => $orang['is_nonaktif'],
							'keterangan_disabilitas' => json_encode($orang['keterangan_disabilitas']),
							'keterangan_meninggal' => $orang['keterangan_meninggal'],
							'masih_hidup_label' => $orang['masih_hidup_label'],
							'padankan_at' => $orang['padankan_at'],
							'periode_blt' => $orang['periode_blt'],
							'periode_blt_bbm' => $orang['periode_blt_bbm'],
							'periode_bpnt' => $orang['periode_bpnt'],
							'periode_bpnt_ppkm' => $orang['periode_bpnt_ppkm'],
							'periode_bst' => $orang['periode_bst'],
							'periode_pbi' => $orang['periode_pbi'],
							'periode_pkh' => $orang['periode_pkh'],
							'periode_rutilahu' => $orang['periode_rutilahu'],
							'periode_sembako_adaptif' => $orang['periode_sembako_adaptif'],
							'verifyid' => $orang['verifyid'],
							'update_at' => date('Y-m-d H:i:s'),
							'active' => 1
						);
						if(empty($cek_id)){
							$wpdb->insert('data_dtks_satset', $opsi);
						}else{
							$wpdb->update('data_dtks_satset', $opsi, array(
								'id' => $cek_id
							));
						}
					}
				}else{
					$ret['status'] = 'error';
					$ret['message'] = 'data tidak ditemukan di WP-SIKS!';
					$ret['data'] = $ret_dtks;
				}
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	function get_data_unit_wpsipd_satset()
	{
		global $wpdb;

		if (empty($_POST['server'])) {
			$data = array(
				'status' => 'error',
				'message' => 'URL Server Tidak Boleh Kosong'
			);
			$response = json_encode($data);
			die($response);
		} else if (empty($_POST['tahun_anggaran'])) {
			$data = array(
				'status' => 'error',
				'message' => 'Tahun Tidak Boleh Kosong'
			);
			$response = json_encode($data);
			die($response);
		} else if (empty($_POST['api_key'])) {
			$data = array(
				'status' => 'error',
				'message' => 'API Key Tidak Boleh Kosong'
			);
			$response = json_encode($data);
			die($response);
		}

		// data to send in our API request
		$api_params = array(
			'action' => 'get_skpd',
			'api_key'	=> $_POST['api_key'],
			'tahun_anggaran' => $_POST['tahun_anggaran']
		);

		$response = wp_remote_post($_POST['server'], array('timeout' => 1000, 'sslverify' => false, 'body' => $api_params));

		$response = wp_remote_retrieve_body($response);

		$data = json_decode($response);

		$satset_data_unit = $data->data;

		if ($data->status == 'success' && !empty($satset_data_unit)) {
			$wpdb->update('satset_data_unit', array('active' => 0), array('tahun_anggaran' => $api_params['tahun_anggaran']));
			foreach ($satset_data_unit as $vdata) {
				$cek = $wpdb->get_var($wpdb->prepare(
					'
					select 
						id 
					from satset_data_unit 
					where id_skpd = %d
						and tahun_anggaran = %d',
					$vdata->id_skpd,
					$vdata->tahun_anggaran
				));
				$opsi = array(
					'id_setup_unit' => $vdata->id_setup_unit,
					'id_unit' => $vdata->id_unit,
					'is_skpd' => $vdata->is_skpd,
					'kode_skpd' => $vdata->kode_skpd,
					'kunci_skpd' => $vdata->kunci_skpd,
					'nama_skpd' => $vdata->nama_skpd,
					'posisi' => $vdata->posisi,
					'status' => $vdata->status,
					'id_skpd' => $vdata->id_skpd,
					'bidur_1' => $vdata->bidur_1,
					'bidur_2' => $vdata->bidur_2,
					'bidur_3' => $vdata->bidur_3,
					'idinduk' => $vdata->idinduk,
					'ispendapatan' => $vdata->ispendapatan,
					'isskpd' => $vdata->isskpd,
					'kode_skpd_1' => $vdata->kode_skpd_1,
					'kode_skpd_2' => $vdata->kode_skpd_2,
					'kodeunit' => $vdata->kodeunit,
					'komisi' => $vdata->komisi,
					'namabendahara' => $vdata->namabendahara,
					'namakepala' => $vdata->namakepala,
					'namaunit' => $vdata->namaunit,
					'nipbendahara' => $vdata->nipbendahara,
					'nipkepala' => $vdata->nipkepala,
					'pangkatkepala' => $vdata->pangkatkepala,
					'setupunit' => $vdata->setupunit,
					'statuskepala' => $vdata->statuskepala,
					'update_at' => $vdata->update_at,
					'tahun_anggaran' => $vdata->tahun_anggaran,
					'active' => $vdata->active
				);
				if (empty($cek)) {
					$wpdb->insert('satset_data_unit', $opsi);
				} else {
					$wpdb->update('satset_data_unit', $opsi, array('id' => $cek));
				}
			}
		}

		$response = json_encode($data);

		die($response);
	}


	function generate_user_satset()
	{
		global $wpdb;
		$ret = array();
		$ret['status'] = 'success';
		$ret['message'] = 'Berhasil Generate User Wordpress dari DB Lokal';
		if (!empty($_POST)) {
			if (!empty($_POST['api_key']) && $_POST['api_key'] == get_option('_crb_apikey_satset')) {
				$users_pa = $wpdb->get_results(
					$wpdb->prepare("
						SELECT 
						* 
						FROM satset_data_unit 
						WHERE active=1
						"),
					ARRAY_A
				);
				$update_pass = false;
				if (
					!empty($_POST['update_pass'])
					&& $_POST['update_pass'] == 'true'
				) {
					$update_pass = true;
				}
				if (!empty($users_pa)) {
					foreach ($users_pa as $k => $user) {
						$user['pass'] = $_POST['pass'];
						$user['loginname'] = $user['nipkepala'];
						$user['jabatan'] = $user['statuskepala'];
						$user['nama'] = $user['namakepala'];
						$user['id_sub_skpd'] = $user['id_skpd'];
						$user['nip'] = $user['nipkepala'];
						$this->gen_user_satset($user, $update_pass);
					}

					// admin bappeda
					$args = array(
						'role'    => 'admin_bappeda',
						'orderby' => 'user_nicename',
						'order'   => 'ASC'
					);
					$users_bappeda = get_users($args);
					$user_data = array();
					$user_data['pass'] = $_POST['pass'];
					$user_data['jabatan'] = 'admin_bappeda';
					if (empty($user_exist)) {
						$user_data['loginname'] = 'admin_perencanaan';
						$user_data['nama'] = 'Admin Perencanaan';
						$this->gen_user_satset($user_data, $update_pass);
					} else {
						foreach ($users_bappeda as $user_exist) {
							$user_data['loginname'] = $user_exist->user_login;
							$user_data['nama'] = $user_exist->display_name;
						}
						$this->gen_user_satset($user_data, $update_pass);
					}

					// admin review
					$args = array(
						'role'    => 'admin_panrb',
						'orderby' => 'user_nicename',
						'order'   => 'ASC'
					);
					$users_fanrb = get_users($args);
					$user_data = array();
					$user_data['pass'] = $_POST['pass'];
					$user_data['jabatan'] = 'admin_panrb';
					if (empty($user_exist)) {
						$user_data['loginname'] = 'admin_panrb';
						$user_data['nama'] = 'Admin Review';
						$this->gen_user_satset($user_data, $update_pass);
					} else {
						foreach ($users_fanrb as $user_exist) {
							$user_data['loginname'] = $user_exist->user_login;
							$user_data['nama'] = $user_exist->display_name;
						}
						$this->gen_user_satset($user_data, $update_pass);
					}

					// admin ortala
					$args = array(
						'role'    => 'admin_ortala',
						'orderby' => 'user_nicename',
						'order'   => 'ASC'
					);
					$users_ortala = get_users($args);
					$user_data = array();
					$user_data['pass'] = $_POST['pass'];
					$user_data['jabatan'] = 'admin_ortala';
					if (empty($user_exist)) {
						$user_data['loginname'] = 'admin_organisasi';
						$user_data['nama'] = 'Admin Organisasi';
						$this->gen_user_satset($user_data, $update_pass);
					} else {
						foreach ($users_ortala as $user_exist) {
							$user_data['loginname'] = $user_exist->user_login;
							$user_data['nama'] = $user_exist->display_name;
						}
						$this->gen_user_satset($user_data, $update_pass);
					}
				} else {
					$ret['status'] = 'error';
					$ret['message'] = 'Data user PA/KPA kosong. Harap lakukan singkronisasi data Perangkat Daerah dulu!';
				}
			} else {
				$ret['status'] = 'error';
				$ret['message'] = 'APIKEY tidak sesuai!';
			}
		} else {
			$ret['status'] = 'error';
			$ret['message'] = 'Format Salah!';
		}
		die(json_encode($ret));
	}

	function gen_user_satset($user = array(), $update_pass = false)
	{
		global $wpdb;
		if (!empty($user)) {
			$username = $user['loginname'];
			if (!empty($user['emailteks'])) {
				$email = $user['emailteks'];
			} else {
				$email = $username . '@sakiplocal.com';
			}
			$user['jabatan'] = strtolower($user['jabatan']);
			$role = get_role($user['jabatan']);
			if (empty($role)) {
				add_role($user['jabatan'], $user['jabatan'], array(
					'read' => true,
					'edit_posts' => false,
					'delete_posts' => false
				));
			}
			$insert_user = username_exists($username);
			if (!$insert_user) {
				$option = array(
					'user_login' => $username,
					'user_pass' => $user['pass'],
					'user_email' => $email,
					'first_name' => $user['nama'],
					'display_name' => $user['nama'],
					'role' => $user['jabatan']
				);
				$insert_user = wp_insert_user($option);

				if (is_wp_error($insert_user)) {
					return $insert_user;
				}
			} else {
				$user_meta = get_userdata($insert_user);
				if (!in_array($user['jabatan'], $user_meta->roles)) {
					$user_meta->add_role($user['jabatan']);
				}
			}

			if (!empty($update_pass)) {
				wp_set_password($user['pass'], $insert_user);
			}

			$meta = array(
				'description' => 'User dibuat dari generate sistem aplikasi WP-Eval-SAKIP'
			);
			if (!empty($user['nip'])) {
				$meta['nip'] = $user['nip'];
			}
			if (!empty($user['id_sub_skpd'])) {
				$skpd = $wpdb->get_var(
					$wpdb->prepare("
						SELECT nama_skpd 
						FROM satset_data_unit 
						WHERE id_skpd=" . $user['id_sub_skpd'] . " 
						  AND active=1")
				);
				$meta['_crb_nama_skpd'] = $skpd;
				$meta['_id_sub_skpd'] = $user['id_sub_skpd'];
			}
			if (!empty($user['iduser'])) {
				$meta['id_user_sipd'] = $user['iduser'];
			}
			foreach ($meta as $key => $val) {
				update_user_meta($insert_user, $key, $val);
			}
		}
	}

}
