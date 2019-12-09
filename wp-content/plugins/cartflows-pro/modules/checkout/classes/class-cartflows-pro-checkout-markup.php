<?php
/**
 * Checkout markup.
 *
 * @package cartflows
 */

/**
 * Checkout Markup
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Checkout_Markup {



	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var is_divi_enabled
	 */
	public $divi_status = false;

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

		$this->include_required_class();

		add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'save_checkout_fields' ), 10, 2 );

		/* Scripts */
		add_action( 'cartflows_checkout_scripts', array( $this, 'checkout_order_scripts' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'load_compatibility_scripts_for_pro' ), 102 );

		/* add_filter( 'cartflows_checkout_layout_template', array( $this, 'include_checkout_template' ), 10, 1 );*/

		add_action( 'cartflows_checkout_form_before', array( $this, 'two_step_actions' ), 10, 1 );

		add_filter( 'woocommerce_checkout_fields', array( $this, 'cartflows_one_column_checkout_fields' ) );

		add_filter( 'woocommerce_billing_fields', array( $this, 'billing_fields_customization' ), 1000, 2 );

		add_filter( 'woocommerce_shipping_fields', array( $this, 'shipping_fields_customization' ), 1000, 2 );

		add_filter( 'woocommerce_default_address_fields', array( $this, 'woo_default_address_fields' ) );

		add_filter( 'woocommerce_get_country_locale_default', array( $this, 'prepare_country_locale' ) );

		add_filter( 'woocommerce_get_country_locale', array( $this, 'woo_get_country_locale' ) );

		/* Hide/Show Order notes  */
		add_filter( 'woocommerce_checkout_fields', array( $this, 'additional_fields_customization' ), 1000 );

		add_action( 'cartflows_checkout_after_configure_cart', array( $this, 'after_configure_cart' ), 10, 1 );

		add_action( 'woocommerce_admin_order_data_after_billing_address', array( $this, 'display_billing_custom_order_meta' ), 10, 1 );
		add_action( 'woocommerce_admin_order_data_after_shipping_address', array( $this, 'display_shipping_custom_order_meta' ), 10, 1 );

		add_filter( 'woocommerce_email_order_meta_fields', array( $this, 'custom_woo_email_order_meta_fields' ), 10, 3 );

		add_filter( 'cartflows_show_coupon_field', array( $this, 'show_hide_coupon_field_on_checkout' ), 10, 2 );

		add_filter( 'global_cartflows_js_localize', array( $this, 'add_frontend_localize_scripts' ) );

	}



	/**
	 * Two Step Layout Actions.
	 *
	 * @param int $checkout_id checkout id.
	 * @since 1.1.9
	 */
	function two_step_actions( $checkout_id ) {

		$checkout_layout = '';

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		if ( 'two-step' == $checkout_layout ) {
			add_action( 'cartflows_add_before_main_section', array( $this, 'get_checkout_form_note' ), 10, 1 );

			add_action( 'cartflows_add_before_main_section', array( $this, 'add_two_step_wrapper' ), 11 );

			add_action( 'cartflows_add_before_main_section', array( $this, 'add_two_step_nav_menu' ), 12, 1 );

			add_action( 'woocommerce_checkout_after_customer_details', array( $this, 'add_two_step_next_btn' ), 13 );

			add_action( 'cartflows_add_after_main_section', array( $this, 'add_two_step_wrap_end' ), 14 );
		}
	}

	/**
	 * Send custom fields in the order email.
	 *
	 * @param array  $fields of fields.
	 * @param string $sent_to_admin domain name to send.
	 * @param array  $order of order details.
	 */
	function custom_woo_email_order_meta_fields( $fields, $sent_to_admin, $order ) {

		// Return if order not found.
		if ( ! $order ) {
			return $fields;
		}

		$order_id    = $order->get_id();
		$checkout_id = get_post_meta( $order_id, '_wcf_checkout_id', true );

		if ( ! $checkout_id ) {
			return $fields;
		}

		// Get custom fields.
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			// Billing Fields & Values.
			$billing_fields = get_post_meta( $checkout_id, 'wcf_fields_billing', true );

			foreach ( $billing_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$fields[ $field ] = array(
						'label' => $data['label'],
						'value' => get_post_meta( $order_id, '_' . $field, true ),
					);
				}
			}

			// Shipping Fields & Values.
			$shipping_fields = get_post_meta( $checkout_id, 'wcf_fields_shipping', true );

			foreach ( $shipping_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$fields[ $field ] = array(
						'label' => $data['label'],
						'value' => get_post_meta( $order_id, '_' . $field, true ),
					);
				}
			}
		}

		return $fields;
	}

	/**
	 * After configure cart.
	 *
	 * @param int $checkout_id checkout id.
	 */
	public function after_configure_cart( $checkout_id ) {

		$discount_coupon = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-discount-coupon' );

		if ( is_array( $discount_coupon ) && ! empty( $discount_coupon ) ) {
			$discount_coupon = reset( $discount_coupon );
		}

		if ( ! empty( $discount_coupon ) ) {
			$show_coupon_msg = apply_filters( 'cartflows_show_applied_coupon_message', true );

			if ( ! $show_coupon_msg ) {
				add_filter( 'woocommerce_coupon_message', '__return_empty_string' );
			}

			WC()->cart->add_discount( $discount_coupon );

			if ( ! $show_coupon_msg ) {
				remove_filter( 'woocommerce_coupon_message', '__return_empty_string' );
			}
		}
	}

	/**
	 *  Add markup classes
	 *
	 * @return void
	 */
	function include_required_class() {

		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-order-bump-product.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pre-checkout-offer-product.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-variation-product.php';
		include_once CARTFLOWS_PRO_CHECKOUT_DIR . 'classes/class-cartflows-pro-checkout-field-optimization.php';
	}

	/**
	 * Load shortcode scripts.
	 *
	 * @return void
	 */
	function checkout_order_scripts() {

		global $post;

		if ( Cartflows_Compatibility::get_instance()->is_divi_enabled() ||
			Cartflows_Compatibility::get_instance()->is_divi_builder_enabled( $post->ID )
		) {
			$this->divi_status = true;
		}

		wp_enqueue_style( 'wcf-pro-checkout', wcf_pro()->utils->get_css_url( 'checkout-styles' ), '', CARTFLOWS_PRO_VER );

		wp_enqueue_script(
			'wcf-pro-checkout',
			wcf_pro()->utils->get_js_url( 'checkout' ),
			array( 'jquery' ),
			CARTFLOWS_PRO_VER,
			true
		);

		$pre_checkout_offer = get_post_meta( $post->ID, 'wcf-pre-checkout-offer', true );

		if ( 'yes' === $pre_checkout_offer ) {
			wp_enqueue_script(
				'wcf-pro-pre-checkout',
				wcf_pro()->utils->get_js_url( 'pre-checkout' ),
				array( 'jquery', 'jquery-ui-dialog' ),
				CARTFLOWS_PRO_VER,
				true
			);
		}

		wp_enqueue_style( 'dashicons' );

		$style = $this->generate_style();
		wp_add_inline_style( 'wcf-pro-checkout', $style );
	}

	/**
	 * Load compatibility scripts.
	 *
	 * @return void
	 */
	function load_compatibility_scripts_for_pro() {

		global $post;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = 0;

			if ( $post ) {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}
		}

		// Add DIVI Compatibility css if DIVI theme is enabled.
		if ( $this->divi_status ) {
			wp_enqueue_style( 'wcf-checkout-styles-divi', wcf_pro()->utils->get_css_url( 'checkout-styles-divi' ), '', CARTFLOWS_PRO_VER );
		}
	}

	/**
	 * Generate styles.
	 *
	 * @return string
	 */
	function generate_style() {

		global $post;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		$output = '';

		/* Remove margin for perticular product variation option. */
		$product_option   = '';
		$variation_option = '';

		$product_option   = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-options' );
		$variation_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-product-variation-options' );

		if ( 'force-all' == $product_option && 'popup' == $variation_option ) {
			$output .= '.wcf-product-option-wrap .wcf-qty-options .wcf-qty-row .wcf-item-choose-options{
				margin: 5px 0 0 0px;
			}';
		}
		/* Remove margin for perticular product variation option. */

		$primary_color    = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-primary-color' );
		$base_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-base-font-family' );

		/* For single product quick view lightbox popup*/
		$r = '';
		$g = '';
		$b = '';

		$submit_tb_padding = '';
		$submit_lr_padding = '';

		$field_heading_color = '';
		$field_color         = '';
		$field_input_size    = '';
		$field_bg_color      = '';
		$field_border_color  = '';
		$field_tb_padding    = '';
		$field_lr_padding    = '';

		$input_font_family = '';
		$input_font_weight = '';

		$submit_button_height      = '';
		$submit_color              = '';
		$submit_bg_color           = $primary_color;
		$submit_border_color       = $primary_color;
		$submit_hover_color        = '';
		$submit_bg_hover_color     = $primary_color;
		$submit_border_hover_color = $primary_color;
		$section_heading_color     = '';
		$section_bg_color          = $primary_color;

		$is_advance_option = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-advance-options-fields' );

		$button_font_family  = '';
		$button_font_weight  = '';
		$heading_font_family = '';
		$heading_font_weight = '';
		$base_font_family    = $base_font_family;

		if ( 'yes' == $is_advance_option ) {
			// Buttons, inputs, title : size, font, color, width options.
			$section_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-color' );

			$section_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-section-bg-color' );

			$field_heading_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-heading-color' );

			$submit_tb_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-tb-padding' );

			$submit_lr_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-lr-padding' );

			$field_input_size = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-field-size' );

			$field_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-color' );

			$field_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-bg-color' );

			$field_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-border-color' );

			$field_tb_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-tb-padding' );

			$field_lr_padding = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-field-lr-padding' );

			$submit_button_height = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-button-size' );

			$submit_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-color' );

			$submit_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-color', $primary_color );

			$submit_border_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-color', $primary_color );

			$submit_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-hover-color' );

			$submit_bg_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-bg-hover-color', $primary_color );

			$submit_border_hover_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-submit-border-hover-color', $primary_color );

			// Font and weight options.
			$button_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-family' );

			$button_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-button-font-weight' );

			$input_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-family' );

			$input_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-input-font-weight' );

			$heading_font_family = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-family' );

			$heading_font_weight = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-heading-font-weight' );
		}

		if ( isset( $primary_color ) ) {
			list($r, $g, $b) = sscanf( $primary_color, '#%02x%02x%02x' );
		}

			$output .= "

			#wcf-quick-view-content{
				font-family: {$base_font_family};
			}
			#wcf-quick-view-content .summary-content .product_title{
				color: {$section_heading_color};
				font-family: {$heading_font_family};
			    font-weight: {$heading_font_weight};
			}
			#wcf-quick-view-content .summary-content .variations select,
			.wcf-qty-options .wcf-qty-selection{
				color: {$field_color};
				background: {$field_bg_color};
				border-color: {$field_border_color};
				padding-top: {$field_tb_padding}px;
				padding-bottom: {$field_tb_padding}px;
				padding-left: {$field_lr_padding}px;
				padding-right: {$field_lr_padding}px;
				min-height: {$field_input_size};
				font-family: {$input_font_family};
			    font-weight: {$input_font_weight};
			}
			#wcf-quick-view-content .summary-content .single_variation_wrap .woocommerce-variation-add-to-cart button{
				color: {$submit_color};
				background: {$submit_bg_color};
				padding-top: {$submit_tb_padding}px;
				padding-bottom: {$submit_tb_padding}px;
				padding-left: {$submit_lr_padding}px;
				padding-right: {$submit_lr_padding}px;
				border-color: {$submit_border_color};
				min-height: {$submit_button_height};
				font-family: {$button_font_family};
			    font-weight: {$button_font_weight};
			}
			#wcf-quick-view-content .summary-content a{
				color: {$primary_color};
			}
			#wcf-quick-view-content .summary-content .woocommerce-product-rating .star-rating, 
			#wcf-quick-view-content .summary-content .woocommerce-product-rating .comment-form-rating .stars a, 
			#wcf-quick-view-content .summary-content .woocommerce-product-rating .star-rating::before{
			    color: {$primary_color};
			}
			.wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, .wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-after-customer .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, .wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:checked:before, .wcf-embed-checkout-form .wcf-product-option-wrap .wcf-qty-row div [type='checkbox']:checked:before {
				color: {$primary_color};
			}
			.wcf-embed-checkout-form .wcf-product-option-wrap .wcf-qty-row input[type=radio]:checked:before{
				background-color:{$primary_color};
			}
			.wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:focus,
			.wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-after-customer .wcf-bump-order-field-wrap input[type=checkbox]:focus,
			.wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-before-checkout .wcf-bump-order-field-wrap input[type=checkbox]:focus,
			.wcf-embed-checkout-form .wcf-product-option-wrap .wcf-qty-row div [type='checkbox']:focus,
			.wcf-embed-checkout-form .wcf-product-option-wrap .wcf-qty-row div [type='radio']:checked:focus,
			.wcf-embed-checkout-form .wcf-product-option-wrap .wcf-qty-row div [type='radio']:not(:checked):focus{
				border-color: {$primary_color};
    			box-shadow: 0 0 2px rgba( " . $r . ', ' . $g . ', ' . $b . ", .8);
			}
			.wcf-embed-checkout-form .woocommerce-checkout #your_products_heading{
				color: {$section_heading_color};
				font-family: {$heading_font_family};
			    font-weight: {$heading_font_weight};
			}
			img.emoji, img.wp-smiley {}
			";

		/* Add css to your order table when variation is enabled*/
		$is_variation_enabled = '';
		$is_variation_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-enable-product-options' );

		if ( 'yes' == $is_variation_enabled ) {
			$output .= '
			.wcf-embed-checkout-form table.shop_table td:first-child,
			.wcf-embed-checkout-form table.shop_table th:first-child{
			    text-align: left;
			}
			.wcf-embed-checkout-form table.shop_table td:last-child,
			.wcf-embed-checkout-form table.shop_table th:last-child{
			    text-align: right;
			}
			img.emoji, img.wp-smiley {}
			';
		}

		/* For Bump Order*/
		$bump_border_style       = '';
		$bump_border_style_value = get_post_meta( $checkout_id, 'wcf-bump-border-style', true );

		if ( 'inherit' !== $bump_border_style_value ) {
			$bump_border_style = $bump_border_style_value;
		}

		$bump_border_color = get_post_meta( $checkout_id, 'wcf-bump-border-color', true );
		$bump_bg_color     = get_post_meta( $checkout_id, 'wcf-bump-bg-color', true );

		$bump_label_color    = get_post_meta( $checkout_id, 'wcf-bump-label-color', true );
		$bump_label_bg_color = get_post_meta( $checkout_id, 'wcf-bump-label-bg-color', true );

		$bump_desc_text_color = get_post_meta( $checkout_id, 'wcf-bump-desc-text-color', true );

		$bump_hl_text_color = get_post_meta( $checkout_id, 'wcf-bump-hl-text-color', true );

		$bump_blinking_arrow_color = get_post_meta( $checkout_id, 'wcf-bump-blinking-arrow-color', true );

		$output .= "

		.wcf-bump-order-wrap{
		    background: {$bump_bg_color};
		    border-style: {$bump_border_style};
		    border-color: {$bump_border_color};
		}
		.wcf-bump-order-style-2 .wcf-bump-order-field-wrap {
		    border-color: {$bump_border_color};
		    border-top-style: {$bump_border_style};
		}
		.wcf-bump-order-style-1 .wcf-bump-order-field-wrap {
		    border-color: {$bump_border_color};
		    border-bottom-style: {$bump_border_style};
		}
		.wcf-embed-checkout-form .wcf-bump-order-wrap .wcf-bump-order-field-wrap{
		    background: {$bump_label_bg_color};
		}
		.wcf-embed-checkout-form .wcf-bump-order-wrap .wcf-bump-order-field-wrap label{
			color: {$bump_label_color};
		}
		.wcf-embed-checkout-form .wcf-bump-order-wrap .wcf-bump-order-desc{
			color: {$bump_desc_text_color};
		}
		.wcf-embed-checkout-form .wcf-bump-order-wrap .wcf-bump-order-bump-highlight {
			color: {$bump_hl_text_color};
		}
		.wcf-bump-order-wrap .dashicons-arrow-right-alt,
		.wcf-bump-order-wrap .dashicons-arrow-left-alt{
			color: {$bump_blinking_arrow_color};
		}
		img.emoji, img.wp-smiley {}
		";

		/* If divi is enabled */

		if ( $this->divi_status ) {
			$output .= "
				.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-wrap.wcf-bump-order-style-2{
				    background: {$bump_bg_color};
				    border-style: {$bump_border_style};
				    border-color: {$bump_border_color};
				}
				.et_pb_module #wcf-embed-checkout-form .wcf-bump-order-style-2 .wcf-bump-order-field-wrap{
					border-color: {$bump_border_color}!important;
		    		border-top-style: {$bump_border_style}!important;
				}
			";
		}

		/* If divi is enabled */

		/* For two Step Layout */

		// Get checkout page layout.
		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		if ( 'two-step' === $checkout_layout ) {
			$checkout_note_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note', false );

			// $two_step_title_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-title-text-color' );
			// $two_step_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-bg-color' );
			// $two_step_active_step_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-active-step-bg-color' );
			// $two_step_section_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-bg-color' );
			$step_two_width = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-width' );

			$two_step_box_text_color = '';

			$two_step_box_bg_color = '';

			$two_step_section_border = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-border' );

			if ( 'yes' == $checkout_note_enabled ) {
				$two_step_box_text_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-text-color', '' );

				$two_step_box_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-bg-color', $primary_color );
			}

			$output .= ".wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note{
			    border-color: {$two_step_box_bg_color};
			    background-color: {$two_step_box_bg_color};
			    color: {$two_step_box_text_color};
			}
			.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-note:before{
				border-top-color:{$two_step_box_bg_color};
			}";

			$output .= "
			.wcf-embed-checkout-form-two-step{
				max-width: {$step_two_width}px;
			}

			.wcf-embed-checkout-form-two-step .woocommerce{
				border-left-style:{$two_step_section_border};
			    border-right-style:{$two_step_section_border};
			    border-bottom-style:{$two_step_section_border};
			}

			.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav{
    			border-top-style: {$two_step_section_border};
				border-left-style: {$two_step_section_border};
    			border-right-style: {$two_step_section_border};
			}

			.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .wcf-current .step-name{
				color:{$primary_color};
			}
			
			.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .steps.wcf-current:before{
				background-color: {$primary_color};
			}

			.wcf-embed-checkout-form-two-step .woocommerce .wcf-embed-checkout-form-nav-btns .wcf-next-button{
				color: {$submit_color};
				background: {$submit_bg_color};
				padding-top: {$submit_tb_padding}px;
				padding-bottom: {$submit_tb_padding}px;
				padding-left: {$submit_lr_padding}px;
				padding-right: {$submit_lr_padding}px;
				border-color: {$submit_border_color};
				min-height: {$submit_button_height};
				font-family: {$button_font_family};
			    font-weight: {$button_font_weight};
			}
			.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns .wcf-next-button:hover{
				color: {$submit_hover_color};
				background-color: {$submit_bg_hover_color};
				border-color: {$submit_border_hover_color};
			}
			.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps .wcf-current .step-name{
				color: {$section_heading_color};
			}
			";
		}

		$is_pre_checkut_upsell = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer' );

		if ( 'yes' === $is_pre_checkut_upsell ) {
			$pre_checkout_bg_color = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-pre-checkout-offer-bg-color' );
			$output               .= '
				/* Pre Checkout upsell */
			';

			$output .= "
				.wcf-pre-checkout-offer-wrapper.wcf-pre-checkout-full-width.open{
					background:{$pre_checkout_bg_color};
				}
				.wcf-pre-checkout-offer-wrapper.open #wcf-pre-checkout-offer-modal{
					font-family:{$base_font_family};
				}
				.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-progress-nav-step{
					background: {$primary_color};
				}
				.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-step-line:before, 
				.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-step-line:after{
					background: {$primary_color};
				}
				.wcf-pre-checkout-offer-wrapper .wcf-content-main-head .wcf-content-modal-title .wcf_first_name{
					color:{$primary_color};
				}
				.wcf-pre-checkout-offer-wrapper #wcf-pre-checkout-offer-content button.wcf-pre-checkout-offer-btn{
					border-color: {$primary_color};
					background:{$primary_color};	
				}
				.wcf-pre-checkout-offer-wrapper .wcf-nav-bar-step.active .wcf-nav-bar-title:before{
					color: {$primary_color};
				}
			";

			$output .= '
				/* Pre Checkout upsell */
			';
		}

		return $output;
	}

	/**
	 * Save checkout fields.
	 *
	 * @param int   $order_id order id.
	 * @param array $posted posted data.
	 * @return void
	 */
	function save_checkout_fields( $order_id, $posted ) {

		if ( isset( $_POST['_wcf_bump_product'] ) ) {
			$bump_product_id = wc_clean( $_POST['_wcf_bump_product'] );

			update_post_meta( $order_id, '_wcf_bump_product', $bump_product_id );
		}
	}

	/**
	 * Save checkout fields.
	 *
	 * @param string $layout_style layout style.
	 * @return link
	 */
	function include_checkout_template( $layout_style ) {

		if ( ( 'two-step' === $layout_style ) || ( 'one-column' === $layout_style ) ) {
			return CARTFLOWS_PRO_CHECKOUT_DIR . 'templates/embed/checkout-template-simple.php';
		}

		return $layout_style;
	}

	/**
	 * Display Two Step Nav Menu.
	 *
	 * @param string $layout_style layout style.
	 * @return markup
	 */
	function add_two_step_nav_menu( $layout_style ) {

		if ( 'two-step' === $layout_style ) {
			// Get Checkout ID.
			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}

			// Get/Set default values.
			$is_note_enabled         = '';
			$checkout_note           = '';
			$step_one_title          = '';
			$step_one_sub_title      = '';
			$step_two_title          = '';
			$step_two_sub_title      = '';
			$two_step_section_border = '';

			// Get default values from the default meta to show if the advance option is not enabled.
			$all_fields = Cartflows_Default_Meta::get_instance()->get_checkout_fields( $checkout_id );

			$step_one_title     = $all_fields['wcf-checkout-step-one-title']['default'];
			$step_one_sub_title = $all_fields['wcf-checkout-step-one-sub-title']['default'];
			$step_two_title     = $all_fields['wcf-checkout-step-two-title']['default'];
			$step_two_sub_title = $all_fields['wcf-checkout-step-two-sub-title']['default'];

				// Get the values form the applied settings.
				// Get step titles.
			$step_one_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-one-title', '' );

			$step_one_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-one-sub-title', '' );

			$step_two_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-two-title', '' );

			$step_two_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-step-two-sub-title', '' );

			$two_step_section_border = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-two-step-section-border' );

			$two_step_html = '';

				$two_step_html .= "<div class='wcf-embed-checkout-form-nav wcf-border-" . $two_step_section_border . " '>";

				$two_step_html             .= "<ul class='wcf-embed-checkout-form-steps'>";
					$two_step_html         .= "<div class='steps step-one wcf-current'>";
						$two_step_html     .= "<a href='#customer_details'>";
							$two_step_html .= "<div class='step-number'>1</div>";

							$two_step_html .= "<div class='step-heading'>";

								$two_step_html .= "<div class='step-name'>" . $step_one_title . '</div>';
								$two_step_html .= "<div class='step-sub-name'>" . $step_one_sub_title . '</div>';

							$two_step_html .= '</div>';

						$two_step_html .= '</a>';
					$two_step_html     .= '</div>';

					$two_step_html         .= "<div class='steps step-two'>";
						$two_step_html     .= "<a href='.wcf-order-wrap'>";
							$two_step_html .= "<div class='step-number'>2</div>";

								$two_step_html .= "<div class='step-heading'>";

									$two_step_html .= "<div class='step-name'>" . $step_two_title . '</div>';
									$two_step_html .= "<div class='step-sub-name'>" . $step_two_sub_title . '</div>';

								$two_step_html .= '</div>';

						$two_step_html .= '</a>';
					$two_step_html     .= '</div>';

				$two_step_html .= '</ul>';
			$two_step_html     .= '</div>';

			echo $two_step_html;
		}

		return $layout_style;
	}

	/**
	 * Display Two Step note box.
	 *
	 * @param string $layout_style layout style.
	 * @return void
	 */
	function get_checkout_form_note( $layout_style ) {

		// Get Checkout ID.
		$checkout_id = 0;

		global $post;

		if ( $post ) {
			$checkout_id = $post->ID;
		}

		$is_note_enabled = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note', false );

		if ( 'yes' == $is_note_enabled ) {
			$checkout_note = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-box-note-text', '' );

			$two_step_note = '';

			$two_step_note .= "<div class='wcf-embed-checkout-form-note'>";

			$two_step_note .= '<p>' . $checkout_note . '</p>';

			$two_step_note .= '</div>';

			echo $two_step_note;
		}
	}

	/**
	 * Display Two Step Nav Next Button.
	 */
	function add_two_step_next_btn() {

		global $post;

		if ( _is_wcf_checkout_type() ) {
			$checkout_id = $post->ID;
		} else {
			$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
		}

		$checkout_layout = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-layout' );

		$button_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-offer-button-title', '' );

		$button_sub_title = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-offer-button-sub-title', '' );

		if ( 'two-step' === $checkout_layout ) {
			$two_step_next_btn_html = '';

			$two_step_next_btn_html .= '<div class="wcf-embed-checkout-form-nav-btns">';

				$two_step_next_btn_html     .= '<a href=".wcf-order-wrap" class="button wcf-next-button" >';
					$two_step_next_btn_html .= '<span class="wcf-next-button-content">';

			if ( '' != $button_title ) {
						$two_step_next_btn_html     .= '<span class="wcf-next-button-icon-wrap">';
							$two_step_next_btn_html .= '<span class="dashicons dashicons-arrow-right-alt"></span>';
							$two_step_next_btn_html .= '<span class="wcf-button-text">' . $button_title . '</span>';
						$two_step_next_btn_html     .= '</span>';
			}

			if ( '' != $button_sub_title ) {
						$two_step_next_btn_html .= '<span class="wcf-button-sub-text">' . $button_sub_title . '</span>';
			}
					$two_step_next_btn_html .= '</span>';
				$two_step_next_btn_html     .= '</a>';

			$two_step_next_btn_html .= '</div>';

			echo $two_step_next_btn_html;
		}
	}

	/**
	 * Change order comments placeholder and label, and set billing phone number to not required.
	 *
	 * @param array $fields checkout fields.
	 * @return fields
	 */
	function cartflows_one_column_checkout_fields( $fields ) {

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {
			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		$fields_skins = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );

		if ( 'style-one' == $fields_skins ) {
			/* Unset the label class of billing address 2 */
			if ( isset( $fields['billing']['billing_address_2']['label_class'] ) ) {
				unset( $fields['billing']['billing_address_2']['label_class'] );
			}

			/* Unset the label class of Shipping address 2*/
			if ( isset( $fields['shipping']['shipping_address_2']['label_class'] ) ) {
				unset( $fields['shipping']['shipping_address_2']['label_class'] );
			}

			if ( isset( $fields['billing'] ) && is_array( $fields['billing'] ) ) {
				foreach ( $fields['billing'] as $key => $field ) {
					$fields['billing'][ $key ]['placeholder'] = '';
				}
			}

			if ( isset( $fields['shipping'] ) && is_array( $fields['shipping'] ) ) {
				foreach ( $fields['shipping'] as $key => $field ) {
					$fields['shipping'][ $key ]['placeholder'] = '';
				}
			}

			if ( isset( $fields['account'] ) && is_array( $fields['account'] ) ) {
				foreach ( $fields['account'] as $key => $field ) {
					$fields['account'][ $key ]['placeholder'] = '';
				}
			}

			if ( isset( $fields['order'] ) && is_array( $fields['order'] ) ) {
				foreach ( $fields['order'] as $key => $field ) {
					$fields['order'][ $key ]['placeholder'] = '';
				}
			}
		}

		return $fields;
	}

	/**
	 * Billing field customization.
	 *
	 * @param array  $fields fields data.
	 * @param string $country country name.
	 * @return array
	 */
	function billing_fields_customization( $fields, $country ) {

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {
			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		if ( ! _is_wcf_meta_custom_checkout( $checkout_id ) ) {
			return $fields;
		}

		if ( is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$saved_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );

		if ( '' == $saved_fields ) {
			$saved_fields = Cartflows_Helper::get_checkout_fields( 'billing', $checkout_id );
		}

		return $this->prepare_address_fields( $saved_fields, $fields, 'billing', $country, $checkout_id );
	}

	/**
	 * Shipping field customization.
	 *
	 * @param array  $fields fields data.
	 * @param string $country country name.
	 * @return array
	 */
	function shipping_fields_customization( $fields, $country ) {

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {
			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		if ( ! _is_wcf_meta_custom_checkout( $checkout_id ) ) {
			return $fields;
		}

		if ( is_wc_endpoint_url( 'edit-address' ) ) {
			return $fields;
		}

		$saved_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );

		if ( '' == $saved_fields ) {
			$saved_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $checkout_id );
		}

		return $this->prepare_address_fields( $saved_fields, $fields, 'shipping', $country, $checkout_id );
	}


	/**
	 * Additional fields customization
	 *
	 * @param array $fields fields.
	 * @return array fields
	 */
	function additional_fields_customization( $fields ) {

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {
			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		/*
		If ( ! _is_wcf_meta_custom_checkout( $checkout_id ) ) {
		return $fields;
		}
		*/

		$show_shipto_diff_addr = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-shipto-diff-addr-fields' );

		$show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-checkout-additional-fields' );

		if ( 'no' === $show ) {
			if ( isset( $fields['order']['order_comments'] ) ) {
				unset( $fields['order']['order_comments'] );
				add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
			}
		}

		if ( 'no' === $show_shipto_diff_addr ) {
			add_filter( 'woocommerce_cart_needs_shipping_address', '__return_false' );
		}

		return $fields;
	}


	/**
	 * Prepare address fields.
	 *
	 * @param array  $fieldset fieldset data.
	 * @param bool   $original_fieldset is original fieldset.
	 * @param string $type address type.
	 * @param string $country country name.
	 * @param int    $checkout_id checkout ID.
	 * @return array
	 */
	function prepare_address_fields( $fieldset, $original_fieldset = false, $type = 'billing', $country, $checkout_id ) {

		if ( is_array( $fieldset ) && ! empty( $fieldset ) ) {
			$priority = 0;

			$locale = WC()->countries->get_country_locale();

			if ( isset( $locale[ $country ] ) && is_array( $locale[ $country ] ) ) {
				foreach ( $locale[ $country ] as $key => $value ) {
					if ( is_array( $value ) && isset( $fieldset[ $type . '_' . $key ] ) ) {
						if ( isset( $value['required'] ) ) {
							$fieldset[ $type . '_' . $key ]['required'] = $value['required'];
						}
					}
				}
			}

			$original_fieldset = $this->prepare_checkout_fields_lite( $fieldset, $fieldset, $checkout_id );

			if ( ! empty( $original_fieldset ) ) {
				foreach ( $original_fieldset as $fieldset_key => $fieldset_value ) {
					if ( ! isset( $fieldset_value['priority'] ) ) {
						$new_priority                                   = $priority + 10;
						$original_fieldset[ $fieldset_key ]['priority'] = $new_priority;
						$priority                                       = $new_priority;
					} else {
						$priority = $fieldset_value['priority'];
					}
				}
			}
		}

		return $original_fieldset;
	}

	/**
	 * Prepare checkout fields.
	 *
	 * @param array $fields fields data.
	 * @param bool  $original_fields is original fields.
	 * @param int   $checkout_id checkout ID.
	 * @return array
	 */
	function prepare_checkout_fields_lite( $fields, $original_fields, $checkout_id ) {

		if ( is_array( $fields ) && ! empty( $fields ) ) {

			$get_ordered_billing_fields  = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );
			$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_shipping' );
			$order_checkout_fields       = array_merge( $get_ordered_billing_fields, $get_ordered_shipping_fields );

			foreach ( $fields as $name => $field ) {

				// Backword compatibility with field enabled.
				if ( isset( $order_checkout_fields[ $name ]['enabled'] ) ) {
					$is_enabled = $order_checkout_fields[ $name ]['enabled'];
				} else {
					$is_enabled = get_post_meta( $checkout_id, 'wcf-' . $name, true );
					$is_enabled = 'yes' === $is_enabled ? true : false;
				}

				// Backword compatibility with field width.
				if ( isset( $order_checkout_fields[ $name ]['width'] ) ) {
					$field_widths = $order_checkout_fields[ $name ]['width'];
				} else {
					$field_widths = get_post_meta( $checkout_id, 'wcf-field-width_' . $name, true );
				}

				// Set/Unset field if checked/unchecked.
				if ( ! $is_enabled ) {
					unset( $original_fields[ $name ] );
					unset( $fields[ $name ] );
				} else {
					if ( ! isset( $original_fields[ $name ] ) ) {
						$original_fields[ $name ] = $field;
					}
					// Add Custom class if set.
					if ( '' != $field_widths ) {
						$original_fields[ $name ]['class'][] = 'wcf-column-' . $field_widths;
					}
				}
			}
		}

		return $original_fields;
	}

	/**
	 * Prepare country locale.
	 *
	 * @param array $fields country locale fields.
	 * @return array
	 */
	function prepare_country_locale( $fields ) {

		if ( ! _is_wcf_checkout_type() ) {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		} else {
			$checkout_id = _get_wcf_checkout_id();
		}

		if ( is_array( $fields ) ) {
			$type = apply_filters( 'wcf_address_field_override_with', 'billing' );

			$address_fields = get_option( 'wcf_fields_' . $type, array() );

			$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

			$override_required    = apply_filters( 'wcf_address_field_override_required', false );
			$override_placeholder = apply_filters( 'wcf_field_override_placeholder', true );
			$override_label       = apply_filters( 'wcf_field_override_label', true );

			if ( 'yes' == $is_custom_fields_enabled ) {
				foreach ( $fields as $key => $props ) {
					if ( isset( $props['priority'] ) ) {
						unset( $fields[ $key ]['priority'] );
					}

					if ( $override_placeholder && isset( $props['placeholder'] ) ) {
						unset( $fields[ $key ]['placeholder'] );
					}
					if ( $override_label && isset( $props['label'] ) ) {
						unset( $fields[ $key ]['label'] );
					}
				}
			} else {
				$fields_skins = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-fields-skins' );

				if ( 'style-one' == $fields_skins ) {
					foreach ( $fields as $key => $props ) {
						if ( $override_placeholder && isset( $props['placeholder'] ) ) {
							unset( $fields[ $key ]['placeholder'] );
						}
					}
				}
			}
		}
		return $fields;
	}

	/**
	 * Prepare default country locale.
	 *
	 * @param array $fields country locale fields.
	 * @return array
	 */
	function woo_default_address_fields( $fields ) {

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {

			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}
		} else {

			if ( _is_wcf_doing_checkout_ajax() ) {

				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $fields;
			}
		}

		$is_custom_fields_enabled = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' == $is_custom_fields_enabled ) {

			$sname = apply_filters( 'wcf_address_field_override_with', 'billing' );

			if ( 'billing' === $sname || 'shipping' === $sname ) {

				$address_fields = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf_field_order_billing' );

				if ( '' == $address_fields ) {

					$address_fields = Cartflows_Helper::get_checkout_fields( $sname, $checkout_id );
				}

				if ( is_array( $address_fields ) && ! empty( $address_fields ) && ! empty( $fields ) ) {
					$override_required = apply_filters( 'wcf_address_field_override_required', true );

					foreach ( $fields as $name => $field ) {
						$fname = $sname . '_' . $name;

						if ( $this->_is_locale_field( $fname ) && $override_required ) {
							$custom_field = isset( $address_fields[ $fname ] ) ? $address_fields[ $fname ] : false;

							if ( $custom_field && ! ( isset( $custom_field['enabled'] ) && false == $custom_field['enabled'] ) ) {
								$fields[ $name ]['required'] = isset( $custom_field['required'] ) && $custom_field['required'] ? true : false;
							}
						}
					}
				}
			}
		}

		return $fields;
	}


	/**
	 * Get country locale.
	 *
	 * @param array $locale country locale.
	 * @return array
	 */
	function woo_get_country_locale( $locale ) {

		if ( ! _is_wcf_checkout_type() ) {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $locale;
			}
		} else {
			$checkout_id = _get_wcf_checkout_id();
		}

		if ( is_array( $locale ) ) {
			foreach ( $locale as $country => $fields ) {
				$locale[ $country ] = $this->prepare_country_locale( $fields );
			}
		}

		return $locale;
	}

	/**
	 * Set locale fields.
	 *
	 * @param string $field_name field name.
	 * @return bool
	 */
	function _is_locale_field( $field_name ) {
		if ( ! empty( $field_name ) && in_array(
			$field_name,
			array(
				'billing_address_1',
				'billing_address_2',
				'billing_state',
				'billing_postcode',
				'billing_city',
				'billing_country',
				'shipping_address_1',
				'shipping_address_2',
				'shipping_state',
				'shipping_postcode',
				'shipping_city',
				'shipping_country',
			)
		)
		) {
			return true;
		}
		return false;
	}

	/**
	 * Show/Hide coupon field on checkout page
	 *
	 * @param bool $is_field true.
	 * @param bool $optimized_field true.
	 * @return bool
	 */
	function show_hide_coupon_field_on_checkout( $is_field, $optimized_field = false ) {

		if ( _is_wcf_checkout_type() || _is_wcf_checkout_shortcode() ) {
			global $post;

			if ( _is_wcf_checkout_type() ) {
				$checkout_id = $post->ID;
			} else {
				$checkout_id = _get_wcf_checkout_id_from_shortcode( $post->post_content );
			}
		} else {
			if ( _is_wcf_doing_checkout_ajax() ) {
				$checkout_id = wcf()->utils->get_checkout_id_from_post_data();
			} else {
				return $is_field;
			}
		}

		$show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-show-coupon-field' );

		if ( $optimized_field ) {
			$optimized_show = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-optimize-coupon-field' );

			if ( 'yes' === $show && 'yes' === $optimized_show ) {
				return true;
			}
		} else {
			if ( 'yes' === $show ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Display billing custom field data on order page
	 *
	 * @param obj $order Order object.
	 * @return void
	 */
	function display_billing_custom_order_meta( $order ) {

		if ( ! $order ) {
			return;
		}

		$order_id    = $order->get_id();
		$checkout_id = get_post_meta( $order_id, '_wcf_checkout_id', true );

		/* Custom Field To Do */
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			$output = '';

			$billing_fields = get_post_meta( $checkout_id, 'wcf_fields_billing', true );

			foreach ( $billing_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$output .= '<p><strong>' . $data['label'] . ':</strong> ' . get_post_meta( $order_id, '_' . $field, true ) . '</p>';
				}
			}

			if ( '' !== $output ) {
				$output = '<h3>' . __( 'Billing Custom Fields', 'cartflows-pro' ) . '</h3>' . $output;
			}

			echo $output;
		}
	}

	/**
	 * Display shipping custom field data on order page
	 *
	 * @param obj $order Order object.
	 * @return void
	 */
	function display_shipping_custom_order_meta( $order ) {

		if ( ! $order ) {
			return;
		}

		$order_id    = $order->get_id();
		$checkout_id = get_post_meta( $order_id, '_wcf_checkout_id', true );

		/* Custom Field To Do */
		$custom_fields = get_post_meta( $checkout_id, 'wcf-custom-checkout-fields', true );

		if ( 'yes' === $custom_fields ) {
			$output = '';

			$shipping_fields = get_post_meta( $checkout_id, 'wcf_fields_shipping', true );

			foreach ( $shipping_fields as $field => $data ) {
				if ( isset( $data['custom'] ) && $data['custom'] ) {
					$output .= '<p><strong>' . $data['label'] . ':</strong> ' . get_post_meta( $order_id, '_' . $field, true ) . '</p>';
				}
			}

			if ( '' !== $output ) {
				$output = '<h3>' . __( 'Shipping Custom Fields', 'cartflows-pro' ) . '</h3>' . $output;
			}

			echo $output;
		}
	}

	/**
	 * Add opening dev
	 *
	 * @since 1.1.9
	 */
	function add_two_step_wrapper() {

		echo "<div class='wcf-two-step-wrap'> ";
	}

	/**
	 * Add Startng & closing dev
	 *
	 * @since 1.1.9
	 */
	function add_two_step_wrap_end() {

		echo '</div> ';
	}


	/**
	 * Add localize variables.
	 *
	 * @since 1.1.5
	 * @param array $localize settings.
	 * @return array $localize settings.
	 */
	function add_frontend_localize_scripts( $localize ) {

		$localize['allow_autocomplete_zipcode'] = apply_filters( 'cartflows_autocomplete_zip_data', 'yes' );
		$localize['add_to_cart_text']           = __( 'Processing...', 'cartflows-pro' );
		return $localize;
	}

}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Checkout_Markup::get_instance();
