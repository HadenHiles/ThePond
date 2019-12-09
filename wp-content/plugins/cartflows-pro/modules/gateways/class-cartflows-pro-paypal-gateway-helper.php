<?php
/**
 * Cod Gateway helper functions.
 *
 * @package cartflows
 */

/**
 * Class Cartflows_Pro_Paypal_Gateway_Helper .
 */
class Cartflows_Pro_Paypal_Gateway_Helper extends Cartflows_Pro_Gateway {

	/**
	 * Live API URL.
	 *
	 * @var live_api
	 */
	public $live_api = 'https://api-3t.paypal.com/nvp';

	/**
	 * Test API URL.
	 *
	 * @var test_api
	 */
	public $test_api = 'https://api-3t.sandbox.paypal.com/nvp';

	/**
	 * Gateway parameters.
	 *
	 * @var parameters
	 */
	public $parameters = array();

	/**
	 * Add single parameter.
	 *
	 * @param string $key key.
	 * @param string $value value.
	 */
	public function add_parameter( $key, $value ) {
		$this->parameters[ $key ] = $value;
	}

	/**
	 * Add multiple parameters.
	 *
	 * @param array $params parameters.
	 */
	public function add_parameters( array $params ) {
		foreach ( $params as $key => $value ) {
			$this->add_parameter( $key, $value );
		}
	}

	/**
	 * Add payment parameters
	 *
	 * @param array $params parameters.
	 *
	 * @since 1.0.0
	 */
	private function add_payment_parameters( array $params ) {
		foreach ( $params as $key => $value ) {
			$this->add_parameter( "PAYMENTREQUEST_0_{$key}", $value );
		}
	}

	/**
	 * Set the method for request
	 *
	 * @param string $method request method param.
	 *
	 * @since 1.0.0
	 */
	public function set_method( $method ) {
		$this->add_parameter( 'METHOD', $method );
	}

