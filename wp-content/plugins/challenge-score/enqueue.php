<?php
function load_challenge_score_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_script( 'jquery', get_template_directory_uri() . '/js/vendor/jquery.min.js', array(), null, true);
    wp_enqueue_style( 'challenge_score_styles', $plugin_url . 'css/style.css' );
    wp_enqueue_script( 'challenge_score_scripts', $plugin_url . 'js/score-management.js' );
}
add_action( 'wp_enqueue_scripts', 'load_challenge_score_scripts' );
?>