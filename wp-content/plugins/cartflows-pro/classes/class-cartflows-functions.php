<?php
/**
 * Cartflows Functions.
 *
 * @package CARTFLOWS
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Check if it is a landing page?
 *
 * @since 1.0.0
 */
function _is_wcf_landing_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'landing' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Returns landing id.
 *
 * @since 1.0.0
 */
function _get_wcf_landing_id() {

	if ( _is_wcf_landing_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Is custom checkout?
 *
 * @param int $checkout_id checkout ID.
 * @since 1.0.0
 */
function _is_wcf_meta_custom_checkout( $checkout_id ) {

	$is_custom = wcf()->options->get_checkout_meta_value( $checkout_id, 'wcf-custom-checkout-fields' );

	if ( 'yes' === $is_custom ) {

		return true;
	}

	return false;
}

/**
 * Check if page is cartflow checkout.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_checkout_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'checkout' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Check if AJAX call is in progress.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_doing_checkout_ajax() {

	if ( wp_doing_ajax() ) {

		if ( isset( $_GET['wc-ajax'] ) &&
			'checkout' === $_GET['wc-ajax'] &&
			isset( $_POST['_wcf_checkout_id'] )
		) {
			return true;
		}
	}

	return false;
}


/**
 * Returns checkout ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_checkout_id() {

	if ( _is_wcf_checkout_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if it is checkout shortcode.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_checkout_shortcode() {

	global $post;

	if ( ! empty( $post ) && has_shortcode( $post->post_content, 'cartflows_checkout' ) ) {

		return true;
	}

	return false;
}

/**
 * Check if it is checkout shortcode.
 *
 * @since 1.0.0
 * @param string $content shortcode content.
 * @return bool
 */
function _get_wcf_checkout_id_from_shortcode( $content = '' ) {

	$checkout_id = 0;

	if ( ! empty( $content ) ) {

		$regex_pattern = get_shortcode_regex( array( 'cartflows_checkout' ) );

		preg_match( '/' . $regex_pattern . '/s', $content, $regex_matches );

		if ( ! empty( $regex_matches ) ) {

			if ( 'cartflows_checkout' == $regex_matches[2] ) {

				$attribure_str = str_replace( ' ', '&', trim( $regex_matches[3] ) );
				$attribure_str = str_replace( '"', '', $attribure_str );

				$attributes = wp_parse_args( $attribure_str );

				if ( isset( $attributes['id'] ) ) {
					$checkout_id = $attributes['id'];
				}
			}
		}
	}

	return $checkout_id;
}

/**
 * Check if post type is upsell.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_upsell_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'upsell' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Returns upsell ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_upsell_id() {

	if ( _is_wcf_upsell_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if post is of type downsell.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _is_wcf_downsell_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'downsell' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Get downsell page ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_downsell_id() {

	if ( _is_wcf_downsell_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}

/**
 * Check if page is of thank you type.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _is_wcf_thankyou_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		if ( 'thankyou' === get_post_meta( $post->ID, 'wcf-step-type', true ) ) {

			return true;
		}
	}

	return false;
}

/**
 * Get thank you page ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_thankyou_id() {

	if ( _is_wcf_thankyou_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}


/**
 * Check if post type is upsell.
 *
 * @since 1.0.0
 * @return bool
 */
function _is_wcf_base_offer_type() {

	if ( wcf()->utils->is_step_post_type() ) {

		global $post;

		$template_type = get_post_meta( $post->ID, 'wcf-step-type', true );

		if ( 'upsell' === $template_type || 'downsell' === $template_type ) {

			return true;
		}
	}

	return false;
}

/**
 * Returns upsell ID.
 *
 * @since 1.0.0
 * @return int/bool
 */
function _get_wcf_base_offer_id() {

	if ( _is_wcf_base_offer_type() ) {

		global $post;

		return $post->ID;
	}

	return false;
}
