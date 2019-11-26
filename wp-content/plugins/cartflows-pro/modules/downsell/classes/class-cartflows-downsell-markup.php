<?php
/**
 * Downsell markup
 *
 * @package cartflows
 */

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Downsell_Markup extends Cartflows_Pro_Base_Offer_Markup {


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

		add_action( 'wp_ajax_wcf_downsell_accepted', array( $this, 'process_downsell_accepted' ) );
		add_action( 'wp_ajax_nopriv_wcf_downsell_accepted', array( $this, 'process_downsell_accepted' ) );

		add_action( 'wp_ajax_wcf_downsell_rejected', array( $this, 'process_downsell_rejected' ) );
		add_action( 'wp_ajax_nopriv_wcf_downsell_rejected', array( $this, 'process_downsell_rejected' ) );

	}

	/**
	 * Process down sell acceptance.
	 */
	function process_downsell_accepted() {

		$nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_downsell_accepted' ) ) {
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
				'message'  => __( 'Downsell Payment Failed', 'cartflows-pro' ),
			);

			$order = wc_get_order( $order_id );

			$extra_data = array(
				'order_id'      => $order_id,
				'product_id'    => $product_id,
				'order_key'     => $order_key,
				'template_type' => 'downsell',
			);

			$result = $this->offer_accepted( $step_id, $extra_data, $result );
		}

		// send json.
		wp_send_json( $result );
	}

	/**
	 * Process down sell rejected.
	 */
	function process_downsell_rejected() {

		$nonce = filter_input( INPUT_POST, '_nonce', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_downsell_rejected' ) ) {
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
					'template_type' => 'downsell',
				);
			}

			$result = $this->offer_rejected( $step_id, $extra_data, $result );
		}

		// send json.
		wp_send_json( $result );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Downsell_Markup::get_instance();
