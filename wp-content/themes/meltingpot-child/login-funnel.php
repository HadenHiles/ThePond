<?php
/**
* Template Name: Login Funnel
* Description: Only use for redirecting accordingly after login
*
* @package WordPress
* @subpackage Meltingpot-child
* @since Twenty Twenty
*/

ini_set('display_errors', 1);
global $smof_data;
get_header("members");

$hasActiveSubscription = false;

$meprUser = new MeprUser(get_current_user_id());

$subscriptions = $meprUser->active_product_subscriptions('ids', false, false);
$activeSubscriptions = $meprUser->active_product_subscriptions('ids');

if (empty($subscriptions)) {
    header('location: /choose-your-subscription');
} else if (!empty($activeSubscriptions)) {
    header('location: /member-dashboard');
} else {
    header('location: /account?action=subscriptions');
}
?>