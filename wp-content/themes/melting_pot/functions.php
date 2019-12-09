<?php
global $smof_data;
/*Melting Pot Theme Developed by Melt Creative Ltd */

require_once get_template_directory() . '/includes/class-tgm-plugin-activation.php';

add_action( 'tgmpa_register', 'melting_pot_register_required_plugins' );
function melting_pot_register_required_plugins() {
	 
	$plugins = array(

	 
		array(
			'name'               => 'Advance Custom Field Pro', // The plugin name.
			'slug'               => 'advanced-custom-fields-pro', // The plugin slug (typically the folder name).
			'source'             => get_template_directory() . '/includes/plugins/advanced-custom-fields-pro.zip', // The plugin source.
			'required'           => true,   
			'version'            => '',  
			'force_activation'   => true, 
			'force_deactivation' => false,  
			'external_url'       => '',  
			'is_callable'        => '', 
		),

		 

	);

	 
	$config = array(
		'id'           => 'melting_pot',                 // Unique ID for hashing notices for multiple instances of TGMPA.
		'default_path' => '',                      // Default absolute path to bundled plugins.
		'menu'         => 'tgmpa-install-plugins', // Menu slug.
		'parent_slug'  => 'themes.php',            // Parent menu slug.
		'capability'   => 'edit_theme_options',    // Capability needed to view plugin install page, should be a capability associated with the parent menu used.
		'has_notices'  => true,                    // Show admin notices or not.
		'dismissable'  => true,                    // If false, a user cannot dismiss the nag message.
		'dismiss_msg'  => '',                      // If 'dismissable' is false, this message will be output at top of nag.
		'is_automatic' => false,                   // Automatically activate plugins after installation or not.
		'message'      => '',                      // Message to output right before the plugins table.
  
	);

	tgmpa( $plugins, $config );
}


define('DEFAULT_IMG', get_stylesheet_directory_uri().'/images/placeholder.png') ;

//Initialize the update checker.

if( !$smof_data['stop_theme_update'] ) { 
	require 'includes/theme-update-checker.php';
	$example_update_checker = new ThemeUpdateChecker(
		'melting_pot',//Theme folder name, AKA "slug". 
		'http://demo.membershipwebsiteslab.com/wordpress/theme.json' //URL of the metadata file.
	);
	
	
	//$example_update_checker->checkForUpdates();
}

function wpb_add_google_fonts() {
 global $smof_data;
 if( $smof_data['google_font_url'] ){
	wp_enqueue_style( 'wpb-google-fonts', $smof_data['google_font_url'], false ); 
  }
  if( $smof_data['google_font_url_sec'] ){
	wp_enqueue_style( 'wpb-google-fonts_sec', $smof_data['google_font_url_sec'], false ); 
  }	
  if( $smof_data['google_font_url']  == '' && $smof_data['google_font_url_sec'] == '' ){
  	wp_enqueue_style( 'style-open', '//fonts.googleapis.com/css?family=Poppins:300,400,700' );
  }	
	
  if( $smof_data['font_awesome_url'] ){
	wp_enqueue_script( 'fonts_awesome', $smof_data['font_awesome_url'], false ); 
  }	
}
 
add_action( 'wp_enqueue_scripts', 'wpb_add_google_fonts' , 9);


function power_scripts() {
	global $smof_data;
	//wp_enqueue_style( 'foundation', get_template_directory_uri().'/css/foundation.css' );
	wp_enqueue_style( 'style', get_stylesheet_uri('main') );
	
	
	if( $smof_data['is_member_site'] )	
		wp_enqueue_style( 'membershipcss', get_template_directory_uri().'/css/membership.css' );
	
	wp_enqueue_style( 'slickslidercss', get_template_directory_uri().'/slick/slick.css' );
	wp_enqueue_style( 'remodalcss', get_template_directory_uri().'/remodal/remodal.css' );
 	wp_enqueue_script( 'script-name', get_template_directory_uri() . '/js/modernizr.js');
	wp_enqueue_script('jquery');
	wp_enqueue_script( 'fitvid', get_template_directory_uri() . '/js/jquery.fitvids.js', array(),'' , true );
	wp_enqueue_script( 'remodaljs', get_template_directory_uri() . '/remodal/remodal.min.js', array(),'' , true );
	wp_enqueue_script( 'slick-js', get_template_directory_uri() . '/slick/slick.js', array(),'', true );
	if(is_post_type_archive('content-library') || is_post_type_archive('podcast') || is_home() || is_search() || is_post_type_archive('members_directory') ) {
		wp_enqueue_script( 'filterizejs', get_template_directory_uri() . '/js/jquery.filterizr.min.js' );
	
	}
	
	if(is_page_template('members-templates/member_roadmap.php')) {
		wp_enqueue_script( 'timelinejs', get_template_directory_uri() . '/js/timeline.js', array(),'', true);
		wp_enqueue_style( 'timelinecss', get_template_directory_uri().'/css/timeline.css' );
	}
	
	//	if(is_page_template('members-templates/member-content.php')) {
	//		wp_dequeue_style( get_template_directory_uri(). 'learndash-blocks-css');
	//		wp_dequeue_style( get_template_directory_uri(). 'learndash_quiz_front_css-css');
	//		wp_dequeue_style( get_template_directory_uri(). 'learndash-front-cs');
	//	}
	//	
	//demo.membershipwebsiteslab.com/wp-content/plugins/sfwd-lms/themes/ld30/assets/css/
 
	wp_enqueue_script( 'customjs', get_template_directory_uri() . '/js/custom.js', array(),'' , true );
	wp_localize_script('customjs', 'meltObj', array(
		'ajax_url' => admin_url( 'admin-ajax.php' ),
		'logout_nonce' => wp_create_nonce('ajax-logout-nonce'),
		'home_url' => get_home_url(),
	));
}

add_action( 'wp_enqueue_scripts', 'power_scripts' );
add_action( 'after_setup_theme', 'defaltthemes_setup' );

if ( ! function_exists( 'defaltthemes_setup' ) ) {
	function defaltthemes_setup () {
 		// Editor Styles
		if ( '' != locate_template( 'editor-style.css' ) ) {
			add_editor_style();
		}
		// This theme uses post thumbnails
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'title-tag' );

		// Add default posts and comments RSS feed links to head
		add_theme_support( 'automatic-feed-links' );

		// Menu Locations
		if ( function_exists( 'wp_nav_menu') ) {
			add_theme_support( 'nav-menus' );
			register_nav_menus(
			array(
			'primary-menu' 	=> __( 'Header Menu', 'default_themes' )
			)
			);

			register_nav_menus(
			array(
			'secondary-menu' 		=> __( 'Footer Menu', 'default_themes' )
			)
			);
			
			register_nav_menus(
			array(
			'member-footer-menu' 		=> __( 'Member Footer Menu', 'default_themes' )
			)
			);
			
			register_nav_menus(
			array(
			'member-menu' 		=> __( 'Member Menu', 'default_themes' )
			)
			);
			register_nav_menus(
			array(
			'course-category-menu' 		=> __( 'Course Category Menu', 'default_themes' )
			)
			);
			register_nav_menus(
			array(
			'member-account-menu' 		=> __( 'Member Account Menu', 'default_themes' )
			)
			);
			
			register_nav_menus(
			array(
			'member-menu-logged-out' 		=> __( 'Member Menu Logout', 'default_themes' )
			)
			);
			
		}

		// Set the content width based on the theme's design and stylesheet.

		if ( ! isset( $content_width ) ) {
			$content_width = 640;
		}

	} // End woothemes_setup()

}

