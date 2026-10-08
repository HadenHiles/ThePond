<?php
/**
 * Plugin Name: The Pond Community Integration
 * Description: Adds BuddyBoss to the existing Pond theme while preserving Firebase, MemberPress and LearnDash flows.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Text Domain: thepond-community
 */

defined('ABSPATH') || exit;

add_action('admin_menu', 'thepond_community_admin_menu');
add_action('admin_init', 'thepond_community_register_setting');
add_action('admin_notices', 'thepond_community_admin_notice');
add_action('plugins_loaded', 'thepond_community_preserve_login', PHP_INT_MAX);
add_action('template_redirect', 'thepond_community_guard_page', 0);
add_action('admin_init', 'thepond_community_guard_ajax', PHP_INT_MAX);
add_filter('rest_request_before_callbacks', 'thepond_community_guard_rest', 10, 3);
add_filter('login_url', 'thepond_community_login_url', PHP_INT_MAX, 3);
add_filter('lostpassword_url', 'thepond_community_password_url', PHP_INT_MAX);
add_filter('bp_get_signup_allowed', '__return_false', PHP_INT_MAX);
add_filter('bp_disable_account_deletion', '__return_true', PHP_INT_MAX);
add_filter('bp_disable_avatar_uploads', '__return_true', PHP_INT_MAX);
add_filter('bp_attachments_current_user_can', 'thepond_community_avatar_permission', 100, 3);
add_filter('bp_enable_private_network', '__return_false', PHP_INT_MAX);
add_filter('pre_option_bb_rl_enabled', '__return_zero');
add_filter('bp_core_fetch_avatar_url_check', 'thepond_community_avatar_url', 1001, 2);
add_action('bp_setup_nav', 'thepond_community_account_nav', 100);
add_action('wp_enqueue_scripts', 'thepond_community_enqueue_styles', 100);
add_filter('bp_get_buddypress_template', 'thepond_community_templates', 100);

function thepond_community_is_open() {
    return thepond_community_sanitize_open(get_option('thepond_community_open', false));
}

function thepond_community_dependency_error() {
    if (!defined('BP_PLATFORM_VERSION') || !function_exists('buddypress')) {
        return new WP_Error('thepond_community_unavailable', __('BuddyBoss Platform must be active.', 'thepond-community'), array('status' => 503));
    }
    if (!class_exists('MeprUser')) {
        return new WP_Error('thepond_community_unavailable', __('MemberPress must be active before community access can be checked.', 'thepond-community'), array('status' => 503));
    }
    if (get_stylesheet() !== 'meltingpot-child') {
        return new WP_Error('thepond_community_unavailable', __('Melting Pot Child must remain the active theme.', 'thepond-community'), array('status' => 503));
    }
    return null;
}

function thepond_community_access_error() {
    $error = thepond_community_dependency_error();
    if ($error) {
        return $error;
    }
    if (current_user_can('manage_options')) {
        return null;
    }
    if (!thepond_community_is_open()) {
        return new WP_Error('thepond_community_not_open', __('The community is not open yet. Please return to your member dashboard.', 'thepond-community'), array('status' => 403));
    }
    if (!is_user_logged_in()) {
        return new WP_Error('thepond_community_login_required', __('Sign in through The Pond to access the community.', 'thepond-community'), array('status' => 401));
    }
    $user = new MeprUser(get_current_user_id());
    // Force a current entitlement check instead of reusing an earlier cached result.
    if (!$user->is_active()) {
        return new WP_Error('thepond_community_membership_required', __('An active membership is required. Manage your subscription in Account & Billing.', 'thepond-community'), array('status' => 403));
    }
    return null;
}

function thepond_community_is_page() {
    return function_exists('bp_is_blog_page') && !bp_is_blog_page();
}

function thepond_community_preserve_login() {
    // BuddyBoss otherwise changes WordPress's final login destination globally.
    remove_filter('login_redirect', 'bp_login_redirect', PHP_INT_MAX);
    remove_filter('register_url', 'bp_get_signup_page');
}

function thepond_community_login_url($url, $redirect, $force_reauth) {
    if (!thepond_community_is_page()) {
        return $url;
    }
    return home_url('/login/');
}

function thepond_community_password_url($url) {
    if (thepond_community_is_page() && class_exists('MeprOptions')) {
        return MeprOptions::fetch()->forgot_password_url();
    }
    return $url;
}

