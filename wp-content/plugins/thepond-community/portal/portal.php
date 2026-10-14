<?php
defined('ABSPATH') || exit;

foreach (array('identity', 'cpt', 'taxonomies', 'acf', 'library', 'shortcodes', 'history', 'HthMeprUser.class') as $module) {
    require_once __DIR__ . '/' . $module . '.php';
}

if (!defined('DEFAULT_IMG')) {
    define('DEFAULT_IMG', content_url('/themes/meltingpot-child/images/placeholder.png'));
}

add_filter('learndash_content_tabs', 'thepond_portal_course_tabs', 20, 4);
add_action('wp_ajax_track_lesson_ajax', 'thepond_portal_track_lesson');

function thepond_portal_is_free_course_sample($lesson_id) {
    if (get_post_type($lesson_id) !== 'sfwd-lessons' ||
        !function_exists('learndash_is_sample') ||
        !learndash_is_sample($lesson_id) ||
        !function_exists('learndash_get_course_id') ||
        !function_exists('learndash_get_setting') ||
        !function_exists('get_post_field')) {
        return false;
    }

    $course_id = learndash_get_course_id($lesson_id);
    return $course_id &&
        get_post_field('post_name', $course_id) === 'free-lessons' &&
        learndash_get_setting($course_id, 'course_price_type') === 'free';
}

function thepond_portal_course_tabs($tabs, $context, $course_id, $user_id) {
    if (!in_array($context, array('course', 'lesson', 'topic'), true) ||
        !is_singular(array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic')) ||
        !function_exists('get_field')) {
        return $tabs;
    }

    $post_id = get_the_ID();
    $has_member_access = current_user_can('memberpress_authorized') &&
        function_exists('sfwd_lms_has_access') &&
        sfwd_lms_has_access($course_id, $user_id);
    $is_public_sample = !$has_member_access &&
        $context === 'lesson' &&
        thepond_portal_is_free_course_sample($post_id);
    if (!$has_member_access && !$is_public_sample) {
        return $tabs;
    }

    foreach ($tabs as &$tab) {
        if ($tab['id'] !== 'content' || strpos($tab['content'], 'data-pond-material') !== false) {
            continue;
        }
        ob_start();
        get_template_part('template-parts/courses/pond-material');
        $material = ob_get_clean();
        $resources = '';
        if (!$is_public_sample) {
            ob_start();
            get_template_part('template-parts/courses/lesson-downloads');
            if ($course_id && $course_id !== $post_id) {
                get_template_part('template-parts/courses/course-downloads');
            }
            get_template_part('template-parts/courses/coursehistory');
            $resources = ob_get_clean();
        }
        $tab['content'] = '<div class="pond-course-material" data-pond-material="' . absint($post_id) . '">' .
            $material . '</div>' . $tab['content'] . '<div class="pond-course-material">' . $resources . '</div>';
    }
    unset($tab);
    return $tabs;
}

function check_lesson_track($lesson, $user, $type = '1', $status = '1') {
    global $wpdb;
    return (bool) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}lessontracker WHERE user_id=%d AND lesson_id=%d AND lesson_status=%d",
        $user, $lesson, $status
    ));
}

function thepond_portal_track_lesson() {
    check_ajax_referer('pond-lesson-tracker', 'nonce');
    $lesson = isset($_POST['lesson_id']) ? absint($_POST['lesson_id']) : 0;
    $status = isset($_POST['track_type']) ? absint($_POST['track_type']) : 0;
    if (!$lesson || !in_array($status, array(1, 2, 5), true) ||
        !in_array(get_post_type($lesson), array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic', 'content-library', 'skills'), true) ||
        !current_user_can('memberpress_authorized')) {
        wp_send_json_error(array('message' => 'This item cannot be saved.'), 403);
    }
    $user = get_current_user_id();
    if (in_array(get_post_type($lesson), array('sfwd-courses', 'sfwd-lessons', 'sfwd-topic'), true) &&
        !sfwd_lms_has_access(learndash_get_course_id($lesson), $user)) {
        wp_send_json_error(array('message' => 'You do not have access to this course.'), 403);
    }
    global $wpdb;
    $exists = check_lesson_track($lesson, $user, 1, $status);
    $values = array('user_id' => $user, 'lesson_id' => $lesson, 'lesson_type' => 1, 'lesson_status' => $status);
    $result = $exists ? $wpdb->delete($wpdb->prefix . 'lessontracker', $values, array('%d', '%d', '%d', '%d')) :
        $wpdb->insert($wpdb->prefix . 'lessontracker', $values, array('%d', '%d', '%d', '%d'));
    if ($result === false) {
        error_log('The Pond: lessontracker update failed: ' . $wpdb->last_error);
        wp_send_json_error(array('message' => 'Your saved content could not be updated. Please try again.'), 500);
    }
    wp_send_json_success(array('saved' => !$exists));
}
