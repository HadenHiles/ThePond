<?php
/**
 * Pre Checkout Offer
 *
 * @package cartflows
 */

/**
 * Order Bump Product
 *
 * @since 1.0.0
 */
class Cartflows_Pre_Checkout_Offer_Product {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	protected static $instance;

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

		add_action( 'wp_footer', array( $this, 'wcf_add_pre_checkout_offer_modal' ), 99, 1 );
		add_action( 'wp_ajax_wcf_add_to_cart', array( $this, 'wcf_add_pre_checkout_product' ) );
		add_action( 'wp_ajax_nopriv_wcf_add_to_cart', array( $this, 'wcf_add_pre_checkout_product' ) );

		add_action( 'wc_ajax_wcf_validate_form', array( $this, 'wcf_precheckout_validate_form' ) );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'wcf_precheckout_offer_price_to_cart_item' ), 9999 );

	}

	/**
	 *  Validate form
	 */
	public function wcf_precheckout_validate_form() {

		$errors      = new \WP_Error();
		$posted_data = WC()->checkout->get_posted_data();

		// Update customer shipping and payment method to posted method.
		$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

		if ( is_array( $posted_data['shipping_method'] ) ) {
			foreach ( $posted_data['shipping_method'] as $i => $value ) {
				$chosen_shipping_methods[ $i ] = $value;
			}
		}

		WC()->session->set( 'chosen_shipping_methods', $chosen_shipping_methods );
		WC()->session->set( 'chosen_payment_method', $posted_data['payment_method'] );

		$this->validate_checkout( $posted_data, $errors );

		if ( ! empty( $errors->get_error_messages() ) ) {
			foreach ( $errors->get_error_messages() as $message ) {
				wc_add_notice( $message, 'error' );
			}
				echo $this->send_ajax_failure_response();

		} else {
			$response = array(
				'result' => 'success',
			);

			// unset( WC()->session->refresh_totals, WC()->session->reload_checkout );.
		}

		echo wp_send_json( $response );
		die();

	}

	/**
	 * Validates that the checkout has enough info to proceed.
	 *
	 * @since  3.0.0
	 * @param  array    $data   An array of posted data.
	 * @param  WP_Error $errors Validation errors.
	 */
	protected function validate_checkout( &$data, &$errors ) {
		$this->validate_posted_data( $data, $errors );
		WC()->checkout->check_cart_items();

		if ( empty( $data['woocommerce_checkout_update_totals'] ) && empty( $data['terms'] ) && ! empty( $_POST['terms-field'] ) ) { // WPCS: input var ok, CSRF ok.
			$errors->add( 'terms', __( 'Please read and accept the terms and conditions to proceed with your order.', 'woocommerce' ) );
		}

		if ( WC()->cart->needs_shipping() ) {
			$shipping_country = WC()->customer->get_shipping_country();

			if ( empty( $shipping_country ) ) {
				$errors->add( 'shipping', __( 'Please enter an address to continue.', 'woocommerce' ) );
			} elseif ( ! in_array( WC()->customer->get_shipping_country(), array_keys( WC()->countries->get_shipping_countries() ), true ) ) {
				/* translators: %s: shipping location */
				$errors->add( 'shipping', sprintf( __( 'Unfortunately <strong>we do not ship %s</strong>. Please enter an alternative shipping address.', 'woocommerce' ), WC()->countries->shipping_to_prefix() . ' ' . WC()->customer->get_shipping_country() ) );
			} else {
				$chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );

				foreach ( WC()->shipping()->get_packages() as $i => $package ) {
					if ( ! isset( $chosen_shipping_methods[ $i ], $package['rates'][ $chosen_shipping_methods[ $i ] ] ) ) {
						$errors->add( 'shipping', __( 'No shipping method has been selected. Please double check your address, or contact us if you need any help.', 'woocommerce' ) );
					}
				}
			}
		}

		do_action( 'woocommerce_after_checkout_validation', $data, $errors );
	}

	/**
	 * See if a fieldset should be skipped.
	 *
	 * @since 3.0.0
	 * @param string $fieldset_key Fieldset key.
	 * @param array  $data         Posted data.
	 * @return bool
	 */
	protected function maybe_skip_fieldset( $fieldset_key, $data ) {
		if ( 'shipping' === $fieldset_key && ( ! $data['ship_to_different_address'] || ! WC()->cart->needs_shipping_address() ) ) {
			return true;
		}

		if ( 'account' === $fieldset_key && ( is_user_logged_in() || ( ! WC()->checkout->is_registration_required() && empty( $data['createaccount'] ) ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Validates the posted checkout data based on field properties.
	 *
	 * @since  3.0.0
	 * @param  array    $data   An array of posted data.
	 * @param  WP_Error $errors Validation error.
	 */
	protected function validate_posted_data( &$data, &$errors ) {
		foreach ( WC()->checkout->get_checkout_fields() as $fieldset_key => $fieldset ) {

			$validate_fieldset = true;

			if ( $this->maybe_skip_fieldset( $fieldset_key, $data ) ) {
				$validate_fieldset = false;
			}

			foreach ( $fieldset as $key => $field ) {
				if ( ! isset( $data[ $key ] ) ) {
					continue;
				}
				$required    = ! empty( $field['required'] );
				$format      = array_filter( isset( $field['validate'] ) ? (array) $field['validate'] : array() );
				$field_label = isset( $field['label'] ) ? $field['label'] : '';

				switch ( $fieldset_key ) {
					case 'shipping':
						/* translators: %s: field name */
						$field_label = sprintf( __( 'Shipping %s', 'woocommerce' ), $field_label );
						break;
					case 'billing':
						/* translators: %s: field name */
						$field_label = sprintf( __( 'Billing %s', 'woocommerce' ), $field_label );
						break;
				}

				if ( in_array( 'postcode', $format, true ) ) {
					$country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
					$data[ $key ] = wc_format_postcode( $data[ $key ], $country );

					if ( $validate_fieldset && '' !== $data[ $key ] && ! WC_Validation::is_postcode( $data[ $key ], $country ) ) {
						switch ( $country ) {
							case 'IE':
								/* translators: %1$s: field name, %2$s finder.eircode.ie URL */
								$postcode_validation_notice = sprintf( __( '%1$s is not valid. You can look up the correct Eircode <a target="_blank" href="%2$s">here</a>.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>', 'https://finder.eircode.ie' );
								break;
							default:
								/* translators: %s: field name */
								$postcode_validation_notice = sprintf( __( '%s is not a valid postcode / ZIP.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' );
						}
						$errors->add( 'validation', apply_filters( 'woocommerce_checkout_postcode_validation_notice', $postcode_validation_notice, $country, $data[ $key ] ) );
					}
				}

				if ( in_array( 'phone', $format, true ) ) {
					if ( $validate_fieldset && '' !== $data[ $key ] && ! WC_Validation::is_phone( $data[ $key ] ) ) {
						/* translators: %s: phone number */
						$errors->add( 'validation', sprintf( __( '%s is not a valid phone number.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ) );
					}
				}

				if ( in_array( 'email', $format, true ) && '' !== $data[ $key ] ) {
					$email_is_valid = is_email( $data[ $key ] );
					$data[ $key ]   = sanitize_email( $data[ $key ] );

					if ( $validate_fieldset && ! $email_is_valid ) {
						/* translators: %s: email address */
						$errors->add( 'validation', sprintf( __( '%s is not a valid email address.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ) );
						continue;
					}
				}

				if ( '' !== $data[ $key ] && in_array( 'state', $format, true ) ) {
					$country      = isset( $data[ $fieldset_key . '_country' ] ) ? $data[ $fieldset_key . '_country' ] : WC()->customer->{"get_{$fieldset_key}_country"}();
					$valid_states = WC()->countries->get_states( $country );

					if ( ! empty( $valid_states ) && is_array( $valid_states ) && count( $valid_states ) > 0 ) {
						$valid_state_values = array_map( 'wc_strtoupper', array_flip( array_map( 'wc_strtoupper', $valid_states ) ) );
						$data[ $key ]       = wc_strtoupper( $data[ $key ] );

						if ( isset( $valid_state_values[ $data[ $key ] ] ) ) {
							// With this part we consider state value to be valid as well, convert it to the state key for the valid_states check below.
							$data[ $key ] = $valid_state_values[ $data[ $key ] ];
						}

						if ( $validate_fieldset && ! in_array( $data[ $key ], $valid_state_values, true ) ) {
							/* translators: 1: state field 2: valid states */
							$errors->add( 'validation', sprintf( __( '%1$s is not valid. Please enter one of the following: %2$s', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>', implode( ', ', $valid_states ) ) );
						}
					}
				}

				if ( $validate_fieldset && $required && '' === $data[ $key ] ) {
					/* translators: %s: field name */
					$errors->add( 'required-field', apply_filters( 'woocommerce_checkout_required_field_notice', sprintf( __( '%s is a required field.', 'woocommerce' ), '<strong>' . esc_html( $field_label ) . '</strong>' ), $field_label ) );
				}
			}
		}
	}

	/**
	 * If checkout failed during an AJAX call, send failure response.
	 */
	protected function send_ajax_failure_response() {
		if ( is_ajax() ) {
			// Only print notices if not reloading the checkout, otherwise they're lost in the page reload.
			if ( ! isset( WC()->session->reload_checkout ) ) {
				$messages = wc_print_notices( true );
			}

			$response = array(
				'result'   => 'failure',
				'messages' => isset( $messages ) ? $messages : '',
				'refresh'  => isset( WC()->session->refresh_totals ),
				'reload'   => isset( WC()->session->reload_checkout ),
			);

			unset( WC()->session->refresh_totals, WC()->session->reload_checkout );

			wp_send_json( $response );
		}
	}

	/**
	 *  Add to cart.
	 */
	public function wcf_add_pre_checkout_product() {

		$pre_checkout_offer_price   = '';
		$checkout_id                = intval( $_POST['checkout_id'] );
		$pre_checkout_offer_product = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-product', true );
		$product_quantity           = intval( $_POST['product_quantity'] );
		$discount_type              = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-discount', true );
		$discount_value             = floatval( get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-discount-value', true ) );

		if ( isset( $pre_checkout_offer_product ) && ! empty( $pre_checkout_offer_product ) ) {

			$product_id = $pre_checkout_offer_product[0];
		} else {
			$data = array(
				'error'   => true,
				'message' => __( 'Product not Found', 'cartflows-pro' ),
			);
			echo wp_send_json( $data );
			wp_die();
		}

		$_product = wc_get_product( $product_id );
		if ( $_product->is_type( 'variable' ) ) {

			$default_attributes = $_product->get_default_attributes();

			if ( ! empty( $default_attributes ) ) {

				foreach ( $_product->get_children() as $c_in => $variation_id ) {

					if ( 0 === $c_in ) {
						$product_id = $variation_id;
					}

					$single_variation = new WC_Product_Variation( $variation_id );

					if ( $default_attributes == $single_variation->get_attributes() ) {

						$product_id = $variation_id;
						break;
					}
				}
			} else {

				$product_childrens = $_product->get_children();

				if ( is_array( $product_childrens ) ) {

					foreach ( $product_childrens  as $c_in => $c_id ) {

						$product_id = $c_id;
						break;
					}
				}
			}
		}

		$_product = wc_get_product( $product_id );

		if ( ! $_product->is_in_stock() ) {
			$data = array(
				'error'   => true,
				'message' => __( 'Product is Out of Stock', 'cartflows-pro' ),
			);
			echo wp_send_json( $data );
			wp_die();
		}

		if ( ! empty( $_product->get_sale_price() ) ) {

			$_product_price = floatval( $_product->get_sale_price() );
		} else {
			$_product_price = floatval( $_product->get_regular_price() );
		}

		if ( '' !== $discount_type && $discount_value > 0 ) {
			$original_product_price = floatval( $_product->get_regular_price() );
			$_product_price         = $this->calculate_pre_checkout_discount( $discount_type, $discount_value, $original_product_price );
		}

		$cart_item_data = array(
			'cartflows_pre_checkout_offer' => true,
		);

		if ( '' !== $_product_price ) {

				$cart_item_data = array(
					'pre_checkout_offer_price'     => $_product_price,
					'cartflows_pre_checkout_offer' => true,
				);
		}

		$cart_hash = WC()->cart->add_to_cart( $product_id, $product_quantity, 0, array(), $cart_item_data );

			/*
			WC()->cart->calculate_totals();.
			$fragments = WC_AJAX::get_refreshed_fragments();
			*/
		if ( ! empty( $cart_hash ) ) {
			$data = array(
				'error'     => false,
				'cart_hash' => $cart_hash,
				'message'   => __( 'Product Added Successfully', 'cartflows-pro' ),
			);
		} else {
			$data = array(
				'error'     => true,
				'cart_hash' => $cart_hash,
				'message'   => __( 'Product is Out of Stock', 'cartflows-pro' ),
			);
		}

			echo wp_send_json( $data );

			wp_die();

	}

	/**
	 * Calculate discount for product.
	 *
	 * @param string $discount_type discount type.
	 * @param int    $discount_value discount value.
	 * @param int    $_product_price product price.
	 * @return int
	 * @since 1.1.5
	 */
	function calculate_pre_checkout_discount( $discount_type, $discount_value, $_product_price ) {

		$custom_price = '';

		if ( ! empty( $discount_type ) ) {
			if ( 'discount_percent' === $discount_type ) {

				if ( $discount_value > 0 ) {
					$custom_price = $_product_price - ( ( $_product_price * $discount_value ) / 100 );
				}
			} elseif ( 'discount_price' === $discount_type ) {

				if ( $discount_value > 0 ) {
					$custom_price = $_product_price - $discount_value;
				}
			}
		}

		return $custom_price;
	}

		/**
		 * Preserve the custom item price added by Variations & Quantity feature
		 *
		 * @param array $cart_object cart object.
		 * @since 1.0.0
		 */
	function wcf_precheckout_offer_price_to_cart_item( $cart_object ) {

		if ( wp_doing_ajax() && ! WC()->session->__isset( 'reload_checkout' ) ) {

			foreach ( $cart_object->cart_contents as $key => $value ) {

				if ( isset( $value['pre_checkout_offer_price'] ) && '' !== $value['pre_checkout_offer_price'] ) {

					$value['data']->set_price( $value['pre_checkout_offer_price'] );
				}
			}
		}
	}

	/**
	 *  Pre checkout Offer Modal.
	 *
	 * @param type $checkout object.
	 */
	public function wcf_add_pre_checkout_offer_modal( $checkout ) {
		global $post;

		$output = '';
		$src    = '';

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			return;
		}

		if ( 0 !== $checkout_id ) {

			$pre_checkout_offer = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer', true );

			if ( 'yes' !== $pre_checkout_offer ) {
				return;
			}

			$pre_checkout_offer_product = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-product', true );
			if ( isset( $pre_checkout_offer_product ) && ! empty( $pre_checkout_offer_product ) ) {

				$pre_checkout_offer_product_id = $pre_checkout_offer_product[0];
			} else {
				return;
			}

			if ( ! empty( $pre_checkout_offer_product ) ) {

				$src      = get_the_post_thumbnail_url( $pre_checkout_offer_product_id );
				$_product = wc_get_product( $pre_checkout_offer_product_id );

				if ( $_product->is_type( 'variable' ) ) {
					$parent_id = $pre_checkout_offer_product_id;

					$default_attributes = $_product->get_default_attributes();

					if ( ! empty( $default_attributes ) ) {

						foreach ( $_product->get_children() as $c_in => $variation_id ) {

							if ( 0 === $c_in ) {
								$pre_checkout_offer_product_id = $variation_id;
							}

							$single_variation = new WC_Product_Variation( $variation_id );

							if ( $default_attributes == $single_variation->get_attributes() ) {

								$pre_checkout_offer_product_id = $variation_id;
								break;
							}
						}
					} else {

						$product_childrens = $_product->get_children();

						if ( is_array( $product_childrens ) ) {

							foreach ( $product_childrens  as $c_in => $c_id ) {

								$pre_checkout_offer_product_id = $c_id;
								break;
							}
						}
					}

					$child_src = get_the_post_thumbnail_url( $pre_checkout_offer_product_id );

					if ( empty( $child_src ) ) {
						$src = get_the_post_thumbnail_url( $parent_id );
					} else {
						$src = $child_src;
					}
				}

				$_product = wc_get_product( $pre_checkout_offer_product_id );
				if ( ! $_product->is_in_stock() ) {
						return;
				}

				if ( ! $src ) {
					$src = wc_placeholder_img_src();

				}

				$pre_checkout_popup_title         = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-popup-title', true );
				$pre_checkout_popup_sub_title     = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-popup-sub-title', true );
				$pre_checkout_popup_btn_text      = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-popup-btn-text', true );
				$pre_checkout_popup_skip_btn_text = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-popup-skip-btn-text', true );

				$pre_checkout_product_title = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-product-title', true );

				if ( empty( $pre_checkout_product_title ) ) {
					$product_title = get_the_title( $pre_checkout_offer_product_id );

				} else {
					$product_title = $pre_checkout_product_title;
				}

				if ( empty( $pre_checkout_popup_title ) ) {
					$pre_checkout_popup_title = __( '{first_name}, Wait! Your Order Is Almost Complete...', 'cartflows-pro' );

				}

				if ( empty( $pre_checkout_popup_btn_text ) ) {
					$pre_checkout_popup_btn_text = __( 'Yes, Add to My Order!', 'cartflows-pro' );

				}

				if ( empty( $pre_checkout_popup_skip_btn_text ) ) {
					$pre_checkout_popup_skip_btn_text = __( 'No, thanks!', 'cartflows-pro' );

				}

				$discount_type  = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-discount', true );
				$discount_value = floatval( get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-discount-value', true ) );

				if ( ! empty( $_product->get_sale_price() ) ) {

					$_product_price = floatval( $_product->get_sale_price() );
				} else {
					$_product_price = floatval( $_product->get_regular_price() );
				}

				if ( ! empty( $discount_type ) && $discount_value > 0 ) {
					$original_product_price = floatval( $_product->get_regular_price() );
					$_product_price         = $this->calculate_pre_checkout_discount( $discount_type, $discount_value, $original_product_price );
					$price_html             = wc_format_sale_price( $original_product_price, $_product_price );
				} else {

					$price_html = $_product->get_price_html();
				}

				$product_description = get_post_meta( $checkout_id, 'wcf-pre-checkout-offer-desc', true );

				if ( empty( $product_description ) ) {
					$product_description = __( 'Write a few words about this awesome product and tell shoppers why they must get it. You may highlight this as "one time offer" and make it irresistible.', 'cartflows-pro' );
				}

				ob_start();
				include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/pre-checkout-offer/pre-checkout-offer.php';

				$output .= ob_get_clean();

				echo $output;

			}
		}
	}
}


/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pre_Checkout_Offer_Product::get_instance();
