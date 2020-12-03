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
 * Plugin Name:       Facebook Group Secret Phrase
 * Plugin URI:        facebook-group-secret
 * Description:       Generate secret phrases for granting access to The Pond members only facebook group
 * Version:           1.0.0
 * Author:            Haden Hiles
 * Author URI:        https://github.com/HadenHiles
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       facebook-group-secret
 * Domain Path:       /languages
 */

define('FB_GROUP_SECRET_PLUGIN_FILE_URL', __FILE__);

require_once('hooks.php');
require_once('ajax.php');

/* Admin menu */
add_action('admin_menu', 'facebook_group_secret_menu');
function facebook_group_secret_menu() {
    $page_title = 'Facebook Group Member Validation';
    $menu_title = 'Group Validation';
    $capability = 'manage_options';
    $menu_slug  = 'facebook-group-member-validation';
    $function   = 'facebook_group_secret_page';
    $icon_url   = 'dashicons-facebook';
    $position   = 4;

    add_menu_page($page_title, $menu_title, $capability, $menu_slug, $function, $icon_url, $position);
}

/* Admin page */
if (!function_exists("facebook_group_secret_page")) {
    function facebook_group_secret_page() {
?>
        <div style="max-width: 600px; margin: 50px auto; text-align: center;">
            <h2>Facebook Group - Member Validation</h2>

            <form id="member-validation">
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Email:</th>
                        <td><input type="text" name="email" id="email" value="" /></td>
                    </tr>
                    <tr>
                        <th scope="row">Phrase:</th>
                        <td><input type="text" name="phrase" id="phrase" value="" /></td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" name="validate" id="validate" class="button button-primary" value="Validate" />
                    <p id="valid-icon" style="display: none; color: green; text-align: left;">Valid</p>
                    <p id="invalid-icon" style="display: none; color: #cc3333; text-align: left;">Invalid</p>
                </p>
            </form>
            <script type="text/javascript">
                (($) => {
                    $('#member-validation').submit((e) => {
                        e.preventDefault();

                        var email = $('#email').val();
                        var phrase = $('#phrase').val();

                        if (email != "" && phrase != "") {
                            $('#validate').attr('disabled', true).addClass('disabled');

                            var valid = false;

                            var data = {
                                action: 'validate_facebook_group_phrase',
                                email: email,
                                phrase: phrase
                            };

                            $.ajax({
                                url: ajaxurl, // this will point to admin-ajax.php
                                type: 'POST',
                                data: data,
                                success: function(response) {
                                    valid = response.data != null ? response.data.valid : false;

                                    if (valid) {
                                        $('#valid-icon').show();
                                        $('#invalid-icon').hide();
                                    } else {
                                        $('#invalid-icon').show();
                                        $('#valid-icon').hide();
                                    }
                                },
                                complete: function() {
                                    setTimeout(() => {
                                        $('#valid-icon').hide();
                                        $('#invalid-icon').hide();
                                        $('#validate').attr('disabled', false).removeClass('disabled');
                                    }, 2000);
                                }
                            });
                        }
                    });
                })(jQuery);
            </script>
        </div>
<?php
    }
}
?>