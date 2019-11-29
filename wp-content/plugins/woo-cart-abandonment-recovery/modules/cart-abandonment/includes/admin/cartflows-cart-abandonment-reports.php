<?php
/**
 * Cartflows view for cart abandonment reports.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

?>

<div class="wcf-ca-report-btn">

	<div class="wcf-ca-left-report-field-group">
		<button onclick="window.location.search += '&filter=today';"
				class="button <?php echo 'today' === $filter ? 'button-primary' : 'button-secondary'; ?>"> <?php _e( 'Today', 'woo-cart-abandonment-recovery' ); ?>
		</button>

		<button onclick="window.location.search += '&filter=yesterday';"
				class="button <?php echo 'yesterday' === $filter ? 'button-primary' : 'button-secondary'; ?>"> <?php _e( 'Yesterday', 'woo-cart-abandonment-recovery' ); ?>
		</button>

		<button onclick="window.location.search += '&filter=last_week';"
				class="button <?php echo 'last_week' === $filter ? 'button-primary' : 'button-secondary'; ?>"> <?php _e( 'Last Week', 'woo-cart-abandonment-recovery' ); ?>
		</button>

		<button onclick="window.location.search += '&filter=last_month';"
				class="button <?php echo 'last_month' === $filter ? 'button-primary' : 'button-secondary'; ?> "> <?php _e( 'Last Month', 'woo-cart-abandonment-recovery' ); ?>
		</button>
	</div>

	<div class="wcf-ca-right-report-field-group">

		<input class="wcf-ca-filter-input" type="text" id="wcf_ca_custom_filter_from" placeholder="YYYY-MM-DD" value="<?php echo $from_date; ?>"/>
		<input class="wcf-ca-filter-input" type="text" id="wcf_ca_custom_filter_to" placeholder="YYYY-MM-DD" value="<?php echo $to_date; ?>" />
		<button id="wcf_ca_custom_filter"
				class="button <?php echo 'custom' === $filter ? 'button-primary' : 'button-secondary'; ?> "> <?php _e( 'Custom Filter', 'woo-cart-abandonment-recovery' ); ?>
		</button>

	</div>

</div>

<div class="wcf-ca-grid-container">

	<div class="wcf-ca-ibox">
		<div class="wcf-ca-ibox-title">
			<h3> <?php _e( 'Recoverable Orders', 'woo-cart-abandonment-recovery' ); ?> </h3>
		</div>
		<div class="wcf-ca-ibox-content">
			<h1> <?php echo $abandoned_report['no_of_orders']; ?> </h1>
			<small> <?php _e( 'Total Recoverable Orders.', 'woo-cart-abandonment-recovery' ); ?>  </small>
		</div>
	</div>

	<div class="wcf-ca-ibox">
		<div class="wcf-ca-ibox-title"><h3><?php _e( 'Recovered Orders', 'woo-cart-abandonment-recovery' ); ?></h3></div>
		<div class="wcf-ca-ibox-content"><h1><?php echo $recovered_report['no_of_orders']; ?></h1>
			<small> <?php _e( 'Total Recovered Orders.', 'woo-cart-abandonment-recovery' ); ?> </small>
		</div>
	</div>

	<div class="wcf-ca-ibox">
		<div class="wcf-ca-ibox-title"><h3><?php _e( 'Lost Orders', 'woo-cart-abandonment-recovery' ); ?></h3></div>
		<div class="wcf-ca-ibox-content"><h1
			><?php echo  $lost_report['no_of_orders']; ?></h1>
			<small> <?php _e( 'Total Lost Orders.', 'woo-cart-abandonment-recovery' ); ?>  </small>
		</div>
	</div>

</div>

<div class="wcf-ca-grid-container">

	<div class="wcf-ca-ibox">
		<div class="wcf-ca-ibox-title"><h3> <?php _e( 'Recoverable Revenue', 'woo-cart-abandonment-recovery' ); ?> </h3></div>
		<div class="wcf-ca-ibox-content">
			<h1>
				<?php echo $currency_symbol . number_format_i18n( $abandoned_report['revenue'], 2 ); ?>
			</h1>
			<small> <?php _e( 'Total Recoverable Revenue.', 'woo-cart-abandonment-recovery' ); ?> </small>
		</div>
	</div>

	<div class="wcf-ca-ibox">
		<div class="wcf-ca-ibox-title"><h3><?php _e( 'Recovered Revenue', 'woo-cart-abandonment-recovery' ); ?></h3></div>
		<div class="wcf-ca-ibox-content"><h1>
				<?php
				echo $currency_symbol . number_format_i18n( $recovered_report['revenue'], 2 );
				?>
			</h1>
			<small> <?php _e( 'Total Recovered Revenue.', 'woo-cart-abandonment-recovery' ); ?> </small>
		</div>
	</div>

	<div class="wcf-ca-ibox">
		<div class="wcf-ca-ibox-title"><h3> <?php _e( 'Recovery Rate', 'woo-cart-abandonment-recovery' ); ?> </h3></div>
		<div class="wcf-ca-ibox-content"><h1><?php echo $conversion_rate . '%'; ?></h1>
			<small><?php _e( 'Total Percentage Of Recovered Orders After Abandonment.', 'woo-cart-abandonment-recovery' ); ?> </small>
		</div>
	</div>

</div>

<hr/>

<div class="wcf-ca-report-btn">
	<div class="wcf-ca-left-report-field-group">
		<button onclick="window.location.search += '&filter_table=<?php echo WCF_CART_ABANDONED_ORDER; ?>';"
				class="button <?php echo WCF_CART_ABANDONED_ORDER === $filter_table ? 'button-primary' : 'button-secondary'; ?> "> <?php _e( 'Recoverable Orders', 'woo-cart-abandonment-recovery' ); ?>
		</button>
		<button onclick="window.location.search += '&filter_table=<?php echo WCF_CART_COMPLETED_ORDER; ?>';"
				class="button <?php echo WCF_CART_COMPLETED_ORDER === $filter_table ? 'button-primary' : 'button-secondary'; ?>"><?php _e( 'Recovered Orders', 'woo-cart-abandonment-recovery' ); ?>
		</button>
		<button onclick="window.location.search += '&filter_table=<?php echo WCF_CART_LOST_ORDER; ?>';"
				class="button <?php echo WCF_CART_LOST_ORDER === $filter_table ? 'button-primary' : 'button-secondary'; ?>"><?php _e( 'Lost Orders', 'woo-cart-abandonment-recovery' ); ?>
		</button>
	</div>
</div>



<?php
if ( count( $wcf_list_table->items ) ) {
	$page = filter_input( INPUT_GET, 'page', FILTER_SANITIZE_STRING );

	?>

<form id="wcf-cart-abandonment-table" method="GET">
	<input type="hidden" name="page" value="<?php echo esc_html( $page ); ?>"/>
	<?php $wcf_list_table->display(); ?>
</form>

	<?php
} else {

	echo '<div> <strong> ' . __( 'No Orders Found.', 'woo-cart-abandonment-recovery' ) . '</strong> </div>';

}

?>
