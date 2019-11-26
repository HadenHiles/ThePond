<?php
/**
 * Checkout
 *
 * @package CartFlows
 */

define( 'CARTFLOWS_PRO_CHECKOUT_DIR', CARTFLOWS_PRO_DIR . 'modules/checkout/' );
define( 'CARTFLOWS_PRO_CHECKOUT_URL', CARTFLOWS_PRO_URL . 'modules/checkout/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Checkout {


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
		require_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-checkout-markup.php';
		require_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-checkout-meta.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout::get_instance();
