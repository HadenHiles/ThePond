<?php
add_action( 'init', 'register_cpt_content_library' );

function register_cpt_content_library() {

	$labels = array(
		'name' => __( 'Content Library', 'content-library' ),
		'singular_name' => __( 'Content Library', 'content-library' ),
		'add_new' => __( 'Add New', 'content-library' ),
		'add_new_item' => __( 'Add New Content Library', 'content-library' ),
		'edit_item' => __( 'Edit Content Library', 'content-library' ),
		'new_item' => __( 'New Content Library', 'content-library' ),
		'view_item' => __( 'View Content Library', 'content-library' ),
		'search_items' => __( 'Search Content Library', 'content-library' ),
		'not_found' => __( 'No content library found', 'content-library' ),
		'not_found_in_trash' => __( 'No content library found in Trash', 'content-library' ),
		'parent_item_colon' => __( 'Parent Content Library:', 'content-library' ),
		'menu_name' => __( 'Content Library', 'content-library' ),
	);

	$args = array(
		'labels' => $labels,
		'hierarchical' => false,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail', 'custom-fields' ),
		'public' => true,
		'show_ui' => true,
		'show_in_menu' => true,
		'menu_position' => 20,
		'show_in_nav_menus' => true,
		'publicly_queryable' => true,
		'exclude_from_search' => false,
		'has_archive' => true,
		'query_var' => true,
		'can_export' => true,
		'rewrite' => true,
		'capability_type' => 'post'
	);

	register_post_type( 'content-library', $args );
}

// Register Custom Taxonomy
function custom_library_taxonomy() {

	$labels = array(
		'name'                       => 'Library Categories',
		'singular_name'              => 'Library Category',
		'menu_name'                  => 'Library Category',
		'all_items'                  => 'All Items',
		'parent_item'                => 'Parent Item',
		'parent_item_colon'          => 'Parent Item:',
		'new_item_name'              => 'New Item Name',
		'add_new_item'               => 'Add New Item',
		'edit_item'                  => 'Edit Item',
		'update_item'                => 'Update Item',
		'view_item'                  => 'View Item',
		'separate_items_with_commas' => 'Separate items with commas',
		'add_or_remove_items'        => 'Add or remove items',
		'choose_from_most_used'      => 'Choose from the most used',
		'popular_items'              => 'Popular Items',
		'search_items'               => 'Search Items',
		'not_found'                  => 'Not Found',
		'no_terms'                   => 'No items',
		'items_list'                 => 'Items list',
		'items_list_navigation'      => 'Items list navigation',
	);
	$args = array(
		'labels'                     => $labels,
		'hierarchical'               => true,
		'public'                     => true,
		'show_ui'                    => true,
		'show_admin_column'          => true,
		'show_in_nav_menus'          => true,
		'show_tagcloud'              => true,
	);
	register_taxonomy( 'library_category', array( 'content-library' ), $args );

}
add_action( 'init', 'custom_library_taxonomy');
