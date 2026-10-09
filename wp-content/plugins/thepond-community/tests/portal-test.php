<?php
define('ABSPATH', dirname(__DIR__, 4) . '/');
define('BP_PLATFORM_VERSION', '3.6.0');
define('THEME_HOOK_PREFIX', 'buddyboss_theme_');
define('OBJECT', 'OBJECT');
require ABSPATH . 'wp-includes/plugin.php';
require ABSPATH . 'wp-includes/class-wp-error.php';
require ABSPATH . 'wp-includes/kses.php';
foreach (array('attribute-token', 'text-replacement', 'span', 'tag-processor') as $html_class) {
    require ABSPATH . 'wp-includes/html-api/class-wp-html-' . $html_class . '.php';
}

$checks = 0;
$member = true;
$access = true;
$post_type = 'sfwd-lessons';
$post_id = 3250;
$legacy = false;
$community = false;
$embed = false;
$admin_request = false;
$logged_in = true;
$theme_template = 'buddyboss-theme';
$pagenow = 'index.php';
$_SERVER['SCRIPT_NAME'] = '/index.php';
$styles = array();
$scripts = array();
$shortcodes = array();
$parts = array();
$fields = array(
    'video_code' => '<iframe src="https://player.vimeo.com/video/440111888"></iframe>',
    'above_media' => '<p>Before video</p>', 'below_media' => '<p>After video</p>',
    'prerequisite_skills' => array(77), 'targeted_skills' => array(88), 'skills' => array(99),
);
$rows = array(
    'tips_for_success' => array(array('title' => 'Tip title', 'content' => 'Tip content')),
    'common_mistakes' => array(array('title' => 'Mistake')),
    'goals' => array(array('goal' => 'Keep control')),
    'documents' => array(array('file_name' => 'Drill sheet', 'file' => '/drill.pdf', 'file_type' => 'PDF')),
    'resources' => array(array('resource_name' => 'Extra resource', 'resource_link' => '/extra/', 'resource_type' => 'Link')),
);
$row_positions = array();
$current_row = array();

