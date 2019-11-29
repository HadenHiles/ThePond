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
class Cartflows_Pro_Default_Meta {


	/**
	 * Member Variable
	 *
	 * @var instance
	 */
	private static $instance;

	/**
	 * Member Variable
	 *
	 * @var offer_fields
	 */
	private static $offer_fields = null;


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

		add_filter( 'cartflows_save_meta_field_values', array( $this, 'fetch_meta_value' ), 10, 4 );
	}

	/**
	 * Get checkout editor meta data.
	 *
	 * @param string  $meta_value defaulkt meta value.
	 * @param integer $post_id post id.
	 * @param string  $key meta key.
	 * @param string  $filter_type filter type.
	 * @return array
	 */
	public function fetch_meta_value( $meta_value, $post_id, $key, $filter_type ) {

		switch ( $filter_type ) {

			case 'FILTER_CARTFLOWS_PRO_CHECKOUT_FIELDS':
				$count                   = 10;
				$ordered_fields          = array();
				$billing_shipping_fields = array();

				if ( isset( $_POST[ $key ] ) && is_array( $_POST[ $key ] ) ) {
					$post_data = $_POST[ $key ];

					if ( 'wcf_field_order_billing' == $key || 'wcf_field_order_shipping' == $key ) {

						$type_of_fields          = ltrim( $key, 'wcf_field_order_' );
						$billing_shipping_fields = Cartflows_Helper::get_checkout_fields( $type_of_fields, $post_id );

						foreach ( $post_data as $field_key_name => $value ) {
							if ( isset( $billing_shipping_fields[ $field_key_name ] ) ) {
								$ordered_fields[ $field_key_name ] = $billing_shipping_fields[ $field_key_name ];

								$ordered_fields[ $field_key_name ]['priority'] = $count;
								$count                                        += 10;

								$ordered_fields[ $field_key_name ]['width']       = filter_var( $value['width'], FILTER_SANITIZE_NUMBER_INT );
								$ordered_fields[ $field_key_name ]['label']       = wp_kses_post( trim( stripslashes( $value['label'] ) ) );
								$ordered_fields[ $field_key_name ]['placeholder'] = wc_clean( stripslashes( $value['placeholder'] ) );
								$ordered_fields[ $field_key_name ]['default']     = wp_kses_post( trim( stripslashes( $value['default'] ) ) );
								$ordered_fields[ $field_key_name ]['required']    = 'yes' === $value['required'] ? true : false;
								$ordered_fields[ $field_key_name ]['optimized']   = 'yes' === $value['optimized'] ? true : false;
								$ordered_fields[ $field_key_name ]['enabled']     = 'yes' === $value['enabled'] ? true : false;
								$ordered_fields[ $field_key_name ]['options']     = '';
								if ( $value['options'] ) {

									$options                                      = explode( ',', $value['options'] );
									$ordered_fields[ $field_key_name ]['options'] = array_combine( $options, $options );

								}
							}
						}

						$meta_value = $ordered_fields;
					}
				}
				break;
		}

		return $meta_value;

	}

	/**
	 *  Offer Default fields.
	 *
	 * @param int $post_id post id.
	 * @return array
	 */
	function get_offer_fields( $post_id ) {

		if ( null === self::$offer_fields ) {

			self::$offer_fields = array(
				'wcf-offer-product'        => array(
					'default'  => array(),
					'sanitize' => 'FILTER_CARTFLOWS_ARRAY',
				),
				'wcf-offer-quantity'       => array(
					'default'  => 1,
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-offer-discount'       => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-offer-discount-value' => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-offer-flat-shipping'  => array(
					'default'  => '',
					'sanitize' => 'FILTER_SANITIZE_NUMBER_INT',
				),
				'wcf-custom-script'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-no-next-step'         => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
				'wcf-yes-next-step'        => array(
					'default'  => '',
					'sanitize' => 'FILTER_DEFAULT',
				),
			);
		}

		return apply_filters( 'cartflows_offer_meta_options', self::$offer_fields );
	}

	/**
	 *  Save Offer Meta fields.
	 *
	 * @param int $post_id post id.
	 * @return void
	 */
	function save_offer_fields( $post_id ) {

		$post_meta = $this->get_offer_fields( $post_id );

		wcf()->options->save_meta_fields( $post_id, $post_meta );
	}

	/**
	 *  Get checkout meta.
	 *
	 * @param int    $post_id post id.
	 * @param string $key options key.
	 * @param mix    $default options default value.
	 * @return string
	 */
	function get_offers_meta_value( $post_id, $key, $default = false ) {

		$value = wcf()->options->get_save_meta( $post_id, $key );

		if ( ! $value ) {

			if ( $default ) {

				$value = $default;
			} else {

				$fields = $this->get_offer_fields( $post_id );

				if ( isset( $fields[ $key ]['default'] ) ) {

					$value = $fields[ $key ]['default'];
				}
			}
		}

		return $value;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Pro_Default_Meta::get_instance();
