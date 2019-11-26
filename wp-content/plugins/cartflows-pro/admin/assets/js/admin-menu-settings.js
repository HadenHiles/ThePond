( function( $ ) {

	/**
	 * AJAX Request Queue
	 *
	 * - add()
	 * - remove()
	 * - run()
	 * - stop()
	 *
	 * @since 1.0.0
	 */
	var UaelAjaxQueue = (function() {

		var requests = [];

		return {

			/**
			 * Add AJAX request
			 *
			 * @since 1.0.0
			 */
			add:  function(opt) {
			    requests.push(opt);
			},

			/**
			 * Remove AJAX request
			 *
			 * @since 1.0.0
			 */
			remove:  function(opt) {
			    if( jQuery.inArray(opt, requests) > -1 )
			        requests.splice($.inArray(opt, requests), 1);
			},

			/**
			 * Run / Process AJAX request
			 *
			 * @since 1.0.0
			 */
			run: function() {
			    var self = this,
			        oriSuc;

			    if( requests.length ) {
			        oriSuc = requests[0].complete;

			        requests[0].complete = function() {
			             if( typeof(oriSuc) === 'function' ) oriSuc();
			             requests.shift();
			             self.run.apply(self, []);
			        };

			        jQuery.ajax(requests[0]);

			    } else {

			      self.tid = setTimeout(function() {
			         self.run.apply(self, []);
			      }, 1000);
			    }
			},

			/**
			 * Stop AJAX request
			 *
			 * @since 1.0.0
			 */
			stop:  function() {

			    requests = [];
			    clearTimeout(this.tid);
			}
		};

	}());

	UaelAdmin = {

		init: function() {
			/**
			 * Run / Process AJAX request
			 */
			UaelAjaxQueue.run();
			this._knowledgebase();
			this._support();

			$( document ).delegate( ".uael-activate-widget", "click", UaelAdmin._activate_widget );
			$( document ).delegate( ".uael-deactivate-widget", "click", UaelAdmin._deactivate_widget );

			$( document ).delegate( ".uael-activate-all", "click", UaelAdmin._bulk_activate_widgets );
			$( document ).delegate( ".uael-deactivate-all", "click", UaelAdmin._bulk_deactivate_widgets );
			
			$( document ).delegate( "#uael-gen-enable-beta-update", "click", UaelAdmin._allow_beta_updates );

			/* White Label */
			$( document ).delegate( "#uael-wl-enable-knowledgebase", "change", UaelAdmin._knowledgebase );
			$( document ).delegate( "#uael-wl-enable-support", "change", UaelAdmin._support );
		},

		/**
		 * Activate All Widgets.
		 */
		_bulk_activate_widgets: function( e ) {
			var button = $( this );

			var data = {
				action: 'uael_bulk_activate_widgets',
				nonce: uael.ajax_nonce,
			};

			if ( button.hasClass( 'updating-message' ) ) {
				return;
			}
			
			$( button ).addClass('updating-message');

			UaelAjaxQueue.add({
				url: ajaxurl,
				type: 'POST',
				data: data,
				success: function(data){

					console.log( data );

					// Bulk add or remove classes to all modules.
					$('.uael-widget-list').children( "li" ).addClass( 'activate' ).removeClass( 'deactivate' );
					$('.uael-widget-list').children( "li" ).find('.uael-activate-widget')
						.addClass('uael-deactivate-widget')
						.text(uael.deactivate)
						.removeClass('uael-activate-widget');
						$( button ).removeClass('updating-message');
					}
			});
			e.preventDefault();
		},

		/**
		 * Deactivate All Widgets.
		 */
		_bulk_deactivate_widgets: function( e ) {
			var button = $( this );

			var data = {
				action: 'uael_bulk_deactivate_widgets',
				nonce: uael.ajax_nonce,
			};

			if ( button.hasClass( 'updating-message' ) ) {
				return;
			}
			$( button ).addClass('updating-message');

			UaelAjaxQueue.add({
				url: ajaxurl,
				type: 'POST',
				data: data,
				success: function(data){

					console.log( data );
					// Bulk add or remove classes to all modules.
					$('.uael-widget-list').children( "li" ).addClass( 'deactivate' ).removeClass( 'activate' );
					$('.uael-widget-list').children( "li" ).find('.uael-deactivate-widget')
						.addClass('uael-activate-widget')
						.text(uael.activate)
						.removeClass('uael-deactivate-widget');
						$( button ).removeClass('updating-message');
					}
			});
			e.preventDefault();
		},

		/**
		 * Activate Module.
		 */
		_activate_widget: function( e ) {
			var button = $( this ),
				id     = button.parents('li').attr('id');

			var data = {
				module_id : id,
				action: 'uael_activate_widget',
				nonce: uael.ajax_nonce,
			};

			if ( button.hasClass( 'updating-message' ) ) {
				return;
			}

			$( button ).addClass('updating-message');

			UaelAjaxQueue.add({
				url: ajaxurl,
				type: 'POST',
				data: data,
				success: function(data){

					// Add active class.
					$( '#' + id ).addClass('activate').removeClass( 'deactivate' );
					// Change button classes & text.
					$( '#' + id ).find('.uael-activate-widget')
						.addClass('uael-deactivate-widget')
						.text(uael.deactivate)
						.removeClass('uael-activate-widget')
						.removeClass('updating-message');
					}
			});

			e.preventDefault();
		},

		/**
		 * Deactivate Module.
		 */
		_deactivate_widget: function( e ) {
			var button = $( this ),
				id     = button.parents('li').attr('id');
			var data = {
				module_id: id,
				action: 'uael_deactivate_widget',
				nonce: uael.ajax_nonce,
			};
			
			if ( button.hasClass( 'updating-message' ) ) {
				return;
			}

			$( button ).addClass('updating-message');

			UaelAjaxQueue.add({
				url: ajaxurl,
				type: 'POST',
				data: data,
				success: function(data){

					// Remove active class.
					$( '#' + id ).addClass( 'deactivate' ).removeClass('activate');
					
					// Change button classes & text.
					$( '#' + id ).find('.uael-deactivate-widget')
						.addClass('uael-activate-widget')
						.text(uael.activate)
						.removeClass('uael-deactivate-widget')
						.removeClass('updating-message');
				}
			})
			e.preventDefault();
		},

		/**
		 * Allow Beta Updates.
		 */
		_allow_beta_updates: function( e ) {
						
			var $this = $(this);
			var allow_beta = $this.attr('data-value');
			
			if ( 'disable' === allow_beta ) {
				allow_beta = 'enable';
			}else{
				allow_beta = 'disable';
			}

			$this.addClass('loading');

			var data = {
				allow_beta: allow_beta,
				action: 'uael_allow_beta_updates',
				nonce: uael.ajax_nonce,
			};

			UaelAjaxQueue.add({
				url: ajaxurl,
				type: 'POST',
				data: data,
				success: function(data){

					window.location.href += '&message=saved';
				}
			})
		},

		/**
		 * Knowledge Base.
		 */
		_knowledgebase: function() {
			if ( $('#uael-wl-enable-knowledgebase').is(':checked') ) {
				$('p.uael-knowledgebase-url').show();
			}else{
				$('p.uael-knowledgebase-url').hide();
			}
		},
		/**
		 * Support.
		 */
		_support: function() {
			if ( $('#uael-wl-enable-support').is(':checked') ) {
				$('p.uael-support-url').show();
			}else{
				$('p.uael-support-url').hide();
			}
		}
	}

	$( document ).ready(function() {
		UaelAdmin.init();
	});
	

} )( jQuery ); 