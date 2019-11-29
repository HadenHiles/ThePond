<?php
/**
 * Payment Gateway class.
 *
 * @package cartflows
 */

/**
 * Class Cartflows_Pro_Gateway.
 */
class Cartflows_Pro_Gateway extends Cartflows_Pro_Api_Base {

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

		$this->load_files();
	}

	/**
	 * Get value from response variable by key.
	 *
	 * @since 1.0.0
	 *
	 * @param array  $response response data.
	 * @param string $key array key item.
	 *
	 * @return string
	 */
	public function get_value_from_response( $response, $key ) {

		if ( $response && isset( $response[ $key ] ) ) {

			return $response[ $key ];
		}
	}

	/**
	 * Format prices.
	 *
	 * @since 1.0.0
	 *
	 * @param float|int $price product price.
	 * @param int       $decimals The number of decimal points.
	 *
	 * @return string
	 */
	public function price_format( $price, $decimals = 2 ) {
		return number_format( $price, $decimals, '.', '' );
	}

	/**
	 * Round a float
	 *
	 * @since 1.0.0
	 *
	 * @param float $number number to round.
	 * @param int   $precision Optional. The number of decimal digits to round to.
	 */
	public function round( $number, $precision = 2 ) {
		return round( (float) $number, $precision );
	}

	/**
	 * Helper method to get item description by replacing all tags and limiting to 127 characters
	 *
	 * @param array       $item cart or order item.
	 * @param \WC_Product $product product data.
	 * @param bool        $is_offer has offer?.
	 *
	 * @return string
	 * @since 1.0.0
	 */
	public function get_item_description( $item, $product, $is_offer = false ) {

		$item_desc = wp_strip_all_tags( wp_staticize_emoji( $product->get_short_description() ) );
		$item_desc = str_replace( "\n", ', ', rtrim( $item_desc ) );
		if ( strlen( $item_desc ) > 127 ) {
			$item_desc = substr( $item_desc, 0, 124 ) . '...';
		}

		return html_entity_decode( $item_desc, ENT_NOQUOTES, 'UTF-8' );
	}

	/**
	 * Load helper files
	 */
	public function load_files() {

		include_once CARTFLOWS_PRO_DIR . 'modules/gateways/class-cartflows-pro-paypal-gateway-helper.php';
	}
}

Cartflows_Pro_Gateway::get_instance();
