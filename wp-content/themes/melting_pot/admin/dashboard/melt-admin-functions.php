<?php


/*-----------------------------------------------------------------------------------*/
/* MELT CUSTOM WORDPRESS STYLE & OPERATING FUNCTIONS */
/*-----------------------------------------------------------------------------------*/




function my_custom_login() {
echo '<link rel="stylesheet" type="text/css" href="' . get_bloginfo('stylesheet_directory') . '/admin/dashboard/melt-admin-styles.css" />';
}
add_action('login_head', 'my_custom_login');


/**REPLACE WP LOGO**/
function admin_css() {
echo '';
}

add_action('admin_head','admin_css');
/**END REPLACE WP LOGO**/


function my_login_logo_url() {
return get_bloginfo( 'url' );
}
add_filter( 'login_headerurl', 'my_login_logo_url' );

function my_login_logo_url_title() {
return get_bloginfo( 'name' );
}
add_filter( 'login_headertitle', 'my_login_logo_url_title' );


// Update CSS within in Admin
function admin_theme_style() {
    wp_register_style('custom-admin-style', get_template_directory_uri() . '/admin/dashboard/melt-admin-styles.css');
	wp_enqueue_style( 'custom-admin-style' );
}
add_action( 'admin_enqueue_scripts', 'admin_theme_style' ); 



function fb_move_admin_bar() {
    echo '
    <style type="text/css">
    #wpadminbar{
	   background: #384044;
	}
	#wpadminbar .ab-top-menu > li.hover > .ab-item, #wpadminbar.nojq .quicklinks .ab-top-menu > li > .ab-item:focus, #wpadminbar:not(.mobile) .ab-top-menu > li:hover > .ab-item, #wpadminbar:not(.mobile) .ab-top-menu > li > .ab-item:focus {
       background: #292F32;
       color: #36a9e1;
	}
	#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon{
	margin: 0!important;
}

#wpadminbar #wp-admin-bar-wp-logo > .ab-item .ab-icon::before {
    content: "";
    width: 100%;
    height: 20px;
    background-image: url(/wp-content/themes/melting_pot/admin/dashboard/images/melt-icon.png);
    background-repeat: no-repeat;
    background-size: contain;
    display: inline-block;
    margin: 0;
}

#wpadminbar #wp-admin-bar-wp-logo > .ab-item {
    padding: 0px 13px 0 13px;
    border-right: #36a9e1 solid 1px;
    border-bottom: solid 1px #36a9e1;
	text-align: center;
}
    </style>';
}
// remove the following line if you want to keep the admin bar at the top on the frontend
add_action( 'wp_head', 'fb_move_admin_bar' );


function remove_dashboard_widgets() {
    global $wp_meta_boxes;
    //unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
    //unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
    //unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']);
    //unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
    //unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
    //unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
    unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
    //unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
}
 
add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );


add_action( 'admin_bar_menu', 'wp_admin_bar_wp_menu_new', 11 );

function wp_admin_bar_wp_menu_new( $wp_admin_bar ) {

       $wp_admin_bar->remove_menu('wp-logo');

       $wp_admin_bar->add_menu( array(
               'id'         => 'wp-logo',
               'title'         => '<span class="ab-icon"></span>',
               'href'         => '/wp-admin',//replace this url with the one of your choice
               'meta'         => array(
                       'title' => __( 'About My Site' ),
               ),
       ) );
}




function register_melt_contact_dashboard_widget() {
 	global $wp_meta_boxes;

	wp_add_dashboard_widget(
		'melt_contact_dashboard_widget',
		'Let us manage and update your site for you…',
		'melt_contact_dashboard_widget_display'
	);

 	$dashboard = $wp_meta_boxes['dashboard']['normal']['core'];

	$my_widget = array( 'melt_contact_dashboard_widget' => $dashboard['melt_contact_dashboard_widget'] );
 	unset( $dashboard['melt_contact_dashboard_widget'] );

 	$sorted_dashboard = array_merge( $my_widget, $dashboard );
 	$wp_meta_boxes['dashboard']['normal']['core'] = $sorted_dashboard;
}
add_action( 'wp_dashboard_setup', 'register_melt_contact_dashboard_widget' );

function melt_contact_dashboard_widget_display() {
	?>

	<img src="/wp-content/themes/melt_default/admin/dashboard/images/logo-white.png" alt="Melt Design Logo" />

	<p>Protect your investment, boost your speed and brand and ward off those pesky hackers.</p>

	<p>We’ll even send you recommendations on how to improve your site to boost your conversions.</p>


	<h3>Get complete peace of mind with your website</h3>
	
	<a class="button-primary" href="https://meltdesign.co.uk/services/wordpress-management/" target="_blank" rel="nofollow">Take a look at our support plans</a>

	<?php
}



?>