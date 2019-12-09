<?php
/**
 * Offer markup.
 *
 * @package cartflows
 */

/**
 * Offer Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Base_Offer_Markup {


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
	 *  Constructor
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'offer_scripts' ) );
	}

	/**
	 *  Offer script
	 */
	function offer_scripts() {

		if ( _is_wcf_base_offer_type() ) {

			global $post;

			$product_id = '';
			$step_id    = $post->ID;
			$flow_id    = wcf()->utils->get_flow_id_from_step_id( $step_id );

			if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {

				$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product', 'dummy' );

				if ( 'dummy' === $offer_product ) {

					$args = array(
						'posts_per_page' => 1,
						'orderby'        => 'rand',
						'post_type'      => 'product',
					);

					$random_product = get_posts( $args );

					if ( isset( $random_product[0]->ID ) ) {
						$offer_product = array(
							$random_product[0]->ID,
						);
					}
				}
			} else {
				$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product' );
			}

			if ( isset( $offer_product[0] ) ) {

				$product_id = $offer_product[0];
			}

			$order_id   = ( isset( $_GET['wcf-order'] ) ) ? $_GET['wcf-order'] : '';
			$order_key  = ( isset( $_GET['wcf-key'] ) ) ? $_GET['wcf-key'] : '';
			$order      = wc_get_order( $order_id );
			$skip_offer = 'no';

			$payment_method = '';
			if ( $order ) {

				$payment_method = $order->get_payment_method();

				$gateways = array( 'paypal', 'ppec_paypal' );
				$gateways = apply_filters( 'cartflows_offer_supported_payment_gateway_slugs', $gateways );
				if ( ( in_array( $payment_method, $gateways, true ) ) && ! wcf_pro()->utils->is_reference_transaction() && ! wcf_pro()->utils->is_zero_value_offered_product() ) {

					$skip_offer = 'yes';
				}
			}

			$localize = array(
				'step_id'                     => $step_id,
				'product_id'                  => $product_id,
				'order_id'                    => $order_id,
				'order_key'                   => $order_key,
				'skip_offer'                  => $skip_offer,
				'wcf_downsell_accepted_nonce' => wp_create_nonce( 'wcf_downsell_accepted' ),
				'wcf_downsell_rejected_nonce' => wp_create_nonce( 'wcf_downsell_rejected' ),
				'wcf_upsell_accepted_nonce'   => wp_create_nonce( 'wcf_upsell_accepted' ),
				'wcf_upsell_rejected_nonce'   => wp_create_nonce( 'wcf_upsell_rejected' ),
				'payment_method'              => $payment_method,
			);

			if ( 'stripe' === $payment_method ) {
				$localize['wcf_stripe_sca_check_nonce'] = wp_create_nonce( 'wcf_stripe_sca_check' );
				wp_register_script( 'stripe', 'https://js.stripe.com/v3/', '', '3.0', true );
				wp_enqueue_script( 'stripe' );
			}

			wp_localize_script( 'jquery', 'cartflows_offer', apply_filters( 'cartflows_offer_js_localize', $localize ) );
		}
	}

	/**
	 * Offer accepeted
	 *
	 * @param int   $step_id Flow step id.
	 * @param array $extra_data extra data.
	 * @param array $result process result.
	 * @since 1.0.0
	 */
	public function offer_accepted( $step_id, $extra_data, $result ) {

		$order_id          = $extra_data['order_id'];
		$order_key         = $extra_data['order_key'];
		$product_id        = $extra_data['product_id'];
		$step_type         = $extra_data['template_type'];
		$is_charge_success = false;
		$order             = wc_get_order( $order_id );
		$skip_payment      = filter_input( INPUT_POST, 'stripe_sca_payment', FILTER_VALIDATE_BOOLEAN );

		// Reverification of 3DS.
		if ( $skip_payment ) {
			$_stripe_intent_id = get_post_meta( $order->get_id(), '_stripe_intent_id_' . $step_id, true );
			$intent_id         = filter_input( INPUT_POST, 'stripe_intent_id', FILTER_SANITIZE_STRING );
			$skip_payment      = ( $intent_id === $_stripe_intent_id ) ? true : false;
		}

		$offer_product = wcf_pro()->utils->get_offer_data( $step_id );

		if ( isset( $offer_product['price'] ) && ( floatval( 0 ) === floatval( $offer_product['price'] )
		|| '' === trim( $offer_product['price'] ) ) || $skip_payment ) {

			$is_charge_success = true;

		} else {

			$order_gateway = $order->get_payment_method();

			wcf()->logger->log( 'Order-' . $order->get_id() . ' ' . $order_gateway . ' - Payment gateway' );

			$gateway_obj = wcf_pro()->gateways->load_gateway( $order_gateway );

			if ( $gateway_obj ) {

				wcf()->logger->log( 'Order-' . $order->get_id() . ' Payment gateway charge' );

				$is_charge_success = $gateway_obj->process_offer_payment( $order, $offer_product );
			}
		}

		if ( $is_charge_success ) {

			if ( 'upsell' === $step_type ) {
				/* Add Product To Main Order */
				wcf_pro()->order->add_upsell_product( $order, $offer_product );

			} else {
				wcf_pro()->order->add_downsell_product( $order, $offer_product );
			}

			do_action( 'cartflows_offer_accepted', $order, $offer_product );

			/**
			 * We need to reduce stock here.
			 *
			 * @todo
			 * reduce_stock();
			 */

			$data = array(
				'action'        => 'offer_accepted',
				'order_id'      => $order_id,
				'order_key'     => $order_key,
				'template_type' => $step_type,
			);

			/* Get Redirect URL */
			$next_step_url = wcf_pro()->flow->get_next_step_url( $step_id, $data );

			$result = array(
				'status'   => 'success',
				'redirect' => $next_step_url,
				'message'  => __( 'Product Added Successfully.', 'cartflows-pro' ),
			);

			Cartflows_Helper::send_fb_response_if_enabled( $order_id, $offer_product );

			wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer accepted' );
		} else {

			/* @todo if payment failed redirect to last page or not */
			$data = array(
				'order_id'  => $order_id,
				'order_key' => $order_key,
			);

			$thank_you_page_url = wcf_pro()->flow->get_thankyou_page_url( $step_id, $data );

			$result = array(
				'status'   => 'failed',
				'redirect' => $thank_you_page_url,
				'message'  => __( 'Oooops! Your Payment Failed.', 'cartflows-pro' ),
			);

			wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer Payment Failed. Redirected to thankyou step.' );
		}

		return $result;
	}

	/**
	 * Offer rejected
	 *
	 * @param int   $step_id Flow step id.
	 * @param array $extra_data extra data.
	 * @param array $result process result.
	 * @since 1.0.0
	 */
	public function offer_rejected( $step_id, $extra_data, $result ) {

		/* Get Redirect URL */
		$next_step_url = wcf_pro()->flow->get_next_step_url( $step_id, $extra_data );

		$order_id  = $extra_data['order_id'];
		$step_type = $extra_data['template_type'];

		$order         = wc_get_order( $order_id );
		$offer_product = wcf_pro()->utils->get_offer_data( $step_id );

		$result = array(
			'status'   => 'success',
			'redirect' => $next_step_url,
			'message'  => __( 'Redirecting...', 'cartflows-pro' ),
		);

		wcf()->logger->log( 'Order-' . $order_id . ' ' . $step_type . ' Offer rejected' );

		do_action( 'cartflows_offer_rejected', $order, $offer_product );

		return $result;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Base_Offer_Markup::get_instance();
