<?php
require_once('../../../wp-load.php');

$redirectUrl = $_COOKIE['redirect_to'];
setcookie('redirect_to', null, -1, '/');

$chosenMembership = $_COOKIE['selected_membership'];
setcookie('selected_membership', null, -1, '/');
$hasActiveSubscription = false;

$recentLogin = $_COOKIE['recent_login'];
setcookie('recent_login', null, -1, '/');

$meprUser = new MeprUser(get_current_user_id());

$subscriptions = $meprUser->active_product_subscriptions('ids', false, false);
$activeSubscriptions = $meprUser->active_product_subscriptions('ids');

// Check if the unsigned in user has recently logged in or not
// * Is the user of this device a member or not -> redirect to login instead of signup
if (empty(get_current_user_id()) && !empty($recentLogin) && $recentLogin == true) {
    header('location: /login');
} else if (empty(get_current_user_id()) && empty($recentLogin)) {
    header('location: /#choose-your-subscription');
} else {
    // Set the recent_login cookie so the user will be redirected to login instead of sign up if they are a member
    setcookie("recent_login", true, strtotime( '+30 days' ), "/"); // expire in 30 days

    if (empty($subscriptions)) {
        // Check if the user already chose a subscription
        if (!empty($chosenMembership)) {
            header('location: ' . $chosenMembership);
        } else {
            header('location: /#choose-your-subscription');
        }
    } else if (!empty($activeSubscriptions)) {
        if(empty($redirectUrl)) {
            header('location: /member-dashboard');
        } else {
            header("location: $redirectUrl");
        }
    } else {
        header('location: /account?action=subscriptions');
    }
}

?>