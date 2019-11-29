<?php
/**
 * Cartflows Frontend.
 *
 * @package cartflows
 */

/**
 * Class Cartflows_Pro_Frontend.
 */
class Cartflows_Pro_Frontend {

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

		/* Set / Destroy Flow Sessions. Set data */
		add_action( 'wp', array( $this, 'init_actions' ), 1 );

		/* Enqueue global required scripts */
		add_action( 'wp', array( $this, 'wp_actions' ), 55 );

		/* Setup Upsell for for payment gatways. Only if we are in out flow */
		add_action( 'woocommerce_pre_payment_complete', array( $this, 'maybe_setup_upsell' ), 99, 1 );
		/* Setup upsell for other gatways which are not covered in "woocommerce_pre_payment_complete" hook */
		add_action( 'woocommerce_checkout_order_processed', array( $this, 'maybe_setup_upsell_ignore_gateways' ), 100, 3 );

		add_action( 'cartflows_order_status_change_to_main_order', array( $this, 'register_cron_for_order_success' ), 10, 3 );
		add_action( 'cartflows_order_status_change_to_main_order', array( $this, 'update_main_order_data_in_transient' ), 10, 3 );

		/* Redirect to next step in flow if next step is other than thank you */
		add_action( 'cartflows_order_started', array( $this, 'set_next_step_url' ) );

		$facebook_settings = Cartflows_Helper::get_facebook_settings();
		if ( 'enable' === $facebook_settings['facebook_pixel_tracking'] ) {
			/* Facebook Pixel */
			add_action( 'wcf_order_bump_item_added', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_quantity_update', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_variation_selection', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_multiple_selection', array( $this, 'trigger_fb_event' ) );
			add_action( 'wcf_after_single_selection', array( $this, 'trigger_fb_event' ) );
		}

	}


	/**
	 * Add updated cart and product in Ajax response.
	 *
	 * @param integer $product_id product id.
	 */
	function trigger_fb_event( $product_id ) {

		add_filter(
			'woocommerce_update_order_review_fragments',
			function( $data ) use ( $product_id ) {
				$data['added_to_cart_data'] = wcf_pro()->utils->prepare_fb_response( $product_id );
				return $data;
			}
		);

	}

	/**
	 * Set next step url.
	 *
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	function set_next_step_url( $order ) {

		/* Modify the checkout order received url to go thank you page in our flow */
		remove_filter( 'woocommerce_get_checkout_order_received_url', array( Cartflows_Frontend::get_instance(), 'redirect_to_thankyou_page' ), 10, 2 );

