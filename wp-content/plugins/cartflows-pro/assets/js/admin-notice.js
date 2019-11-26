

(function($){

	CartFlowsProAdminNotice = {

		/**
		 * Init
		 */
		init: function()
		{
			this._bind();
		},

		/**
		 * Binds events
		 */
		_bind: function()
		{
			$( document ).on('click', '.cartflows-dismissible-notice .notice-dismiss',	CartFlowsProAdminNotice.disable_license_admin_notice);
		},

		/**
		 * Import
		 */
		disable_license_admin_notice: function( event )
		{
			event.preventDefault();

			var btn = $(this);

			$.ajax({
				url  : ajaxurl,
				type : 'POST',
				data : {
					action   : 'cartflows_disable_activate_license_notice',
					security : CartFlowsProAdminNoticeVars._nonce
				},
			})
			.done(function( data, status, XHR ) {

				if( data.success ) {

				} else {

				}
			})
			.fail(function( jqXHR, textStatus )
			{
			})
			.always(function()
			{
			});
		}

	};

	/**
	 * Initialization
	 */
	$(function(){
		CartFlowsProAdminNotice.init();
	});

})(jQuery);	