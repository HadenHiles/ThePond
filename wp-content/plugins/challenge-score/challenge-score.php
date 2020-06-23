<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/HadenHiles
 * @author						Haden Hiles
 * @since             1.0.0
 * @package           Content_Library_Custom_Fields
 *
 * @wordpress-plugin
 * Plugin Name:       Challenge Score
 * Plugin URI:        challenge-score
 * Description:       Add user specific "challenge score" field to the content library category named "challenges"
 * Version:           1.0.0
 * Author:            Haden Hiles
 * Author URI:        https://github.com/HadenHiles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       challenge-score
 * Domain Path:       /languages
 */

function create_challenge_scores_table() {
    global $table_prefix, $wpdb;

    $tblname = 'challenge_scores';
    $wp_track_table = $table_prefix . "$tblname";

    #Check to see if the table exists already, if not, then create it
    if($wpdb->get_var( "show tables like '$wp_track_table'" ) != $wp_track_table) {
        $sql = "CREATE TABLE `". $wp_track_table . "` ( ";
        $sql .= "  `id` int(11) NOT NULL auto_increment, ";
        $sql .= "  `challenge_id` int(128) NOT NULL, ";
        $sql .= "  `user_id` int(128) NOT NULL, ";
        $sql .= "  `score` DECIMAL(8,4) NOT NULL, ";
        $sql .= "  PRIMARY KEY `id` (`id`) ";
        $sql .= ") ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ; ";
        require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
        dbDelta($sql);   
    }
}

register_activation_hook( __FILE__, 'create_challenge_scores_table' );

// Override the single content library template
add_filter( 'template_include', 'single_content_library' );
function single_content_library( $template )
{
    $template = plugin_dir_path( __FILE__ ) . 'single-content-library.php';
    return $template;
}

// Add ajax endpoint for adding a challenge score
add_action( 'wp_ajax_add_challenge_score', 'add_challenge_score' );
function add_challenge_score() {
    $challenge_id = $_POST['challenge_id'];
    $user_id = $_POST['user_id'];
    $score = $_POST['score'];

    global $wpdb;
    $table_name = $wpdb->prefix . "challenge_scores";
    $wpdb->insert(
        $table_name, 
        array(
            'challenge_id' => $challenge_id,
            'user_id' => $user_id,
            'score' => $score
        ),
        array(
            '%d',
            '%d',
            '%d'
        )
    );
}

 ?>
