(function($){

	/* Create default steps */
	var wcf_setup_default_steps = function() {
		
		$('.wcf-setup-default-steps').on('click', function(e) {

			e.preventDefault();

			if( $( 'body' ).hasClass( 'post-type-cartflows_flow' ) && $( 'body' ).hasClass( 'post-new-php' ) ) {
				
				var	$wpTitle = $( '#title' );

				if ( ! $wpTitle.val() ) {
					$wpTitle.val( 'Flow #' + $( '#post_ID' ).val() );
				}
				
				if ( wp.autosave ) {
					wp.autosave.server.triggerSave();
				}

				$(window).off( 'beforeunload.edit-post' );
			}
			
			var $this 	= $(this);
				post_id = $( 'form#post #post_ID').val();

			$.ajax({
	            url: ajaxurl,
				data: {
					action: "cartflows_setup_default_steps",
					post_id : post_id,
					security: cartflows_admin.wcf_setup_default_steps_nonce
				},
				dataType: 'json',
				type: 'POST',
				success: function ( data ) {
					
					if ( data.status ) {
						
						

						location.href = data.redirect.replace( '&amp;', '&' );
					}
					console.log( data );
				}
			});
		});
	}

	var wcf_steps_hide_show_delete = function() {
		
		$('.wcf-flow-settings .wcf-step-delete').on('click', function(e) {
			
			e.preventDefault();
			var current_target = $( e.target );

			var $this 	= $(this),
				step_id = $this.data('id'),
				icon_span = $this.find('.dashicons-trash'),
				text_span = $this.find('.wcf-step-act-btn-text'),
				parent    = $this.parents('.wcf-step-wrap');
			
			var delete_status = confirm( "This action will delete this flow step. Are you sure?" );
			if (true == delete_status) {

				console.log( 'Step Deleting' );
				icon_span.addClass('wp-ui-text-notification');
				text_span.addClass('wp-ui-text-notification').text('Deleting..');
				//$this.text('Deleting..');

				var post_id = $( 'form#post #post_ID').val();

				$.ajax({
		            url: ajaxurl,
					data: {
						action: "cartflows_delete_flow_step",
						post_id : post_id,
						step_id : step_id,
						security: cartflows_admin.wcf_delete_flow_step_nonce
					},
					dataType: 'json',
					type: 'POST',
					success: function ( data ) {
						parent.slideUp(400, 'swing', function() {
							parent.remove();
						});

						setTimeout(function() {
						    $('.wcf-flow-steps-container').trigger('wcf-step-deleted',[step_id]);
						}, 600);

						console.log( data );
					}
				});
			}
		});
	}
	
	var wcf_flow_steps_sortbale = function() {

    	$('.wcf-flow-settings .wcf-flow-steps-container').sortable({
    		
    		connectWith: '.wcf-flow-steps-container',
    		forcePlaceholderSize: true,
			placeholder: "sortable-placeholder",

    		update: function(event, ui) {
				
				var $this 			= $(this),
					step_fields 	= $this.find('.wcf-steps-hidden'), 
					step_ids 		= [],
					post_id 		= $( 'form#post #post_ID').val();

				step_fields.each(function(i, obj) {
					step_ids.push( $(this).val() ) //test
				});

				$this.sortable('disable');

				$.ajax({
		            url: ajaxurl,
					data: {
						action: "cartflows_reorder_flow_steps",
						post_id : post_id,
						step_ids : step_ids,
						security: cartflows_admin.wcf_reorder_flow_steps_nonce
					},
					dataType: 'json',
					type: 'POST',
					success: function ( data ) {
						
						$this.sortable('enable');
						
						if ( data.status ) {
							console.log( 'Sorted' );
						}

						console.log( data );
					}
				});
			}
    	});

	}
	$(document).ready(function($) {

		wcf_setup_default_steps();

		wcf_steps_hide_show_delete();

		wcf_flow_steps_sortbale();
	});
})(jQuery);
