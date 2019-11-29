<?php
/**
 * Variation Product Options
 *
 * @package carflows-pro
 */

/**
 * Variation
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Variation_Product {


	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var object product_option
	 */
	private static $product_option = 'force-all';

	/**
	 * Member Variable
	 *
	 * @var object is_variation
	 */
	private static $is_variation = 'no';

	/**
	 * Member Variable
	 *
	 * @var object is_quantity
	 */
	private static $is_quantity = 'no';

	/**
	 * Member Variable
	 *
	 * @var object is_quantity
	 */
	private static $variation_as = 'inline';

	/**
	 * Member Variable
	 *
	 * @var object is_title
	 */
	private static $title = '';

	/**
	 * Member Variable
	 *
	 * @var object is_quantity
	 */
	private static $cart_products = array();

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

		if ( ! is_admin() ) {
			add_filter( 'global_cartflows_js_localize', array( $this, 'add_localize_vars' ), 10, 1 );
		}

		/* Product Selection Options */

		add_action( 'cartflows_add_before_main_section', array( $this, 'product_variation_option_position' ) );

		add_action( 'cartflows_checkout_before_configure_cart', array( $this, 'variation_options_compatibility' ) );

		/* Force All Selection */
		add_action( 'wp_ajax_wcf_variation_selection', array( $this, 'variation_selection' ) );
		add_action( 'wp_ajax_nopriv_wcf_variation_selection', array( $this, 'variation_selection' ) );

		/* Multiple Selection */
		add_action( 'wp_ajax_wcf_multiple_selection', array( $this, 'multiple_selection' ) );
		add_action( 'wp_ajax_nopriv_wcf_multiple_selection', array( $this, 'multiple_selection' ) );

		/* Single Selection */
		add_action( 'wp_ajax_wcf_single_selection', array( $this, 'single_selection' ) );
		add_action( 'wp_ajax_nopriv_wcf_single_selection', array( $this, 'single_selection' ) );

		/* Quantity Ajax */
		add_action( 'wp_ajax_wcf_quantity_update', array( $this, 'quantity_update' ) );
		add_action( 'wp_ajax_nopriv_wcf_quantity_update', array( $this, 'quantity_update' ) );

		/* Wp Footer Action */
		add_action( 'wp_footer', array( $this, 'variation_popup' ) );

		// Quick view ajax.
		add_action( 'wp_ajax_wcf_woo_quick_view', array( $this, 'load_quick_view_product' ) );
		add_action( 'wp_ajax_nopriv_wcf_woo_quick_view', array( $this, 'load_quick_view_product' ) );

		/* Add TO Cart */
		add_action( 'wp_ajax_wcf_add_cart_single_product', array( $this, 'add_cart_single_product_ajax' ) );
		add_action( 'wp_ajax_nopriv_wcf_add_cart_single_product', array( $this, 'add_cart_single_product_ajax' ) );
	}

	/**
	 * Add localize variables.
	 *
	 * @param array $localize localize array.
	 *
	 * @since 1.0.0
	 */
	public function add_localize_vars( $localize ) {

		global $post;
		$step_id = $post->ID;

		$localize['wcf_bump_order_process_nonce'] = wp_create_nonce( 'wcf_bump_order_process' );
		$localize['wcf_multiple_selection_nonce'] = wp_create_nonce( 'wcf_multiple_selection' );
		$localize['wcf_single_selection_nonce']   = wp_create_nonce( 'wcf_single_selection' );
		$localize['wcf_quantity_update_nonce']    = wp_create_nonce( 'wcf_quantity_update' );

		return $localize;
	}


	/**
	 * Product Variation option position
	 *
	 * @param string $checkout_layout layout of checkout.
	 */
	function product_variation_option_position( $checkout_layout ) {

		if ( 'two-step' === $checkout_layout ) {
			add_action( 'woocommerce_checkout_before_order_review', array( $this, 'product_selection_option' ) );
		} else {
			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'product_selection_option' ) );
		}
	}


	/**
	 * Variaiton options compatibility
	 *
	 * @param int $checkout_id post id.
	 */
	function variation_options_compatibility( $checkout_id ) {

		$is_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

		if ( 'yes' === $is_product_options ) {

			$product_sel_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );

			if ( 'single-selection' === $product_sel_option ) {
				add_filter( 'cartflows_skip_other_products', array( $this, 'skip_cart_products' ), 10, 2 );
			}
		}
	}

	/**
	 * Get all selected products
	 *
	 * @param bool $is_skip post id.
	 * @param int  $product_count count.
	 */
	function skip_cart_products( $is_skip, $product_count ) {

		if ( $product_count >= 1 ) {
			$is_skip = true;
		}

		return $is_skip;
	}
	/**
	 * Product selection options
	 */
	function product_selection_option() {

		if ( _is_wcf_checkout_type() ) {

			global $post;

			$checkout_id        = $post->ID;
			$is_product_options = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

			if ( 'yes' !== $is_product_options ) {
				return;
			}

			$product_sel_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );

			$is_product_variation = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-variation' );
			$is_product_quantity  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-quantity' );
			$variation_as         = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-variation-options' );
			$title                = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-opt-title' );

			self::$product_option = $product_sel_option;
			self::$is_variation   = $is_product_variation;
			self::$is_quantity    = $is_product_quantity;
			self::$variation_as   = $variation_as;
			self::$title          = $title;

			/* Preapre cart products variable */
			$this->prepare_cart_products();

			if ( 'force-all' === $product_sel_option ) {
				$this->force_all_options( $checkout_id );
			} elseif ( 'single-selection' === $product_sel_option ) {
				$this->single_selection_options( $checkout_id );
			} elseif ( 'multiple-selection' === $product_sel_option ) {
				$this->multiple_selection_options( $checkout_id );
			}
		}
	}

	/**
	 * Prepare cart products
	 */
	function prepare_cart_products() {

		$cart_products = array();

		$get_cart = WC()->cart->get_cart();

		foreach ( $get_cart as $cart_item_key => $cart_item ) {

			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$_product->quantity                   = $cart_item['quantity'];
				$cart_products[ $_product->get_id() ] = $_product;
			}
		}

		self::$cart_products = $cart_products;
	}

	/**
	 * Get all selected products
	 *
	 * @param int $post_id post id.
	 * @return array product IDs.
	 */
	function get_all_main_products( $post_id ) {

		$product_data = wcf()->options->get_checkout_meta_value( $post_id, 'wcf-checkout-products' );
		$products     = array();

		if ( is_array( $product_data ) ) {

			foreach ( $product_data as $p_index => $p_data ) {

				if ( ! isset( $p_data['product'] ) ) {
					continue;
				}

				$products[ $p_data['product'] ] = array(
					'id'        => $p_data['product'],
					'variable'  => false,
					'variation' => false,
				);

				$_product = wc_get_product( $p_data['product'] );

				if ( ! empty( $_product ) ) {

					if ( $_product->is_type( 'variable' ) ) {

						$products[ $p_data['product'] ]['variable'] = true;
					}

					if ( $_product->is_type( 'variation' ) ) {

						$products[ $p_data['product'] ]['variation'] = true;
					}
				}
			}
		}

		return $products;
	}


	/*================ Force all products option ===========================================*/

	/**
	 * Quantity selection markup
	 *
	 * @param object $current_product product.
	 * @param array  $data product data.
	 * @return string
	 */
	function force_all_product_markup( $current_product, $data ) {
		$output = '';

		if ( $data['variable'] || $data['variation'] ) {

			if ( $data['variable'] ) {

				$current_variation_id = false;
				$show                 = false;
				$single_variation     = false;

				$default_attributes = $current_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $current_product->get_children() as $c_in => $variation_id ) {

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( 'yes' === self::$is_variation ) {
							if ( 'inline' === self::$variation_as ) {
								$output .= $this->force_variation_product_markup( $current_product, $single_variation, 'inline' );
							} else {
								if ( 0 === $c_in ) {
									$output .= $this->force_variation_product_markup( $current_product, $single_variation, 'popup' );
									break;
								}
							}
						} elseif ( $default_attributes == $single_variation->get_attributes() ) {
							$output .= $this->force_variation_product_markup( $current_product, $single_variation );
						}
					}
				} else {

					$product_childrens = $current_product->get_children();

					if ( is_array( $product_childrens ) && 'yes' === self::$is_variation ) {

						if ( 'inline' === self::$variation_as ) {
							foreach ( $product_childrens  as $c_in => $c_id ) {

								$single_variation = new WC_Product_Variation( $c_id );

								$output .= $this->force_variation_product_markup( $current_product, $single_variation, 'inline' );
							}
						} else {

							if ( isset( $product_childrens[0] ) ) {

								$single_variation_product = $this->get_variation_product_from_cart( $current_product->get_id() );
								$single_variation_product = $single_variation_product ? $single_variation_product : $product_childrens[0];
								$single_variation         = new WC_Product_Variation( $single_variation_product );

								$output .= $this->force_variation_product_markup( $current_product, $single_variation, 'popup' );
							}
						}
					} elseif ( isset( $product_childrens[0] ) ) {

						$single_variation = new WC_Product_Variation( $product_childrens[0] );

						$output .= $this->force_variation_product_markup( $current_product, $single_variation );
					}
				}
			} else {
				$single_variation = $current_product;
				$parent_product   = wc_get_product( $current_product->get_parent_id() );

				$output .= $this->force_variation_product_markup( $parent_product, $single_variation );
			}
		} else {
			$output .= $this->force_normal_product_markup( $current_product );
		}

		return $output;
	}

	/**
	 * Force all products
	 *
	 * @param int $checkout_id product id.
	 */
	function force_all_options( $checkout_id ) {
		$this->force_all_products( $checkout_id );
	}

	/**
	 * Quantity options markup
	 *
	 * @param int $checkout_id product id.
	 */
	function force_all_products( $checkout_id ) {

		$products = $this->get_all_main_products( $checkout_id );

		if ( ! is_array( $products ) || empty( $products ) ) {
			return;
		}

		$var_output  = '<div class="wcf-product-option-wrap">';
		$var_output .= '<h3 id="your_products_heading">' . $this->product_option_title() . '</h3>';

		$output = '';

		$quantity_hidden = '';

		if ( 'yes' !== self::$is_quantity ) {
			$quantity_hidden = 'wcf-qty-hidden';
		}

		$var_output                 .= '<div class="wcf-qty-options ' . $quantity_hidden . '">';
			$var_output             .= '<div class="wcf-qty-row">';
				$var_output         .= '<div class="wcf-qty-header wcf-item">';
						$var_output .= '<div class="wcf-field-label"><strong>' . __( 'Product', 'cartflows-pro' ) . '</strong></div>';
				$var_output         .= '</div>';
				$var_output         .= '<div class="wcf-qty-header wcf-qty">';
					$var_output     .= '<div class="wcf-field-label"><strong>' . __( 'Quantity', 'cartflows-pro' ) . '</strong></div>';
				$var_output         .= '</div>';
				$var_output         .= '<div class="wcf-qty-header wcf-price">';
					$var_output     .= '<div class="wcf-field-label">' . __( 'Price', 'cartflows-pro' ) . '</div>';
				$var_output         .= '</div>';
			$var_output             .= '</div>';

		foreach ( $products as $p_id => $p_data ) {

			$current_product = wc_get_product( $p_id );

			$var_output .= '<hr class="wcf-hr">';
			$var_output .= $this->force_all_product_markup( $current_product, $p_data );

		}

		$var_output .= '</div>';
		$var_output .= '</div>'; // Close product option wrap.

		echo $var_output;
	}

	/**
	 * Force variation product markup
	 *
	 * @param object $current_product product obj.
	 * @param object $single_variation product obj.
	 * @param string $type Select type.
	 * @return string.
	 */
	function force_variation_product_markup( $current_product, $single_variation, $type = false ) {

		$output = '';

		if ( $single_variation && $single_variation->is_in_stock() ) {

			$parent_id    = $current_product->get_id();
			$variation_id = $single_variation->get_id();

			$output     .= '<div class="wcf-qty-row wcf-qty-row-' . $variation_id . '">';
				$output .= '<div class="wcf-item">';

			if ( 'inline' === $type ) {

				$checked = '';

				if ( isset( self::$cart_products[ $variation_id ] ) ) {
					$checked = 'checked';
				}

				$output .= '<div class="wcf-item-selector wcf-item-var-sel"><input class="wcf-var-sel" id="wcf-item-product-' . $variation_id . '"  type="radio" name="wcf-var-sel[' . $parent_id . ']" value="' . $variation_id . '" ' . $checked . '>';

					$output .= '<label class="wcf-item-product-label" for="wcf-item-product-' . $variation_id . '" ></label>';
				$output     .= '</div>';
			}

			$output .= '<div class="wcf-item-wrap">' . $single_variation->get_name() . '</div>';

			if ( 'popup' === $type ) {

				// $output .= '<div class="wcf-item-wrap">' . $single_variation->get_name() . '</div>';
				$output .= '<div class="wcf-item-choose-options"><a href="#" data-product="' . $parent_id . '" data-variation="' . $variation_id . '">' . $this->variation_popup_toggle_text() . '</a></div>';
			}

				$output .= '</div>';
				$output .= '<div class="wcf-qty">';

					$sel_data = json_encode(
						array(
							'product_id'   => $current_product->get_id(),
							'variation_id' => $variation_id,
							'type'         => 'variation',
							'mode'         => 'quantity',
						)
					);

			$selected_val = $this->get_cart_product_quantity( $variation_id );

					$output .= '<input autocomplete="off" data-options="' . htmlspecialchars( $sel_data ) . '" type="number" value="' . $selected_val . '" min="1" name="wcf_qty_selection" class="wcf-qty-selection">';
				$output     .= '</div>';
				$output     .= '<div class="wcf-price">';
					$output .= '<div class="wcf-field-label">' . wc_price( $single_variation->get_price() ) . '</div>';
				$output     .= '</div>';
			$output         .= '</div>';
		}

		return $output;
	}

	/**
	 * FOrce normal product markup
	 *
	 * @param object $current_product product obj.
	 * @return string
	 */
	function force_normal_product_markup( $current_product ) {

		$output = '';
		if ( $current_product && $current_product->is_in_stock() ) {

			$output         .= '<div class="wcf-qty-row wcf-qty-row-' . $current_product->get_id() . '">';
				$output     .= '<div class="wcf-item">';
					$output .= '<div class="wcf-item-wrap">' . $current_product->get_name() . '</div>';
				$output     .= '</div>';
				$output     .= '<div class="wcf-qty">';

					$sel_data = json_encode(
						array(
							'product_id' => $current_product->get_id(),
							'type'       => 'simple',
							'mode'       => 'quantity',
						)
					);

			$qty             = $this->get_cart_product_quantity( $current_product->get_id() );
			$output         .= '<input autocomplete="off" data-options="' . htmlspecialchars( $sel_data ) . '" type="number" value="' . $qty . '" min="1" name="wcf_qty_selection" class="wcf-qty-selection">';
				$output     .= '</div>';
				$output     .= '<div class="wcf-price">';
					$output .= '<div class="wcf-field-label">' . wc_price( $current_product->get_price() ) . '</div>';
				$output     .= '</div>';
			$output         .= '</div>';
		}

		return $output;
	}
	/*================ Single selection options =============================================*/

	/**
	 * Single selection options
	 *
	 * @param int $checkout_id checkout id.
	 */
	function single_selection_options( $checkout_id ) {

		$products = $this->get_all_main_products( $checkout_id );

		if ( ! is_array( $products ) || empty( $products ) ) {
			return;
		}

		$var_output  = '<div class="wcf-product-option-wrap">';
		$var_output .= '<h3 id="your_products_heading">' . $this->product_option_title() . '</h3>';

		$output = '';

		$quantity_hidden = '';

		if ( 'yes' !== self::$is_quantity ) {
			$quantity_hidden = 'wcf-qty-hidden';
		}

		$var_output                 .= '<div class="wcf-qty-options ' . $quantity_hidden . '">';
			$var_output             .= '<div class="wcf-qty-row">';
				$var_output         .= '<div class="wcf-qty-header wcf-item">';
						$var_output .= '<div class="wcf-field-label"><strong>' . __( 'Product', 'cartflows-pro' ) . '</strong></div>';
				$var_output         .= '</div>';
				$var_output         .= '<div class="wcf-qty-header wcf-qty">';
					$var_output     .= '<div class="wcf-field-label"><strong>' . __( 'Quantity', 'cartflows-pro' ) . '</strong></div>';
				$var_output         .= '</div>';
				$var_output         .= '<div class="wcf-qty-header wcf-price">';
					$var_output     .= '<div class="wcf-field-label">' . __( 'Price', 'cartflows-pro' ) . '</div>';
				$var_output         .= '</div>';
			$var_output             .= '</div>';

		foreach ( $products as $p_id => $p_data ) {

			$current_product = wc_get_product( $p_id );

			$var_output .= '<hr class="wcf-hr">';
			$var_output .= $this->single_sel_product_markup( $current_product, $p_data );

		}

		$var_output .= '</div>';
		$var_output .= '</div>'; // Close product option wrap.

		echo $var_output;
	}

	/**
	 * Quantity selection markup
	 *
	 * @param object $current_product product obj.
	 * @param array  $data product data.
	 * @return string
	 */
	function single_sel_product_markup( $current_product, $data ) {

		$output = '';

		if ( $data['variable'] || $data['variation'] ) {

			if ( $data['variable'] ) {

				$current_variation_id = false;
				$show                 = false;
				$single_variation     = false;

				$default_attributes = $current_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $current_product->get_children() as $var_index => $variation_id ) {

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( 'yes' === self::$is_variation ) {

							if ( 'popup' === self::$variation_as ) {

								if ( 0 === $var_index ) {
									$output .= $this->single_sel_variation_product_markup( $current_product, $single_variation, 'popup' );
									break;
								}
							} else {

								$output .= $this->single_sel_variation_product_markup( $current_product, $single_variation );
							}
						} elseif ( $default_attributes == $single_variation->get_attributes() ) {
							$output .= $this->single_sel_variation_product_markup( $current_product, $single_variation );
						}
					}
				} else {

					$product_childrens = $current_product->get_children();

					if ( is_array( $product_childrens ) && 'yes' === self::$is_variation ) {

						foreach ( $product_childrens  as $c_in => $c_id ) {

							$single_variation = new WC_Product_Variation( $c_id );

							if ( 'popup' === self::$variation_as ) {

								if ( 0 === $c_in ) {
									$output .= $this->single_sel_variation_product_markup( $current_product, $single_variation, 'popup' );
									break;
								}
							} else {
								$output .= $this->single_sel_variation_product_markup( $current_product, $single_variation );
							}
						}
					} elseif ( isset( $product_childrens[0] ) ) {

						$single_variation = new WC_Product_Variation( $product_childrens[0] );

						$output .= $this->single_sel_variation_product_markup( $current_product, $single_variation );
					}
				}
			} else {
				$single_variation = $current_product;
				$parent_product   = wc_get_product( $current_product->get_parent_id() );

				$output .= $this->single_sel_variation_product_markup( $parent_product, $single_variation );
			}
		} else {
			$output .= $this->single_sel_normal_product_markup( $current_product );
		}

		return $output;
	}

	/**
	 * Single select normal product
	 *
	 * @param object $current_product product obj.
	 * @param object $single_variation product obj.
	 * @param string $type Select type.
	 * @return string.
	 */
	function single_sel_variation_product_markup( $current_product, $single_variation, $type = '' ) {

		$output = '';

		if ( $single_variation && $single_variation->is_in_stock() ) {

			$parent_id    = $current_product->get_id();
			$variation_id = $single_variation->get_id();

			$output     .= '<div class="wcf-qty-row wcf-qty-row-' . $variation_id . '">';
				$output .= '<div class="wcf-item">';

					$checked = '';

			if ( isset( self::$cart_products[ $variation_id ] ) ) {
				$checked = 'checked';
			}

					$cb_sel_data = json_encode(
						array(
							'product_id'   => $parent_id,
							'variation_id' => $variation_id,
							'type'         => 'variation',
						)
					);

					$output .= '<div class="wcf-item-selector wcf-item-single-sel"><input class="wcf-single-sel" id="wcf-item-product-' . $variation_id . '" type="radio" name="wcf-single-sel" data-options="' . htmlspecialchars( $cb_sel_data ) . '" value="' . $variation_id . '" ' . $checked . '>';

					$output .= '<label class="wcf-item-product-label" for="wcf-item-product-' . $variation_id . '"></label>';

					$output .= '</div>';

					$output .= '<div class="wcf-item-wrap">' . $single_variation->get_name() . '</div>';

			if ( 'popup' === $type ) {

				$output .= '<div class="wcf-item-choose-options"><a href="#" data-product="' . $parent_id . '" data-variation="' . $variation_id . '">' . $this->variation_popup_toggle_text() . '</a></div>';
			}

				$output .= '</div>';
				$output .= '<div class="wcf-qty">';

					$sel_data = json_encode(
						array(
							'product_id'   => $parent_id,
							'variation_id' => $variation_id,
							'type'         => 'variation',
							'mode'         => 'quantity',
						)
					);

					$selected_val = 1;

					$output .= '<input autocomplete="off" data-options="' . htmlspecialchars( $sel_data ) . '" type="number" value="' . $selected_val . '" min="1" name="wcf_qty_selection" class="wcf-qty-selection">';
				$output     .= '</div>';
				$output     .= '<div class="wcf-price">';
					$output .= '<div class="wcf-field-label">' . wc_price( $single_variation->get_price() ) . '</div>';
				$output     .= '</div>';
			$output         .= '</div>';
		}

		return $output;
	}

	/**
	 * Single select normal product
	 *
	 * @param object $current_product product obj.
	 * @return string.
	 */
	function single_sel_normal_product_markup( $current_product ) {

		$output = '';

		if ( $current_product && $current_product->is_in_stock() ) {

			$product_id = $current_product->get_id();

			$output     .= '<div class="wcf-qty-row wcf-qty-row-' . $product_id . '">';
				$output .= '<div class="wcf-item">';

					$checked = '';

			if ( isset( self::$cart_products[ $product_id ] ) ) {
				$checked = 'checked';
			}

					$cb_sel_data = json_encode(
						array(
							'product_id' => $product_id,
							'type'       => 'simple',
						)
					);

					$output .= '<div class="wcf-item-selector wcf-item-single-sel"><input class="wcf-single-sel" type="radio" id="wcf-item-product-' . $product_id . '" name="wcf-single-sel" data-options="' . htmlspecialchars( $cb_sel_data ) . '" value="' . $product_id . '" ' . $checked . '>';

						$output .= '<label class="wcf-item-product-label" for="wcf-item-product-' . $product_id . '"></label>';

					$output .= '</div>';

					$output .= '<div class="wcf-item-wrap" >' . $current_product->get_name() . '</div>';

				$output .= '</div>';
				$output .= '<div class="wcf-qty">';

					$sel_data = json_encode(
						array(
							'product_id' => $product_id,
							'type'       => 'simple',
							'mode'       => 'quantity',
						)
					);

					$output .= '<input autocomplete="off" data-options="' . htmlspecialchars( $sel_data ) . '" type="number" value="1" min="1" name="wcf_qty_selection" class="wcf-qty-selection">';
				$output     .= '</div>';
				$output     .= '<div class="wcf-price">';
					$output .= '<div class="wcf-field-label">' . wc_price( $current_product->get_price() ) . '</div>';
				$output     .= '</div>';
			$output         .= '</div>';
		}

		return $output;
	}
	/*================ Multiple selection options ============================================*/

	/**
	 * Multiple selection options
	 *
	 * @param int $checkout_id checkout id.
	 * @return string.
	 */
	function multiple_selection_options( $checkout_id ) {

		$products = $this->get_all_main_products( $checkout_id );

		if ( ! is_array( $products ) || empty( $products ) ) {
			return;
		}

		$var_output  = '<div class="wcf-product-option-wrap">';
		$var_output .= '<h3 id="your_products_heading">' . $this->product_option_title() . '</h3>';

		$output = '';

		$quantity_hidden = '';

		if ( 'yes' !== self::$is_quantity ) {
			$quantity_hidden = 'wcf-qty-hidden';
		}

		$var_output                 .= '<div class="wcf-qty-options ' . $quantity_hidden . '">';
			$var_output             .= '<div class="wcf-qty-row">';
				$var_output         .= '<div class="wcf-qty-header wcf-item">';
						$var_output .= '<div class="wcf-field-label"><strong>' . __( 'Product', 'cartflows-pro' ) . '</strong></div>';
				$var_output         .= '</div>';
				$var_output         .= '<div class="wcf-qty-header wcf-qty">';
					$var_output     .= '<div class="wcf-field-label"><strong>' . __( 'Quantity', 'cartflows-pro' ) . '</strong></div>';
				$var_output         .= '</div>';
				$var_output         .= '<div class="wcf-qty-header wcf-price">';
					$var_output     .= '<div class="wcf-field-label">' . __( 'Price', 'cartflows-pro' ) . '</div>';
				$var_output         .= '</div>';
			$var_output             .= '</div>';

		foreach ( $products as $p_id => $p_data ) {

			$current_product = wc_get_product( $p_id );

			$var_output .= '<hr class="wcf-hr">';
			$var_output .= $this->multiple_sel_product_markup( $current_product, $p_data );

		}

		$var_output .= '</div>';
		$var_output .= '</div>'; // Close product option wrap.

		echo $var_output;
	}

	/**
	 * Quantity selection markup
	 *
	 * @param object $current_product product.
	 * @param array  $data product data.
	 * @return string
	 */
	function multiple_sel_product_markup( $current_product, $data ) {
		$output = '';

		if ( $data['variable'] || $data['variation'] ) {

			if ( $data['variable'] ) {

				$current_variation_id = false;
				$show                 = false;
				$single_variation     = false;

				$default_attributes = $current_product->get_default_attributes();

				if ( ! empty( $default_attributes ) ) {

					foreach ( $current_product->get_children() as $var_index => $variation_id ) {

						$single_variation = new WC_Product_Variation( $variation_id );

						if ( 'yes' === self::$is_variation ) {

							if ( 'popup' === self::$variation_as ) {

								if ( 0 === $var_index ) {
									$output .= $this->multiple_sel_variation_product_markup( $current_product, $single_variation, 'popup' );
									break;
								}
							} else {

								$output .= $this->multiple_sel_variation_product_markup( $current_product, $single_variation );
							}
						} elseif ( $default_attributes == $single_variation->get_attributes() ) {
							$output .= $this->multiple_sel_variation_product_markup( $current_product, $single_variation );
						}
					}
				} else {

					$product_childrens = $current_product->get_children();

					if ( is_array( $product_childrens ) && 'yes' === self::$is_variation ) {

						foreach ( $product_childrens  as $c_in => $c_id ) {

							$single_variation = new WC_Product_Variation( $c_id );

							if ( 'popup' === self::$variation_as ) {

								if ( 0 === $c_in ) {
									$output .= $this->multiple_sel_variation_product_markup( $current_product, $single_variation, 'popup' );
									break;
								}
							} else {
								$output .= $this->multiple_sel_variation_product_markup( $current_product, $single_variation );
							}
						}
					} elseif ( isset( $product_childrens[0] ) ) {

						$single_variation = new WC_Product_Variation( $product_childrens[0] );

						$output .= $this->multiple_sel_variation_product_markup( $current_product, $single_variation );
					}
				}
			} else {
				$single_variation = $current_product;
				$parent_product   = wc_get_product( $current_product->get_parent_id() );

				$output .= $this->multiple_sel_variation_product_markup( $parent_product, $single_variation );
			}
		} else {
			$output .= $this->multiple_sel_normal_product_markup( $current_product );
		}

		return $output;
	}

	/**
	 * Multiple sel variation product markup
	 *
	 * @param object $current_product product obj.
	 * @param object $single_variation product obj.
	 * @param string $type Select type.
	 */
	function multiple_sel_variation_product_markup( $current_product, $single_variation, $type = '' ) {

		$output = '';

		if ( $single_variation && $single_variation->is_in_stock() ) {

			$parent_id    = $current_product->get_id();
			$variation_id = $single_variation->get_id();

			$output     .= '<div class="wcf-qty-row wcf-qty-row-' . $variation_id . '">';
				$output .= '<div class="wcf-item">';

					$checked = '';

			if ( isset( self::$cart_products[ $variation_id ] ) ) {
				$checked = 'checked';
			}

					$cb_sel_data = json_encode(
						array(
							'product_id'   => $parent_id,
							'variation_id' => $variation_id,
							'type'         => 'variation',
						)
					);

					$output .= '<div class="wcf-item-selector wcf-item-multiple-sel"><input class="wcf-multiple-sel" type="checkbox" name="wcf-multiple-sel" data-options="' . htmlspecialchars( $cb_sel_data ) . '" value="' . $variation_id . '" ' . $checked . '></div>';

					$output .= '<div class="wcf-item-wrap">' . $single_variation->get_name() . '</div>';

			if ( 'popup' === $type ) {

				$output .= '<div class="wcf-item-choose-options"><a href="#" data-product="' . $parent_id . '" data-variation="' . $variation_id . '">' . $this->variation_popup_toggle_text() . '</a></div>';
			}

				$output .= '</div>';
				$output .= '<div class="wcf-qty">';

					$sel_data = json_encode(
						array(
							'product_id'   => $parent_id,
							'variation_id' => $variation_id,
							'type'         => 'variation',
							'mode'         => 'quantity',
						)
					);

					$selected_val = 1;

					$output .= '<input autocomplete="off" data-options="' . htmlspecialchars( $sel_data ) . '" type="number" value="' . $selected_val . '" min="1" name="wcf_qty_selection" class="wcf-qty-selection">';
				$output     .= '</div>';
				$output     .= '<div class="wcf-price">';
					$output .= '<div class="wcf-field-label">' . wc_price( $single_variation->get_price() ) . '</div>';
				$output     .= '</div>';
			$output         .= '</div>';
		}

		return $output;
	}

	/**
	 * Multiple sel noraml product markup
	 *
	 * @param object $current_product product obj.
	 */
	function multiple_sel_normal_product_markup( $current_product ) {

		$output = '';

		if ( $current_product && $current_product->is_in_stock() ) {

			$product_id = $current_product->get_id();

			$output     .= '<div class="wcf-qty-row wcf-qty-row-' . $product_id . '">';
				$output .= '<div class="wcf-item">';

					$checked = '';

			if ( isset( self::$cart_products[ $product_id ] ) ) {
				$checked = 'checked';
			}

					$cb_sel_data = json_encode(
						array(
							'product_id' => $product_id,
							'type'       => 'simple',
						)
					);

					$output .= '<div class="wcf-item-selector wcf-item-multiple-sel"><input class="wcf-multiple-sel" type="checkbox" name="wcf-multiple-sel" data-options="' . htmlspecialchars( $cb_sel_data ) . '" value="' . $product_id . '" ' . $checked . '></div>';

					$output .= '<div class="wcf-item-wrap">' . $current_product->get_name() . '</div>';
				$output     .= '</div>';
				$output     .= '<div class="wcf-qty">';

					$sel_data = json_encode(
						array(
							'product_id' => $product_id,
							'type'       => 'simple',
							'mode'       => 'quantity',
						)
					);

			$qty             = $this->get_cart_product_quantity( $product_id );
			$output         .= '<input autocomplete="off" data-options="' . htmlspecialchars( $sel_data ) . '" type="number" value="' . $qty . '" min="1" name="wcf_qty_selection" class="wcf-qty-selection">';
				$output     .= '</div>';
				$output     .= '<div class="wcf-price">';
					$output .= '<div class="wcf-field-label">' . wc_price( $current_product->get_price() ) . '</div>';
				$output     .= '</div>';
			$output         .= '</div>';
		}

		return $output;
	}

	/*=====================================================================================*/

	/**
	 * Quantity update in cart
	 */
	function quantity_update() {

		$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_quantity_update' ) ) {
			return;
		}

		$option       = $_POST['option'];
		$product_id   = intval( $option['product_id'] );
		$product_type = sanitize_text_field( $option['type'] );
		$mode         = sanitize_text_field( $option['mode'] );

		$qty           = intval( $option['qty'] );
		$variation_id  = 0;
		$variations    = array();
		$cart_products = array();

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			$cart_products[ $cart_item['product_id'] ] = $cart_item_key;

			if ( $cart_item['variation_id'] > 0 ) {
				$variations[ $cart_item['variation_id'] ] = $cart_item_key;
			}
		}

		if ( 'variation' === $product_type ) {

			$variation_id = intval( $option['variation_id'] );

			if ( ! isset( $variations[ $variation_id ] ) ) {
				WC()->cart->add_to_cart( $product_id, $qty, $variation_id );
			}
		} else {
			if ( ! isset( $cart_products[ $product_id ] ) ) {
				WC()->cart->add_to_cart( $product_id, $qty );
			}
		}

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			if ( 'variation' === $product_type ) {
				if ( isset( $variations[ $variation_id ] ) && ( $cart_item['quantity'] != $qty ) && ( $cart_item['variation_id'] == $variation_id ) ) {
					WC()->cart->set_quantity( $cart_item_key, $qty );
				}

				if ( isset( $variations[ $variation_id ] ) && ( 0 == $qty ) && ( $cart_item['variation_id'] == $variation_id ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			} else {
				if ( isset( $cart_products[ $product_id ] ) && ( $cart_item['quantity'] != $qty ) && ( $cart_item['product_id'] == $product_id ) ) {
					WC()->cart->set_quantity( $cart_item_key, $qty );
				}

				if ( isset( $cart_products[ $product_id ] ) && ( 0 == $qty ) && ( $cart_item['product_id'] == $product_id ) ) {
					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
		}

		do_action( 'wcf_after_quantity_update', $product_id );
		wp_send_json( wcf_pro()->utils->get_fragments() );
	}

	/************** Ajax *************************************************************************/

	/**
	 * Force All Selection
	 */
	function variation_selection() {

		$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_variation_selection' ) ) {
			return;
		}

		$option       = $_POST['option'];
		$product_id   = intval( $option['product_id'] );
		$mode         = $option['mode'];
		$variation_id = intval( $option['variation_id'] );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			if ( $cart_item['product_id'] === $product_id ) {

				WC()->cart->remove_cart_item( $cart_item_key );
			}
		}

		WC()->cart->add_to_cart( $product_id, 1, $variation_id );

		do_action( 'wcf_after_variation_selection', $variation_id );
		wp_send_json( wcf_pro()->utils->get_fragments() );
	}

	/**
	 * Multiple Selection
	 */
	function multiple_selection() {

		$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_multiple_selection' ) ) {
			return;
		}

		$option       = $_POST['option'];
		$product_id   = intval( $option['product_id'] );
		$variation_id = isset( $option['variation_id'] ) ? intval( $option['variation_id'] ) : 0;
		$type         = sanitize_text_field( $option['type'] );
		$is_checked   = sanitize_text_field( $option['checked'] );
		$qty          = intval( $option['qty'] );
		// $mode       	= $option['mode'];
		if ( 'yes' === $is_checked ) {

			if ( 'variation' === $type ) {
				WC()->cart->add_to_cart( $product_id, $qty, $variation_id );
			} else {
				WC()->cart->add_to_cart( $product_id, $qty );
			}

			do_action( 'wcf_after_multiple_selection', $product_id );
		} else {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if ( 'variation' === $type ) {

					if ( $cart_item['variation_id'] === $variation_id ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				} else {

					if ( $cart_item['product_id'] === $product_id ) {
						WC()->cart->remove_cart_item( $cart_item_key );
					}
				}
			}
		}

		wp_send_json( wcf_pro()->utils->get_fragments() );
	}

	/**
	 * Single Selection
	 */
	function single_selection() {

		$nonce = filter_input( INPUT_POST, 'security', FILTER_SANITIZE_STRING );
		if ( ! wp_verify_nonce( $nonce, 'wcf_single_selection' ) ) {
			return;
		}

		$option       = $_POST['option'];
		$product_id   = intval( $option['product_id'] );
		$variation_id = isset( $option['variation_id'] ) ? intval( $option['variation_id'] ) : 0;
		$type         = sanitize_text_field( $option['type'] );
		$qty          = intval( $option['qty'] );

		$checkout_id = intval( $option['checkout_id'] );

		$assigned_products = $this->get_all_main_products( $checkout_id );

		$products = array();

		if ( ! empty( $assigned_products ) ) {
			foreach ( $assigned_products as $key => $value ) {

				if ( $value['variable'] ) {
					$_product = wc_get_product( $key );
					$children = $_product->get_children();
					$products = array_merge( $products, $children );
				}

				array_push( $products, $key );

			}
		}

		$this->wcf_check_product_in_cart( $products );

		// $mode       	= $option['mode'];
		if ( 'variation' === $type ) {
			WC()->cart->add_to_cart( $product_id, $qty, $variation_id );
		} else {
			WC()->cart->add_to_cart( $product_id, $qty );
		}

		do_action( 'wcf_after_single_selection', $product_id );
		wp_send_json( wcf_pro()->utils->get_fragments() );
	}

	/**
	 * Check product in cart and remove.
	 *
	 * @since 1.1.5
	 * @param array $products product array.
	 * @return void.
	 * */
	function wcf_check_product_in_cart( $products ) {
		if ( ! empty( $products ) ) {
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				$label = 'product_id';
				if ( 0 != $cart_item['variation_id'] ) {

					$label = 'variation_id';
				}

				if ( in_array( $cart_item[ $label ], $products ) ) {

					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}
		}
		return;
	}

	/**************************************** Popups *************************************/

	/**
	 * Variation Popup
	 */
	function variation_popup() {

		if ( _is_wcf_checkout_type() ) {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			wp_enqueue_script( 'flexslider' );

			include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/quick-view/quick-view-modal.php';
		}
	}

	/**
	 * Load Quick View Product.
	 *
	 * @since 0.0.1
	 * @access public
	 */
	public function load_quick_view_product() {

		if ( ! isset( $_REQUEST['product_id'] ) ) {
			die();
		}

		// add_action( 'cartflows_woo_quick_view_product_image', 'woocommerce_show_product_sale_flash', 10 );
		// Image.
		add_action( 'cartflows_woo_quick_view_product_image', array( $this, 'quick_view_product_images_markup' ), 20 );

		// Summary.
		add_action( 'cartflows_woo_quick_view_product_summary', array( $this, 'quick_view_product_content_structure' ), 10 );

		$product_id = intval( $_REQUEST['product_id'] );

		// set the main wp query for the product.
		wp( 'p=' . $product_id . '&post_type=product' );

		ob_start();

		// load content template.
		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/quick-view/quick-view-product.php';

		echo ob_get_clean();

		die();
	}

	/**
	 * Quick view product images markup.
	 */
	function quick_view_product_images_markup() {

		include CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/quick-view/quick-view-product-image.php';
	}

	/**
	 * Product Option title.
	 *
	 * @return title.
	 */
	function product_option_title() {

		return apply_filters( 'cartflows_product_option_title', self::$title );
	}

	/**
	 * Choose a vatiation text.
	 *
	 * @return text.
	 */
	function variation_popup_toggle_text() {

		return apply_filters( 'cartflows_variation_popup_toggle_text', __( 'Choose a variation', 'cartflows-pro' ) );
	}

	/**
	 * Quick view product content structure.
	 */
	function quick_view_product_content_structure() {

		global $product;

		$post_id = $product->get_id();

		$single_structure = apply_filters(
			'cartflows_quick_view_product_structure',
			array(
				'title',
				// 'ratings',
				'price',
				'short_desc',
				// 'meta',
				'add_cart',
			)
		);

		if ( is_array( $single_structure ) && ! empty( $single_structure ) ) {

			foreach ( $single_structure as $value ) {

				switch ( $value ) {
					case 'title':
						/**
						 * Add Product Title on single product page for all products.
						 */
						do_action( 'cartflows_quick_view_title_before', $post_id );
						woocommerce_template_single_title();
						do_action( 'cartflows_quick_view_title_after', $post_id );
						break;
					case 'price':
						/**
						 * Add Product Price on single product page for all products.
						 */
						do_action( 'cartflows_quick_view_price_before', $post_id );
						woocommerce_template_single_price();
						do_action( 'cartflows_quick_view_price_after', $post_id );
						break;
					case 'ratings':
						/**
						 * Add rating on single product page for all products.
						 */
						do_action( 'cartflows_quick_view_rating_before', $post_id );
						woocommerce_template_single_rating();
						do_action( 'cartflows_quick_view_rating_after', $post_id );
						break;
					case 'short_desc':
						do_action( 'cartflows_quick_view_short_description_before', $post_id );
						woocommerce_template_single_excerpt();
						do_action( 'cartflows_quick_view_short_description_after', $post_id );
						break;
					case 'add_cart':
						do_action( 'cartflows_quick_view_add_to_cart_before', $post_id );
						woocommerce_template_single_add_to_cart();
						do_action( 'cartflows_quick_view_add_to_cart_after', $post_id );
						break;
					case 'meta':
						do_action( 'cartflows_quick_view_category_before', $post_id );
						woocommerce_template_single_meta();
						do_action( 'cartflows_quick_view_category_after', $post_id );
						break;
					default:
						break;
				}
			}
		}
	}

	/**
	 * Single Product add to cart ajax request
	 *
	 * @since 1.1.0
	 *
	 * @return void.
	 */
	function add_cart_single_product_ajax() {

		$product_id   = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		$variation_id = isset( $_POST['variation_id'] ) ? intval( $_POST['variation_id'] ) : 0;
		$quantity     = isset( $_POST['quantity'] ) ? intval( $_POST['quantity'] ) : 0;

		$response = array(
			'name'          => '',
			'product_id'    => $product_id,
			'variation_id'  => $variation_id,
			'added_to_cart' => 'no',
			'price'         => false,
		);

		if ( $variation_id ) {

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				if ( $cart_item['product_id'] === $product_id ) {

					WC()->cart->remove_cart_item( $cart_item_key );
				}
			}

			WC()->cart->add_to_cart( $product_id, $quantity, $variation_id );

			$single_variation = new WC_Product_Variation( $variation_id );

			$response = array(
				'name'          => $single_variation->get_name(),
				'product_id'    => $product_id,
				'variation_id'  => $variation_id,
				'added_to_cart' => 'yes',
				'price'         => '<strong>' . wc_price( $single_variation->get_price() ) . '</strong>',
			);
		}

		wp_send_json_success( $response );
	}

	/**
	 * Get Cart product variation.
	 *
	 * @since 1.1.5
	 * @param int $product_id product_id.
	 * @return int variation_id.
	 * */
	function get_variation_product_from_cart( $product_id ) {
		$variation_id = 0;
		$get_cart     = WC()->cart->get_cart();
		foreach ( $get_cart as $cart_item_key => $cart_item ) {

			if ( $cart_item['product_id'] == $product_id ) {
				$variation_id = $cart_item['variation_id'];
				break;
			}
		}
		return $variation_id;
	}

	/**
	 * Get Cart product quantity.
	 *
	 * @since 1.1.0
	 * @param int $product_id product_id.
	 * @return int $qty.
	 */
	function get_cart_product_quantity( $product_id ) {
		$qty          = 1;
		$cart_product = isset( self::$cart_products[ $product_id ] ) ? self::$cart_products[ $product_id ] : null;
		if ( isset( $cart_product ) && $cart_product->quantity ) {
			$qty = $cart_product->quantity;
		}
		return $qty;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Variation_Product::get_instance();