	/**
	 * Update payment params
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order order object.
	 * @param int      $step_id step ID.
	 * @param string   $type transaction type.
	 * @param bool     $use_deprecated_params whether to use deprecated PayPal?.
	 * @param bool     $is_offer_charge is offer charge.
	 */
	public function add_payment_params( WC_Order $order, $step_id, $type, $use_deprecated_params = false, $is_offer_charge = false ) {

		$calculated_total = 0;
		$order_subtotal   = 0;
		$item_count       = 0;
		$order_items      = array();
		$offer_package    = array();

		if ( true === $is_offer_charge ) {

			$offer_package = wcf_pro()->utils->get_offer_data( $step_id );

			if ( $offer_package ) {

				$order_items[] = array(
					'NAME'    => $offer_package['name'],
					'DESC'    => $offer_package['desc'],
					'AMT'     => $this->round( $offer_package['price'] ),
					'QTY'     => 1,
					'ITEMURL' => $offer_package['url'],
				);

				$order_subtotal += $offer_package['total'];
			}
		} else {

			// Add line items.
			foreach ( $order->get_items() as $item ) {

				$product = new WC_Product( $item['product_id'] );

				$order_items[] = array(
					'NAME'    => $product->get_title(),
					'DESC'    => $this->get_item_description( $item, $product, $is_offer_charge ),
					'AMT'     => $this->round( $order->get_item_subtotal( $item ) ),
					'QTY'     => ( ! empty( $item['qty'] ) ) ? absint( $item['qty'] ) : 1,
					'ITEMURL' => $product->get_permalink(),
				);

				$order_subtotal += $item['line_total'];
			}

			foreach ( $order->get_fees() as $fee ) {

				$order_items[] = array(
					'NAME' => ( $fee['name'] ),
					'AMT'  => $this->round( $fee['line_total'] ),
					'QTY'  => 1,
				);

				$order_subtotal += $fee['line_total'];
			}

			if ( $order->get_total_discount() > 0 ) {

				$order_items[] = array(
					'NAME' => __( 'Total Discount', 'cartflows-pro' ),
					'QTY'  => 1,
					'AMT'  => - $this->round( $order->get_total_discount() ),
				);
			}
		}

		if ( true === $is_offer_charge ) {

			/** Handle paypal data setup for the offers */

			/**
			 * Code for reference transaction
			 */
			$total_amount = $offer_package['total'];

			$item_names = array();

			foreach ( $order_items as $item ) {
				$item_names[] = sprintf( '%1$s x %2$s', $item['NAME'], $item['QTY'] );
			}
			$item_count = 0;

			// Add individual order items.
			foreach ( $order_items as $item ) {
				$this->add_litem_params( $item, $item_count ++, $use_deprecated_params );
			}

			$this->add_parameters(
				array(
					'AMT'              => $total_amount,
					'CURRENCYCODE'     => wcf_pro()->wc_common->get_currency( $order ),
					'ITEMAMT'          => $this->round( $order_subtotal ),
					'SHIPPINGAMT'      => 0,
					'INVNUM'           => $this->wc_gateway()->get_option( 'invoice_prefix' ) . wcf_pro()->wc_common->get_order_id( $order ) . '_' . $offer_package['step_id'],
					'PAYMENTACTION'    => $type,
					'PAYMENTREQUESTID' => wcf_pro()->wc_common->get_order_id( $order ),
					'TAXAMT'           => 0,
					'CUSTOM'           => json_encode(
						array(
							'_wcf_order_id' => wcf_pro()->wc_common->get_order_id( $order ),
						)
					),
				)
			);

		} else {

			/** Do things for the main order */
			if ( $this->skip_line_items( $order, $order_items ) ) {

				$total_amount = $this->round( $order->get_total() );

				// Calculate the total.
				$calculated_total += $this->round( $order_subtotal + $order->get_cart_tax() ) + $this->round( $order->get_total_shipping() + $order->get_shipping_tax() );

				// Calculate order subtotal.
				if ( $this->price_format( $total_amount ) !== $this->price_format( $calculated_total ) ) {
					$order_subtotal = $order_subtotal - ( $calculated_total - $total_amount );
				}

				$item_names = array();

				foreach ( $order_items as $item ) {
					$item_names[] = sprintf( '%1$s x %2$s', $item['NAME'], $item['QTY'] );
				}

				// Add line item paramter.
				$this->add_litem_params(
					array(
						// translators: placeholder is blogname.
						'NAME' => sprintf( __( '%s - Order', 'cartflows-pro' ), get_option( 'blogname' ) ),
						'DESC' => ( implode( ', ', $item_names ) ),
						'AMT'  => $this->round( $order_subtotal + $order->get_cart_tax() ),
						'QTY'  => 1,
					),
					0,
					$use_deprecated_params
				);

				if ( $use_deprecated_params ) {

					$this->add_parameters(
						array(
							'AMT'              => $total_amount,
							'CURRENCYCODE'     => wcf_pro()->wc_common->get_currency( $order ),
							'ITEMAMT'          => $this->round( $order_subtotal + $order->get_cart_tax() ),
							'SHIPPINGAMT'      => $this->round( $order->get_total_shipping() + $order->get_shipping_tax() ),
							'INVNUM'           => $this->wc_gateway()->get_option( 'invoice_prefix' ) . wcf_pro()->wc_common->get_order_id( $order ),
							'PAYMENTACTION'    => $type,
							'PAYMENTREQUESTID' => wcf_pro()->wc_common->get_order_id( $order ),
							'CUSTOM'           => json_encode(
								array(
									'_wcf_order_id' => wcf_pro()->wc_common->get_order_id( $order ),
								)
							),
						)
					);
				} else {

					$this->add_payment_parameters(
						array(
							'AMT'              => $total_amount,
							'CURRENCYCODE'     => wcf_pro()->wc_common->get_currency( $order ),
							'ITEMAMT'          => $this->round( $order_subtotal + $order->get_cart_tax() ),
							'SHIPPINGAMT'      => $this->round( $order->get_total_shipping() + $order->get_shipping_tax() ),
							'INVNUM'           => $this->wc_gateway()->get_option( 'invoice_prefix' ) . wcf_pro()->wc_common->get_order_id( $order ),
							'PAYMENTACTION'    => $type,
							'PAYMENTREQUESTID' => wcf_pro()->wc_common->get_order_id( $order ),
							'CUSTOM'           => json_encode(
								array(
									'_wcf_order_id' => wcf_pro()->wc_common->get_order_id( $order ),
								)
							),
						)
					);
				}
			} else {

				// Add individual order items.
				foreach ( $order_items as $item ) {
					$this->add_litem_params( $item, $item_count ++, $use_deprecated_params );
				}

				$total_amount = $this->round( $order->get_total() );

				// Add order-level parameters.
				if ( $use_deprecated_params ) {
					$this->add_parameters(
						array(
							'AMT'              => $total_amount,
							'CURRENCYCODE'     => wcf_pro()->wc_common->get_currency( $order ),
							'ITEMAMT'          => $this->round( $order_subtotal ),
							'SHIPPINGAMT'      => $this->round( $order->get_total_shipping() ),
							'TAXAMT'           => $this->round( $order->get_total_tax() ),
							'INVNUM'           => $this->wc_gateway()->get_option( 'invoice_prefix' ) . wcf_pro()->wc_common->get_order_id( $order ),
							'PAYMENTACTION'    => $type,
							'PAYMENTREQUESTID' => wcf_pro()->wc_common->get_order_id( $order ),

						)
					);
				} else {
					$this->add_payment_parameters(
						array(
							'AMT'              => $total_amount,
							'CURRENCYCODE'     => wcf_pro()->wc_common->get_currency( $order ),
							'ITEMAMT'          => $this->round( $order_subtotal ),
							'SHIPPINGAMT'      => $this->round( $order->get_total_shipping() ),
							'TAXAMT'           => $this->round( $order->get_total_tax() ),
							'INVNUM'           => $this->wc_gateway()->get_option( 'invoice_prefix' ) . wcf_pro()->wc_common->get_order_id( $order ),
							'PAYMENTACTION'    => $type,
							'PAYMENTREQUESTID' => wcf_pro()->wc_common->get_order_id( $order ),
							'CUSTOM'           => json_encode(
								array(
									'_wcf_order_id' => wcf_pro()->wc_common->get_order_id( $order ),
								)
							),
						)
					);
				}
			}
		}
	}

