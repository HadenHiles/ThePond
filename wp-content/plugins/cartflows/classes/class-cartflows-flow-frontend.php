<?php
/**
 * Frontend & Markup
 *
 * @package CartFlows
 */

/**
 * Flow Markup
 *
 * @since 1.0.0
 */
class Cartflows_Flow_Frontend {


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

		/* Analytics */
		add_action( 'cartflows_wp_footer', array( $this, 'footer_markup' ) );
	}

	/**
	 *  Footer markup
	 */
	function footer_markup() {

		if ( wcf()->utils->is_step_post_type() ) {
			// @codingStandardsIgnoreStart
			$flow_id = wcf()->utils->get_flow_id();
			?>
			<?php if( $this->is_flow_testmode( $flow_id ) ) { ?>
			<div class="wcf-preview-mode">
				<span><?php _e( 'Test mode is active — which displays random products for previewing. It can be deactivated from the flow settings in the admin dashboard.', 'cartflows' ); ?></span>
				<?php if ( current_user_can( 'manage_options' ) ) { ?>
					<?php
						$flow_edit_link = add_query_arg( 'edit_test_mode', 'yes', get_edit_post_link( $flow_id ) );
					?>
					<a href="<?php echo $flow_edit_link; ?>"><?php _e( 'Click here to disable it', 'cartflows'); ?></a>
				<?php } ?>
			</div>
			<?php } ?>
			<?php
			// @codingStandardsIgnoreEnd
		}
	}

	/**
	 * Check if flow test mode is enable.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 *
	 * @return boolean
	 */
	function is_flow_testmode( $flow_id = '' ) {

		if ( ! $flow_id ) {
			$flow_id = wcf()->utils->get_flow_id();
		}

		$test_mode = wcf()->options->get_flow_meta_value( $flow_id, 'wcf-testing' );

		if ( 'no' === $test_mode ) {
			return false;
		}

		return true;
	}

	/**
	 * Get steps data.
	 *
	 * @since 1.0.0
	 * @param int $flow_id flow ID.
	 *
	 * @return array
	 */
	function get_steps( $flow_id ) {

		$steps = get_post_meta( $flow_id, 'wcf-steps', true );

		if ( ! is_array( $steps ) ) {

			$steps = array();
		}

		return $steps;
	}

	/**
	 * Check thank you page exists.
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 *
	 * @return bool
	 */
	function is_thankyou_page_exists( $order ) {

		$thankyou_step_exist = false;

		$flow_id = wcf()->utils->get_flow_id_from_order( $order->get_id() );

		if ( $flow_id ) {

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );
			$step_id    = wcf()->utils->get_checkout_id_from_order( $order->get_id() );

			if ( is_array( $flow_steps ) ) {

				$current_step_found = false;

				foreach ( $flow_steps as $index => $data ) {

					if ( $current_step_found ) {

						if ( 'thankyou' === $data['type'] ) {

							$thankyou_step_exist = true;
							break;
						}
					} else {

						if ( intval( $data['id'] ) === $step_id ) {

							$current_step_found = true;
						}
					}
				}
			}
		}

		return $thankyou_step_exist;
	}

	/**
	 * Check thank you page exists.
	 *
	 * @since 1.0.0
	 * @param array $order order data.
	 *
	 * @return bool
	 */
	function get_thankyou_page_id( $order ) {

		$thankyou_step_id = false;

		$flow_id = wcf()->utils->get_flow_id_from_order( $order->get_id() );

		if ( $flow_id ) {

			$flow_steps = get_post_meta( $flow_id, 'wcf-steps', true );
			$step_id    = wcf()->utils->get_checkout_id_from_order( $order->get_id() );

			if ( is_array( $flow_steps ) ) {

				$current_step_found = false;

				foreach ( $flow_steps as $index => $data ) {

					if ( $current_step_found ) {

						if ( 'thankyou' === $data['type'] ) {

							$thankyou_step_id = intval( $data['id'] );
							break;
						}
					} else {

						if ( intval( $data['id'] ) === $step_id ) {

							$current_step_found = true;
						}
					}
				}
			}
		}

		return $thankyou_step_id;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Flow_Frontend::get_instance();
