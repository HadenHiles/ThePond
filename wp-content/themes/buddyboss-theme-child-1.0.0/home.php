<?php
/* Template Name: The Pond - Static Landing Page */
$landing = get_theme_root() . '/melting_pot/home.php';
if (!is_readable($landing)) {
    wp_die('The Pond landing page is missing. Keep the existing Melting Pot theme installed.', 'Landing page unavailable', array('response' => 503));
}
require $landing;
