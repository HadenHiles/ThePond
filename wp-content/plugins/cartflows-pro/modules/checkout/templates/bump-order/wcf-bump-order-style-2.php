<?php
// @codingStandardsIgnoreStart
?>
<div class="wcf-bump-order-wrap wcf-bump-order-style-2 wcf-<?php echo $order_bump_pos; ?>">
	
	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<!-- wcf-bump-order-content -->
	<div class="wcf-bump-order-content">

		<!-- Offer box  -->
		<div class="wcf-bump-order-offer">
			<span class="wcf-bump-order-bump-highlight"> <?php echo $order_bump_hl_text; ?>
			</span>
		</div>
		<!-- Offer box  -->

		<!-- wcf-content-wrap -->
		<div class="wcf-content-container">

		<?php 
			if( isset( $bump_order_image ) && ! empty( $bump_order_image ) ){	
		?>
			<!-- Left side box -->
			<div class="wcf-bump-order-offer-content-left">
				<img src="<?php if( isset( $bump_order_image ) ){ print $bump_order_image;}?>" class="wcf-image" />
			</div>
			<!-- Left side box  -->
		<?php 
			}
		?>
			<!-- Right side box  -->
			<div class="wcf-bump-order-offer-content-right">

				<!-- wcf-bump-order-desc -->
				<div class="wcf-bump-order-desc">
					<?php echo $order_bump_desc; ?>
				</div>
				<!-- wcf-bump-order-desc -->
			</div>
			<!-- Right side box  -->

			<!-- wcf-bump-order-field-wrap -->
			<div class="wcf-bump-order-field-wrap">
				<label>
					<?php if( isset( $bump_order_blinking_arrow ) ){ echo $bump_order_blinking_arrow;} ?>
					<input type="checkbox" class="wcf-bump-order-cb" name="wcf-bump-order-cb" value="<?php echo $product_id; ?>" id="wcf-bump-order-cb"<?php checked( $order_bump_checked, true, true ); ?> >
					<span class="wcf-bump-order-label"><?php echo $order_bump_label; ?>
					</span>
				</label>
			</div>
			<!-- wcf-bump-order-field-wrap -->

		</div>
		<!-- wcf-content-wrap -->
	</div>
	<!-- wcf-bump-order-content -->

</div> 
 <!-- Main Div Close -->
<?php 
	// @codingStandardsIgnoreEnd
