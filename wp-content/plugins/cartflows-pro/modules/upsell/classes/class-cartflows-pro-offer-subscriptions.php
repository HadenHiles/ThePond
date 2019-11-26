<?php
/**
 * Subscriptions
 *
 * @package cartflows
 */

/**
 * CartFlows offer subscriptions
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Offer_Subscriptions {

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

		add_action( 'cartflows_offer_accepted', array( $this, 'maybe_create_subscription' ), 10, 2 );
	}

	/**
	 *  Create WooCommerce subscription
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 * @param array $offer_product offer product data.
	 * @return void
	 */
	function maybe_create_subscription( $order, $offer_product ) {

		$product_id = $offer_product['id'];
		$product    = wc_get_product( $product_id );

		wcf()->logger->log( 'Subscription Product-' . $product_id );

		// If product is of subscription type.
		if ( $product instanceof WC_Product && ( $product->get_type() === 'subscription' || $product->get_type() === 'subscription_variation' ) ) {

			if ( is_user_logged_in() ) {
				$user_id = $order->get_user_id();
			} else {

				// Create new customer.
				$user_id      = ( null === $user_created ) ? $this->create_new_customer( $order->get_billing_email() ) : $user_created;
				$user_created = $user_id;
				$order->set_customer_id( $user_id );
				$order->save();
			}

			$transaction_id = $order->get_transaction_id();
			$start_date     = date( 'Y-m-d H:i:s' );
			$period         = WC_Subscriptions_Product::get_period( $product );
			$interval       = WC_Subscriptions_Product::get_interval( $product );
			$trial_period   = WC_Subscriptions_Product::get_trial_period( $product );
			$order_id       = $order->get_id();

			// Create Woo subscription.
			$subscription = wcs_create_subscription(
				array(
					'start_date'       => $start_date,
					'order_id'         => $order_id,
					'billing_period'   => $period,
					'billing_interval' => $interval,
					'customer_note'    => $order->get_customer_note(),
					'customer_id'      => $user_id,
				)
			);

			if ( ! empty( $subscription ) ) {

				// Add product to subscription.
				$subscription_item_id = $subscription->add_product( $product, 1 );

				$subscription = wcs_copy_order_address( $order, $subscription );

				// set subscription dates.
				$trial_end_date    = WC_Subscriptions_Product::get_trial_expiration_date( $product->get_id(), $start_date );
				$next_payment_date = WC_Subscriptions_Product::get_first_renewal_payment_date( $product->get_id(), $start_date );
				$end_date          = WC_Subscriptions_Product::get_expiration_date( $product->get_id(), $start_date );

				$subscription->update_dates(
					array(
						'trial_end'    => $trial_end_date,
						'next_payment' => $next_payment_date,
						'end'          => $end_date,
					)
				);

				// Set meta for order if product has trial period.
				if ( WC_Subscriptions_Product::get_trial_length( $product->get_id() ) > 0 ) {
					wc_add_order_item_meta( $subscription_item_id, '_has_trial', 'true' );
				}

				// Set payment method data.
				$subscription->set_payment_method( $order->get_payment_method() );
				$subscription->set_payment_method_title( $order->get_payment_method_title() );

				if ( ! empty( $user_id ) ) {
					update_post_meta( $subscription->get_id(), '_customer_user', $user_id );
				}

				// Complete payment.
				$subscription->payment_complete( $transaction_id );

				$subscription->calculate_totals();
				$subscription->save();

				do_action( 'cf_subscription_created', $subscription, $order );
			}
		}
	}


	/**
	 *  Create new customer.
	 *
	 * @since 1.0.0
	 * @param string $email user email.
	 * @return int
	 */
	public function create_new_customer( $email ) {

		if ( empty( $email ) ) {
			return false;
		}

		/**
		 * Try to get the user by the email provided, if present then process as user ID exists.
		 */
		$maybe_user = get_user_by( 'email', $email );
		if ( $maybe_user instanceof WP_User ) {
			return $maybe_user->ID;
		}
		$username = sanitize_user( current( explode( '@', $email ) ), true );

		// username has to be unique.
		$append     = 1;
		$o_username = $username;

		while ( username_exists( $username ) ) {
			$username = $o_username . $append;

			++ $append;
		}

		$password = wp_generate_password();

		$customer_id = wc_create_new_customer( $email, $username, $password );

		if ( ! empty( $customer_id ) ) {
			wp_set_current_user( $customer_id, $username );

			wc_set_customer_auth_cookie( $customer_id );
		}

		return $customer_id;

	}
}

Cartflows_Pro_Offer_Subscriptions::get_instance();
