<?php
add_action( 'admin_post_print.csv', 'wpplf23_print_csv' );

function wpplf23_print_csv() {
    if ( ! current_user_can( 'manage_options' ) )
        return;
		$host = DB_HOST;
		$user = DB_USER;
		$pass = DB_PASSWORD;
		$db = DB_NAME;
		global $wpdb;
		$db_prefix = $wpdb->prefix;
		$table = $db_prefix.'postcode_lookup_form';
		$file = 'export';
		$csv_output = "";
		
		// Step 1: Establish a connection
		$db = new PDO("mysql:host=".$host.";dbname=".$db, $user, $pass);
		// Step 2: Construct a query
		$query = "SELECT * FROM ".$table." LIMIT 1";
		// Step 3: Send the query
		$result = $db->query($query);
		$i = 0;
		// Step 4: Iterate over the results - Get headers
		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$i++;
			foreach($row as $key => $value) {
				$csv_output .= "$key;";
			}
			
$csv_output .= "
";

		}
		
		$query = "SELECT * FROM ".$table."";
		$result = $db->query($query);
		$i = 0;
		// Step 4: Iterate over the results - Get body
		while($row = $result->fetch(PDO::FETCH_ASSOC)) {
			$i++;
			foreach($row as $key => $value) {
				$csv_output .= "$value;";
			}
			
$csv_output .= "
";
			
		}
		
		$filename = $file."_".date("Y-m-d_H-i",time());
		header("Content-type: application/vnd.ms-excel");
		header("Content-disposition: csv" . date("Y-m-d") . ".csv");
		header( "Content-disposition: filename=".$filename.".csv");
		print $csv_output;
		

		exit;
}

?>