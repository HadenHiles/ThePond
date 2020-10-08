<?php
require_once('../../../wp-load.php');

$chosenMembership = $_COOKIE['selected_membership'];
setcookie('selected_membership', null, -1, '/');
$hasActiveSubscription = false;

$meprUser = new MeprUser(get_current_user_id());

$subscriptions = $meprUser->active_product_subscriptions('ids', false, false);
$activeSubscriptions = $meprUser->active_product_subscriptions('ids');

if (empty($subscriptions)) {
    // Check if the user already chose a subscription
    if (!empty($chosenMembership)) {
        header('location: ' . $chosenMembership);
    } else {
        header('location: /');
    }
} else if (!empty($activeSubscriptions)) {
    header('location: /member-dashboard');
} else {
    header('location: /account?action=subscriptions');
}

get_footer("members");

?>