<?php
/**
 * Session
 *
 * @package CartFlows
 */

/**
 * Initialization
 *
 * @since 1.0.0
 */
class Cartflows_Session {


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

	}

	/**
	 *  Set session
	 *
	 * @param int   $flow_id flow ID.
	 * @param array $data trasient data.
	 */
	function set_session( $flow_id, $data = array() ) {

		if ( isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {
			$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];
		} else {
			$key = $flow_id . '_' . md5( time() . rand() );
		}

		// Set the browser cookie to expire in 30 minutes.
		setcookie( 'cartflows_session_' . $flow_id, $key, time() + 30 * MINUTE_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );

		// Try to grab the transient from the database, if it exists.
		$transient = $data;

		// Store the transient, but expire in 30 minutes.
		set_transient( 'cartflows_data_' . $key, $transient, 30 * MINUTE_IN_SECONDS );

		wp_cache_set( 'cartflows_data_' . $key, $transient );

		wcf()->logger->log( 'Flow-' . $flow_id . ' Session Set : ' . $key . ' ' . json_encode( $transient ) );
	}

	/**
	 *  Update session
	 *
	 * @param int   $flow_id flow ID.
	 * @param array $data trasient data.
	 */
	function update_session( $flow_id, $data = array() ) {

		if ( ! isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {

			$this->set_session( $flow_id, $data );
		}

		$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];

		// Try to grab the transient from the database, if it exists.
		$transient = get_transient( 'cartflows_data_' . $key );

		// Set the browser cookie to expire in 30 minutes.
		setcookie( 'cartflows_session_' . $flow_id, $key, time() + 30 * MINUTE_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN );

		// Store the transient, but expire in 30 minutes.
		set_transient( 'cartflows_data_' . $key, $transient, 30 * MINUTE_IN_SECONDS );

		wp_cache_set( 'cartflows_data_' . $key, $transient );
	}

	/**
	 *  Destroy session
	 *
	 * @param int $flow_id flow ID.
	 */
	function destroy_session( $flow_id ) {

		if ( isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {

			$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];

			// Delete Transient.
			delete_transient( 'cartflows_data_' . $key );

			wp_cache_delete( 'cartflows_data_' . $key );

			unset( $_COOKIE[ 'cartflows_session_' . $flow_id ] );

			// empty value and expiration one hour before.
			setcookie( 'cartflows_session_' . $flow_id, $key, time() - 3600, COOKIEPATH, COOKIE_DOMAIN );

			wcf()->logger->log( 'Flow-' . $flow_id . ' Session Destroyed : ' . $key );
		}
	}

	/**
	 *  Get session
	 */
	function get_session() {

		if ( isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {

			$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];

			$data = get_transient( 'cartflows_data_' . $key );
		}
	}

	/**
	 *  Update transient data for cart flows.
	 *
	 * @param int   $flow_id flow ID.
	 * @param array $data data.
	 */
	function update_data( $flow_id, $data = array() ) {

		if ( isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {

			$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];

			// Try to grab the transient from the database, if it exists.
			$transient = get_transient( 'cartflows_data_' . $key );

			if ( ! is_array( $transient ) ) {
				$transient = array();
			}

			$transient = array_merge( $transient, $data );

			// Store the transient, but expire in 30 minutes.
			set_transient( 'cartflows_data_' . $key, $transient, 30 * MINUTE_IN_SECONDS );

			wp_cache_set( 'cartflows_data_' . $key, $transient );
		}
	}

	/**
	 *  Update transient data for cart flows.
	 *
	 * @param int $flow_id flow ID.
	 * @return bool
	 */
	function get_data( $flow_id ) {

		if ( isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {

			$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];

			// Try to grab the transient from the database, if it exists.
			$transient = get_transient( 'cartflows_data_' . $key );

			if ( is_array( $transient ) ) {
				return $transient;
			}
		}

		return false;
	}


	/**
	 *  Check if session is active.
	 *
	 * @param int $flow_id flow ID.
	 * @return bool
	 */
	function is_active_session( $flow_id ) {

		$is_active = false;

		if ( isset( $_GET['wcf-sk'] ) && isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {

			$sk  = sanitize_text_field( $_GET['wcf-sk'] );
			$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];

			if ( $sk === $key ) {

				if ( isset( $_GET['wcf-order'] ) && isset( $_GET['wcf-key'] ) ) {

					// Get the order.
					$order_id  = empty( $_GET['wcf-order'] ) ? 0 : intval( $_GET['wcf-order'] );
					$order_key = empty( $_GET['wcf-key'] ) ? '' : wc_clean( wp_unslash( $_GET['wcf-key'] ) );

					if ( $order_id > 0 ) {

						$order = wc_get_order( $order_id );

						if ( $order && $order->get_order_key() === $order_key ) {
							$is_active = true;
						}
					}
				}
			}
		}

		return $is_active;
	}

	/**
	 * Get session key for flow
	 *
	 * @param int $flow_id flow ID.
	 * @return bool
	 */
	function get_session_key( $flow_id ) {

		if ( isset( $_COOKIE[ 'cartflows_session_' . $flow_id ] ) ) {

			$key = (string) $_COOKIE[ 'cartflows_session_' . $flow_id ];

			return $key;
		}

		return false;
	}
}

/**
 *  Kicking this off by calling 'get_instance()' method
 */
Cartflows_Session::get_instance();