function thepond_community_guard_page() {
    if (!thepond_community_is_page()) {
        return;
    }
    nocache_headers();
    if (!defined('DONOTCACHEPAGE')) {
        define('DONOTCACHEPAGE', true);
    }
    $error = thepond_community_access_error();
    if ($error) {
        if ($error->get_error_code() === 'thepond_community_login_required') {
            wp_safe_redirect(home_url('/login/'));
            exit;
        }
        wp_die(
            esc_html($error->get_error_message()) . '<p><a href="' . esc_url(home_url('/account/')) . '">' .
            esc_html__('Account & Billing', 'thepond-community') . '</a> &middot; <a href="' .
            esc_url(home_url('/member-dashboard/')) . '">' . esc_html__('Member Dashboard', 'thepond-community') . '</a></p>',
            esc_html__('The Pond Community', 'thepond-community'),
            array('response' => $error->get_error_data()['status'])
        );
    }
    if (bp_is_register_page() || bp_is_activation_page()) {
        wp_die(esc_html__('Please use The Pond membership signup flow, not BuddyBoss registration.', 'thepond-community'), '', array('response' => 403));
    }
    if (bp_is_settings_component() && in_array(bp_current_action(), array('', 'general', 'delete-account'), true)) {
        wp_safe_redirect(home_url('/account/'));
        exit;
    }
    if (bp_is_user_profile() && in_array(bp_current_action(), array('change-avatar', 'delete-avatar'), true)) {
        wp_safe_redirect(home_url('/account/?action=avatar'));
        exit;
    }
}

function thepond_community_callback_is_buddyboss($callback) {
    if (!is_callable($callback)) {
        return false;
    }
    if (is_array($callback)) {
        $reflection = new ReflectionMethod($callback[0], $callback[1]);
    } elseif (is_string($callback) && strpos($callback, '::') !== false) {
        list($class, $method) = explode('::', $callback, 2);
        $reflection = new ReflectionMethod($class, $method);
    } elseif (is_object($callback) && !($callback instanceof Closure)) {
        $reflection = new ReflectionMethod($callback, '__invoke');
    } else {
        $reflection = new ReflectionFunction($callback);
    }
    $file = $reflection->getFileName();
    if (!$file) {
        return false;
    }
    $file = wp_normalize_path($file);
    foreach (array('buddyboss-platform', 'buddyboss-platform-pro', 'buddyboss-learndash') as $plugin) {
        if (strpos($file, wp_normalize_path(WP_PLUGIN_DIR) . '/' . $plugin . '/') === 0) {
            return true;
        }
    }
    return false;
}

function thepond_community_guard_ajax() {
    if (!wp_doing_ajax() || !isset($_REQUEST['action']) || !is_string($_REQUEST['action'])) {
        return;
    }
    global $wp_filter;
    $action = wp_unslash($_REQUEST['action']);
    if ($action === 'heartbeat') {
        $error = thepond_community_access_error();
        if (!$error) {
            return;
        }
        // Keep WordPress/editor heartbeat working, but remove community payloads.
        foreach (array('heartbeat_received', 'heartbeat_nopriv_received') as $hook) {
            if (!isset($wp_filter[$hook])) {
                continue;
            }
            foreach ($wp_filter[$hook]->callbacks as $priority => $callbacks) {
                foreach ($callbacks as $callback) {
                    if (thepond_community_callback_is_buddyboss($callback['function'])) {
                        remove_filter($hook, $callback['function'], $priority);
                    }
                }
            }
            add_filter($hook, function ($response) use ($error) {
                $response['thepond_community_error'] = array('code' => $error->get_error_code(), 'message' => $error->get_error_message());
                return $response;
            });
        }
        return;
    }
    $hook = (is_user_logged_in() ? 'wp_ajax_' : 'wp_ajax_nopriv_') . $action;
    if (!isset($wp_filter[$hook])) {
        return;
    }
    foreach ($wp_filter[$hook]->callbacks as $callbacks) {
        foreach ($callbacks as $callback) {
            if (thepond_community_callback_is_buddyboss($callback['function'])) {
                $error = thepond_community_access_error();
                if ($error) {
                    wp_send_json_error(array('code' => $error->get_error_code(), 'message' => $error->get_error_message()), $error->get_error_data()['status']);
                }
                return;
            }
        }
    }
}

