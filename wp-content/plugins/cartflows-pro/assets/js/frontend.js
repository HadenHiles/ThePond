(function($){

	var wcf_process_offer = function ( ajax_data ) {
		ajax_data._nonce = cartflows_offer[ajax_data.action + "_nonce" ];
		$.ajax({
			url: cartflows.ajax_url,
			data: ajax_data,
			dataType: 'json',
			type: 'POST',
			success: function ( data ) {
				var msg = data.message;
				var msg_class = 'wcf-payment-' + data.status;
				$( "body").trigger( "wcf-update-msg", [ msg, msg_class ] );
				setTimeout(function() {
					window.location.href = data.redirect;
				}, 500);
			}
		});


	}

	var wcf_offer_button_action = function() {
		
		console.log( 'Offer Action' );

		$('a[href*="wcf-up-offer"], a[href*="wcf-down-offer"]').each(function(e) {

			var $this 	= $(this), 
				href 	= $this.attr('href');

			if ( href.indexOf( 'wcf-up-offer-yes' ) !== -1 ) {
				$this.attr( 'id', 'wcf-upsell-offer' );
			}

			if ( href.indexOf( 'wcf-down-offer-yes' ) !== -1 ) {
				$this.attr( 'id', 'wcf-downsell-offer' );
			}
		});
		
		$(document).on( 'click', 'a[href*="wcf-up-offer"], a[href*="wcf-down-offer"]', function(e) {
			
			e.preventDefault();

			console.log( $(this) );

			var $this 			= $(this), 
				href			= $this.attr('href'),
				offer_action 	= 'yes',
				offer_type		= 'upsell',
				step_id 		= cartflows_offer.step_id,
				product_id 		= cartflows_offer.product_id,
				order_id 		= cartflows_offer.order_id,
				order_key 		= cartflows_offer.order_key;

			if ( href.indexOf( 'wcf-up-offer' ) !== -1 ) {
				
				offer_type = 'upsell';

				if ( href.indexOf( 'wcf-up-offer-yes' ) !== -1 ) {
					offer_action = 'yes';
				}else{
					offer_action = 'no';
				}
			}

			if ( href.indexOf( 'wcf-down-offer' ) !== -1 ) {

				offer_type = 'downsell';

				if ( href.indexOf( 'wcf-down-offer-yes' ) !== -1 ) {
					offer_action = 'yes';
				}else{
					offer_action = 'no';
				}
			}

			if ( 'yes' === cartflows_offer.skip_offer && 'yes' === offer_action ) {
				return;
			}

			$( 'body' ).trigger( 'wcf-show-loader', offer_action );

			if ( 'yes' === offer_action ) {
				action = 'wcf_' + offer_type + '_accepted';
			}else{
				action = 'wcf_' + offer_type + '_rejected';
			}


			var ajax_data = {
				action				: '',
				offer_action		: offer_action,
				offer_type			: offer_type,
				step_id				: step_id,
				product_id			: product_id,
				order_id			: order_id,
				order_key			: order_key,
				stripe_sca_payment	: false,
				stripe_intent_id	: '',
				_nonce  			: '',
			};
			if( "stripe" === cartflows_offer.payment_method && 'yes' === offer_action ) {

				ajax_data.action = 'wcf_stripe_sca_check';
				ajax_data._nonce = cartflows_offer.wcf_stripe_sca_check_nonce;
				$.ajax({
					url: cartflows.ajax_url,
					data: ajax_data,
					dataType: 'json',
					type: 'POST',
					success: function ( response ) {

						if( response.hasOwnProperty('intent_secret') ) {
							var stripe = Stripe( response.stripe_pk );
							stripe.handleCardPayment( response.intent_secret )
								.then( function( response ) {

									if ( response.error ) {
										throw response.error;
									}
									if ( 'requires_capture' !== response.paymentIntent.status && 'succeeded' !== response.paymentIntent.status ) {
										return;
									}

									ajax_data.action = action;
									ajax_data.stripe_sca_payment = true;
									ajax_data.stripe_intent_id = response.paymentIntent.id;
									wcf_process_offer( ajax_data );
								} )
								.catch( function( error ) {

									window.location.reload();
								} );
						} else {

							ajax_data.action = action;
							wcf_process_offer( ajax_data );
						}
					}
				});
			} else {

				ajax_data.action = action;
				wcf_process_offer( ajax_data );
			}

			return false;
		});

		/* Will Remove later */
		$(document).on( 'click', '.wcf-upsell-offer, .wcf-downsell-offer', function(e) {
			
			e.preventDefault();

			var $this 			= $(this), 
				offer_action 	= $this.data('action'),
				offer_type		= 'upsell',
				step_id 		= $this.data('step'),
				product_id 		= $this.data('product'),
				order_id 		= $this.data('order'),
				order_key 		= $this.data('order_key');
			
			if ( $this.hasClass( 'cartflows-skip' ) && 'yes' === offer_action ) {
				return;
			}
			
			$( 'body' ).trigger( 'wcf-show-loader', offer_action );

			if ( $this.hasClass('wcf-downsell-offer') ) {
				offer_type = 'downsell';
			}

			if ( 'yes' === offer_action ) {
				action = 'wcf_' + offer_type + '_accepted';
			}else{
				action = 'wcf_' + offer_type + '_rejected';
			}

			$.ajax({
	            url: cartflows.ajax_url,
				data: {
					action: action,
					offer_action: offer_action,
					step_id: step_id,
					product_id: product_id,
					order_id: order_id,
					order_key: order_key,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( data ) {
					
					var msg = data.message;
					var msg_class = 'wcf-payment-' + data.status;

					$( "body").trigger( "wcf-update-msg", [ msg, msg_class ] );

					setTimeout(function() {
						window.location.href = data.redirect;
					}, 500);
				}
			});

			return false;
		});
		/* Will Remove later */
	}

	var wcf_facebook_pixel = function () {

		jQuery(document).ajaxComplete(function (event, xhr, settings) {
			if ( ! xhr.hasOwnProperty('responseJSON') ) {
				return;
			}
			var fragmants = xhr.responseJSON.hasOwnProperty('fragments') ? xhr.responseJSON.fragments : null;
			if ( fragmants && fragmants.hasOwnProperty('added_to_cart_data') ) {
				fbq('track', 'AddToCart', fragmants.added_to_cart_data.added_to_cart);
			}
		});

	}

	$(document).ready(function($) {

		$( "body" ).on( "wcf-show-loader", function( event, action ) {

			if( "no" === action ) {
				jQuery(".wcf-note-yes").hide();
				jQuery(".wcf-note-no").show()
			}

			$('.wcf-loader-bg').addClass('show');
		});

		$( "body" ).on( "wcf-hide-loader", function( event ) {
			console.log('Hide Loader');
			$('.wcf-loader-bg').removeClass('show');
		});

		$( "body" ).on( "wcf-update-msg", function( event, msg, msg_class ) {
			$('.wcf-order-msg .wcf-process-msg').text( msg ).addClass( msg_class );
		});

		wcf_offer_button_action();

		wcf_facebook_pixel();
	});
})(jQuery);