/***--------------------  Add Melt Theme options -------------------***/
  function theme_customize_register( $wp_customize ) {
   
  
	   // h1 Title Color
    $wp_customize->add_setting( 'h1_title', array(
      'default'   => '',
      'transport' => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'h1_title', array(
      'section' => 'colors',
      'label'   => esc_html__( 'H1 Title Color', 'melt_default' ),
    ) ) );
	  
	  // h2 Title Color
    $wp_customize->add_setting( 'h2_title', array(
      'default'   => '',
      'transport' => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'h2_title', array(
      'section' => 'colors',
      'label'   => esc_html__( 'H2 Title Color', 'melt_default' ),
    ) ) );


	  
	  
	 // Member header
    $wp_customize->add_setting( 'member_head', array(
      'default'   => '',
      'transport' => 'refresh',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'member_head', array(
      'section' => 'colors',
      'label'   => esc_html__( 'Member Head', 'melt_default' ),
    ) ) );

	  
	  
	  // Link color
    $wp_customize->add_setting( 'MemberNav_color', array(
      'default'   => '',
      'transport' => 'refresh',
      'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'MemberNav_color', array(
      'section' => 'colors',
      'label'   => esc_html__( 'Member Nav color', 'theme' ),
    ) ) );
	  
	    // Link color Hover
    $wp_customize->add_setting( 'MemberNavHover_color', array(
      'default'   => '',
      'transport' => 'refresh',
      'sanitize_callback' => 'sanitize_hex_color',
    ) );

    $wp_customize->add_control( new WP_Customize_Color_Control( $wp_customize, 'MemberNavHover_color', array(
      'section' => 'colors',
      'label'   => esc_html__( 'Member Nav Hover color', 'theme' ),
    ) ) );

	  

	  
	  
  }

add_action( 'customize_register', 'theme_customize_register' );



//**---Echo the Customisation---**/ 

function theme_get_customizer_css() {
    ob_start();

   //H1 Title Colour
    $h1_title = get_theme_mod( 'h1_title', '' );
    if ( ! empty( $h1_title ) ) { ?>
      .UseThemeOption h1, .UseThemeOption > h1{
        color: <?php echo $h1_title; ?> !important;
      }
      <?php
    }
	
	//H2 Title Colour
    $h2_title = get_theme_mod( 'h2_title', '' );
    if ( ! empty( $h2_title ) ) { ?>

      .UseThemeOption h2, .UseThemeOption > h2{
        color: <?php echo $h2_title; ?> !important;
      }
      <?php
    }

	
	
	//Member header
    $member_header = get_theme_mod( 'member_head', '' );
    if ( ! empty( $member_header ) ) { ?>
      .MemberheaderWrap, .CTABannerBG, #CourseHeader, #LessonHeader, .radial-progress .inset, .mp-form-submit input[type="submit"]{
        background-color: <?php echo $member_header; ?> !important;
      }
      <?php
    }
	
	//Membnav Colour
	 $membnav_color = get_theme_mod( 'MemberNav_color', '' );
    if ( ! empty( $membnav_color ) ) {
      ?>
      ul.LoggedInDONTWORK li a {
        color: <?php echo $membnav_color; ?> !important;
      }
      <?php
    }
	
	//Membnav hover Colour
	 $membnavhover_color = get_theme_mod( 'MemberNavHover_color', '' );
    if ( ! empty( $membnavhover_color ) ) {
      ?>
      ul.LoggedIn li a:hover {
        color: <?php echo $membnavhover_color; ?> !important;
        border-bottom:2px solid <?php echo $membnavhover_color; ?> !important;
      }
      <?php
    }
	
	
  

    $css = ob_get_clean();
    return $css;
  }


// Modify our styles registration like so:

function theme_enqueue_styles() {
  wp_enqueue_style( 'theme-styles', get_stylesheet_uri() ); // This is where you enqueue your theme's main stylesheet
  $custom_css = theme_get_customizer_css();
  wp_add_inline_style( 'theme-styles', $custom_css );
}

add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );


/****--------------------- End Customise --------------------------****/



require_once( get_template_directory() . '/admin/index.php' );

/***  Add Melt Theme Loging & Dashboard customisation ***/

require_once( get_template_directory() . '/admin/dashboard/melt-admin-functions.php' );

/***** Register Widget *******/
add_action( 'widgets_init', 'footer_widgets_init' );

function footer_widgets_init() {
	register_sidebar( array(
	'name'          => __( 'Footer Widget Area', 'power' ),
	'id'            => 'footer-widget',
	'description'   => __( 'Appears in the footer section of the site.', 'power' ),
	'before_widget' => '<div class="">',
	'after_widget'  => '</div>',
	'before_title'  => '<h4 class="widget-title" style="display:none">',
	'after_title'   => '</h4>',
	) );

}

add_action( 'widgets_init', 'sideform_widgets_init' );

function sideform_widgets_init() {
	register_sidebar( array(
	'name'          => __( 'Sidebar 1', 'power' ),
	'id'            => 'sideform_widget',
	'description'   => __( 'Appears in the right section of the homepage.', 'power' ),
	'before_widget' => '<div">',
	'after_widget'  => '</div>',
	'before_title'  => '<h4 class="widget-title">',
	'after_title'   => '</h4>',
	) );

}

add_action( 'widgets_init', 'fullform_widgets_init' );

function fullform_widgets_init() {
	register_sidebar( array(
	'name'          => __( 'Sidebar 2', 'power' ),
	'id'            => 'fullform_widget',
	'description'   => __( 'Appears in the section of the site.', 'power' ),
	'before_widget' => '<div>',
	'after_widget'  => '</div>',
	'before_title'  => '<h4 class="widget-title">',
	'after_title'   => '</h4>',
	) );

}

add_action( 'widgets_init', 'power_archive_widgets_init' );
function power_archive_widgets_init() {
	register_sidebar( array(
	'name'          => __( 'Sidebar 3', 'power' ),
	'id'            => 'archive-widget',
	'before_widget' => '<aside  class="widget widget_archive">',
	'after_widget'  => '</aside>',
	'before_title'  => '<h3 class="archives">',
	'after_title'   => '</h3>',
	) );
	
	
		register_sidebar( array(
		'name' => __( 'Sidebar 4', 'power' ),
		'id' => 'sidebar4-widget',
		'description' => __( 'Appears in page slected.', 'power' ),
		'before_widget' => '<div class="memberSidedbar">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title">',
		'after_title' => '</h4>',
	) );
}



/* Learndash Course Progress */
add_action( 'widgets_init', 'lms_course_side_widgets_init' );

