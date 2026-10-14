<?php
defined('ABSPATH') || exit;

add_action('after_setup_theme', 'pond_theme_setup', 30);
add_action('wp_enqueue_scripts', 'pond_theme_assets', 10000);
add_action('template_redirect', 'pond_theme_dependencies');
add_filter('wp_nav_menu_items', 'pond_theme_mobile_account', 20, 2);
add_filter('nav_menu_link_attributes', 'pond_theme_menu_links', 20, 3);
add_filter('wp_nav_menu_objects', 'pond_theme_dashboard_menu_items', 20);
add_filter('wp_nav_menu_objects', 'pond_theme_hide_saved_content_links', 30);
add_filter('register_url', 'pond_theme_registration_url', 1000);
add_filter('body_class', 'pond_theme_auth_classes');
add_filter('wp_nav_menu_items', 'pond_theme_account_menu', 30, 2);
add_filter('comments_open', 'pond_theme_lesson_comments_open', 20, 2);
add_action('learndash-lesson-after', 'pond_theme_lesson_discussion_panel');
add_action('admin_post_pond_lesson_discussion', 'pond_theme_post_lesson_discussion');
add_action('admin_init', 'pond_theme_learndash_ajax_templates');
add_action('template_redirect', 'pond_theme_member_404_button', 1);
add_action('template_redirect', 'pond_theme_member_logo_link', 1);
add_action('template_redirect', 'pond_theme_public_layout', 1);
add_filter('pre_wp_nav_menu', 'pond_theme_hide_public_header_menu', 10, 2);
add_action('template_redirect', 'pond_theme_mobile_community_icons', 1);
add_filter('nav_menu_item_title', 'pond_theme_menu_item_icons', 20);

// Menu labels saved in WordPress use Font Awesome Pro duotone icons, which the site doesn't load.
function pond_theme_menu_item_icons($title) {
    if (strpos($title, 'fa-sign-out') === false) {
        return $title;
    }
    return preg_replace('/<i\s+class="fad\s+fa-sign-out(?:-alt)?"[^>]*>\s*<\/i>/', '<i class="bb-icon-l bb-icon-sign-out" aria-hidden="true"></i>', $title);
}

function pond_theme_mobile_community_icons() {
    if (!is_user_logged_in() || is_admin() || !function_exists('bp_is_active')) {
        return;
    }
    global $buddyboss_theme_options;
    if (!isset($buddyboss_theme_options)) {
        $buddyboss_theme_options = (array) get_option('buddyboss_theme_options', array());
    }
    foreach (array('messages', 'notifications') as $component) {
        if (bp_is_active($component)) {
            $buddyboss_theme_options['mobile_component_opt_multi_checkbox']['mobile_' . $component] = 1;
        }
    }
}

function pond_theme_member_404_button() {
    if (!is_404() || !is_user_logged_in()) {
        return;
    }
    global $buddyboss_theme_options;
    if (!isset($buddyboss_theme_options)) {
        $buddyboss_theme_options = (array) get_option('buddyboss_theme_options', array());
    }
    $buddyboss_theme_options['404_button_link'] = home_url('/member-dashboard/');
}

// BuddyBoss sends signed-in logo clicks to this page ID (header, mobile header and panel).
function pond_theme_member_logo_link() {
    if (!is_user_logged_in()) {
        return;
    }
    $dashboard = get_page_by_path('member-dashboard');
    if (!$dashboard) {
        return;
    }
    global $buddyboss_theme_options;
    if (!isset($buddyboss_theme_options)) {
        $buddyboss_theme_options = (array) get_option('buddyboss_theme_options', array());
    }
    $buddyboss_theme_options['header_logo_loggedin_link'] = $dashboard->ID;
}

// Signed-out pages (e.g. /support/) skip the member sidebar; header Sign in/Sign up replace its Join Now/Member Login links.
function pond_theme_public_layout() {
    if (is_user_logged_in()) {
        return;
    }
    global $buddyboss_theme_options;
    if (!isset($buddyboss_theme_options)) {
        $buddyboss_theme_options = (array) get_option('buddyboss_theme_options', array());
    }
    $buddyboss_theme_options['buddypanel'] = 0;
}

