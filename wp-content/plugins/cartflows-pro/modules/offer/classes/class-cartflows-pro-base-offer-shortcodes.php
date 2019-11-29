<?php
/**
 * Logger.
 *
 * @package cartflows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Pro_Base_Offer_Shortcodes {


	/**
	 * Member Variable
	 *
	 * @var instance
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

		/* Offer Shortcode */
		add_shortcode( 'cartflows_offer', array( $this, 'offer_button_shortcode_markup' ) );

		/* Offer Shortcode */
		add_shortcode( 'cartflows_offer_link_yes', array( $this, 'offer_link_yes_markup' ) );
		add_shortcode( 'cartflows_offer_link_no', array( $this, 'offer_link_no_markup' ) );

		/* Order details shortcode */
		add_shortcode( 'cartflows_order_info', array( $this, 'order_info_markup' ) );

	}

	/**
	 * Offer shortcode markup
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	function offer_button_shortcode_markup( $atts ) {

		$atts = shortcode_atts(
			array(
				'id'     => 0,
				'action' => 'yes',
				'text'   => '',
				'type'   => 'link',
			),
			$atts,
			'cartflows_offer'
		);

		if ( '' === $atts['text'] ) {

			if ( 'yes' === $atts['action'] ) {
				$atts['text'] = __( 'Buy Now', 'cartflows-pro' );
			} else {
				$atts['text'] = __( 'No, Thanks', 'cartflows-pro' );
			}
		}

		$output = '';

		if ( _is_wcf_base_offer_type() ) {

			if ( ! $atts['id'] ) {
				$step_id = _get_wcf_base_offer_id();
			} else {
				$step_id = intval( $atts['id'] );
			}

			if ( $step_id ) {

				$action = $atts['action'];

				$template_type = get_post_meta( $step_id, 'wcf-step-type', true );

				$order_id  = ( isset( $_GET['wcf-order'] ) ) ? $_GET['wcf-order'] : '';
				$order_key = ( isset( $_GET['wcf-key'] ) ) ? $_GET['wcf-key'] : '';

				$classes = 'wcf-' . $template_type . '-offer-yes';

				$order = wc_get_order( $order_id );

				if ( $order ) {

					$payment_method = $order->get_payment_method();
					$gateways       = array( 'paypal', 'ppec_paypal' );
					$gateways       = apply_filters( 'cartflows_offer_supported_payment_gateway_slugs', $gateways );

					if ( ( in_array( $payment_method, $gateways, true ) ) && ! wcf_pro()->utils->is_reference_transaction() ) {

						$classes .= ' cartflows-skip';
					}
				}

				$attr = array(
					'target'         => '_self',
					'href'           => 'javascript:void(0)',
					'data-order'     => $order_id,
					'data-order_key' => $order_key,
					'data-action'    => 'yes',
					'data-step'      => $step_id,
					'class'          => $classes,
					'id'             => 'wcf-' . $template_type . '-offer',
				);

				if ( 'button' === $atts['type'] ) {
					$attr['class'] = $attr['class'] . ' wcf-button button';
				}

				if ( 'yes' === $action ) {

					$flow_id = wcf()->utils->get_flow_id_from_step_id( $step_id );

					if ( wcf()->flow->is_flow_testmode( $flow_id ) ) {

						$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product', 'dummy' );

						if ( 'dummy' === $offer_product ) {

							$args = array(
								'posts_per_page' => 1,
								'orderby'        => 'rand',
								'post_type'      => 'product',
							);

							$random_product = get_posts( $args );

							if ( isset( $random_product[0]->ID ) ) {
								$offer_product = array(
									$random_product[0]->ID,
								);
							}
						}
					} else {
						$offer_product = wcf_pro()->options->get_offers_meta_value( $step_id, 'wcf-offer-product' );
					}

					if ( isset( $offer_product[0] ) ) {

						$product_id = $offer_product[0];

						$attr['data-product'] = $product_id;

						$attr_string = '';

						foreach ( $attr as $key => $value ) {
							$attr_string .= ' ' . $key . '= "' . $value . '"';
						}

						$output = '<div><a ' . $attr_string . '>' . $atts['text'] . '</a></div>';
					}
				} elseif ( 'no' === $action ) {

					$attr['data-action'] = 'no';

					$attr_string = '';

					foreach ( $attr as $key => $value ) {
						$attr_string .= ' ' . $key . '= "' . $value . '"';
					}

					$output = '<div><a ' . $attr_string . '>' . $atts['text'] . '</a></div>';
				}
			}
		}

		return $output;
	}

	/**
	 * Offer shortcode markup
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	function offer_link_yes_markup( $atts ) {

		$order_id  = ( isset( $_GET['wcf-order'] ) ) ? $_GET['wcf-order'] : '';
		$order_key = ( isset( $_GET['wcf-key'] ) ) ? $_GET['wcf-key'] : '';

		$output = '#';

		if ( _is_wcf_base_offer_type() ) {

			$step_id = _get_wcf_base_offer_id();

			if ( $step_id ) {

				$action = 'yes';

				$template_type = get_post_meta( $step_id, 'wcf-step-type', true );

				$attr = array(
					'class' => 'wcf-' . $template_type . '-offer-' . $action,
				);

				$order = wc_get_order( $order_id );

				if ( $order ) {

					$payment_method = $order->get_payment_method();

					$gateways = array( 'paypal', 'ppec_paypal' );
					$gateways = apply_filters( 'cartflows_offer_supported_payment_gateway_slugs', $gateways );
					if ( ( in_array( $payment_method, $gateways, true ) ) && ! wcf_pro()->utils->is_reference_transaction() ) {

						$attr['skip'] = 'cartflows-skip';
					}
				}

				$attr_string = '?';

				foreach ( $attr as $key => $value ) {
					$attr_string .= $key . '=' . $value . '&';
				}

				$output = rtrim( $attr_string, '&' );
			}
		}

		return $output;
	}

	/**
	 * Offer shortcode markup
	 *
	 * @param array $atts attributes.
	 * @return string
	 */
	function offer_link_no_markup( $atts ) {

		$output = '#';

		if ( _is_wcf_base_offer_type() ) {

			$step_id = _get_wcf_base_offer_id();

			if ( $step_id ) {

				$action = 'no';

				$template_type = get_post_meta( $step_id, 'wcf-step-type', true );

				$attr = array(
					'class' => 'wcf-' . $template_type . '-offer-' . $action,
				);

				$attr_string = '?';

				foreach ( $attr as $key => $value ) {
					$attr_string .= $key . '=' . $value . '&';
				}

				$output = rtrim( $attr_string, '&' );
			}
		}

		return $output;
	}

	/**
	 * Order details shortcode markup.
	 *
	 * @param array $atts attributes ( $atts['key'] can be first_name, last_name, email, phone, city ).
	 * @return string
	 */
	function order_info_markup( $atts ) {

		if ( ! _is_wcf_base_offer_type() ) {
			return;
		}

		$output    = '';
		$order_id  = isset( $_GET['wcf-order'] ) ? intval( $_GET['wcf-order'] ) : '';
		$order_key = isset( $_GET['wcf-key'] ) ? wc_clean( wp_unslash( $_GET['wcf-key'] ) ) : '';

		$field   = isset( $atts['field'] ) ? sanitize_text_field( $atts['field'] ) : '';
		$def_val = isset( $atts['default'] ) ? sanitize_text_field( $atts['default'] ) : '';

		if ( ! empty( $order_key ) && ! empty( $order_id ) ) {

			$order = wc_get_order( $order_id );

			// Validate order key.
			if ( ! $order || $order->get_order_key() !== $order_key ) {
				return $output;
			}

			$order_data = $order->get_data();
			$type       = isset( $atts['type'] ) ? sanitize_text_field( $atts['type'] ) : 'billing';
			$details    = isset( $order_data[ $type ] ) ? $order_data[ $type ] : array();

			if ( '' !== $field ) {

				if ( ! empty( $details ) && isset( $details[ $field ] ) && '' !== $details[ $field ] ) {
					$output .= $details[ $field ];
				} else {
					$output .= $def_val;
				}
			}
		}

		return $output;
	}
}

/**
 * Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Base_Offer_Shortcodes::get_instance();
