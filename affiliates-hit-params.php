<?php
/*
Plugin Name: Affiliates Hit Params
Description: Adds the ability to send extra params in affiliate links (s1, s2, s3, s4, s5) that will be stored and reported back to affiliates
Author: pablit07
License: GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


register_activation_hook( __FILE__, 'affiliates_hit_params_activate' );

function affiliates_hit_params_activate() {
	global $wpdb;
	$hits_table = _affiliates_get_tablename( 'hits' );

	if ( $wpdb->get_var( "SHOW TABLES LIKE '" . $hits_table . "'" ) != $hits_table ) {
		$query = "ALTER TABLE `$table` ADD COLUMN s1 VARCHAR(100), ADD COLUMN s2 VARCHAR(100), ADD COLUMN s3 VARCHAR(100), ADD COLUMN s4 VARCHAR(100), ADD COLUMN s5 VARCHAR(100);";
		if (!$wpdb->query( $query )) {
			throw new Exception("SQL Error: '" . $query . "'", 1);
		}
	}
}

add_action( 'affiliates_hit', 'affiliates_hit_params_update_hit' );

function affiliates_hit_params_update_hit( $args ) {
	global $wpdb;

	$datetime = $args['datetime'];
	$table = _affiliates_get_tablename( 'hits' );

	if (isset($_GET['s1'])) {
		$s1 = $_GET['s1'];
	}
	if (isset($_GET['s2'])) {
		$s2 = $_GET['s2'];
	}
	if (isset($_GET['s3'])) {
		$s3 = $_GET['s3'];
	}
	if (isset($_GET['s4'])) {
		$s4 = $_GET['s4'];
	}
	if (isset($_GET['s5'])) {
		$s5 = $_GET['s5'];
	}

	$query = $wpdb->prepare( "UPDATE `$table` SET s1 = %s, s2 = %s, s3 = %s, s4 = %s, s5 = %s WHERE `datetime` = %s", $s1, $s2, $s3, $s4, $s5, $datetime );
	if ($wpdb->query( $query )) {

	} else {
		throw new Exception("Error Processing Request", 1);
	}

}

add_shortcode( 'affiliates_hit_params' , 'affiliates_hit_params_shortcode' );

function affiliates_hit_params_shortcode() {
	global $wpdb;

	$hits_table = _affiliates_get_tablename( 'hits' );
	$affiliates_table = _affiliates_get_tablename( 'affiliates' );
	$where = "WHERE a.affiliate_id != 1 AND s1 IS NOT NULL AND s1 != ''";

	$query = "SELECT s1, s2, s3, s4, s5, count(s1) as hits, a.name as affiliate FROM $hits_table h JOIN $affiliates_table a ON a.affiliate_id = h.affiliate_id  $where GROUP BY s1, s2, s3, s4, s5, a.name";
	$rows = $wpdb->get_results( $query, OBJECT );

	// $rows = array('1' => , 1);
	$html = '<div id="" class="affiliate-stats summary">
<h3>Sub Ids</h3>
<table class="wp-list-table widefat fixed" cellspacing="0">
	<thead>
		<tr>
			<th scope="col" class="">Affiliate</th>
			<th scope="col" class="">Sub ID 1</th>
			<th scope="col" class="">Sub ID 2</th>
			<th scope="col" class="">Sub ID 3</th>
			<th scope="col" class="">Sub ID 4</th>
			<th scope="col" class="">Sub ID 5</th>
			<th scope="col">Hits</th>
			<th scope="col" class=""></th>
		</tr>
	</thead>
	<tbody>';

	foreach ($rows as $row)
	{
		$s1 = $row->s1;
		$s2 = $row->s2;
		$s3 = $row->s3;
		$s4 = $row->s4;
		$s5 = $row->s5;
		$hits = $row->hits;
		$affiliate = $row->affiliate;

		$html .= "
		<tr>
			<td class=''>
				$affiliate
			</td>
			<td class=''>
				$s1
			</td>
			<td class=''>
				$s2
			</td>
			<td class=''>
				$s3
			</td>
			<td class=''>
				$s4
			</td>
			<td class=''>
				$s5
			</td>
			<td class='hits'>
				$hits
			</td>
			<td class='referrals'>
				
			</td>
		</tr>";
	}
	$html .= '</tbody>
</table>
</div>';
	
	return $html;
}