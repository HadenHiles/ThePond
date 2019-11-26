jQuery(document).ready(function($){

    if(jQuery('#wcf-pre-checkout-offer-modal').length < 1){
        return;
    }

    var current_gateway = '';

    function wcf_pre_checkout_process( e ) {
        
        var payment_method = jQuery( 'form.checkout' ).find( 'input[name="payment_method"]:checked' ).val();
        
        current_gateway = payment_method;

        jQuery('.wcf_validation_error').remove();

        jQuery( 'form.checkout' ).addClass('loader').block({
            message: null,
            overlayCSS: {
                background: '#fff',
                opacity: 0.6
            }
        });

        $.ajax({
            
            url: wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'wcf_validate_form' ),
            data: $( 'form.checkout' ).serialize(),
            dataType: 'json',
            type: 'POST',
            success: function ( response ) {
                try {
                    jQuery( 'form.checkout' ).removeClass('loader').unblock();
                   
                    if(response.result == "success"){

                        if ( 'authorize_net_cim_credit_card' === payment_method ) {
                            var display = "yes";
                        }else{
                            var payment_method_verified  = jQuery( 'form.checkout' ).triggerHandler( 'checkout_place_order_'+payment_method );

                            if(payment_method_verified == undefined){
                                var display = "yes";
                            }else if(payment_method_verified != undefined && payment_method_verified == true){
                                var display = "yes";
                            }else{
                                var display = "no";
                            }
                        }

                        if(display =="yes"){
                            var billing_first_name = jQuery('#billing_first_name').val();
                            var old_title = jQuery('.wcf-pre-checkout-offer-wrapper .wcf-content-modal-title h1').text();

                            var result = old_title.match(/{([^}]+)}/);

                            if(result != null && billing_first_name.length !=0 ){
                                var str_tag = result[1];
                                if(str_tag === "first_name"){
                                    var new_title = old_title.replace(/{(.*?)}/, '<span class="wcf_first_name"></span>');
                                }else{
                                    var new_title = old_title.replace(/{(.*?)}/,"");
                                }

                                $('.wcf-pre-checkout-offer-wrapper .wcf-content-modal-title h1').html(new_title);

                                $('.wcf_first_name').text(billing_first_name);
                            }

                            $('.wcf-pre-checkout-offer-wrapper').addClass('open');
                            $('html').addClass('wcf-pre-checkout-offer-open');
                            $('.wcf-pre-checkout-offer-action').val("");

                            setTimeout( function() {
                                update_popup_height();
                            }, 100);
                        }//end of display
                    }else if ( response.result  ===  'failure') {
                        throw 'Result failure';
                    } else {
                        throw 'Invalid response';
                    }
                } catch( err ) {

                    if ( response.messages ) {
                        jQuery( 'form.checkout' ).prepend('<div class="wcf_validation_error">'+response.messages+'</div>');

                        jQuery( 'form.checkout' ).removeClass( 'processing' ).unblock();
                        jQuery( 'form.checkout' ).find( '.input-text, select, input:checkbox' ).trigger( 'validate' ).blur();
                        var scrollElement  = $( '.wcf_validation_error' );

                        if ( ! scrollElement.length ) {
                            scrollElement = $( '.form.checkout' );
                        }
                        $.scroll_to_notices( scrollElement );
                    } 
                } //end of catch
            }//end of success
        }); //end of ajax
    }

    jQuery( 'form.checkout' ).on( 'checkout_place_order.wcf_pre_checkout', function(e) {

        if( jQuery('.wcf-pre-checkout-offer-action').val() === "add" ){
            
            setTimeout(function() {
                wcf_pre_checkout_process();
            }, 100 );
    
            return false;
        }
    });

    $('body').on('click', '.wcf-pre-checkout-offer-btn', function (e) {
        e.preventDefault();
        $checkoutForm = $('.wcf-pre-checkout-offer-btn').find('form.checkout');
        var checkout_id = $('._wcf_checkout_id').val();
        if( checkout_id != ""){
           $('.wcf-pre-checkout-offer-btn').html(cartflows.add_to_cart_text);
            $.ajax({
                url: cartflows.ajax_url,
                data: {
                    action: "wcf_add_to_cart",
                    product_quantity: 1,
                    checkout_id:checkout_id
                },
                dataType: 'json',
                type: 'POST',
                success: function ( response ) {

                    /* Unbind Event */
                    jQuery( 'form.checkout' ).off( 'checkout_place_order.wcf_pre_checkout' );

                    if ( 'authorize_net_cim_credit_card' === current_gateway ) {

                        $( "body" ).trigger( "update_checkout", { update_shipping_method: false } );

                        // Fire updated_checkout event.
                        $( document.body ).on( 'updated_checkout.wcf_pre_checkout', function( data ) {
                            console.log( data );
                            $('.wcf-pre-checkout-offer-action').val("");
                            $('.wcf-pre-checkout-offer-btn').html(response.message);
                            $('.wcf-pre-checkout-offer-wrapper').removeClass('open');
                            $('html').removeClass('wcf-pre-checkout-offer-open');
                            $( 'form.checkout' ).submit();

                            /* Unbind Event */
                            $( document.body ).off( 'updated_checkout.wcf_pre_checkout');
                        });
                    }else{

                        $('.wcf-pre-checkout-offer-action').val("");
                        $('.wcf-pre-checkout-offer-btn').html(response.message);

                        setTimeout(function() {
                            $('.wcf-pre-checkout-offer-wrapper').removeClass('open');
                            $('html').removeClass('wcf-pre-checkout-offer-open');
                            $( 'form.checkout' ).submit();
                        }, 600);
                    }
                }
            });
        }
    });

    $('body').on('click', '.wcf-pre-checkout-skip', function (){
        
        /* Unbind Event */
        jQuery( 'form.checkout' ).off( 'checkout_place_order.wcf_pre_checkout' );

        $('.wcf-pre-checkout-offer-action').val("");
        $('.wcf-pre-checkout-offer-wrapper').removeClass('open');
        $('html').removeClass('wcf-pre-checkout-offer-open');
        $( 'form.checkout' ).submit(); 
    });

    $(window).resize(function(){
        update_popup_height();
    });

    var update_popup_height = function() {
        var window_height = $(window).height(),
            popup_height = $('#wcf-pre-checkout-offer-modal').height();

        if ( popup_height > window_height ) {
            $('html').removeClass('wcf-pre-checkout-screen-size');
        }else{
            $('html').addClass('wcf-pre-checkout-screen-size');
        }
    };
});