// Only the desktop header copy is removed; the mobile panel still needs these links.
function pond_theme_hide_public_header_menu($output, $args) {
    if (!is_user_logged_in() && isset($args->theme_location, $args->menu_id) &&
        $args->theme_location === 'header-menu-logout' && $args->menu_id === 'primary-menu') {
        return '';
    }
    return $output;
}

// BuddyBoss only swaps in its LearnDash templates on template_redirect, which never runs in
// admin-ajax, so profile search and pagination would otherwise re-render with different markup.
function pond_theme_learndash_ajax_templates() {
    $actions = array('ld30_ajax_profile_search', 'ld30_ajax_pager');
    if (!wp_doing_ajax() || !isset($_REQUEST['action']) || !in_array($_REQUEST['action'], $actions, true)) {
        return;
    }
    if (function_exists('buddyboss_theme') && method_exists(buddyboss_theme(), 'learndash_helper')) {
        $helper = buddyboss_theme()->learndash_helper();
        if ($helper && method_exists($helper, 'ld_30_template_override')) {
            $helper->ld_30_template_override();
        }
    }
    add_action('pre_get_posts', 'pond_theme_learndash_ajax_course_status');
}

// admin-ajax is an admin context, so course queries there also return drafts to editors.
function pond_theme_learndash_ajax_course_status($query) {
    if ($query->get('post_type') === 'sfwd-courses' && !$query->get('post_status')) {
        // WP_Query still limits private posts to users allowed to read them, matching page load.
        $query->set('post_status', array('publish', 'private'));
    }
}

function pond_theme_auth_classes($classes) {
    if (is_page_template(array('firebase-login.php', 'members-templates/member_register.php'))) {
        $classes[] = 'pond-auth-page';
    }
    return $classes;
}

function pond_theme_login_destination($requested = '') {
    $dashboard = home_url('/member-dashboard/');
    $destination = $requested === '' ? $dashboard : wp_validate_redirect($requested, $dashboard);
    $path = rtrim((string) parse_url($destination, PHP_URL_PATH), '/');
    $login_path = rtrim((string) parse_url(home_url('/login/'), PHP_URL_PATH), '/');
    return $path === $login_path ? $dashboard : $destination;
}

function pond_theme_lesson_discussion_allowed($lesson_id) {
    return get_post_type($lesson_id) === 'sfwd-lessons' &&
        is_user_logged_in() &&
        current_user_can('memberpress_authorized') &&
        function_exists('bp_is_active') && bp_is_active('activity') &&
        function_exists('bp_activity_post_update') &&
        function_exists('sfwd_lms_has_access') &&
        sfwd_lms_has_access(learndash_get_course_id($lesson_id), get_current_user_id()) &&
        !(function_exists('thepond_community_access_error') && thepond_community_access_error());
}

function pond_theme_lesson_comments_open($open, $post_id) {
    // New lesson conversations go to the main activity feed; legacy responses still render.
    return get_post_type($post_id) === 'sfwd-lessons' && function_exists('bp_is_active') && bp_is_active('activity') ? false : $open;
}

function pond_theme_lesson_activities($lesson_id, $limit = 5) {
    if (!function_exists('bp_activity_get')) {
        return array();
    }
    $result = bp_activity_get(array(
        'per_page' => $limit,
        'display_comments' => false,
        'filter' => array('object' => 'activity', 'action' => 'activity_update'),
        'meta_query' => array(array('key' => 'pond_lesson_id', 'value' => (int) $lesson_id)),
    ));
    return isset($result['activities']) ? $result['activities'] : array();
}

