(function ($) {
    'use strict';

    var settings = window.fbGroupSecret || {};
    var statusTimer;

    function setStatus($card, message, type) {
        var $status = $card.find('.fb-group-card__status');
        clearTimeout(statusTimer);
        $status.removeClass('is-error is-success').addClass(type ? 'is-' + type : '').text(message || '');
        if (message && type === 'success') {
            statusTimer = setTimeout(function () {
                $status.removeClass('is-success').text('');
            }, 4000);
        }
    }

    function fallbackCopy(input) {
        input.focus();
        input.select();
        try {
            return document.execCommand('copy');
        } catch (e) {
            return false;
        }
    }

    // Delegated so the controls keep working if the section is ever re-rendered.
    $(document).on('click', '[data-fb-group] #copyButton', function (e) {
        e.preventDefault();
        var $button = $(this);
        var $card = $button.closest('[data-fb-group]');
        var input = $card.find('#phrase').get(0);
        var done = function (ok) {
            if (!ok) {
                setStatus($card, settings.copyFailed, 'error');
                return;
            }
            $button.addClass('is-copied');
            setStatus($card, settings.copied, 'success');
            setTimeout(function () { $button.removeClass('is-copied'); }, 2000);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(input.value).then(function () { done(true); }, function () { done(fallbackCopy(input)); });
        } else {
            done(fallbackCopy(input));
        }
    });

    $(document).on('click', '[data-fb-group] #generate-new-phrase', function (e) {
        e.preventDefault();
        var $button = $(this);
        var $card = $button.closest('[data-fb-group]');
        if ($button.prop('disabled')) {
            return;
        }

        var started = Date.now();
        $button.prop('disabled', true).attr('aria-busy', 'true');
        $card.addClass('is-generating');
        setStatus($card, settings.generating);

        // Keep the spinner visible long enough to read as feedback on fast responses.
        var finish = function (callback) {
            setTimeout(function () {
                $button.prop('disabled', false).removeAttr('aria-busy');
                $card.removeClass('is-generating');
                callback();
            }, Math.max(0, 700 - (Date.now() - started)));
        };

        $.ajax({
            url: settings.ajaxUrl || '/wp-admin/admin-ajax.php',
            type: 'POST',
            dataType: 'json',
            data: { action: 'generate_facebook_group_phrase' }
        }).done(function (response) {
            var data = response && response.data;
            if (!data || !data.success || !data.phrase) {
                finish(function () { setStatus($card, settings.failed, 'error'); });
                return;
            }
            finish(function () {
                var $field = $card.find('.fb-group-card__field');
                $card.find('#phrase').val(data.phrase);
                $field.addClass('is-updated');
                setTimeout(function () { $field.removeClass('is-updated'); }, 1200);
                setStatus($card, settings.generated, 'success');
            });
        }).fail(function () {
            finish(function () { setStatus($card, settings.failed, 'error'); });
        });
    });
})(jQuery);
