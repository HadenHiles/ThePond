<?php
function add_challenge_score() {
    try {
        $challenge_id = $_POST['challenge_id'];
        $user_id = $_POST['user_id'];
        $score = $_POST['score'];

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

function get_challenge_scores() {
    try {
        $challenge_id = $_POST['challenge_id'];
        $user_id = $_POST['user_id'];

        global $wpdb;
        $table_name = $wpdb->prefix . "challenge_scores";
        $query =    "SELECT id, challenge_id, score FROM '$table_name'
                        WHERE challenge_id = %d
                        AND user_id = %d";

        $results = $wpdb->get_results( $wpdb->prepare($query, $challenge_id, $user_id) );
        
        send_response($results);
    } catch (Exception $e) {
        send_response(null, $e);
    }
}

// Add ajax endpoint for adding a challenge score
add_action( 'wp_ajax_get_challenge_scores', 'get_challenge_scores' );

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