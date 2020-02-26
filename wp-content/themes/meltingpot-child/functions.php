<?php
/**
* Fix sql strict mode
*/
add_action( 'init', 'mysql_set_sql_mode_traditional', -1);

function mysql_set_sql_mode_traditional() {
    global $wpdb;
    $wpdb->query("SET SESSION sql_mode = ''");
}

/**
* Manually trigger supervisord hook running on thepond DO droplet any time that elementor data is updated
* - this is required so that styles will be applied
* TODO: figure out a way to automatically expire the cache of modified files
*/
add_action( 'elementor/editor/after_save', function() {
  // Curl code pulled from postman test
  $curl = curl_init();

  curl_setopt_array($curl, array(
    CURLOPT_URL => "http://178.128.232.27:9000/hooks/updatecdn",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => "",
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => "POST",
  ));

  $response = curl_exec($curl);

  curl_close($curl);
});

add_action( 'wp_enqueue_scripts', 'child_theme_enqueue_styles' );
function child_theme_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    wp_enqueue_style( 'style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
        wp_get_theme()->get('Version')
    );
}

/**
 * Join posts and postmeta tables
 *
 * http://codex.wordpress.org/Plugin_API/Filter_Reference/posts_join
 */
function cf_search_join( $join ) {
    global $wpdb;

    if ( is_search() ) {
        $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
    }

    return $join;
}
add_filter('posts_join', 'cf_search_join' );

function cf_search_where( $where ) {
    global $pagenow, $wpdb;

    if ( is_search() ) {
        $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_value LIKE $1)", $where );
    }

    return $where;
}
add_filter( 'posts_where', 'cf_search_where' );

function cf_search_distinct( $where ) {
    global $wpdb;

    if ( is_search() ) {
        return "DISTINCT";
    }

    return $where;
}
add_filter( 'posts_distinct', 'cf_search_distinct' );

/* Include shortcodes */
require_once('shortcodes.php');

/* Include scripts */
require_once('scripts.php');
