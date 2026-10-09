(function ($) {
    $(document).ready(function () {
        // Only load this code if on the single content library challenge page
        if ($('#challenge-scores').length > 0 && $('#challenge-id').length > 0) {
            // Firebase initialization
            var a_key = "AIzaSyCoSWim4GptSro0gly6dN8dClVQMcxeCbA";
            var pid = "the-pond-app";
            var firebaseConfig = {
                apiKey: a_key,
                authDomain: pid + '.firebaseapp.com',
                databaseURL: 'https://' + pid + '.firebaseio.com',
                projectId: pid,
                storageBucket: ''
            };

            // Initialize Firebase
            if (!firebase.apps.length) {
                firebase.initializeApp(firebaseConfig);
            }
            var auth = firebase.auth();
            var db = firebase.firestore();
            // var user = JSON.parse(getCookie('fb_user')).user;
            auth.onAuthStateChanged(user => {
                $('#add-score, #challenge-score').prop('disabled', !user);
                $('#add-score').off('click.pondScores');
                $('#challenge-score').off('keypress.pondScores');
                $('.scores').off('click.pondScores', '.delete-challenge-score');
                if (user) {
                    // User is signed in.
                    // Get scores
                    getScores();

                    // Add score
                    var $addButton = $('#add-score');
                    $addButton.on('click.pondScores', function (e) {
                        e.preventDefault();
                        if ($addButton.prop('disabled')) return;
                        addScore(function (success = true) {
                            if (success) {
                                getScores(true);
                            }
                            $addButton.html('<i class="fa fa-plus-circle"></i>');
                            $addButton.prop('disabled', false);
                        });
                    });
                    // Add score on enter keypress
                    $('input#challenge-score').on('keypress.pondScores', function (e) {
                        if (e.which == 13) {
                            if ($addButton.prop('disabled')) return false;
                            addScore(function (success = true) {
                                if (success) {
                                    getScores(true);
                                }
                                $addButton.html('<i class="fa fa-plus-circle"></i>');
                                $addButton.prop('disabled', false);
                            });

                            return false;
                        }
                    });

                    // Delete score
                    $('.scores').on('click.pondScores', '.delete-challenge-score', function (e) {
                        e.preventDefault();
                        var $this = $(this);
                        if ($this.parent('.score').hasClass('deleting')) return;
                        var scoreId = $(this).data('challenge-id');
                        $this.parent('.score').addClass('deleting');
                        $this.html('<i class="fa fa-spinner fa-spin"></i>');
                        deleteScore(scoreId, function (success) {
                            if (success) $this.parent('.score').remove();
                            else $this.parent('.score').removeClass('deleting');
                            if ($('#scores').children('.score').length == 0) {
                                $('#scores').html('<p class="empty-result">You haven\'t entered any scores yet!</p>');
                            }
                            $this.html('<i class="fa fa-trash"></i>');
                        });
                    });

                    function getScores(spinner = false) {
                        var challengeId = parseInt($('input#challenge-id').val());
                        var $scores = $('#scores');

                        if (spinner) {
                            showSpinner();
                        }

                        db.collection("challenge_scores").doc(user.uid).collection("challenge_scores").where("challenge_id", "==", challengeId).orderBy('created')
                            .get()
                            .then((querySnapshot) => {
                                var scoresHtml = '';
                                querySnapshot.forEach((doc) => {
                                    var data = doc.data();
                                    // format the date for visual appeal
                                    var date = new Date(data.created.toDate());
                                    var date = formatDateMonthDayYear(date) + " " + formatAMPM(date);
                                    scoresHtml += '<div class="score"><div class="number">' + data.score + '</div>  <span class="datetime">' + date + '</span> <a href="#" class="delete-challenge-score" aria-label="Delete score" data-challenge-id="' + doc.id + '"><i class="fa fa-trash" aria-hidden="true"></i></a></div>\n';
                                });

                                $scores.html(scoresHtml);
                                if ($('#scores').children('.score').length == 0) {
                                    $scores.html('<p class="empty-result">You haven\'t entered any scores yet!</p>');
                                }

                                $('#scores').css('min-height', '0px');
                            }).catch(function (cause) {
                                console.error("Error loading challenge_scores: ", cause);
                                $scores.html('<p class="empty-result">Unable to load your scores. Please reload to try again.</p>');
                                $scores.css('min-height', '0px');
                                error(cause.message);
                            });
                    }

                    function addScore(cb) {
                        var $addButton = $('#add-score');
                        var challengeId = $('input#challenge-id').val();
                        var userId = $('input#user-id').val();
                        var score = parseFloat($('input#challenge-score').val()).toFixed(4);

                        if (challengeId != null && userId != null && Number.isFinite(Number(score)) && score >= 0) {
                            $addButton.prop('disabled', true);
                            $addButton.html('<i class="fa fa-spinner fa-spin"></i>');
                            showSpinner();

                            // Save to firestore
                            db.collection('challenge_scores').doc(user.uid).collection('challenge_scores').add({
                                'challenge_id': parseInt(challengeId),
                                'wp_user_id': parseInt(userId),
                                'score': parseFloat(score),
                                'created': new Date(),
                            }).then(function (doc) {
                                console.log("challenge_score saved with ID: ", doc.id);
                                success();
                                cb();
                            }).catch(function (cause) {
                                console.error("Error adding challenge_score: ", cause);
                                error(cause.message);
                                getScores();
                                cb(false);
                            });
                        } else {
                            error("Please enter a valid score");
                            cb(false);
                        }
                    }

                    function deleteScore(scoreId, cb) {
                        if (scoreId != null) {
                            db.collection('challenge_scores').doc(user.uid).collection('challenge_scores').doc(scoreId).delete().then(() => {
                                console.log(`challenge_score ${scoreId} successfully deleted!`);
                                cb(true);
                            }).catch((cause) => {
                                console.error(`Error removing challenge_score: ${scoreId}`, cause);
                                error(cause.message);
                                cb(false);
                            });
                        } else {
                            error("Unable to identify this score");
                            cb(false);
                        }
                    }
                } else {
                    $('#scores').html('<p class="empty-result">Sign in with your member login to load and save your scores.</p>');
                }
            });

            function success() {
                var $challengeScore = $('#challenge-score');
                var $successMessage = $('#success-message');
                $('#error-message').hide();
                $challengeScore.parent('.add-score').addClass('success');
                $successMessage.show();
                $challengeScore.val('');

                setTimeout(function () {
                    $challengeScore.parent('.add-score').removeClass('success');
                    $successMessage.hide();
                }, 2500);
            }

            function error(message = null) {
                var $challengeScore = $('#challenge-score');
                var $errorMessage = $('#error-message');
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
        }
    });
})(jQuery);

// Date formatting
function toDateTime(secs) {
    var t = new Date(1970, 0, 1); // Epoch
    t.setSeconds(secs);
    return t;
}
function formatDateMonthDayYear(d) {
    return d.getMonth() + 1 + "/" + d.getDate() + "/" + d.getFullYear();
}
function formatAMPM(date) {
    var hours = date.getHours();
    var minutes = date.getMinutes();
    var ampm = hours >= 12 ? 'pm' : 'am';
    hours = hours % 12;
    hours = hours ? hours : 12; // the hour '0' should be '12'
    minutes = minutes < 10 ? '0' + minutes : minutes;
    var strTime = hours + ':' + minutes + ' ' + ampm;
    return strTime;
}