<?php
/**
 * Cartflows Gateways.
 *
 * @package cartflows
 */

/**
 * Class Cartflows_Pro_Gateways.
 */
class Cartflows_Pro_Gateways {

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	public $gateway_obj = array();

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

		add_filter( 'wp_loaded', array( $this, 'load_required_integrations' ), 20 );

		add_action( 'wp_ajax_nopriv_cartflows_front_create_express_checkout_token', array( $this, 'generate_express_checkout_token' ), 10 );
		add_action( 'wp_ajax_cartflows_front_create_express_checkout_token', array( $this, 'generate_express_checkout_token' ), 10 );

		add_action( 'wp_ajax_nopriv_cartflows_front_create_ppec_paypal_checkout_token', array( $this, 'generate_ppec_paypal_checkout_token' ), 10 );
		add_action( 'wp_ajax_cartflows_front_create_ppec_paypal_checkout_token', array( $this, 'generate_ppec_paypal_checkout_token' ), 10 );

		/**
		 * Paypal Standard API calls response and process billing agreement creation
		 */
		add_action( 'woocommerce_api_cartflows_paypal', array( $this, 'maybe_handle_paypal_api_call' ) );

		/**
		 * Paypal Express API calls response and process billing agreement creation
		 */
		add_action( 'woocommerce_api_cartflows_ppec_paypal', array( $this, 'maybe_handle_ppec_paypal_api_call' ) );

		/**
		 *  Add actions and filters for Angelleye.
		 */

		do_action( 'cartflows_add_offer_payment_gateway_actions' );

	}

	/**
	 * Load required gateways.
	 *
	 * @since 1.0.0
	 * @return array.
	 */
	function load_required_integrations() {

		$gateways = $this->get_supported_gateways();

		if ( is_array( $gateways ) ) {

			foreach ( $gateways as $key => $gateway ) {

				$this->load_gateway( $key );
			}
		}

		return $gateways;
	}

	/**
	 * Load Gateway.
	 *
	 * @param string $type gateway type.
	 * @since 1.0.0
	 * @return array.
	 */
	function load_gateway( $type ) {

		$gateways = $this->get_supported_gateways();

		if ( isset( $gateways[ $type ] ) ) {

			$temp_gateway = $gateways[ $type ];
			$gateway_path = isset( $temp_gateway['path'] ) ? $temp_gateway['path'] : CARTFLOWS_PRO_DIR . 'modules/gateways/class-cartflows-pro-gateway-' . $temp_gateway['file'];
			if ( ! file_exists( $gateway_path ) ) {
				return false;
			}
			include_once $gateway_path;
			$class_name = $temp_gateway['class'];

			$this->gateway_obj[ $class_name ] = call_user_func( array( $class_name, 'get_instance' ) );

			return $this->gateway_obj[ $class_name ];
		}

		return false;
	}

	/**
	 * Generates express checkout token
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	function generate_express_checkout_token() {
		$this->load_gateway( 'paypal' )->generate_express_checkout_token();
	}

	/**
	 * Generates express checkout token
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	function generate_ppec_paypal_checkout_token() {
		$this->load_gateway( 'ppec_paypal' )->generate_express_checkout_token();
	}

	/**
	 * Get Supported Gateways.
	 *
	 * @since 1.0.0
	 * @return array.
	 */
	function get_supported_gateways() {

		$supported_gateways = array(
			'cod'         => array(
				'file'  => 'cod.php',
				'class' => 'Cartflows_Pro_Gateway_Cod',
			),
			'stripe'      => array(
				'file'  => 'stripe.php',
				'class' => 'Cartflows_Pro_Gateway_Stripe',
			),
			'paypal'      => array(
				'file'  => 'paypal-standard.php',
				'class' => 'Cartflows_Pro_Gateway_Paypal_Standard',
			),
			'ppec_paypal' => array(
				'file'  => 'paypal-express.php',
				'class' => 'Cartflows_Pro_Gateway_Paypal_Express',
			),
		);

		return apply_filters( 'cartflows_offer_supported_payment_gateways', $supported_gateways );

	}

	/**
	 * Handles paypal API call
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	function maybe_handle_paypal_api_call() {

		$this->load_gateway( 'paypal' )->create_billing_agreement();
		$this->load_gateway( 'paypal' )->process_api_calls();
	}

	/**
	 * Handles ppec_paypal API call
	 *
	 * @since 1.0.0
	 * @return void.
	 */
	function maybe_handle_ppec_paypal_api_call() {

		$this->load_gateway( 'ppec_paypal' )->create_billing_agreement();
		$this->load_gateway( 'ppec_paypal' )->process_api_calls();
	}
}

/**
 *  Prepare if class 'Cartflows_Pro_Gateways' exist.
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Gateways::get_instance();
