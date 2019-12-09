<?php
/**
 * WOO Multicurrency.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Utils.
 */
class Cartflows_Pro_Woo_Multicurrency {

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
	 * Constructor
	 */
	public function __construct() {
		add_filter( 'wcf_offer_custom_price', array( $this, 'wcf_modify_product_price' ), 10, 1 );
		add_filter( 'wcf_offer_product_price', array( $this, 'wcf_modify_product_price' ), 10, 1 );

	}

	/**
	 * Price Converter
	 *
	 * @param int $product_price product price.
	 * @return int
	 */
	public function wcf_modify_product_price( $product_price ) {
		$api_obj       = new WOOMC\MultiCurrency\API();
		$product_price = $api_obj->convert( $product_price, get_woocommerce_currency(), get_option( 'woocommerce_currency' ) );
		return $product_price;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Frontend' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Woo_Multicurrency::get_instance();

