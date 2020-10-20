<?php
require_once('../../../wp-load.php');
header('Content-Type: application/json');

$email = $_POST['email'];
$response = array("subscriptions" => array(), "error" => null);

if (empty($email) || !isset($email)) {
    $response['error'] = "No email address provided";
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
}

echo json_encode($response);
exit();
