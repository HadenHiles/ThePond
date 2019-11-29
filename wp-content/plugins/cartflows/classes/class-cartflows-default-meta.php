<?php
/**
 * Cartflow default options.
 *
 * @package Cartflows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Default_Meta {



	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var checkout_fields
	 */
	private static $checkout_fields = null;

	/**
	 * Member Variable
	 *
	 * @var checkout_fields
	 */
	private static $thankyou_fields = null;

	/**
	 * Member Variable
	 *
	 * @var flow_fields
	 */
	private static $flow_fields = null;

		/**
		 * Member Variable
		 *
		 * @var landing_fields
		 */
	private static $landing_fields = null;

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
	}

	/**
	 *  Checkout Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	function get_checkout_fields( $post_id ) {

		if ( null === self::$checkout_fields ) {
			self::$checkout_fields = array(
				'wcf-field-google-font-url'     => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-checkout-products'         => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_CHECKOUT_PRODUCTS',
				),
				'wcf-checkout-layout'           => array(
					'default'  => 'two-column',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-font-family'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-font-weight'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-heading-font-family'       => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-heading-font-weight'       => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-base-font-family'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-advance-options-fields'    => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-base-font-weight'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-button-font-family'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-button-font-weight'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-primary-color'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-heading-color'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-section-bg-color'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-hl-bg-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-tb-padding'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-lr-padding'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-fields-skins'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-field-size'          => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-color'               => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-bg-color'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-border-color'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-box-border-color'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-field-label-color'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-tb-padding'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-lr-padding'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-input-button-size'         => array(
					'default'  => '33px',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-color'              => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-hover-color'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-bg-color'           => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-bg-hover-color'     => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-border-color'       => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-submit-border-hover-color' => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-active-tab'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-header-logo-image'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-header-logo-width'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-custom-script'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);

			self::$checkout_fields = apply_filters( 'cartflows_checkout_meta_options', self::$checkout_fields, $post_id );
		}

		return self::$checkout_fields;
	}

	/**
	 *  Save Checkout Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	function save_checkout_fields( $post_id ) {

		$post_meta = $this->get_checkout_fields( $post_id );

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Save Landing Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	function save_landing_fields( $post_id ) {

		$post_meta = $this->get_landing_fields( $post_id );

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Save ThankYou Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	function save_thankyou_fields( $post_id ) {

		$post_meta = $this->get_thankyou_fields( $post_id );

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Flow Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	function get_flow_fields( $post_id ) {

		if ( null === self::$flow_fields ) {
			self::$flow_fields = array(
				'wcf-steps'   => array(
					'default'  => array(),
					'sanitize' => 'FILTER_DEFAULT',
				),

				'wcf-testing' => array(
					'default'  => 'no',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}

		return apply_filters( 'cartflows_flow_meta_options', self::$flow_fields );
	}

	/**
	 *  Save Flow Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	function save_flow_fields( $post_id ) {

		$post_meta = $this->get_flow_fields( $post_id );

		if ( isset( $post_meta['wcf-steps'] ) ) {
			unset( $post_meta['wcf-steps'] );
		}

		$this->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Save Meta fields - Common Function.
	 *
	 * @param int   $post_id post id.
	 * @param array $post_meta options to store.
	 * @return void
	 */
	function save_meta_fields( $post_id, $post_meta ) {

		if ( ! ( $post_id && is_array( $post_meta ) ) ) {
			return;
		}

		foreach ( $post_meta as $key => $data ) {
			$meta_value = false;

			// Sanitize values.
			$sanitize_filter = ( isset( $data['sanitize'] ) ) ? $data['sanitize'] : 'FILTER_DEFAULT';

			switch ( $sanitize_filter ) {
				case 'FILTER_SANITIZE_STRING':
					$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_STRING );
					break;

				case 'FILTER_SANITIZE_URL':
					$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_URL );
					break;

				case 'FILTER_SANITIZE_NUMBER_INT':
					$meta_value = filter_input( INPUT_POST, $key, FILTER_SANITIZE_NUMBER_INT );
					break;

				case 'FILTER_CARTFLOWS_ARRAY':
					if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
						$meta_value = array_map( 'sanitize_text_field', $_POST[ $key ] );
					}
					break;

				case 'FILTER_CARTFLOWS_CHECKOUT_PRODUCTS':
					if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
						$i = 0;
						$q = 0;

						foreach ( $_POST[ $key ] as $p_index => $p_data ) {
							foreach ( $p_data as $i_key => $i_value ) {
								if ( is_array( $i_value ) ) {
									foreach ( $i_value as $q_key => $q_value ) {
										$meta_value[ $i ][ $i_key ][ $q ] = array_map( 'sanitize_text_field', $q_value );

										$q++;
									}
								} else {
									$meta_value[ $i ][ $i_key ] = sanitize_text_field( $i_value );
								}
							}

							$i++;
						}
					}
					break;
				case 'FILTER_CARTFLOWS_CHECKOUT_FIELDS':
					$count          = 10;
					$ordered_fields = array();
					$billing_fields = array();

					if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
							$post_data = $_POST[ $key ];

						if ( 'wcf_field_order_billing' == $key ) {
							$billing_fields = Cartflows_Helper::get_checkout_fields( 'billing', $post_id );

							foreach ( $post_data as $index => $value ) {
								if ( isset( $billing_fields[ $value ] ) ) {
									$ordered_fields[ $value ]             = $billing_fields[ $value ];
									$ordered_fields[ $value ]['priority'] = $count;
									$count                               += 10;
								}
							}

							$meta_value = $ordered_fields;
						}

						if ( 'wcf_field_order_shipping' == $key ) {
							$shipping_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $post_id );
							foreach ( $post_data as $index => $value ) {
								if ( isset( $shipping_fields[ $value ] ) ) {
									$ordered_fields[ $value ]             = $shipping_fields[ $value ];
									$ordered_fields[ $value ]['priority'] = $count;
									$count                               += 10;
								}
							}
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_label_text_field_billing' == $key ) {
							$get_ordered_billing_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_billing' );

							if ( isset( $get_ordered_billing_fields ) && ! empty( $get_ordered_billing_fields ) ) {
								echo 'con 1';
								$billing_fields = $get_ordered_billing_fields;
							} else {
								echo 'con 2';
								$billing_fields = Cartflows_Helper::get_checkout_fields( 'billing', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								if ( isset( $billing_fields[ $index ] ) ) {
									$ordered_fields[ $index ]          = $billing_fields[ $index ];
									$ordered_fields[ $index ]['label'] = wp_kses_post( trim( stripslashes( $value ) ) );
								}
							}
							$key        = 'wcf_field_order_billing';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_label_text_field_shipping' == $key ) {
							$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_shipping' );

							if ( isset( $get_ordered_shipping_fields ) && ! empty( $get_ordered_shipping_fields ) ) {
									$shipping_fields = $get_ordered_shipping_fields;
							} else {
								$shipping_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								if ( isset( $shipping_fields[ $index ] ) ) {
									$ordered_fields[ $index ]          = $shipping_fields[ $index ];
									$ordered_fields[ $index ]['label'] = wp_kses_post( trim( stripslashes( $value ) ) );
								}
							}
							$key        = 'wcf_field_order_shipping';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_label_placeholder_field_billing' == $key ) {
							$get_ordered_billing_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_billing' );

							if ( isset( $get_ordered_billing_fields ) && ! empty( $get_ordered_billing_fields ) ) {
								$billing_fields = $get_ordered_billing_fields;
							} else {
								$billing_fields = Cartflows_Helper::get_checkout_fields( 'billing', $post_id );
							}
							foreach ( $post_data as $index => $value ) {
								if ( isset( $billing_fields[ $index ] ) ) {
									$ordered_fields[ $index ]                = $billing_fields[ $index ];
									$ordered_fields[ $index ]['placeholder'] = wc_clean( stripslashes( $value ) );
								}
							}

							$key        = 'wcf_field_order_billing';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_label_placeholder_field_shipping' == $key ) {
							$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_shipping' );

							if ( isset( $get_ordered_shipping_fields ) && ! empty( $get_ordered_shipping_fields ) ) {
								$shipping_fields = $get_ordered_shipping_fields;
							} else {
								$shipping_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								if ( isset( $shipping_fields[ $index ] ) ) {
									$ordered_fields[ $index ]                = $shipping_fields[ $index ];
									$ordered_fields[ $index ]['placeholder'] = wc_clean( stripslashes( $value ) );
								}
							}

							$key        = 'wcf_field_order_shipping';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_label_default_field_billing' == $key ) {
							$get_ordered_billing_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_billing' );

							if ( isset( $get_ordered_billing_fields ) && ! empty( $get_ordered_billing_fields ) ) {
								$billing_fields = $get_ordered_billing_fields;
							} else {
								$billing_fields = Cartflows_Helper::get_checkout_fields( 'billing', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								if ( isset( $billing_fields[ $index ] ) ) {
									$ordered_fields[ $index ]            = $billing_fields[ $index ];
									$ordered_fields[ $index ]['default'] = wp_kses_post( trim( stripslashes( $value ) ) );
								}
							}

							$key        = 'wcf_field_order_billing';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_label_default_field_shipping' == $key ) {
							$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_shipping' );

							if ( isset( $get_ordered_shipping_fields ) && ! empty( $get_ordered_shipping_fields ) ) {
									$shipping_fields = $get_ordered_shipping_fields;
							} else {
								$shipping_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								if ( isset( $shipping_fields[ $index ] ) ) {
									$ordered_fields[ $index ]            = $shipping_fields[ $index ];
									$ordered_fields[ $index ]['default'] = wp_kses_post( trim( stripslashes( $value ) ) );
								}
							}

							$key        = 'wcf_field_order_shipping';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_is_required_field_billing' == $key ) {
							$get_ordered_billing_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_billing' );

							if ( isset( $get_ordered_billing_fields ) && ! empty( $get_ordered_billing_fields ) ) {
								$billing_fields = $get_ordered_billing_fields;
							} else {
								$billing_fields = Cartflows_Helper::get_checkout_fields( 'billing', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								if ( isset( $billing_fields[ $index ] ) ) {
									$ordered_fields[ $index ] = $billing_fields[ $index ];
									if ( 'yes' == $value ) {
										$ordered_fields[ $index ]['required'] = true;
									} else {
										$ordered_fields[ $index ]['required'] = false;
									}
								}
							}

							$key        = 'wcf_field_order_billing';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_is_required_field_shipping' == $key ) {
							$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_shipping' );

							if ( isset( $get_ordered_shipping_fields ) && ! empty( $get_ordered_shipping_fields ) ) {
									$shipping_fields = $get_ordered_shipping_fields;
							} else {
								$shipping_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								if ( isset( $shipping_fields[ $index ] ) ) {
									$ordered_fields[ $index ] = $shipping_fields[ $index ];

									if ( 'yes' == $value ) {
										$ordered_fields[ $index ]['required'] = true;
									} else {
										$ordered_fields[ $index ]['required'] = false;
									}
								}
							}

							$key        = 'wcf_field_order_shipping';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_select_option_field_billing' == $key ) {
							$get_ordered_billing_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_billing' );

							if ( isset( $get_ordered_billing_fields ) && ! empty( $get_ordered_billing_fields ) ) {
								$billing_fields = $get_ordered_billing_fields;
							} else {
								$billing_fields = Cartflows_Helper::get_checkout_fields( 'billing', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								$options = explode( ',', $value );

								if ( isset( $billing_fields[ $index ] ) ) {
									$ordered_fields[ $index ] = $billing_fields[ $index ];

									$ordered_fields[ $index ]['options'] = array();

									foreach ( $options as $key => $option ) {
										$ordered_fields[ $index ]['options'][ $option ] = trim( stripslashes( $option ) );
									}
								}
							}

							$key        = 'wcf_field_order_billing';
							$meta_value = $ordered_fields;
						}

						if ( 'wcf_select_option_field_shipping' == $key ) {
							$get_ordered_shipping_fields = wcf()->options->get_checkout_meta_value( $post_id, 'wcf_field_order_shipping' );

							if ( isset( $get_ordered_shipping_fields ) && ! empty( $get_ordered_shipping_fields ) ) {
									$shipping_fields = $get_ordered_shipping_fields;
							} else {
								$shipping_fields = Cartflows_Helper::get_checkout_fields( 'shipping', $post_id );
							}

							foreach ( $post_data as $index => $value ) {
								$options = explode( ',', $value );

								if ( isset( $shipping_fields[ $index ] ) ) {
									$ordered_fields[ $index ] = $shipping_fields[ $index ];

									$ordered_fields[ $index ]['options'] = array();

									foreach ( $options as $key => $option ) {
										$ordered_fields[ $index ]['options'][ $option ] = trim( stripslashes( $option ) );
									}
								}
							}

							$key        = 'wcf_field_order_shipping';
							$meta_value = $ordered_fields;
						}
					}
					break;

				default:
					if ( 'FILTER_DEFAULT' === $sanitize_filter ) {
						$meta_value = filter_input( INPUT_POST, $key, FILTER_DEFAULT );
					} else {
						$meta_value = apply_filters( 'cartflows_save_meta_field_values', $meta_value, $post_id, $key, $sanitize_filter );
					}

					break;
			}

			if ( false !== $meta_value ) {
				update_post_meta( $post_id, $key, $meta_value );
			} else {
				delete_post_meta( $post_id, $key );
			}
		}
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	function get_flow_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_flow_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	function get_checkout_meta_value( $post_id = 0, $key = '', $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( false !== $default ) {
				$value = $default;
			} else {
				$fields = $this->get_checkout_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Get post meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @return string
	 */
	function get_save_meta( $post_id, $key ) {

		$value = get_post_meta( $post_id, $key, true );

		return $value;
	}

	/**
	 *  Thank You Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	function get_thankyou_fields( $post_id ) {

		if ( null === self::$thankyou_fields ) {
			self::$thankyou_fields = array(
				'wcf-field-google-font-url'     => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-active-tab'                => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-text-color'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-font-family'            => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-heading-color'          => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-heading-font-family'    => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-heading-font-wt'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-container-width'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-section-bg-color'       => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-tq-advance-options-fields' => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-overview-section'     => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-details-section'      => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-billing-section'      => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-show-shipping-section'     => array(
					'default'  => 'yes',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-custom-script'             => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}

		return apply_filters( 'cartflows_thankyou_meta_options', self::$thankyou_fields, $post_id );
	}

	/**
	 *  Get Thank you section meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	function get_thankyou_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );

		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_thankyou_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

		/**
		 *  Get Landing section meta.
		 *
		 * @param int    $post_id post id.
		 * @param string $key options key.
		 * @param mix    $default options default value.
		 * @return string
		 */
	function get_landing_meta_value( $post_id, $key, $default = false ) {

		$value = $this->get_save_meta( $post_id, $key );
		if ( ! $value ) {
			if ( $default ) {
				$value = $default;
			} else {
				$fields = $this->get_landing_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {
					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}

	/**
	 *  Thank You Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	function get_landing_fields( $post_id ) {

		if ( null === self::$landing_fields ) {
			self::$landing_fields = array(
				'wcf-custom-script' => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}
		return apply_filters( 'cartflows_landing_meta_options', self::$landing_fields, $post_id );
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Default_Meta::get_instance();
