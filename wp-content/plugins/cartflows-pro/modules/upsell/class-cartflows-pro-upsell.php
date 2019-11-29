<?php
/**
 * Upsell
 *
 * @package cartflows
 */

define( 'CARTFLOWS_PRO_UPSELL_DIR', CARTFLOWS_PRO_DIR . 'modules/upsell/' );
define( 'CARTFLOWS_PRO_UPSELL_URL', CARTFLOWS_URL . 'modules/upsell/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Upsell {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 *  Initiator
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		require_once CARTFLOWS_PRO_UPSELL_DIR . 'classes/class-cartflows-pro-upsell-meta.php';
		require_once CARTFLOWS_PRO_UPSELL_DIR . 'classes/class-cartflows-pro-upsell-markup.php';

		if ( class_exists( 'WC_Subscriptions' ) ) {
			require_once CARTFLOWS_PRO_UPSELL_DIR . 'classes/class-cartflows-pro-offer-subscriptions.php';
		}
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Upsell::get_instance();
