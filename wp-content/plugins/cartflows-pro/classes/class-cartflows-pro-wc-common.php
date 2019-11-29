<?php
/**
 * WC Common.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Wc_Common.
 */
class Cartflows_Pro_Wc_Common {

	/**
	 * Member Variable
	 *
	 * @var instance
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
	 * Get Currency
	 *
	 * @param obj $order Order.
	 * @return string
	 */
	function get_currency( $order ) {

		return $order ? $order->get_currency() : get_woocommerce_currency();
	}

	/**
	 * Get Order ID
	 *
	 * @param obj $order Order.
	 * @return string
	 */
	function get_order_id( $order ) {

		return $order->get_id();
	}
}
