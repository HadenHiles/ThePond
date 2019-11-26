<?php
/**
 * Utils.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Class Cartflows_Pro_Utils.
 */
class Cartflows_Pro_Utils {

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
	 * Fetch updated fragments after cart update.
	 *
	 * @return array
	 */
	public static function get_fragments() {

		ob_start();
		woocommerce_order_review();
		$woocommerce_order_review = ob_get_clean();

		return array(
			'cart_total' => WC()->cart->total,
			'fragments'  =>
			apply_filters(
				'woocommerce_update_order_review_fragments',
				array(
					'.woocommerce-checkout-review-order-table' => $woocommerce_order_review,

				)
			),

		);
	}



	/**
	 * Prepare response for facebook.
	 *
	 * @param integer $product_id product id.
	 * @return array
	 */
	public static function prepare_fb_response( $product_id ) {

		$response = array();
		$product  = wc_get_product( $product_id );

		$add_to_cart['content_type']       = 'product';
		$add_to_cart['content_category'][] = strip_tags( wc_get_product_category_list( $product->get_id() ) );
		$add_to_cart['currency']           = get_woocommerce_currency();
		$add_to_cart['language']           = get_bloginfo( 'language' );
		$add_to_cart['userAgent']          = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '';
		$add_to_cart['value']              = $product->get_price();
		$add_to_cart['content_name']       = $product->get_title();
		$add_to_cart['content_ids'][]      = $product->get_id();

		$response['added_to_cart'] = $add_to_cart;
		$response['current_cart']  = Cartflows_Helper::prepare_cart_data_fb_response();

		return $response;
	}

	/**
	 * Check is offer page
	 *
	 * @param int $step_id step ID.
	 * @return bool
	 */
	function check_is_offer_page( $step_id ) {

		$step_type = $this->get_step_type( $step_id );

		if ( 'upsell' === $step_type || 'downsell' === $step_type ) {

			return true;
		}

		return false;
	}

	/**
	 * Get offer data
	 *
	 * @param int $step_id step ID.
	 * @return array
	 */
	function get_offer_data( $step_id ) {

		$data = array();

		$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product' );

		if ( isset( $offer_product[0] ) ) {
			$product_id = $offer_product[0];
		}

		$product = wc_get_product( $product_id );

		if ( $product ) {

			$custom_price = $product->get_price( 'edit' );

			/* Product Quantity */
			$product_qty = intval( wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-quantity' ) );

			if ( $product_qty > 1 ) {
				$custom_price = $custom_price * $product_qty;
			}

			/* Offer Discount */
			$discount_type = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-discount' );

			if ( ! empty( $discount_type ) ) {

				$discount_value = intval( wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-discount-value' ) );

				if ( 'discount_percent' === $discount_type ) {

					if ( $discount_value > 0 ) {
						$custom_price = $custom_price - ( ( $custom_price * $discount_value ) / 100 );
					}
				} elseif ( 'discount_price' === $discount_type ) {

					if ( $discount_value > 0 ) {
						$custom_price = $custom_price - $discount_value;
					}
				}
			}

			/* Set Product Price */
			$product_price = $custom_price;

			$tax_enabled = get_option( 'woocommerce_calc_taxes' );

			// If tax rates are enabled.
			if ( 'yes' === $tax_enabled ) {

				// Price excluding tax.
				if ( wc_prices_include_tax() ) {
					$product_price = wc_get_price_excluding_tax( $product, array( 'price' => $custom_price ) );
				} else {
					$custom_price = wc_get_price_including_tax( $product, array( 'price' => $custom_price ) );
				}
			}

			$product_price = apply_filters( 'wcf_offer_product_price', $product_price );
			$custom_price  = apply_filters( 'wcf_offer_custom_price', $custom_price );

			$data = array(
				'step_id' => $step_id,
				'id'      => $product_id,
				'name'    => $product->get_title(),
				'desc'    => $product->get_description(),
				'qty'     => $product_qty,
				'args'    => array(
					'total' => $product_price,
				),
				'price'   => $custom_price,
				'url'     => $product->get_permalink(),
				'total'   => $custom_price,
			);
		}

		return $data;
	}

	/**
	 * Check if reference transaction for paypal is enabled.
	 *
	 * @return bool
	 */
	function is_reference_transaction() {

		$settings = Cartflows_Helper::get_common_settings();

		if ( isset( $settings['paypal_reference_transactions'] ) && 'enable' == $settings['paypal_reference_transactions'] ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if offered product has zero value.
	 *
	 * @return bool
	 */
	function is_zero_value_offered_product() {
		global $post;
		$step_id       = $post->ID;
		$offer_product = $this->get_offer_data( $step_id );
		if ( array_key_exists( 'total', $offer_product ) && 0 != $offer_product['total'] ) {
			return false;
		}
		return true;
	}

	/**
	 * Get assets urls
	 *
	 * @return array
	 * @since 1.1.6
	 */
	function get_assets_path() {

		$rtl = '';

		if ( is_rtl() ) {
			$rtl = '-rtl';
		}

		$file_prefix = '';
		$dir_name    = '';

		$is_min = apply_filters( 'cartflows_load_min_assets', false );

		if ( $is_min ) {
			$file_prefix = '.min';
			$dir_name    = 'min-';
		}

		$js_gen_path  = CARTFLOWS_PRO_URL . 'assets/' . $dir_name . 'js/';
		$css_gen_path = CARTFLOWS_PRO_URL . 'assets/' . $dir_name . 'css/';

		return array(
			'css'         => $css_gen_path,
			'js'          => $js_gen_path,
			'file_prefix' => $file_prefix,
			'rtl'         => $rtl,
		);
	}

	/**
	 * Get assets css url
	 *
	 * @param string $file file name.
	 * @return string
	 * @since 1.1.6
	 */
	function get_css_url( $file ) {

		$assets_vars = wcf_pro()->assets_vars;

		$url = $assets_vars['css'] . $file . $assets_vars['rtl'] . $assets_vars['file_prefix'] . '.css';

		return $url;
	}

	/**
	 * Get assets js url
	 *
	 * @param string $file file name.
	 * @return string
	 * @since 1.1.6
	 */
	function get_js_url( $file ) {

		$assets_vars = wcf_pro()->assets_vars;

		$url = $assets_vars['js'] . $file . $assets_vars['file_prefix'] . '.js';

		return $url;
	}

}
