<?php
/**
 * CartFlows License.
 *
 * @package CartFlows
 * @since 1.0.0
 */

if ( ! class_exists( 'CartFlows_Pro_Licence' ) ) :

	/**
	 * CartFlows License
	 *
	 * @since 1.0.0
	 */
	class CartFlows_Pro_Licence {

		/**
		 * Instance
		 *
		 * @since 1.0.0
		 * @access private
		 * @var object Class object.
		 */
		private static $instance;

		/**
		 * Wc_am_instance_id
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string wc_am_instance_id.
		 */
		public $wc_am_instance_id;

		/**
		 * Wc_am_domain
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string wc_am_domain.
		 */
		public $wc_am_domain;

		/**
		 * Wc_am_software_version
		 *
		 * @since 1.0.0
		 * @access public
		 * @var string wc_am_software_version.
		 */
		public $wc_am_software_version;

		/**
		 * Initiator
		 *
		 * @since 1.0.0
		 * @return object initialized object of class.
		 */
		public static function get_instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 */
		public function __construct() {
			global $wcam_lib;

			$this->product_id             = CARTFLOWS_PRO_PRODUCT_TITLE; // Product id.
			$this->wc_am_domain           = str_ireplace( array( 'http://', 'https://' ), '', home_url() ); // blog domain name.
			$this->wc_am_software_version = CARTFLOWS_PRO_VER;
			$this->wc_am_instance_id      = get_option( 'wc_am_client_cartflows_instance' ); // A unique password generated for each installation.
			$this->activate_status        = get_option( 'wc_am_client_cartflows_activated', 'Deactivated' );

			add_action( 'plugin_action_links_' . CARTFLOWS_PRO_BASE, array( $this, 'license_popup_link' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
			add_action( 'wp_ajax_cartflows_activate_license', array( $this, 'activate_license' ) );
			add_action( 'wp_ajax_cartflows_deactivate_license', array( $this, 'deactivate_license' ) );
			add_action( 'admin_footer', array( $this, 'export_popup' ) );
			add_action( 'admin_head', array( $this, 'reset_license_info' ) );

			add_filter( 'wc_am_inactive_notice_override', '__return_true' );
			add_filter( 'admin_notices', array( $this, 'inactive_notice' ) );
			add_action( 'wp_ajax_cartflows_disable_activate_license_notice', array( $this, 'disable_activate_licence_notice' ) );

			// Disabled un-install hook.
			add_filter( 'wc_am_uninstall_disable', '__return_true' );

			// Disabled un-install hook.
			remove_action( 'admin_menu', array( $wcam_lib, 'register_menu' ) );
			remove_action( 'admin_init', array( $wcam_lib, 'load_settings' ) );

		}

		/**
		 * Disable the Activate License Notice
		 *
		 * @return void
		 */
		function disable_activate_licence_notice() {

			check_ajax_referer( 'cartflows-admin-notice-nonce', 'security' );

			// Saving the `true` for 10 minutes into transient `my_transient`.
			set_transient( 'cartflows-activate-licence', true, 10 * MINUTE_IN_SECONDS );

			wp_send_json_success();
		}


		/**
		 * License Inactive Notice
		 *
		 * @param string $default Default notice.
		 * @since 1.0.0
		 */
		function inactive_notice( $default ) {

			if ( 'Activated' === $this->activate_status ) {
				return;
			}

			$expired = get_transient( 'cartflows-activate-licence' );

			// Is transient expired?
			if ( false != $expired && ! empty( $expired ) ) {
				return;
			}

			wp_enqueue_script( 'cartflows-admin-notice' );

			/* translators: %1$s Software Title, %2$s Plugin, %3$s Anchor opening tag, %4$s Anchor closing tag, %5$s Software Title. */
			$message = sprintf( __( 'The <strong>%1$s</strong> License Key has not been activated, so the %2$s is inactive! %3$sClick here%4$s to activate <strong>%5$s</strong>.', 'cartflows-pro' ), esc_attr( CARTFLOWS_PRO_DISPLAY_TITLE ), 'plugin', '<a class="cartflows-license-popup-open-button" href="' . esc_url( admin_url( 'plugins.php?cartflows-license-popup' ) ) . '">', '</a>', esc_attr( CARTFLOWS_PRO_DISPLAY_TITLE ) );

			$output  = '<div class="cartflows-dismissible-notice notice notice-error is-dismissible">';
			$output .= '<p>' . wp_kses_post( $message ) . '</p>';
			$output .= '</div>';

			echo $output;

			return true;
		}

		/**
		 * Reset License Info
		 *
		 * @since 1.0.0
		 */
		function reset_license_info() {
			if ( ! isset( $_GET['cartflows-license-reset'] ) ) {
				return;
			}

			delete_option( 'wc_am_client_cartflows_activated' );
			delete_option( 'wc_am_client_cartflows_api_key' );
		}

		/**
		 * Export popup.
		 *
		 * @since 1.0.0
		 *
		 * @return void
		 */
		function export_popup() {

			if ( 'plugins' !== get_current_screen()->base ) {
				return;
			}

			$data        = get_option( 'wc_am_client_cartflows_api_key', array() );
			$license_key = isset( $data['api_key'] ) ? $data['api_key'] : '';

			?>
			<div id="cartflows-license-popup-overlay" style="display:none;"></div>
			<div id="cartflows-license-popup" style="display:none;" data-license-key="<?php echo esc_attr( $this->activate_status ); ?>">
				<div class="inner">
					<div class="heading">
						<span><?php _e( 'Activate License', 'cartflows-pro' ); ?></span>
						<span class="cartflows-close-popup-button tb-close-icon"></span>
					</div>
					<div class="contents">
					</div>
				</div>
			</div>
			<script type="text/template" id="tmpl-cartflows-activate-license">
				<table class="widefat">
					<tr class="cartflows-row">
						<td class="cartflows-heading"><?php _e( 'License Key', 'cartflows-pro' ); ?></td>
						<td class="cartflows-content">
							<input type="text" placeholder="<?php _e( 'Enter your License Key', 'cartflows-pro' ); ?>" class="regular-text license_key" name="license_key" value="<?php echo esc_attr( $license_key ); ?>" autocomplete="off">
							<?php /* translators: Cartflows site URL. */ ?>
							<p class="description"><?php printf( __( 'If you don\'t have License key, you can get it from <a target="_blank" href="%1$s">here</a>.', 'cartflows-pro' ), esc_url( CARTFLOWS_SERVER_URL . 'api-keys/' ) ); ?> </p>
						</td>
					</tr>
					<tr class="cartflows-row">
						<td colspan="2" class="submit-button-td">
							<span class="button button-primary cartflows-activate-license"><i class="cartflows-processing dashicons dashicons-update"></i><span class="text"><?php _e( 'Activate', 'cartflows-pro' ); ?></span></span>
						</td>
					</tr>
				</table>
			</script>
			<script type="text/template" id="tmpl-cartflows-deactivate-license">
				<table class="widefat">
					<tr class="cartflows-row">
						<td class="cartflows-heading"><?php _e( 'License Key', 'cartflows-pro' ); ?></td>
						<td class="cartflows-content">
							<input type="password" placeholder="<?php _e( '******************', 'cartflows-pro' ); ?>" class="regular-text license_key" value="" autocomplete="off" text="" readonly="readonly">
						</td>
					</tr>
					<tr class="cartflows-row">
						<td colspan="2" class="submit-button-td">
							<span class="button button-primary cartflows-deactivate-license"><i class="cartflows-processing dashicons dashicons-update"></i><span class="text"><?php _e( 'Deactivate', 'cartflows-pro' ); ?></span></span>
						</td>
					</tr>
				</table>
			</script>
			<?php
		}

		/**
		 * Show action links on the plugin screen.
		 *
		 * @param   mixed $links Plugin Action links.
		 * @return  array
		 */
		function license_popup_link( $links ) {

			if ( 'Deactivated' == $this->activate_status ) {
				$links['license_key'] = '<a href="#"" class="cartflows-license-popup-open-button active" aria-label="' . esc_attr__( 'Settings', 'cartflows-pro' ) . '">' . esc_html__( 'Activate License', 'cartflows-pro' ) . '</a>';
			} else {
				$links['license_key'] = '<a href="#"" class="cartflows-license-popup-open-button inactive" aria-label="' . esc_attr__( 'Settings', 'cartflows-pro' ) . '">' . esc_html__( 'Deactivate License', 'cartflows-pro' ) . '</a>';
			}

			return $links;
		}

		/**
		 * Enqueues the needed CSS/JS for Backend.
		 *
		 * @param  string $hook Current hook.
		 *
		 * @since 1.0.0
		 */
		function admin_scripts( $hook = '' ) {

			wp_register_script( 'cartflows-admin-notice', CARTFLOWS_PRO_URL . 'assets/js/admin-notice.js', array( 'jquery' ), CARTFLOWS_PRO_VER, true );

			$vars = array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'_nonce'  => wp_create_nonce( 'cartflows-admin-notice-nonce' ),
			);

			wp_localize_script( 'cartflows-admin-notice', 'CartFlowsProAdminNoticeVars', $vars );

			if ( 'plugins.php' == $hook ) {
				wp_enqueue_style( 'cartflows-license', CARTFLOWS_PRO_URL . 'assets/css/license-popup.css', null, CARTFLOWS_PRO_VER, 'all' );
				wp_enqueue_script( 'cartflows-license', CARTFLOWS_PRO_URL . 'assets/js/license-popup.js', array( 'wp-util', 'jquery' ), CARTFLOWS_PRO_VER, true );

				$defaults = array(
					'activation_status' => get_option( 'wc_am_client_cartflows_activated', 'Deactivated' ),
				);

				$args = get_option( 'wc_am_client_cartflows_api_key', array() );

				$localize_vars = wp_parse_args( $args, $defaults );

				wp_localize_script( 'cartflows-license', 'CartFlowsProLicenseVars', $localize_vars );

			}
		}

		/**
		 * Deactivate license.
		 *
		 * @since 1.0.0
		 *
		 * @return void.
		 */
		function deactivate_license() {

			$default_args = array(
				'api_key' => '',
			);

			$args = get_option( 'wc_am_client_cartflows_api_key', $default_args );

			$response = $this->deactivate_request( $args );

			update_option( 'wc_am_client_cartflows_activated', 'Deactivated' );

			update_option( 'wc_am_client_cartflows_api_key', $default_args );

			// Store the API key which used for plugin update.
			$new_data = array(
				'wc_am_client_cartflows_api_key' => $default_args['api_key'],
			);
			update_option( 'wc_am_client_cartflows', $new_data );

			wp_send_json_success( $response );
		}

		/**
		 * Sends the request to deactivate to the API Manager.
		 *
		 * @param array $args args.
		 *
		 * @return bool|string
		 */
		public function deactivate_request( $args ) {
			$defaults = array(
				'request'    => 'deactivate',
				'product_id' => $this->product_id,
				'instance'   => $this->wc_am_instance_id,
				'object'     => $this->wc_am_domain,
			);

			$args       = wp_parse_args( $defaults, $args );
			$target_url = add_query_arg( 'wc-api', 'am-software-api', CARTFLOWS_SERVER_URL ) . '&' . http_build_query( $args );
			// $target_url = esc_url_raw( $this->create_software_api_url( $args ) );
			$request = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

			if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
				// Request failed.
				return false;
			}

			$response = wp_remote_retrieve_body( $request );

			return $response;
		}

		/**
		 * Save All admin settings here
		 *
		 * @hook cartflows_activate_license
		 */
		function activate_license() {

			$license_key = isset( $_REQUEST['license_key'] ) ? sanitize_text_field( $_REQUEST['license_key'] ) : '';

			$args = array(
				'api_key' => $license_key,
			);

			$response = json_decode( $this->activation_request( $args ), true );
			// $response = $this->request( 'activation', $license_key, $license_email );
			if ( true === $response['success'] && true === $response['activated'] ) {

				$data = array(
					'api_key' => $license_key,
				);

				// Store the API key.
				update_option( 'wc_am_client_cartflows_api_key', $data );

				// Activate.
				update_option( 'wc_am_client_cartflows_activated', 'Activated' );

				// Store the API key which used for plugin update.
				$new_data = array(
					'wc_am_client_cartflows_api_key' => $data['api_key'],
				);
				update_option( 'wc_am_client_cartflows', $new_data );

				wp_send_json_success( $response );
			}

			wp_send_json_error( $response );
		}

		/**
		 * Sends the request to activate to the API Manager.
		 *
		 * @param array $args args.
		 *
		 * @return bool|string
		 */
		function activation_request( $args ) {
			$defaults = array(
				'request'          => 'activate',
				'product_id'       => $this->product_id,
				'instance'         => $this->wc_am_instance_id,
				'object'           => $this->wc_am_domain,
				'software_version' => $this->wc_am_software_version,
			);

			$args = wp_parse_args( $defaults, $args );

			$target_url = add_query_arg( 'wc-api', 'am-software-api', CARTFLOWS_SERVER_URL ) . '&' . http_build_query( $args );
			$request    = wp_safe_remote_post( $target_url, array( 'timeout' => 15 ) );

			if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) != 200 ) {
				// Request failed.
				return false;
			}

			$response = wp_remote_retrieve_body( $request );

			return $response;
		}
	}


	/**
	 * Initialize class object with 'get_instance()' method
	 */
	CartFlows_Pro_Licence::get_instance();

