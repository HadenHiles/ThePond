(function ($) {

    /* Disable/Enable Custom Field section*/
    var wcf_toggle_fields_facebook_pixel = function () {
        var fb_pixel_fields = ".wcf-fb-pixel-wrapper";
        jQuery(fb_pixel_fields).toggle(jQuery("#wcf_wcf_facebook_pixel_tracking").is(":checked"));
        jQuery("#wcf_wcf_facebook_pixel_tracking").click(function () {
            jQuery(fb_pixel_fields).toggle(jQuery("#wcf_wcf_facebook_pixel_tracking").is(":checked"));
        });
    }

    $(document).ready(function () {
        wcf_toggle_fields_facebook_pixel();
    });

})(jQuery);