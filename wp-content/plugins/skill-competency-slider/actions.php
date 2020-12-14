<?php
function get_skill_competency_rating($user_id, $skill_id) {
    try {
        $raw = (!empty($user_id) && !empty($skill_id));
        $skillId = !$raw ? intval($_POST['skill_id']) : $skill_id;
        $userId = !$raw ? intval($_POST['user_id']) : $user_id;

        if ($userId != get_current_user_id()) {
            throw new Exception('You don\'t have permission to view this rating');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "skill_competency_ratings";
        $query =    "SELECT id, skill_id, `percentage`, `rgb`, `date` FROM $table_name
                        WHERE skill_id = %d
                        AND `user_id` = %d
                        LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $skillId, $userId));

        if ($raw) {
            return $results[0];
        }
        send_response($results[0]);
    } catch (Exception $e) {
        if ($raw) {
            return array(null, "error" => $e);
        }
        send_response(null, $e);
    }
}

// Add ajax endpoint for retrieving their rating
add_action('wp_ajax_get_skill_competency_rating', 'get_skill_competency_rating');

add_filter('get_skill_competency_rating', 'get_skill_competency_rating', 10, 2 );

function update_skill_competency_rating() {
    try {
        $skill_id = intval($_POST['skill_id']);
        $user_id = intval($_POST['user_id']);
        $percentage = floatval($_POST['percentage']);
        $rgb = $_POST['rgb'];

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to add this score');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "skill_competency_ratings";

        global $wpdb;
        $table_name = $wpdb->prefix . "skill_competency_ratings";
        $query =    "SELECT id, skill_id, `percentage`, `rgb`, `date` FROM $table_name
                        WHERE skill_id = %d
                        AND `user_id` = %d
                        LIMIT 1";

        $results = $wpdb->get_results($wpdb->prepare($query, $skill_id, $user_id));

        $currentSkillRating = $results[0];

        if ($currentSkillRating != null) {
            if ($wpdb->update(
                $table_name,
                array(
                    'percentage' => $percentage,
                    'rgb' => $rgb
                ),
                array(
                    'id' => $currentSkillRating->id
                )
            )) {
                send_response(array('success' => true));
            } else {
                throw new Exception('Failed to add skill competency rating');
            }
        } else {
            if ($wpdb->insert(
                $table_name,
                array(
                    'id' => null,
                    'skill_id' => $skill_id,
                    'user_id' => $user_id,
                    'percentage' => $percentage,
                    'rgb' => $rgb
                ),
                array(
                    '%d',
                    '%d',
                    '%d',
                    '%f',
                    '%s'
                )
            )) {
                send_response(array('success' => true));
            } else {
                throw new Exception('Failed to add skill competency rating');
            }
        }
    } catch (Exception $e) {
        send_response(null, $e);
    }
}

// Add ajax endpoint for inserting or updating a skill competency rating
add_action('wp_ajax_update_skill_competency_rating', 'update_skill_competency_rating');

/**
 * Send a formatted json response to the client
 */
if (!function_exists('send_response')) {
    function send_response($data, Exception $e = null) {
        if (empty($e)) {
            wp_send_json(
                array(
                    'data' => $data
                ),
                200
            );
        } else {
            wp_send_json(
                array(
                    'error' => array(
                        'code' => $e->getCode(),
                        'message' => $e->getMessage()
                    )
                ),
                $e->getCode()
            );
        }
    }
}
