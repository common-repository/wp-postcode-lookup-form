<?php
add_action( 'admin_menu', 'wpplf23_plugin_setup_menu' );
function wpplf23_plugin_setup_menu(){
	add_menu_page( 'WP Postcode Lookup Form Plugin Page', 'Postcode Lookup Form', 'manage_options', 'wp-postcode-lookup-form', 'wpplf23_admin_init' );
}

function wpplf23_admin_init(){
	wpplf23_debug_log( 'INFO', 'Initializing admin options... ' );
	echo "<div id='options-page-wrapper'>";
	
	//check if form has been posted
	$submission_action_posted = wpplf23_action_check ();
	if ( $submission_action_posted == true ) {
		//continue script
	} elseif ( $submission_action_posted == false ) {
		wpplf23_build_submissions_table ();
		wpplf23_build_options_page ();
		wpplf23_build_log_options ();
	}
	
	echo '</div>';
}

function wpplf23_build_options_page () {
	?>
	<div class="options-div-wrapper">
		<div class="options-top-panel" id="options-top-panel-id">
			<div class='left'>
				<h1>Options</h1>
			</div>
			<div class='right'>
				<button class="button-primary hide-button" id="hide-options-button" onclick="wpplf23_hide_options()">Hide</button>
			</div>
		</div>
		<div class='options-div' id='options-div'>
			<form method="post" action="options.php" enctype="multipart/form-data">  
				<?php settings_fields( 'wpplf23_plugin_options' ); ?>
				<?php do_settings_sections( __FILE__ ); ?>
				<p class="submit">    
					<input name="Submit" type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
				</p>
			</form>  
		</div>
	</div>
	<?php
}

function wpplf23_build_log_options () {
	$options = get_option( 'wpplf23_plugin_options' );  
	//$debug_log_enable_option = $options['debug_log_enable'];
	if ( isset( $options['debug_log_enable'] ) ) {
		?>
		<div class="options-div-wrapper">
			<div class="options-top-panel" id="log-options-top-panel">
				<div class='left'>
					<h1>Debug Log</h1>
				</div>
				<div class='right'>
					<button class="button-primary hide-button" id="hide-log-options-button" onclick="wpplf23_hide_log_options()">Hide</button>
				</div>
			</div>
		<?php
		
		global $wpdb;
		$db_prefix   = $wpdb->prefix;
		$query       = "SELECT * FROM ". $db_prefix. "postcode_lookup_form_log";
		$total_query = "SELECT COUNT(1) FROM ( ${query}) AS combined_table";
		$total       = $wpdb->get_var( $total_query );
		
		$options = get_option( 'wpplf23_plugin_options' );  

		$result = $wpdb->get_results( $query . " ORDER BY timestamp DESC" );

		echo "
			<div class='options-div' id='log-options-div'>
				<table id='log-options-table' class='widefat scroll'>
					<thead>
						<tr>
							<th>ID</th>
							<th>Timestamp</th>
							<th>Title</th>
							<th>Message</th>
						</tr>
					</thead>
					<tbody>
		";

		foreach ( $result as $row ) {
			echo "
			<tr>
			<td>". $row->id. "</td>
			<td>". $row->timestamp. "</td>
			<td>". $row->title. "</td>
			<td>". $row->message. "</td>
			</tr>
			";
		}

		echo "
				</tbody>
			</table>
		</div>
		</div>
		";
	}	
}