function pond_theme_lesson_discussion_panel($lesson_id) {
    if (post_password_required() || !pond_theme_lesson_discussion_allowed($lesson_id)) {
        return;
    }
    $status = isset($_GET['pond_discussion']) && is_string($_GET['pond_discussion']) ? sanitize_key($_GET['pond_discussion']) : '';
    $feed_url = function_exists('bp_get_activity_directory_permalink') ? bp_get_activity_directory_permalink() : home_url('/news-feed/');
    $activities = pond_theme_lesson_activities($lesson_id);
    ?>
    <section id="pond-lesson-discussion" class="pond-lesson-discussion" aria-labelledby="pond-lesson-discussion-heading">
        <h2 id="pond-lesson-discussion-heading"><?php esc_html_e('Discuss this lesson', 'buddyboss-theme-child'); ?></h2>
        <p><?php esc_html_e('Ask a question or share your progress. Your post goes to the community news feed with a link back to this lesson.', 'buddyboss-theme-child'); ?></p>
        <?php if ($status === 'posted') : ?>
            <p class="pond-lesson-discussion__notice" role="status"><?php esc_html_e('Posted to the news feed.', 'buddyboss-theme-child'); ?> <a href="<?php echo esc_url($feed_url); ?>"><?php esc_html_e('View feed', 'buddyboss-theme-child'); ?></a></p>
        <?php elseif ($status === 'error') : ?>
            <p class="pond-lesson-discussion__notice is-error" role="alert"><?php esc_html_e('Your post could not be shared. Please write a message and try again.', 'buddyboss-theme-child'); ?></p>
        <?php endif; ?>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="pond_lesson_discussion">
            <input type="hidden" name="lesson_id" value="<?php echo absint($lesson_id); ?>">
            <?php wp_nonce_field('pond_lesson_discussion_' . absint($lesson_id), 'pond_lesson_nonce'); ?>
            <label class="screen-reader-text" for="pond-lesson-message"><?php esc_html_e('Your message', 'buddyboss-theme-child'); ?></label>
            <textarea id="pond-lesson-message" name="pond_lesson_message" rows="3" maxlength="5000" required placeholder="<?php esc_attr_e('What are you working on?', 'buddyboss-theme-child'); ?>"></textarea>
            <button type="submit" class="button"><?php esc_html_e('Post to news feed', 'buddyboss-theme-child'); ?></button>
        </form>
        <?php if ($activities) : ?>
            <h3><?php esc_html_e('Recent posts about this lesson', 'buddyboss-theme-child'); ?></h3>
            <ul class="pond-lesson-discussion__posts">
                <?php foreach ($activities as $activity) : ?>
                    <li>
                        <?php echo bp_core_fetch_avatar(array('item_id' => $activity->user_id, 'type' => 'thumb', 'width' => 36, 'height' => 36)); ?>
                        <div>
                            <strong><?php echo esc_html(bp_core_get_user_displayname($activity->user_id)); ?></strong>
                            <p><?php echo esc_html(wp_trim_words(wp_strip_all_tags(preg_replace('#<p[^>]*>\s*' . preg_quote(__('Lesson:', 'buddyboss-theme-child'), '#') . '.*?</p>#s', '', $activity->content)), 30, '…')); ?></p>
                            <a href="<?php echo esc_url(bp_activity_get_permalink($activity->id, $activity)); ?>"><?php esc_html_e('View and reply', 'buddyboss-theme-child'); ?></a>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
        <p class="pond-lesson-discussion__footer"><a href="<?php echo esc_url($feed_url); ?>"><?php esc_html_e('Open the community news feed', 'buddyboss-theme-child'); ?></a></p>
    </section>
    <?php
}