function lms_course_side_widgets_init() {
	
	register_sidebar( array(
		'name' => __( 'LearnDash', 'power' ),
		'id' => 'lms-progress-widget',
		'description' => __( 'Appears in the sidebar of the programme.', 'power' ),
		'before_widget' => '<div class="">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title" style="display:none">',
		'after_title' => '</h4>',
	) );
	
		register_sidebar( array(
		'name' => __( 'Shop Sidebar', 'power' ),
		'id' => 'shop-sidebar-widget',
		'description' => __( 'Appears in the sidebar of the programme.', 'power' ),
		'before_widget' => '<div class="">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title" style="display:none">',
		'after_title' => '</h4>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Sidebar Promo', 'power' ),
		'id' => 'sidebar-promo-widget',
		'description' => __( 'Appears in page slected.', 'power' ),
		'before_widget' => '<div class="memberPromo">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title" style="display:none">',
		'after_title' => '</h4>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Sidebar Support', 'power' ),
		'id' => 'sidebar-support-widget',
		'description' => __( 'Appears in page slected.', 'power' ),
		'before_widget' => '<div class="memberSupport">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title" style="display:none">',
		'after_title' => '</h4>',
	) );
	
	register_sidebar( array(
		'name' => __( 'Reasons To Join', 'power' ),
		'id' => 'reason-to-join-widget',
		'description' => __( 'Appears in page slected.', 'power' ),
		'before_widget' => '<div class="memberReason">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widget-title" style="display:none">',
		'after_title' => '</h4>',
	) );

}




/*
 * Helper function to return the theme option value. If no value has been saved, it returns $default.
 * Needed because options are saved as serialized strings.
 *
 * This code allows the theme to work without errors if the Options Framework plugin has been disabled.
 */

if ( !function_exists( 'of_get_option' ) ) {
	function of_get_option($name, $default = false) {

		$optionsframework_settings = get_option('optionsframework');

		// Gets the unique option id
		$option_name = $optionsframework_settings['id'];

		if ( get_option($option_name) ) {
			$options = get_option($option_name);
		}

		if ( isset($options[$name]) ) {
			return $options[$name];
		} else {
			return $default;
		}
	}
}



/**
 * Register Post Type: Testimonials.
 */

