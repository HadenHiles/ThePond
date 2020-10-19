<?php
require_once('../../../wp-load.php');
header('Content-Type: application/json');

$email = $_POST['email'];

if (empty($email) || !isset($email)) {
    echo json_encode("{ 'subscriptions': null, 'error': 'Please provide an email address' }");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode("{ 'subscriptions': null, 'error': 'Invalid email format'}");
    exit();
}

$wp_user = get_user_by('login', $email);

if (empty($wp_user)) {
    echo json_encode("{ 'subscriptions': null, 'error': 'No user exists for the email: ${$email}' }");
    exit(); 
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
    echo json_encode("{ 'subscriptions': " . json_encode($subs) . ", 'error': null }");
    exit();
}

echo json_encode("{ 'subscriptions': null, 'error': null }");
exit();
