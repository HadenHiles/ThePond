<?php
/**
 * A unique identifier is defined to store the options in the database and reference them from the theme.
 * By default it uses the theme name, in lowercase and without spaces, but this can be changed if needed.
 * If the identifier changes, it'll appear as if the options have been reset.
 *
 */

function optionsframework_option_name() {

	// This gets the theme name from the stylesheet (lowercase and without spaces)
	$themename = get_option( 'stylesheet' );
	$themename = preg_replace("/\W/", "_", strtolower($themename) );

	$optionsframework_settings = get_option('optionsframework');
	$optionsframework_settings['id'] = $themename;
	update_option('optionsframework', $optionsframework_settings);

	// echo $themename;
}

/**
 * Defines an array of options that will be used to generate the settings page and be saved in the database.
 * When creating the 'id' fields, make sure to use all lowercase and no spaces.
 *
 */

function optionsframework_options() {

	// Test data
	$test_array = array(
		'one' => __('One', 'options_check'),
		'two' => __('Two', 'options_check'),
		'three' => __('Three', 'options_check'),
		'four' => __('Four', 'options_check'),
		'five' => __('Five', 'options_check')
	);

	// Multicheck Array
	$multicheck_array = array(
		'one' => __('French Toast', 'options_check'),
		'two' => __('Pancake', 'options_check'),
		'three' => __('Omelette', 'options_check'),
		'four' => __('Crepe', 'options_check'),
		'five' => __('Waffle', 'options_check')
	);

	// Multicheck Defaults
	$multicheck_defaults = array(
		'one' => '1',
		'five' => '1'
	);

	// Background Defaults
	$background_defaults = array(
		'color' => '',
		'image' => '',
		'repeat' => 'repeat',
		'position' => 'top center',
		'attachment'=>'scroll' );

	// Typography Defaults
	$typography_defaults = array(
		'size' => '15px',
		'face' => 'georgia',
		'style' => 'bold',
		'color' => '#bada55' );
		
	// Typography Options
	$typography_options = array(
		'sizes' => array( '6','12','14','16','20' ),
		'faces' => array( 'Helvetica Neue' => 'Helvetica Neue','Arial' => 'Arial' ),
		'styles' => array( 'normal' => 'Normal','bold' => 'Bold' ),
		'color' => false
	);

	// Pull all the categories into an array
	$options_categories = array();
	$options_categories_obj = get_categories();
	foreach ($options_categories_obj as $category) {
		$options_categories[$category->cat_ID] = $category->cat_name;
	}
	
	// Pull all tags into an array
	$options_tags = array();
	$options_tags_obj = get_tags();
	foreach ( $options_tags_obj as $tag ) {
		$options_tags[$tag->term_id] = $tag->name;
	}

	// Pull all the pages into an array
	$options_pages = array();
	$options_pages_obj = get_pages('sort_column=post_parent,menu_order');
	$options_pages[''] = 'Select a page:';
	foreach ($options_pages_obj as $page) {
		$options_pages[$page->ID] = $page->post_title;
	}
	
	$postarray1 = array();
    query_posts('post_type=post&posts_per_page=-1');
	$postarray1[''] = 'Select a Post ';
    if (have_posts()) :
        while (have_posts()) : the_post();
            $postarray1[get_the_ID()] = get_the_title();
        endwhile; endif;
    wp_reset_query();
	
	$options_pos = array();
	$options_pos[1] = 'UP';
	$options_pos[2] = 'DOWN';

	// If using image radio buttons, define a directory path
	$imagepath =  get_template_directory_uri() . '/images/';
	
	$wp_editor_settings = array(
			'wpautop' => true, // Default
			'textarea_rows' => 5,
			'tinymce' => array( 'plugins' => 'wordpress,wplink' )
	);

 
	// Below are the various theme options.
/* General Settings */
$options = array();
$shortname = "power";
$options[] = array( "name" => __( 'General Settings', 'power' ),
					"icon" => "general",
                    "type" => "heading");
 
$options[] = array( "name" => __( 'Topbar Left ', 'power' ),
		"desc" => __( 'Write text on left side of top bar.', 'power' ),
		"id" => $shortname."_topleft",
		"std" => "",
		"type" => "text");

$options[] = array( "name" => __( 'Topbar Right ', 'power' ),
		"desc" => __( 'Write text on right side of top bar.', 'power' ),
		"id" => $shortname."_topright",
		"std" => "",
		"type" => "text");

 
$options[] = array( "name" => __( 'Logo', 'power' ),
					"desc" => __( 'Upload a logo for your theme, or specify an image URL directly.', 'power' ),
					"id" => $shortname."_logo",
					"std" => "",
					"type" => "upload");

$options[] = array( "name" => __( 'Footer Logo', 'power' ),
		"desc" => __( 'Upload a logo for your theme, or specify an image URL directly.', 'power' ),
		"id" => $shortname."_footer_logo",
		"std" => "",
		"type" => "upload");

$options[] = array( "name" => __( 'Slogan', 'power' ),
		"desc" => __( 'Write slogan for your theme.', 'power' ),
		"id" => $shortname."_slogan",
		"std" => "",
		"type" => "textarea");

$options[] = array( "name" => __( 'Main Heading', 'power' ),
		"desc" => __( 'Write heading for your theme.', 'power' ),
		"id" => $shortname."_main_heading",
		"std" => "",
		"type" => "text");

$options[] = array( "name" => __( 'Custom Favicon', 'power' ),
					"desc" => __( 'Upload a 16px x 16px Png/Gif image that will represent your website\'s favicon.', 'power' ),
					"id" => $shortname."_custom_favicon",
					"std" => "",
					"type" => "upload");

$options[] = array( "name" => __( 'Tracking Code', 'power' ),
					"desc" => __( 'Paste your Google Analytics (or other) tracking code here. This will be added into the footer template of your theme.', 'power' ),
					"id" => $shortname."_google_analytics",
					"std" => "",
					"type" => "textarea");
 	

/* General Styling */
$options[] = array( "name" => __( 'Basic Setting', 'power' ),
					"icon" => "styling",
					"type" => "heading");

 
$options[] = array( "name" => __( 'Login Link', 'power' ),
					"desc" => __( 'Insert login url of your site.', 'power' ),
					"id" => $shortname."_login_url",
					"std" => "",
					"type" => "text");

$options[] = array( "name" => __( 'Register Link', 'power' ),
		"desc" => __( 'Insert register url of your site.', 'power' ),
		"id" => $shortname."_register_url",
		"std" => "",
		"type" => "text");
 

$options[] = array( "name" => __( 'Blog Text', 'power' ),
		"desc" => __( 'Add blog heading on home page .', 'power' ),
		"id" => $shortname."_blog_heading",
		"std" => "",
		"type" => "textarea");


$options[] = array( "name" => __( 'Footer Form', 'power' ),
		"desc" => __( 'Add footer form code here  .', 'power' ),
		"id" => $shortname."_footer_form",
		"std" => "",
		"type" => "editor",
		'settings' => $wp_editor_settings
);

 
	return $options;
}