add_action( 'admin_init', 'wpplf23_register_and_build_fields' );
function wpplf23_register_and_build_fields() {
	register_setting( 'wpplf23_plugin_options', 'wpplf23_plugin_options', 'wpplf23_validate_setting' );
	
	add_settings_section( 'main_section', 'Main Settings', 'wpplf23_section_cb', __FILE__ );
	add_settings_field( 'form_redirect', 'Postcode form redirect page:', 'wpplf23_form_redirect_setting', __FILE__, 'main_section' );
	add_settings_field( 'thankyou_page', 'Thankyou Page:', 'wpplf23_thankyou_page_setting', __FILE__, 'main_section' );
	add_settings_field( 'table_items_per_page', 'Table items per page:', 'wpplf23_table_items_per_page_setting', __FILE__, 'main_section' );
	add_settings_field( 'debug_log_enable', 'Enable debug log:', 'wpplf23_debug_log_enable_setting', __FILE__, 'main_section' );
	add_settings_field( 'gmap_api_key', 'Google Maps API Key:', 'wpplf23_gmap_api_key_setting', __FILE__, 'main_section' );
	
	add_settings_section( 'recaptcha_section', 'reCAPTCHA Settings', 'wpplf23_section_cb', __FILE__ );
	add_settings_field( 'recaptcha_enable', 'Enable reCAPTCHA:', 'wpplf23_recaptcha_enable_setting', __FILE__, 'recaptcha_section' );
	add_settings_field( 'recaptcha_site_key', 'Site Key:', 'wpplf23_recaptcha_site_key_setting', __FILE__, 'recaptcha_section' );
	add_settings_field( 'recaptcha_secret_key', 'Secret Key:', 'wpplf23_recaptcha_secret_key_setting', __FILE__, 'recaptcha_section' );
	
	add_settings_section( 'email_section', 'Notification Email Settings', 'wpplf23_section_cb', __FILE__ );
	add_settings_field( 'notification_email_enable', 'Enable Notification Email:', 'wpplf23_notification_email_enable_setting', __FILE__, 'email_section' );
	add_settings_field( 'notification_email_address', 'Email Address:', 'wpplf23_notification_email_address_setting', __FILE__, 'email_section' );
	add_settings_field( 'notification_email_suject', 'Email Subject:', 'wpplf23_notification_email_subject_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_enable', 'Enable SMTP Email:', 'wpplf23_smtp_email_enable_setting', __FILE__, 'email_section' );
	
	add_settings_field( 'smtp_email_host', 'Host Address:', 'wpplf23_smtp_email_host_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_port', 'Port:', 'wpplf23_smtp_email_port_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_user', 'Username:', 'wpplf23_smtp_email_user_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_pass', 'Password:', 'wpplf23_smtp_email_pass_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_auth_enabled', 'Enable SMTP Authentication:', 'wpplf23_smtp_email_auth_enabled_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_auth_type', 'SMTP Authentication Type:', 'wpplf23_smtp_email_auth_type_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_to_address', 'To Address:', 'wpplf23_smtp_email_to_address_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_to_name', 'To Address Name:', 'wpplf23_smtp_email_to_name_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_from_address', 'From Address:', 'wpplf23_smtp_email_from_address_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_from_name', 'From Name:', 'wpplf23_smtp_email_from_name_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_reply_address', 'Reply to Address:', 'wpplf23_smtp_email_reply_address_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_reply_name', 'Reply to Name:', 'wpplf23_smtp_email_reply_name_setting', __FILE__, 'email_section' );
	add_settings_field( 'smtp_email_cc', 'cc:', 'wpplf23_smtp_email_cc_setting', __FILE__, 'email_section' );
	add_settings_field( 'customer_email_message', 'Customer email message:', 'wpplf23_customer_email_message_setting', __FILE__, 'email_section' );
	
	add_settings_section( 'restrict_postcode_section', 'Restrict Postcodes', 'wpplf23_section_cb', __FILE__ );
	add_settings_field( 'restrict_postcode_enable', 'Enable/Disable postcode restrictions:', 'wpplf23_restrict_postcode_enable_setting', __FILE__, 'restrict_postcode_section' );
	add_settings_field( 'restrict_postcode_list', 'Postcodes to restrict:', 'wpplf23_restrict_postcode_list_setting', __FILE__, 'restrict_postcode_section' );
}

function wpplf23_validate_setting( $wpplf23_plugin_options ) {
	return $wpplf23_plugin_options;
}

//Supposed to be empty
function wpplf23_section_cb() {}

// Form redirect page
function wpplf23_form_redirect_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );

	?>
		<select name="wpplf23_plugin_options[form_redirect]"> 
			<option selected disabled><?php echo esc_attr( __( 'Select page' ) ); ?></option> 
			<?php 
				$pages = get_pages(); 
				foreach ( $pages as $page ) {
					if ( $page->ID == $options['form_redirect'] ) {
						$option = '<option value="' . $page->ID . '" selected>' . $page->post_title . '</option>';
					} else {
						$option = '<option value="' . $page->ID . '">' . $page->post_title . '</option>';
					}
					echo $option;
				}
			?>
		</select>
	<?php
}

