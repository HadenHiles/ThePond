<?php
function load_player_card_scripts() {
    $plugin_url = plugin_dir_url( __FILE__ );

    wp_enqueue_style( 'player_card_style', $plugin_url . '/css/style.css', array(), null, false);
}
add_action( 'wp_enqueue_scripts', 'load_player_card_scripts' );
?>