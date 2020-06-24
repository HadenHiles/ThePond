(function($) {

    $(document).ready(function() {
        // Get scores
        getScores();

        // Add score
        var $addButton = $('#add-score');
        $addButton.click(function(e) {
            e.preventDefault();
            $addButton.html('<i class="fa fa-spinner fa-spin"></i>');
            addScore(function(success = true) {
                if (success) {
                    getScores(true);
                }
                $addButton.html('<i class="fa fa-plus-circle"></i>');
            });
        });

        // Delete score
        $('.scores').on('click', '.delete-challenge-score', function(e) {
            e.preventDefault();
            var $this = $(this);
            var scoreId = parseInt($(this).data('challenge-id'));
            $this.parent('.score').addClass('deleting');
            $this.html('<i class="fa fa-spinner fa-spin"></i>');
            deleteScore(scoreId, function() {
                $this.parent('.score').remove();
                if ($('#scores').children('.score').length == 0) {
                    $('#scores').html('<p class="empty-result">You haven\'t entered any scores yet!');
                }
                $this.html('<i class="fa fa-trash"></i>');
            });
        });
    });


    function getScores (spinner = false) {
        var challengeId = $('input#challenge-id').val();
        var userId = $('input#user-id').val();
        var $scores = $('#scores');
        
        var data = {
            action: 'get_challenge_scores',
            challenge_id: challengeId,
            user_id: userId
        };

        if (spinner) {
            $scores.html('<i class="fa fa-spinner fa-spin" style="align-self: center; margin: 4px auto;"></i>');
        }

        $.ajax({
            url: ajaxurl, // this will point to admin-ajax.php
            type: 'POST',
            data: data,
            success: function (response) {
                var scoresHtml = '';
                for (const [key, value] of Object.entries(response.data)) {
                    var score = parseFloat(value.score.toString());
                    scoresHtml += '<div class="score">' + score + ' <a href="#" class="delete-challenge-score" data-challenge-id=' + value.id + '><i class="fa fa-trash"></i></a></div>\n';
                }

                $scores.html(scoresHtml);
                if ($('#scores').children('.score').length == 0) {
                    $scores.html('<p class="empty-result">You haven\'t entered any scores yet!');
                }
                // console.log('Ajax response:', response);
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
                    cb(false);
                }
            });
        } else {
            error("Please enter a valid score");
            cb(false);
        }
    }

    function deleteScore (scoreId, cb) {
        var userId = $('input#user-id').val();

        if(userId != null && scoreId != null) {
            var data = {
                action: 'delete_challenge_score',
                user_id: userId,
                score_id: scoreId
            };
    
            $.ajax({
                url: ajaxurl, // this will point to admin-ajax.php
                type: 'POST',
                data: data,
                success: function (response) {
                    if (response.error == null) {
                        console.log('Ajax response:', response);
                    } else {
                        console.error('Ajax error: ' + response.error.message + '. code: ' + response.error.code);
                    }

                    cb();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Ajax server error: ' + textStatus + ': ' + errorThrown);
                    cb();
                }
            });
        } else {
            cb();
        }
    }

    function success() {
        $challengeScore = $('#challenge-score');
        $successMessage = $('#success-message');
        $challengeScore.parent('.add-score').addClass('success');
        $successMessage.show();
        $challengeScore.val('');
        
        setTimeout(function () {
            $challengeScore.parent('.add-score').removeClass('success');
            $successMessage.hide();
        }, 2500);
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