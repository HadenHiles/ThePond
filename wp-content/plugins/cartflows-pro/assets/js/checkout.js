(function($){

	var wcf_toggle_optimized_fields = function () {
		jQuery.each(cartflows_optimized_fields, function (field, cartflows_optimized_field) {
			if (cartflows_optimized_field.is_optimized) {
				jQuery("#" + field).prepend('<a href="#" id="wcf_optimized_' + field + '">' + cartflows_optimized_field.field_label + '</a>');
				jQuery("#wcf_optimized_" + field).click(function (e) {
					e.preventDefault();
					jQuery("#" + field).removeClass('wcf-hide-field')
					// jQuery("#" + field).removeClass('mt20')
					var field_id = field.replace(/_field/g,'')
					$("#"+field_id).focus();
					jQuery(this).remove();
				});
			}
		});
	}

	var wcf_page_title_notification = {
		vars: {
			originalTitle: document.title,
			interval: null
		},
		On: function (notification, intervalSpeed) {
			var _this = this;
			_this.vars.interval = setInterval(function () {
				document.title = (_this.vars.originalTitle == document.title)
					? notification
					: _this.vars.originalTitle;
			}, (intervalSpeed) ? intervalSpeed : 1000);
		},
		Off: function () {
			clearInterval(this.vars.interval);
			document.title = this.vars.originalTitle;
		}
	}

	var wcf_animate_browser_tab = function () {

		$(window).blur(function () {
			wcf_page_title_notification.On(cartflows_animate_tab_fields.title);
		});

		$(window).focus(function () {
			wcf_page_title_notification.Off();
		});

	}

	var wcf_display_spinner = function () {

		$( '.woocommerce-checkout-review-order-table, .wcf-product-option-wrap' ).block({
			message: null,
			overlayCSS: {
				background: '#fff',
				opacity: 0.6
			}
		});

	}

	var wcf_remove_spinner = function (rsp) {

		if (jQuery(".wc_payment_methods").length) {

			if (rsp.hasOwnProperty('cart_total')) {

				var cart_total = parseFloat(rsp.cart_total);
				if (cart_total > 0) {
					if (rsp.hasOwnProperty('fragments')) {
						$.each(rsp.fragments, function (key, value) {
							$(key).replaceWith(value);
						});
					}
					$( '.woocommerce-checkout-review-order-table, .wcf-product-option-wrap' ).unblock();
				} else {
					$("body").trigger("update_checkout");
				}
			}

		} else {
			$("body").trigger("update_checkout");
		}
	}

	var wcf_product_quantity_var_options = function() {
		
		/* Single Selection */
		$( document ).on( "change", ".wcf-single-sel", function() {
			
			var $this 	= $( this );
			var option 	= $this.data( 'options' );
			var wrap 	= $this.closest('.wcf-qty-row');
			var input 	= wrap.find( '.wcf-qty input' );
			var qty 	= input.val();
			var checkout_id 	= jQuery('._wcf_checkout_id').val();

			option['qty'] 		= qty;
			option['checkout_id'] 		= checkout_id;

			wcf_display_spinner();
			$.ajax({
	            url: cartflows.ajax_url,
				data: {
					action: "wcf_single_selection",
					option: option,
					security: cartflows.wcf_single_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					wcf_remove_spinner( response );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				}
			});
		});

		/* Multiple Selection */
		$( document ).on( "change", ".wcf-multiple-sel", function() {
			
			var checked_cb = $('.wcf-multiple-sel:checked');
			var $this 	= $( this );
			var option 	= $this.data( 'options' );
			var wrap 	= $this.closest('.wcf-qty-row');
			var input 	= wrap.find( '.wcf-qty input' );
			var qty 	= input.val();

			if( 0 == checked_cb.length ){
				$this.attr('checked', true);
				$this.attr('disabled', true);
				return;
			}

			if ( 1 == checked_cb.length ) {
				checked_cb.attr( 'disabled', true );
			}else{
				checked_cb.removeAttr( 'disabled' );
			}

			option['qty'] 		= qty;
			option['checked']	= 'no';

			if ( $this.is(":checked") ) {
				option['checked']	= 'yes';
			}

			wcf_display_spinner();

			$.ajax({
	            url: cartflows.ajax_url,
				data: {
					action: "wcf_multiple_selection",
					option: option,
					security: cartflows.wcf_multiple_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {

					wcf_remove_spinner( response );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				}
			});
		});

		/* Force All Selection */
		$( document ).on( "change", ".wcf-var-sel", function() {
			
			var $this 	= $( this );
			var wrap 	= $this.closest('.wcf-qty-row');
			var input 	= wrap.find( '.wcf-qty input' );
			var option 	= input.data( 'options' );
			var qty 	= input.val();

			option['qty'] = qty;

			wcf_display_spinner();
			$.ajax({
	            url: cartflows.ajax_url,
				data: {
					action: "wcf_variation_selection",
					option: option,
					security: cartflows.wcf_variation_selection_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					wcf_remove_spinner( response );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				}
			});
		});


		/* Quantity Selection For All type */
		$( document ).on( "change", ".wcf-qty-selection", function() {
			
			var $this 			= $( this );
				wrap 			= $this.closest( '.wcf-qty-row' );
				item_selector	= wrap.find( '.wcf-item-selector' );

			if ( item_selector.length > 0 ) {

				var selector_input = item_selector.find( 'input' );
				
				if ( selector_input.length > 0 && ! selector_input.is(':checked') ) {
					return;
				}	
			}
			
			var option 	= $this.data( 'options' );
			var qty 	= $this.val();

			option['qty'] = qty;

			wcf_display_spinner();
			$.ajax({
	            url: cartflows.ajax_url,
				data: {
					action: "wcf_quantity_update",
					option: option,
					security: cartflows.wcf_quantity_update_nonce,
				},
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					wcf_remove_spinner( response );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				}
			});
		});

		/* Variation Popup */
		wcf_quick_view();
	};

	var wcf_order_bump_ajax = function() {

		var wcf_order_bump_clicked = false;
		var wcf_order_bump_checked = false;

		$(document).on( "change", ".wcf-bump-order-cb", function(e) {
			
			if( true === wcf_order_bump_clicked ) {
				return false;
			}

			wcf_order_bump_clicked = true;

			var $this = $(this);
			var product_id = $this.val();
			var checkout_id = $("[name=_wcf_checkout_id]").val();
			var data = {
				security: cartflows.wcf_bump_order_process_nonce,
				_wcf_checkout_id: checkout_id,
				_wcf_product_id: product_id,
				action: 'wcf_bump_order_process'
			}
			if( $this.is(":checked") ) {
				wcf_order_bump_checked = true;
				$("[name=_wcf_bump_product]").val( product_id );
				$("[name=_wcf_bump_product_action]").val( 'add_bump_product' );
				data._wcf_bump_product_action = 'add_bump_product';
			} else {
				wcf_order_bump_checked = false;
				$("[name=_wcf_bump_product]").val( '' );
				$("[name=_wcf_bump_product_action]").val( 'remove_bump_product' );
				data._wcf_bump_product_action = 'remove_bump_product';
			}

			wcf_display_spinner();

			$.ajax({
				url: cartflows.ajax_url,
				data: data,
				dataType: 'json',
				type: 'POST',
				success: function ( response ) {
					wcf_remove_spinner( response );
				},
				error: function ( error ) {
					$( '.woocommerce-checkout-review-order-table' ).unblock();
				}
			});

			wcf_order_bump_clicked = false;

			return false;
		});

		// Fire updated_checkout event.
		$( document.body ).on( 'updated_checkout', function( e, data ){
			$("[name=_wcf_bump_product_action]").val( '' );
		});
	};

	var wcf_nav_tab_hide_show_events = function() {

		/* Ready */
		wcf_nav_tab_hide_show();
		$('.wcf-embed-checkout-form-two-step  #customer_details').show();
		$('.wcf-embed-checkout-form-two-step .woocommerce-form-coupon-toggle').hide();
		$('.wcf-embed-checkout-form-two-step .wcf-product-option-wrap').hide();
		$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns').show();

		/* Change Custom Field*/
		$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps a').on( 'click', function( e ) {
			e.preventDefault();
			wcf_nav_tab_hide_show();
		});

		/* Change on click of next button */
		$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns a').on( 'click', function( e ) {
			e.preventDefault();

			// Check form validation before go to step two.
			
			wcf_nav_tab_hide_show_next_btn();

		});
	};

	var wcf_nav_tab_hide_show_next_btn = function(){

		if( wcf_two_step_validations() == 'true' ){
			$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div.wcf-current').removeClass('wcf-current');

			var selector = $('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns a').attr('href'),
				scrollTo = '';

				if(selector == '#customer_details'){
	    			$(selector).show();

	    			_scroll_to_top($('.wcf-embed-checkout-form-nav'));

	    			$('.wcf-embed-checkout-form-two-step .wcf-product-option-wrap').hide();
	    			$('.wcf-embed-checkout-form-two-step .wcf-order-wrap').hide();
	    			// $('.wcf-embed-checkout-form-two-step .wcf-order-wrap_heading').hide();
	    			$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns').show();
	    			$('.wcf-embed-checkout-form-two-step').find('.step-one').addClass('wcf-current');
	    			$('.wcf-embed-checkout-form-two-step .woocommerce-form-coupon-toggle').hide();
				}else if(selector == '.wcf-order-wrap'){
					$(selector).show();

					_scroll_to_top($('.wcf-embed-checkout-form-nav'));

					$('.wcf-embed-checkout-form-two-step .wcf-product-option-wrap').show();
	    			// $('.wcf-embed-checkout-form-two-step .wcf-order-wrap_heading').show();
	    			$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns').hide();
	    			$('.wcf-embed-checkout-form-two-step').find('.step-two').addClass('wcf-current');
	    			$('.wcf-embed-checkout-form-two-step .woocommerce-form-coupon-toggle').show();
	    			$('.wcf-embed-checkout-form-two-step #customer_details').hide();
				}

			// Scroll to top for the two step navigation.
			function _scroll_to_top(scrollTo){

			    if( scrollTo.length ) {
			        event.preventDefault();
			        $('html, body').stop().animate({
			            scrollTop: scrollTo.offset().top
			        }, 100);
			    }
			}

		}
	};

	var wcf_nav_tab_hide_show = function(){

		$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps a').on( 'click', function( e ) {
			e.preventDefault();

			// Check form validation before go to step two.
			// if ( $("div").hasClass("wcf-embed-checkout-form-two-step") && ! $('form[name="checkout"]').valid() ) {
			// 	return false;
			// }
				
			if( wcf_two_step_validations() == 'true' ){
				
				
				var $this 	= $(this),
					wrap 	= $this.closest('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div');
				// 	validated = wcf_woocommerce_field_validate();

				// if(validated == false){
				// 	return false;
				// }

				$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div.wcf-current').removeClass('wcf-current');
	    		wrap.addClass('wcf-current');
				
				var selector = $this.closest('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-steps div a').attr('href');
				if(selector == '#customer_details'){
	    			$(selector).show();
	    			$('.wcf-embed-checkout-form-two-step .wcf-order-wrap').hide();
	    			// $('.wcf-embed-checkout-form-two-step .wcf-order-wrap_heading').hide();
	    			$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns').show();
	    			$('.wcf-embed-checkout-form-two-step .wcf-product-option-wrap').hide();
	    			$('.wcf-embed-checkout-form-two-step .woocommerce-form-coupon-toggle').hide();
				}else if(selector == '.wcf-order-wrap'){
					$(selector).show();
					$('.wcf-embed-checkout-form-two-step .wcf-embed-checkout-form-nav-btns').hide();
					$('.wcf-embed-checkout-form-two-step .wcf-product-option-wrap').show();
	    			// $('.wcf-embed-checkout-form-two-step .wcf-order-wrap_heading').show();
	    			$('.wcf-embed-checkout-form-two-step .woocommerce-form-coupon-toggle').show();
	    			$('.wcf-embed-checkout-form-two-step #customer_details').hide();
				}
			}
		});
	};

	/**
	* Label Animation
	*/
	var wcf_anim_field_label = function(){

		var $inputs = $('.wcf-field-style-one form.woocommerce-checkout').find('input');
		var $select_input = $('.wcf-field-style-one form.woocommerce-checkout').find('.select2');
		var $textarea = $('.wcf-field-style-one form.woocommerce-checkout').find('textarea');

		//Add focus class on clicked on input types
		$inputs.focus(function(){
		    var $this 		= $(this),
		    	field_row   = $this.closest('.form-row');
		    	has_class   = field_row.hasClass('wcf-anim-label');
		    	field_value = $this.val();
		    	
			    if ( field_value == '' )  
			    {
		    		field_row.addClass('wcf-anim-label');
			    }
		});

		//Remove focus class on clicked outside/other input types
		$inputs.focusout(function(){
		    var $this 		= $(this),
		    	field_row   = $this.closest('.form-row');
		    	has_class   = field_row.hasClass('wcf-anim-label');
		    	field_value = $this.val();

			    if ( field_value == '' )  
			    {
		    		field_row.removeClass('wcf-anim-label');
			    }else{
			    	field_row.addClass('wcf-anim-label');
			    }
		});

		//Add focus class on clicked on Select
		$select_input.click(function(){
		    var $this 		= $(this),
		    	field_row   = $this.closest('.form-row');
		    	has_class   = field_row.hasClass('wcf-anim-label');
		    	field_value = $this.find('.select2-selection__rendered').text();

			    if ( field_value == '' )  
			    {
		    		field_row.addClass('wcf-anim-label');
			    }
		});

		//Remove focus class on clicked outside/another Select or fields
		$select_input.focusout(function(){
		    var $this 		= $(this),
		    	field_row   = $this.closest('.form-row');
		    	has_class   = field_row.hasClass('wcf-anim-label');
		    	field_value = $this.find('.select2-selection__rendered').text();

			    if ( field_value == '' )  
			    {
		    		field_row.removeClass('wcf-anim-label');
			    }else{
			    	field_row.addClass('wcf-anim-label');
			    }
		});



		//Add focus class on clicked on textarea
		$textarea.click(function(){
		    var $this 		= $(this),
		    	field_row   = $this.closest('.form-row');
		    	has_class   = field_row.hasClass('wcf-anim-label');
		    	field_value = $this.val();
		    	
			    if ( field_value == '' )  
			    {
		    		field_row.addClass('wcf-anim-label');
			    }
		});

		//Remove focus class on clicked outside/another textarea or fields
		$textarea.focusout(function(){
		    var $this 		= $(this),
		    	field_row   = $this.closest('.form-row');
		    	has_class   = field_row.hasClass('wcf-anim-label');
		    	field_value = $this.val();

			    if ( field_value == '' )  
			    {
		    		field_row.removeClass('wcf-anim-label');
			    }else{
			    	field_row.addClass('wcf-anim-label');
			    }
		});
	};

	var wcf_anim_field_label_event = function(){

		//Add focus class automatically if value is present in input
		var $all_inputs = $('.wcf-field-style-one form.woocommerce-checkout').find('input');

		$( $all_inputs ).each(function( index ) {

			var $this = $( this ),
				field_type  = $this.attr('type'),
				form_row = $this.closest('.form-row'),
				has_class = form_row.hasClass('mt20'),
				input_elem_value = $this.val();
				
				_add_anim_class(has_class, input_elem_value, field_type, form_row);
		});

		//Add focus class automatically if value is present in selects
		var $all_selects = $('.wcf-field-style-one form.woocommerce-checkout').find('select');

		$( $all_selects ).each(function( index ) {

			var $this = $( this ),
				form_row = $this.closest('.form-row'),
				field_type  = 'select',
				has_class = form_row.hasClass('mt20'),
				input_elem_value = $this.val();
				
				_add_anim_class(has_class, input_elem_value, field_type, form_row);
		});

		// Common function to add wcf-anim-label
		function _add_anim_class(has_class, input_elem_value, field_type, form_row){

			if(has_class){
				form_row.removeClass('mt20');
			}

			if( input_elem_value !== '' || input_elem_value !== ' ' && 'select' === field_type ){

				form_row.addClass('wcf-anim-label');
			}

			if( field_type === 'hidden' ){
				form_row.removeClass('wcf-anim-label');
				form_row.addClass('wcf-anim-label-fix');
			}
		}

	};

	/* Autocomplete Zip Code */
	var wcf_autocomplete_zip_data = function(){
		 
		var zip_code_timeout;
		
		$(document.body).on('textInput input change keypress paste', '#billing_postcode, #shipping_postcode', function(e) {
			var $this 		= $(this), 
				type 		= $this.attr("id").split("_")[0],
				zip_code 	= $this.val().trim(),
				country 	= $('#' + type + '_country').val();

			if ( '' === country ) {
				return;
			}

			if ( '' === zip_code ) {
				return;
			}

			clearTimeout( zip_code_timeout );

			zip_code_timeout = setTimeout(function() {
				if ( -1 === ["GB", "CA"].indexOf( country ) ) {
					get_zip_data_and_update( type, country, zip_code);
				}
			}, 800 );
		});

		var get_zip_data_and_update = function( type, country, zip_code ) {

			$.ajax({
				url: "https://api.zippopotam.us/" + country + "/" + zip_code,
				cache: !0,
				dataType: "json",
				type: "GET",
				success: function( result, status ) {

					$.each(result.places, function(e, t) {
						$("#" + type + "_city").val(this["place name"]).trigger("change");
						$('[name="' + type + '_state"]:visible').val(this["state abbreviation"]).trigger("change");
						return false;
					});
				},
				error: function(e, t) {}
			});
		};
	};

	/**
	 * Quick View
	 */
	var wcf_quick_view = function() {

		
		var quick_view_btn 	= $('.wcf-item-choose-options a');
		
		var modal_wrap 		= $('.wcf-quick-view-wrapper' );

		modal_wrap.appendTo( document.body );

		var wcf_quick_view_bg    	= modal_wrap.find( '.wcf-quick-view-bg' ),
			wcf_qv_modal    		= modal_wrap.find( '#wcf-quick-view-modal' ),
			wcf_qv_content  		= wcf_qv_modal.find( '#wcf-quick-view-content' ),
			wcf_qv_close_btn 		= wcf_qv_modal.find( '#wcf-quick-view-close' ),
			wcf_qv_wrapper  		= wcf_qv_modal.find( '.wcf-content-main-wrapper'),
			wcf_qv_wrapper_w 		= wcf_qv_wrapper.width(),
			wcf_qv_wrapper_h 		= wcf_qv_wrapper.height();

		quick_view_btn
			.off( 'click' )
			.on( 'click', function(e){
				
				e.preventDefault();

				var $this = $(this);
				
				/* Check if product is selected */
				var cls_wrap = $this.closest( '.wcf-item' );			
				
				if ( ! cls_wrap.find( '.wcf-item-selector input' ).is(':checked') ) {
					cls_wrap.find( '.wcf-item-selector input' ).trigger('click');
				}

				var product_id  = $this.data( 'product' );

				$this.addClass( 'wcf-variation-popup-open' );

				if( ! wcf_qv_modal.hasClass( 'loading' ) ) {
					wcf_qv_modal.addClass('loading');
				}

				if ( ! wcf_quick_view_bg.hasClass( 'wcf-quick-view-bg-ready' ) ) {
					wcf_quick_view_bg.addClass( 'wcf-quick-view-bg-ready' );
				}

				$(document).trigger( 'wcf_quick_view_loading' );
				
				wcf_qv_ajax_call( $this, product_id );
			});

		var wcf_qv_ajax_call = function( t, product_id ) {

			wcf_qv_modal.css( 'opacity', 0 );

			$.ajax({
	            url: cartflows.ajax_url,
				data: {
					action: 'wcf_woo_quick_view',
					product_id: product_id
				},
				dataType: 'html',
				type: 'POST',
				success: function (data) {
					wcf_qv_content.html(data);
					wcf_qv_content_height();
				}
			});
		};

		var wcf_qv_content_height = function() {

			// Variation Form
			var form_variation = wcf_qv_content.find('.variations_form');

			form_variation.trigger( 'check_variations' );
			form_variation.trigger( 'reset_image' );

			if (!wcf_qv_modal.hasClass('open')) {

				wcf_qv_modal.removeClass('loading').addClass('open');

				var scrollbar_width = wcf_get_scrollbar_width();
				var $html = $('html');

				$html.css( 'margin-right', scrollbar_width );
				$html.addClass('wcf-quick-view-is-open');
			}

			if ( form_variation.length > 0 && 'function' === typeof form_variation.wc_variation_form) {
				form_variation.wc_variation_form();
				form_variation.find('select').change();
			}

			/*wcf_qv_content.imagesLoaded( function(e) {

				var image_slider_wrap = wcf_qv_modal.find('.wcf-qv-image-slider');

				if ( image_slider_wrap.find('li').length > 1 ) {
					image_slider_wrap.flexslider({
						animation: "slide",
						start: function( slider ){
							setTimeout(function() {
								wcf_update_summary_height( true );
							}, 300);
						},
					});
				}else{
					setTimeout(function() {
						wcf_update_summary_height( true );
					}, 300);
				}
			});*/

			var image_slider_wrap = wcf_qv_modal.find('.wcf-qv-image-slider');

			if ( image_slider_wrap.find('li').length > 1 ) {
				image_slider_wrap.flexslider({
					animation: "slide",
					start: function( slider ){
						setTimeout(function() {
							wcf_update_summary_height( true );
						}, 300);
					},
				});
			}else{
				setTimeout(function() {
					wcf_update_summary_height( true );
				}, 300);
			}

			// stop loader
			$(document).trigger('wcf_quick_view_loader_stop');
		};

		var wcf_qv_close_modal = function() {

			// Close box by click overlay
			wcf_qv_wrapper.on( 'click', function(e){

				if ( this === e.target ) {
					wcf_qv_close();
				}
			});

			// Close box with esc key
			$(document).keyup(function(e){
				if( e.keyCode === 27 ) {
					wcf_qv_close();
				}
			});

			// Close box by click close button
			wcf_qv_close_btn.on( 'click', function(e) {
				e.preventDefault();
				wcf_qv_close();
			});

			var wcf_qv_close = function() {
				wcf_quick_view_bg.removeClass( 'wcf-quick-view-bg-ready' );
				wcf_qv_modal.removeClass('open').removeClass('loading');
				$('html').removeClass('wcf-quick-view-is-open');
				$('html').css( 'margin-right', '' );

				quick_view_btn.removeClass( 'wcf-variation-popup-open' );

				setTimeout(function () {
					wcf_qv_content.html('');
				}, 600);
			}
		};


		/*var	ast_qv_center_modal = function() {

			ast_qv_wrapper.css({
				'width'     : '',
				'height'    : ''
			});

			ast_qv_wrapper_w 	= ast_qv_wrapper.width(),
			ast_qv_wrapper_h 	= ast_qv_wrapper.height();

			var window_w = $(window).width(),
				window_h = $(window).height(),
				width    = ( ( window_w - 60 ) > ast_qv_wrapper_w ) ? ast_qv_wrapper_w : ( window_w - 60 ),
				height   = ( ( window_h - 120 ) > ast_qv_wrapper_h ) ? ast_qv_wrapper_h : ( window_h - 120 );

			ast_qv_wrapper.css({
				'left' : (( window_w/2 ) - ( width/2 )),
				'top' : (( window_h/2 ) - ( height/2 )),
				'width'     : width + 'px',
				'height'    : height + 'px'
			});
		};

		*/
		var wcf_update_summary_height = function( update_css ) {
			var quick_view = wcf_qv_content,
				img_height = quick_view.find( '.product .wcf-qv-image-slider' ).first().height(),
				summary    = quick_view.find('.product .summary.entry-summary'),
				content    = summary.css('content');

			if ( 'undefined' != typeof content && 544 == content.replace( /[^0-9]/g, '' ) && 0 != img_height && null !== img_height ) {
				summary.css('height', img_height );
			} else {
				summary.css('height', '' );
			}

			if ( true === update_css ) {
				wcf_qv_modal.css( 'opacity', 1 );
			}
		};

		var wcf_get_scrollbar_width = function () {

			var div = $('<div style="width:50px;height:50px;overflow:hidden;position:absolute;top:-200px;left:-200px;"><div style="height:100px;"></div>');
			// Append our div, do our calculation and then remove it
			$('body').append(div);
			var w1 = $('div', div).innerWidth();
			div.css('overflow-y', 'scroll');
			var w2 = $('div', div).innerWidth();
			$(div).remove();

			return (w1 - w2);
		}


		wcf_qv_close_modal();
		//wcf_update_summary_height();

		window.addEventListener("resize", function(event) {
			wcf_update_summary_height();
		});

		/* Add to cart ajax */
		/**
		 * wcf_add_to_cart_ajax class.
		 */
		var wcf_add_to_cart_ajax = function() {

			modal_wrap
				.off( 'click', '#wcf-quick-view-content .single_add_to_cart_button' )
				.off( 'wcf_added_to_cart' )
				.on( 'click', '#wcf-quick-view-content .single_add_to_cart_button', this.onAddToCart )
				.on( 'wcf_added_to_cart', this.updateButton );
		};

		/**
		 * Handle the add to cart event.
		 */
		wcf_add_to_cart_ajax.prototype.onAddToCart = function( e ) {

			e.preventDefault();
			
			var $form = $(this).closest('form');

			// If the form inputs are invalid
			if( ! $form[0].checkValidity() ) {
				$form[0].reportValidity();
				return false;
			}

			var $thisbutton 	= $( this ),
				product_id 		= $('input[name="product_id"]').val() || '',
				variation_id 	= $('input[name="variation_id"]').val() || '',
				choose_var 		= $('.wcf-variation-popup-open' ),
				quantity 		= choose_var.closest( '.wcf-qty-row').find('.wcf-qty-selection').val() || 1;

			if ( $thisbutton.is( '.single_add_to_cart_button' ) ) {

				$thisbutton.removeClass( 'added' );
				$thisbutton.addClass( 'loading' );

				// Ajax action.
				if ( variation_id != '') {
					jQuery.ajax ({
						url: cartflows.ajax_url,
						type:'POST',
						data:'action=wcf_add_cart_single_product&product_id=' + product_id + '&variation_id=' + variation_id + '&quantity=' + quantity,

						success:function(results) {

							result = results.data;
							if ( result && 'yes' === result.added_to_cart ) {

								/* Update Name in summary */
								choose_var = $('.wcf-variation-popup-open' );
								choose_var.closest( '.wcf-item').find('.wcf-item-wrap').text( result.name );
								
								/* Update Variaiton id in attributes */
								choose_var.data( 'variation', result.variation_id );

								var var_wrap		= choose_var.closest( '.wcf-qty-row'),
									qty_selection 	= var_wrap.find('.wcf-qty-selection'),
									qty_options 	= qty_selection.data( 'options' ),
									price_wrap 		= var_wrap.find('.wcf-price');

								qty_options.variation_id = result.variation_id;
								
								qty_selection.data( 'options', qty_options );

								/* Item selector */
								var var_selection 	= var_wrap.find('.wcf-item-selector');

								if ( var_selection.length > 0 ) {
									var var_input 	= var_selection.find('input'),
										var_options	= var_input.data( 'options' );
								
									var_options.variation_id = result.variation_id;
								
									var_input.data( 'options', var_options );
								}

								if ( 'no' != result.price ) {
									price_wrap.find( '.wcf-field-label').html( result.price );
								}
							}

							// Trigger event so themes can refresh other areas.
							$( document.body ).trigger( 'wc_fragment_refresh' );
							
							modal_wrap.trigger( 'wcf_added_to_cart', [ $thisbutton ] );
						}
					});
				} /*else {
					jQuery.ajax ({
						url: cartflows.ajax_url,
						type:'POST',
						data:'action=wcf_add_cart_single_product&product_id=' + product_id + '&quantity=' + quantity,

						success:function(results) {
							// Trigger event so themes can refresh other areas.
							$( document.body ).trigger( 'wc_fragment_refresh' );
							//modal_wrap.trigger( 'wcf_added_to_cart', [ $thisbutton ] );

							$( "body" ).trigger( "update_checkout" );

							wcf_qv_close_btn.trigger( 'click' );
						}
					});
				}*/
			}
		};

		/**
		 * Update cart page elements after add to cart events.
		 */
		wcf_add_to_cart_ajax.prototype.updateButton = function( e, button ) {
			
			$( "body" ).trigger( "update_checkout" );

			wcf_qv_close_btn.trigger( 'click' );

			return;

			button = typeof button === 'undefined' ? false : button;

			if ( $(button) ) {
				$(button).removeClass( 'loading' );
				$(button).addClass( 'added' );

				// View cart text.
				if ( ! cartflows.is_cart && $(button).parent().find( '.added_to_cart' ).length === 0  && cartflows.is_single_product) {
					$(button).after( ' <a href="' + cartflows.cart_url + '" class="added_to_cart wc-forward" title="' +
						cartflows.view_cart + '">' + cartflows.view_cart + '</a>' );
				}


			}
		};

		/**
		 * Init wcf_add_to_cart_ajax.
		 */
		new wcf_add_to_cart_ajax();
	}

	var wcf_two_step_validations = function () {
		
		var $billing_inputs, $billing_select, $shipping_inputs, $shipping_select = [];

			$billing_inputs = $('.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields').find('input[type="text"], input[type="tel"], input[type="email"], input[type="password"]');

			$billing_select = $('.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-billing-fields').find('.select2');

			$shipping_inputs = $('.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields').find('input[type="text"], input[type="tel"], input[type="email"], input[type="password"]');

			$shipping_select = $('.wcf-embed-checkout-form-two-step form.woocommerce-checkout .woocommerce-shipping-fields').find('.select2');
		
		var is_ship_to_diff = $('.wcf-embed-checkout-form-two-step form.woocommerce-checkout').find('h3#ship-to-different-address input[type="checkbox"]:checked').val();

		
		//Add focus class on clicked on input types
		var access		= 'true',
			field_focus = '';
			
		Array.from($billing_inputs).forEach(function ($this) {
		    var	type 		= $this.type,
		    	name 		= $this.name,
		    	field_row   = $this.closest('.form-row');
		    	has_class   = field_row.classList.contains('validate-required');
		    			    	
		    	field_value = $.trim($this.value);
				// whiteSpace  = /\s/g.test(field_value);

			    if ( has_class && '' == field_value )  
			    {
			    	$this.classList.add('field-required');
		    		access = 'false';
		    		if( '' == field_focus ){

		    			field_focus = $this;
		    		}
			    }else{

			    	if( 'email' == type && false == /^([a-zA-Z0-9_\+\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,14})$/.test(field_value) ){
			    		$this.classList.add('field-required');
			    		access = 'false';
			    		
			    		if( '' == field_focus ){

			    			field_focus = $this;
			    		}
			    	}

			    	$this.classList.remove('field-required');
			    }
		});

		Array.from($billing_select).forEach(function ($this) {
		    var field_row   = $this.closest('.form-row'),
			    has_class   = field_row.classList.contains('validate-required'),
		    	field_value = $.trim(field_row.querySelector('.select2-selection__rendered[title]'));
		    	name		= field_row.querySelector('select').name;

		    	

		    	if ( has_class && '' == field_value )  
			    {
		    		$this.classList.add('field-required');
		    		access = 'false';
		    		if( '' == field_focus ){

		    			field_focus = $this;
		    		}
			    }else{
			    	$this.classList.remove('field-required');
			    }
		});

		if( 1 == is_ship_to_diff ){ 
			Array.from($shipping_inputs).forEach(function ($this) {
			    var	type 		= $this.type,
			    	name 		= $this.name,
			    	field_row   = $this.closest('.form-row');
			    	has_class   = field_row.classList.contains('validate-required');
			    	
			    				    	
			    	field_value = $.trim($this.value);

				    if ( has_class && '' == field_value )  
				    {
				    	$this.classList.add('field-required');
			    		access = 'false';

			    		if( '' == field_focus ){

			    			field_focus = $this;
			    		}

				    }else{

				    	if( 'email' == type && false == /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(field_value) ){
				    		$this.classList.add('field-required');
				    		access = 'false';
				    		
				    		if( '' == field_focus ){

				    			field_focus = $this;
				    		}
				    	}

				    	$this.classList.remove('field-required');
				    }
			});

			Array.from($shipping_select).forEach(function ($this) {
			    var field_row   = $this.closest('.form-row'),
				    has_class   = field_row.classList.contains('validate-required'),
			    	field_value = $.trim( field_row.querySelector('.select2-selection__rendered[title]'));
			    	name		= field_row.querySelector('select').name;

			    	whiteSpace  = /\s/g.test(field_value);

			    	
			    	if ( has_class && '' == field_value )  
				    {
			    		$this.classList.add('field-required');
			    		access = 'false';
			    		
			    		if( '' == field_focus ){

			    			field_focus = $this;
			    		}

				    }else{
				    	$this.classList.remove('field-required');
				    }
			});
		}

		// Focus the errored field
		if( '' != field_focus ){
			field_focus.focus();
		}


		return access;
		
	}

	$(document).ready(function($) {

		wcf_toggle_optimized_fields();

		if( cartflows_animate_tab_fields.enabled ) {
			wcf_animate_browser_tab();
		}

		if( 'yes' === cartflows.allow_autocomplete_zipcode ){
			wcf_autocomplete_zip_data();
		}

		wcf_product_quantity_var_options();
		wcf_order_bump_ajax();
		wcf_anim_field_label_event();
		wcf_anim_field_label();
		if ( $(".wcf-embed-checkout-form-two-step").length > 0 ) {
			wcf_nav_tab_hide_show_events();
		}
	});
})(jQuery);