// Thankyou page
function wpplf23_thankyou_page_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );

	?>
		<select name="wpplf23_plugin_options[thankyou_page]"> 
			<option selected disabled><?php echo esc_attr( __( 'Select page' ) ); ?></option> 
			<?php 
				$pages = get_pages(); 
				foreach ( $pages as $page ) {
					if ( $page->ID == $options['thankyou_page'] ) {
						$option = '<option value="' . $page->ID . '" selected>' . $page->post_title . '</option>';
					} else {
						$option = '<option value="' . $page->ID . '">' . $page->post_title . '</option>';
					}
					echo $option;
				}
			?>
		</select>
	<?php
}

// Table items per page
function wpplf23_table_items_per_page_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[table_items_per_page]' type='number' value='<?php if ( ( isset( $options['table_items_per_page'] ) ) ) { echo $options['table_items_per_page']; } ?>' />
	<?php
}

// Notification Email Address
function wpplf23_notification_email_address_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[notification_email_address]' type='text' value='<?php if ( ( isset( $options['notification_email_address'] ) ) ) { echo $options['notification_email_address']; } ?>' />
	<?php
}

// Notification Email Subject
function wpplf23_notification_email_subject_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[notification_email_subject]' type='text' value='<?php if ( ( isset( $options['notification_email_subject'] ) ) ) { echo $options['notification_email_subject']; } ?>' />
	<?php
}

// Notification Email Enable
function wpplf23_notification_email_enable_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name="wpplf23_plugin_options[notification_email_enable]" type="checkbox" value="1" <?php if ( ( isset( $options['notification_email_enable'] ) ) ) { checked( '1', $options['notification_email_enable'] ); } ?> />
	<?php
}

// SMTP Email Enable
function wpplf23_smtp_email_enable_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name="wpplf23_plugin_options[smtp_email_enable]" id="smtp_enable" type="checkbox" value="1" <?php if ( ( isset( $options['smtp_email_enable'] ) ) ) { checked( '1', $options['smtp_email_enable'] ); } ?> />
	<?php
}

// SMTP Auth Enable
function wpplf23_smtp_email_auth_enabled_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name="wpplf23_plugin_options[smtp_email_auth_enabled]" type="checkbox" value="1" <?php if ( ( isset( $options['smtp_email_auth_enabled'] ) ) ) { checked( '1', $options['smtp_email_auth_enabled'] ); } ?> />
	<?php
}

// SMTP Auth Type
function wpplf23_smtp_email_auth_type_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name="wpplf23_plugin_options[smtp_email_auth_type]" type="radio" value="TLS" <?php if ( ( isset( $options['smtp_email_auth_type'] ) ) ) { checked( 'TLS', $options['smtp_email_auth_type'] ); } ?> /> TLS
		<input name="wpplf23_plugin_options[smtp_email_auth_type]" type="radio" value="SSL" <?php if ( ( isset( $options['smtp_email_auth_type'] ) ) ) { checked( 'SSL', $options['smtp_email_auth_type'] ); } ?> /> SSL
	<?php
}

// SMTP From Address
function wpplf23_smtp_email_from_address_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_from_address]' type='email' value='<?php if ( ( isset( $options['smtp_email_from_address'] ) ) ) { echo $options['smtp_email_from_address']; } ?>' />
	<?php
}

// SMTP To Address
function wpplf23_smtp_email_to_address_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_to_address]' type='email' value='<?php if ( ( isset( $options['smtp_email_to_address'] ) ) ) { echo $options['smtp_email_to_address']; } ?>' />
	<?php
}

// SMTP To Name
function wpplf23_smtp_email_to_name_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_to_name]' type='text' value='<?php if ( ( isset( $options['smtp_email_to_name'] ) ) ) { echo $options['smtp_email_to_name']; } ?>' />
	<?php
}

// SMTP From Name
function wpplf23_smtp_email_from_name_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_from_name]' type='text' value='<?php if ( ( isset( $options['smtp_email_from_name'] ) ) ) { echo $options['smtp_email_from_name']; } ?>' />
	<?php
}

