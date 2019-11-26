<?php
/**
 * CartFlows Orders
 *
 * @package CartFlows
 * @since 1.0.0
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Orders {


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
	 *  Constructor
	 */
	public function __construct() {

		/* Register New Order Status */
		add_filter( 'woocommerce_register_shop_order_post_statuses', array( $this, 'register_new_order_status' ), 99 );

		/* Add order Status to WooCommerce options */
		add_filter( 'wc_order_statuses', array( $this, 'update_to_native_stauses' ), 99 );

		add_action( 'carflows_schedule_normalize_order_status', array( $this, 'schedule_normalize_order_status' ), 99, 3 );

		/* Order Status to main order */
		add_action( 'cartflows_order_started', array( $this, 'register_order_status_to_main_order' ), 10 );

	}

	/**
	 * Get order status slug.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function _get_order_status_slug() {

		return 'wc-wcf-main-order';
	}

	/**
	 * Get order status title.
	 *
	 * @since 1.0.0
	 *
	 * @return string
	 */
	function _get_order_status_title() {

		return _x( 'Main Order Accepted', 'Order status', 'cartflows-pro' );
	}

	/**
	 * Register new order status.
	 *
	 * @since 1.0.0
	 * @param string $order_status order status.
	 *
	 * @return array
	 */
	function register_new_order_status( $order_status ) {

		$order_status_title = $this->_get_order_status_title();

		$order_status[ $this->_get_order_status_slug() ] = array(
			'label'                     => $order_status_title,
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			/* translators: %s: Single count value */
			'label_count'               => _n_noop( 'Main Order Accepted <span class="count">(%s)</span>', 'Main Order Accepted <span class="count">(%s)</span>', 'cartflows-pro' ),
		);

		return $order_status;
	}

	/**
	 * Update native statuses.
	 *
	 * @since 1.0.0
	 * @param string $order_status Order status.
	 *
	 * @return array
	 */
	function update_to_native_stauses( $order_status ) {

		$order_status[ $this->_get_order_status_slug() ] = $this->_get_order_status_title();

		return $order_status;
	}

	/**
	 * Add upsell product and order meta.
	 *
	 * @since 1.0.0
	 * @param array $order order.
	 * @param array $upsell_product upsell product.
	 * @return void
	 */
	function add_upsell_product( $order, $upsell_product ) {

		$item_id = $order->add_product( wc_get_product( $upsell_product['id'] ), $upsell_product['qty'], $upsell_product['args'] );

		wc_add_order_item_meta( $item_id, '_cartflows_upsell', 'yes' );
		wc_add_order_item_meta( $item_id, '_cartflows_step_id', $upsell_product['step_id'] );

		$order->calculate_totals();
	}

	/**
	 * Add downsell product.
	 *
	 * @since 1.0.0
	 * @param array $order order.
	 * @param array $downsell_product downsell product.
	 * @return void
	 */
	function add_downsell_product( $order, $downsell_product ) {

		$item_id = $order->add_product( wc_get_product( $downsell_product['id'] ), $downsell_product['qty'], $downsell_product['args'] );

		wc_add_order_item_meta( $item_id, '_cartflows_downsell', 'yes' );
		wc_add_order_item_meta( $item_id, '_cartflows_step_id', $downsell_product['step_id'] );

		$order->calculate_totals();
	}

	/**
	 * Normalize order status.
	 *
	 * @since 1.0.0
	 * @param array $order order.
	 * @return void
	 */
	function may_be_normalize_status( $order ) {

		wcf()->logger->log( 'Entering: ' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->log( 'Order status: ' . $order->get_status() );

		/* @todo : Check if it is our status */
		$flow_id = wcf()->utils->get_flow_id_from_order( $order->get_id() );

		$before_normal = 'pending';
		$normal_status = 'processing';

		$session_data = wcf_pro()->session->get_data( $flow_id );

		if ( $session_data ) {

			$before_normal = $session_data['before_normal'];
			$normal_status = $session_data['normal_status'];
		}

		$this->do_normalize_status( $order, $before_normal, $normal_status );
	}

	/**
	 * Normalize order status.
	 *
	 * @since 1.0.0
	 * @param array  $order order.
	 * @param string $before_normal before status.
	 * @param string $normal_status normal status.
	 * @return void
	 */
	function do_normalize_status( $order, $before_normal = 'pending', $normal_status = 'processing' ) {

		wcf()->logger->log( 'Entering: ' . __CLASS__ . '::' . __FUNCTION__ );
		wcf()->logger->log( 'Before Normal: ' . $before_normal );
		wcf()->logger->log( 'Normal: ' . $normal_status );

		if ( false === is_a( $order, 'WC_Order' ) ) {
			return;
		}

		$current_status = $order->get_status();

		if ( 'wcf-main-order' !== $current_status ) {
			return;
		}

		/* Setup Beofore Normal Status */
		$order->update_status( $before_normal );

		$normal_status = apply_filters( 'wcf_order_status_after_order_complete', $normal_status, $order );

		/* Setup Normal Staus */
		$order->update_status( $normal_status );
	}

	/**
	 * Check if order is active.
	 *
	 * @since 1.0.0
	 * @return bool
	 */
	function is_main_order_active() {

		if ( isset( $_GET['wcf-order'] ) && isset( $_GET['wcf-key'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Schedule normalize order status.
	 *
	 * @since 1.0.0
	 * @param int    $order_id order id.
	 * @param string $before_normal before status.
	 * @param string $normal_status normal status.
	 * @return void
	 */
	function schedule_normalize_order_status( $order_id, $before_normal, $normal_status ) {

		$order = wc_get_order( $order_id );

		$this->do_normalize_status( $order, $before_normal, $normal_status );
	}

	/**
	 * Register order status to main order.
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 * @return void
	 */
	function register_order_status_to_main_order( $order ) {

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return;
		}

		if ( 'cod' === $order->get_payment_method() ) {
			return;
		}

		add_filter( 'woocommerce_payment_complete_order_status', array( $this, 'maybe_set_completed_order_status' ), 999, 3 );
	}

	/**
	 * Set order status to complete.
	 *
	 * @since 1.0.0
	 * @param string $order_status order status.
	 * @param int    $id order id.
	 * @param array  $order order data.
	 * @return string
	 */
	function maybe_set_completed_order_status( $order_status, $id, $order ) {

		wcf()->logger->log( __CLASS__ . '::maybe_set_completed_order_status' );

		if ( ! is_a( $order, 'WC_Order' ) ) {
			return $order_status;
		}

		remove_filter( 'woocommerce_payment_complete_order_status', array( $this, 'maybe_set_completed_order_status' ), 999 );

		$new_status = $this->_get_order_status_slug();

		/**
		 * $new_status = our new status
		 * $order_status = default status change
		 */
		do_action( 'cartflows_order_status_change_to_main_order', $new_status, $order_status, $order );

		return $this->_get_order_status_slug();

	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Orders::get_instance();
