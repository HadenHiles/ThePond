<?php
define('ABSPATH', dirname(__DIR__, 4) . '/');
define('BP_PLATFORM_VERSION', '3.6.0');
define('THEME_HOOK_PREFIX', 'buddyboss_theme_');
define('OBJECT', 'OBJECT');
require ABSPATH . 'wp-includes/plugin.php';
require ABSPATH . 'wp-includes/class-wp-error.php';
require ABSPATH . 'wp-includes/kses.php';
foreach (array('attribute-token', 'text-replacement', 'span', 'decoder', 'tag-processor') as $html_class) {
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
function is_404() { global $is_404_page; return !empty($is_404_page); }
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
function post_password_required() { return $GLOBALS['password_protected'] ?? false; }
function add_query_arg($key, $value, $url = null) {
    if (is_array($key)) {
        $pairs = array();
        foreach ($key as $name => $item) { $pairs[] = $name . '=' . $item; }
        return $value . (strpos($value, '?') === false ? '?' : '&') . implode('&', $pairs);
    }
    return $url . '?' . $key . '=' . urlencode($value);
}
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
function is_post_type_archive($type) { return in_array($GLOBALS['archive_type'] ?? '', (array) $type, true); }
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
function get_terms($taxonomy, $args = array()) {
    $GLOBALS['terms_taxonomy'] = $taxonomy;
    return array((object) array('term_id' => 10, 'slug' => 'passing', 'name' => 'Passing'));
}
function get_permalink($id = 0) { return '/item/' . (is_object($id) ? $id->ID : $id); }
function get_page_template_slug($id) { return $GLOBALS['page_templates'][$id] ?? ''; }
function get_post_permalink($id) { return get_permalink($id); }
function get_the_content() { return '<p>Existing page content</p>'; }
function get_the_excerpt() { return 'Skill practice summary'; }
function wp_allowed_protocols() { return array('http', 'https', 'mailto'); }
function wp_get_attachment_image_url($id, $size) { return ''; }
function get_the_post_thumbnail_url() { return ''; }
function wp_get_post_terms($id, $taxonomy, $args) {
    if (($args['fields'] ?? '') === 'ids') return array(10);
    return array((object) array('name' => $taxonomy === 'library_category' ? $GLOBALS['detail_category'] : 'Skating'));
}
function get_post($id) {
    return (object) array('ID' => $id, 'post_title' => 'Course ' . $id, 'post_type' => $GLOBALS['post_type'],
        'post_status' => 'publish', 'post_excerpt' => $GLOBALS['excerpts'][$id] ?? '', 'post_content' => $GLOBALS['contents'][$id] ?? '');
}
function get_queried_object() {
    $post = new WP_Post();
    foreach (get_object_vars(get_post(get_the_ID())) as $key => $value) { $post->$key = $value; }
    return $post;
}
function _n($single, $plural, $count, $domain = '') { return $count === 1 ? $single : $plural; }
function get_the_post_thumbnail($id, $size, $attr = array()) { return '<img src="/thumb-' . $id . '.jpg" alt="">'; }
function learndash_course_get_children_of_step($course, $step, $type) { return $GLOBALS['course_lessons'] ?? array(); }
function get_post_status($id = 0) { return 'publish'; }
function get_page_by_path($path) { return $path === 'member-dashboard' ? (object) array('ID' => 77) : null; }
function has_excerpt($post) { return ($post->post_excerpt ?? '') !== ''; }
function strip_shortcodes($text) { return preg_replace('/\[[^\]]+\]/', '', $text); }
function get_bloginfo($key = '', $filter = '') { return $key === 'charset' ? 'UTF-8' : 'The Pond'; }
function wp_strip_all_tags($text) { return trim(strip_tags($text)); }
function wp_trim_words($text, $words = 55, $more = '…') {
    $parts = preg_split('/\s+/', trim($text));
    return count($parts) > $words ? implode(' ', array_slice($parts, 0, $words)) . $more : implode(' ', $parts);
}
function wpautop($text) { return '<p>' . $text . '</p>'; }
function sanitize_key($key) { return preg_replace('/[^a-z0-9_\-]/', '', strtolower($key)); }
function sanitize_text_field($text) { return trim(strip_tags($text)); }
function sanitize_textarea_field($text) { return trim(strip_tags($text)); }
function wp_nonce_field($action, $name) { echo '<input type="hidden" name="' . $name . '" value="' . $action . '">'; }
function wp_verify_nonce($nonce, $action) { return $nonce === $action; }
function bp_is_active($component) {
    return isset($GLOBALS['active_components']) ? in_array($component, $GLOBALS['active_components'], true) :
        ($GLOBALS['activity_active'] ?? true);
}
function bp_activity_post_update($args) { $GLOBALS['posted_activity'] = $args; return $GLOBALS['activity_result'] ?? 901; }
function bp_activity_update_meta($id, $key, $value) { $GLOBALS['activity_meta'][$id][$key] = $value; }
function bp_activity_get($args) { $GLOBALS['activity_query'] = $args; return array('activities' => $GLOBALS['lesson_activities'] ?? array()); }
function bp_get_activity_directory_permalink() { return home_url('/news-feed/'); }
function bp_core_fetch_avatar($args) { return '<img src="/avatar-' . $args['item_id'] . '.jpg" alt="">'; }
function bp_core_get_user_displayname($id) { return 'Member ' . $id; }
function bp_activity_get_permalink($id, $activity = null) { return home_url('/news-feed/p/' . $id . '/'); }
function get_post_thumbnail_id($id = 0) { return 0; }
function wp_get_attachment_image_src($id, $size, $icon = false, $attr = '') {
    $GLOBALS['attachment_image_request'] = array($id, $size);
    return array($id === 456 ? 'https://example.test/uploaded.jpg' : '/course.png');
}
function get_current_blog_id() { return 1; }
function get_post_meta($id, $key, $single = false) { return $GLOBALS['post_meta'][$id][$key] ?? ''; }
function get_the_terms($id, $taxonomy) { return array((object) array('term_id' => 10, 'slug' => 'skating', 'name' => 'Skating')); }
function learndash_course_progress($args) {
    $GLOBALS['course_progress_calls'][] = $args;
    return $GLOBALS['course_progress_results'][$args['course_id']];
}
function learndash_get_course_id($id = 0) { return 3194; }
function get_field($name, $id = 0) { global $fields; return isset($fields[$name]) ? $fields[$name] : false; }
$transients = array();
$remote_requests = array();
function get_transient($key) { return $GLOBALS['transients'][$key] ?? false; }
function set_transient($key, $value, $ttl = 0) { $GLOBALS['transients'][$key] = $value; return true; }
function wp_remote_get($url, $args = array()) { $GLOBALS['remote_requests'][] = $url; return array('response' => array('code' => 200), 'body' => '{"thumbnail_url":"https://i.vimeocdn.com/video/thumb_640.jpg"}'); }
function wp_remote_retrieve_response_code($response) { return $response['response']['code']; }
function wp_remote_retrieve_body($response) { return $response['body']; }
defined('MONTH_IN_SECONDS') || define('MONTH_IN_SECONDS', 2592000);
defined('DAY_IN_SECONDS') || define('DAY_IN_SECONDS', 86400);
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
function get_posts($args = array()) { return $GLOBALS['skill_lessons'] ?? array(); }
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
    public $unauthorized_redirect_url = '/wp-content/themes/meltingpot-child/funnel.php';
    public function forgot_password_url() { return home_url('/forgot-password/'); }
}
class MeprRule { public static function is_locked($post) { return $GLOBALS['mepr_locked'] ?? true; } }
class WP_Post {
    public $ID; public $post_author; public $post_title; public $post_type; public $post_status; public $post_excerpt; public $post_content;
}
class WP_User { public $ID; public function __construct($id) { $this->ID = $id; } }
class WP_Query {
    private $posts;
    public function __construct($args) {
        $GLOBALS['last_query_args'] = $args;
        $this->posts = $args['post_type'] === array('sfwd-courses') ? ($GLOBALS['course_feed_ids'] ?? array()) : ($GLOBALS['archive_feed_ids'] ?? array());
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
    if (isset($GLOBALS['message_users'])) {
        foreach ($GLOBALS['message_users'] as $user) {
            $property = array('login' => 'user_login', 'slug' => 'user_nicename', 'email' => 'user_email', 'id' => 'ID')[$field];
            if ($user->$property === $id) {
                return $user;
            }
        }
        return false;
    }
    return (object) array('ID' => is_numeric($id) ? (int) $id : 123, 'user_login' => '<member>');
}
function is_email($value) { return filter_var($value, FILTER_VALIDATE_EMAIL); }
function bp_is_username_compatibility_mode() { return $GLOBALS['username_compatibility'] ?? false; }
function bb_is_rest() { return $GLOBALS['message_rest'] ?? false; }
function wp_parse_id_list($list) { return array_values(array_unique(array_map('intval', (array) $list))); }
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
$page_templates = array(387 => 'members-templates/member-favourite-content.php');
$saved_menu_items = array(
    (object) array('ID' => 1, 'url' => home_url('/saved-content/'), 'object_id' => 0, 'menu_item_parent' => 0),
    (object) array('ID' => 2, 'url' => home_url('/renamed-saved-page/'), 'object_id' => 387, 'menu_item_parent' => 0),
    (object) array('ID' => 3, 'url' => home_url('/account/'), 'object_id' => 8, 'menu_item_parent' => 0),
    (object) array('ID' => 4, 'url' => home_url('/saved-child/'), 'object_id' => 0, 'menu_item_parent' => 1),
    (object) array('ID' => 5, 'url' => home_url('/saved-grandchild/'), 'object_id' => 0, 'menu_item_parent' => 4),
);
check(array_column(pond_theme_hide_saved_content_links($saved_menu_items), 'ID') === array(3), 'Saved-content links or orphaned submenu links remain');
check(pond_theme_hide_saved_content_links(array($saved_menu_items[2])) === array($saved_menu_items[2]), 'Unrelated account navigation changed');
check(is_file(get_stylesheet_directory() . '/members-templates/member-favourite-content.php'), 'Saved-content page removed instead of navigation only');
ob_start();
require get_stylesheet_directory() . '/archive-content-library.php';
$resources_html = ob_get_clean();
check(strpos($resources_html, 'pond-portal pond-library-page') !== false && strpos($resources_html, '<h1>Resources</h1>') !== false, 'Resources archive missing shared library layout');
check(strpos($resources_html, 'type="search"') !== false && strpos($resources_html, 'aria-pressed="true"') !== false, 'Resources search or accessible category filters missing');
check(strpos($resources_html, 'No resources are available yet.') !== false, 'Resources empty state missing');
$archive_type = 'skills';
check(pond_theme_is_legacy_page(), 'Skills archive missing shared portal assets');
ob_start();
require get_stylesheet_directory() . '/archive-skills.php';
$skills_html = ob_get_clean();
check(strpos($skills_html, '<h1>Skills</h1>') !== false && strpos($skills_html, 'pond-portal pond-library-page') !== false, 'Skills archive missing shared library layout');
check(strpos($skills_html, 'Search skills') !== false && strpos($skills_html, 'aria-pressed="true"') !== false, 'Skills search or accessible filters missing');
check(strpos($skills_html, 'No skills are available yet.') !== false, 'Skills empty state missing');
check($terms_taxonomy === 'skill-type' && $last_query_args['post_type'] === 'skills' && $last_query_args['posts_per_page'] === -1, 'Skills filters or full archive query incorrect');
$archive_feed_ids = array(123);
ob_start();
require get_stylesheet_directory() . '/archive-skills.php';
$skills_html = ob_get_clean();
check(strpos($skills_html, 'class="pond-skill-card__link" href="/item/123"') !== false && strpos($skills_html, 'aria-disabled="true"') === false && strpos($skills_html, 'Coming Soon') === false, 'Published skill incorrectly gated by Resources availability');
check(strpos($skills_html, 'data-category="10,"') !== false && strpos($skills_html, 'Skill practice summary') !== false, 'Skill card category or summary missing');
check(strpos($skills_html, '<article class="card shadow challenge filter pond-skill-card-item"') !== false && strpos($skills_html, '<a href="/item/123" class="card') === false, 'Skill cards should not nest action links inside a card link');
check(strpos($skills_html, 'pond-skill-card__facts') !== false && strpos($skills_html, 'Frequency: ') !== false && strpos($skills_html, 'class="puck') !== false, 'Skill card frequency missing');
check(strpos($skills_html, 'pond-skill__rating') !== false && strpos($skills_html, 'Your rating') !== false, 'Skill card rating missing');
check(strpos($skills_html, 'id="pond-skill-archive-sort"') !== false && strpos($skills_html, 'data-frequency="') !== false, 'Skills archive sort missing');
check(strpos($skills_html, 'Breakdown') !== false && strpos($skills_html, 'id="skillsVaultModal"') !== false, 'Member skill cards missing Breakdown action or modal');
check(strpos($resources_html, 'pond-skill-card-item') === false && strpos($resources_html, 'id="pond-skill-archive-sort"') === false && strpos($resources_html, 'skillsVaultModal') === false, 'Resources archive should keep its existing cards');
$fixture_video_code = $fields['video_code'] ?? null;
$fields['video_code'] = '<iframe src="https://player.vimeo.com/video/473605460?h=abc"></iframe>';
$remote_requests = array();
ob_start();
require get_stylesheet_directory() . '/archive-skills.php';
$skills_html = ob_get_clean();
check(strpos($skills_html, 'https://i.vimeocdn.com/video/thumb_640.jpg') !== false && strpos($skills_html, 'placeholder.png') === false, 'Skill card without featured image missing Vimeo thumbnail');
check(count($remote_requests) === 1 && strpos($remote_requests[0], 'vimeo.com%2F473605460') !== false, 'Vimeo thumbnail lookup URL incorrect');
ob_start();
require get_stylesheet_directory() . '/archive-skills.php';
ob_end_clean();
check(count($remote_requests) === 1, 'Vimeo thumbnail lookup not cached');
check(pond_theme_vimeo_thumbnail('<iframe src="https://www.youtube.com/embed/x"></iframe>') === '' && count($remote_requests) === 1, 'Non-Vimeo videos should not trigger thumbnail lookups');
$fields['video_code'] = $fixture_video_code;
unset($archive_feed_ids, $archive_type);
ob_start();
require get_stylesheet_directory() . '/move-makers.php';
$move_makers_html = ob_get_clean();
check(strpos($move_makers_html, 'pond-portal pond-library-page') !== false, 'Move Makers missing shared library grid styling');
check(strpos($move_makers_html, 'type="search"') !== false && strpos($move_makers_html, 'aria-pressed="true"') !== false, 'Move Makers search or accessible filters missing');
check(strpos($move_makers_html, 'No move makers are available yet.') !== false, 'Move Makers empty state missing');
$reset_count = 0;
check(apply_filters('mepr-account-nav-courses-link', '/account/?action=courses') === home_url('/member-dashboard/#courses'), 'Account Courses tab does not link to dashboard courses');
$moderation_where = array(
    'suspend_where' => '( s.user_suspended = 0 OR s.user_suspended IS NULL )',
    'moderation_where' => '( s.hide_sitewide = 0 OR s.hide_sitewide IS NULL ) AND a.user_id NOT IN (SELECT user_id FROM blocked_members) OR a.component = "groups" AND ( s.hide_parent = 0 OR s.hide_parent IS NULL )',
);
$expected_where = $moderation_where;
$expected_where['moderation_where'] = str_replace('a.component = "groups"', "a.component = 'groups'", $expected_where['moderation_where']);
check(apply_filters('bp_suspend_activity_get_where_conditions', $moderation_where) === $expected_where, 'Activity SQL literal incompatible with ANSI_QUOTES or moderation conditions changed');
check(apply_filters('bp_suspend_activity_get_where_conditions', $expected_where) === $expected_where, 'Already compatible moderation SQL changed');
check(apply_filters('bp_suspend_activity_get_where_conditions', array('suspend_where' => 's.user_suspended = 0')) === array('suspend_where' => 's.user_suspended = 0'), 'Suspension-only SQL changed');
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
foreach (array('My Training', 'Account &amp; Billing', 'Change Profile Photo', 'Community Profile', 'Help &amp; Support', 'Sign Out') as $label) {
    check(strpos($account_menu, $label) !== false, 'Account menu label missing: ' . $label);
}
check(strpos($account_menu, 'My Content') === false && strpos($account_menu, '/item/387') === false, 'My Content returned to the account menu');
foreach (array('/member-dashboard/', '/account/', '/account/?action=avatar', '/community-members/member-123/', '/item/392', 'action=logout') as $url) {
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
$message_users = array(
    (object) array('ID' => 196, 'user_login' => 'jeremy@example.test', 'user_nicename' => 'jeremyexample-test', 'user_email' => 'jeremy@example.test'),
    (object) array('ID' => 653, 'user_login' => 'jeremy-two', 'user_nicename' => 'jeremy-two', 'user_email' => 'second@example.test'),
    (object) array('ID' => 800, 'user_login' => 'renamed-login', 'user_nicename' => 'secondexample-test', 'user_email' => 'renamed@example.test'),
);
$recipients = array('jeremy@example.test', ' second@example.test ', 'jeremy-two', 'unknown@example.test', 196, '196', '');
$normalized = array('jeremyexample-test', 'jeremy-two', 'jeremy-two', 'unknown@example.test', 196, '196', '');
check(apply_filters('bp_messages_recipients', $recipients) === $normalized, 'Email and login recipients must normalize before archived-thread checks without changing IDs or unknown recipients');
check(apply_filters('bp_after_messages_new_message_parse_args', array('recipients' => 'jeremy@example.test'))['recipients'] === array('jeremyexample-test'), 'Single string recipients must normalize without string-offset writes');
check(apply_filters('bp_messages_recipients', array('secondexample-test')) === array('secondexample-test'), 'Native profile slugs must take precedence over email fallback');
$message_args = array('recipients' => $recipients, 'sender_id' => 123, 'content' => 'Do not send', 'thread_id' => false);
$resolved_args = apply_filters('bp_after_messages_new_message_parse_args', $message_args);
check($resolved_args['recipients'] === $normalized && $resolved_args['sender_id'] === 123 && $resolved_args['content'] === 'Do not send', 'Message argument filter must only normalize recipients');
$message_rest = true;
check(apply_filters('bp_after_messages_new_message_parse_args', $message_args) === $message_args, 'REST recipient handling must remain with BuddyBoss');
$message_rest = false;
$username_compatibility = true;
check(apply_filters('bp_messages_recipients', array('second@example.test', 'jeremy@example.test')) === array('jeremy-two', 'jeremy@example.test'), 'Compatibility mode must resolve to login instead of slug');
check(apply_filters('bp_messages_recipients', array('jeremyexample-test')) === array('jeremy@example.test'), 'Public suggestion handle must resolve even in username compatibility mode');
$username_compatibility = false;
$suggestions = array(
    (object) array('ID' => 'jeremy@example.test', 'user_id' => 196, 'user_nicename' => 'jeremyexample-test', 'name' => 'Jeremy', 'image' => '/first.jpg'),
    (object) array('ID' => 'jeremy-two', 'user_id' => 653, 'user_nicename' => 'jeremy-two', 'name' => 'Jeremy', 'image' => '/second.jpg'),
    (object) array('ID' => 'third', 'user_id' => 700, 'user_nicename' => 'third', 'name' => '<Jeremy & friends>', 'image' => '/third.jpg'),
);
$suggestions = apply_filters('bp_members_suggestions_results', $suggestions);
check($suggestions[0]->name === 'Jeremy (@jeremyexample-test)' && $suggestions[1]->name === 'Jeremy (@jeremy-two)', 'Duplicate recipient names must show distinct public handles');
check($suggestions[0]->ID === 'jeremyexample-test' && $suggestions[0]->user_id === 196 && $suggestions[0]->image === '/first.jpg', 'Suggestion must submit safe public handle while retaining REST user ID and photo');
check($suggestions[2]->name === '&lt;Jeremy &amp; friends&gt; (@third)', 'Suggestion labels must be safe in native HTML');
$labels = array_column($suggestions, 'name');
check(array_column(apply_filters('bp_members_suggestions_results', $suggestions), 'name') === $labels, 'REST applies the suggestions filter twice; labels must be idempotent');
check(apply_filters('bp_members_suggestions_results', array()) === array(), 'Empty recipient results must remain empty');
foreach (array(false, true) as $username_compatibility) {
    foreach ($suggestions as $suggestion) {
        if ($suggestion->user_id === 700) { continue; }
        preg_match('/(^|[^@\w\-])@([a-zA-Z0-9_\-\.]{1,50})\b/', '@' . $suggestion->ID, $match);
        check($match[0] === '@' . $suggestion->ID, 'Native Nouveau regex must retain the entire selected suggestion handle');
        $resolved = apply_filters('bp_messages_recipients', array(trim($match[0], '@')));
        $user = get_user_by($username_compatibility ? 'login' : 'slug', $resolved[0]);
        check($user && $user->ID === $suggestion->user_id, 'Selected suggestion must resolve through native validation in both username modes');
    }
}
$username_compatibility = false;
$_GET = array('action' => 'messages_search_recipients', 'except' => array('@jeremyexample-test', '@unknown', '@jeremyexample-test'));
$query = array('exclude' => array(123), 'search_terms' => 'Jeremy');
$excluded_query = apply_filters('bp_members_suggestions_query_args', $query);
check($excluded_query['exclude'] === array(123, 196) && $excluded_query['search_terms'] === 'Jeremy', 'Selected public handles must be excluded without replacing native restrictions');
$_GET = array('action' => 'unrelated', 'except' => array('@jeremyexample-test'));
check(apply_filters('bp_members_suggestions_query_args', $query) === $query, 'Unrelated suggestions must remain unchanged');
$_GET = array();
$neutral_avatar = apply_filters('bp_core_default_avatar_user', '/shared-portrait.jpg');
check($neutral_avatar === '/wp-content/plugins/buddyboss-platform/bp-core/images/profile-avatar-buddyboss.png', 'Missing user photos must use a neutral default');
check(apply_filters('bb_default_fetch_avatar_url_user', '/shared-portrait.jpg') === $neutral_avatar, 'Missing Gravatar fallback must also use a neutral default');
check(apply_filters('bp_core_fetch_avatar_url', $neutral_avatar, array('object' => 'user', 'item_id' => 123)) === $neutral_avatar, 'Missing custom photo must retain neutral default');
$avatar_meta[123] = array('avatar_url' => 'https://example.test/social.jpg', 'use_firebase_avatar' => true);
check(apply_filters('bp_core_fetch_avatar_url', $neutral_avatar, array('object' => 'user', 'item_id' => 123)) === 'https://example.test/social.jpg', 'Personal photo must override neutral default');
$avatar_meta = array();
check(is_readable(ABSPATH . ltrim($neutral_avatar, '/')), 'Neutral avatar asset must exist');
$wpua_avatar_default = 456;
// One User Avatar actually supplies only two arguments, despite its docblock.
check(apply_filters('wpua_get_avatar_filter_url', 'https://example.test/uploaded.jpg', 196) === 'https://example.test/uploaded.jpg', 'Vendor two-argument hook must not cause an ArgumentCountError');
check(has_filter('wpua_get_avatar_filter_url', 'thepond_community_wpua_default_avatar') === false, 'Invalid vendor callback must not remain registered');
$avatar_params = array('object' => 'user', 'item_id' => 196, 'width' => 50);
check(apply_filters('bp_core_fetch_avatar_url', 'https://example.test/uploaded.jpg', $avatar_params) === $neutral_avatar, 'BuddyBoss URL hook must replace configured shared portrait after vendor filters');
check($GLOBALS['attachment_image_request'] === array(456, array(50, 50)), 'Shared portrait comparison must use the requested avatar width');
$shared_html = '<img src="https://example.test/uploaded.jpg" srcset="/shared-2x.jpg 2x" class="avatar">';
$neutral_html = apply_filters('bp_core_fetch_avatar', $shared_html, $avatar_params);
check(strpos($neutral_html, 'src="' . $neutral_avatar . '"') !== false && strpos($neutral_html, 'srcset') === false, 'BuddyBoss HTML hook must replace shared portrait and stale srcset');
check(apply_filters('bp_core_fetch_avatar_url', 'https://example.test/personal.jpg', $avatar_params) === 'https://example.test/personal.jpg', 'Existing personal and Gravatar photos must remain unchanged');
check(apply_filters('bp_core_fetch_avatar_url', 'https://example.test/uploaded.jpg', array('object' => 'group', 'item_id' => 196)) === 'https://example.test/uploaded.jpg', 'Group avatar defaults must remain unchanged');
check(apply_filters('bp_core_fetch_avatar', $shared_html, array('object' => 'group', 'item_id' => 196)) === $shared_html, 'Group markup and srcset must remain unchanged');
check(apply_filters('bp_core_fetch_avatar_url', 'https://example.test/uploaded.jpg', $avatar_params + array('force_default' => true)) === 'https://example.test/uploaded.jpg', 'Explicit force-default request must remain unchanged');
$wpua_avatar_default = null;
check(apply_filters('bp_core_fetch_avatar_url', 'https://example.test/personal.jpg', $avatar_params) === 'https://example.test/personal.jpg', 'No configured portrait must retain the existing fallback');
unset($message_users);
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

$member = true; $access = true; $post_type = 'sfwd-lessons'; $post_id = 3250; $logged_in = true;
add_filter('pre_option_thepond_community_open', '__return_true');
check(!has_filter('bbp_get_form_topic_title') && !has_filter('bbp_get_form_topic_content'), 'Forum discussion prefill still active');
check(pond_theme_lesson_discussion_allowed(3250), 'Lesson discussion unavailable to members with course access');
check(!apply_filters('comments_open', true, 3250), 'New lesson conversations remain split between comments and the feed');
$lesson_activities = array((object) array('id' => 77, 'user_id' => 5, 'content' => '<p>Working on reach</p><p class="pond-lesson-link">Lesson: <a href="/item/3250">Title 3250</a></p>'));
ob_start();
do_action('learndash-lesson-after', 3250, 3194, 123);
$discussion_html = ob_get_clean();
check(strpos($discussion_html, 'name="action" value="pond_lesson_discussion"') !== false, 'Lesson feed composer missing');
check(strpos($discussion_html, 'pond_lesson_discussion_3250') !== false, 'Lesson composer not nonce-protected per lesson');
check(strpos($discussion_html, 'Post to news feed') !== false && strpos($discussion_html, '/news-feed/') !== false, 'Feed destination not explained');
check(strpos($discussion_html, 'Working on reach') !== false && strpos($discussion_html, 'Lesson: ') === false, 'Recent lesson posts missing or repeat the lesson link');
check(strpos($discussion_html, '/news-feed/p/77/') !== false, 'Recent lesson post does not link to its feed thread');
check(($activity_query['meta_query'][0] ?? array()) === array('key' => 'pond_lesson_id', 'value' => 3250), 'Recent posts not scoped to the lesson');
$lesson_activities = array();
check(strpos(file_get_contents(get_stylesheet_directory() . '/comments.php'), "get_template_directory() . '/comments.php'") !== false, 'Native existing response template replaced');
foreach (array('member', 'access', 'activity_active', 'logged_in') as $flag) {
    $GLOBALS[$flag] = false;
    check(!pond_theme_lesson_discussion_allowed(3250), 'Lesson discussion ignores access gate: ' . $flag);
    ob_start();
    do_action('learndash-lesson-after', 3250, 3194, 123);
    check(ob_get_clean() === '', 'Lesson composer shown without access: ' . $flag);
    $GLOBALS[$flag] = true;
}
$activity_active = false;
check(apply_filters('comments_open', true, 3250), 'Lesson comments disabled without the activity feed');
$activity_active = true;
$post_type = 'post';
check(!pond_theme_lesson_discussion_allowed(12), 'Non-lesson discussion allowed');
$post_type = 'sfwd-lessons';
$_POST = array('lesson_id' => '3250', 'pond_lesson_nonce' => 'pond_lesson_discussion_3250', 'pond_lesson_message' => "Keeping my feet planted <b>@coach</b>\nAny tips?");
try {
    pond_theme_post_lesson_discussion();
    check(false, 'Lesson post did not redirect');
} catch (PortalRedirect $redirect) {
    check(strpos($redirect->getMessage(), 'pond_discussion=posted') !== false, 'Successful lesson post not confirmed');
}
check(($posted_activity['privacy'] ?? '') === 'public' && ($posted_activity['user_id'] ?? 0) === 123, 'Lesson post not shared to the main feed as the member');
check(strpos($posted_activity['content'], '<b>') === false && strpos($posted_activity['content'], '@coach') !== false, 'Lesson post content not sanitized safely');
check(strpos($posted_activity['content'], 'href="/item/3250"') !== false, 'Lesson post missing its lesson link');
check(($activity_meta[901]['pond_lesson_id'] ?? 0) === 3250, 'Lesson post not tagged with its lesson');
$posted_activity = null;
foreach (array(
    array('pond_lesson_message' => '   '),
    array('pond_lesson_message' => str_repeat('a', 5001)),
) as $override) {
    $_POST = array_merge($_POST, $override);
    try {
        pond_theme_post_lesson_discussion();
    } catch (PortalRedirect $redirect) {
        check(strpos($redirect->getMessage(), 'pond_discussion=error') !== false && $posted_activity === null, 'Invalid lesson post accepted');
    }
}
$_POST['pond_lesson_message'] = 'Valid';
$activity_result = new WP_Error('bp_activity_error', 'Failed');
try {
    pond_theme_post_lesson_discussion();
} catch (PortalRedirect $redirect) {
    check(strpos($redirect->getMessage(), 'pond_discussion=error') !== false, 'Failed activity save reported as success');
}
$activity_result = null;
foreach (array(array('pond_lesson_nonce' => 'stale'), array('lesson_id' => '0')) as $override) {
    $_POST = array_merge(array('lesson_id' => '3250', 'pond_lesson_nonce' => 'pond_lesson_discussion_3250', 'pond_lesson_message' => 'Hi'), $override);
    try {
        pond_theme_post_lesson_discussion();
        check(false, 'Forged lesson post accepted');
    } catch (PortalRedirect $redirect) {
        check(false, 'Forged lesson post redirected instead of rejected');
    } catch (RuntimeException $error) {
        check($error->getCode() === 403, 'Forged lesson post not rejected with 403');
    }
}
$_POST = array('lesson_id' => '3250', 'pond_lesson_nonce' => 'pond_lesson_discussion_3250', 'pond_lesson_message' => 'Hi');
$member = false;
try {
    pond_theme_post_lesson_discussion();
    check(false, 'Non-member lesson post accepted');
} catch (PortalRedirect $redirect) {
    check(false, 'Non-member lesson post redirected');
} catch (RuntimeException $error) {
    check($error->getCode() === 403, 'Non-member lesson post not rejected with 403');
}
$member = true; $_POST = array();

// SEO descriptions, permalinks and the anonymous preview.
$post_meta[3250] = array(
    'below_media' => '<p>Being able to stickhandle effectively with the puck in close and far away from our feet is important for overall puck control. It allows us to retrieve more loose pucks, pull off more moves, and carry the puck through traffic. https://vimeo.com/1</p><iframe src="https://player.vimeo.com/video/1">Video</iframe>',
    'tips_for_success' => '1', 'tips_for_success_0_content' => '<p>Keep your feet planted.</p>',
);
$description = apply_filters('wpseo_metadesc', '');
check(strpos($description, 'Being able to stickhandle effectively') === 0, 'Lesson description not built from lesson intro');
check(mb_strlen($description) <= 155 && substr($description, -3) === '…', 'Lesson description not trimmed for search snippets');
check(strpos($description, '<') === false && strpos($description, 'vimeo') === false && strpos($description, 'Video') === false, 'Lesson description includes markup, embeds or URLs');
check(apply_filters('wpseo_opengraph_desc', '') === $description && apply_filters('wpseo_twitter_description', '') === $description, 'Social descriptions differ from the lesson description');
$excerpts[3250] = 'A hand-written lesson summary.';
check(apply_filters('wpseo_metadesc', '') === 'A hand-written lesson summary.', 'Lesson excerpt does not take priority');
$excerpts = array();
$post_meta[3250]['_yoast_wpseo_metadesc'] = 'Custom Yoast';
check(apply_filters('wpseo_metadesc', 'Custom Yoast') === 'Custom Yoast', 'Custom Yoast description overwritten');
unset($post_meta[3250]['_yoast_wpseo_metadesc'], $post_meta[3250]['below_media']);
check(apply_filters('wpseo_metadesc', '') === 'Keep your feet planted.', 'Lesson tips fallback missing');
$post_meta[3250] = array();
check(apply_filters('wpseo_metadesc', '') === 'Learn Title 3250, a lesson in Title 3194 on The Pond hockey training.', 'Lesson title fallback missing');
$post_type = 'sfwd-courses'; $post_id = 3194;
$post_meta[3194] = array('goals' => '1', 'goals_0_goal' => 'Be able to control the puck all around your body');
check(apply_filters('wpseo_metadesc', '') === 'Title 3194 on The Pond: Be able to control the puck all around your body', 'Course description not built from goals');
check(apply_filters('wpseo_dynamic_permalinks_enabled', false), 'Stale Yoast lesson/course permalinks still used');
$post_type = 'page';
check(apply_filters('wpseo_metadesc', 'Global') === 'Global', 'Non-training SEO description changed');
check(!apply_filters('wpseo_dynamic_permalinks_enabled', false), 'Dynamic permalinks enabled site-wide');
$post_type = 'sfwd-courses';
check(apply_filters('mepr-pre-run-rule-redirection', true, '/', '?'), 'Members lost the MemberPress funnel');
$logged_in = false;
check(!apply_filters('mepr-pre-run-rule-redirection', true, '/', '?'), 'Anonymous course visitors still redirected away from SEO preview');
check(apply_filters('template_include', '/single.php') === get_stylesheet_directory() . '/template-parts/seo-preview.php', 'Anonymous course preview template missing');
$preview_classes = apply_filters('body_class', array('single', 'has-sidebar', 'bb-sfwd-aside', 'learndash-cpt', 'sfwd-single-sidebar-left'));
check(in_array('pond-seo-preview-page', $preview_classes, true) && !in_array('has-sidebar', $preview_classes, true) && !in_array('learndash-cpt', $preview_classes, true), 'Preview keeps LearnDash sidebar layout');
$course_lessons = array(3250, 3251);
ob_start();
require get_stylesheet_directory() . '/template-parts/seo-preview.php';
$preview_html = ob_get_clean();
check(strpos($preview_html, '<h1 class="pond-seo-preview__title">Title 3194</h1>') !== false, 'Preview title missing');
check(strpos($preview_html, 'control the puck all around your body') !== false, 'Preview course goals missing');
check(strpos($preview_html, '2 lessons') !== false && strpos($preview_html, 'href="/item/3251"') !== false, 'Preview lesson list missing');
check((bool) preg_match('#funnel\.php\?mepr-unauth-page=3194&(amp;|\#038;)?redirect_to=%2Fitem%2F3194#', $preview_html), 'Preview join link skips the membership funnel');
check(strpos($preview_html, '/login/?redirect_to=') !== false, 'Preview sign-in link missing destination');
check(strpos($preview_html, 'video_code') === false && strpos($preview_html, 'Being able to') === false, 'Preview leaked member-only lesson material');
$mepr_locked = false;
check(apply_filters('mepr-pre-run-rule-redirection', true, '/', '?') && apply_filters('template_include', '/single.php') === '/single.php', 'Unlocked courses replaced by preview');
$mepr_locked = true; $post_type = 'page';
check(apply_filters('mepr-pre-run-rule-redirection', true, '/', '?'), 'Anonymous non-training pages skip MemberPress');
$logged_in = true; $post_type = 'sfwd-lessons'; $post_id = 3250; $course_lessons = array(); $post_meta = array();
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
$fb_plugin = ABSPATH . 'wp-content/plugins/facebook-group-secret/';
$fb_hooks = file_get_contents($fb_plugin . 'hooks.php');
$fb_js = file_get_contents($fb_plugin . 'assets/facebook-group.js');
$fb_css = file_get_contents($fb_plugin . 'assets/facebook-group.css');
check(strpos($fb_css, '.bb-mp-account-settings > .mepr-facebook-wrapper') !== false &&
    strpos($fb_css, 'flex: 1;') !== false && strpos($fb_css, 'min-width: 0;') !== false,
    'Facebook account content wrapper lacks explicit flex sizing');
token_get_all($fb_hooks, TOKEN_PARSE);
check(!file_exists($fb_plugin . 'learndash/profile.php') && strpos($fb_hooks, 'learndash_template') === false,
    'Facebook plugin must not override the LearnDash profile (AJAX course search duplicated its section)');
$pond_source = file_get_contents(get_stylesheet_directory() . '/inc/pond.php');
check(strpos($pond_source, "add_action('admin_init', 'pond_theme_learndash_ajax_templates')") !== false &&
    strpos($pond_source, "array('ld30_ajax_profile_search', 'ld30_ajax_pager')") !== false &&
    strpos($pond_source, '->ld_30_template_override()') !== false &&
    strpos($pond_source, "add_action('pre_get_posts', 'pond_theme_learndash_ajax_course_status')") !== false &&
    strpos($pond_source, "\$query->set('post_status', array('publish', 'private'))") !== false,
    'LearnDash profile AJAX must reuse the BuddyBoss templates used on page load');
$buddyboss_theme_options = array('404_button_link' => 'https://example.test/');
$is_404_page = true;
$logged_in = false;
pond_theme_member_404_button();
check($buddyboss_theme_options['404_button_link'] === 'https://example.test/', 'Anonymous 404 button should keep the site home link');
$logged_in = true;
pond_theme_member_404_button();
check($buddyboss_theme_options['404_button_link'] === 'https://example.test/member-dashboard/', 'Member 404 button should return to the dashboard');
$buddyboss_theme_options = array('header_logo_loggedin_link' => '');
$logged_in = false;
pond_theme_member_logo_link();
check($buddyboss_theme_options['header_logo_loggedin_link'] === '', 'Signed-out logo should keep the landing page link');
$logged_in = true;
pond_theme_member_logo_link();
check($buddyboss_theme_options['header_logo_loggedin_link'] === 77, 'Signed-in logo should link to the member dashboard');
$is_404_page = false;
$original_mobile_options = array(
    'mobile_component_opt_multi_checkbox' => array('mobile_header_search' => 1, 'mobile_messages' => 0, 'mobile_notifications' => 0),
    'desktop_component_opt_multi_checkbox' => array('desktop_messages' => 0),
);
foreach (array(false, true) as $logged_in) {
    foreach (array(array(), array('messages'), array('notifications'), array('messages', 'notifications')) as $active_components) {
        $buddyboss_theme_options = $original_mobile_options;
        pond_theme_mobile_community_icons();
        foreach (array('messages', 'notifications') as $component) {
            check($buddyboss_theme_options['mobile_component_opt_multi_checkbox']['mobile_' . $component] ===
                ($logged_in && in_array($component, $active_components, true) ? 1 : 0),
                'Mobile community icon gate changed: ' . $component);
        }
        check($buddyboss_theme_options['mobile_component_opt_multi_checkbox']['mobile_header_search'] === 1 &&
            $buddyboss_theme_options['desktop_component_opt_multi_checkbox'] === $original_mobile_options['desktop_component_opt_multi_checkbox'],
            'Mobile icon fix changes unrelated header settings');
    }
}
$admin_request = true;
$buddyboss_theme_options = $original_mobile_options;
pond_theme_mobile_community_icons();
check($buddyboss_theme_options === $original_mobile_options, 'Mobile icon override must not modify admin settings');
$admin_request = false;
$logged_in = true;
unset($active_components);
$dashboard_js = file_get_contents(get_stylesheet_directory() . '/assets/js/pond-dashboard.js');
check(strpos($dashboard_js, "attr('data-ld-expand-id', this.id)") !== false &&
    strpos($dashboard_js, "'data-ld-expands': 'ld-main-course-list'") !== false &&
    strpos($dashboard_js, "closest('#ld-profile .ld-expand-button')") !== false,
    'Dashboard course expand buttons must target their BuddyBoss containers');
check(strpos($fb_hooks, 'return ob_get_clean();') !== false, 'Facebook shortcode must return markup instead of echoing');
check(strpos($fb_hooks, 'echo do_shortcode("[facebook_secret_phrase_shortcode]")') !== false, 'MemberPress Facebook tab no longer prints the shortcode');
check(strpos($fb_hooks, '<script') === false && strpos($fb_hooks, 'user_id: <?=') === false, 'Facebook shortcode JS should be enqueued, not inlined');
foreach (array('id="phrase"', 'id="copyButton"', 'id="generate-new-phrase"', 'role="status"', 'esc_attr($phrase)', 'fb-group-card__steps', 'thepond.howtohockey') as $needle) {
    check(strpos($fb_hooks, $needle) !== false, 'Facebook card missing ' . $needle);
}
check(strpos($fb_js, "$(document).on('click', '[data-fb-group] #generate-new-phrase'") !== false, 'Refresh handler is not delegated');
check(strpos($fb_js, "addClass('is-generating')") !== false && strpos($fb_js, "attr('aria-busy', 'true')") !== false, 'Refresh loading state missing');
check(strpos($fb_js, 'if ($button.prop(\'disabled\'))') !== false, 'Refresh double-submit guard missing');
check(strpos($fb_js, 'user_id') === false, 'Refresh must use the server-side current user');
check(strpos($fb_css, '.is-generating .fb-icon-refresh { animation:') !== false && strpos($fb_css, '@keyframes fb-group-spin') !== false,
    'Refresh spinner animation missing');
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
$rows['skill_quick_tips'] = array(array('title' => 'Keep your head up', 'content' => '<p>Scan the ice.</p>'), array('title' => 'Soft hands', 'content' => ''));
$rows['skill_pros'] = array(array('title' => 'Fast release', 'content' => ''));
$fields['skill_quick_tips'] = $rows['skill_quick_tips'];
$fields['puck_level'] = '4';
$fixture_video_code = $fields['video_code'] ?? null;
$fields['video_code'] = '<iframe src="https://player.vimeo.com/video/473605460"></iframe>';
$fields['when_to_use_it'] = '<p>On the rush</p>';
$skill_lessons = array((object) array('ID' => 77));
foreach (array(true, false) as $member) {
    $remaining_posts = 1;
    $row_positions = array();
    ob_start();
    require get_stylesheet_directory() . '/single-skills.php';
    $skill_html = ob_get_clean();
    check(strpos($skill_html, 'pond-portal pond-library-detail pond-skill-detail') !== false && strpos($skill_html, 'memberDashWelc') === false, 'Skill page missing shared detail layout');
    check(substr_count($skill_html, '<h1') === 1 && strpos($skill_html, '</h3>') === false, 'Skill page heading structure invalid');
    check(substr_count($skill_html, 'class="puck"') === 4 && substr_count($skill_html, 'class="puck faded"') === 1 && strpos($skill_html, 'Frequency: 4 of 5') !== false, 'Skill frequency pucks incorrect');
    check(strpos($skill_html, 'When To Use It') !== false && strpos($skill_html, '/item/77') !== false, 'Skill page lost When To Use It or related lessons');
    check((strpos($skill_html, '473605460') !== false) === $member && (strpos($skill_html, '2 Quick Tips') !== false) === $member && (strpos($skill_html, '>Pros<') !== false) === $member, 'Skill member content gating changed');
    check((strpos($skill_html, 'This content is for members only') !== false) === !$member, 'Skill unauthorized preview changed');
    check(strpos($skill_html, 'class="backBTN" href="/skills/"') !== false && strpos($skill_html, '#skills-vault') === false, 'Skill back destination incorrect');
}
unset($rows['skill_quick_tips'], $rows['skill_pros'], $fields['skill_quick_tips'], $fields['puck_level'], $fields['when_to_use_it'], $skill_lessons);
$fields['video_code'] = $fixture_video_code;
$member = true;
$vault_template = file_get_contents(get_stylesheet_directory() . '/members-templates/member-dashboard.php');
$vault_source = $vault_template
    . file_get_contents(get_stylesheet_directory() . '/inc/pond.php')
    . file_get_contents(get_stylesheet_directory() . '/template-parts/skills/card.php')
    . file_get_contents(get_stylesheet_directory() . '/template-parts/skills/vault-actions.php')
    . file_get_contents(get_stylesheet_directory() . '/template-parts/skills/vault-modal.php');
check(strpos($vault_template, 'challenges pond-skill-cards') !== false && strpos($vault_template, "template-parts/skills/card.php") !== false, 'Skills Vault should reuse the Skills card in the dashboard card row');
check(strpos($vault_template, 'pond_theme_sort_skills_by_frequency(') !== false && strpos($vault_template, ', 0, 3)') !== false, 'Skills Vault should show the three most frequent skills like the other dashboard rows');
check(strpos($vault_template, '<a href="/skills/" class="BTN all-challenges">All Skills</a>') !== false && strpos($vault_template, 'pond-skill-footer') === false, 'Skills Vault should end with the shared All Skills button');
check(strpos($vault_source, '<table') === false && strpos($vault_source, 'id="pond-skill-list"') === false, 'Skills Vault list rows should be replaced by cards');
check(strpos($vault_source, "apply_filters('get_skill_rating'") !== false && strpos($vault_source, 'skill_rating_box') === false, 'Skills Vault rating must not use the table-cell shortcode');
foreach (array('data-frequency=', 'data-rating=', 'pond-skill-card__facts', 'id="skillsVaultModal"', 'data-side=') as $needle) {
    check(strpos($vault_source, $needle) !== false, 'Skills Vault markup missing ' . $needle);
}
check(pond_theme_skill_rating_rgb(0) === 'rgb(255, 0, 0)' && pond_theme_skill_rating_rgb(10) === 'rgb(255, 57, 0)' && pond_theme_skill_rating_rgb(50) === 'rgb(255, 140, 0)' && pond_theme_skill_rating_rgb(90) === 'rgb(128, 181, 0)', 'Skill rating colour should follow the competency slider gradient');
$slider_js = file_get_contents(dirname(dirname(__DIR__)) . '/skill-competency-slider/js/competency-slider.js');
check(strpos($slider_js, 'rgb: competencyColor(ui.value)') !== false && strpos($slider_js, '!event.originalEvent') !== false, 'Competency slider must not save a transparent colour when loading a rating');
$vault_js = file_get_contents(get_stylesheet_directory() . '/assets/js/pond-dashboard.js');
check(strpos($vault_js, 'DataTable(') === false && strpos($vault_js, '#pond-skill-list') === false && strpos($vault_js, '#skillsVaultModal') !== false, 'Skills Vault script should only drive the Breakdown/Examples modal');
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