		add_filter( 'woocommerce_get_checkout_order_received_url', array( $this, 'show_offer_step' ), 10, 2 );
	}

	/**
	 * Show offer step.
	 *
	 * @param string $order_recieve_url recieve url.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	function show_offer_step( $order_recieve_url, $order ) {

		wcf()->logger->log( 'Start-' . __CLASS__ . '::' . __FUNCTION__ );

		if ( $order->get_status() !== 'failed' ) {

			if ( _is_wcf_doing_checkout_ajax() ) {

				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();

				if ( ! $checkout_id ) {
					$checkout_id = wcf()->utils->get_checkout_id_from_order( $order->get_id() );
				}
			} else {
				$checkout_id = wcf()->utils->get_checkout_id_from_order( $order->get_id() );
			}

			wcf()->logger->log( $checkout_id );

			if ( $checkout_id ) {

				$flow_id = wcf()->utils->get_flow_id_from_order( $order->get_id() );

				if ( $flow_id ) {

					$next_step_id = wcf()->utils->get_next_step_id( $flow_id, $checkout_id );

					if ( $next_step_id ) {

						$order_recieve_url = get_permalink( $next_step_id );

						$session_key = wcf_pro()->session->get_session_key( $flow_id );

						$query_args = array(
							'wcf-order' => $order->get_id(),
							'wcf-key'   => $order->get_order_key(),
						);

						if ( $session_key ) {
							$query_args['wcf-sk'] = $session_key;
						}

						$order_recieve_url = add_query_arg( $query_args, $order_recieve_url );
					}
				}
			}
		}

		Cartflows_Helper::send_fb_response_if_enabled( $order->get_id() );

		wcf()->logger->log( 'End-' . __CLASS__ . '::' . __FUNCTION__ );

		return $order_recieve_url;
	}

	/**
	 * Register Cron.
	 *
	 * @param string $new_status new status.
	 * @param string $normal_status normal status.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	function register_cron_for_order_success( $new_status, $normal_status, $order ) {

		if ( false === is_a( $order, 'WC_Order' ) ) {

			/* Not Valid Order */
			wcf()->logger->log( 'Not a valid order' );

			return;
		}

		wcf()->logger->log( 'new-status' . $new_status );

		if ( wcf_pro()->order->_get_order_status_slug() !== $new_status ) {

			/* Not Valid Order Status */
			wcf()->logger->log( 'Not a valid order status' );

			return;
		}

		$args = array(
			'order_id'      => $order->get_id(),
			'before_normal' => $order->get_status(), // Pending.
			'normal_status' => $normal_status, // On Hold/Processing etc.
		);

		if ( false === wp_next_scheduled( 'carflows_schedule_normalize_order_status', $args ) ) {

			/* Setup Schedule */

			wp_schedule_single_event( time() + ( 30 * MINUTE_IN_SECONDS ), 'carflows_schedule_normalize_order_status', $args );

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Cron Scheduled for Normalize Order Status' );
		}
	}

	/**
	 * Update main order data in transient.
	 *
	 * @param string $new_status new status.
	 * @param string $normal_status normal status.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	function update_main_order_data_in_transient( $new_status, $normal_status, $order ) {

		if ( false === is_a( $order, 'WC_Order' ) ) {

			/* Not Valid Order */
			wcf()->logger->log( 'Not a valid order' );

			return;
		}

		wcf()->logger->log( 'new-status' . $new_status );

		if ( wcf_pro()->order->_get_order_status_slug() !== $new_status ) {

			/* Not Valid Order Status */
			wcf()->logger->log( 'Not a valid order status' );

			return;
		}

		$flow_id = get_post_meta( $order->get_id(), '_wcf_flow_id', true );

		$data = array(
			'order_id'      => $order->get_id(),
			'before_normal' => $order->get_status(), // Pending.
			'normal_status' => $normal_status, // On Hold/Processing etc.
		);

		wcf()->logger->log( 'Gateway status change - Flow-' . $flow_id . ' Order-' . $order->get_id() . json_encode( $data ) );

		wcf_pro()->session->update_data( $flow_id, $data );
	}

	/**
	 * Init Actions.
	 *
	 * @since 1.0.0
	 */
	function init_actions() {

		$this->set_flow_session();
	}

	/**
	 * Set flow session.
	 *
	 * @since 1.0.0
	 */
	function set_flow_session() {

		if ( wcf()->utils->is_step_post_type() ) {

			wcf()->utils->do_not_cache();

			$flow_id = wcf()->utils->get_flow_id();

			if ( ! $flow_id ) {
				return;
			}

			if ( _is_wcf_thankyou_type() ) {

				// Destroy Session On Thank You Page.
				wcf_pro()->session->destroy_session( $flow_id );

			} elseif ( _is_wcf_landing_type() || _is_wcf_checkout_type() ) {

				$data = array(
					'flow_id' => $flow_id,
					'steps'   => get_post_meta( $flow_id, 'wcf-steps', true ),
				);

				wcf_pro()->session->set_session( $flow_id, $data );

			} elseif ( _is_wcf_upsell_type() || _is_wcf_downsell_type() ) {

				if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {
					return;
				}

				if ( ! ( is_user_logged_in() && current_user_can( 'manage_options' ) ) ) {

					if ( ! wcf_pro()->session->is_active_session( $flow_id ) ) {
						wp_die( __( 'Your session is expired', 'cartflows-pro' ) );
					}
				}
			}
		}

	}

	/**
	 * Loads module files.
	 *
	 * @param int $order_id Order id.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function maybe_setup_upsell( $order_id = '' ) {

		wcf()->logger->log( ' woocommerce_pre_payment_complete ' );

		if ( '' == $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {

			wcf()->logger->log( 'Not valid order' );
		}

		if ( ! wcf_pro()->flow->is_upsell_exists( $order ) ) {

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Upsell not exists' );

			return;
		}

		$order_gateway = $order->get_payment_method();

		$gateway_obj = wcf_pro()->gateways->load_gateway( $order_gateway );

		if ( $gateway_obj ) {

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Flow Started' );

			/* Checkout recive url filter */
			do_action( 'cartflows_order_started', $order );

		} else {
			wcf()->logger->log( 'Order-' . $order->get_id() . ' Gateway object not found' );
		}
	}

	/**
	 * Loads module files.
	 *
	 * @param int $order_id Order id.
	 * @since 1.0.0
	 *
	 * @return void
	 */
	function setup_upsell( $order_id = '' ) {

		wcf()->logger->log( 'Force setup upsell' );

		if ( '' == $order_id ) {
			return;
		}

		$order = wc_get_order( $order_id );

		if ( ! is_object( $order ) ) {

			wcf()->logger->log( 'Not valid order' );
		}

		if ( ! wcf_pro()->flow->is_upsell_exists( $order ) ) {

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Upsell not exists' );

			return;
		}

		$order_gateway = $order->get_payment_method();

		$gateway_obj = wcf_pro()->gateways->load_gateway( $order_gateway );

		if ( $gateway_obj ) {

			wcf()->logger->log( 'Order-' . $order->get_id() . ' Flow Started' );

			/* Checkout recive url filter */
			do_action( 'cartflows_order_started', $order );

		} else {
			wcf()->logger->log( 'Order-' . $order->get_id() . ' Gateway object not found' );
		}
	}

	/**
	 * Ignore Gateways checkout processed.
	 *
	 * @param int    $order_id order id.
	 * @param array  $posted_data post data.
	 * @param object $order order object.
	 * @since 1.0.0
	 */
	function maybe_setup_upsell_ignore_gateways( $order_id, $posted_data, $order ) {
		wcf()->logger->log( ' woocommerce_checkout_order_processed ' );

		if ( '' == $order_id ) {
			return;
		}

		// Added here again to solve the issue: Some times checkout was redirecting to default thank you page instead of upsell/downsell.
		$order_gateway = $order->get_payment_method();

		$gateways = array( 'stripe', 'ppec_paypal' );
		$gateways = apply_filters( 'cartflows_offer_supported_payment_gateway_slugs', $gateways );
		if ( in_array( $order_gateway, $gateways, true ) ) {

			$order = wc_get_order( $order_id );

			if ( ! is_object( $order ) ) {

				wcf()->logger->log( 'Not valid order' );
			}

			if ( ! wcf_pro()->flow->is_upsell_exists( $order ) ) {

				wcf()->logger->log( 'Order-' . $order->get_id() . ' Upsell not exists' );

				return;
			}

			$gateway_obj = wcf_pro()->gateways->load_gateway( $order_gateway );

			if ( $gateway_obj ) {

				wcf()->logger->log( 'Order-' . $order->get_id() . ' Flow Started' );

				/* Checkout recive url filter */
				do_action( 'cartflows_order_started', $order );

			} else {
				wcf()->logger->log( 'Order-' . $order->get_id() . ' Gateway object not found' );
			}
		}
		// Added here again to solve the issue: Some times checkout was redirecting to default thank you page instead of upsell/downsell.
	}

	/**
	 * WP Actions.
	 *
	 * @since 1.0.0
	 */
	function wp_actions() {

		if ( wcf()->utils->is_step_post_type() ) {

			add_action( 'wp_enqueue_scripts', array( $this, 'global_flow_scripts' ), 20 );

			/* Add pro version class to body frontend */
			add_filter( 'body_class', array( $this, 'pro_body_class' ) );
		}
	}

	/**
	 * Global flow scripts.
	 *
	 * @since 1.0.0
	 */
	function global_flow_scripts() {

		if ( wcf()->utils->is_step_post_type() ) {

			wp_enqueue_style(
				'wcf-pro-frontend-global',
				wcf_pro()->utils->get_css_url( 'frontend' ),
				array(),
				CARTFLOWS_PRO_VER
			);

			wp_enqueue_script(
				'wcf-pro-frontend-global',
				wcf_pro()->utils->get_js_url( 'frontend' ),
				array( 'jquery' ),
				CARTFLOWS_PRO_VER,
				true
			);
		}
	}

	/**
	 * Add pro version class to body in frontend.
	 *
	 * @since 1.1.5
	 * @param array $classes classes.
	 * @return array $classes classes.
	 */
	function pro_body_class( $classes ) {

		$classes[] = ' cartflows-pro-' . CARTFLOWS_PRO_VER;

		return $classes;
	}

}

/**
 *  Prepare if class 'Cartflows_Pro_Frontend' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Frontend::get_instance();
