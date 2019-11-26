<?php
/**
 * Upsell meta.
 *
 * @package cartflows
 */

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Upsell_Meta extends Cartflows_Pro_Base_Offer_Meta {


	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Meta Option
	 *
	 * @var $meta_option
	 */
	private static $meta_option = null;

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
	 * Constructor
	 */
	public function __construct() {

	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Upsell_Meta::get_instance();
