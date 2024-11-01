<?php
/**
 * Plugin Name: WP Postcode Lookup Form
 * Plugin URI: https://wordpress.org/plugins/wp-postcode-lookup-form/
 * Description: WP Postcode Lookup Form plugin was designed to help you easily add lead generation functionality to your Wordpress websites with UK address auto-complete.
 * Version: 0.6.0
 * Author: RKB Computing
 * Author URI: http://rkbcomputing.co.uk
 * License: GPL2
 */

include( plugin_dir_path( __FILE__ ) . 'options.php' );
include( plugin_dir_path( __FILE__ ) . 'modules/send-mail.php' );
include( plugin_dir_path( __FILE__ ) . 'modules/print-csv.php' );
include_once(ABSPATH . WPINC . '/class-phpmailer.php' );

add_action( 'wp_enqueue_scripts', 'wpplf23_frontend_recaptcha_script' );
function wpplf23_frontend_recaptcha_script() {
	wp_register_script( 'recaptcha', 'https://www.google.com/recaptcha/api.js' );
	wp_enqueue_script( "recaptcha" );
}

add_action( 'wp_head', 'wpplf23_plugin_scripts' );
function wpplf23_plugin_scripts() {
	wpplf23_debug_log( 'INFO', 'Initializing plugin scripts... ' );
	wp_enqueue_style('wpplf23_plugin_styles', plugin_dir_url( __FILE__ ) . 'css/main.css' );
	wp_register_script( 'wpplf23_plugin_scripts', plugin_dir_url( __FILE__ ) . 'js/main.js', array( 'jquery' ), '1.1', True );
	wp_enqueue_script( 'wpplf23_plugin_scripts' );
	wp_register_script( 'wpplf23_tel_validate_script', plugin_dir_url( __FILE__ ) . 'js/jstelnumbers.js', array( 'jquery' ), '1.1', True );
	wp_enqueue_script( 'wpplf23_tel_validate_script' );
}

