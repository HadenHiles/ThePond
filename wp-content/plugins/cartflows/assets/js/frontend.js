(function($){

	/* It will redirect if anyone clicked on link before ready */
	$(document).on( 'click', 'a[href*="wcf-next-step"]', function(e) {
		
		e.preventDefault();

		if( 'undefined' !== typeof cartflows.is_pb_preview && '1' == cartflows.is_pb_preview ) {
			e.stopPropagation();
			return;
		}

		window.location.href = cartflows.next_step; 

		return false;
	});

	/* Once the link is ready this will work to stop conditional propogation*/
	$(document).on( 'click', '.wcf-next-step-link', function(e) {

		if( 'undefined' !== typeof cartflows.is_pb_preview && '1' == cartflows.is_pb_preview ) {
			e.preventDefault();
			e.stopPropagation();
			return false;
		}
	});

	// Remove css when oceanwp theme is enabled.
	var remove_oceanwp_custom_style = function(){
		if( 'OceanWP' === cartflows.current_theme && 'default' !== cartflows.page_template){
			var style = document.getElementById("oceanwp-style-css");
			if( null != style ){
				style.remove();
			}
		}
	};

	var trigger_facebook_events = function () {
		if ('enable' === cartflows.fb_active['facebook_pixel_tracking']) {

			if (cartflows.fb_active['facebook_pixel_id'] != '') {

				var facebook_pixel = cartflows.fb_active['facebook_pixel_id'];
				var initial_checkout_event = cartflows.fb_active['facebook_pixel_initiate_checkout'];
				var purchase_event = cartflows.fb_active['facebook_pixel_purchase_complete'];
				var add_payment_info_event = cartflows.fb_active['facebook_pixel_add_payment_info'];
				var is_checkout_page = cartflows.is_checkout_page;

				fbq('init', facebook_pixel);
				fbq('track', 'PageView', {'plugin': 'CartFlows'});

				if ('enable' === initial_checkout_event) {
					if ('1' === is_checkout_page) {
						fbq('track', 'AddToCart', cartflows.params);
						fbq('track', 'InitiateCheckout', cartflows.params);
					}
				}

				if ('enable' === purchase_event) {
					var order_details = $.cookie('wcf_order_details');
					if (typeof order_details !== 'undefined') {
						fbq('track', 'Purchase', jQuery.parseJSON(order_details));
						$.removeCookie('wcf_order_details', {path: '/'});
					}
				}

				if ('enable' === add_payment_info_event) {
					jQuery("form.woocommerce-checkout").on('submit', function () {
						var params = cartflows.params;
						fbq('track', 'AddPaymentInfo', params);
					});
				}

			}
		}
	}

	$(document).ready(function($) {
		
		/* Assign the class & link to specific button */
		var next_links = $('a[href*="wcf-next-step"]');

		if ( next_links.length > 0 && 'undefined' !== typeof cartflows.next_step ) {
			next_links.addClass( 'wcf-next-step-link' );
			next_links.attr( 'href', cartflows.next_step );
		}
		remove_oceanwp_custom_style();

		trigger_facebook_events();
	});
	
})(jQuery);