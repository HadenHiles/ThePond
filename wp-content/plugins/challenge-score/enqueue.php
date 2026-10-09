<?php
function load_challenge_score_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'challenge_score_styles', $plugin_url . 'css/style.css' );
    wp_enqueue_script( 'challenge_score_scripts', $plugin_url . 'js/score-management.js',
        array('jquery', 'mo_firebase_app_script', 'mo_firebase_auth_script', 'mo_firebase_firestore_script'),
        filemtime(__DIR__ . '/js/score-management.js'), true );
}
add_action( 'wp_enqueue_scripts', 'load_challenge_score_scripts' );
?>