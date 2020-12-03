<?php
require_once('../../../wp-load.php');
header('Content-Type: application/json');

$email = $_POST['email'];
$phrase = $_POST['phrase'];
$response = array("subscriptions" => array(), "error" => null);

if (empty($email) || empty($phrase)) {
    $response['error'] = "Missing post parameters [email, phrase]";
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $response['error'] = "Invalid email address";
}

$wp_user = get_user_by('login', $email);
if (empty($wp_user)) {
    $wp_user = get_user_by('email', $email);
}

if (empty($wp_user)) {
    $response['error'] = "No user exists for the email: $email";
}

/* Lookup the secret phrase from the database for that user */
try {
    global $wpdb;
    $table_name = $wpdb->prefix . "the_pond_facebook_group_phrases";
    $query =    "SELECT id, phrase, `timestamp`, used FROM $table_name
                    WHERE phrase = %s
                    AND `user_id` = %d
                    AND used IS FALSE
                    ORDER BY `timestamp` DESC
                    LIMIT 1";

    $results = $wpdb->get_results( $wpdb->prepare($query, $phrase, $wp_user->id) );

    $response['valid'] = sizeof($results) == 1;

} catch (Exception $e) {
    $response['valid'] = false;
}

/* Check for active memberpress subscriptions */
$mpUser = new MeprUser($wp_user->id);
$activeSubscriptions = $mpUser->active_product_subscriptions("transactions");

$subs = array();
foreach($activeSubscriptions as $s) {
    $sub = array(
        "id" => $s->product_id,
        "price" => $s->amount,
        "created_at" => $s->created_at,
        "expires_at" => $s->expires_at,
        "transaction_type" => $s->txn_type,
        "transaction_num" => $s->trans_num,
        "gateway" => $s->gateway,
        "status" => $s->status
    );

    $subs[] = $sub;
}

$hasActiveMembership = !empty($activeSubscriptions);

if ($hasActiveMembership) {
    $response['subscriptions'] = $subs;
} else {
    $response['error'] = "No active subscriptions for user $email";
}

echo json_encode($response);
exit();
