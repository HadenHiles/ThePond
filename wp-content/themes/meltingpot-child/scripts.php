<?php
wp_enqueue_scripts( 'wp-utils' );
wp_enqueue_script( 'main', get_template_directory_uri() . '/js/main.js', array('jquery'), null, true);
?>
