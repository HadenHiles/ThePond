(function($){

	CartFlowsProLicense = {

		/**
		 * Init
		 */
		init: function()
		{
			this._check_popup();
			this._bind();
		},

		_check_popup: function()
		{
			var self = CartFlowsProLicense;
			var open_popup = self._getUrlParameter('cartflows-license-popup') || '';
			if( open_popup && 'Deactivated' == CartFlowsProLicenseVars.activation_status ) {
				self._open_popup();
			}
		},
		
		/**
		 * Binds events
		 */
		_bind: function()
		{
			$( document ).on('click', '.cartflows-license-popup-open-button',	CartFlowsProLicense._export_button_click);
			$( document ).on('click', '.cartflows-close-popup-button',	CartFlowsProLicense._close_popup);
			$( document ).on('click', '#cartflows-license-popup-overlay',	CartFlowsProLicense._close_popup);
			$( document ).on('click', '.cartflows-activate-license',	CartFlowsProLicense._activate_license);
			$( document ).on('click', '.cartflows-deactivate-license',	CartFlowsProLicense._deactivate_license);
		},

		/**
		 * Debugging.
		 * 
		 * @param  {mixed} data Mixed data.
		 */
		_log: function( data ) {
			
			var date = new Date();
			var time = date.toLocaleTimeString();

			if (typeof data == 'object') { 
				console.log('%c ' + JSON.stringify( data ) + ' ' + time, 'background: #ededed; color: #444');
			} else {
				console.log('%c ' + data + ' ' + time, 'background: #ededed; color: #444');
			}
		},

		_export_button_click: function( e ) {
			e.preventDefault();
			CartFlowsProLicense._open_popup();
		},

		_open_popup: function() {

			var popup  	    = $('#cartflows-license-popup-overlay, #cartflows-license-popup'),
				license_key = $('#cartflows-license-popup').attr('data-license-key') || '',
				contents    = popup.find( '.contents' );

			console.log( license_key );

			// Add validate license window.
			if( 'Activated' == license_key ) {
				contents.html( wp.template( 'cartflows-deactivate-license' ) );
			} else {
				contents.html( wp.template( 'cartflows-activate-license' ) );
			}

			popup.show();
		},

		_close_popup: function( ) {

			var popup = $('#cartflows-license-popup-overlay, #cartflows-license-popup');

			if( popup.hasClass('validating') ) {
				
				// Proceed?
				if( ! confirm( "WARNING! License request not complete!!\n\nPlease wait for a moment until complete the license request." ) ) {
					return;
				}
			}

			popup.hide();
		},

		

		/**
		 * Import
		 */
		_activate_license: function( event )
		{
			event.preventDefault();
			var self          = CartFlowsProLicense;
			var btn           = $(this);
			var parent        = $('#cartflows-license-popup');
			var contents      = parent.find('.contents');
			var license_btn   = $('.cartflows-license-popup-open-button');
			var license_key   = parent.find('.license_key').val() || '';

			if( ! license_key.length ) {
				return;
			}

			if( btn.hasClass('disabled') || btn.hasClass('validating') ) {
				return;
			}

			parent.addClass('validating');
			btn.find('.text').text('Validating..');

			if( contents.find('.notice').length ) {
				contents.find('.notice').remove();
			}

			btn.find('.cartflows-processing').addClass('is-active');

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action        : 'cartflows_activate_license',
					license_key   : license_key,
				},
			})
			.done(function( data, status, XHR ) {

				parent.removeClass('validating');

				btn.find('.cartflows-processing').removeClass('is-active');
				
				if( data.success ) {

					license_btn.removeClass('active').addClass('inactive').text('Deactivate License');
					btn.find('.text').text('Successfully Activated! Reloading..');
					parent.attr('data-license-key', license_key);

					setTimeout(function() {
						// CartFlowsProLicense._close_popup();
						location.reload();
					}, 2500);
					
					parent.find('input').addClass('disabled').attr('readonly', 'readonly');

					// var msg = data.data.message || data.data;
					// if( msg ) {
					// 	contents.append( '<div class="notice notice-success"><p>' + msg + '</p></div>' );
					// }

				} else {

					var msg = data.data.error || data.data || '';
					if( msg ) {
						contents.append( '<div class="notice notice-error"><p>' + msg + '</p></div>' );
					}

					btn.find('.text').text('Failed!');
				}

				// tb_remove();
			})
			.fail(function( jqXHR, textStatus )
			{
			})
			.always(function()
			{
			});
		},

		/**
		 * Import
		 */
		_deactivate_license: function( event )
		{
			event.preventDefault();

			var self        = $(this);
			var license_btn = $('.cartflows-license-popup-open-button');
			var parent      = $('#cartflows-license-popup');
			var contents   = parent.find('.contents');
			var license_key = parent.find('.license_key').val() || '';

			parent.addClass('validating');
			self.find('.text').text('Deactivating..');

			if( contents.find('.notice').length ) {
				contents.find('.notice').remove();
			}

			self.find('.cartflows-processing').addClass('is-active');

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action : 'cartflows_deactivate_license'
				},
			})
			.done(function( data, status, XHR ) {

				parent.removeClass('validating');

				self.find('.cartflows-processing').removeClass('is-active');
				
				if( data.success ) {

					license_btn.removeClass('inactive').addClass('active').text('Activate License');

					self.find('.text').text('Successfully Deactivated! Reloading..');
					parent.attr('data-license-key', '');

					setTimeout(function() {
						location.reload();
						// CartFlowsProLicense._close_popup();
					}, 2500);

				} else {

					var msg = data.data.message || data.data || data.response || '';
					if( msg ) {
						contents.append( '<div class="notice notice-error"><p>' + msg + '</p></div>' );
					}

					self.find('.text').text('Failed!');
				}

				// tb_remove();
			})
			.fail(function( jqXHR, textStatus )
			{
			})
			.always(function()
			{
			});
		},

		_getUrlParameter: function( param ) {
		    var page_url = decodeURIComponent( window.location.search.substring(1) ),
		        url_variables = page_url.split('&'),
		        parameter_name,
		        i;

		    for ( i = 0; i < url_variables.length; i++ ) {
		        parameter_name = url_variables[i].split('=');

		        if (parameter_name[0] === param) {
		            return parameter_name[1] === undefined ? true : parameter_name[1];
		        }
		    }
		}

	};

	/**
	 * Initialization
	 */
	$(function(){
		CartFlowsProLicense.init();
	});

})(jQuery);	