<?php
function get_challenge_scores() {
    try {
        $challenge_id = $_POST['challenge_id'];
        $user_id = $_POST['user_id'];

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to view these scores');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "challenge_scores";
        $query =    "SELECT id, challenge_id, score, `date` FROM $table_name
                        WHERE challenge_id = %d
                        AND `user_id` = %d";

        $results = $wpdb->get_results( $wpdb->prepare($query, $challenge_id, $user_id) );
        
        send_response($results);
    } catch (Exception $e) {
        send_response(null, $e);
    }
}

// Add ajax endpoint for retrieving challenge scores
add_action( 'wp_ajax_get_challenge_scores', 'get_challenge_scores' );

function add_challenge_score() {
    try {
        $challenge_id = $_POST['challenge_id'];
        $user_id = $_POST['user_id'];
        $score = $_POST['score'];

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to add this score');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "challenge_scores";
        if ($wpdb->insert($table_name, array(
                'id' => null,
                'challenge_id' => $challenge_id,
                'user_id' => $user_id,
                'score' => $score
            ),
            array(
                '%d',
                '%d',
                '%d',
                '%d'
            )
        )) {
            send_response(array('success' => true));
        } else {
            throw new Exception('Failed to add score');
        }
    } catch (Exception $e) {
        send_response(null, $e);
    }
}

// Add ajax endpoint for adding a challenge score
add_action( 'wp_ajax_add_challenge_score', 'add_challenge_score' );

function delete_challenge_score() {
    try {
        $user_id = $_POST['user_id'];
        $scoreId = $_POST['score_id'];

        if ($user_id != get_current_user_id()) {
            throw new Exception('You don\'t have permission to delete this score');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . "challenge_scores";
        if ($wpdb->delete($table_name, array(
                'id' => $scoreId,
                'user_id' => $user_id
            ),
            array(
                '%d',
                '%d'
            )
        )) {
            send_response(array('success' => true));
        } else {
            throw new Exception('Failed to delete score');
        }
    } catch (Exception $e) {
        send_response(null, $e);
    }
}

// Add ajax endpoint for deleting a challenge score
add_action( 'wp_ajax_delete_challenge_score', 'delete_challenge_score' );

/**
 * Send a formatted json response to the client
 */
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

?>