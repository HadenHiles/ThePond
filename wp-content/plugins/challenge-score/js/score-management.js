(function($) {
    $(document).ready(function() {
        // Only load this code if on the single content library challenge page
        if ($('#challenge-scores').length > 0) {
            // Get scores
            getScores();

            // Add score
            var $addButton = $('#add-score');
            $addButton.click(function(e) {
                e.preventDefault();
                addScore(function(success = true) {
                    if (success) {
                        getScores(true);
                    }
                    $addButton.html('<i class="fa fa-plus-circle"></i>');
                });
            });
            // Add score on enter keypress
            $('input#challenge-score').keypress(function (e) {
                if (e.which == 13) {
                    addScore(function(success = true) {
                        if (success) {
                            getScores(true);
                        }
                        $addButton.html('<i class="fa fa-plus-circle"></i>');
                    });

                    return false;
                }
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
        }
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
            showSpinner();
        }

        $.ajax({
            url: ajaxurl, // this will point to admin-ajax.php
            type: 'POST',
            data: data,
            success: function (response) {
                var scoresHtml = '';
                for (const [key, value] of Object.entries(response.data)) {
                    var score = parseFloat(value.score.toString());
                    // parse the raw datetime coming from MySql
                    var date = new Date(Date.parse(value.date));
                    // format the date so that we can append UTC for local browser timezone support
                    var date = formatDateMonthDayYear(date) + " " + formatAMPM(date);
                    // append UTC
                    var date = new Date(date + " UTC");
                    // format the date again for visual appeal
                    var date = formatDateMonthDayYear(date) + " " + formatAMPM(date);
                    scoresHtml += '<div class="score"><div class="number">' + score + '</div>  <span class="datetime">' + date + '</span> <a href="#" class="delete-challenge-score" data-challenge-id=' + value.id + '><i class="fa fa-trash"></i></a></div>\n';
                }

                $scores.html(scoresHtml);
                if ($('#scores').children('.score').length == 0) {
                    $scores.html('<p class="empty-result">You haven\'t entered any scores yet!');
                }

                $('#scores').css('min-height', '0px');

                // console.log('Ajax response:', response);
            }
        });
    }

    function addScore (cb) {
        var $addButton = $('#add-score');
        var challengeId = $('input#challenge-id').val();
        var userId = $('input#user-id').val();
        var score = parseFloat($('input#challenge-score').val()).toFixed(4);
        
        if(challengeId != null && userId != null && score >= 0) {
            $addButton.html('<i class="fa fa-spinner fa-spin"></i>');
            showSpinner();

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
                        cb();
                        // console.log('Ajax response:', response);
                    } else {
                        error(response.error.message);
                        console.error('Ajax error: ' + response.error.message + '. code: ' + response.error.code);
                        cb(false);
                    }
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
                url: "/wp-admin/admin-ajax.php",
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
    function showSpinner() {
        var height = $('#scores').outerHeight();
        $('#scores').css('min-height', height + 'px');
        $('#scores').html('<i class="fa fa-spinner fa-spin" style="align-self: center; margin: 2% auto;"></i>');
    }
})(jQuery);

// Date formatting
function formatDateMonthDayYear(d) {
    return d.getMonth()+1 + "/" + d.getDate() + "/" + d.getFullYear();
}
function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0'+minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
  }