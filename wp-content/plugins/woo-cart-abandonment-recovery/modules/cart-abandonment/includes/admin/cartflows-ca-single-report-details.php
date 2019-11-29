<?php
/**
 * Cartflows view for single cart abandonment report details.
 *
 * @package Woocommerce-Cart-Abandonment-Recovery
 */

?>


<div class="wcf-ca-report-btn">
	<div class="wcf-ca-left-report-field-group">
		<?php
		if ( wp_get_referer() ) {
			$back_link = wp_get_referer();
		} else {
			$back_link = add_query_arg(
				array(
					'page'   => WCF_CA_PAGE_NAME,
					'action' => WCF_ACTION_REPORTS,
				),
				admin_url( '/admin.php' )
			);
		}
		?>
		<a href="<?php echo $back_link; ?>" class="button button-secondary back-button"><span
					class="dashicons dashicons-arrow-left"></span> <?php _e( 'Back to Reports', 'woo-cart-abandonment-recovery' ); ?> </a>
	</div>
</div>

<!-- First panel Start -->
<div class="wcf-ca-panel">
	<div class="wcf-ca-column wcf-ca-column-two wcf-ca-margin-right">
		<div class="wcf-ca-email-data">

			<div class="wcf-ca-report-btn" style="padding: 0px">
				<div class="wcf-ca-left-report-field-group">
					<h2> <?php _e( 'Email Details:', 'woo-cart-abandonment-recovery' ); ?> </h2>
				</div>
				<div class="wcf-ca-right-report-field-group">

					<?php if ( WCF_CART_ABANDONED_ORDER === $details->order_status && ! $details->unsubscribed ) : ?>
						<?php add_thickbox(); ?>
						<div id="wcf-ca-confirm-email-reschedule" style="display:none;">
							<div style="text-align:center;">
								<p>
									<?php
									_e(
										'All new activated emails will be reschedule for this abandoned order. <br/>New emails will
											be sent to user according to schedule time.',
										'woo-cart-abandonment-recovery'
									);
									?>
								</p>
								<p>
									<strong><?php _e( 'Are your sure?', 'woo-cart-abandonment-recovery' ); ?></strong>
								</p>
								<p>
									<button onclick="window.location.search += '&sub_action=<?php echo WCF_SUB_ACTION_REPORTS_RESCHEDULE; ?>';"
											class="button button-secondary"> <?php _e( 'Reschedule', 'woo-cart-abandonment-recovery' ); ?>
									</button>
									<button type="button"
											onclick='document.getElementById("TB_closeWindowButton").click()'
											class="button button-secondary"> <?php _e( 'Close', 'woo-cart-abandonment-recovery' ); ?>
									</button>
								</p>
							</div>
						</div>
						<a name="Do you really want to reschedule emails?" href="#TB_inline?&width=500&height=200&inlineId=wcf-ca-confirm-email-reschedule" class="thickbox button button-secondary"> <?php _e( 'Reschedule Emails', 'woo-cart-abandonment-recovery' ); ?> </a>
					<?php endif; ?>
				</div>
			</div>

			<?php if ( empty( $scheduled_emails ) ) : ?>
				<div style="text-align: center;"><strong> <?php _e( ' No Email Scheduled.', 'woo-cart-abandonment-recovery' ); ?></strong>
				</div>
			<?php else : ?>
				<table cellpadding="15" cellspacing="0" class="wcf-table wcf-table-striped fixed posts">
					<thead>
					<tr>

						<th class="wcf-ca-report-table-row"> <?php _e( 'Scheduled Template', 'woo-cart-abandonment-recovery' ); ?></th>
						<th class="wcf-ca-report-table-row"> <?php _e( 'Email Subject', 'woo-cart-abandonment-recovery' ); ?></th>
						<th class="wcf-ca-report-table-row"> <?php _e( 'Email Coupon', 'woo-cart-abandonment-recovery' ); ?></th>
						<th class="wcf-ca-report-table-row"> <?php _e( 'Email Sent', 'woo-cart-abandonment-recovery' ); ?></th>
						<th class="wcf-ca-report-table-row"><span class="dashicons dashicons-clock"></span> <?php _e( 'Scheduled At', 'woo-cart-abandonment-recovery' ); ?>
						</th>

					</tr>
					</thead>

					<tbody>
					<?php foreach ( $scheduled_emails as $scheduled_email ) : ?>

						<?php
						$email_tmpl_url = wp_nonce_url(
							add_query_arg(
								array(
									'page'       => WCF_CA_PAGE_NAME,
									'action'     => WCF_ACTION_EMAIL_TEMPLATES,
									'sub_action' => WCF_SUB_ACTION_EDIT_EMAIL_TEMPLATES,
									'id'         => $scheduled_email->template_id,
								),
								admin_url( '/admin.php' )
							),
							WCF_EMAIL_TEMPLATES_NONCE
						);



						switch ( $scheduled_email->email_sent ) {
							case 0:
								if ( $details->unsubscribed ) {
									$icon  = '<span class="dashicons dashicons-minus"></span>';
									$title = __( 'The email has been unsubscribed and won\'t be sent further.', 'woo-cart-abandonment-recovery' );
								} else {
									$icon  = '<span class="dashicons dashicons-no"></span>';
									$title = __( 'Email is in the queue and will be sent at the scheduled time.', 'woo-cart-abandonment-recovery' );
								}
								break;
							case 1:
								$icon  = '<span class="dashicons dashicons-yes wp-ui-text-highlight" ></span>';
								$title = __( 'The email has been sent.', 'woo-cart-abandonment-recovery' );
								break;
							case -1:
								$icon  = '<span class="dashicons dashicons-dismiss wp-ui-text-highlight" ></span>';
								$title = __( 'The email has been unscheduled due to the complete order and won\'t be sent further.', 'woo-cart-abandonment-recovery' );
								break;
						}


						$scheduled_time = date( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ), strtotime( $scheduled_email->scheduled_time ) );
						?>

						<tr class="wcf-ca-report-table-row">
							<td class="wcf-ca-report-table-row"><a
										href="<?php echo $email_tmpl_url; ?>"
										class="wp-ui-text-highlight"> <?php echo( $scheduled_email->template_name ); ?> </a>
							</td>
							<td class="wcf-ca-report-table-row"> <?php echo( $scheduled_email->email_subject ); ?> </td>
							<td class="wcf-ca-report-table-row"> <?php echo( $scheduled_email->coupon_code ? $scheduled_email->coupon_code : '--' ); ?> </td>
							<td class="wcf-ca-report-table-row wcf-ca-icon-row"> <?php echo $icon; ?> 
								<span class="wcf-ca-tooltip-text"><?php echo $title; ?></span>
							</td>
							<td class="wcf-ca-report-table-row"> <?php echo( $scheduled_time ); ?> </td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>

		</div>
	</div>

	<div class="wcf-ca-column wcf-ca-column-two wcf-ca-margin-left">
		<div class="wcf-ca-user-detail ">

			<div class="wcf-ca-report-btn" style="padding: 0px">
				<div class="wcf-ca-left-report-field-group">
					<h2> <?php _e( 'User Address Details:', 'woo-cart-abandonment-recovery' ); ?> </h2>
				</div>
				<div class="wcf-ca-right-report-field-group">
					<?php if ( $details->unsubscribed ) : ?>
						<span class="wcf-ca-tag"> <?php _e( 'Unsubscribed', 'woo-cart-abandonment-recovery' ); ?> </span>
					<?php endif; ?>

					<span class="wcf-ca-tag"> <?php echo ucfirst( $details->order_status ); ?> </span>
				</div>
			</div>

			<div class="wcf-ca-user-address wcf-pull-left">
				<h3> <?php _e( 'Billing Address', 'woo-cart-abandonment-recovery' ); ?> </h3>
				<p><strong> <?php _e( 'Name', 'woo-cart-abandonment-recovery' ); ?> </strong>
					<?php echo $user_details->wcf_first_name . ' ' . $user_details->wcf_last_name; ?> </p>
				<p>
					<strong> <?php _e( 'Email address', 'woo-cart-abandonment-recovery' ); ?> </strong>
					<a href="mailto:<?php echo( $details->email ); ?>"><?php echo( $details->email ); ?></a>
				</p>

				<p>
					<strong> <?php _e( 'Phone', 'woo-cart-abandonment-recovery' ); ?> </strong>
					<a href="tel:<?php echo( $user_details->wcf_phone_number ); ?>"><?php echo( $user_details->wcf_phone_number ); ?></a>
				</p>

				<p>
					<strong> <?php _e( 'Address 1:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo $user_details->wcf_billing_address_1; ?>
				</p>
				<p>
					<strong> <?php _e( 'Address 2:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo $user_details->wcf_billing_address_2; ?>
				</p>
				<p>
					<strong> <?php _e( 'Country, City:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_location ); ?>
				</p>
				<p>
					<strong> <?php _e( 'State:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_billing_state ); ?>
				</p>

				<p>
					<strong> <?php _e( 'Postcode:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_billing_postcode ); ?>
				</p>
			</div>

			<div class="wcf-ca-user-address wcf-pull-left">
				<h3> <?php _e( 'Shipping Address', 'woo-cart-abandonment-recovery' ); ?> </h3>
				<p>
					<strong> <?php _e( 'Address 1:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_shipping_address_1 ); ?>
				</p>
				<p>
					<strong> <?php _e( 'Address 2:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_shipping_address_2 ); ?>
				</p>
				<p>
					<strong> <?php _e( 'City:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_shipping_city ); ?>
				</p>
				<p>
					<strong> <?php _e( 'State:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_shipping_state ); ?>
				</p>
				<p>
					<strong> <?php _e( 'Country:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_shipping_country ); ?>
				</p>
				<p>
					<strong> <?php _e( 'Postcode:', 'woo-cart-abandonment-recovery' ); ?> </strong> <?php echo( $user_details->wcf_shipping_postcode ); ?>
				</p>
				<p>
					<?php
					$cart_abandonment = Cartflows_Ca_Cart_Abandonment::get_instance();
					$token_data       = array( 'wcf_session_id' => $details->session_id );
					?>
					<strong> <a target="_blank" href=" <?php echo $cart_abandonment->get_checkout_url( $details->checkout_id, $token_data ); ?> ">
							<?php _e( 'Checkout Link', 'woo-cart-abandonment-recovery' ); ?>
						</a>
					</strong>
				</p>
			</div>

		</div>
	</div>
</div>
<!-- First panel closed -->

<!-- Second panel Start -->
<div class="wcf-ca-panel">
	<div class="wcf-ca-column wcf-ca-column-one">
		<div class="wcf-ca-user-order">
			<h2> <?php _e( 'User Order Details:', 'woo-cart-abandonment-recovery' ); ?> </h2>
			<?php echo( $this->get_admin_product_block( $details->cart_contents, $details->cart_total ) ); ?>
		</div>
	</div>
</div>
<!-- Second panel closed -->
