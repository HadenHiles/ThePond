<?php
defined('ABSPATH') || exit;

add_action('template_redirect', 'thepond_analytics_log_view', 99);
add_action('thepond_analytics_daily', 'thepond_analytics_prune_views');

/**
 * One row per member, post and day. Server-side so it works without cookie
 * consent and isn't skewed by ad blockers; only aggregate counts are reported.
 */
function thepond_analytics_log_view() {
    if (!is_user_logged_in() || is_admin() || wp_doing_ajax() || is_preview() || !is_singular()) {
        return;
    }
    if (isset($_SERVER['REQUEST_METHOD']) && $_SERVER['REQUEST_METHOD'] !== 'GET') {
        return;
    }
    $purpose = $_SERVER['HTTP_SEC_PURPOSE'] ?? ($_SERVER['HTTP_PURPOSE'] ?? '');
    if (stripos($purpose, 'prefetch') !== false) {
        return;
    }
    $post = get_queried_object();
    if (!$post instanceof WP_Post) {
        return;
    }
    $tracked = in_array($post->post_type, thepond_analytics_content_types(), true)
        || ($post->post_type === 'page' && in_array($post->post_name, thepond_analytics_member_pages(), true));
    if (!$tracked) {
        return;
    }
    $user_id = get_current_user_id();
    if (thepond_analytics_is_staff($user_id)) {
        return;
    }
    thepond_analytics_insert_view($user_id, (int) $post->ID, $post->post_type);
}

function thepond_analytics_member_pages() {
    return apply_filters('thepond_analytics_member_pages', array('member-dashboard', 'challenges', 'routines', 'move-makers', 'skills-vault', 'member-support'));
}

function thepond_analytics_insert_view($user_id, $post_id, $post_type) {
    global $wpdb;
    $table = $wpdb->prefix . 'pond_content_views';
    $wpdb->query($wpdb->prepare(
        "INSERT INTO $table (user_id, post_id, post_type, view_date, views, last_viewed) VALUES (%d, %d, %s, %s, 1, %s)
         ON DUPLICATE KEY UPDATE views = views + 1, last_viewed = VALUES(last_viewed)",
        $user_id,
        $post_id,
        substr($post_type, 0, 40),
        current_time('Y-m-d'),
        current_time('mysql')
    ));
}

function thepond_analytics_prune_views() {
    global $wpdb;
    $cutoff = wp_date('Y-m-d', time() - 400 * DAY_IN_SECONDS);
    $wpdb->query($wpdb->prepare("DELETE FROM {$wpdb->prefix}pond_content_views WHERE view_date < %s", $cutoff));
}
