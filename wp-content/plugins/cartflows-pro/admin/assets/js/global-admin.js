( function( $ ) {

	var _show_image_field_bump_offer = function(){

		$('.field-wcf-order-bump-style select').on('change', function(e) {

			e.preventDefault();

			var $this 	= $(this),
				selected_value = $this.val();
			
			
			$('.field-wcf-order-bump-image').removeClass("hide");
		});
	}

	var _show_image_field_bump_offer_event = function(){

		var get_wrap  	  = $('.wcf-product-order-bump');
			get_field_row = get_wrap.find('.field-wcf-order-bump-style'),
			get_field     = get_field_row.find('select'),
			get_value     = get_field.val();

		var get_img_field = get_wrap.find('.field-wcf-order-bump-image');

			console.log(get_img_field);

			$('.field-wcf-order-bump-image').removeClass("hide");
	}



	var wcf_step_delete_sortable =  function () {
		$('.wcf-flow-steps-container').on('wcf-step-deleted', function(e,step_id) {

			$('.wcf-conditional-badge[data-no-step="'+ step_id +'"]').attr('data-no-step',"");
			$('.wcf-conditional-badge[data-yes-step="'+ step_id +'"]').attr('data-yes-step',"");

			wcf_step_sorting();
		});

	}


	/* Create default steps */
	var wcf_sort_flow = function() {		
		$( '.wcf-flow-settings .wcf-flow-steps-container' ).on( "sortupdate", function( event, ui ) {

			var target = ui.item;
			var step_type = $(target).data('term-slug');

			wcf_step_sorting();
		});
	}

	var wcf_step_sorting = function(){
		
		var upsell_downsell_steps = $('.wcf-step-wrap[data-term-slug="upsell"], .wcf-step-wrap[data-term-slug="downsell"] ');

		$.each( upsell_downsell_steps , function() {

			var $this = $(this);

			var next_step_element = $this.next();

			if( $this.data('term-slug') === "upsell"){

				var next_yes_upsell = $this.next();

				var next_upsell_found = false;

				var yes_step_badge_element = $this.find('.wcf-yes-next-badge');

				var render_yes_step = yes_step_badge_element.data('yes-step');

				if(  render_yes_step === "" || render_yes_step === undefined){

					while( !next_upsell_found ){

						if( next_yes_upsell.data('term-slug') === "downsell" ){
								next_yes_upsell = next_yes_upsell.next();

						}else{

							var next_yes_upsell_step_name = next_yes_upsell.find('.wcf-step-left-content').children().eq(1).text();

							yes_step_badge_element.remove();

							if( next_yes_upsell.length !== 0){

								var yes_label = cartflows_admin.add_yes_label + next_yes_upsell_step_name;
								
							}else{
								var yes_label = cartflows_admin.add_yes_label + cartflows_admin.not_found_label;
							}
							
							var yes_step_html = '<span class="wcf-flow-badge wcf-conditional-badge wcf-yes-next-badge">'+yes_label+'</span>';

							$this.find('.wcf-badges').prepend(yes_step_html);

							next_upsell_found = true;
						}

					}
				}


			}else{

				var render_yes_step = $this.find('.wcf-yes-next-badge').data('yes-step');
		
				if(  render_yes_step === "" || render_yes_step === undefined){

					var next_step_name = next_step_element.find('.wcf-step-left-content').children().eq(1).text();

					$this.find('.wcf-yes-next-badge').remove();

					if( next_step_element.length !== 0 ){
						var yes_label = cartflows_admin.add_yes_label + next_step_name;

					}else{

						var yes_label = cartflows_admin.add_yes_label + cartflows_admin.not_found_label;
					}

					var yes_step_html = '<span class="wcf-flow-badge wcf-conditional-badge wcf-yes-next-badge">'+ yes_label +'</span>';
					$this.find('.wcf-badges').prepend(yes_step_html);

					
				}

			}

			var no_step_badge_element = $this.find('.wcf-no-next-badge');

			var render_no_step = no_step_badge_element.data('no-step');
			
			if( render_no_step === "" || render_no_step === undefined ){

				var next_no_name  = next_step_element.find('.wcf-step-left-content').children().eq(1).text();

				no_step_badge_element.remove();

				if( next_step_element.length !== 0 ){
					var no_label = cartflows_admin.add_no_label + next_no_name;
					
				}else{
					var no_label = cartflows_admin.add_no_label + cartflows_admin.not_found_label;
				}

				var no_step_html = '<span class="wcf-flow-badge wcf-conditional-badge wcf-no-next-badge">'+ no_label+'</span>';
				
				$this.find('.wcf-badges').append(no_step_html);

			}

		});

		$('.wcf-conditional-badge').show();

		wcf_remove_step_tags();

	}

	var wcf_remove_step_tags = function(){
		$('.wcf-step-wrap[data-term-slug="thankyou"]').nextAll().find('.wcf-conditional-badge').hide();
		$('.wcf-step-wrap[data-term-slug="checkout"]').prevAll().find('.wcf-conditional-badge').hide();
	}


	$( document ).ready(function() {
		_show_image_field_bump_offer_event();

		_show_image_field_bump_offer();

		wcf_sort_flow();
		wcf_step_delete_sortable();

		wcf_remove_step_tags();

	});
	

} )( jQuery ); 