// SMTP Reply Address
function wpplf23_smtp_email_reply_address_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_reply_address]' type='email' value='<?php if ( ( isset( $options['smtp_email_reply_address'] ) ) ) { echo $options['smtp_email_reply_address']; } ?>' />
	<?php
}

// SMTP Reply Name
function wpplf23_smtp_email_reply_name_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_reply_name]' type='text' value='<?php if ( ( isset( $options['smtp_email_reply_name'] ) ) ) { echo $options['smtp_email_reply_name']; } ?>' />
	<?php
}

// SMTP CC
function wpplf23_smtp_email_cc_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_cc]' type='email' value='<?php if ( ( isset( $options['smtp_email_cc'] ) ) ) { echo $options['smtp_email_cc']; } ?>' />
	<?php
}

// SMTP Email Host
function wpplf23_smtp_email_host_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_host]' id="smtp_host" type='text' value='<?php if ( ( isset( $options['smtp_email_host'] ) ) ) { echo $options['smtp_email_host']; } ?>' />
	<?php
}

// SMTP Email Port
function wpplf23_smtp_email_port_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_port]' type='number' value='<?php if ( ( isset( $options['smtp_email_port'] ) ) ) { echo $options['smtp_email_port']; } ?>' />
	<?php
}

// SMTP Email Username
function wpplf23_smtp_email_user_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_user]' type='text' value='<?php if ( ( isset( $options['smtp_email_user'] ) ) ) { echo $options['smtp_email_user']; } ?>' />
	<?php
}

// SMTP Email Password
function wpplf23_smtp_email_pass_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[smtp_email_pass]' type='password' value='<?php if ( ( isset( $options['smtp_email_pass'] ) ) ) { echo $options['smtp_email_pass']; } ?>' />
	<?php
}

// reCAPTCHA Enable
function wpplf23_recaptcha_enable_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name="wpplf23_plugin_options[recaptcha_enable]" type="checkbox" value="1" <?php if ( ( isset( $options['recaptcha_enable'] ) ) ) { checked( '1', $options['recaptcha_enable'] ); } ?> />
	<?php
}

// reCAPTCHA Site Key
function wpplf23_recaptcha_site_key_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[recaptcha_site_key]' type='text' value='<?php if ( ( isset( $options['recaptcha_site_key'] ) ) ) { echo $options['recaptcha_site_key']; } ?>' />
	<?php
}

// reCAPTCHA Secret Key
function wpplf23_recaptcha_secret_key_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[recaptcha_secret_key]' type='text' value='<?php if ( ( isset( $options['recaptcha_secret_key'] ) ) ) { echo $options['recaptcha_secret_key']; } ?>' />
	<?php
}

// Debug log Enable
function wpplf23_debug_log_enable_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name="wpplf23_plugin_options[debug_log_enable]" type="checkbox" value="1" <?php if ( ( isset( $options['debug_log_enable'] ) ) ) { checked( '1', $options['debug_log_enable'] ); } ?> />
	<?php
}

// customer_email_message
function wpplf23_customer_email_message_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	/**
	 * Define the array of defaults
	 */ 
	//$defaults = array(
	//	'textarea_name' => 'wpplf23_plugin_options[customer_email_message]',
	//	'textarea_rows' => '10',
	//	'editor_id'     => 'customer_email_message_editor'
	//);
	$settings = array( 
		'textarea_name' => 'wpplf23_plugin_options[customer_email_message]',
		'textarea_rows' => '10',
		'editor_id'     => 'customer_email_message_editor'
	);
	//$merged_settings = wp_parse_args( $settings, $defaults );
	//$editor_id = 'customer_email_message_editor';
	
	if ( isset( $options['customer_email_message'] ) && $options['customer_email_message'] != '' ) {
		$content = $options['customer_email_message'];
		wp_editor( $content, 'edit_customer_email_message', $settings );
	} else {
		$content = 'Thank you for your submission, we will be in contact soon. ';
		wp_editor( $content, 'edit_customer_email_message', $settings );
	}
}

// restrict_postcode_list_setting
function wpplf23_restrict_postcode_list_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<p>One per line - Only first half of postcode</p>
		<textarea name='wpplf23_plugin_options[restrict_postcode_list]' cols="40" rows="5" /><?php if ( ( isset( $options['restrict_postcode_list'] ) ) ) { echo esc_textarea( $options['restrict_postcode_list'] ); } ?></textarea>
	<?php
}

