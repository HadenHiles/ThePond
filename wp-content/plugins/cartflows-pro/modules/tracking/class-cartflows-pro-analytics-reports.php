<?php
/**
 * Flow
 *
 * @package cartflows
 */

/**
 * Analytics reports class.
 */
class Cartflows_Pro_Analytics_Reports {

	/**
	 * Member Variable
	 *
	 * @var object instance
	 */
	private static $instance;

	/**
	 * Flow orders
	 *
	 * @var array flow_orders
	 */
	private static $flow_orders = array();

	/**
	 * Flow gross sell
	 *
	 * @var int flow_gross
	 */
	private static $flow_gross = 0;

	/**
	 * Flow visits
	 *
	 * @var array flow_visits
	 */
	private static $flow_visits = array();

	/**
	 * Steps data
	 *
	 * @var array step_data
	 */
	private static $step_data = array();

	/**
	 * Earnings for flow
	 *
	 * @var array flow_earnings
	 */
	private static $flow_earnings = array();

	/**
	 * Report interval
	 *
	 * @var int report_interval
	 */
	private static $report_interval = 30;

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

		add_action( 'admin_enqueue_scripts', array( $this, 'load_analytics_scripts' ), 20 );
		add_action( 'wp_ajax_cartflows_set_visit_data', array( $this, 'set_visits_data' ) );

		/*
		* add_action( 'edit_form_top', array( $this, 'setup_analytics_button' ), 99 );
		*/
		add_action( 'admin_footer', array( $this, 'render_analytics_stat' ) );

