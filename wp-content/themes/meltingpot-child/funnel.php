<?php
require_once('../../../wp-load.php');

$redirectUrl = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : $_COOKIE['redirect_to'];
setcookie('redirect_to', null, -1, '/');

setcookie('request_path', $redirectUrl, 36000, '/');

$chosenMembership = $_COOKIE['selected_membership'];
$hasActiveSubscription = false;

$recentLogin = $_COOKIE['recent_login'];

$meprUser = new MeprUser(get_current_user_id());

$subscriptions = $meprUser->active_product_subscriptions('ids', false, false);
$activeSubscriptions = $meprUser->active_product_subscriptions('ids');

if (empty($chosenMembership)) {
    // Check if the unsigned in user has recently logged in or not
    // * Is the user of this device a member or not -> redirect to login instead of signup
    if (!is_user_logged_in() && !empty($recentLogin)) {
        setcookie('redirect_to', $redirectUrl, -1, '/');
        header('location: /login');
    } else if (!empty($_COOKIE['request_path'])) {
        header('location: ' . $_COOKIE['request_path']);
        setcookie('request_path', null, -1, '/');
    } else if (!is_user_logged_in()) {
        header('location: /#choose-your-subscription');
    }
}

if (empty($subscriptions)) {
    // Check if the user already chose a subscription
    if (!empty($chosenMembership)) {
        header('location: ' . $chosenMembership);
    } else if (!empty($_COOKIE['request_path'])) {
        header('location: ' . $_COOKIE['request_path']);
        setcookie('request_path', null, -1, '/');
    } else {
        header('location: /#choose-your-subscription');
    }
} else if (!empty($activeSubscriptions)) {
    // Reset the relevant cookies
    setcookie('selected_membership', null, -1, '/');
    setcookie("recent_login", true, strtotime( '+30 days' ), "/"); // expire in 30 days

    if(empty($redirectUrl)) {
        header('location: /member-dashboard');
    } else {
        header("location: $redirectUrl");
    }
} else if (is_user_logged_in()) {
    header('location: /account?action=subscriptions');
}

?>