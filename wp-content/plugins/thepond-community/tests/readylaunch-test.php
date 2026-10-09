<?php

define('ABSPATH', dirname(__DIR__, 4) . '/');
define('BP_PLATFORM_VERSION', '3.6.0');
require ABSPATH . 'wp-includes/plugin.php';
require ABSPATH . 'wp-includes/class-wp-error.php';

$community = in_array('--community', $argv, true);
$enabled = true;
$admin = false;
$ajax = false;
$embed = false;
$compat = true;
$logged_in = true;
$active = true;
$open = true;
$styles = array();
$checks = 0;

function check($condition, $message) {
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}

function get_option($name, $default = false) {
    global $enabled, $open;
    $pre = apply_filters('pre_option_' . $name, false, $name, $default);
    if ($pre !== false) {
        return $pre;
    }
    if ($name === 'bb_rl_enabled') {
        return $enabled;
    }
    if ($name === 'thepond_community_open') {
        return $open;
    }
    return $default;
}
function bp_get_option($name, $default = false) { return get_option($name, $default); }
function bb_is_readylaunch_enabled() { return bp_get_option('bb_rl_enabled'); }
function bp_is_blog_page() { global $community; return !$community; }
function is_admin() { global $admin; return $admin; }
function wp_doing_ajax() { global $ajax; return $ajax; }
function is_embed() { global $embed; return $embed; }
function bp_is_theme_compat_active() { global $compat; return $compat; }
function get_stylesheet() { return 'meltingpot-child'; }
function get_template() { return 'melting_pot'; }
function get_stylesheet_directory() { return ABSPATH . 'wp-content/themes/meltingpot-child'; }
function get_stylesheet_directory_uri() { return '/wp-content/themes/meltingpot-child'; }
function wp_enqueue_style($handle, $url, $deps, $version) { global $styles; $styles[$handle] = $version; }
function get_bloginfo($name, $context = '') { return 'The Pond'; }
function bp_get_title_parts() { return array('Haden', 'Profile'); }
function bp_get_canonical_url() { return 'https://example.test/community-members/haden/profile/'; }
function __($text, $domain) { return $text; }
function __return_true() { return true; }
function __return_false() { return false; }
function current_user_can($capability) { global $admin; return $admin; }
function is_user_logged_in() { global $logged_in; return $logged_in; }
function get_current_user_id() { return 123; }
function buddypress() { return new stdClass(); }
class MeprUser {
    public function __construct($id) {}
    public function is_active() { global $active; return $active; }
}

function bp_is_active($component) { return false; }
function bp_is_members_directory() { return false; }
function bp_is_video_directory() { return false; }
function bp_is_media_directory() { return false; }
function bp_is_document_directory() { return false; }
function bp_is_activity_directory() { return false; }
function bp_is_groups_directory() { return false; }
function bp_is_group_single() { return false; }
function bp_is_group_activity() { return false; }
function bp_is_group_create() { return false; }
function bp_is_user() { global $community; return $community; }
function bp_is_single_activity() { return false; }
function bp_is_user_activity() { return false; }
function bp_is_messages_component() { return false; }
function bp_is_current_component($component) { return false; }
function bp_is_register_page() { return false; }
function bp_enable_site_registration() { return true; }
function bp_allow_custom_registration() { return false; }
function bp_is_activation_page() { return false; }
function is_login() { return false; }
function wp_is_block_theme() { return false; }
function has_block($name, $post) { return false; }
function get_post($id) { return null; }
function get_the_ID() { return 0; }
function bp_locate_template($name) {
    return ABSPATH . 'wp-content/plugins/buddyboss-platform/bp-templates/bp-nouveau/readylaunch/' . $name;
}

require ABSPATH . 'wp-content/plugins/buddyboss-platform/bp-core/classes/class-bb-readylaunch.php';
$reflection = new ReflectionClass('BB_Readylaunch');
$readylaunch = $reflection->newInstanceWithoutConstructor();
$instance = $reflection->getProperty('instance');
if (PHP_VERSION_ID < 80100) {
    $instance->setAccessible(true);
}
$instance->setValue(null, $readylaunch);
function bb_load_readylaunch() { return BB_Readylaunch::instance(); }
function bb_learndash_load_readylaunch_helper() {
    require_once ABSPATH . 'wp-content/plugins/buddyboss-learndash/includes/class-bb-readylaunch-learndash-helper.php';
}