function cptui_register_my_cpts_testimonials() {

	$labels = array(
		"name" => __( "Testimonials", "testimonials" ),
		"singular_name" => __( "Testimonial", "testimonials" ),
	);

	$args = array(
		"label" => __( "Testimonials", "testimonials" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"delete_with_user" => false,
		"show_in_rest" => false,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => array( "slug" => "testimonials", "with_front" => true ),
		"query_var" => true,
		"menu_icon" => "dashicons-testimonial",
		"supports" => array( "title", "editor", "thumbnail" ),
	);

	register_post_type( "testimonials", $args );
}

add_action( 'init', 'cptui_register_my_cpts_testimonials' );




add_action( 'after_setup_theme', 'woocommerce_support' );
function woocommerce_support() {
    add_theme_support( 'woocommerce' );
}

add_filter( 'woocommerce_product_tabs', 'woo_remove_product_tabs', 98 );
function woo_remove_product_tabs( $tabs ) {
    unset( $tabs['additional_information'] );   // Remove the additional information tab
    return $tabs;
}

function show_testimonial_slider($atts) {
  ob_start();
  get_template_part('template-parts/elementor-testimonials');
  return ob_get_clean();
}
add_shortcode('testimonial_slider', 'show_testimonial_slider');







/*-----------------------------------------------------------------------------------*/
/* SECURITY NINJA FUNCTIONS */
/*-----------------------------------------------------------------------------------*/

function remove_version() {
  return '';
}
add_filter('the_generator', 'remove_version');

function wrong_login() {
  return 'Wrong username or password.';
}
add_filter('login_errors', 'wrong_login');

define('DISALLOW_FILE_EDIT', true);

remove_action('wp_head', 'wlwmanifest_link');

remove_action('wp_head', 'rsd_link');

add_filter('xmlrpc_enabled', '__return_false');


if(!function_exists('yith_ywpi_before_replace_customer_details_call_back')) {
function yith_ywpi_before_replace_customer_details_call_back($replace_value, $match, $order_id)
{
if (('_billing_state' == $match)) {
$state_code = yit_get_prop(wc_get_order($order_id), '_billing_state', true);
$country_code = yit_get_prop(wc_get_order($order_id), '_billing_country', true);
$states = WC()->countries->get_states();

if (isset($states[$country_code][$state_code])) {
$replace_value = $states[$country_code][$state_code];
}
}
return $replace_value;
}
add_filter('yith_ywpi_replace_customer_details', 'yith_ywpi_before_replace_customer_details_call_back', 10, 3);
}


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


//fbr integration

  
 
 // Register Custom Post Type
function Members_Directory() {

	$labels = array(
		'name'                  => _x( 'Members Directory', 'Post Type General Name', 'melt_default' ),
		'singular_name'         => _x( 'Members Directory', 'Post Type Singular Name', 'melt_default' ),
		'menu_name'             => __( 'Members Directory', 'melt_default' ),
		'name_admin_bar'        => __( 'Post Type', 'melt_default' ),
		'archives'              => __( 'Item Archives', 'melt_default' ),
		'attributes'            => __( 'Item Attributes', 'melt_default' ),
		'parent_item_colon'     => __( 'Parent Item:', 'melt_default' ),
		'all_items'             => __( 'All Items', 'melt_default' ),
		'add_new_item'          => __( 'Add New Item', 'melt_default' ),
		'add_new'               => __( 'Add New', 'melt_default' ),
		'new_item'              => __( 'New Item', 'melt_default' ),
		'edit_item'             => __( 'Edit Item', 'melt_default' ),
		'update_item'           => __( 'Update Item', 'melt_default' ),
		'view_item'             => __( 'View Item', 'melt_default' ),
		'view_items'            => __( 'View Items', 'melt_default' ),
		'search_items'          => __( 'Search Item', 'melt_default' ),
		'not_found'             => __( 'Not found', 'melt_default' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'melt_default' ),
		'featured_image'        => __( 'Featured Image', 'melt_default' ),
		'set_featured_image'    => __( 'Set featured image', 'melt_default' ),
		'remove_featured_image' => __( 'Remove featured image', 'melt_default' ),
		'use_featured_image'    => __( 'Use as featured image', 'melt_default' ),
		'insert_into_item'      => __( 'Insert into item', 'melt_default' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'melt_default' ),
		'items_list'            => __( 'Items list', 'melt_default' ),
		'items_list_navigation' => __( 'Items list navigation', 'melt_default' ),
		'filter_items_list'     => __( 'Filter items list', 'melt_default' ),
	);
	$args = array(
		'label'                 => __( 'Members Directory', 'melt_default' ),
		'description'           => __( 'Post Type Description', 'melt_default' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
//		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive' 			=> true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
	);
	
 
	global $smof_data;
	
	if( $smof_data['enable_member_directory'] )	
		register_post_type( 'members_directory', $args );
	

}
add_action( 'init', 'Members_Directory', 0 );
 
 
 
 // Register Podcast Post Type
function podcast_cpt_function() {

	$labels = array(
		'name'                  => _x( 'Podcast', 'Post Type General Name', 'melt_default' ),
		'singular_name'         => _x( 'Podcast', 'Post Type Singular Name', 'melt_default' ),
		'menu_name'             => __( 'Podcast', 'melt_default' ),
		'name_admin_bar'        => __( 'Podcast', 'melt_default' ),
		'archives'              => __( 'Item Archives', 'melt_default' ),
		'attributes'            => __( 'Item Attributes', 'melt_default' ),
		'parent_item_colon'     => __( 'Parent Item:', 'melt_default' ),
		'all_items'             => __( 'All Items', 'melt_default' ),
		'add_new_item'          => __( 'Add New Item', 'melt_default' ),
		'add_new'               => __( 'Add New', 'melt_default' ),
		'new_item'              => __( 'New Item', 'melt_default' ),
		'edit_item'             => __( 'Edit Item', 'melt_default' ),
		'update_item'           => __( 'Update Item', 'melt_default' ),
		'view_item'             => __( 'View Item', 'melt_default' ),
		'view_items'            => __( 'View Items', 'melt_default' ),
		'search_items'          => __( 'Search Item', 'melt_default' ),
		'not_found'             => __( 'Not found', 'melt_default' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'melt_default' ),
		'featured_image'        => __( 'Featured Image', 'melt_default' ),
		'set_featured_image'    => __( 'Set featured image', 'melt_default' ),
		'remove_featured_image' => __( 'Remove featured image', 'melt_default' ),
		'use_featured_image'    => __( 'Use as featured image', 'melt_default' ),
		'insert_into_item'      => __( 'Insert into item', 'melt_default' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'melt_default' ),
		'items_list'            => __( 'Items list', 'melt_default' ),
		'items_list_navigation' => __( 'Items list navigation', 'melt_default' ),
		'filter_items_list'     => __( 'Filter items list', 'melt_default' ),
	);
	$args = array(
		'label'                 => __( 'Podcast', 'melt_default' ),
		'description'           => __( 'Post Type Description', 'melt_default' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
//		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive' 			=> true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
	);
	
 
	global $smof_data;
	
	if( $smof_data['enable_podcast'] )	
		register_post_type( 'podcast', $args );
	

}
add_action( 'init', 'podcast_cpt_function', 0 ); 
 // Register Custom Post Type
function Case_Study() {

	$labels = array(
		'name'                  => _x( 'Case Study', 'Post Type General Name', 'melt_default' ),
		'singular_name'         => _x( 'Case Study', 'Post Type Singular Name', 'melt_default' ),
		'menu_name'             => __( 'Case Study', 'melt_default' ),
		'name_admin_bar'        => __( 'Post Type', 'melt_default' ),
		'archives'              => __( 'Item Archives', 'melt_default' ),
		'attributes'            => __( 'Item Attributes', 'melt_default' ),
		'parent_item_colon'     => __( 'Parent Item:', 'melt_default' ),
		'all_items'             => __( 'All Items', 'melt_default' ),
		'add_new_item'          => __( 'Add New Item', 'melt_default' ),
		'add_new'               => __( 'Add New', 'melt_default' ),
		'new_item'              => __( 'New Item', 'melt_default' ),
		'edit_item'             => __( 'Edit Item', 'melt_default' ),
		'update_item'           => __( 'Update Item', 'melt_default' ),
		'view_item'             => __( 'View Item', 'melt_default' ),
		'view_items'            => __( 'View Items', 'melt_default' ),
		'search_items'          => __( 'Search Item', 'melt_default' ),
		'not_found'             => __( 'Not found', 'melt_default' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'melt_default' ),
		'featured_image'        => __( 'Featured Image', 'melt_default' ),
		'set_featured_image'    => __( 'Set featured image', 'melt_default' ),
		'remove_featured_image' => __( 'Remove featured image', 'melt_default' ),
		'use_featured_image'    => __( 'Use as featured image', 'melt_default' ),
		'insert_into_item'      => __( 'Insert into item', 'melt_default' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'melt_default' ),
		'items_list'            => __( 'Items list', 'melt_default' ),
		'items_list_navigation' => __( 'Items list navigation', 'melt_default' ),
		'filter_items_list'     => __( 'Filter items list', 'melt_default' ),
	);
	$args = array(
		'label'                 => __( 'Case Study', 'melt_default' ),
		'description'           => __( 'Post Type Description', 'melt_default' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
//		'taxonomies'            => array( 'category', 'post_tag' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 5,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive' 			=> true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
	);
	
 
	global $smof_data;
	
	if( $smof_data['enable_case_study'] )	
		register_post_type( 'enable_case_study', $args );
	

}
add_action( 'init', 'Case_Study', 0 );
  


// custom excerpt length
function themify_custom_excerpt_length( $length ) {
   return 15;
}
add_filter( 'excerpt_length', 'themify_custom_excerpt_length', 999 );

// add more link to excerpt
function themify_custom_excerpt_more($more) {
   global $post;
   return '';
}
add_filter('excerpt_more', 'themify_custom_excerpt_more');

function exclude_category( $query ) {
if ( $query->is_home() && $query->is_main_query() && !is_user_logged_in()  ) {
$query->set( 'cat', '-12' );
}
}
add_action( 'pre_get_posts', 'exclude_category' );

/* breadcrumb */
function aj_breadcrumbs() {
		global $wp_query;
		 
		$course_label      =  "Courses" ;
		// Define main variables
		$trail   = array();
		$trail[] =  aj_build_anchor_links( get_permalink(253), esc_html__( 'Dashboard', 'uncanny-learndash-toolkit' ) );
		//$dashboard_link      = get_post_type_archive_link( 'sfwd-courses' );
		$dashboard_link      = '';
		$dashboard_text      = 'Course';
		$dashboard_separator = '&raquo;';

		$get_dashboard_text      = '' ;
		$get_dashboard_link      = '';
		//$get_dashboard_separator = '>';
		$course_archive_link     = aj_build_anchor_links( get_post_type_archive_link( 'sfwd-courses' ), esc_html__( $course_label, 'uncanny-learndash-toolkit' ) );
		//$course_archive_link     = self::uo_build_anchor_links( get_post_type_archive_link( 'sfwd-courses' ), esc_html__( 'Courses', 'uncanny-learndash-toolkit' ) );

		if ( strlen( trim( $get_dashboard_text ) ) ) {
			$dashboard_text = $get_dashboard_text;
		}

		if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
			$dashboard_link = get_permalink( $get_dashboard_link );
			$dashboard_link = aj_build_anchor_links( $dashboard_link, $dashboard_text );
		}

		if ( strlen( trim( $get_dashboard_separator ) ) ) {
			$dashboard_separator = $get_dashboard_separator;
		}
		$lesson_id = false;


		// If it's on home page
		if ( is_front_page() ) {
			$trail = array(); //Removing Single Home link from Homepage.
		} elseif ( is_singular() ) {
			// Get singular vars (page, post, attachments)
			$post      = $wp_query->get_queried_object();
			$post_id   = absint( $wp_query->get_queried_object_id() );
			$post_type = $post->post_type;

			if ( 'post' === $post_type ) {
				$maybe_tax = aj_post_taxonomy( $post_id );

				if ( false !== $maybe_tax ) {
					$trail[] = $maybe_tax;
				}
				$trail[] = get_the_title( $post_id );

			} elseif ( 'page' === $post_type ) {
				// If Woocommerce is installed and being viewed, add shop page to cart, checkout pages
				if ( class_exists( 'Woocommerce' ) ) {

					if ( is_cart() || is_checkout() ) {
						// Get shop page
						if ( function_exists( 'wc_get_page_id' ) ) {
							$shop_id    = wc_get_page_id( 'shop' );
							$shop_title = get_the_title( $shop_id );
							if ( function_exists( 'wpml_object_id' ) ) {
								$shop_title = get_the_title( wpml_object_id( $shop_id, 'page' ) );
							}
							// Shop page
							if ( $shop_id && $shop_title ) {
								$trail[] = aj_build_anchor_links( get_permalink( $shop_id ), $shop_title );
							}
						}
					}
					//$trail[] = get_the_title( $post_id );
				} else {
					// Regular pages. See if the page has any ancestors. Add in the trail if ancestors are found
					$ancestors = get_ancestors( $post_id, 'page' );
					if ( ! empty ( $ancestors ) ) {
						$ancestors = array_reverse( $ancestors );
						foreach ( $ancestors as $page ) {
							$trail[] = aj_build_anchor_links( get_permalink( $page ), get_the_title( $page ) );
						}
					}
					//$trail[] = get_the_title( $post_id );
				}
			} elseif ( 'sfwd-courses' === $post_type ) {
				// See if Single Course is being displayed.
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}
				//$trail[] = get_the_title( $post_id );
			} elseif ( 'sfwd-lessons' === $post_type ) {
				// See if Single Lesson is being displayed.
				$course_id = get_post_meta( $post_id, 'course_id', true ); // Getting Parent Course ID
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}
				$trail[] = aj_build_anchor_links( get_permalink( $course_id ), get_the_title( $course_id ) ); // Getting Lesson's Course Link
				//$trail[] = get_the_title( $post_id );
			} elseif ( 'sfwd-topic' === $post_type ) {
				// See if single Topic is being displayed
				$course_id = get_post_meta( $post_id, 'course_id', true ); // Getting Parent Course ID
				$lesson_id = get_post_meta( $post_id, 'lesson_id', true ); // Getting Parent Lesson ID
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}
				$trail[] = aj_build_anchor_links( get_permalink( $course_id ), get_the_title( $course_id ) ); // Getting Lesson's Course Link
				$trail[] = aj_build_anchor_links( get_permalink( $lesson_id ), get_the_title( $lesson_id ) ); // Getting Topics's Lesson Link
				//$trail[] = get_the_title( $post_id );
			} elseif ( 'sfwd-quiz' === $post_type ) {
				// See if quiz is being displayed
				$course_id = get_post_meta( $post_id, 'course_id', true ); // Getting Parent Course ID
				if ( strlen( trim( $get_dashboard_link ) ) && '0' !== $get_dashboard_link ) {
					$trail[] = $dashboard_link;
				} else {
					$trail[] = $course_archive_link;
				}

				$topic_id = get_post_meta( $post_id, 'lesson_id', true ); // Getting Parent Topic/Lesson ID
				if ( 'sfwd-topic' === get_post_type( $topic_id ) ) {
					$lesson_id = get_post_meta( $topic_id, 'lesson_id', true ); // Getting Parent Lesson ID
				}
				$trail[] = aj_build_anchor_links( get_permalink( $course_id ), get_the_title( $course_id ) ); // Getting Lesson's Course Link
				//If $lesson_id is false, the quiz is associated with a lesson and course but not a topic.
				if ( $lesson_id ) {
					$trail[] = aj_build_anchor_links( get_permalink( $lesson_id ), get_the_title( $lesson_id ) ); // Getting Topics's Lesson Link
				}
				//If $topic_id is false, the quiz is associated with a course but not associated with any lessons or topics.
				if ( $topic_id ) {
					$trail[] = aj_build_anchor_links( get_permalink( $topic_id ), get_the_title( $topic_id ) );
				}
				//$trail[] = get_the_title( $post_id );

			} else {
				// Add shop page to single product
				if ( 'product' === $post_type ) {
					// Get shop page
					if ( class_exists( 'Woocommerce' ) && function_exists( 'wc_get_page_id' ) ) {
						$shop_id    = wc_get_page_id( 'shop' );
						$shop_title = get_the_title( $shop_id );
						if ( function_exists( 'wpml_object_id' ) ) {
							$shop_title = get_the_title( wpml_object_id( $shop_id, 'page' ) );
						}

						// Shop page
						if ( $shop_id && $shop_title ) {
							$trail[] = aj_build_anchor_links( get_permalink( $shop_id ), $shop_title );
						}
					}
				}

				// Getting terms of the post.
				if ( aj_lms_get_taxonomy( $post_id, $post_type ) ) {
					$trail[] = aj_lms_get_taxonomy( $post_id, $post_type );
				}
				//$trail[] = get_the_title( $post_id );
			}
		}
		// If it's an Archive
		if ( is_archive() ) {
			//Ignore if Courses & Products
			if ( ! is_post_type_archive( 'sfwd-courses' ) && ! is_post_type_archive( 'product' ) ) {
				if ( is_category() || is_tax() ) {
					$trail[] = single_cat_title( '', false ); // If its Blog Category
				}
				if ( is_day() ) {
					$trail[] = get_the_date(); // If its Single Day Archive
				}
				if ( is_month() ) {
					$trail[] = get_the_date( __( 'F Y', 'uncanny-learndash-toolkit' ) ) . esc_html__( ' Archives', 'uncanny-learndash-toolkit' ); // If Mothly Archives
				}
				if ( is_year() ) {
					$trail[] = get_the_date( __( 'Y', 'uncanny-learndash-toolkit' ) ) . esc_html__( ' Archives', 'uncanny-learndash-toolkit' ); // If its Yearly Archives
				}
				if ( is_author() ) {
					$trail[] = get_the_author(); // If its Author's Archives
				}
			} elseif ( is_post_type_archive( 'sfwd-courses' ) ) {
				$trail[] = esc_html__( $course_label, 'uncanny-learndash-toolkit' );
			} elseif ( is_post_type_archive( 'product' ) ) {
				$trail[] = esc_html__( 'Shop', 'uncanny-learndash-toolkit' );
			}
		}

		if ( is_search() ) {
			$trail[] = esc_html__( 'Search', 'uncanny-learndash-toolkit' );
			$trail[] = get_search_query();
		}

		// Build breadcrumbs
		$classes = 'sfwd-breadcrumbs clr';

		if ( array_key_exists( 'the_content', $GLOBALS['wp_filter'] ) ) {
			$classes .= ' lms-breadcrumbs ';
		}
		 
		// Open breadcrumbs
		$breadcrumb = '<nav class="' . esc_attr( $classes ) . '"><div class="breadcrumb-trail">';

		// Separator HTML
		$separator = '<span class="sep"> ' . stripslashes( $dashboard_separator ) . ' </span>';

		// Join all trail items into a string
		$breadcrumb .= implode( $separator, $trail );

		// Close breadcrumbs
		$breadcrumb .= '</div></nav>';

		echo $breadcrumb;
	}
	
