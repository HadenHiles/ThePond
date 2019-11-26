<?php
/**
 * Upsell markup.
 *
 * @package cartflows
 */

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Upsell_Markup extends Cartflows_Pro_Base_Offer_Markup {


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

		/* Add or Cancel Upsell Product */
		add_action( 'wp_ajax_wcf_upsell_accepted', array( $this, 'process_upsell_accepted' ) );
		add_action( 'wp_ajax_nopriv_wcf_upsell_accepted', array( $this, 'process_upsell_accepted' ) );

		add_action( 'wp_ajax_wcf_upsell_rejected', array( $this, 'process_upsell_rejected' ) );
		add_action( 'wp_ajax_nopriv_wcf_upsell_rejected', array( $this, 'process_upsell_rejected' ) );

	}

	/**
	 *  Process upsell acceptance
	 *
	 * @return void
	 */
	function process_upsell_accepted() {

		$nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_upsell_accepted' ) ) {
			return;
		}

		$offer_action = sanitize_text_field( $_POST['offer_action'] );
		$step_id      = intval( $_POST['step_id'] );
		$product_id   = intval( $_POST['product_id'] );
		$order_id     = sanitize_text_field( $_POST['order_id'] );
		$order_key    = sanitize_text_field( $_POST['order_key'] );

		$result = array(
			'status'   => 'failed',
			'redirect' => '#',
			'message'  => __( 'Order does not exist', 'cartflows-pro' ),
		);

		if ( $order_id && $product_id ) {

			$result = array(
				'status'   => 'failed',
				'redirect' => '#',
				'message'  => __( 'Upsell Payment Failed', 'cartflows-pro' ),
			);

			$extra_data = array(
				'order_id'      => $order_id,
				'product_id'    => $product_id,
				'order_key'     => $order_key,
				'template_type' => 'upsell',
			);

			$result = $this->offer_accepted( $step_id, $extra_data, $result );
		}

		// send json.
		wp_send_json( $result );
	}

	/**
	 *  Process upsell rejection
	 *
	 * @return void
	 */
	function process_upsell_rejected() {

		$nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_upsell_rejected' ) ) {
			return;
		}

		$step_id   = intval( $_POST['step_id'] );
		$order_id  = sanitize_text_field( $_POST['order_id'] );
		$order_key = sanitize_text_field( $_POST['order_key'] );

		$result = array(
			'status'   => 'failed',
			'redirect' => '#',
			'message'  => __( 'Current Step Not Found', 'cartflows-pro' ),
		);

		if ( $step_id ) {

			$result = array(
				'status'   => 'failed',
				'redirect' => '#',
				'message'  => __( 'Order does not exist', 'cartflows-pro' ),
			);

			if ( $order_id ) {

				$extra_data = array(
					'action'        => 'offer_rejected',
					'order_id'      => $order_id,
					'order_key'     => $order_key,
					'template_type' => 'upsell',
				);

				$result = $this->offer_rejected( $step_id, $extra_data, $result );
			}
		}

		// send json.
		wp_send_json( $result );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Upsell_Markup::get_instance();