require dirname(__DIR__) . '/thepond-community.php';
$wp_query = new class {
    public function is_404() { return false; }
};
add_action('bp_init', array($readylaunch, 'bb_rl_init'), 9);
add_action('bb_integration_readylaunch_loaded', 'bb_learndash_load_readylaunch_helper');
add_action('wp_enqueue_scripts', array($readylaunch, 'bb_readylaunch_lms_enqueue_styles'));
$original_template = function ($path) { return '/pond/custom-lesson.php'; };
add_filter('learndash_template', $original_template);

do_action('bp_init');
check(class_exists('BB_Readylaunch_Learndash_Helper') === $community, 'LearnDash helper must load only for community requests');
check(has_filter('learndash_template', $original_template) !== false || $community, 'Existing lesson filter was removed');
check((has_action('wp_enqueue_scripts', array($readylaunch, 'bb_readylaunch_lms_enqueue_styles')) !== false) === $community, 'LMS stylesheet scope incorrect');
if (!$community) {
    check(has_action('wp_enqueue_scripts', array($readylaunch, 'bb_readylaunch_lms_enqueue_styles')) === false, 'LMS styles leaked to ordinary pages');
    check(apply_filters('learndash_template', '/default.php') === '/pond/custom-lesson.php', 'Custom lesson template changed');
}

check(bp_get_option('bb_rl_enabled_pages') === array('registration' => false, 'courses' => false, 'blog' => false), 'Optional layouts not excluded');
foreach (array('registration', 'courses', 'blog') as $page) {
    check(!$readylaunch->bb_rl_is_page_enabled_for_integration($page), 'Native optional layout enabled: ' . $page);
}
if ($community) {
    check(BB_Readylaunch_Learndash_Helper::instance()->bb_rl_override_learndash_template_path('/pond/lesson.php', '', array(), '', false) === '/pond/lesson.php', 'Native helper ignores Courses exclusion');
}
check(bb_is_readylaunch_enabled(), 'Native activation toggle must remain usable');
$enabled = false;
check(!bb_is_readylaunch_enabled(), 'Native deactivation toggle must remain usable');
$enabled = true;
check(thepond_community_readylaunch_page(true) === $community, 'Wrong frontend layout scope');
check(!thepond_community_readylaunch_page(false), 'Must not claim unsupported pages');
$admin = true;
check(thepond_community_readylaunch_page(true), 'ReadyLaunch admin settings blocked');
$admin = false;
$ajax = true;
check(thepond_community_readylaunch_page(true), 'ReadyLaunch AJAX blocked');
$ajax = false;

$layout = $readylaunch->override_page_templates('/pond/page.php');
check($layout === ($community ? bp_locate_template('layout.php') : '/pond/page.php'), 'Native ReadyLaunch layout selection failed');
check(thepond_community_template_include($layout) === $layout, 'Pond wrapper replaced ReadyLaunch or ordinary template');
thepond_community_enqueue_styles();
check(isset($styles['thepond-community']) === !$community, 'Compatibility CSS leaked into ReadyLaunch');
$enabled = false;
check(thepond_community_template_include('/elementor/canvas.php') === ($community ? get_stylesheet_directory() . '/buddypress.php' : '/elementor/canvas.php'), 'ReadyLaunch-off fallback failed');
thepond_community_enqueue_styles();
check($styles['thepond-community'] === '1.1.0', 'Fallback/header stylesheet version incorrect');
$enabled = true;
$embed = true;
check(thepond_community_template_include('/embed.php') === '/embed.php', 'Embed overridden');
$embed = false;
check(apply_filters('wpseo_title', 'No Access') === ($community ? 'Haden - Profile - The Pond' : 'No Access'), 'Incorrect SEO title');
check(apply_filters('wpseo_canonical', '/no-access/') === ($community ? bp_get_canonical_url() : '/no-access/'), 'Incorrect SEO URL');
check(apply_filters('bp_enable_private_network', false) === true, 'Public-site flag regressed');

check(thepond_community_access_error() === null, 'Active member denied');
$active = false;
check(thepond_community_access_error()->get_error_code() === 'thepond_community_membership_required', 'Inactive member admitted');
$logged_in = false;
check(thepond_community_access_error()->get_error_code() === 'thepond_community_login_required', 'Anonymous member admitted');
$admin = true;
check(thepond_community_access_error() === null, 'Administrator denied');
$admin = false;
$open = false;
check(thepond_community_access_error()->get_error_code() === 'thepond_community_not_open', 'Closed gate ignored');

echo 'PASS: ' . $checks . ' checks (' . ($community ? 'community' : 'ordinary/course') . " request)\n";