// restrict_postcode Enable
function wpplf23_restrict_postcode_enable_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name="wpplf23_plugin_options[restrict_postcode_enable]" type="checkbox" value="1" <?php if ( ( isset( $options['restrict_postcode_enable'] ) ) ) { checked( '1', $options['restrict_postcode_enable'] ); } ?> />
	<?php
}

// gmap_api_key
function wpplf23_gmap_api_key_setting() {  
	$options = get_option( 'wpplf23_plugin_options' );
	?>
		<input name='wpplf23_plugin_options[gmap_api_key]' type='text' value='<?php if ( ( isset( $options['gmap_api_key'] ) ) ) { echo $options['gmap_api_key']; } ?>' />
	<?php
}

function wpplf23_build_submissions_table () {
	$admin_url = admin_url();
	global $wpdb;
	$customPagHTML = "";
	$db_prefix     = $wpdb->prefix;
	$query         = "SELECT * FROM " . $db_prefix . "postcode_lookup_form";
	$total_query   = "SELECT COUNT(1) FROM ( ${query}) AS combined_table";
	$total         = $wpdb->get_var( $total_query );
	
	$options = get_option( 'wpplf23_plugin_options' );  
	if ( ( isset( $options['table_items_per_page'] ) ) && $options['table_items_per_page'] > 0) {
		$table_items_per_page_option = $options['table_items_per_page'];
		$items_per_page = $table_items_per_page_option;
	} else {
		$items_per_page = 10;
	}

	$page      = isset( $_GET['cpage'] ) ? abs( ( int ) $_GET['cpage'] ) : 1;
	$offset    = ( $page * $items_per_page ) - $items_per_page;
	$result    = $wpdb->get_results( $query . " ORDER BY timestamp DESC LIMIT ${offset}, ${items_per_page}" );
	$totalPage = ceil( $total / $items_per_page);
	
	$customPagHTML     =  '<div class="pagination">
		'.paginate_links( array(
		'base'      => add_query_arg( 'cpage', '%#%' ),
		'format'    => '',
		'prev_text' => __( '&laquo;' ),
		'next_text' => __( '&raquo;' ),
		'total'     => $totalPage,
		'current'   => $page
		) ). '<span>   Page ' . $page . ' of ' . $totalPage . '</span>
		</div>';
	
	echo "
	<div class='options-top-panel' id='options-top-panel-id'>
		<div class='left'>
			<h1>Submissions</h1>
		</div>
		<div class='right'>
			<button class='button-primary hide-button' id='hide-submissions-button' onclick='wpplf23_hide_submissions()'>Hide</button>
		</div>
	</div>
	<div class='options-div' id='submissions-table-div'>
	
	";

	echo "
		<section>
			<div class='left'>" . $customPagHTML . "</div>
			<div class='right'>" . wpplf23_DB_Tables_Rows() . "</div>
		</section>
		<div class='submissions-table-wrapper'>
			<table class='widefat' id='submissions-table'>
				<thead>
					<tr>
						<th>First Name</th>
						<th>Last Name</th>
						<th>Email</th>
						<th>Phone</th>
						<th>City</th>
						<th>Postcode</th>
						<th>Timestamp</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
		";

		foreach ( $result as $row ) {
			echo "
			<tr>
			<td>" . $row->first_name . "</td>
			<td>" . $row->last_name . "</td>
			<td>" . $row->email . "</td>
			<td>" . $row->phone . "</td>
			<td>" . $row->city . "</td>
			<td>" . $row->postcode . "</td>
			<td>" . $row->timestamp . "</td>
			<td>
			<form action='". $admin_url. "/admin.php?page=wp-postcode-lookup-form' method='post'>
				<input type='hidden' value='" . $row->id . "' name='id' />
				<input type='hidden' value='" . $page . "' name='cpage' />
				<input id='lead-details-button' class='lead-details-button button-primary' name='action' type='submit' value='details'>
			</form>
			</td>
			</tr>
			";
		}

		echo "
		</tbody>
		</table>
		</div>
		";	
	echo $customPagHTML;
	echo "
		<div class='export-div'>
			<h3>Export Submissions</h3>
			<a href='admin-post.php?action=print.csv' class='button-primary'>Export</a>
			<p>Click on the 'Export Submissions' button to download all of the Submissions in the database as a .csv file.</p>
		</div>
		<div class='delete-all-div'>
			<h3>Delete Submissions</h3>
			<form action='" . $admin_url . "/admin.php?page=wp-postcode-lookup-form' method='post'>
				
				<input type='hidden' value='" . $page . "' name='cpage' />
				<input id='delete-all-button' class='delete-all-button button-primary' name='action' type='submit' value='delete all' onclick='return confirm(\"Are you sure you want to delete all submissions?\" );'>
			</form>
			<p>Click on the 'Delete Submissions' button to delete all of the Submissions in the database.</p>
		</div>
		";
	echo "</div>";
}

