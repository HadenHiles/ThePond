<?php
/**
 * Base Offer meta.
 *
 * @package cartflows
 */

/**
 * Meta Boxes setup
 */
class Cartflows_Pro_Base_Offer_Meta {


	/**
	 * Instance
	 *
	 * @var $instance
	 */
	private static $instance;

	/**
	 * Meta Option
	 *
	 * @var $meta_option
	 */
	private static $meta_option = null;

	/**
	 * Template Type
	 *
	 * @var $template_type
	 */
	private static $template_type = null;

	/**
	 * Initiator
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

		/* Init Metabox */
		add_action( 'load-post.php', array( $this, 'init_metabox' ) );
		add_action( 'load-post-new.php', array( $this, 'init_metabox' ) );
	}

	/**
	 * Init Metabox
	 */
	public function init_metabox() {

		add_action( 'add_meta_boxes', array( $this, 'setup_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );
	}

	/**
	 *  Setup Metabox
	 */
	function setup_meta_box() {

		if ( ! wcf_pro()->is_woo_active ) {
			return;
		}

		if ( _is_wcf_base_offer_type() ) {
			add_meta_box(
				'wcf-offer-settings',                // Id.
				__( 'Offer Page Settings', 'cartflows-pro' ), // Title.
				array( $this, 'markup_meta_box' ),      // Callback.
				wcf()->utils->get_step_post_type(),                 // Post_type.
				'normal',                               // Context.
				'high'                                  // Priority.
			);
		}
	}

	/**
	 * Metabox Markup
	 *
	 * @param  object $post Post object.
	 * @return void
	 */
	function markup_meta_box( $post ) {

		wp_nonce_field( 'save-nonce-offer-step-meta', 'nonce-offer-step-meta' );

		$stored = get_post_meta( $post->ID );

		$offers_meta = self::get_meta_option( $post->ID );

		// Set stored and override defaults.
		foreach ( $stored as $key => $value ) {
			if ( array_key_exists( $key, $offers_meta ) ) {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? maybe_unserialize( $stored[ $key ][0] ) : '';
			} else {
				self::$meta_option[ $key ]['default'] = ( isset( $stored[ $key ][0] ) ) ? $stored[ $key ][0] : '';
			}
		}

		// Get defaults.
		$meta    = self::get_meta_option( $post->ID );
		$options = array();

		foreach ( $meta as $key => $value ) {

			$options[ $key ] = $meta[ $key ]['default'];
		}

		do_action( 'wcf_offer_settings_markup_before' );
		$this->offer_metabox_html( $options, $post->ID );
		do_action( 'wcf_offer_settings_markup_after' );
	}

	/**
	 * Page Header Tabs
	 *
	 * @param array $options options.
	 * @param int   $post_id post ID.
	 */
	function offer_metabox_html( $options, $post_id ) {

		$step_type = get_post_meta( $post_id, 'wcf-step-type', true );

		$active_tab = get_post_meta( $post_id, 'wcf-active-tab', true );

		$flow_id = get_post_meta( $post_id, 'wcf-flow-id', true );

		if ( empty( $active_tab ) ) {
			$active_tab = 'wcf-offer-general';
		}

			$tabs = array(
				array(
					'title' => __( 'Shortcodes', 'cartflows-pro' ),
					'id'    => 'wcf-offer-shortcodes',
					'class' => 'wcf-offer-shortcodes' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
					'icon'  => 'dashicons-editor-code',
				),
				array(
					'title' => __( 'Select Product', 'cartflows-pro' ),
					'id'    => 'wcf-offer-general',
					'class' => 'wcf-offer-general' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
					'icon'  => 'dashicons-info',
				),
				array(
					'title' => __( 'Conditional Redirect ', 'cartflows-pro' ),
					'id'    => 'wcf-conditions',
					'class' => 'wcf-conditionals' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
					'icon'  => 'dashicons-randomize',
				),
				array(
					'title' => __( 'Custom Script', 'cartflows-pro' ),
					'id'    => 'wcf-offer-custom-script-header',
					'class' => 'wcf-offer-custom-script-header' === $active_tab ? 'wcf-tab wp-ui-text-highlight active' : 'wcf-tab',
					'icon'  => 'dashicons-format-aside',
				),
			);

			$tabs = apply_filters( 'cartflows_offer_panel_tabs', $tabs, $active_tab );

			?>
		<div class="wcf-offer-table wcf-metabox-wrap widefat">
			<div class="wcf-table-container">
				<div class="wcf-column-left">
					<div class="wcf-tab-wrapper">
						<?php
						foreach ( $tabs as $key => $tab ) {

							?>
							<div class="<?php echo esc_attr( $tab['class'] ); ?>" data-tab="<?php echo esc_attr( $tab['id'] ); ?>">
								<span class="dashicons <?php echo esc_attr( $tab['icon'] ); ?>"></span>
								<span class="wcf-tab-title"><?php echo esc_html( $tab['title'] ); ?></span>
							</div>
						<?php } ?>
						<input type="hidden" id="wcf-active-tab" name="wcf-active-tab" value="<?php echo esc_attr( $active_tab ); ?>" />
					</div>
				</div>
				<div class="wcf-column-right">
					<div class="wcf-offer-shortcodes wcf-tab-content active widefat">
						<?php

							$offer_yes_link = '';
							$offer_no_link  = '';

						if ( 'upsell' === $step_type ) {

							$offer_yes_link = wcf()->utils->get_linking_url(
								array( 'class' => 'wcf-up-offer-yes' )
							);

							$offer_no_link = wcf()->utils->get_linking_url(
								array( 'class' => 'wcf-up-offer-no' )
							);
						}

						if ( 'downsell' === $step_type ) {

							$offer_yes_link = wcf()->utils->get_linking_url(
								array( 'class' => 'wcf-down-offer-yes' )
							);

							$offer_no_link = wcf()->utils->get_linking_url(
								array( 'class' => 'wcf-down-offer-no' )
							);
						}

							echo wcf()->meta->get_shortcode_field(
								array(
									'label'   => __( 'Offer - Yes Link', 'cartflows-pro' ),
									'name'    => 'wcf-offer-yes',
									'content' => $offer_yes_link,
								)
							);

							echo wcf()->meta->get_shortcode_field(
								array(
									'label'   => __( 'Offer - No Link', 'cartflows-pro' ),
									'name'    => 'wcf-offer-no',
									'content' => $offer_no_link,
								)
							);

						?>
					</div>
					<div class="wcf-offer-general wcf-tab-content active widefat">

						<?php

						echo wcf()->meta->get_product_selection_field(
							array(
								'label' => __( 'Select Product', 'cartflows-pro' ),
								'name'  => 'wcf-offer-product',
								'value' => $options['wcf-offer-product'],
							)
						);

						echo wcf()->meta->get_number_field(
							array(
								'label' => __( 'Product Quantity', 'cartflows-pro' ),
								'name'  => 'wcf-offer-quantity',
								'value' => $options['wcf-offer-quantity'],
							)
						);

						echo wcf()->meta->get_select_field(
							array(
								'label'   => __( 'Discount Type', 'cartflows-pro' ),
								'options' => array(
									''                 => __( 'Original', 'cartflows-pro' ),
									'discount_percent' => __( 'Discount Percentage', 'cartflows-pro' ),
									'discount_price'   => __( 'Discount Price', 'cartflows-pro' ),
								),
								'name'    => 'wcf-offer-discount',
								'value'   => $options['wcf-offer-discount'],
							)
						);

						echo wcf()->meta->get_number_field(
							array(
								'label' => __( 'Discount value', 'cartflows-pro' ),
								'name'  => 'wcf-offer-discount-value',
								'value' => $options['wcf-offer-discount-value'],
							)
						);

						echo wcf()->meta->get_description_field(
							array(
								'name'    => 'wcf-price-notice',
								'content' => __( 'Select product and save once to see prices', 'cartflows-pro' ),
							)
						);

						echo wcf()->meta->get_display_field(
							array(
								'label'   => __( 'Original Price', 'cartflows-pro' ),
								'name'    => 'wcf-original-price',
								'content' => $this->get_original_price( $options, $post_id ),
							)
						);

						echo wcf()->meta->get_display_field(
							array(
								'label'   => __( 'Discount Price', 'cartflows-pro' ),
								'name'    => 'wcf-discount-price',
								'content' => $this->get_discount_price( $options, $post_id ),
							)
						);

						?>
					</div>
					<div class="wcf-conditions wcf-tab-content active widefat">
						<?php
						echo wcf_pro()->meta->get_optgroup_field(
							array(
								'label'           => __( 'Offer - Yes Next Step', 'cartflows-pro' ),
								'optgroup'        => array(
									'upsell'   => __( 'Upsell &lpar;Woo&rpar;', 'cartflows-pro' ),
									'downsell' => __( 'Downsell &lpar;Woo&rpar;', 'cartflows-pro' ),
									'thankyou' => __( 'Thankyou &lpar;Woo&rpar;', 'cartflows-pro' ),
								),
								'name'            => 'wcf-yes-next-step',
								'value'           => $options['wcf-yes-next-step'],
								'data-flow-id'    => $flow_id,
								'data-exclude-id' => $post_id,
							)
						);

						echo wcf_pro()->meta->get_optgroup_field(
							array(
								'label'           => __( 'Offer - No Next Step', 'cartflows-pro' ),
								'optgroup'        => array(
									'upsell'   => __( 'Upsell &lpar;Woo&rpar;', 'cartflows-pro' ),
									'downsell' => __( 'Downsell &lpar;Woo&rpar;', 'cartflows-pro' ),
									'thankyou' => __( 'Thankyou &lpar;Woo&rpar;', 'cartflows-pro' ),
								),
								'name'            => 'wcf-no-next-step',
								'value'           => $options['wcf-no-next-step'],
								'data-flow-id'    => $flow_id,
								'data-exclude-id' => $post_id,
							)
						);
						?>
					</div>
					<div class="wcf-offer-custom-script-header wcf-tab-content active widefat">
						<?php
						echo wcf()->meta->get_area_field(
							array(
								'label' => __( 'Custom Script', 'cartflows-pro' ),
								'name'  => 'wcf-custom-script',
								'value' => htmlspecialchars( $options['wcf-custom-script'] ),
								'help'  => __( 'Custom script lets you add your own custom script on front end of this flow page.', 'cartflows-pro' ),
							)
						);
						?>
					</div>
					<?php do_action( 'cartflows_offer_panel_tab_content', $options, $post_id ); ?>
				</div>
			</div>
		</div>

		<?php
	}

	/**
	 * Get original price
	 *
	 * @param array $options options.
	 * @param int   $post_id post id.
	 * @return string
	 */
	function get_original_price( $options, $post_id ) {

		$offer_product = $options['wcf-offer-product'];

		$custom_price = __( 'Product not selected', 'cartflows-pro' );

		if ( isset( $offer_product[0] ) ) {

			$custom_price = __( 'Product not exists', 'cartflows-pro' );

			$product_id = $offer_product[0];

			$product = wc_get_product( $product_id );

			if ( is_object( $product ) ) {

				$custom_price = $product->get_price();

				/* Product Quantity */
				$product_qty = intval( $options['wcf-offer-quantity'] );

				if ( $product_qty > 1 ) {
					$custom_price = $custom_price * $product_qty;
				}

				return wc_price( $custom_price );
			}
		}

		return $custom_price;
	}

	/**
	 * Get discount price
	 *
	 * @param array $options options.
	 * @param int   $post_id post id.
	 * @return string
	 */
	function get_discount_price( $options, $post_id ) {

		$offer_product = $options['wcf-offer-product'];

		$custom_price = __( 'Product not selected', 'cartflows-pro' );

		if ( isset( $offer_product[0] ) ) {

			$custom_price = __( 'Product not exists', 'cartflows-pro' );

			$product_id = $offer_product[0];

			$product = wc_get_product( $product_id );

			if ( is_object( $product ) ) {

				$custom_price = $product->get_price();

				/* Product Quantity */
				$product_qty = intval( $options['wcf-offer-quantity'] );

				if ( $product_qty > 1 ) {
					$custom_price = $custom_price * $product_qty;
				}

				/* Offer Discount */
				$discount_type = $options['wcf-offer-discount'];

				if ( ! empty( $discount_type ) ) {

					$discount_value = intval( $options['wcf-offer-discount-value'] );

					if ( 'discount_percent' === $discount_type ) {

						if ( $discount_value > 0 ) {
							$custom_price = $custom_price - ( ( $custom_price * $discount_value ) / 100 );
						}
					} elseif ( 'discount_price' === $discount_type ) {

						if ( $discount_value > 0 ) {
							$custom_price = $custom_price - $discount_value;
						}
					}
				}

				return wc_price( $custom_price );
			}
		}

		return $custom_price;
	}


	/**
	 * Get metabox options
	 *
	 * @param int $post_id post ID.
	 * @return array
	 */
	public static function get_meta_option( $post_id ) {

		if ( null === self::$meta_option ) {

			/**
			 * Set metabox options
			 */
			self::$meta_option = wcf_pro()->options->get_offer_fields( $post_id );
		}

		return self::$meta_option;
	}

	/**
	 * Metabox Save
	 *
	 * @param  number $post_id Post ID.
	 * @return void
	 */
	function save_meta_box( $post_id ) {

		// Checks save status.
		$is_autosave = wp_is_post_autosave( $post_id );
		$is_revision = wp_is_post_revision( $post_id );

		$is_valid_nonce = ( isset( $_POST['nonce-offer-step-meta'] ) && wp_verify_nonce( $_POST['nonce-offer-step-meta'], 'save-nonce-offer-step-meta' ) ) ? true : false;

		// Exits script depending on save status.
		if ( $is_autosave || $is_revision || ! $is_valid_nonce ) {
			return;
		}

		wcf_pro()->options->save_offer_fields( $post_id );
	}
}


/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Base_Offer_Meta::get_instance();
