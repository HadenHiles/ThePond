<?php
defined('ABSPATH') || exit;

add_filter('mepr-pre-run-rule-redirection', 'pond_seo_preview_redirection', 20);
add_filter('template_include', 'pond_seo_preview_template', 99);
add_filter('body_class', 'pond_seo_preview_classes', 999);
add_filter('wpseo_metadesc', 'pond_seo_description', 20);
add_filter('wpseo_opengraph_desc', 'pond_seo_description', 20);
add_filter('wpseo_twitter_description', 'pond_seo_description', 20);
add_filter('wpseo_dynamic_permalinks_enabled', 'pond_seo_dynamic_permalinks', 20);

function pond_seo_post_types() {
    return array('sfwd-courses', 'sfwd-lessons');
}

function pond_seo_is_training() {
    return is_singular(pond_seo_post_types());
}

// Anonymous visitors (including search and link-preview crawlers) get a public summary instead of the
// MemberPress redirect. Signed-in non-members keep the existing membership funnel.
function pond_seo_is_preview() {
    $post = get_queried_object();
    return pond_seo_is_training() && !is_user_logged_in() && $post instanceof WP_Post &&
        $post->post_status === 'publish' && !post_password_required($post) &&
        class_exists('MeprRule') && MeprRule::is_locked($post);
}

function pond_seo_preview_redirection($redirect) {
    return pond_seo_is_preview() ? false : $redirect;
}

function pond_seo_preview_template($template) {
    if (!pond_seo_is_preview()) {
        return $template;
    }
    $preview = get_stylesheet_directory() . '/template-parts/seo-preview.php';
    return file_exists($preview) ? $preview : $template;
}

function pond_seo_preview_classes($classes) {
    if (!pond_seo_is_preview()) {
        return $classes;
    }
    $classes = array_filter($classes, function ($class) {
        return !preg_match('/^(has-sidebar|sfwd-sidebar|sfwd-single-sidebar|bb-sfwd-aside|learndash-)/', $class);
    });
    $classes[] = 'pond-seo-preview-page';
    return array_values($classes);
}

function pond_seo_dynamic_permalinks($enabled) {
    return pond_seo_is_training() ? true : $enabled;
}

function pond_seo_clean_text($text) {
    $text = strip_shortcodes((string) $text);
    $text = preg_replace('#<(script|style|iframe)[^>]*>.*?</\1>#is', ' ', $text);
    $text = html_entity_decode(wp_strip_all_tags($text), ENT_QUOTES, get_bloginfo('charset') ?: 'UTF-8');
    $text = preg_replace('#https?://\S+#i', ' ', $text);
    return trim(preg_replace('/\s+/u', ' ', $text));
}

function pond_seo_truncate($text, $limit = 155) {
    if (function_exists('mb_strlen') ? mb_strlen($text) <= $limit : strlen($text) <= $limit) {
        return $text;
    }
    $cut = function_exists('mb_substr') ? mb_substr($text, 0, $limit - 1) : substr($text, 0, $limit - 1);
    $space = strrpos($cut, ' ');
    if ($space !== false && $space > $limit * 0.6) {
        $cut = substr($cut, 0, $space);
    }
    return rtrim($cut, " \t\n\r\0\x0B,.;:-") . '…';
}

function pond_seo_repeater_values($post_id, $field, $sub_field, $max = 3) {
    $values = array();
    $count = min((int) get_post_meta($post_id, $field, true), $max);
    for ($i = 0; $i < $count; $i++) {
        $value = pond_seo_clean_text(get_post_meta($post_id, "{$field}_{$i}_{$sub_field}", true));
        if ($value !== '') {
            $values[] = $value;
        }
    }
    return $values;
}

function pond_seo_course_id($post_id) {
    return get_post_type($post_id) === 'sfwd-courses' ? (int) $post_id :
        (function_exists('learndash_get_course_id') ? (int) learndash_get_course_id($post_id) : 0);
}

// Full public summary for a lesson or course, built from the fields editors already maintain.
function pond_seo_summary($post_id) {
    $post = get_post($post_id);
    if (!$post || !in_array($post->post_type, pond_seo_post_types(), true)) {
        return '';
    }
    if (has_excerpt($post)) {
        $summary = pond_seo_clean_text($post->post_excerpt);
        if ($summary !== '') {
            return $summary;
        }
    }
    $summary = pond_seo_clean_text($post->post_content);
    if ($summary !== '') {
        return $summary;
    }
    if ($post->post_type === 'sfwd-lessons') {
        $summary = pond_seo_clean_text(get_post_meta($post_id, 'above_media', true) . ' ' . get_post_meta($post_id, 'below_media', true));
        if ($summary !== '') {
            return $summary;
        }
        $tips = pond_seo_repeater_values($post_id, 'tips_for_success', 'content', 2);
        if ($tips) {
            return implode(' ', $tips);
        }
        $course = pond_seo_course_id($post_id);
        return $course ?
            sprintf('Learn %1$s, a lesson in %2$s on The Pond hockey training.', get_the_title($post_id), get_the_title($course)) :
            sprintf('Learn %s with The Pond hockey training.', get_the_title($post_id));
    }
    $goals = pond_seo_repeater_values($post_id, 'goals', 'goal');
    if ($goals) {
        return sprintf('%1$s on The Pond: %2$s', get_the_title($post_id), implode('. ', $goals));
    }
    return sprintf('%s, an online hockey training course on The Pond.', get_the_title($post_id));
}

function pond_seo_description($description) {
    if (!pond_seo_is_training()) {
        return $description;
    }
    $post_id = get_queried_object_id();
    if (trim((string) get_post_meta($post_id, '_yoast_wpseo_metadesc', true)) !== '') {
        return $description;
    }
    $summary = pond_seo_truncate(pond_seo_summary($post_id));
    return $summary !== '' ? $summary : $description;
}

function pond_seo_course_lessons($course_id) {
    if (!$course_id || !function_exists('learndash_course_get_children_of_step')) {
        return array();
    }
    return array_values(array_filter(array_map('absint', (array) learndash_course_get_children_of_step($course_id, $course_id, 'sfwd-lessons')), function ($id) {
        return get_post_status($id) === 'publish';
    }));
}

function pond_seo_join_url($post_id) {
    $path = (string) parse_url(get_permalink($post_id), PHP_URL_PATH);
    $base = class_exists('MeprOptions') ? MeprOptions::fetch()->unauthorized_redirect_url : '';
    if (!$base) {
        return home_url('/#choose-your-subscription');
    }
    return add_query_arg(array('mepr-unauth-page' => $post_id, 'redirect_to' => rawurlencode($path)), $base);
}

function pond_seo_login_url($post_id) {
    return add_query_arg('redirect_to', rawurlencode(get_permalink($post_id)), home_url('/login/'));
}