function check($condition, $message) {
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
function get_template() { global $theme_template; return $theme_template; }
function get_stylesheet() { return 'buddyboss-theme-child-1.0.0'; }
function get_stylesheet_directory() { return ABSPATH . 'wp-content/themes/' . get_stylesheet(); }
function get_template_directory() { return ABSPATH . 'wp-content/themes/buddyboss-theme'; }
function get_stylesheet_directory_uri() { return '/wp-content/themes/' . get_stylesheet(); }
function get_option($key, $default = false) { return apply_filters('pre_option_' . $key, $default); }
function bp_get_option($key, $default = false) { return get_option($key, $default); }
function is_admin() { global $admin_request; return $admin_request; }
function wp_login_url($redirect = '', $force_reauth = false) {
    static $depth = 0;
    $depth++;
    try {
        if ($depth > 4) {
            throw new RuntimeException('Recursive login_url filter');
        }
        return apply_filters('login_url', home_url('/wp-login.php'), $redirect, $force_reauth);
    } finally {
        $depth--;
    }
}
// Mirror WordPress load.php's is_login(), including its login_url dependency.
function is_login() { return false !== stripos(wp_login_url(), $_SERVER['SCRIPT_NAME']); }
function wp_doing_ajax() { return false; }
function is_embed() { global $embed; return $embed; }
function bp_is_theme_compat_active() { return true; }
function bp_is_blog_page() { global $community; return !$community; }
function locate_template($name) { return ABSPATH . 'wp-content/themes/buddyboss-theme/' . $name; }
function is_user_logged_in() { global $logged_in; return $logged_in; }
function current_user_can($cap) { global $member; return $cap === 'memberpress_authorized' && $member; }
function get_current_user_id() { return 123; }
function buddypress() { return new stdClass(); }
function __($value, $domain = '') { return $value; }
function __return_true() { return true; }
function is_wp_error($value) { return $value instanceof WP_Error; }
function home_url($path = '') { return 'https://example.test' . $path; }
function wp_validate_redirect($url, $fallback = '') {
    if (strpos($url, '//') === 0) { return $fallback; }
    $host = parse_url($url, PHP_URL_HOST);
    return !$host || $host === 'example.test' ? $url : $fallback;
}
class PortalRedirect extends RuntimeException {}
function wp_safe_redirect($url) { throw new PortalRedirect($url); }
function nocache_headers() {}
function wp_unslash($value) { return stripslashes($value); }
function wp_die($message, $title = '', $args = array()) { throw new RuntimeException($message, $args['response'] ?? 500); }
function get_post_type($id = 0) { return $GLOBALS['post_type']; }
function get_page_by_path($path, $output = OBJECT, $type = 'page') { return $GLOBALS['training_forum'] ?? null; }
function bbp_get_forum_permalink($id) { return home_url('/forums/forum/training-discussions/'); }
function bbp_is_forum_open($id) { return $GLOBALS['forum_open'] ?? true; }
function bbp_user_can_view_forum($args) { return $GLOBALS['forum_visible'] ?? true; }
function bbp_is_single_forum() { return $GLOBALS['single_forum'] ?? false; }
function bbp_get_forum_id() { return $GLOBALS['current_forum_id'] ?? 60096; }
function bbp_is_post_request() { return $GLOBALS['forum_post_request'] ?? false; }
function bbp_is_topic_edit() { return $GLOBALS['forum_topic_edit'] ?? false; }
function post_password_required() { return $GLOBALS['password_protected'] ?? false; }
function add_query_arg($key, $value, $url) { return $url . '?' . $key . '=' . urlencode($value); }
function content_url($path = '') { return '/wp-content' . $path; }
function plugins_url($path = '') { return '/wp-content/plugins/' . $path; }
function admin_url($path = '') { return '/wp-admin/' . $path; }
function wp_create_nonce($action) { return $action; }
function wp_script_is($handle, $state) { global $scripts; return isset($scripts[$handle]); }
function wp_register_script($handle, $src, $deps = array(), $version = null, $footer = false) {
    global $scripts; $scripts[$handle] = array('src' => $src, 'deps' => $deps, 'footer' => $footer, 'version' => $version);
}
function wp_enqueue_script($handle, $src = '', $deps = array(), $version = null, $footer = false) {
    global $scripts;
    if ($src) { wp_register_script($handle, $src, $deps, $version, $footer); }
    elseif (!isset($scripts[$handle])) { $scripts[$handle] = array('src' => '', 'deps' => array()); }
}
function wp_localize_script($handle, $name, $values) { $GLOBALS['localized'][$handle][$name] = $values; }
function wp_enqueue_style($handle, $src = '', $deps = array(), $version = null) {
    global $styles; $styles[$handle] = $src;
}
function load_theme_textdomain($domain, $path) {}
function register_nav_menus($locations) {}
function register_post_type($name, $args) { $GLOBALS['post_types'][$name] = $args; }
function register_taxonomy($name, $objects, $args) { $GLOBALS['taxonomies'][$name] = array($objects, $args); }
function add_post_type_support($type, $feature) {}
function _x($text, $context, $domain = '') { return $text; }
function add_shortcode($name, $callback) { global $shortcodes; $shortcodes[$name] = $callback; }
function shortcode_atts($defaults, $atts, $tag = '') { return array_merge($defaults, array_intersect_key($atts, $defaults)); }
function is_page_template($templates) {
    global $legacy, $page_template;
    return isset($page_template) ? in_array($page_template, (array) $templates, true) : $legacy;
}
function is_singular($types) { global $post_type; return in_array($post_type, (array) $types, true); }
function is_post_type_archive($type) { return false; }
function sfwd_lms_has_access($course, $user) { global $access; return $access; }
function absint($value) { return abs((int) $value); }
function esc_html($value) { return htmlspecialchars((string) $value, ENT_QUOTES); }
function esc_html_e($value, $domain = '') { echo esc_html($value); }
function esc_html__($value, $domain = '') { return esc_html($value); }
function esc_attr($value) { return esc_html($value); }
function esc_attr_e($value, $domain = '') { echo esc_attr($value); }
function esc_url($value) { return $value; }
function esc_url_raw($value) { return $value; }
function get_user_meta($id, $key, $single = false) {
    $value = $GLOBALS['avatar_meta'][$id][$key] ?? '';
    return $single ? $value : ($value === '' ? array() : array($value));
}
function has_wp_user_avatar($id) { return !empty($GLOBALS['uploaded_avatars'][$id]); }
function get_wp_user_avatar_src($id, $size = 96) {
    throw new RuntimeException('Avatar URL helper recursively renders through get_wp_user_avatar filters');
}
function bp_core_get_user_domain($id) { return home_url('/community-members/member-' . $id . '/'); }
function get_the_ID() { global $post_id; return $post_id; }
function get_queried_object_id() { return get_the_ID(); }
function get_the_id_for_test() { return get_the_ID(); }
function get_the_title($id = 0) { return 'Title ' . $id; }
function get_term_by($field, $value, $taxonomy) { return (object) array('term_id' => 10); }
function get_terms($taxonomy, $args = array()) { return array((object) array('term_id' => 10, 'slug' => 'passing', 'name' => 'Passing')); }
function get_permalink($id = 0) { return '/item/' . (is_object($id) ? $id->ID : $id); }
function get_post_permalink($id) { return get_permalink($id); }
function get_the_content() { return '<p>Existing page content</p>'; }
function get_the_post_thumbnail_url() { return ''; }
function wp_get_post_terms($id, $taxonomy, $args) {
    if (($args['fields'] ?? '') === 'ids') return array(10);
    return array((object) array('name' => $taxonomy === 'library_category' ? $GLOBALS['detail_category'] : 'Skating'));
}
function get_post($id) { return (object) array('ID' => $id, 'post_title' => 'Course ' . $id); }
function get_post_thumbnail_id($id) { return 0; }
function wp_get_attachment_image_src($id, $size, $icon = false, $attr = '') {
    $GLOBALS['attachment_image_request'] = array($id, $size);
    return array($id === 456 ? 'https://example.test/uploaded.jpg' : '/course.png');
}
function get_current_blog_id() { return 1; }
function get_post_meta($id, $key, $single = false) { return ''; }
function get_the_terms($id, $taxonomy) { return array((object) array('slug' => 'skating', 'name' => 'Skating')); }
function learndash_course_progress($args) {
    $GLOBALS['course_progress_calls'][] = $args;
    return $GLOBALS['course_progress_results'][$args['course_id']];
}
function learndash_get_course_id($id = 0) { return 3194; }
function get_field($name, $id = 0) { global $fields; return isset($fields[$name]) ? $fields[$name] : false; }
function have_rows($name, $id = 0) {
    global $rows, $row_positions;
    $key = $name . ':' . $id;
    $position = isset($row_positions[$key]) ? $row_positions[$key] : 0;
    $GLOBALS['next_row_key'] = $key;
    $GLOBALS['next_row_name'] = $name;
    if (!isset($rows[$name][$position])) {
        $row_positions[$key] = 0;
        return false;
    }
    return true;
}
function the_row() {
    global $rows, $row_positions, $current_row;
    $key = $GLOBALS['next_row_key'];
    $position = isset($row_positions[$key]) ? $row_positions[$key] : 0;
    $current_row = $rows[$GLOBALS['next_row_name']][$position];
    $row_positions[$key] = $position + 1;
}
function get_sub_field($name) { global $current_row; return isset($current_row[$name]) ? $current_row[$name] : false; }
function get_template_part($slug) {
    global $parts; $parts[] = $slug;
    $file = get_stylesheet_directory() . '/' . $slug . '.php';
    check(is_readable($file), 'Missing template part: ' . $slug);
    require $file;
}
function wp_reset_postdata() { $GLOBALS['reset_count']++; }
class MeprUser {
    public function __construct($id = 0) {}
    public function is_active() { return true; }
}
class MeprOptions {
    public static function fetch() { return new self(); }
    public function forgot_password_url() { return home_url('/forgot-password/'); }
}
class WP_Post { public $post_author; }
class WP_User { public $ID; public function __construct($id) { $this->ID = $id; } }
class WP_Query {
    private $posts;
    public function __construct($args) {
        $this->posts = $args['post_type'] === array('sfwd-courses') ? ($GLOBALS['course_feed_ids'] ?? array()) : array();
    }
    public function have_posts() { return !empty($this->posts); }
    public function the_post() { $GLOBALS['post_id'] = array_shift($this->posts); }
    public function get_posts() { return array(); }
}
function get_header() { echo '<header>Site header</header>'; }
function get_footer() { echo '<footer>Site footer</footer>'; }
function has_post_thumbnail() { return false; }
function have_posts() { return $GLOBALS['remaining_posts'] > 0; }
function the_post() { $GLOBALS['remaining_posts']--; }
function the_title() { echo 'Login'; }
function the_content() { echo '<p>Existing page content</p>'; }
function the_field($name) { echo get_field($name); }
function dynamic_sidebar($name) { echo '<p>Existing membership sidebar: ' . esc_html($name) . '</p>'; }
function get_user_by($field, $id) {
    return (object) array('ID' => is_numeric($id) ? (int) $id : 123, 'user_login' => '<member>');
}
function wp_logout_url($redirect) { return '/wp-login.php?action=logout'; }
function do_shortcode($shortcode) {
    $GLOBALS['rendered_shortcodes'][] = $shortcode;
    return '<span data-shortcode="' . esc_attr($shortcode) . '"></span>';
}
function shortcode_exists($shortcode) { return $GLOBALS['facebook_shortcode_enabled'] ?? true; }
class PortalDatabase {
    public $prefix;
    public function prepare($query) { return $query; }
    public function get_var($query) { return 0; }
    public function get_blog_prefix($id) { return 'wp_'; }
}
$wpdb = new PortalDatabase();
$wpdb->prefix = 'wp_';
$reset_count = 0;

ob_start();
require dirname(__DIR__) . '/thepond-community.php';
require get_stylesheet_directory() . '/functions.php';
require ABSPATH . 'wp-content/plugins/challenge-score/filters.php';
do_action('after_setup_theme');
check(ob_get_clean() === '', 'Theme initialization contaminates AJAX responses');
do_action('init');
check(isset($GLOBALS['post_types']['skills'], $GLOBALS['post_types']['skill-examples'], $GLOBALS['post_types']['content-library']), 'Content types were not registered');
check(isset($GLOBALS['taxonomies']['performance-level'], $GLOBALS['taxonomies']['skill-type'], $GLOBALS['taxonomies']['library_category']), 'Existing taxonomy slugs changed');
check(function_exists('skills_post_type') && function_exists('register_cpt_content_library'), 'Content registrations did not load');
check(isset($shortcodes['ld_courses_by_categories'], $shortcodes['list_library_items'], $shortcodes['lessonhistory']), 'Portable shortcodes missing');
check(get_option('bb_rl_enabled', true) === '0', 'ReadyLaunch competes with BuddyBoss Theme');
check(thepond_community_dependency_error() === null, 'BuddyBoss child rejected');
$bp_pages = (object) array(
    'register' => (object) array('name' => 'register', 'id' => 1),
    'activate' => (object) array('name' => 'activate', 'id' => 2),
    'activity' => (object) array('name' => 'news-feed', 'id' => 3),
    'members' => (object) array('name' => 'members', 'id' => 4),
    'groups' => (object) array('name' => 'groups', 'id' => 5),
);
$portal_pages = apply_filters('bp_pages', $bp_pages);
check(!isset($portal_pages->register) && !isset($portal_pages->activate), 'BuddyBoss still claims MemberPress registration or activation routes');
check(isset($bp_pages->register, $bp_pages->activate), 'Registration filter mutates the original page map');
foreach (array('activity', 'members', 'groups') as $component) {
    check($portal_pages->$component === $bp_pages->$component, 'Community component route changed: ' . $component);
}
$admin_request = true;
check(apply_filters('bp_pages', $bp_pages) === $bp_pages, 'Administrative registration bindings changed');
$admin_request = false;
check(thepond_community_templates(array('members/single/home.php')) === array('members/single/home.php'), 'Native BuddyBoss hierarchy replaced');
check(thepond_community_template_include('/native.php') === '/native.php', 'Native template replaced');
$community = true;
check(thepond_community_template_include('/elementor/canvas.php') === locate_template('buddypress.php'), 'Elementor replaced the native community shell');
$embed = true;
check(thepond_community_template_include('/embed.php') === '/embed.php', 'Community embed replaced');
$embed = false; $community = false;
check(apply_filters('single_template', '/native-library.php') === '/native-library.php', 'Challenge Score replaced the migrated single template');
thepond_community_enqueue_styles();
check(!isset($styles['thepond-community']), 'Melting Pot community CSS loaded');
check(thepond_community_login_url('/wp-login.php', '', false) === home_url('/login/'), 'Native header login bypasses Firebase');
check(thepond_community_login_url('/wp-login.php', '', true) === '/wp-login.php', 'Administrator reauthentication changed');
$admin_request = true;
check(thepond_community_login_url('/wp-login.php', '', false) === '/wp-login.php', 'WordPress admin login changed');
$admin_request = false; $pagenow = 'wp-login.php';
check(thepond_community_login_url('/wp-login.php', '', false) === '/wp-login.php', 'Core login page changed');
check(thepond_community_password_url('/wp-login.php?action=lostpassword') === '/wp-login.php?action=lostpassword', 'Core password reset changed');
$pagenow = 'index.php';
check(wp_login_url() === home_url('/login/'), 'Real login_url hook fails or recurses');
check(!is_login(), 'WordPress frontend login detection fails or recurses');
$pagenow = 'wp-login.php'; $_SERVER['SCRIPT_NAME'] = '/wp-login.php';
check(is_login(), 'WordPress core login detection changed');
$pagenow = 'index.php'; $_SERVER['SCRIPT_NAME'] = '/index.php';
check(strpos(thepond_community_login_url('/wp-login.php', '/lesson/', false), 'redirect_to=') !== false, 'Login destination lost');
check(pond_theme_registration_url('/wp-login.php?action=register') === home_url('/#pricingSect'), 'Signup bypasses membership selection');
foreach (array('firebase-login.php', 'members-templates/member_register.php', 'members-templates/member-dashboard.php', 'members-templates/member_support.php') as $page_template) {
    $body_classes = apply_filters('body_class', array('logged-in', 'bb-buddypanel'));
    $auth_page = in_array($page_template, array('firebase-login.php', 'members-templates/member_register.php'), true);
    check(in_array('pond-auth-page', $body_classes, true) === $auth_page, 'Auth navigation scope changed: ' . $page_template);
    check(array_slice($body_classes, 0, 2) === array('logged-in', 'bb-buddypanel'), 'Native body classes were removed');
}
$page_template = null;

foreach (array('buddyboss-theme', 'melting_pot') as $theme_template) {
    foreach (array(false, true) as $community) {
        $mapped = $theme_template === 'buddyboss-theme' || $community;
        check(wp_login_url() === home_url($mapped ? '/login/' : '/wp-login.php'), 'Frontend login hook changed or recurses: ' . $theme_template);
        check(wp_login_url('', true) === home_url('/wp-login.php'), 'Reauthentication hook changed: ' . $theme_template);
        $reset_url = home_url('/wp-login.php?action=lostpassword');
        check(apply_filters('lostpassword_url', $reset_url) === ($mapped ? MeprOptions::fetch()->forgot_password_url() : $reset_url), 'Frontend password reset hook changed: ' . $theme_template);
        $admin_request = true;
        check(wp_login_url() === home_url('/wp-login.php'), 'Admin login hook changed: ' . $theme_template);
        check(apply_filters('lostpassword_url', $reset_url) === $reset_url, 'Admin password reset hook changed: ' . $theme_template);
        $admin_request = false; $pagenow = 'wp-login.php';
        check(wp_login_url() === home_url('/wp-login.php'), 'Core login hook changed: ' . $theme_template);
        check(apply_filters('lostpassword_url', $reset_url) === $reset_url, 'Core password reset hook changed: ' . $theme_template);
        $pagenow = 'index.php';
    }
}
$theme_template = 'buddyboss-theme'; $community = false;

$tabs = array(array('id' => 'content', 'content' => '<p>Native lesson content</p>'), array('id' => 'materials', 'content' => 'Native materials'));
$rendered = apply_filters('learndash_content_tabs', $tabs, 'lesson', 3194, 123);
foreach (array('440111888', 'Before video', 'After video', 'Tip content', 'Mistake', 'Keep control', '/drill.pdf', '/extra/', '/item/77', '/item/88', '/item/99', 'Native lesson content', 'tool_fav', 'tool_bookmark') as $text) {
    check(strpos($rendered[0]['content'], $text) !== false, 'Course material missing: ' . $text);
}
check($rendered[1] === $tabs[1], 'Native materials tab replaced');
check(apply_filters('learndash_content_tabs', $rendered, 'lesson', 3194, 123) === $rendered, 'Course material duplicated');
$access = false;
check(apply_filters('learndash_content_tabs', $tabs, 'lesson', 3194, 123) === $tabs, 'Unenrolled media exposed');
$access = true; $member = false;
check(apply_filters('learndash_content_tabs', $tabs, 'lesson', 3194, 123) === $tabs, 'Nonmember media exposed');
$member = true;
check(apply_filters('learndash_content_tabs', $tabs, 'quiz', 3194, 123) === $tabs, 'Quiz changed');
$post_type = 'page';
check(apply_filters('learndash_content_tabs', $tabs, 'lesson', 3194, 123) === $tabs, 'Ordinary page changed');
$post_type = 'sfwd-topic';
check(strpos(apply_filters('learndash_content_tabs', $tabs, 'topic', 3194, 123)[0]['content'], '440111888') !== false, 'Topic media missing');
$post_type = 'sfwd-courses';
check(strpos(apply_filters('learndash_content_tabs', $tabs, 'course', 3194, 123)[0]['content'], '440111888') !== false, 'Course media missing');

ob_start();
$cards = display_library_items(array());
$leak = ob_get_clean();
check($leak === '' && strpos($cards, 'challenges-wrapper') !== false, 'Library shortcode leaks output');
ob_start();
$courses = learndash_courses_by_categories(array());
$leak = ob_get_clean();
check($leak === '' && strpos($courses, 'dashboard-courses') !== false, 'Course shortcode leaks output');
check($reset_count === 2, 'Secondary loops did not restore dashboard fields');
$saved_post_id = $post_id;
$course_feed_ids = array(101, 102, 103, 104, 105);
$course_progress_results = array(
    101 => array('percentage' => 25, 'completed' => 1, 'total' => 4),
    102 => array('percentage' => 100, 'completed' => 6, 'total' => 6),
    103 => array('percentage' => 0, 'completed' => 0, 'total' => 0),
    104 => array('percentage' => 0, 'completed' => 0, 'total' => 4),
    105 => array('percentage' => 37, 'completed' => 3, 'total' => 8),
);
$course_progress_calls = array();
$courses = learndash_courses_by_categories(array('categories' => 'skating'));
preg_match_all('/<a class="course-item[^"]*"[^>]*>.*?<\/a>/s', $courses, $course_cards);
check(count($course_cards[0]) === count($course_feed_ids), 'Native progress changed course visibility');
foreach ($course_feed_ids as $index => $course_id) {
    check($course_progress_calls[$index] === array('user_id' => 123, 'course_id' => $course_id, 'array' => true),
        'Card progress must use the native LearnDash API for the current user and course');
    $percentage = $course_progress_results[$course_id]['percentage'];
    check(strpos($course_cards[0][$index], 'class="progress-bar-small" style="width: ' . $percentage . '%"') !== false,
        'Card bar differs from native LearnDash percentage');
    check((strpos($course_cards[0][$index], '>Complete</div>') !== false) === ($percentage === 100),
        'Completed course label differs from native LearnDash progress');
    if ($percentage > 0 && $percentage < 100) {
        check(strpos($course_cards[0][$index], '>' . $percentage . '%</div>') !== false, 'Card percentage label changed');
    }
}
check(count($course_progress_calls) === count($course_feed_ids), 'Native progress should be fetched once per course');
$course_feed_ids = array();
$post_id = $saved_post_id;
$legacy = false; $post_type = 'page';
do_action('wp_enqueue_scripts');
check(isset($scripts['pond-firebase']) && in_array('buddyboss-child-js', $scripts['pond-firebase']['deps'], true), 'Firebase cookie helper dependency missing');
check(isset($scripts['pond-appearance']) && !$scripts['pond-appearance']['footer'], 'Appearance preference must load in the head');
check($GLOBALS['localized']['pond-appearance']['pondAppearance']['lightColor'] === '#14345a', 'Light appearance branding missing');
check($GLOBALS['localized']['pond-appearance']['pondAppearance']['darkColor'] === '#36cce4', 'Dark appearance branding missing');
check(!isset($scripts['pond-dashboard'], $styles['pond-legacy']), 'Legacy dashboard assets leak to native pages');
check(!isset($scripts['jquery']) || $scripts['jquery']['src'] === '', 'WordPress jQuery replaced');
$legacy = true;
do_action('wp_enqueue_scripts');
check(isset($styles['pond-grid'], $styles['pond-legacy'], $scripts['pond-dashboard']), 'Dashboard assets missing');
check($scripts['pond-dashboard']['version'] === filemtime(get_stylesheet_directory() . '/assets/js/pond-dashboard.js'),
    'Dashboard script updates must invalidate stale browser caches');
$menu_items = array(
    (object) array('url' => home_url('/member-dashboard/'), 'current' => true, 'classes' => array('current-menu-item', 'current_page_item', 'menu-item')),
    (object) array('url' => '/member-dashboard/#courses', 'current' => true, 'classes' => array('scroll', 'current-menu-item', 'current_page_item')),
    (object) array('url' => home_url('/member-dashboard/#skills-vault'), 'current' => true, 'classes' => array('current-menu-item', 'menu-item')),
    (object) array('url' => '/news-feed/', 'current' => false, 'classes' => array('menu-item')),
);
$menu_urls = array_map(function ($item) { return $item->url; }, $menu_items);
$rendered_menu = apply_filters('wp_nav_menu_objects', $menu_items);
check($rendered_menu[0]->current && in_array('current-menu-item', $rendered_menu[0]->classes, true), 'Dashboard page highlight removed');
foreach (array(1, 2) as $index) {
    check(!$rendered_menu[$index]->current, 'Dashboard section incorrectly marked as current page');
    check(!array_intersect(array('current-menu-item', 'current_page_item'), $rendered_menu[$index]->classes), 'Dashboard section highlight retained');
    check($rendered_menu[$index]->url === $menu_urls[$index], 'Dashboard anchor destination changed');
}
check($rendered_menu[1]->classes === array('scroll'), 'Dashboard anchor scroll class removed');
check($rendered_menu[3]->classes === array('menu-item'), 'Community navigation changed');
$legacy = false;
$other_menu = array((object) array('url' => '/other/#section', 'current' => true, 'classes' => array('current-menu-item')));
check(apply_filters('wp_nav_menu_objects', $other_menu)[0]->current, 'Other template navigation changed');
$account_menu = pond_theme_account_menu('<li>Legacy icons</li>', (object) array('theme_location' => 'header-my-account'));
foreach (array('My Training', 'Account &amp; Billing', 'Change Profile Photo', 'Community Profile', 'My Content', 'Help &amp; Support', 'Sign Out') as $label) {
    check(strpos($account_menu, $label) !== false, 'Account menu label missing: ' . $label);
}
foreach (array('/member-dashboard/', '/account/', '/account/?action=avatar', '/community-members/member-123/', '/item/387', '/item/392', 'action=logout') as $url) {
    check(strpos($account_menu, $url) !== false, 'Canonical account destination missing: ' . $url);
}
check(pond_theme_account_menu('Other menu', (object) array('theme_location' => 'primary-menu')) === 'Other menu', 'Primary navigation replaced');
$logged_in = false;
check(pond_theme_account_menu('Public menu', (object) array('theme_location' => 'header-my-account')) === 'Public menu', 'Public account menu replaced');
$logged_in = true;
$avatar_meta = array(123 => array('avatar_url' => 'https://example.test/social.jpg', 'use_firebase_avatar' => true, 'wp_user_avatar' => 456));
$uploaded_avatars = array(123 => 'https://example.test/uploaded.jpg');
$avatar_html = '<img src="https://example.test/default.jpg" srcset="https://example.test/default-2x.jpg 2x" class="avatar photo" alt="Member" width="100" height="100" loading="lazy">';
$vendor_avatar_override = function ($avatar) use ($avatar_html) { return $avatar_html; };
add_filter('get_avatar', $vendor_avatar_override, 10);
foreach (array(123, 'member@example.test', new WP_User(123), (object) array('user_id' => 123)) as $identity) {
    $result = apply_filters('get_avatar', $avatar_html, $identity, 100, '', 'Member', array());
    check(strpos($result, 'src="https://example.test/social.jpg"') !== false, 'Social avatar lost for user identifier');
    check(strpos($result, 'srcset') === false, 'Stale default retina avatar retained');
    check(strpos($result, 'loading="lazy"') !== false && strpos($result, 'class="avatar photo"') !== false, 'Native avatar markup lost');
    check(apply_filters('get_avatar_url', '/default.jpg', $identity, array('size' => 100)) === 'https://example.test/social.jpg', 'Avatar URL API differs');
}
$post_author = new WP_Post(); $post_author->post_author = 123;
check(thepond_portal_avatar_user_id($post_author) === 123, 'Post author avatar resolution changed');
foreach (array('bp_core_fetch_avatar_url_check', 'bp_core_fetch_avatar_url') as $avatar_filter) {
    check(apply_filters($avatar_filter, '/default.jpg', array('object' => 'user', 'item_id' => 123)) === 'https://example.test/social.jpg', 'BuddyBoss URL preference lost');
}
check(strpos(apply_filters('bp_core_fetch_avatar', $avatar_html, array('object' => 'user', 'item_id' => 123)), 'social.jpg') !== false, 'BuddyBoss HTML preference lost');
check(apply_filters('bp_core_fetch_avatar', $avatar_html, array('object' => 'group', 'item_id' => 123)) === $avatar_html, 'Group avatar changed');
check(apply_filters('get_avatar', $avatar_html, 123, 100, '', 'Member', array('force_default' => true)) === $avatar_html, 'Forced default avatar changed');
$avatar_meta[123]['use_firebase_avatar'] = false;
check(strpos(apply_filters('get_avatar', $avatar_html, 123, 100, '', 'Member'), 'uploaded.jpg') !== false, 'Uploaded avatar preference lost');
check($GLOBALS['attachment_image_request'] === array(456, array(100, 100)), 'Uploaded avatar must use its attachment ID and requested dimensions');
check(strpos(apply_filters('get_wp_user_avatar', $avatar_html, 123, 'thumbnail', '', 'Member'), 'uploaded.jpg') !== false,
    'Vendor HTML avatar filter must resolve uploads without recursively rendering an avatar');
check($GLOBALS['attachment_image_request'] === array(456, 'thumbnail'), 'Named attachment image sizes must be preserved');
$attachment_override = function ($image) { return array('https://example.test/filtered-upload.jpg'); };
add_filter('wpua_get_attachment_image_src', $attachment_override);
check(thepond_community_avatar_url('', array('object' => 'user', 'item_id' => 123)) === 'https://example.test/filtered-upload.jpg',
    'Existing attachment source filters must be preserved');
remove_filter('wpua_get_attachment_image_src', $attachment_override);
$uploaded_avatars = array();
check(apply_filters('get_avatar', $avatar_html, 123, 100, '', 'Member') === $avatar_html, 'Existing fallback avatar changed');
$avatar_meta = array();
remove_filter('get_avatar', $vendor_avatar_override, 10);
$legacy = true;
foreach (array('pond-bootstrap', 'pond-datatables', 'pond-popper') as $handle) {
    check(is_readable(ABSPATH . ltrim($scripts[$handle]['src'], '/')), 'Legacy static dependency missing: ' . $handle);
}
check(strpos(file_get_contents(get_stylesheet_directory() . '/members-templates/member_register.php'), 'setcookie') <
    strpos(file_get_contents(get_stylesheet_directory() . '/members-templates/member_register.php'), 'get_header'), 'Signup cookie sent after output');
check(strpos(file_get_contents(get_stylesheet_directory() . '/home.php'), '/melting_pot/home.php') !== false, 'Static public landing page lost');
$auth_hook_args = null;
add_action('mo_custom_login_form_end', function ($username, $password, $submit) use (&$auth_hook_args) {
    $auth_hook_args = array($username, $password, $submit);
}, 10, 3);
$fields['show_full_width_section'] = 'yes';
$fields['full_width_section'] = 'Optional training content';
foreach (array(false, true) as $logged_in) {
    foreach (array(false, true) as $reset) {
        $_GET = $reset ? array('action' => 'forgot_password') : array();
        $remaining_posts = 1;
        $rendered_shortcodes = array();
        $auth_hook_args = null;
        ob_start();
        try {
            require get_stylesheet_directory() . '/firebase-login.php';
            check(!$logged_in || $reset, 'Signed-in login visit failed to redirect');
        } catch (PortalRedirect $redirect) {
            $auth_html = ob_get_clean();
            check($logged_in && !$reset, 'Anonymous sign-in or reset unexpectedly redirected');
            check($redirect->getMessage() === home_url('/member-dashboard/'), 'Signed-in login does not default to dashboard');
            check($auth_html === '', 'Login redirect happened after output');
            continue;
        }
        $auth_html = ob_get_clean();
        check(strpos($auth_html, 'class="pond-auth-card"') !== false, 'Login card shell missing');
        check(strpos($auth_html, 'large-4 columns') === false, 'Legacy three-column login layout retained');
        check(strpos($auth_html, 'Optional training content') !== false, 'Optional login content removed');
        if (!$logged_in) {
            check(in_array('[mepr-login-form use_redirect="true"]', $rendered_shortcodes, true), 'MemberPress login/reset shortcode changed');
            check(in_array('[mo_firebase_auth_login]', $rendered_shortcodes, true), 'Firebase shortcode initialization removed');
            check($auth_hook_args === array('user_login', 'user_pass', 'wp-submit'), 'Firebase form hook or field identifiers changed');
            check(strpos($auth_html, 'Existing page content') !== false, 'Login page content removed');
            check(strpos($auth_html, home_url('/#pricingSect')) !== false, 'Membership link misses the live pricing section');
            if ($reset) {
                check(strpos($auth_html, 'Reset your password') !== false, 'Password reset heading missing');
                check(strpos($auth_html, 'class="pond-auth-providers"') === false, 'Sign-in providers clutter password reset');
            } else {
                foreach (array('Google' => 'google', 'Facebook' => 'facebook') as $provider => $class) {
                    check(strpos($auth_html, 'type="button" class="' . $class . '-btn pond-auth-provider" id="mo_firebase_' . $provider . '_provider_login"') !== false, 'Accessible Firebase provider or identifier missing: ' . $provider);
                }
            }
        } else {
            check(!$rendered_shortcodes, 'Anonymous form rendered for a signed-in member');
            check(strpos($auth_html, '&lt;member&gt;</h2>') !== false, 'Signed-in username is unescaped or heading is mismatched');
            foreach (array('/member-dashboard', '/account', 'action=logout') as $destination) {
                check(strpos($auth_html, $destination) !== false, 'Signed-in account action missing: ' . $destination);
            }
        }
    }
}
$_GET = array();
$logged_in = true;
foreach (array(
    '' => home_url('/member-dashboard/'),
    '/lesson/' => '/lesson/',
    home_url('/courses/example/') => home_url('/courses/example/'),
    home_url('/login/?redirect_to=/login/') => home_url('/member-dashboard/'),
    '/login/' => home_url('/member-dashboard/'),
    'https://outside.test/' => home_url('/member-dashboard/'),
    '//outside.test/' => home_url('/member-dashboard/'),
) as $requested => $expected) {
    check(pond_theme_login_destination($requested) === $expected, 'Login destination changed or loops: ' . $requested);
}

$training_forum = (object) array('ID' => 60096, 'post_status' => 'private');
$member = true; $access = true; $post_type = 'sfwd-lessons'; $post_id = 3250;
add_filter('pre_option_thepond_community_open', '__return_true');
check(pond_theme_lesson_discussion_url(3250) === home_url('/forums/forum/training-discussions/') . '?pond_lesson=3250#new-post', 'Lesson forum context link missing');
check(!apply_filters('comments_open', true, 3250), 'New lesson conversations remain split between comments and forums');
ob_start();
do_action('learndash-lesson-after', 3250, 3194, 123);
$discussion_html = ob_get_clean();
check(strpos($discussion_html, 'Start a lesson discussion') !== false, 'Discussion panel missing when a lesson has no old comments');
check(strpos($discussion_html, 'pond_lesson=3250#new-post') !== false, 'Discussion panel loses lesson context');
check(strpos(file_get_contents(get_stylesheet_directory() . '/comments.php'), "get_template_directory() . '/comments.php'") !== false, 'Native existing response template replaced');
$_GET = array('pond_lesson' => '3250'); $single_forum = true;
pond_theme_lesson_topic_context();
check(apply_filters('bbp_get_form_topic_title', '') === 'Title 3250', 'Lesson topic title not prefilled');
check(strpos(apply_filters('bbp_get_form_topic_content', ''), '/item/3250') !== false, 'Lesson context link missing from discussion');
check(apply_filters('bbp_get_form_topic_content', 'Draft') === 'Draft', 'Existing discussion draft overwritten');
foreach (array('forum_post_request', 'forum_topic_edit') as $flag) {
    $GLOBALS[$flag] = true;
    check(apply_filters('bbp_get_form_topic_title', '') === '', 'Posted/edited topic prefilled: ' . $flag);
    $GLOBALS[$flag] = false;
}
foreach (array('member', 'access', 'forum_visible', 'forum_open') as $flag) {
    $GLOBALS[$flag] = false;
    check(pond_theme_lesson_discussion_url(3250) === '', 'Forum link ignores access gate: ' . $flag);
    $GLOBALS[$flag] = true;
}
$_GET = array('pond_lesson' => '3250bad');
try {
    pond_theme_lesson_topic_context();
    check(false, 'Malformed lesson discussion accepted');
} catch (RuntimeException $error) {
    check($error->getCode() === 400, 'Malformed discussion does not return explicit input error');
}
$_GET = array(); $single_forum = false; $training_forum = null;
check(apply_filters('comments_open', true, 3250), 'Lesson comments disabled without a configured forum');
$post_type = 'post';
check(apply_filters('comments_open', true, 12), 'Blog comments changed');
$post_type = 'sfwd-lessons';
remove_filter('pre_option_thepond_community_open', '__return_true');
foreach (array(true, false) as $member) {
    foreach (array(true, false) as $facebook_shortcode_enabled) {
        $rendered_shortcodes = array();
        ob_start();
        require get_stylesheet_directory() . '/members-templates/member-dashboard.php';
        $dashboard_html = ob_get_clean();
        check((strpos($dashboard_html, 'id="facebook-secret-phrase"') !== false) === $member, 'Facebook generator membership gate changed');
        check(in_array('[facebook_secret_phrase_shortcode]', $rendered_shortcodes, true) === ($member && $facebook_shortcode_enabled), 'Existing Facebook generator shortcode was not reused safely');
        check(in_array('[ld_profile course_points_user="false" show_quizzes="false"]', $rendered_shortcodes, true), 'LearnDash profile removed by Facebook generator');
        if ($member && !$facebook_shortcode_enabled) {
            check(strpos($dashboard_html, 'role="alert"') !== false, 'Missing generator plugin fails silently');
        }
    }
}
$member = true;
$fields['_mepr_product_price'] = '14.99';
$post = (object) array('ID' => 479);
foreach (array(false, true) as $logged_in) {
    foreach (array(false, true) as $with_sidebar) {
        $fields['sidebar'] = $with_sidebar ? 'membership-info' : false;
        $remaining_posts = 1;
        $rendered_shortcodes = array();
        ob_start();
        require get_stylesheet_directory() . '/members-templates/member_register.php';
        $signup_html = ob_get_clean();
        check(strpos($signup_html, 'pond-auth-card pond-signup-card') !== false, 'Membership signup card missing');
        check(strpos($signup_html, 'large-8 medium-8 columns') === false, 'Legacy checkout columns retained');
        check(strpos($signup_html, 'Existing page content') !== false, 'MemberPress checkout content removed');
        check((strpos($signup_html, 'pond-signup-benefits') !== false) === $with_sidebar, 'Membership sidebar visibility changed');
        if ($with_sidebar) {
            check(strpos($signup_html, 'Existing membership sidebar: membership-info') !== false, 'Configured membership widget removed');
        }
        if (!$logged_in) {
            check(in_array('[mo_firebase_auth_login]', $rendered_shortcodes, true), 'Signup Firebase initialization removed');
            check(strpos($signup_html, 'id="register-form-wrapper" class="pond-signup-form-wrapper" style="display: none;"') !== false, 'Signup email-wrapper identifier or initial state changed');
            foreach (array('Google', 'Facebook') as $provider) {
                check(strpos($signup_html, 'id="mo_firebase_' . $provider . '_provider_login"') !== false, 'Signup provider identifier removed');
            }
            check(strpos($signup_html, 'type="button" class="email-btn pond-auth-provider" id="signup-with-email-btn"') !== false, 'Email signup button changed');
            check(strpos($signup_html, home_url('/login/')) !== false, 'Signup sign-in destination missing');
        } else {
            check(strpos($signup_html, 'id="register-form-wrapper"') === false, 'Anonymous Firebase signup handler claims signed-in checkout');
            check(!$rendered_shortcodes, 'Anonymous signup controls rendered for a signed-in buyer');
        }
    }
}
$logged_in = true;
$fields['sidebar'] = false;
foreach (array('challenges', 'routines') as $library_template) {
    foreach (array(true, false) as $member) {
        ob_start();
        require get_stylesheet_directory() . '/' . $library_template . '.php';
        $library_html = ob_get_clean();
        check(strpos($library_html, 'pond-portal pond-library-page') !== false, 'Library styling scope missing');
        check(strpos($library_html, 'No ' . $library_template . ' are available yet.') !== false, 'Empty library state missing');
        check(strpos($library_html, 'for="filterSearch"') !== false, 'Library search label missing');
        check(strpos($library_html, 'type="search"') !== false, 'Library search control changed');
        check(strpos($library_html, 'type="button" id="clearFilter"') !== false, 'Library clear must be keyboard operable');
        check(strpos($library_html, 'data-filter="all" aria-pressed="true"') !== false, 'Active filter state missing');
        check(strpos($library_html, 'aria-pressed="false">Passing') !== false, 'Category filter state missing');
        check(strpos($library_html, 'id="latestChallengeModal"') === false, 'Empty library must not render a missing challenge preview');
    }
}
$member = true;
foreach (array('Challenges', 'Routines') as $detail_category) {
    foreach (array(true, false) as $member) {
        $remaining_posts = 1;
        $fields['skills'] = array((object) array('ID' => 99));
        $row_positions = array();
        $parts = array();
        ob_start();
        require get_stylesheet_directory() . '/single-content-library.php';
        $detail_html = ob_get_clean();
        check(strpos($detail_html, 'pond-portal pond-library-detail') !== false, 'Detail styling scope missing');
        check(substr_count($detail_html, '<article') === substr_count($detail_html, '</article>'), 'Detail article markup is unbalanced');
        check(strpos($detail_html, 'pond-library-sidebar') !== false && strpos($detail_html, '</aside>') !== false, 'Detail sidebar missing');
        check(strpos($detail_html, $detail_category === 'Challenges' ? '/challenges/' : '/routines/') !== false, 'Detail back destination changed');
        check(strpos($detail_html, '/item/99') !== false, 'Existing related skill link removed');
        check((strpos($detail_html, 'id="challenge-scores"') !== false) === ($detail_category === 'Challenges'), 'Scores must only appear for challenges');
        if ($detail_category === 'Challenges') {
            check(strpos($detail_html, 'Your Scores') !== false, 'Your Scores heading removed');
            check((strpos($detail_html, 'id="challenge-id"') !== false) === $member, 'Membership score controls changed');
            if ($member) {
                foreach (array('challenge-id', 'user-id', 'challenge-score', 'add-score', 'scores', 'success-message', 'error-message') as $control) {
                    check(strpos($detail_html, 'id="' . $control . '"') !== false, 'Score binding missing: ' . $control);
                }
                check(strpos($detail_html, 'type="button" class="add-score-button"') !== false, 'Add score must be a keyboard-accessible button');
            }
        }
        foreach (array('lesson-topic-fields', 'lesson-downloads') as $part) {
            check(in_array('template-parts/courses/' . $part, $parts, true) === $member, 'Protected detail content changed: ' . $part);
        }
        if ($member) {
            check(strpos($detail_html, '440111888') !== false && strpos($detail_html, '/drill.pdf') !== false, 'Existing video or download removed');
            check(strpos($detail_html, 'tool_fav') === false && strpos($detail_html, 'tool_bookmark') === false, 'Challenge/routine save controls should be removed');
            check(strpos($detail_html, 'pond-related-skill-title') !== false && strpos($detail_html, 'pond-related-skill-level') !== false,
                'Related skill title/level missing');
        } else {
            check(strpos($detail_html, 'This content is for members only') !== false, 'Unauthorized detail preview removed');
        }
    }
}
$detail_category = 'Move Makers';
$member = true;
$remaining_posts = 1;
$fields['skills'] = array();
$row_positions = array();
ob_start();
require get_stylesheet_directory() . '/single-content-library.php';
$other_detail_html = ob_get_clean();
check(strpos($other_detail_html, 'tool_fav') !== false && strpos($other_detail_html, 'tool_bookmark') !== false,
    'Save controls on other library content must remain available');
check(strpos($other_detail_html, 'pond-related-skill-list') === false, 'Empty related skills should not render a list');
$member = true;
foreach (array(dirname(__DIR__) . '/portal', get_stylesheet_directory()) as $directory) {
    foreach (new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory, FilesystemIterator::SKIP_DOTS)) as $file) {
        if ($file->getExtension() === 'php') {
            $source = file_get_contents($file->getPathname());
            token_get_all($source, TOKEN_PARSE);
            check(!preg_match('/<\?(?!php|=|xml)/', $source), 'Short PHP tag in ' . $file->getPathname());
        }
        foreach (array('filters.php', 'enqueue.php') as $file) {
            token_get_all(file_get_contents(ABSPATH . 'wp-content/plugins/challenge-score/' . $file), TOKEN_PARSE);
            check(true, 'Challenge Score syntax: ' . $file);
        }
    }
}
echo 'PASS: ' . $checks . " BuddyBoss portal checks and PHP syntax validation\n";
