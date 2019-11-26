<?php
/**
 * Update Compatibility
 *
 * @package CartFlows
 */

if ( ! class_exists( 'Cartflows_Pro_Update' ) ) :

	/**
	 * CartFlows Update initial setup
	 *
	 * @since 1.0.0
	 */
	class Cartflows_Pro_Update {

		/**
		 * Class instance.
		 *
		 * @access private
		 * @var $instance Class instance.
		 */
		private static $instance;

		/**
		 * Initiator
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 *  Constructor
		 */
		public function __construct() {
			add_action( 'admin_init', __CLASS__ . '::init' );
		}

		/**
		 * Init
		 *
		 * @since 1.0.0
		 * @return void
		 */
		public static function init() {

			do_action( 'cartflows_pro_update_before' );

			// Get auto saved version number.
			$saved_version = get_option( 'cartflows-pro-version', false );

			// Update auto saved version number.
			if ( ! $saved_version ) {
				update_option( 'cartflows-pro-version', CARTFLOWS_PRO_VER );
				return;
			}

			// If equals then return.
			if ( version_compare( $saved_version, CARTFLOWS_PRO_VER, '=' ) ) {
				return;
			}

			// Update to older version than 1.1.17 version.
			if ( version_compare( $saved_version, '1.1.17', '<' ) ) {
				self::v_1_1_17();
			}

			// Update to older version than 1.1.19 version.
			if ( version_compare( $saved_version, '1.1.19', '<' ) ) {
				self::v_1_1_19();
			}

			// Update auto saved version number.
			update_option( 'cartflows-pro-version', CARTFLOWS_PRO_VER );

			do_action( 'cartflows_pro_update_after' );
		}

		/**
		 * License update
		 *
		 * @since 1.1.16
		 * @return void
		 */
		public static function v_1_1_17() {

			// Update instance.
			$stored_instance = get_option( 'cartflows_instance', '' );
			update_option( 'wc_am_client_cartflows_instance', $stored_instance );

			// Update activation status.
			$stored_status = get_option( 'cartflows_activated', '' );
			update_option( 'wc_am_client_cartflows_activated', $stored_status );

			// Update license key.
			$stored_license_data = get_option(
				'cartflows_data',
				array(
					'api_key' => '',
				)
			);

			$new_license_data = array(
				'api_key' => $stored_license_data['api_key'],
			);

			update_option( 'wc_am_client_cartflows_api_key', $new_license_data );
		}

		/**
		 * License update
		 *
		 * @since 1.1.16
		 * @return void
		 */
		public static function v_1_1_19() {
			$defaults = array(
				'api_key' => '',
			);

			$stored = (array) get_option( 'wc_am_client_cartflows_api_key', array() );

			$old_data = wp_parse_args( $stored, $defaults );

			$new_data = array(
				'wc_am_client_cartflows_api_key' => $old_data['api_key'],
			);
			update_option( 'wc_am_client_cartflows', $new_data );
		}
	}

	/**
	 * Kicking this off by calling 'get_instance()' method
	 */
	Cartflows_Pro_Update::get_instance();

endif;