function wpplf23_action_check () {
	global $wpdb;
	$db_prefix  = $wpdb->prefix;
	$table_name = $db_prefix . 'postcode_lookup_form';
	$admin_url  = admin_url();
	if ( ( isset( $_POST['action'] ) ) && ( isset( $_POST['action'] ) ) ) {
		$action = sanitize_text_field( $_POST['action'] );
		$id = sanitize_text_field( $_POST['id'] );
	}
	else {
		return false;
	}

	if ( $action == "edit" ) {
		$page = sanitize_text_field( $_POST['cpage'] );
		
		$result = $wpdb->get_row( "SELECT * FROM " . $table_name . " WHERE id=" . $id . " " );

		$output = "
		<div class='submission-edit-form-div'>
			<form action='" . $admin_url . "/admin.php?page=wp-postcode-lookup-form' method='post' class='submission-edit-form'>
				<label for='first_name' id='first_name'>First Name: </label>
				<input type='text' name='first_name' id='first_name' value='" . $result->first_name . "' maxlength='50' required/>
				<label for='last_name' id='last_name'>Last Name: </label>
				<input type='text' name='last_name' id='last_name' value='" . $result->last_name . "' maxlength='50' required/>
				<label for='email' id='email'>Email: </label>
				<input type='email' name='email' id='email' value='" . $result->email . "' maxlength='50' required/>
				<label for='phone' id='phone'>Phone: </label>
				<input type='tel' name='phone' id='phone' value='" . $result->phone . "' maxlength='13' required/>
				<label for='house_number' id='house_number'>House Number: </label>
				<input type='text' name='house_number' id='house_number' value='" . $result->house_number . "' maxlength='50' required/>
				<label for='street' id='street'>Street: </label>
				<input type='text' name='street' id='street' value='" . $result->street . "' maxlength='50' required/>
				<label for='city' id='city'>Town / City: </label>
				<input type='text' name='city' id='city' value='" . $result->city . "' maxlength='50' required/>
				<label for='postcode' id='postcode'>Postcode: </label>
				<input type='text' name='postcode' id='postcode' value='" . $result->postcode . "' maxlength='8' required/>
				<input type='hidden' value='" . $id . "' name='id' />
				<input type='hidden' value='" . $page . "' name='cpage' />
				<input id='lead-update-button' class='lead-update-button button-primary' name='action' type='submit' value='update'>
				<input id='lead-delete-button' class='lead-delete-button button-primary' name='action' type='submit' value='delete' onclick='return confirm(\"Are you sure you want to delete this item?\" );delete_all_subs ();'>
			</form>

			<a href='" . $admin_url . "/admin.php?page=wp-postcode-lookup-form&cpage=" . $page . "' class='button-primary'>Cancel</a>
		</div>
		";
		echo $output;
		return true;
	}
	elseif ( $action == "details" ) {
		$page = sanitize_text_field( $_POST['cpage'] );
		global $wpdb;
		$db_prefix = $wpdb->prefix;
		$result = $wpdb->get_row( "SELECT * FROM " . $table_name . " WHERE id=" . $id . " " );
		$output = "
		<div class='submission-details-div'>
			<a href='" . $admin_url . "/admin.php?page=wp-postcode-lookup-form&cpage=" . $page . "' class='button-primary'>Back</a>
			<span><h3>ID:</h3><p>" . $result->id . "</p></span>
			<span><h3>Sumitted:</h3><p>" . $result->timestamp . "</p></span>
			<span><h3>First Name:</h3><p>" . $result->first_name . "</p></span>
			<span><h3>Last Name:</h3><p>" . $result->last_name . "</p></span>
			<span><h3>Email Address:</h3><p>" . $result->email . "</p></span>
			<span><h3>Telephone Number:</h3><p>" . $result->phone . "</p></span>
			<span><h3>House Number:</h3><p>" . $result->house_number . "</p></span>
			<span><h3>Street:</h3><p>" . $result->street . "</p></span>
			<span><h3>Town / City:</h3><p>" . $result->city . "</p></span>
			<span><h3>Postcode:</h3><p>" . $result->postcode . "</p></span>
			<span><h3>IP Address:</h3><p>" . $result->ip . "</p></span>
			<form action='". $admin_url . "/admin.php?page=wp-postcode-lookup-form' method='post' class='wp-postcode-lookup-form'>
				<input type='hidden' value='" . $id . "' name='id' />
				<input type='hidden' value='" . $page . "' name='cpage' />
				<input id='lead-edit-button' class='lead-edit-button button-primary' name='action' type='submit' value='edit'>
				<input id='lead-delete-button' class='lead-delete-button button-primary' name='action' type='submit' value='delete' onclick='return confirm(\"Are you sure you want to delete this item?\" );'>
			</form>
		</div>
		";
		echo $output;
		return true;
	}
	elseif ( $action == "delete" ) {
		global $wpdb;
		$db_prefix = $wpdb->prefix;
		$wpdb->delete( $table_name, array( 'id' => $id ) );
		return true;
	}
	elseif ( $action == "update" ) {
		$page =         sanitize_text_field( $_POST['cpage'] );
		$id =           sanitize_text_field( $_POST['id'] );
		$first_name =   sanitize_text_field( $_POST['first_name'] );
		$last_name =    sanitize_text_field( $_POST['last_name'] );
		$email =        sanitize_email( $_POST['email'] );
		$phone =        sanitize_text_field( $_POST['phone'] );
		$house_number = sanitize_text_field( $_POST['house_number'] );
		$street =       sanitize_text_field( $_POST['street'] );
		$city =         sanitize_text_field( $_POST['city'] );
		$post_code =    sanitize_text_field( $_POST['postcode'] );

		// insert into database
		global $wpdb;
		$db_prefix = $wpdb->prefix;
		 
		$wpdb->update( 
			$table_name, 
			array( 
				"last_name"    => $last_name, 
				"first_name"   => $first_name, 
				"email"        => $email, 
				"phone"        => $phone, 
				"house_number" => $house_number, 
				"street"       => $street, 
				"city"         => $city, 
				"postcode"     => $post_code 
				), 
			array( 'id' => $id ), 
			array( 
				'%s',	// value1
				'%s',	// value2
				'%s',	// value3
				'%s',	// value4
				'%s',	// value5
				'%s',	// value6
				'%s',	// value7
				'%s'	// value8
			), 
			array( '%d' ) 
		);

		echo '
		<h1>Update Successful!</h1>
		<a href="' . $admin_url . '/admin.php?page=wp-postcode-lookup-form&cpage=' . $page . '" class="button-primary">Back</a>
		';

		return true;
	} 
	elseif ( $action == "export" ) {
		$outputFile = sanitize_text_field( $_POST['outputFile'] );
		wpplf23_print_csv();
		return true;
	}
	elseif ( $action == 'delete all' ) {
		wpplf23_delete_all_subs ();
		echo '
		<h1>Delete Successful!</h1>
		<a href="' . $admin_url . '/admin.php?page=wp-postcode-lookup-form" class="button-primary">Back</a>
		';
		return true;
	}
}

//To show number of rows in table
function wpplf23_DB_Tables_Rows() {
	global $wpdb;
	$db_prefix   = $wpdb->prefix;
	$table_name  = $db_prefix . "postcode_lookup_form";
	$count_query = "select count(*) from $table_name";
	$num = $wpdb->get_var( $count_query);

	return 'Submissions: ' . $num;
}

function wpplf23_delete_all_subs () {
	global $wpdb;
	$db_prefix = $wpdb->prefix;
	$query     = "TRUNCATE TABLE `" . $db_prefix . "postcode_lookup_form`";
	$result    = $wpdb->get_results( $query );
}