<?php
// Override the single content library template
function single_content_library_template($single_template) {
    global $post;

    if ($post->post_type == 'content-library') {
        $single_template = plugin_dir_path( __FILE__ ) . 'single-content-library.php';
    }

    return $single_template;
}
add_filter( 'single_template', 'single_content_library_template' );
?>