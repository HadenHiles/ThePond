<?php
/**
 * Cod Gateway.
 *
 * @package cartflows
 */

/**
 * Class Cartflows_Pro_Gateway_Cod.
 */
class Cartflows_Pro_Gateway_Cod {

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

		add_filter( 'woocommerce_cod_process_payment_order_status', array( $this, 'maybe_setup_upsell_cod' ), 999, 2 );
	}

	/**
	 * Loads module files.
	 *
	 * @since 1.0.0
	 * @param string $order_status order status.
	 * @param array  $order order data.
	 * @return string
	 */
	function maybe_setup_upsell_cod( $order_status, $order ) {

		wcf()->logger->log( 'COD Process payment order status called woocommerce_cod_process_payment_order_status' );

		if ( false === is_a( $order, 'WC_Order' ) ) {

			// Create Log.
			wcf()->logger->log( 'Not a valid order' );

			return $order_status;
		}

		$flow_id = get_post_meta( $order->get_id(), '_wcf_flow_id', true );

		if ( ! wcf_pro()->flow->is_upsell_exists( $order ) ) {

			wcf()->logger->log( 'Flow-' . $flow_id . ' Order-' . $order->get_id() . ' Upsell not exists' );

			return $order_status;
		}

		do_action( 'cartflows_order_started', $order );

		$new_status = wcf_pro()->order->_get_order_status_slug();

		$data = array(
			'flow_id'  => $flow_id,
			'order_id' => $order->get_id(),
		);

		wcf_pro()->session->set_session( $flow_id, $data );

		/**
		 * $new_status = our new status
		 * $order_status = default status change
		 */
		do_action( 'cartflows_order_status_change_to_main_order', $new_status, $order_status, $order );

		wcf()->logger->log( 'Flow-' . $flow_id . ' Order-' . $order->get_id() . ' Status changed to Main Order' );

		return $new_status;
	}

	/**
	 * Process offer payment
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 * @param array $product product data.
	 * @return bool
	 */
	public function process_offer_payment( $order, $product ) {

		return true;
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateway_Cod' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateway_Cod::get_instance();
