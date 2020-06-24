(function($) {

    $(document).ready(function() {
        // Get scores
        

        // Add score
        var $addButton = $('#add-score');
        $addButton.click(function(e) {
            e.preventDefault();
            $addButton.html('<i class="fa fa-spinner fa-spin"></i>');
            addScore(function() {
                $addButton.html('<i class="fa fa-plus-circle"></i>');
            });
        });
    });


    function getScores () {
        var challengeId = $('input#challenge-id').val();
        var userId = $('input#user-id').val();
        
        var data = {
            action: 'add_challenge_score',
            challenge_id: challengeId,
            user_id: userId,
            score: score
        };

        $.ajax({
            url: ajaxurl, // this will point to admin-ajax.php
            type: 'POST',
            data: data,
            success: function (response) {
                console.log('Ajax response:', response);
            }
        });
    }

    function addScore (cb) {
        var challengeId = $('input#challenge-id').val();
        var userId = $('input#user-id').val();
        var score = parseFloat($('input#challenge-score').val());
        
        if(challengeId != null && userId != null && score >= 0) {
            var data = {
                action: 'add_challenge_score',
                challenge_id: challengeId,
                user_id: userId,
                score: score
            };
    
            $.ajax({
                url: ajaxurl, // this will point to admin-ajax.php
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.error == null) {
                        success();
                        // console.log('Ajax response:', response);
                    } else {
                        error(response.error.message);
                        console.error('Ajax error: ' + response.error.message + '. code: ' + response.error.code);
                    }

                    cb();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    error();
                    console.error('Ajax server error: ' + textStatus + ': ' + errorThrown);
                    cb();
                }
            });
        } else {
            error("Please enter a valid score");
            cb();
        }
    }

    function success() {
        $challengeScore = $('#challenge-score');
        $successMessage = $('#success-message');
        $challengeScore.parent('.add-score').addClass('success');
        $successMessage.show();
        
        setTimeout(function () {
            $challengeScore.parent('.add-score').removeClass('success');
            $successMessage.hide();
        }, 1500);
    }
    function error(message = null) {
        $challengeScore = $('#challenge-score');
        $errorMessage = $('#error-message');
        var defaultMessage = $errorMessage.text();
        $challengeScore.parent('.add-score').addClass('error');
        if (message != null) {
            $errorMessage.text(message);
        }
        $errorMessage.show();
        
        setTimeout(function () {
            $challengeScore.parent('.add-score').removeClass('error');
            $errorMessage.hide();
            if (message != null) {
                $errorMessage.text(defaultMessage);
            }
        }, 2500);
    }
})(jQuery);