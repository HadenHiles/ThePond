<?php
/**
 * Action call to remove the astra two step action hooks.
 *
 * @since X.X.X
 *
 * @package CartFlows
 */

add_action( 'cartflows_checkout_before_shortcode', 'cartflows_theme_compatibility_astra' );

if ( ! function_exists( 'cartflows_theme_compatibility_astra' ) ) {

	/**
	 * Function to remove the astra hooks.
	 *
	 * @since X.X.X
	 *
	 * @return void
	 */
	function cartflows_theme_compatibility_astra() {
		remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_wrapper_div', 1 );
		remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_form_ul_wrapper', 2 );
		remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_div_wrapper_close', 30 );
		remove_action( 'woocommerce_checkout_order_review', 'astra_woocommerce_ul_close', 30 );
		remove_action( 'woocommerce_checkout_before_customer_details', 'astra_two_step_checkout_address_li_wrapper', 5 );
		remove_action( 'woocommerce_checkout_after_customer_details', 'astra_woocommerce_li_close' );
		remove_action( 'woocommerce_checkout_before_order_review', 'astra_two_step_checkout_order_review_wrap', 1 );
		remove_action( 'woocommerce_checkout_after_order_review', 'astra_woocommerce_li_close', 40 );
	}
}
