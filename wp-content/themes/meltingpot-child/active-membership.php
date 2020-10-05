<?php
require_once('../../../wp-load.php');
header('Content-Type: application/json');

$email = $_POST['email'];

if (empty($email) || !isset($email)) {
    echo json_encode("{ 'active': false, 'error': 'Please provide an email address' }");
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode("{ 'active': false, 'error': 'Invalid email format'}");
    exit();
}

$wp_user = get_user_by('login', $email);

if (empty($wp_user)) {
    echo json_encode("{ 'active': false, 'error': 'No user exists for the email: ${$email}' }");
    exit(); 
}

$mpUser = new MeprUser($wp_user->id);
$activeSubscriptions = $mpUser->active_product_subscriptions('ids');
$hasActiveMembership = !empty($activeSubscriptions);

if ($hasActiveMembership) {
    echo json_encode("{ 'active': true, 'error': null }");
    exit();
}

echo json_encode("{ 'active': false, 'error': null }");
exit();
