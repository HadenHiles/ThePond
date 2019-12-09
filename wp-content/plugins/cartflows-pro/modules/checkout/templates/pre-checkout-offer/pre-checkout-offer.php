<?php
// @codingStandardsIgnoreStart
?>

<div class="wcf-pre-checkout-offer-wrapper wcf-pre-checkout-full-width">
	<div id="wcf-pre-checkout-offer-modal">
		<div class="wcf-content-main-wrapper"><?php /*Don't remove this html comment*/ ?><!-- --> 
			<div class="wcf-lightbox-content">
				<div class="wcf-content-modal-progress-bar">
					<div class="wcf-progress-bar-nav">
						<div class="wcf-pre-checkout-progress">
							<div class="wcf-nav-bar-step active">
								<div class="wcf-nav-bar-title">
									<?php _e( 'Order Submitted', 'cartflows-pro' ); ?>
								</div>
								<div class="wcf-nav-bar-step-line">
									<div class="wcf-progress-nav-step"></div>
								</div>
							</div>
							<div class="wcf-nav-bar-step active inprogress">
								<div class="wcf-nav-bar-title">
									<?php _e( 'Special Offer', 'cartflows-pro' ); ?>
								</div>
								<div class="wcf-nav-bar-step-line">
									<div class="wcf-progress-nav-step"></div>
								</div>
							</div>
							<div class="wcf-nav-bar-step">
								<div class="wcf-nav-bar-title">
									<?php _e( 'Order Receipt', 'cartflows-pro' ); ?>
								</div>
								<div class="wcf-nav-bar-step-line">
									<div class="wcf-progress-nav-step"></div>
								</div>
							</div>
						</div>
					</div>
				</div>

				<div class="wcf-content-main-head">
					<div class="wcf-content-modal-title"><h1><?php echo $pre_checkout_popup_title; ?></h1></div>
					<?php if(!empty($pre_checkout_popup_sub_title)){ ?>
						<div class="wcf-content-modal-sub-title"><span><?php echo $pre_checkout_popup_sub_title; ?></span></div>
					<?php } ?>	
				</div>

				<!-- <div class="wcf-pre-checkout-divider">X</div> -->
				
				<div id="wcf-pre-checkout-offer-content" class="woocommerce">
					<div class="wcf-pre-checkout-info wcf-pre-checkout-img">
						<img src="<?php echo $src; ?>" />
					</div>
					<div class="wcf-pre-checkout-info wcf-pre-checkout-offer-product-details">
						<div class="wcf-pre-checkout-offer-product-title"><h1><?php echo $product_title; ?></h1></div>
						
						<?php if(!empty($price_html)){ ?>
							<div class="wcf-pre-checkout-offer-price"><?php echo $price_html; ?></div>
						<?php }?>

						<div class="wcf-pre-checkout-offer-desc"><span><?php echo $product_description; ?></span></div>
						
						<input type="hidden" value="add" class="wcf-pre-checkout-offer-action" name="wcf-pre-checkout-offer-action">
					</div>
					<div class="wcf-pre-checkout-offer-actions">
						<div class="wcf-pre-checkout-offer-btn-action wcf-pre-checkout-add-cart-btn">
							<button class="wcf-pre-checkout-offer-btn button alt" data-wcf-pre-checkout-offer-btn type="submit" data-pre-checkout-offer-flow="<?php echo base64_encode($checkout_id); ?>">
							<?php echo $pre_checkout_popup_btn_text; ?></button>
						</div>
						<div class="wcf-pre-checkout-offer-btn-action wcf-pre-checkout-skip-btn">
							<a class="wcf-pre-checkout-skip" data-wcf-pre-checkout-skip href="javascript:void(0);"><?php echo $pre_checkout_popup_skip_btn_text; ?></a>
						</div>
					</div>
				</div>

			</div>
		</div>
	</div>
</div>
<?php 
	// @codingStandardsIgnoreEnd
