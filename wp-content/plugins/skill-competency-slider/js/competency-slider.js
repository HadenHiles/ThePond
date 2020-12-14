(($) => {
    var gradient = [
        [
            0,
            [255, 0, 0]
        ],
        [
            20,
            [255, 113, 0]
        ],
        [
            40,
            [255, 113, 0]
        ],
        [
            60,
            [255, 166, 0]
        ],
        [
            80,
            [255, 166, 0]
        ],
        [
            100,
            [0, 195, 0]
        ]
    ];

    var sliderWidth = $("#competency-slider").outerWidth();

    $(window).resize(() => {
        sliderWidth = $("#competency-slider").outerWidth();
    });

    $(document).ready(() => {
        var data = {
            action: 'get_skill_competency_rating',
            skill_id: $('#competency-slider').data('skill-id'),
            user_id: $('#competency-slider').data('user-id'),
        };

        $.ajax({
            url: ajaxurl, // this will point to admin-ajax.php
            type: 'POST',
            data: data,
            success: function (response) {
                if (response.error == null) {
                    console.log('Rating:', response.data);

                    if (response.data != null) {
                        $("#competency-slider").slider({value: response.data.percentage});
                        
                        if (response.data.percentage >= 99) {
                            $('#current-competency-color').css({"background-color": ""});
                            $('#current-competency-color').html('<img src="/wp-content/plugins/skill-competency-slider/images/hth_logo.png" style="width: 100%;" />');
                        } else {
                            $('#current-competency-color').html("");
                            $('#current-competency-color').css({"background-color": response.data.rgb});
                        }
                    }
                } else {
                    // error(response.error.message);
                    console.error('Ajax error: ' + response.error.message + '. code: ' + response.error.code);
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('Ajax server error: ' + textStatus + ': ' + errorThrown);
            }
        });
    });

    $("#competency-slider").slider({
        min: 1,
        slide: function (event, ui) {

            var colorRange = []
            $.each(gradient, function (index, value) {
                if (ui.value <= value[0]) {
                    colorRange = [index - 1, index]
                    return false;
                }
            });

            //Get the two closest colors
            var firstcolor = gradient[colorRange[0]][1];
            var secondcolor = gradient[colorRange[1]][1];

            //Calculate ratio between the two closest colors
            var firstcolor_x = sliderWidth * (gradient[colorRange[0]][0] / 100);
            var secondcolor_x = sliderWidth * (gradient[colorRange[1]][0] / 100) - firstcolor_x;
            var slider_x = sliderWidth * (ui.value / 100) - firstcolor_x;
            var ratio = slider_x / secondcolor_x

            //Get the color with pickHex
            var result = pickHex(secondcolor, firstcolor, ratio);

            if (ui.value >= 99) {
                $('#current-competency-color').css({"background-color": ""});
                $('#current-competency-color').html('<img src="/wp-content/plugins/skill-competency-slider/images/hth_logo.png" style="width: 100%;" />');
            } else {
                $('#current-competency-color').html("");
                $('#current-competency-color').css({"background-color": 'rgb(' + result.join() + ')'});
            }

        },
        change: (event, ui) => {

            var data = {
                action: 'update_skill_competency_rating',
                skill_id: $('#competency-slider').data('skill-id'),
                user_id: $('#competency-slider').data('user-id'),
                percentage: ui.value,
                rgb: $('#current-competency-color').css("background-color")
            };
            $.ajax({
                url: ajaxurl, // this will point to admin-ajax.php
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.error == null) {
                        // console.log('Ajax response:', response);
                    } else {
                        // error(response.error.message);
                        console.error('Ajax error: ' + response.error.message + '. code: ' + response.error.code);
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Ajax server error: ' + textStatus + ': ' + errorThrown);
                }
            });
        }
    });

    function pickHex(color1, color2, weight) {
        var p = weight;
        var w = p * 2 - 1;
        var w1 = (w / 1 + 1) / 2;
        var w2 = 1 - w1;
        var rgb = [Math.round(color1[0] * w1 + color2[0] * w2),
        Math.round(color1[1] * w1 + color2[1] * w2),
        Math.round(color1[2] * w1 + color2[2] * w2)];
        return rgb;
    }
})(jQuery);