function pond_theme_post_lesson_discussion() {
    $lesson_id = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
    if (!$lesson_id || !isset($_POST['pond_lesson_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['pond_lesson_nonce'])), 'pond_lesson_discussion_' . $lesson_id)) {
        wp_die(esc_html__('This discussion form has expired. Please reload the lesson and try again.', 'buddyboss-theme-child'), '', array('response' => 403));
    }
    if (!pond_theme_lesson_discussion_allowed($lesson_id)) {
        wp_die(esc_html__('You do not have access to discuss this lesson.', 'buddyboss-theme-child'), '', array('response' => 403));
    }
    $back = get_permalink($lesson_id);
    $message = isset($_POST['pond_lesson_message']) && is_string($_POST['pond_lesson_message']) ?
        trim(sanitize_textarea_field(wp_unslash($_POST['pond_lesson_message']))) : '';
    $length = function_exists('mb_strlen') ? mb_strlen($message) : strlen($message);
    if ($message === '' || $length > 5000) {
        wp_safe_redirect(add_query_arg('pond_discussion', 'error', $back) . '#pond-lesson-discussion');
        exit;
    }
    $content = wpautop(esc_html($message)) . '<p class="pond-lesson-link">' . esc_html__('Lesson:', 'buddyboss-theme-child') .
        ' <a href="' . esc_url($back) . '">' . esc_html(get_the_title($lesson_id)) . '</a></p>';
    $activity_id = bp_activity_post_update(array(
        'content' => $content,
        'title_required' => false,
        'user_id' => get_current_user_id(),
        'privacy' => 'public',
        'error_type' => 'wp_error',
    ));
    if (!$activity_id || is_wp_error($activity_id)) {
        wp_safe_redirect(add_query_arg('pond_discussion', 'error', $back) . '#pond-lesson-discussion');
        exit;
    }
    bp_activity_update_meta($activity_id, 'pond_lesson_id', $lesson_id);
    wp_safe_redirect(add_query_arg('pond_discussion', 'posted', $back) . '#pond-lesson-discussion');
    exit;
}

function pond_theme_setup() {
    register_nav_menus(array(
        'pond-member-footer' => 'Pond Footer - Logged in',
        'pond-public-footer' => 'Pond Footer - Logged out',
    ));
    add_action(THEME_HOOK_PREFIX . 'footer', 'pond_theme_footer', 20);
}

function pond_theme_dependencies() {
    if (!function_exists('thepond_portal_course_tabs') || !class_exists('MeprUser') || !function_exists('get_field')) {
        wp_die('The Pond requires the updated The Pond Community plugin, MemberPress and Advanced Custom Fields. Activate these before using the BuddyBoss child theme.', 'Portal dependencies unavailable', array('response' => 503));
    }
}

// Mirrors the competency slider gradient; stored rgb values can be transparent.
function pond_theme_skill_rating_rgb($percent) {
    $stops = array(array(0, array(255, 0, 0)), array(20, array(255, 113, 0)), array(40, array(255, 113, 0)), array(60, array(255, 166, 0)), array(80, array(255, 166, 0)), array(100, array(0, 195, 0)));
    $percent = max(0, min(100, (float) $percent));
    for ($i = 1; $i < count($stops); $i++) {
        if ($percent <= $stops[$i][0]) {
            break;
        }
    }
    $i = min($i, count($stops) - 1);
    list($from_at, $from) = $stops[$i - 1];
    list($to_at, $to) = $stops[$i];
    $ratio = ($percent - $from_at) / ($to_at - $from_at);
    $rgb = array();
    for ($c = 0; $c < 3; $c++) {
        $rgb[] = (int) round($from[$c] + ($to[$c] - $from[$c]) * $ratio);
    }
    return 'rgb(' . implode(', ', $rgb) . ')';
}

function pond_theme_skill_card($skill_id) {
    $types = get_the_terms($skill_id, 'skill-type');
    $types = is_array($types) ? $types : array();
    $levels = get_the_terms($skill_id, 'performance-level');
    $levels = is_array($levels) ? $levels : array();
    $rating = is_user_logged_in() ? apply_filters('get_skill_rating', $skill_id) : null;
    $rating = is_object($rating) && !empty($rating->data) ? $rating->data : null;
    $percent = $rating && !empty($rating->percentage) ? (float) $rating->percentage : 0;
    $pluck = function ($terms, $key) {
        return array_map(function ($term) use ($key) { return $term->$key; }, $terms);
    };
    $field = function ($name) use ($skill_id) {
        $value = get_field($name, $skill_id);
        return empty($value) ? array() : $value;
    };
    return array(
        'name' => get_the_title($skill_id),
        'url' => get_permalink($skill_id),
        'puckLevel' => max(0, min(5, intval(get_post_meta($skill_id, 'puck_level', true)))),
        'pros' => $field('skill_pros'),
        'cons' => $field('skill_cons'),
        'skillExamples' => $field('skill_examples'),
        'whenToUseIt' => get_field('when_to_use_it', $skill_id),
        'videoCode' => get_field('video_code', $skill_id),
        'skillTypes' => $types,
        'skillTypeSlugs' => $pluck($types, 'slug'),
        'skillTypeString' => implode(', ', $pluck($types, 'name')),
        'performanceLevelString' => implode(', ', $pluck($levels, 'name')),
        'ratingPercent' => $percent,
        'ratingRgb' => $percent > 0 ? pond_theme_skill_rating_rgb($percent) : '',
        'ratingLabel' => $percent >= 99 ? 'Game Changer' : ($percent > 0 ? sprintf('Rated %d%%', round($percent)) : 'Not rated yet'),
    );
}

function pond_theme_skill_rating_badge($card) {
    $percent = $card['ratingPercent'];
    $style = $percent > 0 && $percent < 99 ? ' style="background-color: ' . esc_attr($card['ratingRgb']) . ';"' : '';
    $logo = $percent >= 99 ? '<img src="/wp-content/plugins/skill-competency-slider/images/hth_logo.png" alt="">' : '';
    return '<span class="pond-skill__rating' . ($percent > 0 ? '' : ' is-empty') . '" title="Your rating: ' . esc_attr($card['ratingLabel']) . '"' . $style . '>'
        . $logo . '<span class="screen-reader-text">Your rating: ' . esc_html($card['ratingLabel']) . '</span></span>';
}

function pond_theme_skill_pucks($level) {
    $html = '<span class="screen-reader-text">' . esc_html(sprintf('Frequency: %d of 5', $level)) . '</span>';
    for ($x = 0; $x < 5; $x++) {
        $html .= '<svg class="puck' . ($x < $level ? '' : ' faded') . '" xmlns="http://www.w3.org/2000/svg" aria-hidden="true" focusable="false" viewBox="0 0 512 512"><path d="M0 160c0-53 114.6-96 256-96s256 43 256 96s-114.6 96-256 96S0 213 0 160zm0 82.2V352c0 53 114.6 96 256 96s256-43 256-96V242.2c-113.4 82.3-398.5 82.4-512 0z" /></svg>';
    }
    return $html;
}

function pond_theme_sort_skills_by_frequency($skills) {
    usort($skills, function ($a, $b) {
        $diff = intval(get_post_meta($b->ID, 'puck_level', true)) - intval(get_post_meta($a->ID, 'puck_level', true));
        return $diff ?: strcasecmp(get_the_title($a->ID), get_the_title($b->ID));
    });
    return $skills;
}

function pond_theme_vimeo_thumbnail($video_html) {
    if (!is_string($video_html) || !preg_match('~(?:player\.)?vimeo\.com/(?:video/)?(\d+)~', $video_html, $match)) {
        return '';
    }
    $key = 'pond_vimeo_thumb_' . $match[1];
    $cached = get_transient($key);
    if ($cached !== false) {
        return $cached === 'none' ? '' : $cached;
    }
    // Limit uncached lookups per page view; remaining thumbnails fill in on later visits.
    static $lookups = 0;
    if (++$lookups > 8) {
        return '';
    }
    $thumbnail = '';
    $response = wp_remote_get('https://vimeo.com/api/oembed.json?width=640&url=' . rawurlencode('https://vimeo.com/' . $match[1]), array('timeout' => 3));
    if (!is_wp_error($response) && wp_remote_retrieve_response_code($response) === 200) {
        $data = json_decode(wp_remote_retrieve_body($response), true);
        if (!empty($data['thumbnail_url'])) {
            $thumbnail = esc_url_raw($data['thumbnail_url']);
        }
    }
    // Cache misses for a shorter time so a temporary Vimeo outage does not hide thumbnails for long.
    set_transient($key, $thumbnail ?: 'none', $thumbnail ? MONTH_IN_SECONDS : DAY_IN_SECONDS);
    return $thumbnail;
}

function pond_theme_is_legacy_page() {
    return is_page_template(array(
        'members-templates/member-dashboard.php', 'members-templates/member_register.php',
        'members-templates/member-favourite-content.php', 'members-templates/member_support.php',
        'firebase-login.php', 'challenges.php', 'routines.php', 'move-makers.php',
    )) || is_singular(array('content-library', 'skills')) || is_post_type_archive(array('content-library', 'skills'));
}

function pond_theme_assets() {
    $uri = get_stylesheet_directory_uri();
    $old = content_url('/themes/melting_pot');
    $version = '1.2.0';
    wp_enqueue_script('pond-appearance', $uri . '/assets/js/pond-appearance.js', array(),
        filemtime(get_stylesheet_directory() . '/assets/js/pond-appearance.js'), false);
    wp_localize_script('pond-appearance', 'pondAppearance', array(
        'lightColor' => bp_get_option('bb_rl_color_light', '#14345a'),
        'darkColor' => bp_get_option('bb_rl_color_dark', '#36cce4'),
        'darkLabel' => __('Switch to dark mode', 'buddyboss-theme-child'),
        'lightLabel' => __('Switch to light mode', 'buddyboss-theme-child'),
        'saveError' => __('Your appearance preference could not be saved. It may reset when you leave this page.', 'buddyboss-theme-child'),
    ));
    wp_enqueue_script('wp-util');
    wp_enqueue_style('pond-menu-icons', plugins_url('elementor/assets/lib/font-awesome/css/all.min.css'), array(), null);
    wp_enqueue_style('pond-menu-icons-legacy', plugins_url('elementor/assets/lib/font-awesome/css/v4-shims.min.css'), array('pond-menu-icons'), null);

    // Reuse the installed SDK handles rather than initializing a second Firebase app.
    $sdk = plugins_url('miniorange-firebase-authentication-enterprise/admin/js/');
    foreach (array('app', 'auth', 'firestore') as $component) {
        $handle = 'mo_firebase_' . $component . '_script';
        if (!wp_script_is($handle, 'registered')) {
            wp_register_script($handle, $sdk . 'firebase-' . $component . '.js',
                $component === 'app' ? array() : array('mo_firebase_app_script'), null, true);
        }
        wp_enqueue_script($handle);
    }
    wp_enqueue_script('pond-firebase', $uri . '/assets/js/firebase.js',
        array('jquery', 'buddyboss-child-js', 'mo_firebase_app_script', 'mo_firebase_auth_script', 'mo_firebase_firestore_script'),
        $version, true);
    wp_localize_script('buddyboss-child-js', 'pondPortal', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'trackerNonce' => wp_create_nonce('pond-lesson-tracker'),
    ));

    if (!pond_theme_is_legacy_page()) {
        return;
    }
    wp_enqueue_style('pond-grid', $uri . '/assets/css/pond-grid.css', array(), $version);
    wp_enqueue_style('pond-legacy', $uri . '/assets/css/pond-legacy.css', array('pond-grid'), $version);
    wp_enqueue_style('pond-datatables', $old . '/bootstrap-4.5.0/DataTables/datatables.min.css', array(), $version);
    wp_enqueue_style('pond-bootstrap', $old . '/bootstrap-4.5.0/dist/css/bootstrap.min.css', array(), $version);
    wp_enqueue_script('pond-datatables', $old . '/bootstrap-4.5.0/DataTables/datatables.min.js', array('jquery'), $version, true);
    wp_enqueue_script('pond-popper', $old . '/bootstrap-4.5.0/dist/js/popper.min.js', array('jquery'), $version, true);
    wp_enqueue_script('pond-bootstrap', $old . '/bootstrap-4.5.0/dist/js/bootstrap.min.js', array('jquery', 'pond-popper'), $version, true);
    wp_enqueue_script('pond-dashboard', $uri . '/assets/js/pond-dashboard.js',
        array('jquery', 'pond-datatables', 'pond-bootstrap'),
        filemtime(get_stylesheet_directory() . '/assets/js/pond-dashboard.js'), true);
}

function pond_theme_mobile_account($items, $args) {
    if ($args->theme_location !== 'mobile-menu-logged-in' || !is_user_logged_in()) {
        return $items;
    }
    $locations = get_nav_menu_locations();
    if (empty($locations['header-my-account'])) {
        return $items;
    }
    return $items . wp_nav_menu(array(
        'menu' => $locations['header-my-account'], 'theme_location' => 'header-my-account',
        'container' => false, 'items_wrap' => '%3$s',
        'echo' => false, 'fallback_cb' => false,
    ));
}

function pond_theme_account_menu($items, $args) {
    if ($args->theme_location !== 'header-my-account' || !is_user_logged_in()) {
        return $items;
    }
    $links = array(
        array(home_url('/member-dashboard/'), __('My Training', 'buddyboss-theme-child'), 'bb-icon-graduation-cap'),
        array(home_url('/account/'), __('Account & Billing', 'buddyboss-theme-child'), 'bb-icon-user'),
        array(home_url('/account/?action=avatar'), __('Change Profile Photo', 'buddyboss-theme-child'), 'bb-icon-camera'),
    );
    if (function_exists('bp_core_get_user_domain')) {
        $links[] = array(bp_core_get_user_domain(get_current_user_id()), __('Community Profile', 'buddyboss-theme-child'), 'bb-icon-id-card');
    }
    $links[] = array(get_permalink(392), __('Help & Support', 'buddyboss-theme-child'), 'bb-icon-question');
    $links[] = array(wp_logout_url(home_url('/')), __('Sign Out', 'buddyboss-theme-child'), 'bb-icon-sign-out');
    $items = '';
    foreach ($links as $link) {
        $items .= '<li class="menu-item pond-account-menu-item"><a href="' . esc_url($link[0]) . '"><i class="bb-icon-l ' .
            esc_attr($link[2]) . '" aria-hidden="true"></i><span>' . esc_html($link[1]) . '</span></a></li>';
    }
    return $items;
}

function pond_theme_menu_links($atts, $item, $args) {
    if (in_array('logout', $item->classes, true)) {
        $atts['href'] = wp_logout_url(home_url('/'));
    }
    if (isset($atts['href']) && $atts['href'] === '#search') {
        $atts['aria-label'] = 'Search';
    }
    return $atts;
}

function pond_theme_dashboard_menu_items($items) {
    if (!is_page_template('members-templates/member-dashboard.php')) {
        return $items;
    }
    // WordPress marks dashboard section links as current pages too.
    foreach ($items as $item) {
        if (strpos($item->url, '#') !== false) {
            $item->current = false;
            $item->classes = array_values(array_diff($item->classes, array('current-menu-item', 'current_page_item')));
        }
    }
    return $items;
}

function pond_theme_hide_saved_content_links($items) {
    $hidden_ids = array();
    foreach ($items as $item) {
        $path = rtrim((string) parse_url($item->url, PHP_URL_PATH), '/');
        if ($path === rtrim((string) parse_url(home_url('/saved-content/'), PHP_URL_PATH), '/') ||
            (isset($item->object_id) && get_page_template_slug($item->object_id) === 'members-templates/member-favourite-content.php')) {
            $hidden_ids[] = (int) $item->ID;
        }
    }
    do {
        $previous_count = count($hidden_ids);
        foreach ($items as $item) {
            if (isset($item->menu_item_parent) && in_array((int) $item->menu_item_parent, $hidden_ids, true) &&
                !in_array((int) $item->ID, $hidden_ids, true)) {
                $hidden_ids[] = (int) $item->ID;
            }
        }
    } while (count($hidden_ids) !== $previous_count);
    return array_values(array_filter($items, function($item) use ($hidden_ids) {
        return !in_array((int) $item->ID, $hidden_ids, true);
    }));
}

function pond_theme_registration_url($url) {
    return home_url('/#pricingSect');
}

function pond_theme_footer() {
    ?>
    <footer class="pond-footer">
        <nav aria-label="Footer">
            <?php wp_nav_menu(array(
                'theme_location' => is_user_logged_in() ? 'pond-member-footer' : 'pond-public-footer',
                'container' => false, 'fallback_cb' => false,
            )); ?>
            <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a>
        </nav>
    </footer>
    <div id="pond-search" hidden>
        <form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
            <label for="pond-search-input">Search The Pond</label>
            <input id="pond-search-input" type="search" name="s">
            <button type="submit">Search</button>
            <button type="button" class="pond-search-close">Close</button>
        </form>
    </div>
    <?php
}