		add_action( 'cartflows_add_flow_metabox', array( $this, 'add_analytics_metabox' ) );
	}

	/**
	 *
	 * Add Analytics Metabox
	 *
	 * @return void
	 */
	function add_analytics_metabox() {
		add_meta_box(
			'wcf-analytics-settings',                    // Id.
			__( 'Analytics', 'cartflows-pro' ), // Title.
			array( $this, 'analytics_metabox_markup' ),      // Callback.
			CARTFLOWS_FLOW_POST_TYPE,               // Post_type.
			'side',                               // Context.
			'high'                                  // Priority.
		);
	}

	/**
	 * Analytics Metabox Markup
	 *
	 * @return void
	 */
	function analytics_metabox_markup() {
		?>
		<div class="wcf-flow-sandbox-table wcf-general-metabox-wrap widefat">
			<div class="wcf-flow-sandbox-table-container">
				<?php

					echo wcf()->meta->get_description_field(
						array(
							'name'    => 'wcf-analytics-note',
							'content' => __( 'Analytics offers data that helps you understand how your flows are performing.', 'cartflows-pro' ),
						)
					);
					$this->setup_analytics_button();
				?>
			</div>
		</div>
		<?php
	}

	/**
	 * Render analytics display button beside title.
	 */
	public function setup_analytics_button() {

		if ( ! Cartflows_Admin::is_flow_edit_admin() ) {
			return;
		}

		$reports_btn_markup          = '<style>.wrap{ position:relative;}</style>';
		$reports_btn_markup         .= "<div class='wcf-reports-button-wrap'>";
			$reports_btn_markup     .= "<button class='wcf-trigger-reports-popup button button-secondary'>";
				$reports_btn_markup .= esc_html__( 'View Analytics', 'cartflows-pro' );
			$reports_btn_markup     .= '</button>';
		$reports_btn_markup         .= '</div>';

		echo $reports_btn_markup;

	}

	/**
	 * Set visits data for later use in analytics.
	 */
	public function set_visits_data() {

		$flow_id = sanitize_text_field( $_POST['flow_id'] );

		$earning = $this->get_earnings( $flow_id );
		$visits  = $this->fetch_visits( $flow_id );

		foreach ( $visits as $index => $visit ) {
			$visits[ $index ]->revenue = 0;
			$step_type                 = wcf()->utils->get_step_type( $visit->step_id );

			$visits[ $index ]->title = get_the_title( $visit->step_id );
			switch ( $step_type ) {

				case 'checkout':
					$visits[ $index ]->revenue = $earning['gross_sale'] - ( $earning['bump_offer'] + $earning['upsell'] + $earning['downsell'] );
					break;
				case 'upsell':
					$visits[ $index ]->revenue = $earning['upsell'];
					break;
				case 'downsell':
					$visits[ $index ]->revenue = $earning['downsell'];
					break;
			}

			$visits[ $index ]->revenue = number_format( (float) $visits[ $index ]->revenue, 2, '.', '' );
		}

		$response = array(
			'visits'  => $visits,
			'revenue' => $earning,
		);

		wp_send_json_success( $response );
	}

	/**
	 * Display analytics stat table.
	 */
	public function render_analytics_stat() {

		if ( ! Cartflows_Admin::is_flow_edit_admin() ) {
			return;
		}

		$currency_symbol = '';

		if ( wcf_pro()->is_woo_active ) {

			if ( function_exists( 'get_woocommerce_currency_symbol' ) ) {
				$currency_symbol = get_woocommerce_currency_symbol();
			}
		}

		?>
		<script type="text/template" id="tmpl-cartflows-analytics-template">

			<div class="wcf-analytics-summary">
				<div class="wcf-gross-wrap">
					<span class="wcf-gross-label"><?php _e( 'Gross Sale', 'cartflows-pro' ); ?></span>
					<div class="wcf-gross-sale">
						<?php echo $currency_symbol; ?>{{ data.revenue.gross_sale }}
					</div>
				</div>
				<div class="wcf-order-value-wrap">
					<span class="wcf-order-value-label"><?php _e( 'Average Order Value', 'cartflows-pro' ); ?></span>
					<div class="wcf-order-value">
						<?php echo $currency_symbol; ?>{{ data.revenue.avg_order_value }}
					</div>
				</div>
				<div class="wcf-bump-offer-wrap">
					<span class="wcf-bump-offer-label"><?php _e( 'Bump Offer Revenue', 'cartflows-pro' ); ?></span>
					<div class="wcf-bump-offer-sale">
						<?php echo $currency_symbol; ?>{{ data.revenue.bump_offer }}
					</div>
				</div>
			</div>
			<div class="wcf-analytics-filter-wrap">
				<div class="wcf-filters">

					<div class="wcf-filter-col text-left">
						<button data-diff="30" class="button button-{{ (30 == data.report_type) ? 'primary' : 'secondary' }} btn-first"><?php _e( 'Last Month', 'cartflows-pro' ); ?></button>
						<button data-diff="7" class="button button-{{ (7 == data.report_type) ? 'primary' : 'secondary' }}"><?php _e( 'Last Week', 'cartflows-pro' ); ?></button>
						<button data-diff="0" class=" button button-{{ ( 0 == data.report_type) ? 'primary' : 'secondary' }}"><?php _e( 'Today', 'cartflows-pro' ); ?></button>
					</div>
					<div class="wcf-filter-col text-right">
						<input class="wcf-custom-filter-input" type="text" id="wcf_custom_filter_from" placeholder="YYYY-MM-DD" value="" readonly="readonly" >
						<input class="wcf-custom-filter-input" type="text" id="wcf_custom_filter_to" placeholder="YYYY-MM-DD" value="" readonly="readonly" >
						<button data-diff="-1" id="wcf_custom_filter" class="button button-{{ (-1 == data.report_type) ? 'primary' : 'secondary' }}">Custom Filter</button>

					</div>
				</div>


			</div>
			<# if( data.visits.length ) { #>
			<div class="wcf-analytics-table-wrap">
				<table class="wcf-analytics-table">
					<tbody>
						<tr>
							<th class="wp-ui-highlight"><?php _e( 'Step', 'cartflows-pro' ); ?></th>
							<th class="wp-ui-highlight"><?php _e( 'Total visits', 'cartflows-pro' ); ?></th>
							<th class="wp-ui-highlight"><?php _e( 'Unique Visits', 'cartflows-pro' ); ?></th>
							<th class="wp-ui-highlight"><?php _e( 'Revenue', 'cartflows-pro' ); ?></th>
						</tr>
						<# for ( key in data.visits ) { #>
							<tr class="wcf-analytics-row">
								<td> {{data.visits[ key ].title}}</td>
								<td>{{data.visits[ key ].total_visits}}</td>
								<td>{{data.visits[ key ].unique_visits}}</td>
								<td><?php echo $currency_symbol; ?>{{data.visits[ key ].revenue}}</td>
							</tr>
						<# } #>
					</tbody>
				</table>
			</div>
			<# } else { #>
			<div class="wcf-no-data-found"> <strong> <?php _e( 'No Data Found', 'cartflows-pro' ); ?> </strong></div>
			<# } #>
		</script>
		<input type="hidden" id="cf-steps-data" data-steps="<?php echo htmlspecialchars( json_encode( self::$step_data ) ); ?>">

		<div id="wcf-analytics-popup-wrap" class="wcf-templates-popup-overlay">
			<div class="wcf-analytics-reports-content">
				<div class="wcf-analytics-header">
					<div class="wcf-template-logo-wrap">
					<span class="wcf-cartflows-logo-img">
						<span class="cartflows-icon"></span>
					</span>
					</div>
					<div class="wcf-analytics-report-title">
						<?php _e( 'Analytics Report', 'cartflows-pro' ); ?>
					</div>
					<div class="wcf-popup-close-wrap">
						<span class="close-icon"><span class="wcf-cartflow-icons dashicons dashicons-no"></span></span>
					</div>
				</div>
				<div class="wcf-analytics-reports-wrap">
				</div>
			</div>
		</div>

		<?php
	}


	/**
	 * Fetch total visits.
	 *
	 * @param integer $flow_id flow_id.
	 * @return array|object|null
	 */
	function fetch_visits( $flow_id ) {

		global $wpdb;
		$visit_db = $wpdb->prefix . CARTFLOWS_PRO_VISITS_TABLE;

		$start_date = filter_input( INPUT_POST, 'date_from', FILTER_SANITIZE_STRING );
		$end_date   = filter_input( INPUT_POST, 'date_to', FILTER_SANITIZE_STRING );
		$start_date = $start_date ? $start_date : date( 'Y-m-d' );
		$end_date   = $end_date ? $end_date : date( 'Y-m-d' );

		$steps    = wcf()->flow->get_steps( $flow_id );
		$step_ids = implode( ', ', wp_list_pluck( (array) $steps, 'id' ) );

		// phpcs:disable WordPress.DB.PreparedSQL.NotPrepared
		$query  = $wpdb->prepare(
			"SELECT step_id, COUNT(step_id) AS total_visits, COUNT(CASE WHEN Visit_type = 'New' THEN Step_id ELSE NULL END) AS unique_visits
             FROM $visit_db WHERE step_id IN ( $step_ids ) AND DATE(`date_visited`) >= %s AND DATE(`date_visited`) <= %s GROUP BY step_id",
			$start_date,
			$end_date
		);
		$visits = $wpdb->get_results( $query );

		$non_visited_steps = array_diff( wp_list_pluck( (array) $steps, 'id' ), wp_list_pluck( (array) $visits, 'step_id' ) );

		if ( $non_visited_steps ) {

			$non_visit = array(
				'step_id'       => 0,
				'total_visits'  => 0,
				'unique_visits' => 0,
				'revenue'       => 0,
			);

			foreach ( $non_visited_steps as $non_visited_step ) {

				$non_visit['step_id'] = $non_visited_step;
				array_push( $visits, (object) $non_visit );

			}
		}

		$step_ids_array = wp_list_pluck( (array) $steps, 'id' );
		usort(
			$visits,
			function ( $a, $b ) use ( $step_ids_array ) {
				return array_search( $a->step_id, $step_ids_array ) - array_search( $b->step_id, $step_ids_array );

			}
		);

        // phpcs:enable
		return $visits;

	}


	/**
	 * Calculate earning.
	 *
	 * @param integer $flow_id flow_id.
	 * @return array
	 */
	function get_earnings( $flow_id ) {

		$orders                   = $this->get_orders_by_flow( $flow_id );
		$total_upsell_earning     = 0;
		$total_downsell_earning   = 0;
		$total_bump_offer_earning = 0;
		$avg_order_value          = 0;
		$total                    = 0;

		if ( ! empty( $orders ) ) {

			foreach ( $orders as $order ) {

				$order_id            = $order->ID;
				$order               = wc_get_order( $order_id );
				$order_total         = $order->get_total();
				$total              += (float) $order_total;
				$bump_product_id     = get_post_meta( $order_id, '_wcf_bump_product', true );
				$upsell_earnings     = 0;
				$downsell_earning    = 0;
				$bump_offer_earnings = 0;

				foreach ( $order->get_items() as $item_id => $item_data ) {

					$item_product_id = $item_data->get_product_id();

					$item_total  = $item_data->get_total();
					$is_upsell   = wc_get_order_item_meta( $item_id, '_cartflows_upsell', true );
					$is_downsell = wc_get_order_item_meta( $item_id, '_cartflows_downsell', true );

					if ( 'yes' == $is_upsell ) {
						$upsell_earnings += $item_total;
					}

					if ( 'yes' == $is_downsell ) {
						$downsell_earning += $item_total;
					}

					if ( $item_product_id == $bump_product_id ) {
						$bump_offer_earnings += $item_total;
					}
				}

				$total_upsell_earning     += $upsell_earnings;
				$total_downsell_earning   += $downsell_earning;
				$total_bump_offer_earning += $bump_offer_earnings;
			}

			$avg_order_value = $total / count( $orders );
		}

		return array(
			'avg_order_value' => number_format( (float) $avg_order_value, 2, '.', '' ),
			'gross_sale'      => number_format( (float) $total, 2, '.', '' ),
			'upsell'          => number_format( (float) $total_upsell_earning, 2, '.', '' ),
			'downsell'        => number_format( (float) $total_downsell_earning, 2, '.', '' ),
			'bump_offer'      => number_format( (float) $total_bump_offer_earning, 2, '.', '' ),
		);
	}

	/**
	 * Load analytics scripts.
	 */
	function load_analytics_scripts() {

		if ( Cartflows_Admin::is_flow_edit_admin() ) {
			wp_enqueue_script( 'jquery-ui-datepicker' );
			wp_enqueue_style( 'jquery-ui-style' );
			wp_enqueue_script( 'cartflows-analytics-admin', CARTFLOWS_TRACKING_URL . 'assets/js/analytics-admin.js', array( 'jquery' ), CARTFLOWS_VER, true );
		}
	}


	/**
	 * Prepare where items for query.
	 *
	 * @param array $conditions conditions to prepare WHERE query.
	 * @return string
	 */
	protected function get_items_query_where( $conditions ) {

		global $wpdb;

		$where_conditions = array();
		$where_values     = array();

		foreach ( $conditions as $key => $condition ) {

			if ( false !== stripos( $key, 'IN' ) ) {
				$where_conditions[] = $key . '( %s )';
			} else {
				$where_conditions[] = $key . '= %s';
			}

			$where_values[] = $condition;
		}

		if ( ! empty( $where_conditions ) ) {
			// @codingStandardsIgnoreStart
			return $wpdb->prepare( 'WHERE 1 = 1 AND ' . implode( ' AND ', $where_conditions ), $where_values );
			// @codingStandardsIgnoreEnd
		} else {
			return '';
		}
	}


	/**
	 * Get orders data for flow.
	 *
	 * @param int $flow_id flow id.
	 * @return int
	 */
	function get_orders_by_flow( $flow_id ) {

		global $wpdb;

		$start_date = filter_input( INPUT_POST, 'date_from', FILTER_SANITIZE_STRING );
		$end_date   = filter_input( INPUT_POST, 'date_to', FILTER_SANITIZE_STRING );
		$start_date = $start_date ? $start_date : date( 'Y-m-d' );
		$end_date   = $end_date ? $end_date : date( 'Y-m-d' );

		$conditions = array(
			'tb1.post_type'           => 'shop_order',
			'tb2.meta_key'            => '_wcf_flow_id',
			'tb2.meta_value'          => $flow_id,
			'DATE( tb1.post_date ) >' => $start_date,
			'DATE( tb1.post_date ) <' => $end_date,
		);

		$where  = $this->get_items_query_where( $conditions );
		$where .= " AND tb1.post_status IN ( 'wc-completed', 'wc-processing' )";

		$query = 'SELECT tb1.ID, DATE( tb1.post_date ) date FROM ' . $wpdb->prefix . 'posts tb1 
		INNER JOIN ' . $wpdb->prefix . 'postmeta tb2
		ON tb1.ID = tb2.post_id 
		' . $where;

		// @codingStandardsIgnoreStart
		$orders = $wpdb->get_results( $query );
		// @codingStandardsIgnoreEnd

		self::$flow_orders = $orders;

		return $orders;
	}
}

Cartflows_Pro_Analytics_Reports::get_instance();