function aj_build_anchor_links( $permalink, $title ) {

		return sprintf(
			'<span itemscope="" itemtype="http://schema.org/Breadcrumb"><a href="%1$s" title="%2$s" rel="%3$s" class="trail-begin"><span itemprop="%2$s">%4$s</span></a></span>',
			esc_url( $permalink ),
			esc_attr( $title ),
			sanitize_title( $title ),
			esc_html( $title )
		);

	}	
function aj_post_taxonomy( $post_id, $taxonomy = 'category' ) {
		$terms = get_the_terms( $post_id, $taxonomy );
		$t     = array();
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$t[] = aj_build_anchor_links( get_term_link( $term->slug, $taxonomy ), $term->name );
			}

			return implode( ' / ', $t );
		} else {
			return false;
		}
	}	
function aj_lms_get_taxonomy( $post_id, $post_type ) {
		$taxonomies = get_taxonomies( array( 'object_type' => array( $post_type ) ), 'objects' );
		$tax        = array();
		if ( $taxonomies ) {
			foreach ( $taxonomies as $taxonomy ) {
				// Pass the $taxonomy name to uo_post_taxonomy to return with proper terms and links
				$tax[] = aj_post_taxonomy( $post_id, $taxonomy->query_var );
			}

			return implode( ' / ', $tax );
		} else {
			return false;
		}
	}
	