	/**
	 * Adds a line item parameters to the request
	 *
	 * @param array $params paramters.
	 * @param int   $item_count current item count.
	 * @param bool  $use_deprecated_params whether to use deprecated PayPal?.
	 * @since 1.0.0
	 */
	public function add_litem_params( array $params, $item_count, $use_deprecated_params = false ) {
		foreach ( $params as $key => $value ) {
			if ( $use_deprecated_params ) {
				$this->add_parameter( "L_{$key}{$item_count}", $value );
			} else {
				$this->add_parameter( "L_PAYMENTREQUEST_0_{$key}{$item_count}", $value );
			}
		}
	}

	/**
	 * Skip line items and send as single item
	 *
	 * @since 1.0.0
	 *
	 * @param WC_Order $order Optional. The WC_Order object. Default null.
	 * @param array    $order_items Order items.
	 *
	 * @return bool true if line items should be skipped, false otherwise
	 */
	public function skip_line_items( $order = null, $order_items = null ) {

		$skip_line_items = false;

		if ( true != $skip_line_items && ! is_null( $order ) && ! is_null( $order_items ) ) {

			$calculated_total = 0;

			foreach ( $order_items as $item ) {
				$calculated_total += $this->round( $item['AMT'] * $item['QTY'] );
			}

			$calculated_total += $this->round( $order->get_total_shipping() ) + $this->round( $order->get_total_tax() );
			$total_amount      = $this->round( $order->get_total() );

			if ( $this->price_format( $total_amount ) !== $this->price_format( $calculated_total ) ) {
				$skip_line_items = true;
			}
		}

		// Filter for line items.
		return apply_filters( 'wcs_paypal_reference_transaction_skip_line_items', $skip_line_items, $order );
	}

