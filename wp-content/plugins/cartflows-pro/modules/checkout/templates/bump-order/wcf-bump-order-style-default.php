<?php
// @codingStandardsIgnoreStart
?>
<div class="wcf-bump-order-wrap default">

	<?php $this->get_order_bump_hidden_data( $product_id, $order_bump_checked ); ?>

	<div class="wcf-bump-order-field-wrap">
		<label>
			<span class="dashicons dashicons-arrow-right-alt"></span>
			<input type="checkbox" class="wcf-bump-order-cb" name="wcf-bump-order-cb" value="<?php echo $product_id; ?>" id="wcf-bump-order-cb"<?php checked( $order_bump_checked, true, false ); ?> >
			<span class="wcf-bump-order-label"><?php echo esc_attr( $order_bump_label ); ?></span>
			<span class="dashicons dashicons-arrow-left-alt"></span>
		</label>
	</div>

	<div class="wcf-bump-order-desc">
		<span class="wcf-bump-order-bump-highlight"><?php echo esc_attr( $order_bump_hl_text ); ?></span>&nbsp;<?php echo esc_attr( $order_bump_desc ); ?>
	</div>
</div>
<?php 
	// @codingStandardsIgnoreEnd
