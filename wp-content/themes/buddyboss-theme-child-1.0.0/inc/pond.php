<?php
defined('ABSPATH') || exit;

add_action('after_setup_theme', 'pond_theme_setup', 30);
add_action('wp_enqueue_scripts', 'pond_theme_assets', 10000);
add_action('template_redirect', 'pond_theme_dependencies');
add_filter('wp_nav_menu_items', 'pond_theme_mobile_account', 20, 2);
add_filter('nav_menu_link_attributes', 'pond_theme_menu_links', 20, 3);
add_filter('wp_nav_menu_objects', 'pond_theme_dashboard_menu_items', 20);
add_filter('register_url', 'pond_theme_registration_url', 1000);
add_filter('body_class', 'pond_theme_auth_classes');
add_filter('wp_nav_menu_items', 'pond_theme_account_menu', 30, 2);
add_filter('comments_open', 'pond_theme_lesson_comments_open', 20, 2);
add_filter('bbp_get_form_topic_title', 'pond_theme_lesson_topic_title');
add_filter('bbp_get_form_topic_content', 'pond_theme_lesson_topic_content');
add_action('template_redirect', 'pond_theme_lesson_topic_context', 20);
add_action('learndash-lesson-after', 'pond_theme_lesson_discussion_panel');

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

function pond_theme_training_forum() {
    if (!function_exists('bbp_get_forum_permalink')) {
        return null;
    }
    $forum = get_page_by_path('training-discussions', OBJECT, 'forum');
    return $forum && in_array($forum->post_status, array('publish', 'private'), true) ? $forum : null;
}

function pond_theme_lesson_discussion_url($lesson_id) {
    if (get_post_type($lesson_id) !== 'sfwd-lessons' ||
        !current_user_can('memberpress_authorized') ||
        !function_exists('sfwd_lms_has_access') ||
        !sfwd_lms_has_access(learndash_get_course_id($lesson_id), get_current_user_id()) ||
        thepond_community_access_error()) {
        return '';
    }
    $forum = pond_theme_training_forum();
    if (!$forum || !bbp_is_forum_open($forum->ID) || !bbp_user_can_view_forum(array('forum_id' => $forum->ID))) {
        return '';
    }
    return add_query_arg('pond_lesson', $lesson_id, bbp_get_forum_permalink($forum->ID)) . '#new-post';
}

function pond_theme_lesson_comments_open($open, $post_id) {
    return get_post_type($post_id) === 'sfwd-lessons' && pond_theme_training_forum() ? false : $open;
}

function pond_theme_lesson_discussion_panel($lesson_id) {
    if (post_password_required()) {
        return;
    }
    $discussion_url = pond_theme_lesson_discussion_url($lesson_id);
    if (!$discussion_url) {
        return;
    }
    ?>
    <section class="pond-lesson-discussion" aria-labelledby="pond-lesson-discussion-heading">
        <h2 id="pond-lesson-discussion-heading"><?php esc_html_e('Discuss this lesson', 'buddyboss-theme-child'); ?></h2>
        <p><?php esc_html_e('Ask a question or share your progress in our shared Training Discussions forum. The lesson title and link will be included in your new discussion.', 'buddyboss-theme-child'); ?></p>
        <a class="button" href="<?php echo esc_url($discussion_url); ?>"><?php esc_html_e('Start a lesson discussion', 'buddyboss-theme-child'); ?></a>
        <p><?php esc_html_e('Existing lesson responses remain below. New conversations take place in the forum.', 'buddyboss-theme-child'); ?></p>
    </section>
    <?php
}

function pond_theme_lesson_topic_context() {
    if (!isset($_GET['pond_lesson']) || !function_exists('bbp_is_single_forum') || !bbp_is_single_forum()) {
        return;
    }
    $forum = pond_theme_training_forum();
    if (!$forum || bbp_get_forum_id() !== (int) $forum->ID) {
        return;
    }
    if (!is_string($_GET['pond_lesson']) || !ctype_digit($_GET['pond_lesson']) || !absint($_GET['pond_lesson'])) {
        wp_die(esc_html__('Invalid lesson discussion link.', 'buddyboss-theme-child'), '', array('response' => 400));
    }
    if (!pond_theme_lesson_discussion_url(absint($_GET['pond_lesson']))) {
        wp_die(esc_html__('You do not have access to this lesson discussion.', 'buddyboss-theme-child'), '', array('response' => 403));
    }
}

function pond_theme_discussion_lesson_id() {
    if (empty($_GET['pond_lesson']) || !is_string($_GET['pond_lesson']) ||
        !ctype_digit($_GET['pond_lesson']) || !function_exists('bbp_is_single_forum') ||
        !bbp_is_single_forum() || bbp_is_post_request() || bbp_is_topic_edit()) {
        return 0;
    }
    $forum = pond_theme_training_forum();
    $lesson_id = absint($_GET['pond_lesson']);
    return $forum && bbp_get_forum_id() === (int) $forum->ID && pond_theme_lesson_discussion_url($lesson_id) ? $lesson_id : 0;
}

function pond_theme_lesson_topic_title($title) {
    $lesson_id = pond_theme_discussion_lesson_id();
    return $title === '' && $lesson_id ? esc_html(get_the_title($lesson_id)) : $title;
}

function pond_theme_lesson_topic_content($content) {
    $lesson_id = pond_theme_discussion_lesson_id();
    return $content === '' && $lesson_id
        ? '<p>' . esc_html__('Lesson:', 'buddyboss-theme-child') . ' <a href="' . esc_url(get_permalink($lesson_id)) . '">' .
            esc_html(get_the_title($lesson_id)) . '</a></p><p></p>'
        : $content;
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

function pond_theme_is_legacy_page() {
    return is_page_template(array(
        'members-templates/member-dashboard.php', 'members-templates/member_register.php',
        'members-templates/member-favourite-content.php', 'members-templates/member_support.php',
        'firebase-login.php', 'challenges.php', 'routines.php', 'move-makers.php',
    )) || is_singular(array('content-library', 'skills')) || is_post_type_archive('content-library');
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
    $links[] = array(get_permalink(387), __('My Content', 'buddyboss-theme-child'), 'bb-icon-bookmark');
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