add_action( 'wp_ajax_track_lesson_ajax', 'track_lesson_ajax' );

function track_lesson_ajax() {
 
	global $wpdb;

	$lesson=$_POST['lesson_id'];
	$user=get_current_user_id();
	$type='1';
	$status=($_POST['track_type'] != '' ? $_POST['track_type'] : "1");

	if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) :

		if (is_numeric($lesson) && is_numeric($user) && is_numeric($status)) :

			$is_entry=check_lesson_track($lesson,$user,$type,$status);

			if (!$is_entry) {
				
				if($status == 3 ) {
					
					$post = get_post( $lesson );
					 
					if ( ! ( $post instanceof WP_Post ) ) {
						return false;
					}
					
					if ( $post->post_type == 'sfwd-courses' ) {
						 
							aj_learndash_courses_mark_complete($user , $lesson );
					}else {	
					/*$meta_key   = 'course_completed_' . $lesson;
					$meta_value = time();
					
					update_user_meta( $user, $meta_key, $meta_value ); */
						 learndash_process_mark_complete($user , $lesson );
					}
				}
				$wpdb->query("INSERT INTO " . $wpdb->prefix . "lessontracker (user_id,lesson_id,lesson_type,lesson_status) VALUES ($user,$lesson,$type,$status)");
				echo 'active';
				 
			} else {
				if($status == 3 ) {
				
					$post = get_post( $lesson );
					if ( ! ( $post instanceof WP_Post ) ) {
						return false;
					}
					
					if ( $post->post_type == 'sfwd-courses' ) {
							aj_learndash_courses_mark_incomplete($user , $lesson );
					}else {	
					/*$meta_key   = 'course_completed_' . $lesson;
					$meta_value = time();
					
					update_user_meta( $user, $meta_key, $meta_value ); */
						 learndash_process_mark_incomplete($user , $lesson );
					}
				
					// learndash_process_mark_incomplete($user , $lesson );
				}
				$wpdb->query("delete from " . $wpdb->prefix . "lessontracker where user_id=$user and lesson_id=$lesson and lesson_type=$type and lesson_status=$status");
				echo 'inactive';
				 
			}

		endif;

		die();

	endif;

}
function track_lesson($lesson,$user,$type="1",$status="1",$unset=0) {
	global $wpdb;
	if ($unset) {
		$is_entry=check_lesson_track($lesson,$user,$type,$status);
		if ($is_entry) {
			$wpdb->query("delete from " . $wpdb->prefix . "lessontracker where user_id=$user and lesson_id=$lesson and lesson_type=$type and lesson_status=$status");
		}
		 

	} else {

		$is_entry=check_lesson_track($lesson,$user,$type,$status);

		if (!$is_entry) {
			$wpdb->query("INSERT INTO " . $wpdb->prefix . "lessontracker (user_id,lesson_id,lesson_type,lesson_status) VALUES ($user,$lesson,$type,$status)");
		}

		 

	}


	//If not in the db, add it
}
function check_lesson_track($lesson,$user,$type="1",$status="1") {

	global $wpdb;

	//Query database for current lesson
	$track_count = $wpdb->get_var( "SELECT COUNT(*) FROM " . $wpdb->prefix . "lessontracker where user_id=$user and lesson_id=$lesson and lesson_status=$status" );
	//Count results
	if ($track_count > 0) {
		return true;
	} else {
		return false;
	}

}





function msa_lessonhistory($atts,$content=null) {
  extract(shortcode_atts(array(
    "type" => 'history',
    'status' => '1'
  ), $atts));

  $returndata='';
  global $wpdb;
  if ($type=='history') :
    $history=$wpdb->get_results("select * from " . $wpdb->prefix . "lessonlog where `user_id`=" . get_current_user_id() . " order by `viewed` desc limit 20",ARRAY_A);

  elseif ($type=='tracking') :

    $history=$wpdb->get_results("select * from " . $wpdb->prefix . "lessontracker where `user_id`=" . get_current_user_id() . " and `lesson_status`=" . $status . " order by `viewed` desc",ARRAY_A);

  endif;

  

  $returndata='<ul>';

  foreach($history as $line) {

    if(date("m-d-y") == date("m-d-y", strtotime($line['viewed']))) {
        $time = "Today";
    }
    else if(date("m-d-y", strtotime("-1 day")) == date("m-d-y", strtotime($line['viewed']))) {
        $time = "Yesterday";
    }
    else {
        $time = date("m-d-y", strtotime($line['viewed']));
    }

      if (get_field('course_page_type',$line['lesson_id'])!='standalone') :
        $course_id=wp_get_post_parent_id($line['lesson_id']);
	  endif;
	  
	  if( $course_id == 0 )	
        $course_id=$line['lesson_id'];
    

      if (get_field('course_page_type',$course_id) == 'module') :
        $course_id=wp_get_post_parent_id($course_id);
      endif;
	
	$post = get_post( $course_id );
	 if ( ! is_object( $post ) ) {
       continue;
    }
	
      if (has_post_thumbnail($course_id)) :
        $thumb_id = get_post_thumbnail_id($course_id);
        $thumb_url_array = wp_get_attachment_image_src($thumb_id, 'full');
        $thumb_url = $thumb_url_array[0];
        $course_thumb =' style="background: url(' . $thumb_url . ')no-repeat;background-size:cover;background-position:center;"';
      else:
        $course_thumb=' style="background: url('.DEFAULT_IMG. ')no-repeat;background-size:cover;background-position:center;"';
      endif;
    ob_start();
?>
<li class="course-listing training_listing listing_history">
      
        <a href="<?php echo get_the_permalink($line['lesson_id']); ?>" class="savedvideo course-content" id="<?php echo $course_id; ?>" >
			
			<div class="coursePrevImage" <?php echo ($course_thumb); ?>></div>

        </a>  
	
	<h4><a href="<?php echo get_the_permalink($line['lesson_id']); ?>"><?php echo get_the_title($line['lesson_id']); ?></a></h4>
			
			<a href="<?php echo get_the_permalink($line['lesson_id']); ?>" class="BTN">Access</a>
	
        </li>

<?php

    $returndata .= ob_get_contents();
    ob_end_clean();

    //wp_reset_postdata();
  }
  $returndata.="</ul>";

  return $returndata;
}
add_shortcode("lessonhistory", "msa_lessonhistory");
		
 
function aj_learndash_courses_mark_complete($user_id , $course_id ) {
	 
	$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true );

	if ( ( empty( $course_progress ) ) || ( ! is_array( $course_progress ) ) ) {
		$course_progress = array();
	}

	if ( ( ! isset( $course_progress[ $course_id ] ) ) || ( empty( $course_progress[ $course_id ] ) ) ) {
		$course_progress[ $course_id ] = array(
			'lessons' => array(),
			'topics'  => array(),
		);
	}
	
	if ( ( ! isset( $course_progress[ $course_id ]['lessons'] ) ) || ( empty( $course_progress[ $course_id ]['lessons'] ) ) ) {
		$course_progress[ $course_id ]['lessons'] = array();
	}

	if ( ( ! isset( $course_progress[ $course_id ]['topics'] ) ) || ( empty( $course_progress[ $course_id ]['topics'] ) ) ) {
		$course_progress[ $course_id ]['topics'] = array();
	}
	
	if( $course_id ) {	
		$lessons = learndash_get_lesson_list( $course_id );
		
	 }
	 if( $lessons ) {	 
	 
		  foreach( $lessons as $less )  {
				$lesson_id = $less->ID; 
				if ( empty( $course_progress[ $course_id ]['lessons'][ $lesson_id ] ) ) {
					$course_progress[ $course_id ]['lessons'][ $lesson_id ] = 1;
					$lesson_completed   = true;
				}
				
				$topics = learndash_get_topic_list( $less->ID );
	
				if ( $topics ) {
				 
					foreach( $topics as $top ){
						if ( empty( $course_progress[ $course_id ]['topics'][ $lesson_id ][ $top->ID ] ) ) {
							$course_progress[ $course_id ]['topics'][ $lesson_id ][ $top->ID ] = 1;
							$topic_completed = true;
						}
					
					}
				}
				
		  
		  }
	  } 
	   
	  update_user_meta( $user_id, '_sfwd-course_progress', $course_progress );
	  learndash_process_mark_complete($user_id , $course_id );
	 //$completed = learndash_course_get_completed_steps( $user_id, $course_id, $course_progress[ $course_id ] );

	 /* $course_progress[ $course_id ]['completed'] = $completed;
	
	  
	  add_user_meta($user_id, 'course_completed_' . $course_id, time(), true );	
	 */ 
	 

} 

