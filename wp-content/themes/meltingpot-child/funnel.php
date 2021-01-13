<?php
require_once('../../../wp-load.php');

$redirectUrl = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : $_COOKIE['redirect_to'];
setcookie('redirect_to', null, -1, '/');

setcookie('request_path', $redirectUrl, 36000, '/');

$chosenMembership = $_COOKIE['selected_membership'];
$hasActiveSubscription = false;

$recentLogin = $_COOKIE['recent_login'];

$requestPath = $_COOKIE['request_path'];

$meprUser = new MeprUser(get_current_user_id());

$subscriptions = $meprUser->active_product_subscriptions('ids', false, false);
$activeSubscriptions = $meprUser->active_product_subscriptions('ids');



if (empty($chosenMembership)) {
    // Check if the unsigned in user has recently logged in or not
    // * Is the user of this device a member or not -> redirect to login instead of signup
    if (!is_user_logged_in() && !empty($recentLogin)) {
        setcookie('redirect_to', $redirectUrl, -1, '/');
        header('location: /login');
        exit();
    } else if (!empty($requestPath)) {
        setcookie('request_path', null, -1, '/');
        header('location: ' . $requestPath);
        exit();
    } else if (!is_user_logged_in()) {
        header('location: /#choose-your-subscription');
        exit();
    }
}

if (empty($subscriptions)) {
    // Check if the user already chose a subscription
    if (!empty($recentLogin)) {
        setcookie('redirect_to', $redirectUrl, -1, '/');
        setcookie('selected_membership', null, -1, '/');
        setcookie('request_path', null, -1, '/');
        header('location: /login');
        exit();
    } else if (!empty($chosenMembership)) {
        setcookie('selected_membership', null, -1, '/');
        header('location: ' . $chosenMembership);
        exit();
    } else if (!empty($_COOKIE['request_path'])) {
        setcookie('request_path', null, -1, '/');
        header('location: ' . $_COOKIE['request_path']);
        exit();
    } else {
        header('location: /#choose-your-subscription');
        exit();
    }
} else if (!empty($activeSubscriptions)) {
    // Reset the relevant cookies
    setcookie('selected_membership', null, -1, '/');
    setcookie("recent_login", true, strtotime( '+30 days' ), "/"); // expire in 30 days

    if(empty($redirectUrl)) {
        header('location: /member-dashboard');
        exit();
    } else {
        header("location: $redirectUrl");
        exit();
    }
} else if (is_user_logged_in()) {
    header('location: /account?action=subscriptions');
    exit();
}

?>