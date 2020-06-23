<?php
function add_challenge_score() {
    $challenge_id = $_POST['challenge_id'];
    $user_id = $_POST['user_id'];
    $score = $_POST['score'];

    global $wpdb;
    $table_name = $wpdb->prefix . "challenge_scores";
    $wpdb->insert(
        $table_name, 
        array(
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
    );
}

// Add ajax endpoint for adding a challenge score
add_action( 'wp_ajax_add_challenge_score', 'add_challenge_score' );

?>