function aj_learndash_courses_mark_incomplete( $user_id , $course_id ) {
	
		$course_progress = get_user_meta( $user_id, '_sfwd-course_progress', true ); 
		
		if ( ( isset( $course_progress[ $course_id ] ) ) || ( !empty( $course_progress[ $course_id ] ) ) ) {
			$course_progress[ $course_id ] = array(
				'lessons' => array(),
				'topics'  => array(),
			);
		}
		
	   update_user_meta( $user_id, '_sfwd-course_progress', $course_progress );
	   delete_user_meta( $user_id , 'course_completed_' . $course_id );	
	   
	   learndash_process_mark_incomplete($user_id , $course_id );
		
}	

		
add_action('init' , 'create_table_for_content_tracking');
function create_table_for_content_tracking(){

	global $wpdb;

$charset_collate = $wpdb->get_charset_collate();
$table_name = $wpdb->prefix . 'lessontracker';
if($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
	$sql = "CREATE TABLE $table_name (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `user_id` int(11) NOT NULL,
	  `lesson_id` int(11) NOT NULL,
	  `lesson_type` int(11) NOT NULL,
	  `viewed` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	  `lesson_status` int(11) NOT NULL,
	  PRIMARY KEY  (id)
	) $charset_collate;";
	
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	dbDelta( $sql );
}
}		

add_filter('register_post_type_args', function($args, $post_type) {
if (!is_admin() && $post_type == 'page') {
$args['exclude_from_search'] = true;
}
return $args;
}, 10, 2);

//set defult image for any post type

 

add_filter( 'post_thumbnail_html', 'set_meltdefault_feature_image', 20, 5 );
function set_meltdefault_feature_image( $html, $post_id, $post_thumbnail_id, $size, $attr  ){
		if ( $post_thumbnail_id )
			return $html;
				
		$html = '<img  src="'.DEFAULT_IMG.'" alt="" class="default-img" />'  ;
		return 	$html;	
}

add_action('acf/include_field_types', 'include_field_types_sidebar_selector');
function include_field_types_sidebar_selector( $version ) {
	include_once('includes/acf_fields/acf-sidebar_field_type.php');
}


add_action('acf/register_fields', 'register_fields_sidebar_selector');
/**
 * ACF 4 Field
 *
 * Loads the field for ACF 4
 *
 * @author Daniel Pataki
 * @since 3.0.0
 *
 */
function register_fields_sidebar_selector() {
	include_once('includes/acf_fields/acf-sidebar_fileds.php');
}


add_action('template_redirect', 'my_disable_archives_function');

function my_disable_archives_function()
{
	global $smof_data;
	
	if ( is_post_type_archive( 'members_directory' ) &&  !$smof_data['enable_member_directory'])
  {
      global $wp_query;
      $wp_query->set_404();
  }
}



 add_filter( 'theme_page_templates', 'rrwd_remove_page_template' );
function rrwd_remove_page_template( $pages_templates ) {
global $smof_data;
	if( $smof_data['is_member_site'] ){
		return $pages_templates;
	}else{
		unset( $pages_templates['members-templates/member-dashboard.php'] );
		unset( $pages_templates['members-templates/member-default.php'] );
		unset( $pages_templates['members-templates/member-favourite-content.php'] );
		unset( $pages_templates['members-templates/member_login.php'] );
		unset( $pages_templates['members-templates/member_register.php'] );
		unset( $pages_templates['members-templates/member_support.php'] );
		unset( $pages_templates['members-templates/member_welcome.php'] );
        unset( $pages_templates['members-templates/member_ask.php'] );
	}	
		
   
    return $pages_templates;
}


function hide_course_from_feed( $query ) {

    if ( ! $query->is_main_query() || is_admin() )
        return;

    if ( is_post_type_archive('sfwd-courses') ) {
     $meta_query = (array)$query->get('meta_query');
     
	 $meta_query[] = array(
                    'key'=>'hide_from_feed',
                    'value'=> true,
                    'compare'=>'!=',
                );
	$query->set('meta_query',$meta_query);
	 $query->set('orderby', 'menu_order');

     $query->set('order', 'ASC');
    }
	
	
	 
	 return  $query;

  
}
add_action( 'pre_get_posts', 'hide_course_from_feed', 99 );


add_action('wp_ajax_aj_custom_ajax_logout', 'aj_custom_ajax_logout');
function aj_custom_ajax_logout(){
    check_ajax_referer( 'ajax-logout-nonce', 'ajaxsecurity' );
    wp_clear_auth_cookie();
    wp_logout();
    ob_clean(); // probably overkill for this, but good habit
    echo 'adios!!';
    wp_die();
}

add_action('wp_ajax_aj_road_mark_complete', 'aj_road_mark_complete');
function aj_road_mark_complete(){
   
   $steps = get_option('road_map_complete_step');
   
   if( empty ( $steps ) ){
   		$steps[] = $_POST['step'];
   
   }elseif( !in_array( $_POST['step'] , $steps ) ) {
   
   		$steps[] = $_POST['step'];
   
   }
   
   
   update_option('road_map_complete_step' , $steps );
   
   echo "done";
   die();
}
 
add_action('wp_ajax_aj_reset_road_map', 'aj_reset_road_map');
function aj_reset_road_map(){
    
   
   update_option('road_map_complete_step' ,array() );
   
   echo "done";
   die();
}

/*
 * Replacement for get_adjacent_post()
 *
 * This supports only the custom post types you identify and does not
 * look at categories anymore. This allows you to go from one custom post type
 * to another which was not possible with the default get_adjacent_post().
 * Orig: wp-includes/link-template.php 
 * 
 * @param string $direction: Can be either 'prev' or 'next'
 * @param multi $post_types: Can be a string or an array of strings
 */
