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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/wp-satset-admin.js', array( 'jquery' ), $this->version, false );

	}

	function crb_attach_satset_options(){
		global $wpdb;

		$satset_homepage = $this->functions->generatePage(array(
			'nama_page' => 'Beranda / Homepage SATSET', 
			'content' => '[satset_homepage]',
        	'show_header' => 0,
        	'update' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$peta_satset = $this->functions->generatePage(array(
			'nama_page' => 'Peta Data Terpadu', 
			'content' => '[peta_satset]',
        	'show_header' => 0,
        	'update' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$peta_batas_desa = $this->functions->generatePage(array(
			'nama_page' => 'Peta Batas Desa', 
			'content' => '[peta_satset_desa]',
        	'show_header' => 0,
        	'update' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$peta_batas_kecamatan = $this->functions->generatePage(array(
			'nama_page' => 'Peta Batas Kecamatan', 
			'content' => '[peta_satset_kecamatan]',
        	'show_header' => 0,
        	'update' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$conversi_peta_satset = $this->functions->generatePage(array(
			'nama_page' => 'Conversi Data SHP ke GEOJSON', 
			'content' => '[conversi_peta_satset]',
        	'show_header' => 0,
        	'update' => 1,
        	'no_key' => 1,
			'post_status' => 'publish'
		));

		$data_p3ke = $this->functions->generatePage(array(
			'nama_page' => 'Data P3KE', 
			'content' => '[data_p3ke]',
        	'show_header' => 0,
        	'update' => 1,
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

		$basic_options_container = Container::make( 'theme_options', __( 'SATSET Options' ) )
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
	            		<li><a target="_blank" href="'.$data_p3ke['url'].'">'.$data_p3ke['title'].'</a></li>
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
	            	->set_help_text('Nama kabupaten dalam huruf besar dan tanpa awalan.')

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
        			->set_default_value(SATSET_PLUGIN_URL.'public/images/lokasi.png')
	        ) );

		Container::make( 'theme_options', __( 'Data P3KE' ) )
			->set_page_parent( $basic_options_container )
			->add_fields( array(
		    	Field::make( 'html', 'crb_p3ke_hide_sidebar' )
		        	->set_html( '
		        		<style>
		        			.postbox-container { display: none; }
		        			#poststuff #post-body.columns-2 { margin: 0 !important; }
		        		</style>
		        	' ),
		        Field::make( 'html', 'crb_p3ke_upload_html' )
	            	->set_html( '<h3>Import EXCEL data P3KE</h3>Pilih file excel .xlsx : <input type="file" id="file-excel" onchange="filePicked(event);"><br>Contoh format file excel bisa <a target="_blank" href="'.SATSET_PLUGIN_URL. 'excel/contoh_p3ke.xlsx">download di sini</a>. Sheet file excel yang akan diimport harus diberi nama <b>data</b>. Untuk kolom nilai angka ditulis tanpa tanda titik.' ),
		        Field::make( 'html', 'crb_p3ke_satset' )
	            	->set_html( 'Data JSON : <textarea id="data-excel" class="cf-select__input"></textarea>' ),
		        Field::make( 'html', 'crb_p3ke_save_button' )
	            	->set_html( '<a onclick="import_excel_p3ke(); return false" href="javascript:void(0);" class="button button-primary">Import WP</a>' )
	        ) );
	}

	function import_excel_p3ke(){
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
					$newData[trim($kk)] = $vv;
				}
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
				    'resiko_stunting' => $newData['resiko_stunting']
				);
				$wpdb->last_error = "";
				if(empty($newData['nik'])){
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_p3ke 
						where kepala_keluarga=%s 
							and kode_kemendagri=%s
							and id_p3ke=%s
							and nik is null"
						, $newData['kepala_keluarga'], $newData['kode_kemendagri'], $newData['id_p3ke']));
				}else{
					$cek_id = $wpdb->get_var($wpdb->prepare("
						SELECT 
							id 
						from data_p3ke 
						where kepala_keluarga=%s 
							and kode_kemendagri=%s
							and id_p3ke=%s
							and nik=%s"
						, $newData['kepala_keluarga'], $newData['kode_kemendagri'], $newData['id_p3ke'], $newData['nik']));
				}
				if(empty($cek_id)){
					$wpdb->insert("data_p3ke", $data_db);
					$ret['data']['insert']++;
				}else{
					$wpdb->update("data_p3ke", $data_db, array(
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

}