endif;


if ( ! function_exists( 'cartflows_pro_license_request' ) ) :

	/**
	 * License Request for Site
	 *
	 * @since x.x.x
	 *
	 * @param  string  $license_key  License key.
	 * @param  integer $site_id      Site ID.
	 * @param  string  $request_type Request type (activate or deactivate).
	 * @return mixed
	 */
	function cartflows_pro_license_request( $license_key = '', $site_id = 1, $request_type = '' ) {

		if ( empty( $license_key ) ) {
			return new WP_Error( 'empty_license_key', __( 'Provide a license key to process license request!', 'cartflows-pro' ) );
		}

		if ( empty( $request_type ) ) {
			return new WP_Error( 'empty_license_request', __( 'Provide a license request!', 'cartflows-pro' ) );
		}

		$requests = array( 'activate', 'deactivate' );

		if ( ! in_array( $request_type, $requests ) ) {
			return new WP_Error( 'invalid_license_request', __( 'Invalid license request!', 'cartflows-pro' ) );
		}

		$wc_am_domain = str_ireplace( array( 'http://', 'https://' ), '', get_site_url( $site_id ) );

		$args = array(
			'api_key' => $license_key,
			'object'  => $wc_am_domain,
		);

		if ( 'activate' === $request_type ) {

			$request = CartFlows_Pro_Licence::get_instance()->activation_request( $args );

			$response = json_decode( $request, true );

			if ( true === $response['success'] && true === $response['activated'] ) {

				$data = array(
					'api_key' => $license_key,
				);

				// Store the API key.
				update_option( 'wc_am_client_cartflows_api_key', $data );

				// Activate.
				update_option( 'wc_am_client_cartflows_activated', 'Activated' );

				// Store the API key which used for plugin update.
				$new_data = array(
					'wc_am_client_cartflows_api_key' => $data['api_key'],
				);
				update_option( 'wc_am_client_cartflows', $new_data );
			}
		} elseif ( 'deactivate' === $request_type ) {

			$request = CartFlows_Pro_Licence::get_instance()->deactivate_request( $args );

			$default_args = array(
				'api_key' => '',
			);

			update_option( 'wc_am_client_cartflows_activated', 'Deactivated' );

			update_option( 'wc_am_client_cartflows_api_key', $default_args );

			// Store the API key which used for plugin update.
			$new_data = array(
				'wc_am_client_cartflows_api_key' => $default_args['api_key'],
			);
			update_option( 'wc_am_client_cartflows', $new_data );
		}

		return json_decode( $request, true );
	}
endif;

if ( ! function_exists( 'cartflows_pro_activate' ) ) :

	/**
	 * Activate License Request for Site
	 *
	 * @since x.x.x
	 *
	 * @param  string  $license_key  License key.
	 * @param  integer $site_id      Site ID.
	 * @return mixed
	 */
	function cartflows_pro_activate( $license_key = '', $site_id = 1 ) {
		return cartflows_pro_license_request( $license_key, $site_id, 'activate' );
	}
endif;

if ( ! function_exists( 'cartflows_pro_deactivate' ) ) :

	/**
	 * Deactivate License Request for Site
	 *
	 * @since x.x.x
	 *
	 * @param  string  $license_key  License key.
	 * @param  integer $site_id      Site ID.
	 * @return mixed
	 */
	function cartflows_pro_deactivate( $license_key = '', $site_id = 1 ) {
		return cartflows_pro_license_request( $license_key, $site_id, 'deactivate' );
	}
endif;
