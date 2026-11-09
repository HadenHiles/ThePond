<?php
/**
 * @package BuddyBoss Child
 * The parent theme functions are located at /buddyboss-theme/inc/theme/functions.php
 * Add your own functions at the bottom of this file.
 */


/****************************** THEME SETUP ******************************/

/**
 * Sets up theme for translation
 *
 * @since BuddyBoss Child 1.0.0
 */
function buddyboss_theme_child_languages()
{
  /**
   * Makes child theme available for translation.
   * Translations can be added into the /languages/ directory.
   */

  // Translate text from the PARENT theme.
  load_theme_textdomain( 'buddyboss-theme', get_stylesheet_directory() . '/languages' );

  // Translate text from the CHILD theme only.
  // Change 'buddyboss-theme' instances in all child theme files to 'buddyboss-theme-child'.
  // load_theme_textdomain( 'buddyboss-theme-child', get_stylesheet_directory() . '/languages' );

}
add_action( 'after_setup_theme', 'buddyboss_theme_child_languages' );

/**
 * Enqueues scripts and styles for child theme front-end.
 *
 * @since Boss Child Theme  1.0.0
 */
function buddyboss_theme_child_scripts_styles()
{
  /**
   * Scripts and Styles loaded by the parent theme can be unloaded if needed
   * using wp_deregister_script or wp_deregister_style.
   *
   * See the WordPress Codex for more information about those functions:
   * http://codex.wordpress.org/Function_Reference/wp_deregister_script
   * http://codex.wordpress.org/Function_Reference/wp_deregister_style
   **/

  // Styles
  wp_enqueue_style( 'buddyboss-child-css', get_stylesheet_directory_uri().'/assets/css/custom.css', pond_theme_is_legacy_page() ? array('pond-legacy', 'pond-bootstrap') : array(), filemtime(get_stylesheet_directory().'/assets/css/custom.css') );

  // Javascript
  wp_enqueue_script( 'buddyboss-child-js', get_stylesheet_directory_uri().'/assets/js/custom.js', array('jquery'), filemtime(get_stylesheet_directory().'/assets/js/custom.js'), true );

  if ( wp_script_is( 'mepr-zxcvbn', 'registered' ) && class_exists( 'MeprZxcvbnCtrl' ) ) {
    $required_text = MeprZxcvbnCtrl::get_required_str();
    $minimum_length = defined( 'THEPOND_FIREBASE_MIN_PASSWORD' ) ? THEPOND_FIREBASE_MIN_PASSWORD : 0;
    wp_enqueue_script( 'pond-signup-password', get_stylesheet_directory_uri().'/assets/js/signup-password.js', array('jquery', 'mepr-zxcvbn'), filemtime(get_stylesheet_directory().'/assets/js/signup-password.js'), true );
    wp_localize_script( 'pond-signup-password', 'pondSignupPassword', array(
      'requiredScore' => MeprZxcvbnCtrl::get_required_int(),
      'minLength' => $minimum_length,
      'guidance' => ( $required_text ? $required_text . '. ' : '' ) .
        ( $minimum_length ? sprintf( __( 'Use at least %d characters. ', 'buddyboss-theme-child' ), $minimum_length ) : '' ) .
        __( 'Try a long passphrase with several unrelated words. Avoid common phrases and personal information.', 'buddyboss-theme-child' ),
      'empty' => __( 'Enter a password to check it.', 'buddyboss-theme-child' ),
      'checking' => __( 'Checking password strength...', 'buddyboss-theme-child' ),
      'tooWeak' => __( 'Not ready: choose a stronger password.', 'buddyboss-theme-child' ),
      'tooShort' => sprintf( __( 'Not ready: use at least %d characters.', 'buddyboss-theme-child' ), $minimum_length ),
      'accepted' => __( 'Meets password requirements.', 'buddyboss-theme-child' ),
    ) );
  }
}
add_action( 'wp_enqueue_scripts', 'buddyboss_theme_child_scripts_styles', 9999 );


/****************************** CUSTOM FUNCTIONS ******************************/

require_once get_stylesheet_directory() . '/inc/pond.php';
require_once get_stylesheet_directory() . '/inc/seo.php';