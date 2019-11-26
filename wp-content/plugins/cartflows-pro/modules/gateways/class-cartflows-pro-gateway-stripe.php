<?php
/**
 * Stripe Gateway.
 *
 * @package cartflows
 */

/**
 * Class Cartflows_Pro_Gateway_Stripe.
 */
class Cartflows_Pro_Gateway_Stripe {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Key name variable
	 *
	 * @var key
	 */
	public $key = 'stripe';

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

		add_filter( 'wc_stripe_force_save_source', array( $this, 'tokenize_if_required' ) );

		add_filter( 'wc_stripe_3ds_source', array( $this, 'save_3ds_source_for_later' ), 10, 2 );

		add_action( 'wc_gateway_stripe_process_response', array( $this, 'redirect_using_wc_function' ), 10, 2 );

		add_action( 'wp_ajax_wcf_stripe_sca_check', array( $this, 'check_stripe_sca' ) );
		add_action( 'wp_ajax_nopriv_wcf_stripe_sca_check', array( $this, 'check_stripe_sca' ) );
	}


	/**
	 * Verify 3DS and create intent accordingly.
	 *
	 * @return bool
	 */
	function check_stripe_sca() {

		$nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_stripe_sca_check' ) ) {
			return;
		}

		$step_id       = intval( $_POST['step_id'] );
		$order_id      = sanitize_text_field( $_POST['order_id'] );
		$offer_type    = sanitize_text_field( $_POST['offer_type'] );
		$offer_action  = sanitize_text_field( $_POST['offer_action'] );
		$order         = wc_get_order( $order_id );
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
				|| '' === trim( $offer_product['price'] ) ) ) {
			return false;
		} else {
			$gateway = $this->get_wc_gateway();
			if ( $gateway ) {
				$order_source = $gateway->prepare_order_source( $order );
				$is_3ds       = $order_source->source_object->card->three_d_secure;
				// Check if 3DS required.
				if ( isset( $is_3ds ) && 'optional' !== $is_3ds && 'yes' === $offer_action ) {
					$intent = $this->create_intent( $order, $order_source, $offer_product );

					$main_settings = get_option( 'woocommerce_stripe_settings' );
					$testmode      = ( ! empty( $main_settings['testmode'] ) && 'yes' === $main_settings['testmode'] ) ? true : false;

					if ( $testmode ) {
						$publishable_key = ! empty( $main_settings['test_publishable_key'] ) ? $main_settings['test_publishable_key'] : '';
					} else {
						$publishable_key = ! empty( $main_settings['publishable_key'] ) ? $main_settings['publishable_key'] : '';
					}

					wp_send_json(
						array(
							'result'        => 'success',
							'redirect'      => $gateway->get_return_url( $order ),
							'intent_secret' => $intent->client_secret,
							'stripe_pk'     => $publishable_key,
						)
					);
				} else {
					do_action( 'wp_ajax_wcf_' . $offer_type . '_accepted' );
				}
			}
		}
	}
	/**
	 * Tokenize to save source of payment if required
	 *
	 * @param bool $save_source force save source.
	 */
	public function tokenize_if_required( $save_source ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
		$flow_id     = wcf()->utils->get_flow_id_from_post_data();

		if ( $checkout_id && $flow_id ) {

			$next_step_id = wcf()->utils->get_next_step_id( $flow_id, $checkout_id );

			if ( wcf()->utils->check_is_offer_page( $next_step_id ) ) {

				$save_source = true;
				wcf()->logger->log( 'Force save source enabled' );
			}
		}

		return $save_source;
	}

	/**
	 * Save 3d source.
	 *
	 * @param array $post_data Threads data.
	 * @param array $order order data.
	 */
	public function save_3ds_source_for_later( $post_data, $order ) {

		if ( $order && wcf_pro()->flow->is_upsell_exists( $order ) ) {

			$order->update_meta_data( '_cartflows_stripe_source_id', $post_data['three_d_secure']['card'] );

			$order->save();

			wcf()->logger->log( '3ds source saved for later use' );
		}

		return $post_data;
	}

	/**
	 * Redirection to order received URL.
	 *
	 * @param array $response response data.
	 * @param array $order order data.
	 */
	public function redirect_using_wc_function( $response, $order ) {

		wcf()->logger->log( 'Started: ' . __CLASS__ . '::' . __FUNCTION__ );

		if ( 1 === did_action( 'cartflows_order_started' ) && 1 === did_action( 'wc_gateway_stripe_process_redirect_payment' ) ) {

			$get_url = $order->get_checkout_order_received_url();
			wp_redirect( $get_url );
			exit();
		}
	}

	/**
	 * Check if token is present.
	 *
	 * @param array $order order data.
	 */
	public function has_token( $order ) {

		$order_id = $order->get_id();

		$token = get_post_meta( $order_id, '_cartflows_stripe_source_id', true );

		if ( empty( $token ) ) {
			$token = get_post_meta( $order_id, '_stripe_source_id', true );
		}

		if ( ! empty( $token ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Get WooCommerce payment geteways.
	 *
	 * @return array
	 */
	public function get_wc_gateway() {

		global $woocommerce;

		$gateways = $woocommerce->payment_gateways->payment_gateways();

		return $gateways[ $this->key ];
	}

	/**
	 * After payment process.
	 *
	 * @param array $order order data.
	 * @param array $product product data.
	 * @return array
	 */
	public function process_offer_payment( $order, $product ) {

		$is_successful = false;

		if ( ! $this->has_token( $order ) ) {

			return $is_successful;
		}

		try {

			$gateway = $this->get_wc_gateway();

			$order_source = $gateway->prepare_order_source( $order );

			$response = WC_Stripe_API::request( $this->generate_payment_request( $order, $order_source, $product ) );

			if ( ! is_wp_error( $response ) ) {

				if ( ! empty( $response->error ) ) {
					$is_successful = false;
				} else {

					// '_transaction_id', $response->id
					$is_successful = true;
				}
			}

			// @todo Show actual error if any.
		} catch ( Exception $e ) {

			// @todo Exception catch to show actual error.
		}

		return $is_successful;
	}

	/**
	 * Create a new PaymentIntent.
	 *
	 * @param WC_Order $order           The order that is being paid for.
	 * @param object   $prepared_source The source that is used for the payment.
	 * @param object   $product offer product.
	 * @return object                   An intent or an error.
	 */
	public function create_intent( $order, $prepared_source, $product ) {
		// The request for a charge contains metadata for the intent.
		$full_request = $this->generate_payment_request( $order, $prepared_source, $product );

		$request = array(
			'source'               => $prepared_source->source,
			'amount'               => WC_Stripe_Helper::get_stripe_amount( $product['price'] ),
			'currency'             => strtolower( $order->get_order_currency() ),
			'description'          => $full_request['description'],
			'metadata'             => $full_request['metadata'],
			'statement_descriptor' => $full_request['statement_descriptor'],
			'capture_method'       => ( 'true' === $full_request['capture'] ) ? 'automatic' : 'manual',
			'payment_method_types' => array(
				'card',
			),
			'customer'             => $prepared_source->customer,
		);

		if ( $prepared_source->customer ) {
			$request['customer'] = $prepared_source->customer;
		}
		// Create an intent that awaits an action.
		$intent = WC_Stripe_API::request( $request, 'payment_intents' );
		if ( ! empty( $intent->error ) ) {
			return $intent;
		}
		$order_id = $order->get_id();

		$step_id = filter_input( INPUT_POST, 'step_id', FILTER_VALIDATE_INT );
		// Save the intent ID to the order.
		update_post_meta( $order_id, '_stripe_intent_id_' . $step_id, $intent->id );

		return $intent;
	}

	/**
	 * Generate payment request.
	 *
	 * @param array  $order order data.
	 * @param string $order_source order source.
	 * @param array  $product product data.
	 * @return array
	 */
	protected function generate_payment_request( $order, $order_source, $product ) {

		$gateway               = $this->get_wc_gateway();
		$post_data             = array();
		$post_data['currency'] = strtolower( $order ? $order->get_currency() : get_woocommerce_currency() );
		$post_data['amount']   = WC_Stripe_Helper::get_stripe_amount( $product['price'], $post_data['currency'] );
		/* translators: %1s site name */
		$post_data['description'] = sprintf( __( '%1$s - Order %2$s - One Time offer', 'cartflows-pro' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() );
		$post_data['capture']     = $gateway->capture ? 'true' : 'false';
		$billing_first_name       = $order->get_billing_first_name();
		$billing_last_name        = $order->get_billing_last_name();
		$billing_email            = $order->get_billing_email();

		if ( ! empty( $billing_email ) && apply_filters( 'wc_stripe_send_stripe_receipt', false ) ) {
			$post_data['receipt_email'] = $billing_email;
		}

		$metadata = array(
			__( 'customer_name', 'cartflows-pro' )  => sanitize_text_field( $billing_first_name ) . ' ' . sanitize_text_field( $billing_last_name ),
			__( 'customer_email', 'cartflows-pro' ) => sanitize_email( $billing_email ),
			'order_id'                              => $order->get_order_number() . '_' . $product['id'],
		);

		$post_data['expand[]'] = 'balance_transaction';
		$post_data['metadata'] = apply_filters( 'wc_stripe_payment_metadata', $metadata, $order, $order_source );

		if ( $order_source->customer ) {
			$post_data['customer'] = $order_source->customer;
		}

		if ( $order_source->source ) {

			$source_3ds = $order->get_meta( '_cartflows_stripe_source_id', true );

			$post_data['source'] = ( '' !== $source_3ds ) ? $source_3ds : $order_source->source;
		}

		return apply_filters( 'wc_stripe_generate_payment_request', $post_data, $order, $order_source );
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Stripe' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Stripe::get_instance();
