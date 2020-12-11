<?php
add_action('wp_enqueue_scripts', 'enqueue_jqueryui');
function enqueue_jqueryui() {
    $plugin_url = plugin_dir_url( __FILE__ );
    if (strpos($_SERVER['REQUEST_URI'], '/skills') !== false) {
        wp_enqueue_script('jquery-ui', 'https://code.jquery.com/ui/1.11.4/jquery-ui.min.js', array(), null, true);
        wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css');
        wp_enqueue_script('competency-slider', $plugin_url . 'js/competency-slider.js', array('jquery'), null, true);
        wp_enqueue_style('competency-slider', $plugin_url . 'css/style.css');
    }
}
?>