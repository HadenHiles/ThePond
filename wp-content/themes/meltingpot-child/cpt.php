<?php
/*
* Creating a function to create our CPT
*/
 
function skills_post_type() {
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Skills', 'Skills'),
        'singular_name'       => _x( 'Skill', 'Skill'),
        'menu_name'           => __( 'Skills'),
        'parent_item_colon'   => __( 'Parent Skill'),
        'all_items'           => __( 'All Skills'),
        'view_item'           => __( 'View Skill'),
        'add_new_item'        => __( 'Add New Skill'),
        'add_new'             => __( 'Add New'),
        'edit_item'           => __( 'Edit Skill'),
        'update_item'         => __( 'Update Skill'),
        'search_items'        => __( 'Search Skill'),
        'not_found'           => __( 'Not Found'),
        'not_found_in_trash'  => __( 'Not found in Trash'),
    );

    // Set other options for Custom Post Type
    $args = array(
        'label'               => __( 'skills'),
        'description'         => __( 'Skills for the skills vault'),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'revisions', 'custom-fields', 'page-attributes'),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array( 'performance-level' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */ 
        'hierarchical'        => true,
        'public'              => true,
        'show_ui'             => true,
        'show_in_menu'        => true,
        'show_in_nav_menus'   => true,
        'show_in_admin_bar'   => true,
        'menu_icon'           => 'dashicons-hammer',
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => true,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
    );

    // Registering your Custom Post Type
    register_post_type( 'skills', $args );
}

    /* Hook into the 'init' action so that the function
    * Containing our post type registration is not 
    * unnecessarily executed. 
    */

    add_action( 'init', 'skills_post_type', 0 );
?>