( function( $ ) {

	CartFlowsWizard = {

		init: function() {
			this._bind();
		},

		/**
		 * Bind
		 */
		_bind: function() {
			$( document ).on('click', '.wcf-install-plugins', 	CartFlowsWizard._installNow);
			$( document ).on('wp-plugin-installing'      , 		CartFlowsWizard._pluginInstalling);
			$( document ).on('wp-plugin-install-error'   , 		CartFlowsWizard._installError);
			$( document ).on('wp-plugin-install-success' , 		CartFlowsWizard._installSuccess);
		},

		/**
		 * Installing Plugin
		 */
		_pluginInstalling: function(event, args) {
			event.preventDefault();
			console.log( 'Installing..' );
		},

		/**
		 * Install Error
		 */
		_installError: function(event, args) {
			event.preventDefault();
			console.log( 'Install Error!' );

			var redirect_link = $( '.page-builder-list' ).data('redirect-link') || '';
			console.log( redirect_link );
			if( '' !== redirect_link ) {
				window.location = redirect_link;
				console.log( 'redirecting..' );
			}
		},

		/**
		 * Install Success
		 */
		_installSuccess: function(event, args) {
			event.preventDefault();

			CartFlowsWizard._activatePlugin();
		},

		_activatePlugin: function() {
			var plugin_slug 	= $( '.page-builder-list option:selected' ).data( 'slug' ) || '',
				plugin_init     = $( '.page-builder-list option:selected' ).data( 'init' ) || '',
				redirect_link   = $( '.page-builder-list' ).data('redirect-link') || '';
			
			console.log( plugin_slug );
			console.log( plugin_init );
			console.log( redirect_link );

			$.ajax({
				url    : ajaxurl,
				method : 'POST',
				data   : {
					action       : 'page_builder_step_save',
					page_builder : plugin_slug,
					plugin_init  : plugin_init,
				},
			})
			.done(function( data ) {
				console.log( data );
				console.log( redirect_link );
				if( data.success ) {
					if( '' !== redirect_link ) {
						window.location = redirect_link;
					}
				}
			})
			.fail(function() {
				console.log("error");
			})
			.always(function() {
				console.log("complete");
			});
		},

		/**
		 * Install Now
		 */
		_installNow: function(event)
		{
			event.preventDefault();

			var $button 	= $( this ),
				$document   = $(document),
				plugin_slug = $( '.page-builder-list option:selected' ).data( 'slug' ) || '',
				install     = $( '.page-builder-list option:selected' ).data( 'install' ) || 'no',
				plugin_init = $( '.page-builder-list option:selected' ).data( 'init' ) || '';

			if ( $button.hasClass( 'updating-message' ) || $button.hasClass( 'button-disabled' ) ) {
				return;
			}

			$button.addClass( 'updating-message' );

			if( 'yes' === install ) {
				CartFlowsWizard._activatePlugin();
			} else {

				console.log( 'plugin_slug' );


				if ( wp.updates.shouldRequestFilesystemCredentials && ! wp.updates.ajaxLocked ) {
					wp.updates.requestFilesystemCredentials( event );

					$document.on( 'credential-modal-cancel', function() {
						var $message = $( '.install-now.updating-message' );

						$message
							.removeClass( 'updating-message' )
							.text( wp.updates.l10n.installNow );

						wp.a11y.speak( wp.updates.l10n.updateCancel, 'polite' );
					} );
				}

				wp.updates.installPlugin( {
					slug: plugin_slug
				} );
			}

			
		},
	}

	$( document ).ready(function() {
		CartFlowsWizard.init();
	});
	

} )( jQuery ); 