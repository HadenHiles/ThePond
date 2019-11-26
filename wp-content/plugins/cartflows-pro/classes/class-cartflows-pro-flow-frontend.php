<?php
/**
 * Frontend & Markup
 *
 * @package cartflows
 */

/**
 * Flow Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Flow_Frontend {


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

		/* Analytics */
		add_action( 'wp_footer', array( $this, 'footer_markup' ) );
	}

	/**
	 *  Footer markup
	 */
	function footer_markup() {

		if ( wcf()->utils->is_step_post_type() ) {
			// @codingStandardsIgnoreStart
			?>
			<div class="wcf-loader-bg">
				<div class="wcf-loader-wrap">
					<div class="wcf-loader"><?php _e( 'Loading...', 'cartflows-pro' ); ?></div>
					<div class="wcf-order-msg">
						<p class="wcf-process-msg" ><?php _e( 'Processing Order...', 'cartflows-pro' ); ?></p>
						<p class="wcf-note wcf-note-yes"><?php _e( 'Please wait while we process your payment...', 'cartflows-pro' ); ?></p>
						<p class="wcf-note wcf-note-no"><?php _e( 'Please wait while we redirect you...', 'cartflows-pro' ); ?></p>
					</div>
				</div>
			</div>
			<?php
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Get next step URL.
	 *
	 * @since 1.0.0
	 * @param int   $step_id step ID.
	 * @param array $data order data.
	 *
	 * @return string
	 */
	function get_next_step_url( $step_id, $data ) {

		$step_id       = intval( $step_id );
		$link          = '#';
		$next_step_id  = false;
		$flow_id       = get_post_meta( $step_id, 'wcf-flow-id', true );
		$order_id      = $data['order_id'];
		$order_key     = $data['order_key'];
		$template_type = isset( $data['template_type'] ) ? $data['template_type'] : '';
		$session_key   = wcf_pro()->session->get_session_key( $flow_id );

		if ( ! $flow_id ) {
			return $link;
		}

		if ( 'upsell' === $template_type ) {

			if ( 'offer_accepted' === $data['action'] ) {
				$next_step_id = $this->get_next_step_id_for_upsell_accepted( $flow_id, $step_id );
			} else {
				$next_step_id = $this->get_next_step_id_for_upsell_rejected( $flow_id, $step_id );
			}
		} elseif ( 'downsell' === $template_type ) {

			if ( 'offer_accepted' === $data['action'] ) {
				$next_step_id = $this->get_next_step_id_for_downsell_accepted( $flow_id, $step_id );
			} else {
				$next_step_id = $this->get_next_step_id_for_downsell_rejected( $flow_id, $step_id );
			}
		} else {

			/* This is normal next step of flow */
			$next_step_id = $this->get_next_step_id_for_flow( $flow_id, $step_id );
		}

		if ( $next_step_id ) {

			$this->may_be_complete_order( $next_step_id, $order_id );

			$query_args = array(
				'wcf-order' => $order_id,
				'wcf-key'   => $order_key,
			);

			if ( $session_key ) {
				$query_args['wcf-sk'] = $session_key;
			}

			$link = add_query_arg( $query_args, get_permalink( $next_step_id ) );
		}

		return $link;
	}

	/**
	 * Normalize status if template is of type thank you page.
	 *
	 * @since 1.0.0
	 * @param int $next_step_id next step ID.
	 * @param int $order_id order id.
	 *
	 * @return void
	 */
	function may_be_complete_order( $next_step_id, $order_id ) {

		wcf()->logger->log( 'Entering: ' . __CLASS__ . '::' . __FUNCTION__ );

		$template_type = get_post_meta( $next_step_id, 'wcf-step-type', true );

		if ( 'thankyou' === $template_type ) {

			$order = wc_get_order( $order_id );

			wcf_pro()->order->may_be_normalize_status( $order );
		}
	}

	/**
	 * Get next step id for upsell.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 * @param int $step_id step ID.
	 *
	 * @return int
	 */
	function get_next_step_id_for_upsell_accepted( $flow_id, $step_id ) {

		$steps = wcf()->flow->get_steps( $flow_id );

		$next_step_id    = false;
		$next_step_index = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {
			$next_yes_step = get_post_meta( $step_id, 'wcf-yes-next-step', true );
			if ( ! empty( $next_yes_step ) ) {

				$next_step_id = $next_yes_step;
			} else {

				foreach ( $steps as $i => $step ) {

					if ( intval( $step['id'] ) === $step_id ) {

						$next_step_index = $i + 1;
						break;
					}
				}

				while ( $next_step_index && isset( $steps[ $next_step_index ] ) ) {

					$temp_next_step_id       = $steps[ $next_step_index ]['id'];
					$temp_next_step_template = get_post_meta( $temp_next_step_id, 'wcf-step-type', true );

					if ( 'downsell' === $temp_next_step_template ) {
						$next_step_index++;
					} else {
						$next_step_id = $temp_next_step_id;
						break;
					}
				}
			}
		}

		return $next_step_id;
	}

	/**
	 * Get next step id for upsell rejected.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 * @param int $step_id step ID.
	 *
	 * @return int
	 */
	function get_next_step_id_for_upsell_rejected( $flow_id, $step_id ) {

		$steps = wcf()->flow->get_steps( $flow_id );

		$next_step_id    = false;
		$next_step_index = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {
			$next_no_step = get_post_meta( $step_id, 'wcf-no-next-step', true );

			if ( ! empty( $next_no_step ) ) {

				$next_step_id = $next_no_step;
			} else {

				foreach ( $steps as $i => $step ) {

					if ( intval( $step['id'] ) === $step_id ) {

						$next_step_index = $i + 1;

						if ( $next_step_index && isset( $steps[ $next_step_index ] ) ) {

							$next_step_id = $steps[ $next_step_index ]['id'];
						}

						break;
					}
				}
			}
		}

		return $next_step_id;
	}

	/**
	 * Get next step id for flow.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 * @param int $step_id step ID.
	 *
	 * @return int
	 */
	function get_next_step_id_for_flow( $flow_id, $step_id ) {

		$steps = wcf()->flow->get_steps( $flow_id );

		$next_step_id    = false;
		$next_step_index = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		}

		foreach ( $steps as $i => $step ) {

			if ( intval( $step['id'] ) === $step_id ) {

				$next_step_index = $i + 1;

				if ( $next_step_index && isset( $steps[ $next_step_index ] ) ) {

					$next_step_id = $steps[ $next_step_index ]['id'];
				}

				break;
			}
		}

		return $next_step_id;
	}

	/**
	 * Get next step id for downsell accepted.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 * @param int $step_id step ID.
	 *
	 * @return int
	 */
	function get_next_step_id_for_downsell_accepted( $flow_id, $step_id ) {

		$steps = wcf()->flow->get_steps( $flow_id );

		$next_step_id    = false;
		$next_step_index = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {
			$next_yes_step = get_post_meta( $step_id, 'wcf-yes-next-step', true );
			if ( ! empty( $next_yes_step ) ) {

				$next_step_id = $next_yes_step;
			} else {

				foreach ( $steps as $i => $step ) {

					if ( intval( $step['id'] ) === $step_id ) {

						$next_step_index = $i + 1;

						if ( $next_step_index && isset( $steps[ $next_step_index ] ) ) {

							$next_step_id = $steps[ $next_step_index ]['id'];
						}

						break;
					}
				}
			}
		}

		return $next_step_id;
	}

	/**
	 * Get next step id for downsell rejected.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 * @param int $step_id step ID.
	 *
	 * @return int
	 */
	function get_next_step_id_for_downsell_rejected( $flow_id, $step_id ) {

		$steps = wcf()->flow->get_steps( $flow_id );

		$next_step_id    = false;
		$next_step_index = false;

		if ( empty( $steps ) ) {
			return $next_step_id;
		} else {
			$next_no_step = get_post_meta( $step_id, 'wcf-no-next-step', true );
			if ( ! empty( $next_no_step ) ) {
				$next_step_id = $next_no_step;
			} else {
				foreach ( $steps as $i => $step ) {

					if ( intval( $step['id'] ) === $step_id ) {

						$next_step_index = $i + 1;

						if ( $next_step_index && isset( $steps[ $next_step_index ] ) ) {

							$next_step_id = $steps[ $next_step_index ]['id'];
						}

						break;
					}
				}
			}
		}

		return $next_step_id;
	}

	/**
	 * Get thank you page URL.
	 *
	 * @since 1.0.0
	 * @param int   $step_id step ID.
	 * @param array $data order data.
	 *
	 * @return string
	 */
	function get_thankyou_page_url( $step_id, $data ) {

		$step_id     = intval( $step_id );
		$link        = '#';
		$thankyou_id = false;
		$flow_id     = get_post_meta( $step_id, 'wcf-flow-id', true );
		$order_id    = $data['order_id'];
		$order_key   = $data['order_key'];

		if ( ! $flow_id ) {
			return $link;
		}

		$thankyou_id = $this->get_thankyou_page_id( $flow_id, $step_id );

		if ( $thankyou_id ) {

			$this->may_be_complete_order( $thankyou_id, $order_id );

			$link = add_query_arg(
				array(
					'wcf-order' => $order_id,
					'wcf-key'   => $order_key,
				),
				get_permalink( $thankyou_id )
			);
		}

		return $link;
	}

	/**
	 * Get thank you page ID.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow id.
	 * @param int $step_id step ID.
	 *
	 * @return int
	 */
	function get_thankyou_page_id( $flow_id, $step_id ) {

		$steps = wcf()->flow->get_steps( $flow_id );

		$thankyou_step_id    = false;
		$thankyou_step_index = false;

		if ( empty( $steps ) ) {
			return $thankyou_step_id;
		}

		foreach ( $steps as $i => $step ) {

			if ( 'thankyou' === $step['type'] ) {

				$thankyou_step_id = $step['id'];

				break;
			}
		}

		return $thankyou_step_id;
	}

	/**
	 * Check if upsell exists.
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 *
	 * @return bool
	 */
	function is_upsell_exists( $order ) {

		$flow_id = wcf()->utils->get_flow_id_from_order( $order->get_id() );

		if ( $flow_id ) {

			$navigation = false;

			$step_id = wcf()->utils->get_checkout_id_from_order( $order->get_id() );

			$next_step_id = wcf()->utils->get_next_step_id( $flow_id, $step_id );

			if ( $next_step_id && wcf()->utils->check_is_offer_page( $next_step_id ) ) {

				return true;
			}
		}

		return false;
	}

	/**
	 * Actions after offer charge completes.
	 *
	 * @since 1.0.0
	 * @param array $step_id step id.
	 * @param array $order_id order id.
	 * @param array $order_key order key.
	 * @param array $is_charge_success is charge successful.
	 *
	 * @return array
	 */
	public function after_offer_charge( $step_id, $order_id, $order_key, $is_charge_success = false ) {

		$result = array();

		$step_type = wcf()->utils->get_step_type( $step_id );

		if ( $is_charge_success ) {

			$order = wc_get_order( $order_id );

			$offer_product = wcf_pro()->utils->get_offer_data( $step_id );

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
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Flow_Frontend::get_instance();
