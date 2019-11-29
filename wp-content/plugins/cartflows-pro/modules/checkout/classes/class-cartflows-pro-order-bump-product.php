<?php
/**
 * Bump order
 *
 * @package cartflows
 */

/**
 * Order Bump Product
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Order_Bump_Product {


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

		add_action( 'cartflows_checkout_before_shortcode', array( $this, 'load_actions' ) );

		/* Add or Cancel Bump Product */
		add_action( 'wp_ajax_wcf_bump_order_process', array( $this, 'order_bump_process' ) );
		add_action( 'wp_ajax_nopriv_wcf_bump_order_process', array( $this, 'order_bump_process' ) );

		add_shortcode( 'cartflows_bump_product_title', array( $this, 'bump_product_title' ) );

		add_action( 'woocommerce_before_calculate_totals', array( $this, 'custom_price_to_cart_item' ), 9999 );

	}

	/**
	 * Load Actions
	 *
	 * @param int $checkout_id checkout id.
	 */
	function load_actions( $checkout_id ) {

		$position = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-position' );

		if ( 'before-checkout' === $position ) {
			/* Before CHeckout Form */
			add_action( 'woocommerce_before_checkout_form', array( $this, 'bump_order' ) );
		}

		if ( 'after-customer' === $position ) {
			/* After customer details */
			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'bump_order' ) );
		}

		if ( 'after-payment' === $position ) {
			/* After payment Selection */
			add_action( 'woocommerce_review_order_before_submit', array( $this, 'bump_order' ) );
		}

		if ( 'after-order' === $position ) {
			/* Position After Order */
			add_action( 'woocommerce_checkout_order_review', array( $this, 'bump_order' ), 11 );
		}

		$order_bump = get_post_meta( $checkout_id, 'wcf-order-bump', true );

		if ( 'yes' === $order_bump ) {
			add_action( 'woocommerce_checkout_after_order_review', array( $this, 'add_order_bump_hidden_fields' ), 99 );
		}
	}

	/**
	 *  Display bump offer box html.
	 */
	function add_order_bump_hidden_fields() {
		echo '<input type="hidden" name="_wcf_bump_product_action" value="">';
		echo '<input type="hidden" name="_wcf_bump_product" value="">';
	}

	/**
	 * Get order bump hidden data.
	 *
	 * @param int     $product_id product id.
	 * @param boolean $order_bump_checked checked value.
	 */
	function get_order_bump_hidden_data( $product_id, $order_bump_checked ) {

		$bump_product_id = $order_bump_checked ? $product_id : '';

		echo '<input type="hidden" name="wcf_bump_product_id" class="wcf-bump-product-id" value="' . $product_id . '">';
		echo '<input type="hidden" name="_wcf_bump_product" value="' . $bump_product_id . '">';
	}

	/**
	 *  Display bump offer box html.
	 */
	function bump_order() {

		global $post;

		$output = '';

		if ( _is_wcf_checkout_type() ) {

			$order_bump                  = get_post_meta( $post->ID, 'wcf-order-bump', true );
			$order_bump_product          = get_post_meta( $post->ID, 'wcf-order-bump-product', true );
			$order_bump_product_quantity = get_post_meta( $post->ID, 'wcf-order-bump-product-quantity', true );

			if ( 'yes' !== $order_bump ) {

				return;
			}

			if ( empty( $order_bump_product ) ) {

				$flow_id = wcf()->utils->get_flow_id_from_step_id( $post->ID );

				if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {
					$order_bump_product = $this->get_bump_test_product( $post->ID );
				} else {
					return;
				}
			}

			$bump_layout        = get_post_meta( $post->ID, 'wcf-order-bump-style', true );
			$order_bump_label   = get_post_meta( $post->ID, 'wcf-order-bump-label', true );
			$order_bump_hl_text = get_post_meta( $post->ID, 'wcf-order-bump-hl-text', true );
			$order_bump_desc    = get_post_meta( $post->ID, 'wcf-order-bump-desc', true );

			$order_bump_prd_title = get_post_meta( $post->ID, 'wcf-checkout-products', true );

			$product_id         = reset( $order_bump_product );
			$order_bump_checked = false;

			$discount_type    = get_post_meta( $post->ID, 'wcf-order-bump-discount', true );
			$discount_value   = get_post_meta( $post->ID, 'wcf-order-bump-discount-value', true );
			$discount_coupon  = get_post_meta( $post->ID, 'wcf-order-bump-discount-coupon', true );
			$bump_order_image = get_post_meta( $post->ID, 'wcf-order-bump-image', true );

			if ( ! empty( $_POST['post_data'] ) ) {

				$post_data = array();

				parse_str( $_POST['post_data'], $post_data );

				if ( ! empty( $post_data['wcf-bump-order-cb'] ) ) {
					$order_bump_checked = true;
				}

				$post_data = null;
			}

			// Chcek if bump order already added in the cart.
			if ( $this->cart_has_product( $product_id, true ) ) {
				$order_bump_checked = true;
			}

			$bump_offer_arr = array(
				'product_id'       => $product_id,
				'product_quantity' => $order_bump_product_quantity,
				'discount_type'    => $discount_type,
				'discount_value'   => $discount_value,
				'discount_coupon'  => $discount_coupon,
				'parent_id'        => $product_id,
				'is_variable'      => 'no',
				'is_variation'     => 'no',
			);

			$_product = wc_get_product( $product_id );

			if ( ! empty( $_product ) ) {

				if ( $_product->is_type( 'variable' ) ) {

					$bump_offer_arr['is_variable'] = 'yes';
					$bump_offer_arr['parent_id']   = $product_id;

					$default_attributes = $_product->get_default_attributes();

					if ( ! empty( $default_attributes ) ) {

						foreach ( $_product->get_children() as $c_in => $variation_id ) {

							if ( 0 === $c_in ) {
								$bump_offer_arr['product_id'] = $variation_id;
							}

							$single_variation = new WC_Product_Variation( $variation_id );

							if ( $default_attributes == $single_variation->get_attributes() ) {

								$bump_offer_arr['product_id'] = $variation_id;
								break;
							}
						}
					} else {

						$product_childrens = $_product->get_children();

						if ( is_array( $product_childrens ) ) {

							foreach ( $product_childrens  as $c_in => $c_id ) {

								$bump_offer_arr['product_id'] = $c_id;
								break;
							}
						}
					}
				}

				if ( $_product->is_type( 'variation' ) ) {

					$bump_offer_arr['is_variation'] = 'yes';
					$bump_offer_arr['parent_id']    = $_product->get_parent_id();
				}
			}

			$bump_offer_data = json_encode( $bump_offer_arr );

			/* Set new ids based on variation */
			$product_id = $bump_offer_arr['product_id'];
			$parent_id  = $bump_offer_arr['parent_id'];

			$order_bump_pos = wcf()->options->get_checkout_meta_value( $post->ID, 'wcf-order-bump-position' );

			/* bump order blinking arrow */
			$is_order_bump_arrow_enabled      = wcf()->options->get_checkout_meta_value( $post->ID, 'wcf-show-bump-arrow' );
			$is_order_bump_arrow_anim_enabled = wcf()->options->get_checkout_meta_value( $post->ID, 'wcf-show-bump-animate-arrow' );

			$bump_order_blinking_arrow = '';
			$bump_order_arrow_animate  = '';

			if ( 'yes' === $is_order_bump_arrow_enabled ) {

				if ( 'yes' === $is_order_bump_arrow_anim_enabled ) {
					$bump_order_arrow_animate = 'wcf-blink';
				}

				$bump_order_blinking_arrow = '<svg version="1.1" class="wcf-pointing-arrow ' . $bump_order_arrow_animate . '" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" width="20px" height="15px" fill="red" viewBox="310 253 90 70" enable-background="new 310 253 90 70" xml:space="preserve"><g><g><path d="M364.348,253.174c-0.623,0.26-1.029,0.867-1.029,1.54v18.257h-51.653c-0.919,0-1.666,0.747-1.666,1.666v26.658
								c0,0.92,0.747,1.666,1.666,1.666h51.653v18.327c0,0.673,0.406,1.28,1.026,1.54c0.623,0.257,1.34,0.116,1.816-0.36l33.349-33.238 c0.313-0.313,0.49-0.737,0.49-1.18c0-0.443-0.177-0.866-0.487-1.179l-33.349-33.335 C365.688,253.058,364.971,252.915,364.348,253.174z"/></g></g></svg>';
			}
			/* bump order blinking arrow */

			/* Execute */
			$order_bump_desc = do_shortcode( $order_bump_desc );

			ob_start();
			if ( ! empty( $bump_layout ) ) {

				include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/bump-order/wcf-bump-order-' . $bump_layout . '.php';
			} else {
				include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/bump-order/wcf-bump-order-style-2.php';
			}

			$output .= ob_get_clean();

			// $output .= '<h1>Bump Order</h1>';
		}

		echo $output;
	}

	/**
	 * Process bump order.
	 */
	function order_bump_process() {

		$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_bump_order_process' ) ) {
			return;
		}

		$post_data = $_POST;

		if ( ! isset( $post_data['_wcf_bump_product_action'] ) ||
			( isset( $post_data['_wcf_bump_product_action'] ) && empty( $post_data['_wcf_bump_product_action'] ) )
		) {
			return;
		}

		$checkout_id = intval( $post_data['_wcf_checkout_id'] );
		$bump_action = sanitize_text_field( $post_data['_wcf_bump_product_action'] );

		if ( 'add_bump_product' === $bump_action ) {
			$checked = 'true';
		} elseif ( 'remove_bump_product' === $bump_action ) {
			$checked = 'false';
		} else {
			return;
		}

		$order_bump_data = $this->get_order_bump_data( $checkout_id );

		if ( empty( $order_bump_data ) ) {
			return;
		}

		/* Set new ids based on variation */
		$product_id = intval( $order_bump_data['product_id'] );
		$parent_id  = intval( $order_bump_data['parent_id'] );

		$is_variable  = sanitize_text_field( $order_bump_data['is_variable'] );
		$is_variation = sanitize_text_field( $order_bump_data['is_variation'] );

		$_product       = wc_get_product( $product_id );
		$_product_price = floatval( $_product->get_regular_price( 'edit' ) );

		$discount_type    = $order_bump_data['discount_type'];
		$discount_value   = floatval( $order_bump_data['discount_value'] );
		$discount_coupon  = $order_bump_data['discount_coupon'];
		$custom_price     = '';
		$order_bump_qty   = intval( $order_bump_data['product_quantity'] );
		$found_item_key   = null;
		$found_item       = null;
		$discount_enabled = false;

		if ( is_array( $discount_coupon ) && ! empty( $discount_coupon ) ) {
			$discount_coupon = reset( $discount_coupon );
		}

		// Loop over cart items.
		foreach ( WC()->cart->get_cart() as $key => $item ) {

			// For variable product.
			if ( 'yes' === $is_variable || 'yes' === $is_variation ) {

				// Check if bump product is variation OR variable.
				if ( ( $item['product_id'] === $parent_id && $item['variation_id'] === $product_id )
				|| ( $item['product_id'] === $product_id && $item['variation_id'] === $product_id ) ) {

					if ( 'false' === $checked ) {

						if ( isset( $item['cartflows_bump'] ) ) {

							$found_item_key = $key;
							$found_item     = $item;

							if ( ! empty( $discount_type ) ) {
								$discount_enabled = true;
							}
							break;
						}
					} else {

						$found_item_key = $key;
						$found_item     = $item;

						if ( ! empty( $discount_type ) ) {
							$discount_enabled = true;
						}

						break;
					}
				}
			} else {

				// if same product is already in cart.
				if ( $item['product_id'] === $product_id ) {

					if ( 'false' === $checked ) {

						if ( isset( $item['cartflows_bump'] ) ) {

							$found_item_key = $key;
							$found_item     = $item;

							if ( ! empty( $discount_type ) ) {
								$discount_enabled = true;
							}

							break;
						}
					} else {

						$found_item_key = $key;
						$found_item     = $item;

						if ( ! empty( $discount_type ) ) {
							$discount_enabled = true;
						}

						break;
					}
				}
			}
		}

		// Bump offer product found in cart and we need to add it.
		if ( null != $found_item_key && 'true' === $checked ) {

			// Case for discount enabled bump offer product.
			if ( $discount_enabled && 'coupon' !== $discount_type ) {

				$custom_price = $this->calculate_discount( $discount_coupon, $discount_type, $discount_value, $_product_price );

				$cart_item_data = array(
					'cartflows_bump' => true,
				);

				if ( isset( $custom_price ) ) {

					$cart_item_data = array(
						'custom_price'   => $custom_price,
						'cartflows_bump' => true,
					);
				}

				if ( 'yes' === $is_variable || 'yes' === $is_variation ) {
					WC()->cart->add_to_cart( $parent_id, $order_bump_qty, $product_id, array(), $cart_item_data );
				} else {
					WC()->cart->add_to_cart( $product_id, $order_bump_qty, 0, array(), $cart_item_data );
				}

				do_action( 'wcf_order_bump_item_added', $product_id );

			} else {

				if ( $discount_enabled && 'coupon' === $discount_type ) {
					$apply_coupon = $this->calculate_discount( $discount_coupon, $discount_type, $discount_value, $_product_price );
				}

				$quantity = isset( $found_item['quantity'] ) ? $found_item['quantity'] : 0;
				$new_qty  = $quantity + $order_bump_qty;

				// If item is already in cart, increase quantity for product in cart.
				WC()->cart->remove_cart_item( $found_item_key );

				if ( $_product->is_in_stock() ) {

					$cart_item_data = array(
						'cartflows_bump' => true,
					);

					if ( 'yes' === $is_variable || 'yes' === $is_variation ) {
						WC()->cart->add_to_cart( $parent_id, $new_qty, $product_id, array(), $cart_item_data );
					} else {
						WC()->cart->add_to_cart( $product_id, $new_qty, 0, array(), $cart_item_data );
					}

					do_action( 'wcf_order_bump_item_added', $product_id );
				}
			}
		}

		// add - if not found, remove/reduce - if found.
		if ( 'true' === $checked && null === $found_item_key ) {

			$custom_price = $this->calculate_discount( $discount_coupon, $discount_type, $discount_value, $_product_price );

			$cart_item_data = array(
				'cartflows_bump' => true,
			);

			if ( isset( $custom_price ) && ( '' !== $custom_price ) ) {

				$cart_item_data = array(
					'custom_price'   => $custom_price,
					'cartflows_bump' => true,
				);
			}

			if ( 'yes' === $is_variable || 'yes' === $is_variation ) {
				WC()->cart->add_to_cart( $parent_id, $order_bump_qty, $product_id, array(), $cart_item_data );
			} else {
				WC()->cart->add_to_cart( $product_id, $order_bump_qty, 0, array(), $cart_item_data );
			}

			do_action( 'wcf_order_bump_item_added', $product_id );

		} elseif ( 'false' === $checked && null != $found_item_key ) {

			$new_qty = $found_item['quantity'] - $order_bump_qty;

			WC()->cart->remove_cart_item( $found_item_key );

			do_action( 'wcf_order_bump_item_removed', $product_id );

			if ( $new_qty > 0 ) {

				if ( 'yes' === $is_variable || 'yes' === $is_variation ) {
					WC()->cart->add_to_cart( $parent_id, $new_qty, $product_id );
				} else {
					WC()->cart->add_to_cart( $product_id, $new_qty );
				}
			}

			if ( ! empty( $discount_coupon ) ) {
				if ( WC()->cart->has_discount( $discount_coupon ) ) {
					WC()->cart->remove_coupon( $discount_coupon );
				}
			}
		}

		wp_send_json( wcf_pro()->utils->get_fragments() );
	}

	/**
	 * Calculate discount for product.
	 *
	 * @param string $discount_coupon discount coupon.
	 * @param string $discount_type discount type.
	 * @param int    $discount_value discount value.
	 * @param int    $_product_price product price.
	 * @return int
	 * @since 1.1.5
	 */
	function calculate_discount( $discount_coupon, $discount_type, $discount_value, $_product_price ) {

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
			} elseif ( 'coupon' === $discount_type ) {

				if ( ! empty( $discount_coupon ) ) {
					WC()->cart->add_discount( $discount_coupon );
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
	function custom_price_to_cart_item( $cart_object ) {

		if ( wp_doing_ajax() && ! WC()->session->__isset( 'reload_checkout' ) ) {

			foreach ( $cart_object->cart_contents as $key => $value ) {

				if ( isset( $value['custom_price'] ) ) {

					$custom_price = floatval( $value['custom_price'] );
					$value['data']->set_price( $custom_price );
				}
			}
		}
	}

	/**
	 * Bump order product title shortcode.
	 *
	 * @param array $atts shortcode atts.
	 * @return string shortcode output.
	 * @since 1.0.0
	 */
	function bump_product_title( $atts ) {

		$output = '';
		if ( _is_wcf_checkout_type() ) {

			global $post;

			$order_bump_product = get_post_meta( $post->ID, 'wcf-order-bump-product', true );

			if ( ! empty( $order_bump_product ) ) {

				$product_id = reset( $order_bump_product );

				$output = get_the_title( $product_id );
			}
		}

		return $output;
	}

	/**
	 * Bump order product title shortcode.
	 *
	 * @param int $step_id step id.
	 * @return array bump order product.
	 * @since 1.0.0
	 */
	function get_bump_test_product( $step_id ) {

		$bump_product = array();

		$args = array(
			'posts_per_page' => 1,
			'orderby'        => 'rand',
			'post_type'      => 'product',
			'meta_query'     => array(
				// Exclude out of stock products.
				array(
					'key'     => '_stock_status',
					'value'   => 'outofstock',
					'compare' => 'NOT IN',
				),
			),
			'tax_query'      => array(
				array(
					'taxonomy' => 'product_type',
					'field'    => 'slug',
					'terms'    => 'simple',
				),
			),
		);

		$random_product = get_posts( $args );

		if ( isset( $random_product[0]->ID ) ) {
			$bump_product = array(
				$random_product[0]->ID,
			);
		}

		return $bump_product;
	}

	/**
	 * Check in Cart if product exists.
	 *
	 * @since 1.1.5
	 * @param int  $product_id product_id.
	 * @param bool $is_bump is bump product.
	 * @return bool.
	 * */
	function cart_has_product( $product_id, $is_bump = false ) {

		$get_cart = WC()->cart->get_cart();

		foreach ( $get_cart as $cart_item_key => $cart_item ) {

			if ( $cart_item['product_id'] == $product_id ) {

				if ( $is_bump ) {

					if ( isset( $cart_item['cartflows_bump'] ) && $cart_item['cartflows_bump'] ) {
						return true;
					}
				} else {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Get order bump data
	 *
	 * @param int $checkout_id checkout ID.
	 * @return array
	 */
	function get_order_bump_data( $checkout_id ) {

		$bump_data = array();

		$is_bump                     = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump' );
		$order_bump_product          = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product' );
		$order_bump_product_quantity = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-product-quantity' );
		$discount_type               = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount' );
		$discount_value              = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-value' );
		$discount_coupon             = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-order-bump-discount-coupon' );

		if ( empty( $order_bump_product ) ) {
			return $bump_data;
		}

		$product_id = reset( $order_bump_product );

		$bump_data = array(
			'product_id'       => $product_id,
			'product_quantity' => $order_bump_product_quantity,
			'discount_type'    => $discount_type,
			'discount_value'   => $discount_value,
			'discount_coupon'  => $discount_coupon,
			'parent_id'        => $product_id,
			'is_variable'      => 'no',
			'is_variation'     => 'no',
		);

		$_product = wc_get_product( $product_id );

		if ( ! empty( $_product ) ) {

			if ( $_product->is_type( 'variable' ) ) {

				$bump_data['is_variable'] = 'yes';
				$bump_data['parent_id']   = $product_id;

				$default_attributes = $_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $_product->get_children() as $c_in => $variation_id ) {

						if ( 0 === $c_in ) {
							$bump_data['product_id'] = $variation_id;
						}

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( $default_attributes == $single_variation->get_attributes() ) {

							$bump_data['product_id'] = $variation_id;
							break;
						}
					}
				} else {

					$product_childrens = $_product->get_children();

					if ( is_array( $product_childrens ) ) {

						foreach ( $product_childrens  as $c_in => $c_id ) {

							$bump_data['product_id'] = $c_id;
							break;
						}
					}
				}
			}

			if ( $_product->is_type( 'variation' ) ) {

				$bump_data['is_variation'] = 'yes';
				$bump_data['parent_id']    = $_product->get_parent_id();
			}
		}

		return $bump_data;
	}
}


/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Order_Bump_Product::get_instance();
