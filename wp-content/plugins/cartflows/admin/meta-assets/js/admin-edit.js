(function($){

	CartFlowsAdminEdit = {

		/**
		 * Init
		 */
		init: function()
		{
			this._bind();
			this._set_font_weigths();
		},
		
		/**
		 * Binds events
		 */
		_bind: function()
		{
			$( document ).on('click', '.wcf-cpf-add', CartFlowsAdminEdit._add_custom_field );
			$( document ).on('click', '.wcf-pro-cpf-add', CartFlowsAdminEdit._add_pro_custom_field );

			$( document ).on('click', '.wcf-cpf-action-remove', CartFlowsAdminEdit._remove_custom_field );
			
			$( document ).on('change', '.wcf-field-font-family', CartFlowsAdminEdit._set_font_weight_select_options );
			$( document ).on('change', '.wcf-field-font-weight', CartFlowsAdminEdit._set_font_weight_val );
		},

		_set_font_weight_val: function(event) {
			event.preventDefault();
			$(this).attr( 'data-selected', $(this).val() );

			CartFlowsAdminEdit._set_google_url();
		},

		_set_font_weigths: function() {

			if ( 'function' !== typeof $('.wcf-field-font-family').select2 ){
				return;
			}

			$('.wcf-field-font-family').select2();

			var google_url = '';
			var google_font_families = {};

			$('.wcf-field-font-family').each(function(index, el) {
				var font_family = $(el).val();
				var id    = $(el).data('for');
				
				var temp = font_family.match("'(.*)'");

				if( temp && temp[1] ) {
					font_family = temp[1];
				}

				var new_font_weights = {};
				if( wcf.google_fonts[ font_family ] ) {

					var variants = wcf.google_fonts[ font_family ][0];

					$.each( variants, function(index, weight) {
						if( ! weight.includes( 'italic' ) ) {
							new_font_weights[ weight ] = wcf.font_weights[ weight ];
						}
					});
		
					var weight = $( '.wcf-field-font-weight[data-for="'+id+'"]' );
					if( weight.length ) {

						weight.empty(); // remove old options
						var current_selected = weight.attr('data-selected');
						var selected = "";

						$.each(new_font_weights, function(key,value) {

							if( key == current_selected ) {
								var selected = "selected='selected'";
							}

							weight.append($("<option "+selected+"></option>").attr("value", key).text(value));
						});
					}

					temp_font_family = font_family.replace(' ', '+');
					google_font_families[ temp_font_family ] = new_font_weights;

				} else if( wcf.system_fonts[ font_family ] ) {

					var variants = wcf.system_fonts[ font_family ]['variants'];

					$.each( variants, function(index, weight) {
						if( ! weight.includes( 'italic' ) ) {
							new_font_weights[ weight ] = wcf.font_weights[ weight ];
						}
					});

					var weight = $( '.wcf-field-font-weight[data-for="'+id+'"]' );

					if( weight.length ) {
						var current_selected = weight.attr('data-selected');

						weight.empty(); // remove old options
						var selected = "";
						$.each(new_font_weights, function(key,value) {

							if( key == current_selected ) {
								var selected = "selected='selected'";
							} else {
							}
							weight.append($("<option "+selected+"></option>").attr("value", key).text(value));
						});
					}
				}
			});

			CartFlowsAdminEdit._set_google_url();
		},

		_set_google_url: function() {
			var google_url = '';
			$('.wcf-field-font-family').each(function(index, el) {

				var font_family = $(el).val();
				var id    = $(el).data('for');

				var temp = font_family.match("'(.*)'");

				if( temp && temp[1] ) {
					font_family = temp[1];
				}

				if( ( 'inherit' != font_family ) && ( 'Helvetica' !== font_family ) && ( 'Verdana' !== font_family ) && ( 'Arial' !== font_family ) && ( 'Times' !== font_family ) && ( 'Georgia' !== font_family ) && ( 'Courier' !== font_family ) ) {
					font_family = font_family.replace(' ', '+');

					var weight      = $( '.wcf-field-font-weight[data-for="'+id+'"]' );
					var font_weight = weight.val();
					
					if( typeof  font_weight == 'undefined' && id == 'wcf-base' ){
						font_weight = '';
					}
					var bar   = '',
						colon = '';

					if( google_url ) {
						if( font_weight != ''){
							bar   = '|';
							colon = ':';
						}
						google_url = google_url + bar + font_family + colon + font_weight;
					} else {
						google_url = font_family;
					}
				}

			});

			$('#wcf-field-google-font-url').val( '//fonts.googleapis.com/css?family=' + google_url );
		},

		_set_font_weight_select_options: function(event) {
			event.preventDefault();

			CartFlowsAdminEdit._set_font_weigths();
		},

		_remove_custom_field: function(e) {

			e.preventDefault();

			var $this = $(this),
				wrap    = $this.closest('.wcf-cpf-actions'),
				post_id = $('#post_ID').val(),
				type    = wrap.data('type'),
				key     = wrap.data('key'),
				row     = $this.parents('.wcf-field-item-edit-active');

				if( row.length < 1 ){
					row     = $this.parents('.wcf-field-row');
				}

			var delete_status = confirm( "This action will delete this field. Are you sure?" );
			if (true == delete_status) {
				$this.addClass('wp-ui-text-notification').text('Removing..');

				$.ajax({
		            url: wcf.ajax_url,
					data: {
						action: "wcf_delete_checkout_custom_field",
						post_id: post_id,
						type : type,
						key : key,
						security: cartflows_admin.wcf_delete_checkout_custom_field_nonce
					},
					dataType: 'json',
					type: 'POST',
					success: function ( data ) {
						row.slideUp(400, 'swing', function() {
							row.remove();
						});
					}
				});
			}			
		},

		_add_custom_field: function(e) {

			e.preventDefault();

			var $this 	= $(this),
				wrap    = $this.closest('.wcf-cpf-wrap'),
				post_id = $('#post_ID').val(),
				add_to  = wrap.find( '.wcf-cpf-fields.wcf-cpf-add_to select' ).val() || '',
				type    = wrap.find( '.wcf-cpf-fields.wcf-cpf-type select' ).val() || '',
				options = wrap.find( '.wcf-cpf-fields.wcf-cpf-options textarea' ).val() || '',
				label   = wrap.find( '.wcf-cpf-fields.wcf-cpf-label input' ).val() || '',
				name    = wrap.find( '.wcf-cpf-fields.wcf-cpf-name input' ).val() || '';

			if( '' === label ) {
				wrap.find( '.wcf-cpf-fields.wcf-cpf-label input' ).focus();
				return;
			}

			if( '' === name ) {
				wrap.find( '.wcf-cpf-fields.wcf-cpf-name input' ).focus();
				return;
			}

			$this.addClass('updating-message').text('Adding Field..');

			$.ajax({
	            url: wcf.ajax_url,
				data: {
					action  : "wcf_add_checkout_custom_field",
					post_id : post_id,
					add_to  : add_to,
					type    : type,
					options : options,
					label   : label,
					name    : name,
					security: cartflows_admin.wcf_add_checkout_custom_field_nonce
				},
				dataType: 'json',
				type: 'POST',
				success: function ( data ) {

					if( $('.' + data.add_to_class ).length ) {
						$('.' + data.add_to_class ).append( data.markup );

						var new_row = $('.wcf-field-row.field-'+data.field_args.name );

						$this.removeClass('updating-message').text('Added Field!');
						setTimeout(function() {
							$this.text('Add New Field');
						}, 1500);


						if( $('.wcf-field-row.field-'+data.field_args.name ).length ) {
							$('html, body').animate({
						        scrollTop: $('.wcf-field-row.field-'+data.field_args.name ).offset().top
						    }, 800);
						}
					}
				}
			});
			
		},

		_add_pro_custom_field: function(e) {

			e.preventDefault();

			var $this 			= $(this),
				wrap    		= $this.closest('.wcf-cpf-wrap'),
				post_id 		= $('#post_ID').val(),
				add_to  		= wrap.find( '.wcf-cpf-fields.wcf-cpf-add_to select' ).val() || '',
				type    		= wrap.find( '.wcf-cpf-fields.wcf-cpf-type select' ).val() || '',
				options 		= wrap.find( '.wcf-cpf-fields.wcf-cpf-options textarea' ).val() || '',
				label   		= wrap.find( '.wcf-cpf-fields.wcf-cpf-label input' ).val() || '',
				width 			= wrap.find( '.wcf-cpf-fields.wcf-cpf-width select' ).val() || '',
				placeholder 	= '',
				is_required 	= wrap.find( '.wcf-cpf-fields.wcf-cpf-required input[type="checkbox"]:checked' ).val() || '';
				default_value 	= '';
				// name    	= wrap.find( '.wcf-cpf-fields.wcf-cpf-name input' ).val() || '';

			if( 'checkbox' == type ){
				default_value = wrap.find( '.wcf-cpf-fields.wcf-cpf-default select' ).val() || '';
			}else{
				default_value = wrap.find( '.wcf-cpf-fields.wcf-cpf-default input' ).val() || '';
			}

			if( 'select' != type ){
				placeholder = wrap.find( '.wcf-cpf-fields.wcf-cpf-placeholder input' ).val() || '';
			}
			
			if( '' === label ) {
				wrap.find( '.wcf-cpf-fields.wcf-cpf-label input' ).focus();
				wrap.find( '.wcf-cpf-fields.wcf-cpf-label input' ).addClass('error');
				wrap.find( '.wcf-cpf-fields.wcf-cpf-label #wcf-cpf-label-error-msg' ).text( ' This field is required' ).css( 'color', 'red' );
				return;
			}

			// if( '' === name ) {
			// 	wrap.find( '.wcf-cpf-fields.wcf-cpf-name input' ).focus();
			// 	return;
			// }

			$this.addClass('updating-message').text('Adding Field..');

			$.ajax({
	            url: wcf.ajax_url,
				data: {
					action  : "wcf_pro_add_checkout_custom_field",
					post_id : post_id,
					add_to  : add_to,
					type    : type,
					options : options,
					label   : label,
					// name    : name,
					placeholder    : placeholder,
					width : width,
					default:default_value,
					required: is_required,
					security: cartflows_admin.wcf_pro_add_checkout_custom_field_nonce
				},
				dataType: 'json',
				type: 'POST',
				success: function ( data ) {
					console.log(data);
					if( $('.' + data.add_to_class ).length ) {
						$('.' + data.add_to_class ).append( data.markup );

						var new_row = $('.wcf-field-row.field-'+data.field_args.name );

						$this.removeClass('updating-message').text('Added Field!');
						setTimeout(function() {
							$this.text('Add New Field');
						}, 1500);


						if( $('.wcf-field-row.field-'+data.field_args.name ).length ) {
							$('html, body').animate({
						        scrollTop: $('.wcf-field-row.field-'+data.field_args.name ).offset().top
						    }, 800);
						}
					}
				}
			});
			
		},
	};

	/**
	 * Initialization
	 */
	$(function(){
		CartFlowsAdminEdit.init();
	});

})(jQuery);

