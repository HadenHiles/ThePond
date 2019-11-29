(function ($) {
  $(document).ready(function() {
    //Trigger a click on stripe checkout automatically
    var done = false; //Prevent double submit (for some reason)
    if(!done) {
      $("button.stripe-button-el").trigger("click");
      done = true;
    }

    // TODO: Before we launch this we need to make this part configurable using wp_localize_script or something
    var card_style = {
      base: {
        lineHeight: '30px',
        fontFamily: 'proxima-nova, sans-serif',
      }
    };

    var stripe = Stripe(MeprStripeGateway.public_key);
    var elements = stripe.elements();
    var card = elements.create('card', {style: card_style});
    card.mount('#card-element');
    card.addEventListener('change', function(event) {
      var displayError = document.getElementById('card-errors');
      if (event.error) {
        displayError.textContent = event.error.message;
      } else {
        displayError.textContent = '';
      }
    });

    var paymentForm = $('#mepr-signup-form, #mepr-stripe-payment-form');
    paymentForm.on('submit', function(e) {
      e.preventDefault();
      if($('#mepr-payment-methods-wrapper').is(':hidden')) {
        paymentForm[0].submit();
        return false;
      }
      paymentForm.find('.mepr-submit').prop('disabled', true);
      paymentForm.find('.mepr-loading-gif').show();
      var paymentType = paymentForm.find('input[name="mepr_payment_method"]:checked').data('paymentMethodType');

      if(paymentType === 'Stripe' || paymentForm.attr('id') === 'mepr-stripe-payment-form') {
        var cardData = {
          billing_details: getBillingDetails()
        };

        stripe.createPaymentMethod('card', card, cardData).then(function(result) {
          if (result.error) {
            handlePaymentError(result.error.message);
          } else {
            confirmPayment({
              payment_method_id: result.paymentMethod.id
            });
          }
        });
      }
      else {
        paymentForm[0].submit();
      }

      return false; // submit from callback
    });

    /**
     * Returns the form fields in a pretty key/value hash
     *
     * @param  {jQuery} form
     * @return {object}
     */
    function getFormData(form) {
      return form.serializeArray().reduce(function(obj, item) {
        obj[item.name] = item.value;
        return obj;
      }, {});
    }

    /**
     * Get the billing details object to pass to Stripe
     *
     * @return {object}
     */
    function getBillingDetails() {
      var formData = getFormData(paymentForm),
          isSpc = paymentForm.hasClass('mepr-signup-form'),
          keys = {
            line1: isSpc ? 'mepr-address-one' : 'card-address-1',
            line2: isSpc ? 'mepr-address-two' : 'card-address-2',
            city: isSpc ? 'mepr-address-city' : 'card-city',
            country: isSpc ? 'mepr-address-country' : 'card-country',
            state: isSpc ? 'mepr-address-state' : 'card-state',
            postal_code: isSpc ? 'mepr-address-zip' : 'card-zip'
          },
          details = {
            address: {}
          };

      if (typeof formData['card-name'] == 'string' && formData['card-name'].length) {
        details.name = formData['card-name'];
      }

      for (var key in keys) {
        if (keys.hasOwnProperty(key)) {
          if (typeof formData[keys[key]] == 'string' && formData[keys[key]].length) {
            details.address[key] = formData[keys[key]];
          }
        }
      }

      return details;
    }

    /**
     * Handle an error with the payment
     *
     * @param {string} message The error message to display
     */
    function handlePaymentError(message) {
      console.log(message);
      // re-enable the submit button
      paymentForm.find('.mepr-submit').prop('disabled', false);
      paymentForm.find('.mepr-loading-gif').hide();
      paymentForm.find('.mepr-form-has-errors').show();

      // Inform the user if there was an error
      $('#card-errors').html(message);
    }

    /**
     * Handle the response from our Ajax endpoint after creating the PaymentIntent
     *
     * @param {object} response
     */
    function handleServerResponse(response) {
      if (response === null || typeof response != 'object') {
        handlePaymentError(MeprStripeGateway.invalid_response_error)
      } else {
        if (response.transaction_id) {
          paymentForm.find('input[name="mepr_transaction_id"]').val(response.transaction_id);
        }

        if (response.error) {
          handlePaymentError(response.error);
        } else if (response.requires_action) {
          handleAction(response);
        } else if (!paymentForm.hasClass('mepr-payment-submitted')) {
          paymentForm.addClass('mepr-payment-submitted');
          paymentForm[0].submit();
        }
      }
    }

    /**
     * Displays the card action dialog to the user, and confirms the payment if successful
     *
     * @param {object} response
     */
    function handleAction(response) {
      var cardData;

      if (response.action === 'handleCardSetup') {
        cardData = {
          payment_method_data: {
            billing_details: getBillingDetails()
          }
        };

        stripe.handleCardSetup(response.client_secret, card, cardData).then(function (result) {
          if (result.error) {
            handlePaymentError(result.error.message);
          } else {
            confirmPayment({
              setup_intent_id: result.setupIntent.id
            });
          }
        });
      } else if (response.action === 'handleCardPayment') {
        cardData = {
          payment_method_data: {
            billing_details: getBillingDetails()
          }
        };

        stripe.handleCardPayment(response.client_secret, card, cardData).then(function (result) {
          if (result.error) {
            handlePaymentError(result.error.message);
          } else {
            confirmPayment({
              payment_intent_id: result.paymentIntent.id
            });
          }
        });
      } else {
        stripe.handleCardAction(response.client_secret).then(function (result) {
          if (result.error) {
            handlePaymentError(result.error.message);
          } else {
            confirmPayment({
              payment_intent_id: result.paymentIntent.id
            });
          }
        });
      }
    }

    /**
     * Confirm the payment with our Ajax endpoint
     *
     * @param {object} extraData Additional data to send with the request
     */
    function confirmPayment(extraData) {
      var data = getFormData(paymentForm);

      $.extend(data, extraData || {}, {
        action: 'mepr_stripe_confirm_payment',
        mepr_current_url: document.location.href
      });

      // We don't want to hit our routes for processing the signup or payment forms
      delete data.mepr_process_signup_form;
      delete data.mepr_process_payment_form;

      $.ajax({
        type: 'POST',
        url: MeprStripeGateway.ajax_url,
        dataType: 'json',
        data: data
      })
      .done(handleServerResponse)
      .fail(function () {
        handlePaymentError(MeprStripeGateway.ajax_error);
      });
    }
  });
})(jQuery);
