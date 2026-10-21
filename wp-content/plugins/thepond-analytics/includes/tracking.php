<?php
defined('ABSPATH') || exit;

const THEPOND_ANALYTICS_CONTENT_TYPES = array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'sfwd-quiz', 'content-library', 'skills', 'skill-examples');

add_action('init', 'thepond_analytics_set_visitor_cookie', 20);
add_action('wp', 'thepond_analytics_replace_ga_plugin');
add_action('wp_head', 'thepond_analytics_print_head', 1);
add_action('thepond_landing_head', 'thepond_analytics_print_landing_head');

function thepond_analytics_tracking_enabled() {
    $settings = thepond_analytics_settings();
    return !empty($settings['enabled']) && !empty($settings['measurement_id']);
}

function thepond_analytics_content_types() {
    return apply_filters('thepond_analytics_content_types', THEPOND_ANALYTICS_CONTENT_TYPES);
}

/**
 * Logged-out pages are served from W3TC's page cache, so the visitor type has to
 * live in a cookie the tracker reads in the browser. It survives logout, which
 * is how a member returning to the landing page to sign in is recognized.
 */
function thepond_analytics_set_visitor_cookie() {
    if (!is_user_logged_in() || wp_doing_ajax() || wp_doing_cron() || (defined('REST_REQUEST') && REST_REQUEST) || headers_sent()) {
        return;
    }
    $type = thepond_analytics_visitor_type(get_current_user_id());
    if (isset($_COOKIE['pond_vt']) && $_COOKIE['pond_vt'] === $type) {
        return;
    }
    setcookie('pond_vt', $type, array(
        'expires' => time() + 400 * DAY_IN_SECONDS,
        'path' => COOKIEPATH ? COOKIEPATH : '/',
        'domain' => COOKIE_DOMAIN ? COOKIE_DOMAIN : '',
        'secure' => is_ssl(),
        'httponly' => false,
        'samesite' => 'Lax',
    ));
    $_COOKIE['pond_vt'] = $type;
}

function thepond_analytics_replace_ga_plugin() {
    if (!thepond_analytics_tracking_enabled() || !thepond_analytics_setting('replace_ga_plugin')) {
        return;
    }
    remove_action('wp_head', 'ga_google_analytics_tracking_code');
    remove_action('wp_footer', 'ga_google_analytics_tracking_code');
}

function thepond_analytics_page_context() {
    $context = array('pageType' => 'other');

    if (is_front_page() || is_page_template('home.php')) {
        $context['pageType'] = 'landing';
    } elseif (class_exists('MeprProduct') && is_singular(MeprProduct::$cpt)) {
        $product = new MeprProduct(get_queried_object_id());
        $context['pageType'] = 'register';
        $context['plan'] = array(
            'id' => get_post_field('post_name', $product->ID),
            'name' => get_the_title($product->ID),
            'price' => (float) $product->price,
        );
    } elseif (is_page('login')) {
        $context['pageType'] = 'login';
    } elseif (is_page('member-dashboard')) {
        $context['pageType'] = 'member_dashboard';
        $signup = thepond_analytics_thank_you_signup();
        if ($signup) {
            $context['signup'] = $signup;
        }
    } elseif (is_page(array('support', 'member-support'))) {
        $context['pageType'] = 'support';
    } elseif (is_singular(thepond_analytics_content_types())) {
        $post = get_queried_object();
        $context['pageType'] = 'content';
        $context['content'] = array(
            'type' => $post->post_type,
            'title' => get_the_title($post),
        );
        if (function_exists('learndash_get_course_id') && strpos($post->post_type, 'sfwd-') === 0) {
            $course_id = (int) learndash_get_course_id($post->ID);
            if ($course_id && $course_id !== (int) $post->ID) {
                $context['content']['course'] = get_the_title($course_id);
            }
        }
    } elseif (is_post_type_archive(thepond_analytics_content_types())) {
        $context['pageType'] = 'content_archive';
    }

    return apply_filters('thepond_analytics_page_context', $context);
}

/**
 * MemberPress sends new members to the dashboard with ?transaction_id=. Only
 * a fresh transaction owned by the current user counts as a signup view.
 */
function thepond_analytics_thank_you_signup() {
    if (empty($_GET['transaction_id']) || !is_user_logged_in() || !class_exists('MeprTransaction')) {
        return null;
    }
    $txn = new MeprTransaction((int) $_GET['transaction_id']);
    if (empty($txn->id) || (int) $txn->user_id !== get_current_user_id()) {
        return null;
    }
    $created = strtotime((string) $txn->created_at . ' UTC');
    if (!$created || $created < time() - DAY_IN_SECONDS) {
        return null;
    }
    return array(
        'transactionId' => 'mepr-' . (int) $txn->id,
        'plan' => get_post_field('post_name', (int) $txn->product_id),
    );
}

function thepond_analytics_tracker_config($page_type = null) {
    $settings = thepond_analytics_settings();
    $context = thepond_analytics_page_context();
    if ($page_type) {
        $context['pageType'] = $page_type;
    }

    $config = array_merge($context, array(
        'measurementId' => $settings['measurement_id'],
        'currency' => class_exists('MeprOptions') ? MeprOptions::fetch()->currency_code : 'USD',
        'loggedIn' => is_user_logged_in(),
    ));

    if (!empty($settings['clarity_id']) && in_array($context['pageType'], (array) $settings['clarity_pages'], true)) {
        $config['clarityId'] = $settings['clarity_id'];
    }
    // Only uncached (logged-in) responses carry a user id.
    if (is_user_logged_in()) {
        $user_id = get_current_user_id();
        $config['userId'] = (string) $user_id;
        $config['visitorType'] = thepond_analytics_visitor_type($user_id);
    }
    if (isset($_GET['pond_debug']) && current_user_can(thepond_analytics_capability())) {
        $config['debug'] = true;
        $config['visitorType'] = 'prospect';
    }

    return apply_filters('thepond_analytics_tracker_config', $config);
}

function thepond_analytics_tracker_url() {
    $file = THEPOND_ANALYTICS_DIR . 'assets/tracker.js';
    return plugins_url('assets/tracker.js', THEPOND_ANALYTICS_FILE) . '?ver=' . (file_exists($file) ? filemtime($file) : THEPOND_ANALYTICS_VERSION);
}

function thepond_analytics_print_head($page_type = null) {
    if (!thepond_analytics_tracking_enabled() || is_admin() || is_feed() || is_robots()) {
        return;
    }
    $config = thepond_analytics_tracker_config(is_string($page_type) ? $page_type : null);
    echo "<script>window.pondAnalytics=" . wp_json_encode($config, JSON_UNESCAPED_SLASHES) . ";</script>\n";
    echo '<script src="' . esc_url(thepond_analytics_tracker_url()) . '" defer></script>' . "\n";
}

/**
 * The landing page is a standalone template without wp_head(), so it gets the
 * cookie banner and tracker directly. The banner must print first so it can
 * hold back analytics until the visitor accepts.
 */
function thepond_analytics_print_landing_head() {
    if (function_exists('Cookie_Notice')) {
        $notice = Cookie_Notice();
        if (isset($notice->frontend) && method_exists($notice->frontend, 'add_cookie_compliance')) {
            $notice->frontend->add_dns_prefetch();
            $notice->frontend->add_cookie_compliance();
        }
    }
    thepond_analytics_print_head('landing');
}