function mod_get_adjacent_post($direction = 'prev', $post_types = 'post') {
    global $post, $wpdb;

    if(empty($post)) return NULL;
    if(!$post_types) return NULL;

    if(is_array($post_types)){
        $txt = '';
        for($i = 0; $i <= count($post_types) - 1; $i++){
            $txt .= "'".$post_types[$i]."'";
            if($i != count($post_types) - 1) $txt .= ', ';
        }
        $post_types = $txt;
    }

    $current_post_date = $post->post_date;

    $join = '';
    $in_same_cat = FALSE;
    $excluded_categories = '';
    $adjacent = $direction == 'prev' ? 'previous' : 'next';
    $op = $direction == 'prev' ? '<' : '>';
    $order = $direction == 'prev' ? 'DESC' : 'ASC';

    $join  = apply_filters( "get_{$adjacent}_post_join", $join, $in_same_cat, $excluded_categories );
    $where = apply_filters( "get_{$adjacent}_post_where", $wpdb->prepare("WHERE p.post_date $op %s AND p.post_type IN({$post_types}) AND p.post_status = 'publish'", $current_post_date), $in_same_cat, $excluded_categories );
    $sort  = apply_filters( "get_{$adjacent}_post_sort", "ORDER BY p.post_date $order LIMIT 1" );

    $query = "SELECT p.* FROM $wpdb->posts AS p $join $where $sort";
    $query_key = 'adjacent_post_' . md5($query);
    $result = wp_cache_get($query_key, 'counts');
    if ( false !== $result )
        return $result;

    $result = $wpdb->get_row("SELECT p.* FROM $wpdb->posts AS p $join $where $sort");
    if ( null === $result )
        $result = '';

    wp_cache_set($query_key, $result, 'counts');
    return $result;
}


// Register Custom Post Type
function register_questions_cpt() {

	$labels = array(
		'name'                  => _x( 'Questions', 'Post Type General Name', 'text_domain' ),
		'singular_name'         => _x( 'Question', 'Post Type Singular Name', 'text_domain' ),
		'menu_name'             => __( 'Questions', 'text_domain' ),
		'name_admin_bar'        => __( 'Questions', 'text_domain' ),
		'archives'              => __( 'Item Archives', 'text_domain' ),
		'attributes'            => __( 'Item Attributes', 'text_domain' ),
		'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
		'all_items'             => __( 'All Items', 'text_domain' ),
		'add_new_item'          => __( 'Add New Item', 'text_domain' ),
		'add_new'               => __( 'Add New', 'text_domain' ),
		'new_item'              => __( 'New Item', 'text_domain' ),
		'edit_item'             => __( 'Edit Item', 'text_domain' ),
		'update_item'           => __( 'Update Item', 'text_domain' ),
		'view_item'             => __( 'View Item', 'text_domain' ),
		'view_items'            => __( 'View Items', 'text_domain' ),
		'search_items'          => __( 'Search Item', 'text_domain' ),
		'not_found'             => __( 'Not found', 'text_domain' ),
		'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
		'featured_image'        => __( 'Featured Image', 'text_domain' ),
		'set_featured_image'    => __( 'Set featured image', 'text_domain' ),
		'remove_featured_image' => __( 'Remove featured image', 'text_domain' ),
		'use_featured_image'    => __( 'Use as featured image', 'text_domain' ),
		'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
		'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
		'items_list'            => __( 'Items list', 'text_domain' ),
		'items_list_navigation' => __( 'Items list navigation', 'text_domain' ),
		'filter_items_list'     => __( 'Filter items list', 'text_domain' ),
	);
	$args = array(
		'label'                 => __( 'Question', 'text_domain' ),
		'description'           => __( 'Post Type Description', 'text_domain' ),
		'labels'                => $labels,
		'supports'              => array( 'title', 'custom-fields' ),
		'hierarchical'          => false,
		'public'                => true,
		'show_ui'               => true,
		'show_in_menu'          => true,
		'menu_position'         => 7,
		'show_in_admin_bar'     => true,
		'show_in_nav_menus'     => true,
		'can_export'            => true,
		'has_archive'           => true,
		'exclude_from_search'   => false,
		'publicly_queryable'    => true,
		'capability_type'       => 'post',
	);
	global $smof_data;
	if( $smof_data['enable_ask_question'] )
		register_post_type( 'question', $args );

}
add_action( 'init', 'register_questions_cpt', 0 );


add_filter( 'gform_post_data', 'change_post_status', 10, 3 );
function change_post_status($post_data, $form, $entry){
    //only change post status on form id 5
global $smof_data;
	$formid = $smof_data['gravity_form_id'];

 if ( $form['id'] != $formid ) {
    return $post_data;
 }
 	
	$title = rgar( $entry, '5' );
	$post_data['post_type'] = 'question';
	$post_data['post_title'] = $title;
	$post_data['post_status'] = 'draft';

    return $post_data;
}

add_action( 'gform_after_create_post',   'save_custom_field' , 10, 3 );
function save_custom_field( $post_id, $entry, $form ) {
global $smof_data;
	$formid = $smof_data['gravity_form_id'];

 if ( $form['id'] != $formid ) {
    return $post_data;
 }
  update_post_meta( $post_id , 'user_name' , rgar( $entry, '1' )) ;
  
  update_post_meta( $post_id , 'user_email' , rgar( $entry, '2' )) ;
  
  update_post_meta( $post_id , 'topic' , rgar( $entry, '3' )) ;
  
}

//mail sent
 

function send_user_notification( $post_id, $post, $update ) {
		if ( wp_is_post_revision( $post_id ) ) {
			return;
        }
		$post_type = get_post_type($post_id);
	 	if ( "question" != $post_type ){
			return;
		} 
		 
			$user =  get_post_meta( $post_id , 'user_name' ,  true) ;
  			$user_email =  get_post_meta( $post_id , 'user_email' ,  true) ;
  			
			 $headers = array('Content-Type: text/html; charset=UTF-8');
			 
			 $subject = 'Your question has been answered.'; 
			 $message = " Hi, $user
			 
			 your question has been answered. 
			 
			 Visit this page to see the answer : 
			 <a href='".site_url()."/ask/' target='_blank'>".site_url()."/ask/ </a> 
			 \n\n
			 
			 Thanks \n\n
			 
			 ";
			 
		
		 	 wp_mail( $user_email, $subject, $message , $headers ) ;
		
		
		 
}
add_action( 'save_post', 'send_user_notification', 10, 3 );


add_action( 'wp_enqueue_scripts', 'load_dashicons_front_end' );
function load_dashicons_front_end() {
}

function replace_admin_menu_icons_css() {
    ?>
    <style>
      #adminmenu #menu-posts div.dashicons-admin-post::before {content: "\f119";}
      #adminmenu #menu-posts-content-library div.dashicons-admin-post::before {content: "\f331";}
      #adminmenu #menu-posts-question div.dashicons-admin-post::before {content: "\f205";}  
      #adminmenu #menu-posts-podcast div.dashicons-admin-post::before {content: "\f521";} 
      #adminmenu #menu-posts-members_directory div.dashicons-admin-post::before {content: "\f337";}   
    </style>
    <?php
}

add_action( 'admin_head', 'replace_admin_menu_icons_css' );


add_action('trashed_post' , 'trashed_post_course_type' , 20);
function trashed_post_course_type( $post_id  ){
		 if( get_post_type( $post_id  ) != 'sfwd-courses' )
	 		return;
	
	    global $wpdb;
  
    $sql = "DELETE FROM " . $wpdb->prefix . "lessontracker where `lesson_id`=" . $post_id;
	$wpdb->query( $sql );
			
}

/*-----------------------------------------------------------------------------------*/
/* END*/
/*-----------------------------------------------------------------------------------*/