function thepond_community_guard_rest($response, $handler, $request) {
    if ($response !== null) {
        return $response;
    }
    $namespace = function_exists('bp_rest_namespace') ? bp_rest_namespace() : 'buddyboss';
    $route = $request->get_route();
    $community_route = preg_match('#^/(?:' . preg_quote($namespace, '#') . '|buddypress)(?:/|$)#', $route);
    if (!$community_route && !thepond_community_callback_is_buddyboss($handler['callback'])) {
        return $response;
    }
    $error = thepond_community_access_error();
    if ($error) {
        return $error;
    }
    if (!in_array($request->get_method(), array('GET', 'HEAD', 'OPTIONS'), true)) {
        $identity_route = preg_match('#/(?:account-settings/(?:general|delete-account)|signup)(?:/|$)#', $route);
        $member_route = preg_match('#/members(?:/\\d+)?/?$#', $route);
        $avatar_route = preg_match('#/members/\\d+/avatar/?$#', $route);
        if ($identity_route || $avatar_route || ($member_route && (
            in_array($request->get_method(), array('POST', 'DELETE'), true) ||
            $request->has_param('email') || $request->has_param('user_email') || $request->has_param('password')
        ))) {
            return new WP_Error('thepond_community_account_managed', __('Use The Pond Account & Billing page to manage your account or avatar.', 'thepond-community'), array('status' => 403));
        }
    }
    return $response;
}

function thepond_community_avatar_url($url, $params) {
    if (($params['object'] ?? '') !== 'user' || empty($params['item_id'])) {
        return $url;
    }
    $id = (int) $params['item_id'];
    $firebase_url = get_user_meta($id, 'avatar_url', true);
    if (get_user_meta($id, 'use_firebase_avatar', true) && $firebase_url) {
        return esc_url_raw($firebase_url);
    }
    if (function_exists('has_wp_user_avatar') && has_wp_user_avatar($id)) {
        return get_wp_user_avatar_src($id, empty($params['width']) ? 96 : (int) $params['width']);
    }
    return $url;
}

function thepond_community_avatar_permission($allowed, $capability, $args) {
    if ($capability === 'edit_avatar' && ($args['object'] ?? '') === 'user') {
        return false;
    }
    return $allowed;
}

function thepond_community_account_nav() {
    if (!bp_is_active('settings')) {
        return;
    }
    bp_core_remove_subnav_item(bp_get_settings_slug(), 'general');
    bp_core_remove_subnav_item(bp_get_settings_slug(), 'delete-account');
}

function thepond_community_enqueue_styles() {
    if (!thepond_community_is_page()) {
        return;
    }
    wp_enqueue_style('thepond-community', get_stylesheet_directory_uri() . '/community.css', array('style'), '1.0.0');
}

function thepond_community_templates($templates) {
    return array_merge(array('buddypress.php'), array_diff($templates, array('buddypress.php')));
}

function thepond_community_admin_menu() {
    add_options_page(__('The Pond Community', 'thepond-community'), __('The Pond Community', 'thepond-community'), 'manage_options', 'thepond-community', 'thepond_community_settings_page');
}

function thepond_community_register_setting() {
    register_setting('thepond_community', 'thepond_community_open', array(
        'type' => 'boolean',
        'default' => false,
        'sanitize_callback' => 'thepond_community_sanitize_open',
    ));
}

function thepond_community_sanitize_open($value) {
    return $value === '1' || $value === 1 || $value === true;
}

function thepond_community_admin_notice() {
    if (!current_user_can('manage_options')) {
        return;
    }
    $error = thepond_community_dependency_error();
    if ($error) {
        echo '<div class="notice notice-warning"><p>' . esc_html__('The Pond Community: ', 'thepond-community') . esc_html($error->get_error_message()) . '</p></div>';
    }
}

function thepond_community_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('You do not have permission to manage these settings.', 'thepond-community'), '', array('response' => 403));
    }
    ?>
    <div class="wrap">
        <h1><?php esc_html_e('The Pond Community', 'thepond-community'); ?></h1>
        <?php settings_errors(); ?>
        <p><?php esc_html_e('Administrators can preview the community before launch. Other users need both an open community and an active MemberPress membership.', 'thepond-community'); ?></p>
        <p><?php esc_html_e('This integration keeps ReadyLaunch, BuddyBoss signup, site-wide private-network mode, account deletion and community avatar uploads disabled. Login destinations, billing, passwords, email addresses and avatars remain managed by the existing Pond flows.', 'thepond-community'); ?></p>
        <form method="post" action="options.php">
            <?php settings_fields('thepond_community'); ?>
            <input type="hidden" name="thepond_community_open" value="0">
            <label>
                <input type="checkbox" name="thepond_community_open" value="1" <?php checked(thepond_community_is_open()); ?>>
                <?php esc_html_e('Open the community to active members', 'thepond-community'); ?>
            </label>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
