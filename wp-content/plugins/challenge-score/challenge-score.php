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

define('PLUGIN_FILE_URL', __FILE__);

require_once('hooks.php');
require_once('actions.php');
require_once('filters.php');
require_once('enqueue.php');
?>
