(function ($) {

    var global_flow_data = {};

    var wcf_analytics_admin = {

        init: function () {

            $(document).on('click', '.wcf-trigger-reports-popup', this._renderAnalyticsPopup);
            $(document).on('focus', '#wcf_custom_filter_from, #wcf_custom_filter_to', this._show_datepicker);
            $(document).on('click', '.wcf-filters button', this._render_analytics_view);
        },

        _show_datepicker: function () {
            $("#wcf_custom_filter_from").datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: '0',
                onClose: function (selectedDate) {
                    jQuery("#wcf_custom_filter_to").datepicker("option", "minDate", selectedDate);
                }
            }).attr('readonly', 'readonly').css('background', 'white');

            $("#wcf_custom_filter_to").datepicker({
                dateFormat: 'yy-mm-dd',
                maxDate: '0',
                onClose: function (selectedDate) {
                    jQuery("#wcf_custom_filter_from").datepicker("option", "maxDate", selectedDate);
                }
            }).attr('readonly', 'readonly').css('background', 'white');
        },

        _renderAnalyticsPopup: function (e) {
            e.preventDefault();
            $("#wcf-analytics-popup-wrap").addClass('open');
            $("html").addClass('wcf-popup-open');
            wcf_analytics_admin._render_analytics_view();
        },

        _render_analytics_view() {
            var date_to = new Date();
            var date_from = new Date();
            var report_date = jQuery(this).data("diff");
            report_date = typeof(report_date) === "undefined" ? 7 : report_date;

            switch (report_date) {
                case 7:
                    date_from.setDate(date_from.getDate() - 7);
                    break;
                case 30:
                    date_from.setDate(date_from.getDate() - 30);
                    break;
                case 1:
                    date_from.setDate(date_from.getDate() - 1);
                    break;
                case -1:
                    date_to = new Date(jQuery("#wcf_custom_filter_to").val());
                    date_from = new Date(jQuery("#wcf_custom_filter_from").val());
                    break;
            }

            var flow_id = $("#post_ID").val();
            date_from = date_from.toISOString().slice(0, 10);
            date_to = date_to.toISOString().slice(0, 10);

            var request_data = {
                action: "cartflows_set_visit_data",
                flow_id: flow_id,
                date_from: date_from,
                date_to: date_to
            };
            var hash = JSON.stringify(request_data);
            var response = sessionStorage.getItem(hash);

            var template = wp.template('cartflows-analytics-template');
            if(response) {
                data = JSON.parse(response);
                data.report_type = report_date;
                $('.wcf-analytics-reports-wrap').html(template(data));
                $("#wcf_custom_filter_from").val(date_from);
                $("#wcf_custom_filter_to").val(date_to);

            } else {
                $.ajax({
                    url: ajaxurl,
                    data: request_data,
                    dataType: 'json',
                    type: 'POST',
                    success: function (response) {

                        if (response.success) {
                            var data = response.data;
                            data.report_type = report_date;
                            $('.wcf-analytics-reports-wrap').html(template(data));
                            $("#wcf_custom_filter_from").val(date_from);
                            $("#wcf_custom_filter_to").val(date_to);
                            sessionStorage.setItem( hash, JSON.stringify(data));
                        }
                    }
                });
            }


        },
    }

    $(document).ready(
        function () {
            wcf_analytics_admin.init();
            sessionStorage.clear();
        });
})(jQuery);