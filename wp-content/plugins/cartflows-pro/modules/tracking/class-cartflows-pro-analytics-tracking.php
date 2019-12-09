<?php
/**
 * Flow
 *
 * @package cartflows
 */

define( 'CARTFLOWS_TRACKING_DIR', CARTFLOWS_PRO_DIR . 'modules/tracking/' );
define( 'CARTFLOWS_TRACKING_URL', CARTFLOWS_PRO_URL . 'modules/tracking/' );

/**
 * Class for analytics tracking.
 */
class Cartflows_Pro_Analytics_Tracking {

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
	 * Constructor function that initializes required actions and hooks
	 */
	public function __construct() {

		if ( ! is_admin() ) {
			add_filter( 'global_cartflows_js_localize', array( $this, 'add_localize_vars' ), 10, 1 );
		}

		add_action( 'template_redirect', array( $this, 'save_analytics_data' ) );

	}

	/**
	 *  Save analytics data.
	 */
	function save_analytics_data() {

		if ( wcf()->utils->is_step_post_type() ) {
			global $post;
			$current_flow = get_post_meta( $post->ID, 'wcf-flow-id', true );
			if ( ! $current_flow ) {
				return;
			}
			$current_step = $post->ID;
			$cookie_name  = 'wcf-visited-flow-' . $current_flow;
			$cookie       = isset( $_COOKIE[ $cookie_name ] ) ? ( (array) json_decode( sanitize_text_field( $_COOKIE[ $cookie_name ] ), true ) ) : array();
			$is_returning = in_array( $current_step, $cookie );
			if ( ! $is_returning ) {
				array_push( $cookie, $current_step );
			}
			setcookie( $cookie_name, wp_json_encode( $cookie ), strtotime( '+1 year' ), '/' );
			$this->save_visit( $current_step, $is_returning );
		}
	}

	/**
	 * Load tracking scripts
	 *
	 * @since 1.0.0
	 */
	public function load_tracking_scripts() {

		if ( $this->maybe_load_scripts() && ! is_admin() ) {
			wp_enqueue_script(
				'wcf-tracking',
				CARTFLOWS_TRACKING_URL . 'assets/js/wcf_tracking.js',
				array( 'jquery' ),
				CARTFLOWS_VER,
				true
			);
		}
	}

	/**
	 * Check if scripts needs to be loaded.
	 *
	 * @since 1.0.0
	 */
	public function maybe_load_scripts() {

		$cartflow_compatibility = Cartflows_Compatibility::get_instance();
		$load_ga_scripts        = false;

		if ( wcf()->utils->is_step_post_type() && ! $cartflow_compatibility->is_page_builder_preview() ) {
			$load_ga_scripts = true;
		}

		$load_ga_scripts = apply_filters( 'wcf_load_ga_scripts', $load_ga_scripts );

		return $load_ga_scripts;
	}


	/**
	 * Save visits and visit meta in database.
	 *
	 * @param int  $step_id step ID.
	 * @param bool $is_returning is returning visitor.
	 *
	 * @since 1.0.0
	 */
	public function save_visit( $step_id, $is_returning ) {

		global $wpdb;
		$visit_db      = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;
		$visit_meta_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_META_TABLE;
		$visit_type    = 'new';
		$http_referer  = isset( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '';

		if ( $is_returning ) {
			$visit_type = 'return';
		}

		// insert visit entry.
		$wpdb->insert(
			$visit_db,
			array(
				'step_id'      => $step_id,
				'date_visited' => current_time( 'mysql', true ),
				'visit_type'   => $visit_type,
			)
		);

		$visit_id = $wpdb->insert_id;

		$meta_data = array(
			'user_ip_address' => $this->get_user_ip_address(),
			'http_referer'    => $http_referer,
		);

		foreach ( $meta_data as $key => $value ) {

			// make sure there is a key and a value before saving.
			if ( ! $key || ! $value ) {
				continue;
			}

			$wpdb->insert(
				$visit_meta_db,
				array(
					'visit_id'   => $visit_id,
					'meta_key'   => $key,
					'meta_value' => $value,
				)
			);
		}
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
		$step_id               = $post->ID;
		$analytics_track_nonce = wp_create_nonce( 'wcf-analytics-nonce-' . $step_id );

		$localize['analytics_nonce'] = $analytics_track_nonce;

		return $localize;
	}

	/**
	 * Get user IP address.
	 *
	 * @since 1.0.0 Added condition to disable IP address collection (for GDRP compliance).
	 * @access public
	 *
	 * @return string User's IP address.
	 */
	public function get_user_ip_address() {

		if ( get_option( 'wcf_disable_ip_address_collection' ) === 'yes' ) {
			return;
		}

		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		return apply_filters( 'wcf_get_user_ip_address', $ip );
	}

}

Cartflows_Pro_Analytics_Tracking::get_instance();