(function($){

	if ( typeof getEnhancedSelectFormatString == "undefined" ) {
		function getEnhancedSelectFormatString() {
			var formatString = {
				noResults: function() {
					return wc_enhanced_select_params.i18n_no_matches;
				},
				errorLoading: function() {
					return wc_enhanced_select_params.i18n_ajax_error;
				},
				inputTooShort: function( args ) {
					var remainingChars = args.minimum - args.input.length;

					if ( 1 === remainingChars ) {
						return wc_enhanced_select_params.i18n_input_too_short_1;
					}

					return wc_enhanced_select_params.i18n_input_too_short_n.replace( '%qty%', remainingChars );
				},
				inputTooLong: function( args ) {
					var overChars = args.input.length - args.maximum;

					if ( 1 === overChars ) {
						return wc_enhanced_select_params.i18n_input_too_long_1;
					}

					return wc_enhanced_select_params.i18n_input_too_long_n.replace( '%qty%', overChars );
				},
				maximumSelected: function( args ) {
					if ( args.maximum === 1 ) {
						return wc_enhanced_select_params.i18n_selection_too_long_1;
					}

					return wc_enhanced_select_params.i18n_selection_too_long_n.replace( '%qty%', args.maximum );
				},
				loadingMore: function() {
					return wc_enhanced_select_params.i18n_load_more;
				},
				searching: function() {
					return wc_enhanced_select_params.i18n_searching;
				}
			};

			var language = { 'language' : formatString };

			return language;
		}
	}
	
	var wcf_init_color_fields = function() {
		
		// Call color picker
    	$('.wcf-color-picker').wpColorPicker();
	}
	var wcf_woo_product_search_init = function() {
		
		var $product_search = $('.wcf-product-search:not(.wc-product-search)');

		if( $product_search.length > 0 ) {
			
			$product_search.addClass('wc-product-search');

			$(document.body).trigger('wc-enhanced-select-init');
		}
	}

	var wcf_woo_coupon_search_init = function() {

		$( ':input.wc-coupon-search' ).filter( ':not(.enhanced)' ).each( function() {
			var select2_args = {
				allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
				placeholder: $( this ).data( 'placeholder' ),
				minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         wc_enhanced_select_params.ajax_url,
					dataType:    'json',
					quietMillis: 250,
					data: function( params, page ) {
						return {
							term:     params.term,
							action:   $( this ).data( 'action' ) || 'wcf_json_search_coupons',
							security: cartflows_admin.wcf_json_search_coupons_nonce
						};
					},
					processResults: function( data, page ) {
						var terms = [];
						if ( data ) {
							$.each( data, function( id, text ) {
								terms.push( { id: id, text: text } );
							});
						}
						return { results: terms };
					},
					cache: true
				}
			};

			select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

			$( this ).select2( select2_args ).addClass( 'enhanced' );
		});
	};
	var wcf_pages_search_init = function() {


		$( 'select.wcf-search-pages' ).each( function() {
			
			var select2_args = {
				allowClear:  $( this ).data( 'allow_clear' ) ? true : false,
				placeholder: $( this ).data( 'placeholder' ) ? $( this ).data( 'placeholder' ): '',
				minimumInputLength: $( this ).data( 'minimum_input_length' ) ? $( this ).data( 'minimum_input_length' ) : '3',
				escapeMarkup: function( m ) {
					return m;
				},
				ajax: {
					url:         wc_enhanced_select_params.ajax_url,
					dataType:    'json',
					quietMillis: 250,
					data: function( params, page ) {
						return {
							term:     params.term,
							action:   $( this ).data( 'action' ) || 'wcf_json_search_pages',
							security: cartflows_admin.wcf_json_search_pages_nonce
						};
					},
					processResults: function( data, page ) {
						
						return { results: data };
					},
					cache: true
				}
			};

			select2_args = $.extend( select2_args, getEnhancedSelectFormatString() );

			$( this ).select2( select2_args ).addClass( 'enhanced' );
		});
	};

	var wcf_add_repeatable_product = function() {
		
		$('.wcf-add-repeatable').on('click', function(e) {

			var $this = $(this),
				field_name = $this.data('name'),
				wrap = $this.closest('.wcf-repeatables-wrap'),
				template = $('#tmpl-wcf-product-repeater').html(),
				highest = 0,
				new_key = 0;

			wrap.find('.wcf-repeatable-row').each(function(er) {
				var r_row 	= $(this),
					key 	= r_row.data('key');
				
				if ( key > highest ) {
					highest = key;
				}
			});

			new_key = highest + 1;

			template = template.replace( /{{id}}/g, new_key );
			

			$( template ).insertBefore( ".wcf-add-repeatable-row" );

			/* Woo Product Search */
			wcf_woo_product_search_init();
			
			e.preventDefault();
		});
		
	}

	var wcf_remove_repeatable_product = function() {
		$(document).on( 'click', '.wcf-repeatable-remove', function(e) {
			
			var $this = $(this),
				deletable_row = $this.closest('.wcf-repeatable-row'),
				wrap = $this.closest('.wcf-repeatables-wrap');

			var all_rows = wrap.find('.wcf-repeatable-row');

			if ( all_rows.length === 1 ) {
				alert("You cannot remove this product.");
			}else{
				deletable_row.remove();
			}
		} );
	}

	/* Simple Quantity */
	var wcf_set_variation_mode_option = function() {
		
		$('.wcf-variation-mode select').each(function(e) {
			var $this 			= $(this),
				variation_mode 	= $this.val(),
				wrap 			= $this.closest('.wcf-repeatable-row-standard-fields'),
				quantity_data 	= wrap.find('.wcf-quantity-data');

			if ( 'simple-quantity' === variation_mode ) {
				quantity_data.show();
			}else{
				quantity_data.hide();
			}
		});

		$(document).on( 'change', '.wcf-variation-mode select', function(e) {
			var $this 			= $(this),
				variation_mode 	= $this.val(),
				wrap 			= $this.closest('.wcf-repeatable-row-standard-fields'),
				quantity_data 	= wrap.find('.wcf-quantity-data');

			if ( 'simple-quantity' === variation_mode ) {
				quantity_data.show();
			}else{
				quantity_data.hide();
			}
		});
	}

	var wcf_add_quantity_data = function() {
		
		$(document).on('click', '.wcf-quantity-add-option', function(e) {

			e.preventDefault();

			var $this = $(this),
				wrap = $this.closest('.wcf-quantity-data'),
				template = $('#tmpl-wcf-product-simple-quantity').html(),
				highest = 0,
				new_key = 0,
				main_wrap = $this.closest('.wcf-repeatable-row'),
				main_key = main_wrap.data('key');

			wrap.find('.wcf-quantity-repeatable-row').each(function(er) {
				var r_row 	= $(this),
					key 	= r_row.data('key');
				
				if ( key > highest ) {
					highest = key;
				}
			});

			new_key = highest + 1;

			template = template.replace( /{{id}}/g, main_key );
			template = template.replace( /{{data_id}}/g, new_key );
			
			$( template ).insertAfter( wrap.find('.wcf-quantity-repeatable-row').last() );
		});
	}

	var wcf_remove_quantity_data = function() {
		
		$(document).on( 'click', '.wcf-quanity-remove', function(e) {
			
			var $this = $(this),
				deletable_row = $this.closest('.wcf-quantity-repeatable-row'),
				wrap = $this.closest('.wcf-quantity-data');

			var all_rows = wrap.find('.wcf-quantity-repeatable-row');

			if ( all_rows.length === 1 ) {
				alert("You cannot remove this product.");
			}else{
				deletable_row.remove();
			}
		} );
	}

	/* Custom Fields Hide / Show */
	var wcf_custom_fields_events = function() {

		/* Ready */
		wcf_custom_fields();

		/* Change Custom Field*/
		$('.wcf-column-right .wcf-checkout-custom-fields .wcf-cc-fields .wcf-cc-checkbox-field input:checkbox').on('change', function(e) {
			wcf_custom_fields();
		});
	}

	/* Disable/Enable Custom Field section*/
	var wcf_custom_fields = function() {

		var wrap 			= $('.wcf-checkout-table'),
			custom_fields 	= wrap.find('.wcf-column-right .wcf-checkout-custom-fields .wcf-cc-fields .wcf-cc-checkbox-field .field-wcf-custom-checkout-fields input:checkbox');

		var field_names = [
			'.wcf-custom-field-box',
			'.wcf-cb-fields',
			'.wcf-sb-fields',
		];

		if ( custom_fields.is(":checked") ) {
			$.each( field_names, function(i, val) {
				wrap.find( val ).show();
			})
		} else {
			$.each( field_names, function(i, val) {
				wrap.find( val ).hide();
			});
		}
	}

	/* Advance Style Fields Hide / Show */
	var wcf_advance_style_fields_events = function() {

		/* Ready */
		wcf_advance_style_fields();
		wcf_thankyou_advance_style_fields();

		/* Change Advance Style Field*/
		$('.wcf-column-right .wcf-checkout-style .wcf-cs-fields .wcf-cs-checkbox-field input:checkbox').on('change', function(e) {
			wcf_advance_style_fields();
		});

		/* Change Advance Style Field*/
		$('.wcf-thankyou-table [name="wcf-tq-advance-options-fields"]').on('change', function(e) {
			wcf_thankyou_advance_style_fields();
		});
	}

	var wcf_thankyou_advance_style_fields = function() {
		var wrap 			= $('.wcf-thankyou-table'),
			checkbox_field  = $('.wcf-thankyou-table [name="wcf-tq-advance-options-fields"]');

		var field_names = [
			'.field-wcf-tq-container-width',
			'.field-wcf-tq-section-bg-color'
		];

		if ( checkbox_field.is(":checked") ) {
			$.each( field_names, function(i, val) {
				wrap.find( val ).show();
			})
		} else {
			$.each( field_names, function(i, val) {
				wrap.find( val ).hide();
			});
		}
	}
	
	/* Disable/Enable Advance Style Field section*/
	var wcf_advance_style_fields = function() {

		var wrap 			= $('.wcf-checkout-table'),
			custom_fields 	= wrap.find('.wcf-column-right .wcf-checkout-style .wcf-cs-fields .wcf-cs-checkbox-field input:checkbox');

		var field_names = [
			'.wcf-cs-fields-options',
			'.wcf-cs-button-options',
			'.wcf-cs-section-options',
		];

		// console.log(custom_fields);

		if ( custom_fields.is(":checked") ) {
			$.each( field_names, function(i, val) {
				wrap.find( val ).show();
			})
		} else {
			$.each( field_names, function(i, val) {
				wrap.find( val ).hide();
			});
		}
	}

	var wcf_settings_tab = function() {

		if( $('.wcf-tab.active').length ) {
			$active_tab = $('.wcf-tab.active');

			$active_tab_markup = '.' + $active_tab.data('tab');

			if( $( $active_tab_markup ).length ) {
				$( $active_tab_markup ).siblings().removeClass('active');
				$( $active_tab_markup ).addClass('active');
			}
		}

		$('.wcf-tab').on('click', function(e) {
			e.preventDefault();

			$this 		= $(this),
			tab_class 	= $this.data('tab');

			$('#wcf-active-tab').val( tab_class );

			$this.siblings().removeClass('wp-ui-text-highlight active');
			$this.addClass('wp-ui-text-highlight active');
			
			if( $( '.' + tab_class ).length ) {
				$( '.' + tab_class ).siblings().removeClass('active');
				$( '.' + tab_class ).addClass('active');
			}
		});
	}

	var wcf_input_file_init = function() {

		var file_frame;
		window.inputWrapper = '';

		$( document.body ).on('click', '.wcf-select-image', function(e) {

			e.preventDefault();

			var button = $(this);
			window.inputWrapper = $(this).closest('.wcf-field-row');
			if ( file_frame ) {
				file_frame.open();
				return;
			}

			// Create the media frame.
			file_frame = wp.media( {
				multiple: false
			} );

			// When an image is selected, run a callback.
			file_frame.on( 'select', function() {

				var attachment = file_frame.state().get( 'selection' ).first().toJSON();
				// place first attachment in field
				window.inputWrapper.find( '#wcf-image-preview' ).show();
				
				window.inputWrapper.find( '#wcf-image-preview' ).children('.saved-image').remove();

				window.inputWrapper.find( '#wcf-image-preview' ).append('<img src="' + attachment.url + '" width="150" class="saved-image" style="margin-bottom:12px;" />');

				window.inputWrapper.find( '#wcf-image-value' ).val( attachment.url );

				// window.inputWrapper.find( '.wcf-image-id' ).val( attachment.id );

				// window.inputWrapper.find( '.wcf-image-value' ).val( attachment.url );

				$('.wcf-remove-image').show();
			});

			// Finally, open the modal
			file_frame.open();
		});

		$( '.wcf-remove-image' ).on( 'click', function( e ) {
			e.preventDefault();

			var button   = $(this),
			    closeRow = $(this).closest('.wcf-field-row');

			    closeRow.find( '#wcf-image-preview img' ).hide();
			    closeRow.find( '.wcf-image-id' ).val('');
			    closeRow.find( '.wcf-image' ).val('');
			    button.hide();
			
		});
	}

	$(document).ready(function($) {

		wcf_settings_tab();

		wcf_init_color_fields();

		/* Woo Product Search */
		wcf_woo_product_search_init();

		/* Woo Coupon Search */
		wcf_woo_coupon_search_init();

		/* Pages Search */
		wcf_pages_search_init();

		/* Select Image Field */
		wcf_input_file_init();

		/* Set Variation Mode Data */
		wcf_set_variation_mode_option();

		/* Repeateble Product */
		wcf_add_repeatable_product();

		/* Custom Fields Show Hide */
		wcf_custom_fields_events();
		
		/* Remove Repeatable Product */
		wcf_remove_repeatable_product();
		
		/* Advance Style Fields Show Hide */
		wcf_advance_style_fields_events();

		/* Quantity Data */
		wcf_add_quantity_data();
		wcf_remove_quantity_data();
	});


})(jQuery);
