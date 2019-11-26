<?php
/**
 * Plugin Name:           GamiPress - LearnDash integration
 * Plugin URI:            https://wordpress.org/plugins/gamipress-learndash-integration/
 * Description:           Connect GamiPress with LearnDash.
 * Version:               1.1.6
 * Author:                GamiPress
 * Author URI:            https://gamipress.com/
 * Text Domain:           gamipress-learndash-integration
 * Domain Path:           /languages/
 * Requires at least:     4.4
 * Tested up to:          5.3
 * License:               GNU AGPL v3.0 (http://www.gnu.org/licenses/agpl.txt)
 *
 * @package               GamiPress\LearnDash
 * @author                GamiPress
 * @copyright             Copyright (c) GamiPress
 */

final class GamiPress_LearnDash {

    /**
     * @var         GamiPress_LearnDash $instance The one true GamiPress_LearnDash
     * @since       1.0.0
     */
    private static $instance;

    /**
     * Get active instance
     *
     * @access      public
     * @since       1.0.0
     * @return      object self::$instance The one true GamiPress_LearnDash
     */
    public static function instance() {

        if( !self::$instance ) {
            self::$instance = new GamiPress_LearnDash();
            self::$instance->constants();
            self::$instance->includes();
            self::$instance->hooks();
            self::$instance->load_textdomain();
        }

        return self::$instance;
    }

    /**
     * Setup plugin constants
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function constants() {
        // Plugin version
        define( 'GAMIPRESS_LD_VER', '1.1.6' );

        // Plugin path
        define( 'GAMIPRESS_LD_DIR', plugin_dir_path( __FILE__ ) );

        // Plugin URL
        define( 'GAMIPRESS_LD_URL', plugin_dir_url( __FILE__ ) );
    }

    /**
     * Include plugin files
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function includes() {

        if( $this->meets_requirements() ) {

            require_once GAMIPRESS_LD_DIR . 'includes/admin.php';
            require_once GAMIPRESS_LD_DIR . 'includes/listeners.php';
            require_once GAMIPRESS_LD_DIR . 'includes/requirements.php';
            require_once GAMIPRESS_LD_DIR . 'includes/rules-engine.php';
            require_once GAMIPRESS_LD_DIR . 'includes/scripts.php';
            require_once GAMIPRESS_LD_DIR . 'includes/triggers.php';

        }
    }

    /**
     * Setup plugin hooks
     *
     * @access      private
     * @since       1.0.0
     * @return      void
     */
    private function hooks() {
        // Setup our activation and deactivation hooks
        register_activation_hook( __FILE__, array( $this, 'activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

        add_action( 'admin_notices', array( $this, 'admin_notices' ) );
    }

    /**
     * Activation hook for the plugin.
     *
     * @since  1.0.0
     */
    function activate() {

        if( $this->meets_requirements() ) {

        }

    }

    /**
     * Deactivation hook for the plugin.
     *
     * @since  1.0.0
     */
    function deactivate() {

    }

    /**
     * Plugin admin notices.
     *
     * @since  1.0.0
     */
    public function admin_notices() {

        if ( ! $this->meets_requirements() && ! defined( 'GAMIPRESS_ADMIN_NOTICES' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'GamiPress - LearnDash integration requires %s and %s in order to work. Please install and activate them.', 'gamipress-learndash-integration' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress</a>',
                        '<a href="http://www.learndash.com" target="_blank">LearnDash</a>'
                    ); ?>
                </p>
            </div>

            <?php define( 'GAMIPRESS_ADMIN_NOTICES', true ); ?>

        <?php elseif ( is_multisite() && version_compare( GAMIPRESS_VER, '1.4.8', '<' ) ) : ?>

            <div id="message" class="notice notice-error is-dismissible">
                <p>
                    <?php printf(
                        __( 'GamiPress - LearnDash integration requires %s or higher in order to enable multisite features. Please update your GamiPress install.', 'gamipress-learndash-integration' ),
                        '<a href="https://wordpress.org/plugins/gamipress/" target="_blank">GamiPress 1.4.8</a>'
                    ); ?>
                </p>
            </div>

        <?php endif;

    }

    /**
     * Check if there are all plugin requirements
     *
     * @since  1.0.0
     *
     * @return bool True if installation meets all requirements
     */
    private function meets_requirements() {

        if ( ! class_exists( 'GamiPress' ) ) {
            return false;
        }

        // Multisite feature requires GamiPress 1.4.8
        if( is_multisite() && version_compare( GAMIPRESS_VER, '1.4.8', '>=' ) ) {

            // Requirements on multisite install
            if( gamipress_is_network_wide_active() && is_main_site() ) {

                // On main site, need to check if integrated plugin is installed on any sub site to load all configuration files
                if( gamipress_is_plugin_active_on_network( 'sfwd-lms/sfwd_lms.php' ) ) {
                    return true;
                }

            }

        }

        if ( ! class_exists( 'SFWD_LMS' ) ) {
            return false;
        }

        return true;

    }

    /**
     * Internationalization
     *
     * @access      public
     * @since       1.0.0
     * @return      void
     */
    public function load_textdomain() {

        // Set filter for language directory
        $lang_dir = GAMIPRESS_LD_DIR . '/languages/';
        $lang_dir = apply_filters( 'gamipress_learndash_languages_directory', $lang_dir );

        // Traditional WordPress plugin locale filter
        $locale = apply_filters( 'plugin_locale', get_locale(), 'gamipress-learndash-integration' );
        $mofile = sprintf( '%1$s-%2$s.mo', 'gamipress-learndash-integration', $locale );

        // Setup paths to current locale file
        $mofile_local   = $lang_dir . $mofile;
        $mofile_global  = WP_LANG_DIR . '/gamipress-learndash-integration/' . $mofile;

        if( file_exists( $mofile_global ) ) {
            // Look in global /wp-content/languages/gamipress-learndash-integration/ folder
            load_textdomain( 'gamipress-learndash-integration', $mofile_global );
        } elseif( file_exists( $mofile_local ) ) {
            // Look in local /wp-content/plugins/gamipress-learndash-integration/languages/ folder
            load_textdomain( 'gamipress-learndash-integration', $mofile_local );
        } else {
            // Load the default language files
            load_plugin_textdomain( 'gamipress-learndash-integration', false, $lang_dir );
        }

    }

}

/**
 * The main function responsible for returning the one true GamiPress_LearnDash instance to functions everywhere
 *
 * @since       1.0.0
 * @return      \GamiPress_LearnDash The one true GamiPress_LearnDash
 */
function GamiPress_LD() {
    return GamiPress_LearnDash::instance();
}
add_action( 'plugins_loaded', 'GamiPress_LD' );