add_action( 'admin_head', 'wpplf23_admin_scripts' );
function wpplf23_admin_scripts() {
	wpplf23_debug_log( 'INFO', 'Initializing admin options scripts... ' );
	wp_enqueue_style( 'style', plugin_dir_url( __FILE__ ) . 'css/admin.css' );
	wp_register_script( 'wpplf23_admin_scripts', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.1', True );
	wp_enqueue_script( 'wpplf23_admin_scripts' );
	
	$options = get_option( 'wpplf23_plugin_options' );
	if ( isset( $options['debug_log_enable'] ) ) {
		$debug_log_enable_option = $options['debug_log_enable'];
	} else {
		$debug_log_enable_option = 0;
	}
	$script_params = array(
    'debug_log_enable' => $debug_log_enable_option
	);
	wp_localize_script( 'wpplf23_admin_scripts', 'scriptParams', $script_params );
}

/* The code to run on activation */
register_activation_hook( __FILE__, 'wpplf23_activate' );
function wpplf23_activate() {
	//check if table exists if not create it
	wpplf23_sql_table_check ();
}

/* The code to run on deactivation */
register_deactivation_hook( __FILE__, 'wpplf23_deactivate' );
function wpplf23_deactivate() {

}

/* The code to run on uninstalltion */
register_uninstall_hook( __FILE__, 'wpplf23_uninstall' );
function wpplf23_uninstall() {
	//remove sql database table
	wpplf23_remove_sql_table();
}

add_shortcode( 'postcode_lookup_form_sc', 'wpplf23_build_postcode_lookup_form' );
function wpplf23_build_postcode_lookup_form(){
	wpplf23_debug_log( 'INFO', 'Building postcode lookup form... ' );
	global $wpdb;
	$this_page	=	$_SERVER['REQUEST_URI'];
	if ( ( isset( $_POST['page'] ) ) ) {
		$page	= sanitize_text_field( $_POST['page'] );
	} else {
		$page = NULL; 
	}
	$options = get_option( 'wpplf23_plugin_options' );  
	
	if ( ( isset( $options['form_redirect'] ) ) ) {
		$uri = get_page_uri( $options['form_redirect'] );
		$form_redirect_option = $uri;
	}
	
	//$form_redirect_option = $options['form_redirect'];
	$thankyou_page_option = $options['thankyou_page'];
	
	if ( ( isset( $options['recaptcha_enable'] ) ) ) {
		$recaptcha_enable_option = $options['recaptcha_enable'];
		$recaptcha_site_key_option = $options['recaptcha_site_key'];
		$recaptcha_secret_key_option = $options['recaptcha_secret_key'];
	} else {
		$recaptcha_enable_option = 0;
	}
	
	if ( $page == NULL ) {
		
		/* $output = "
			<div id='main_postcode_form'>
				<form id='postcode-lookup-form' method='post' action='" . $form_redirect_option . "' >
					<input id='txt-postcode-lookup' name='txt-postcode-lookup' type='text' placeholder='Enter your postcode... ' maxlength='8' required/>
					<input type='hidden' value='1' name='page' />
					<input id='btn-postcode-lookup' class='cta' alt='Find' name='btn-postcode-lookup' type='submit' value='Find My Property' />
				</form>
			</div>
		"; */
		
		$output = "
					<div class='postcode-lookup-wrapper'>
						<form class='postcode-lookup-form' method='post' action='" . $form_redirect_option . "' >
							<section class='flexbox-postcode-lookup'>
								<div class='postcode-text-div'>
									<input class='postcode-text' name='txt-postcode-lookup' type='text' placeholder='Enter your postcode...' maxlength='8' required />
									<input type='hidden' value='1' name='page' />
								</div>
								<div class='postcode-button-div'>
									<button class='postcode-button' type='submit' title='Find' >Find</button>
								</div>
							</section>
						</form>
					</div>
				";
		
		return $output ;
	}//End Page 1 of Form
	elseif ( $page == 1 ) {
		$post_code = sanitize_text_field( $_POST['txt-postcode-lookup'] );

		if ( ( isset( $options['gmap_api_key'] ) ) ){
			if ( $options['gmap_api_key'] != '' ){
				$gmap_api_key = $options['gmap_api_key'];
				$url = "https://maps.googleapis.com/maps/api/geocode/xml?key=" . $gmap_api_key . "&address=" . $post_code . "&sensor=false";
			} else {
				$url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . $post_code . "&sensor=false";
			}
			
		} else {
			$url = "http://maps.googleapis.com/maps/api/geocode/xml?address=" . $post_code . "&sensor=false";
		}
		
		$parsedXML = simplexml_load_file( $url );
		
		if ( $parsedXML->status != 'OK' ) {
			//echo "There has been a problem: " . $parsedXML->status;
			$output = "
						<div class='postcode-lookup-wrapper'>
							<h3>Your postcode was not recognized!</h3>
							<p>Please try again.</p>
							<form class='postcode-lookup-form' method='post' action='" . $form_redirect_option . "' >
								<section class='flexbox-postcode-lookup'>
									<div class='stretch-postcode-lookup'>
										<input class='postcode-txt-postcode-lookup' name='txt-postcode-lookup' type='text' placeholder='Enter your postcode...' maxlength='8' required />
										<input type='hidden' value='1' name='page' />
									</div>
									<div class='normal-postcode-lookup'>
										<button class='submit-postcode-lookup' type='submit' title='Find' >Find</button>
									</div>
								</section>
							</form>
						</div>
						";
			return $output ;
		}
		
		$myAddress = array();
		foreach ( $parsedXML->result->address_component as $component) {
			if (is_array( $component->type) ) $type = (string)$component->type[0];
			else $type = (string)$component->type;

			$myAddress[$type] = (string)$component->long_name;
		}
		
		$house_number              = '';
		$street                    = '';
		$city                      = '';
		$postcode                  = '';
		$postcode_restrict_enabled = '';
		
		if ( ( isset( $myAddress['street_number'] ) ) ) { 
			$house_number = $myAddress['street_number'];
		}
		if ( ( isset( $myAddress['route'] ) ) ) { 
			$street = $myAddress['route'];
		}
		if ( ( isset( $myAddress['administrative_area_level_2'] ) ) ) { 
			$city = $myAddress['administrative_area_level_2'];
		}
		if ( ( isset( $myAddress['postal_code'] ) ) ) { 
			$postcode = $myAddress['postal_code'];
		}
		//$street = $myAddress['route'];
		//$city = $myAddress['locality'];
		//$myAddress['administrative_area_level_3'];
		//$city = $myAddress['administrative_area_level_2'];
		//$myAddress['administrative_area_level_1'];
		//$myAddress['country'];
		//$postcode = $myAddress['postal_code'];

		//Check if postcode is in region
		if ( ( isset( $options['restrict_postcode_enable'] ) ) ) { 
			$postcode_restrict_enabled = $options['restrict_postcode_enable'];
		}
		
		if ( $postcode_restrict_enabled == 1 ) {
			$in_region = wpplf23_postcode_in_region( $postcode );
			if (!$in_region){
			$output = '<h3>Sorry, your postcode is out of our area</h3>
				<p>Please try again another time</p>
				<p>Thank you for your interest</p>
				';
				return $output ;
			}
		}

		//Got past region check and validated postcode so goto form and auto-complete
		$output = "
		<form id='postcode-lookup-form' name='postcode-lookup-form' method='post' action='" . $form_redirect_option . "' onsubmit='return wpplf23_validateForm(this)' >
			<label for='first_name' id='first_name'>First Name: </label>
			<input type='text' name='first_name' id='first_name' maxlength='50' required/>
			<label for='last_name' id='last_name'>Last Name: </label>
			<input type='text' name='last_name' id='last_name' maxlength='50' required/>
			<label for='email' id='email'>Email: </label>
			<input class='input-text' type='email' name='email' id='emailAdd' maxlength='50' required/>
			<label for='phone' id='phone'>Phone: </label>
			<input class='input-text' type='tel' name='phone' id='phoneNo' maxlength='13' required/>
			<h2 class='bold'>Address:</h2>
			<label for='house_number' id='house_number'>House Number: </label>
			<input type='text' name='house_number' id='house_number' value='" . $house_number . "' maxlength='50' required/>
			<label for='street' id='street'>Street: </label>
			<input type='text' name='street' id='street' value='" . $street . "' maxlength='50' required/>
			<label for='city' id='city'>Town / City: </label>
			<input type='text' name='city' id='city' value='" . $city . "' maxlength='50' required/>
			<label for='txt-postcode-lookup' id='txt-postcode-lookup'>Postcode: </label>
			<input name='txt-postcode-lookup' type='text' placeholder='Enter your postcode... ' value='" . $postcode . "' maxlength='8' required/>
			<input type='hidden' value='2' name='page' />
			<input type='hidden' value='" . $_SERVER['REMOTE_ADDR'] . "' name='ip' />
			";
			
			if ( $recaptcha_enable_option == 1) {
				$output = $output. "<div class='g-recaptcha' data-sitekey='" . $recaptcha_site_key_option . "'>reCAPTCHA:</div>";
			}
			
			$output = $output . "
				<input class='cta' alt='Find' name='btn-postcode-lookup' type='submit' value='Submit' />
			</form>
			";
		return $output ;

	}//End Page 2 of Form
	elseif ( $page == 2 ) {
		$options = get_option( 'wpplf23_plugin_options' );
		
		//Sanitize input from POST into variables
		$first_name   = sanitize_text_field( $_POST['first_name'] );
		$last_name    = sanitize_text_field( $_POST['last_name'] );
		$email        = sanitize_email( $_POST['email'] );
		$phone        = sanitize_text_field( $_POST['phone'] );
		$house_number = sanitize_text_field( $_POST['house_number'] );
		$street       = sanitize_text_field( $_POST['street'] );
		$city         = sanitize_text_field( $_POST['city'] );
		$post_code    = sanitize_text_field( $_POST['txt-postcode-lookup'] );
		$ip           = sanitize_text_field( $_POST['ip'] );
		
		//reCAPTCHA variables
		$g_recaptcha_response        = sanitize_text_field( $_POST['g-recaptcha-response'] );	
		$recaptcha_enable_option     = $options['recaptcha_enable'];
		$recaptcha_site_key_option   = $options['recaptcha_site_key'];
		$recaptcha_secret_key_option = $options['recaptcha_secret_key'];
		$jsonurl                     = "https://www.google.com/recaptcha/api/siteverify?secret=" . $recaptcha_secret_key_option . "&response=" . $g_recaptcha_response . "&remoteip=" . $_SERVER['REMOTE_ADDR'];	
		$json                        = file_get_contents( $jsonurl );
		$json_a                      = json_decode( $json, true );
		$recaptcha_success           = $json_a['success'];
		
		if ( $recaptcha_success == True ) {
			// if pass reCAPTCHA insert into database
			global $wpdb;
			$db_prefix = $wpdb->prefix;
			$table_name = $db_prefix . "postcode_lookup_form";
			$wpdb->insert( $table_name, array( 
				"last_name"    => $last_name, 
				"first_name"   => $first_name, 
				"email"        => $email, 
				"phone"        => $phone, 
				"house_number" => $house_number, 
				"street"       => $street, 
				"city"         => $city, 
				"postcode"     => $post_code,
				"ip"           => $ip
			) );
		} else {
			
			$output = "
			<h2>reCAPTCHA Failed!</h2>
			<h3>Please try again.</h3>
			<form id='postcode-lookup-form' name='postcode-lookup-form' method='post' action='" . $form_redirect_option . "' onsubmit='return wpplf23_validateForm(this)' >
				<label for='first_name' id='first_name'>First Name: </label>
				<input type='text' name='first_name' id='first_name' maxlength='50' value='" . $first_name . "' required/>
				<label for='last_name' id='last_name'>Last Name: </label>
				<input type='text' name='last_name' id='last_name' maxlength='50' value='" . $last_name . "' required/>
				<label for='email' id='email'>Email: </label>
				<input class='input-text' type='email' name='email' id='emailAdd' maxlength='50' value='" . $email . "' required/>
				<label for='phone' id='phone'>Phone: </label>
				<input class='input-text' type='tel' name='phone' id='phoneNo' maxlength='13' value='" . $phone . "' required/>
				<h2 class='bold'>Address:</h2>
				<label for='house_number' id='house_number'>House Number: </label>
				<input type='text' name='house_number' id='house_number' value='" . $house_number . "' maxlength='50' required/>
				<label for='street' id='street'>Street: </label>
				<input type='text' name='street' id='street' value='" . $street . "' maxlength='50' required/>
				<label for='city' id='city'>Town / City: </label>
				<input type='text' name='city' id='city' value='" . $city . "' maxlength='50' required/>
				<label for='txt-postcode-lookup' id='txt-postcode-lookup'>Postcode: </label>
				<input name='txt-postcode-lookup' type='text' placeholder='Enter your postcode... ' value='" . $post_code . "' maxlength='8' required/>
				<input type='hidden' value='2' name='page' />
				<input type='hidden' value='" . $_SERVER['REMOTE_ADDR'] . "' name='ip' />
				";
				
				if ( $recaptcha_enable_option == 1 ) {
					$output = $output . "<div class='g-recaptcha' data-sitekey='" . $recaptcha_site_key_option . "'>reCAPTCHA:</div>";
				}
				
				$output = $output . "
					<input class='cta' alt='Find' name='btn-postcode-lookup' type='submit' value='Submit' />
				</form>
				";
			return $output ;
		}
		
		//Build admin email
		$blogname = get_option( 'blogname' );
		$siteurl = get_option( 'siteurl' );
		$output = "
			<h1>Submission from " . $blogname . " ( " . $siteurl . " )</h1>
			<h3>Postcode:</h3>
			<p>" . $post_code . "</p>
			<h3>First Name:</h3>
			<p>" . $first_name . "</p>
			<h3>Last Name:</h3>
			<p>" . $last_name . "</p>
			<h3>Email Address:</h3>
			<p>" . $email . "</p>
			<h3>Telephone Number:</h3>
			<p>" . $phone . "</p>
			<h3>House Number:</h3>
			<p>" . $house_number . "</p>
			<h3>Street:</h3>
			<p>" . $street . "</p>
			<h3>Town / City:</h3>
			<p>" . $city . "</p>
			<h3>IP Address:</h3>
			<p>" . $ip . "</p>
		";
		
		//send mail notification to admin ( "" = admin - if you input email it gets sent there)
		wpplf23_sendMail( $output, '' );
		
		//https://github.com/leemunroe/html-email-template > View the source and instructions on GitHub for email template
		//send mail notification to customer
		wpplf23_sendMail( $options['customer_email_message'], $email);

		if ( isset( $options['thankyou_page'] ) && $options['thankyou_page'] != '' ) {

				$my_postid = $thankyou_page_option;//This is page id or post id
				$content_post = get_post($my_postid);
				$content = $content_post->post_content;
				$content = apply_filters('the_content', $content);
				$content = str_replace(']]>', ']]&gt;', $content);

				return $content;
			
			//$output = $options['thankyou_page'];
			//return $output ;
		} else {
			header("Refresh:0; url=$thankyou_page_option");
			$output = '
				<h2>Thank you for your enquirey</h2>
				<p>We will reply to your request as soon as possible.</p>
				</br>
				<h2>What\'s the next stage?</h2>
				<p>One of our representatives will be in contact with you soon, to deal with your request</p>
			';
			//return $output ;
		}
	}//End Page 3 of Form
}


function wpplf23_postcode_in_region ( $postcode) {
	$options = get_option( 'wpplf23_plugin_options' );  

	$postcode_list     = $options['restrict_postcode_list'];
	$postcode_list_arr = explode( "\r\n", $postcode_list );
	$postcode_pieces   = explode( ' ', $postcode );

	foreach ( $postcode_list_arr as $value ) {
		$postcode_pieces2 = explode( ' ', $value );

		if ( $postcode_pieces[0] == $postcode_pieces2[0] ) {
			return true;
		}
	}
	return false;
}

function wpplf23_sql_table_check () {
	wpplf23_debug_log( 'INFO', 'Checking SQL tables exist... ' );
	global $wpdb;
	$db_prefix = $wpdb->prefix;
	$table_name = $db_prefix . "postcode_lookup_form";
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
		//Table is not created. Creating table...
		
		$charset_collate = $wpdb->get_charset_collate();
		//You must have two spaces between the words PRIMARY KEY and the definition of your primary key.
		$sql = "CREATE TABLE $table_name (
		id int(7) NOT NULL AUTO_INCREMENT,
		first_name varchar(50) NOT NULL,
		last_name varchar(50) NOT NULL,
		email varchar(50) NOT NULL,
		phone varchar(13) NOT NULL,
		house_number varchar(50) NOT NULL,
		street varchar(50) NOT NULL,
		city varchar(50) NOT NULL,
		postcode varchar(8) NOT NULL,
		ip varchar(50) NOT NULL,
		timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		
		wpplf23_debug_log( "ERROR", $table_name . " table not found!" );
	} else {
		wpplf23_debug_log( "INFO", $table_name . " table found" );
	}
	$log_table_name = $db_prefix. "postcode_lookup_form_log";
	if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $log_table_name ) {
		//Table is not created. Creating table...
		
		$charset_collate = $wpdb->get_charset_collate();
		//You must have two spaces between the words PRIMARY KEY and the definition of your primary key.
		$sql = "CREATE TABLE $log_table_name (
		id int(9) NOT NULL AUTO_INCREMENT,
		title varchar(50) NOT NULL,
		message varchar(100) NOT NULL,
		timestamp timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
		PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
		wpplf23_debug_log( 'ERROR', $log_table_name . " table not found!" );
	} else {
		wpplf23_debug_log( "INFO", $log_table_name . " table found" );
	}
}

function wpplf23_remove_sql_table () {
	global $wpdb;
	$db_prefix = $wpdb->prefix;
	$query     = "DROP TABLE `" . $db_prefix . "postcode_lookup_form`";
	$result    = $wpdb->get_results( $query );
}

function wpplf23_debug_log ( $title, $message ) {
	$options = get_option( 'wpplf23_plugin_options' );  
	if ( isset( $options['debug_log_enable'] ) ) {
		global $wpdb;
		$db_prefix = $wpdb->prefix;
			$log_table_name = $db_prefix . "postcode_lookup_form_log";
		$wpdb->insert( $log_table_name, array( 
				"title" => sanitize_text_field( $title ), 
				"message" => sanitize_text_field( $message )
			) );
	}
}