<?php
class SATSET_Functions
{

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

	public function __construct($plugin_name, $version){

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	public function get_data_infrastruktur_fields(){
		return array(
			'objectid' => array('label' => 'OBJECTID', 'type' => 'integer', 'maxlength' => 20),
			'wilayah' => array('label' => 'Wilayah', 'type' => 'text', 'maxlength' => 50),
			'nomor' => array('label' => 'Nomor', 'type' => 'text', 'maxlength' => 50),
			'kl_dat_das' => array('label' => 'Klasifikasi Data Dasar', 'type' => 'textarea'),
			'nm_ruas' => array('label' => 'Nama Ruas', 'type' => 'text', 'maxlength' => 191, 'required' => true),
			'thn_data' => array('label' => 'Tahun Data', 'type' => 'integer', 'maxlength' => 5),
			'status' => array('label' => 'Status', 'type' => 'text', 'maxlength' => 100),
			'fungsi' => array('label' => 'Fungsi', 'type' => 'text', 'maxlength' => 100),
			'mendukung' => array('label' => 'Mendukung', 'type' => 'text', 'maxlength' => 150),
			'ura_dukung' => array('label' => 'Uraian Dukungan', 'type' => 'textarea'),
			'kd_bd_pu' => array('label' => 'Kode Bidang PU', 'type' => 'text', 'maxlength' => 50),
			'kd_jns_inf' => array('label' => 'Kode Jenis Infrastruktur', 'type' => 'text', 'maxlength' => 50),
			'kd_inf' => array('label' => 'Kode Infrastruktur', 'type' => 'text', 'maxlength' => 50),
			'propinsi' => array('label' => 'Provinsi', 'type' => 'text', 'maxlength' => 150),
			'kab_kot' => array('label' => 'Kabupaten / Kota', 'type' => 'text', 'maxlength' => 150),
			'kecamatan' => array('label' => 'Kecamatan', 'type' => 'text', 'maxlength' => 150),
			'desa_kel' => array('label' => 'Desa / Kelurahan', 'type' => 'text', 'maxlength' => 150),
			'tk_ruas_aw' => array('label' => 'Titik Ruas Awal', 'type' => 'textarea'),
			'tk_ruas_ak' => array('label' => 'Titik Ruas Akhir', 'type' => 'textarea'),
			'kd_patok' => array('label' => 'Kode Patok', 'type' => 'text', 'maxlength' => 100),
			'km_awal' => array('label' => 'KM Awal', 'type' => 'text', 'maxlength' => 50),
			'km_akhir' => array('label' => 'KM Akhir', 'type' => 'text', 'maxlength' => 50),
			'nm_lintas' => array('label' => 'Nama Lintas', 'type' => 'text', 'maxlength' => 150),
			'kon_baik' => array('label' => 'Kondisi Baik', 'type' => 'text', 'maxlength' => 50),
			'kon_sdg' => array('label' => 'Kondisi Sedang', 'type' => 'text', 'maxlength' => 50),
			'kon_rgn' => array('label' => 'Kondisi Ringan', 'type' => 'text', 'maxlength' => 50),
			'kon_rusak' => array('label' => 'Kondisi Rusak', 'type' => 'text', 'maxlength' => 50),
			'kon_mntp' => array('label' => 'Kondisi Mantap', 'type' => 'text', 'maxlength' => 50),
			'kon_t_mntp' => array('label' => 'Kondisi Tidak Mantap', 'type' => 'text', 'maxlength' => 50),
			'panjang' => array('label' => 'Panjang', 'type' => 'text', 'maxlength' => 50),
			'lbr_keras' => array('label' => 'Lebar Keras', 'type' => 'text', 'maxlength' => 50),
			'lhrt' => array('label' => 'LHRT', 'type' => 'text', 'maxlength' => 50),
			'vcr' => array('label' => 'VCR', 'type' => 'text', 'maxlength' => 50),
			'tipe_jln' => array('label' => 'Tipe Jalan', 'type' => 'text', 'maxlength' => 50),
			'mst' => array('label' => 'MST', 'type' => 'text', 'maxlength' => 50),
			'tipe_keras' => array('label' => 'Tipe Perkerasan', 'type' => 'text', 'maxlength' => 100),
			'tanah_kri' => array('label' => 'Tanah Kritis', 'type' => 'text', 'maxlength' => 50),
			'macadam' => array('label' => 'Macadam', 'type' => 'text', 'maxlength' => 50),
			'aspal' => array('label' => 'Aspal', 'type' => 'text', 'maxlength' => 50),
			'rigid' => array('label' => 'Rigid', 'type' => 'text', 'maxlength' => 50),
			'thn_pen_ak' => array('label' => 'Tahun Penanganan Akhir', 'type' => 'text', 'maxlength' => 50),
			'jns_pen' => array('label' => 'Jenis Penanganan', 'type' => 'text', 'maxlength' => 100),
			'koord_x_aw' => array('label' => 'Koordinat X Awal', 'type' => 'decimal', 'maxlength' => 15),
			'koord_y_aw' => array('label' => 'Koordinat Y Awal', 'type' => 'decimal', 'maxlength' => 15),
			'koord_x_ak' => array('label' => 'Koordinat X Akhir', 'type' => 'decimal', 'maxlength' => 15),
			'koord_y_ak' => array('label' => 'Koordinat Y Akhir', 'type' => 'decimal', 'maxlength' => 15)
		);
	}

    function allow_access_private_post(){
    	if(
    		!empty($_GET) 
    		&& !empty($_GET['key'])
    	){
    		$key = base64_decode($_GET['key']);
    		$decode = $this->decode_key($_GET['key']);
    		if(!empty($decode['skip'])){
    			return;
    		}
    		
    		$key_db = md5(get_option( SATSET_APIKEY ));
    		$key = explode($key_db, $key);
    		$valid = 0;
    		if(
    			!empty($key[1]) 
    			&& $key[0] == $key[1]
    			&& is_numeric($key[1])
    		){
    			$tgl1 = new DateTime();
    			$date = substr($key[1], 0, strlen($key[1])-3);
    			$tgl2 = new DateTime(date('Y-m-d', $date));
    			$valid = $tgl2->diff($tgl1)->days+1;
    		}
    		if($valid == 1){
	    		global $wp_query;
		        // print_r($wp_query);
		        // print_r($wp_query->queried_object); die('tes');
		        if(!empty($wp_query->queried_object)){
		    		if($wp_query->queried_object->post_status == 'private'){
						wp_update_post(array(
					        'ID'    =>  $wp_query->queried_object->ID,
					        'post_status'   =>  'publish'
				        ));
				        die('<script>window.location =  window.location.href;</script>');
					}else{
						wp_update_post(array(
					        'ID'    =>  $wp_query->queried_object->ID,
					        'post_status'   =>  'private'
				        ));
					}
				}else if($wp_query->found_posts >= 1){
					global $wpdb;
					$sql = $wp_query->request;
					$post = $wpdb->get_results($sql, ARRAY_A);
					if(!empty($post)){
						if($post[0]['post_status'] == 'private'){
							wp_update_post(array(
						        'ID'    =>  $post[0]['ID'],
						        'post_status'   =>  'publish'
					        ));
					        die('<script>window.location =  window.location.href;</script>');
						}else{
							wp_update_post(array(
						        'ID'    =>  $post[0]['ID'],
						        'post_status'   =>  'private'
					        ));
						}
					}
				}
			}
    	}
    }

	function gen_key($key_db = false, $options = array()){
		$now = time()*1000;
		if(empty($key_db)){
			$key_db = md5(get_option( SATSET_APIKEY ));
		}
		$tambahan_url = '';
		if(!empty($options['custom_url'])){
			$custom_url = array();
			foreach ($options['custom_url'] as $k => $v) {
				$custom_url[] = $v['key'].'='.$v['value'];
			}
			$tambahan_url = $key_db.implode('&', $custom_url);
		}
		$key = base64_encode($now.$key_db.$now.$tambahan_url);
		return $key;
	}

	public function decode_key($value){
		$key = base64_decode($value);
		$key_db = md5(get_option( SATSET_APIKEY ));
		$key = explode($key_db, $key);
		$get = array();
		if(!empty($key[2])){
			$all_get = explode('&', $key[2]);
			foreach ($all_get as $k => $v) {
				$current_get = explode('=', $v);
				$get[$current_get[0]] = $current_get[1];
			}
		}
		return $get;
	}

	public function get_link_post($custom_post){
		$link = get_permalink($custom_post);
		$options = array();
		if(!empty($custom_post->custom_url)){
			$options['custom_url'] = $custom_post->custom_url;
		}
		if(strpos($link, '?') === false){
			$link .= '?key=' . $this->gen_key(false, $options);
		}else{
			$link .= '&key=' . $this->gen_key(false, $options);
		}
		return $link;
	}

	public function get_page_by_title( $page_title, $output = OBJECT, $post_type = 'page' ) {
		global $wpdb;
		if ( is_array( $post_type ) ) {
			$post_type = esc_sql( $post_type );
			$post_type_in_string = "'" . implode( "','", $post_type ) . "'";
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
					AND post_type IN ($post_type_in_string)
			", $page_title);
		} else {
			$sql = $wpdb->prepare("
				SELECT ID
				FROM $wpdb->posts
				WHERE post_title = %s
					AND post_type = %s
			", $page_title, $post_type);
		}
		$page = $wpdb->get_var( $sql );
		if ( $page ) {
			return get_post( $page, $output );
		}
		return null;
	}

	public function generatePage($options = array()){
		$post_type = 'page';
		$status = 'private';
		if(!empty($options['post_status'])){
			$status = $options['post_status'];
		}
		if(!empty($options['post_type'])){
			$post_type = $options['post_type'];
		}

		if(!empty($options['post_id'])){
			$custom_post = get_page($options['post_id']);
		}else{
			$custom_post = $this->get_page_by_title($options['nama_page'], OBJECT, $post_type);
		}
		$_post = array(
			'post_title'	=> $options['nama_page'],
			'post_content'	=> $options['content'],
			'post_type'		=> $post_type,
			'post_status'	=> $status,
			'comment_status'	=> 'closed'
		);
		if (empty($custom_post) || empty($custom_post->ID)) {
			$id = wp_insert_post($_post);
			$_post['insert'] = 1;
			$_post['ID'] = $id;
			$custom_post = $this->get_page_by_title($options['nama_page'], OBJECT, $post_type);
			if(empty($options['show_header'])){
				update_post_meta($custom_post->ID, 'ast-main-header-display', 'disabled');
				update_post_meta($custom_post->ID, 'footer-sml-layout', 'disabled');
			}
			update_post_meta($custom_post->ID, 'ast-breadcrumbs-content', 'disabled');
			update_post_meta($custom_post->ID, 'ast-featured-img', 'disabled');
			update_post_meta($custom_post->ID, 'site-content-layout', 'page-builder');
			update_post_meta($custom_post->ID, 'site-post-title', 'disabled');
			update_post_meta($custom_post->ID, 'site-sidebar-layout', 'no-sidebar');
			update_post_meta($custom_post->ID, 'theme-transparent-header-meta', 'disabled');
		}else if(!empty($options['update'])){
			if(empty($options['show_header'])){
				update_post_meta($custom_post->ID, 'ast-main-header-display', 'disabled');
				update_post_meta($custom_post->ID, 'footer-sml-layout', 'disabled');
			}
			$_post['ID'] = $custom_post->ID;
			wp_update_post( $_post );
			$_post['update'] = 1;
		}
		if(!empty($options['custom_url'])){
			$custom_post->custom_url = $options['custom_url'];
		}
		if(!empty($options['no_key'])){
			$link = get_permalink($custom_post);
		}else{
			$link = $this->get_link_post($custom_post);
		}
		return array(
			'post' => $custom_post,
			'id' => $custom_post->ID,
			'title' => $options['nama_page'],
			'url' => $link
		);
	}

	public function generateRandomString($length = 10) {
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}

	public function CekNull($number, $length=2){
        $l = strlen($number);
        $ret = '';
        for($i=0; $i<$length; $i++){
            if($i+1 > $l){
                $ret .= '0';
            }
        }
        $ret .= $number;
        return $ret;
    }

	function user_has_role($user_id, $role_name, $return=false){
		if(empty($user_id)){
			return false;
		}
	    $user_meta = get_userdata($user_id);
	    $user_roles = $user_meta->roles;
	    if($return){
	    	return $user_roles;
	    }else{
	    	return in_array($role_name, $user_roles);
	    }
	}

	function get_option_complex($key, $type){
		global $wpdb;
        $ret = $wpdb->get_results('select option_name, option_value from '.$wpdb->prefix.'options where option_name like \''.$key.'|%\'', ARRAY_A);
        $res = array();
        $types = array();
        foreach($ret as $v){
            $k = explode('|', $v['option_name']);
            $column = $k[1];
            $group = $k[3];
            if($column == ''){
            	$types[$group] = $v['option_value'];
            }
        }
        foreach($ret as $v){
            $k = explode('|', $v['option_name']);
            $column = $k[1];
            $loop = $k[2];
            $group = $k[3];
            if($column != ''){
	            if(
	            	isset($types[$loop])
	            	&& $type == $types[$loop]
	            ){
		            if(empty($res[$loop])){
		                $res[$loop] = array();
		            }
		            $res[$loop][$column] = $v['option_value'];
		        }
		    }
        }
        return $res;
    }

	function get_option_multiselect($key){
		global $wpdb;
        $ret = $wpdb->get_results('select option_name, option_value from '.$wpdb->prefix.'options where option_name like \''.$key.'|||%\'', ARRAY_A);
        $res = array();
        foreach($ret as $v){
            $res[$v['option_value']] = $v['option_value'];
        }
        return $res;
    }

	function isInteger($input){
	    return(ctype_digit(strval($input)));
	}

    function curl_post($options){
        $url = $options['url'] ?? '';
        if (empty($url)) {
            return false;
        }
        // Default headers to bypass firewall
        if (empty($options['header'])) {
            $options['header'] = [
                'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0 Safari/537.36',
                'Accept: */*',
                'Content-Type: application/x-www-form-urlencoded',
            ];
        }
        $req = http_build_query($options['data']);
        // Temporary cookie jar for session persistence (helps with firewall challenge)
        $cookieJar = sys_get_temp_dir() . '/curl_cookie_' . uniqid() . '.txt';
        $curlOpts = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => $req,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_CONNECTTIMEOUT => 0,
            CURLOPT_TIMEOUT => 10000,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $options['header'],
            CURLOPT_COOKIEJAR => $cookieJar,
            CURLOPT_COOKIEFILE => $cookieJar,
        ];
        // First request
        $curl = curl_init();
        curl_setopt_array($curl, $curlOpts);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);
        // Detect firewall challenge and retry after injecting cookie
        if (!$err && strpos($response, 'Checking your browser') !== false) {
            $host = parse_url($url, PHP_URL_HOST);
            $fh = @fopen($cookieJar, 'a');
            if ($fh) {
                fwrite($fh, $host . "\tFALSE\t/\tFALSE\t0\thc_js_gate\t1\n");
                fclose($fh);
            }
            $curl = curl_init();
            curl_setopt_array($curl, $curlOpts);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
        }
        if ($err) {
            $msg = "cURL Error #:" . $err . " (" . $url . ")";
            if (!empty($options['debug']) && $options['debug'] == 1) {
                die($msg);
            }
            return $msg;
        }
        return $response;
    }

    function send_tg($options){
		$login = false;
		if(is_user_logged_in()){
		    $current_user = wp_get_current_user();
		    if($this->user_has_role($current_user->ID, 'administrator')){
		        $login = true;
		    }
		}
	  	$bot_tg = get_option('_crb_satset_bot_tg');
	  	$id_akun_tg = get_option('_crb_satset_akun_tg');
	  	$ret = array();
	  	if(!empty($bot_tg) && !empty($id_akun_tg)){
	  		$message = $options['message'];
	  		$id_akun_tg = explode(',', $id_akun_tg);
	  		foreach($id_akun_tg as $id_akun){
	  			$url = "https://api.telegram.org/$bot_tg/sendMessage?chat_id=$id_akun&text=$message";
	  			$ret_url = file_get_contents($url);
	  			if(true == $login){
	  				$ret[] = array(
	  					'return'=> $ret_url,
	  					'url'=> $url
	  				);
	  			}
	  		}
	  	}
	  	return $ret;
    }
}