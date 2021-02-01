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
 * @author			  Haden Hiles
 * @since             1.0.0
 * @package           Content_Library_Custom_Fields
 *
 * @wordpress-plugin
 * Plugin Name:       The Pond Player Profile
 * Plugin URI:        player-profile
 * Description:       Display the user's player profile information (player card, skills progressions, challenges, etc.)
 * Version:           1.0.0
 * Author:            Haden Hiles
 * Author URI:        https://github.com/HadenHiles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       player-profile
 * Domain Path:       /languages
 */

add_shortcode('player_profile', 'player_profile');
function player_profile($atts = [], $content = null, $tag = '') {
    $user = get_user_by('id', get_current_user_id());
    $mpUser = new MeprUser($user->ID);
?>
    <div class="player-card" id="player-card">
        <div class="top-half">
            <div class="overall-rating rookie">
                <div class="inner-ribbon">
                    <p class="level">Rookie</p>
                    <h2 class="rating">87</h2>
                </div>
            </div>
            <div class="headshot" 
            style=" background-image: url('/wp-content/plugins/player-profile/images/bronze-bg.png');
                    background-size: 110% 100%;
                    background-position: -20px 0px;
                    background-repeat: no-repeat;">
                <?=do_shortcode('[avatar size="large"]') ?>
            </div>
        </div>
        <div class="bottom-half">
            <div class="stats">
                <h1 class="name">
                    <span class="first"><?= $mpUser->first_name ?></span>
                    <span class="last"><?= $mpUser->last_name ?></span>
                </h1>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        (($) => {})(jQuery);
    </script>
<?php
}

// Override the Learndash Profile Template so we can display the secret phrase code in the member dashboard
function replace_learndash_profile_template($filepath, $name, $args, $echo, $return_file_path) {
    if ($name == 'profile') {
        $filepath = plugin_dir_path(__FILE__) . 'learndash/profile.php';
    }

    return $filepath;
}
add_filter('learndash_template', 'replace_learndash_profile_template', 90, 5);

require_once('scripts.php');
?>