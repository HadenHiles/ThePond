<?php
require_once('../wp-load.php');
header('Cache-Control: no-cache, must-revalidate');
header('Content-Type: application/json');

global $wpdb;
$table_name = $wpdb->prefix . "mepr_members";
$query = "SELECT COUNT(*) AS 'count' FROM $table_name WHERE active_txn_count = 1";

$results = $wpdb->get_results($wpdb->prepare($query));

$response['count'] = intval($results[0]->count);

echo json_encode($response);
exit();
