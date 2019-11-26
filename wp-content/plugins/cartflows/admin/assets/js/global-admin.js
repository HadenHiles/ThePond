( function( $ ) {

	/**
	 * Add Shortcode next to metabox heading.
	 *
	 * @since 1.0.0
	 */


	/* Show Hide Custom Options for input types */
	var wcf_show_field_custom_options = function() {
		var default_value = $('.field-wcf-input-field-size select').val();

		if( 'custom' == default_value ){
			$('.field-wcf-field-tb-padding').show();
			$('.field-wcf-field-lr-padding').show();
		}else{
			$('.field-wcf-field-tb-padding .wcf-field-row-content input[type="number"]').val('');
			$('.field-wcf-field-lr-padding .wcf-field-row-content input[type="number"]').val('');

			$('.field-wcf-field-tb-padding').hide();
			$('.field-wcf-field-lr-padding').hide();
		}

		$('.field-wcf-input-field-size select').on('change', function(e) {

			e.preventDefault();

			var $this 	= $(this),
				selected_value = $this.val();
			
			if(selected_value == 'custom' ){
				$('.field-wcf-field-tb-padding').show();
				$('.field-wcf-field-lr-padding').show();
			}else{
				$('.field-wcf-field-tb-padding').hide();
				$('.field-wcf-field-lr-padding').hide();
			}
		});
	}

	/* Show Hide Custom Options for Buttons */
	var wcf_show_button_custom_options = function() {
		var default_value = $('.field-wcf-input-button-size select').val();

		if( 'custom' == default_value ){
			$('.field-wcf-submit-tb-padding').show();
			$('.field-wcf-submit-lr-padding').show();
		}else{
			$('.field-wcf-submit-tb-padding .wcf-field-row-content input[type="number"]').val('');
			$('.field-wcf-submit-lr-padding .wcf-field-row-content input[type="number"]').val('');
			
			$('.field-wcf-submit-tb-padding').hide();
			$('.field-wcf-submit-lr-padding').hide();
		}

		
		
		$('.field-wcf-input-button-size select').on('change', function(e) {

			e.preventDefault();

			var $this 	= $(this),
				selected_value = $this.val();
			
			if(selected_value == 'custom' ){
				$('.field-wcf-submit-tb-padding').show();
				$('.field-wcf-submit-lr-padding').show();
			}else{
				$('.field-wcf-submit-tb-padding').hide();
				$('.field-wcf-submit-lr-padding').hide();
			}
		});
	}

	function wcf_prevent_toggle_for_shortcode() {
		// Prevent inputs in meta box headings opening/closing contents.
		$( '#wcf-checkout-settings' ).find( '.hndle' ).unbind( 'click.postboxes' );

		$( '#wcf-checkout-settings' ).on( 'click', '.hndle', function( event ) {

			// If the user clicks on some form input inside the h3 the box should not be toggled.
			if ( $( event.target ).filter( 'input, option, label, select' ).length ) {
				return;
			}

			$( '#wcf-checkout-settings' ).toggleClass( 'closed' );
		});
	}

	function add_tool_tip_msg(){
		var tooltip = false;

		$('.wcf-field-heading-help').click(function(){
			var tip_wrap = $(this).closest('.wcf-field-row');
	        	closest_tooltip = tip_wrap.find('.wcf-tooltip-text');
	        	
	        	closest_tooltip.toggleClass('display_tool_tip');
	    });

	}


	// Check for the highlight area and add the class.
	function highlight_the_metabox(){

		if( ( 'undefined' !== typeof cartflows_admin ) && ( cartflows_admin.wcf_edit_test_mode ) ){

			$('#wcf-sandbox-settings').addClass("wcf-highlight");

			// Remove the class automatically after 6 seconds.
			setTimeout(function(){
				deactivateHighlight()
			}, 6000);

			// Click outside the higlight element and remove the class
			$(document).on('click', function (e) {
				deactivateHighlight();
			});
		}
	}

	// Function to remove the highlighted class
	function deactivateHighlight() {
		$('#wcf-sandbox-settings').removeClass('wcf-highlight');
	}

	function wcf_toggle_post_update() {

		if ( 'undefined' === typeof cartflows_woo ) {
			return;
		}

		if( ! cartflows_woo.show_update_post ) {
			$("#submitdiv").hide();
		}
	}

	$( document ).ready(function() {
		//alert("Before Stattement");
		$( '#field-wcf-shortcode' ).appendTo( '#wcf-checkout-settings .hndle span' );
		$( '#field-wcf-shortcode' ).css( "display", "inline" );
		//alert("After Stattement");

		wcf_show_field_custom_options();

		wcf_show_button_custom_options();

		wcf_prevent_toggle_for_shortcode();

		add_tool_tip_msg();

		highlight_the_metabox();

		wcf_toggle_post_update();
		
	});

} )( jQuery ); 
