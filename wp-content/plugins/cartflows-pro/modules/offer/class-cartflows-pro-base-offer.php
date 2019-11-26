<?php
/**
 * Offer
 *
 * @package cartflows
 */

define( 'CARTFLOWS_PRO_BASE_OFFER_DIR', CARTFLOWS_PRO_DIR . 'modules/offer/' );

/**
 * Initial Setup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Base_Offer {


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
		require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-base-offer-meta.php';
		require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-base-offer-shortcodes.php';
		require_once CARTFLOWS_PRO_BASE_OFFER_DIR . 'classes/class-cartflows-pro-base-offer-markup.php';
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Base_Offer::get_instance();