	/**
	 * Convert parameter array to HTTP build string
	 *
	 * @return string
	 */
	public function to_string() {
		wcf()->logger->log( print_r( $this->get_parameters(), true ) );

		return http_build_query( $this->get_parameters(), '', '&' );
	}

	/**
	 * Get payment parameters.
	 *
	 * @return array
	 * @throws Exception Paramter errors.
	 */
	public function get_parameters() {

		/**
		 * Filter PPE request parameters.
		 *
		 * Use this to modify the PayPal request parameters prior to validation
		 *
		 * @param array $parameters
		 * @param \WC_PayPal_Express_API_Request $this instance
		 */
		$this->parameters = apply_filters( 'wcs_paypal_request_params', $this->parameters, $this );

		// validate parameters.
		foreach ( $this->parameters as $key => $value ) {

			// remove unused params.
			if ( '' === $value || is_null( $value ) ) {
				unset( $this->parameters[ $key ] );
			}

			// format and check amounts.
			if ( false !== strpos( $key, 'AMT' ) ) {

				// amounts must be 10,000.00 or less for USD.
				if ( isset( $this->parameters['PAYMENTREQUEST_0_CURRENCYCODE'] ) && 'USD' == $this->parameters['PAYMENTREQUEST_0_CURRENCYCODE'] && $value > 10000 ) {

					throw new Exception( sprintf( '%s amount of %s must be less than $10,000.00', $key, $value ) );
				}

				$this->parameters[ $key ] = $this->price_format( $value );
			}
		}

		return $this->parameters;
	}

	/**
	 * Check if api response has error
	 *
	 * @param array $response response object array.
	 * @return bool
	 */
	public function has_error_api_response( $response ) {

		// Consider error when ACK parameter is missing.
		if ( ! isset( $response['ACK'] ) ) {
			return true;
		}

		// Check if response contains success string.
		return ( 'Success' !== $this->get_value_from_response( $response, 'ACK' ) && 'SuccessWithWarning' !== $this->get_value_from_response( $response, 'ACK' ) );
	}

	/**
	 * Set variables for API calls
	 *
	 * @param int    $gateway_id gateway ID.
	 * @param string $api_env Api enviornment.
	 * @param string $api_usr API username.
	 * @param string $api_pass API password.
	 * @param string $api_sign API signature.
	 */
	public function setup_api_vars( $gateway_id, $api_env, $api_usr, $api_pass, $api_sign ) {

		$this->api_username  = $api_usr;
		$this->api_password  = $api_pass;
		$this->api_signature = $api_sign;

		$this->gateway_id = $gateway_id;

		// Rquest URI per enviornment.
		$this->request_uri = ( 'live' === $api_env ) ? $this->live_api : $this->test_api;

		// Set HTTP version to 1.1.
		$this->request_http_version = '1.1';

	}

	/**
	 * Add api credentials parameters
	 *
	 * @param string $api_username API username.
	 * @param string $api_password API password.
	 * @param string $api_signature API signature.
	 * @param string $api_version API version.
	 * @return void
	 */
	public function add_credentials_param( $api_username, $api_password, $api_signature, $api_version ) {

		$this->add_parameters(
			array(
				'USER'      => $api_username,
				'PWD'       => $api_password,
				'SIGNATURE' => $api_signature,
				'VERSION'   => $api_version,
			)
		);
	}
}
