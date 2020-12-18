<?php
require_once('../../../wp-load.php');

$redirectUrl = !empty($_GET['redirect_to']) ? $_GET['redirect_to'] : $_COOKIE['redirect_to'];
setcookie('redirect_to', null, -1, '/');

$chosenMembership = $_COOKIE['selected_membership'];
setcookie('selected_membership', null, -1, '/');
$hasActiveSubscription = false;

$recentLogin = $_COOKIE['recent_login'];

$meprUser = new MeprUser(get_current_user_id());

$subscriptions = $meprUser->active_product_subscriptions('ids', false, false);
$activeSubscriptions = $meprUser->active_product_subscriptions('ids');

// Check if the unsigned in user has recently logged in or not
// * Is the user of this device a member or not -> redirect to login instead of signup
if (get_current_user_id() == 0 && !empty($recentLogin)) {
    setcookie('redirect_to', $redirectUrl, -1, '/');
    header('location: /login');
} else if (get_current_user_id() == 0 && empty($recentLogin) && empty($subscriptions)) {
    header('location: /#choose-your-subscription');
} else {
    // Refresh the recent_login cookie
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