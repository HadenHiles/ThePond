// Should only enqueue this on pages where it's needed 
(function ($) {
    $(document).ready(function () {
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
            var auth = firebase.auth();

            var user = JSON.parse(getCookie('fb_user'));

            /* New password redirect */
            // var checkFirebaseInitialized = setInterval(function () {
            //     if (window.location.href.includes("/account")) {
            //         if (auth.currentUser != null) {
            //             var passwordProviderData = auth.currentUser.providerData.filter((provider) => {
            //                 return provider.providerId == "password";
            //             });

            //             if (passwordProviderData.length < 1 && !window.location.href.includes(`action=newpassword`) && !window.location.href.includes(`/login`)) {
            //                 window.location.href = "/account/?action=newpassword";
            //             }

            //             clearInterval(checkFirebaseInitialized);
            //         }
            //     }
            // }, 100);

            /* Email/password sign up */
            var $signupForm = $('#register-form-wrapper .mepr-signup-form');
            if ($signupForm.length == 1) {
                var fbUserCreated = false;
                $(".mepr-signup-form .mepr-submit").click((e) => {
                    if (!fbUserCreated) {
                        e.preventDefault();
                        e.stopPropagation();
                        var email = $("#user_email1").val();
                        var pass = $("#mepr_user_password1").val();
                        var confirm_pass = $("#mepr_user_password_confirm1").val();
                        if (pass == confirm_pass) {
                            auth.createUserWithEmailAndPassword(email, pass)
                                .then(function (firebaseUser) {
                                    fbUserCreated = true;
                                    setCookie("fb_user", JSON.stringify(firebaseUser), 90);
                                    setCookie("request_path", "/login", 1);
                                    $(".mepr-signup-form .mepr-submit").trigger('click');
                                })
                                .catch(function (error) {
                                    console.error(error);
                                    fbUserCreated = true;
                                    setCookie("request_path", "/login", 1);
                                    $(".mepr-signup-form .mepr-submit").trigger('click');
                                });
                        }
                    }
                });
            }

            /* Password reset */
            if ($('#mepr_forgot_password_form').length == 1) {
                var sentFirebaseResetEmail = false;
                $('#mepr_forgot_password_form').submit((e) => {
                    if (!sentFirebaseResetEmail) {
                        e.preventDefault();
                        $('#mepr_forgot_password_form #wp-submit').attr('disabled', true);

                        $('#mepr_forgot_password_form #wp-submit').css({ 'filter': 'brightness(0.4)' });

                        if ($('#mepr_user_or_email').val().length > 0) {
                            auth.sendPasswordResetEmail($('#mepr_user_or_email').val(), null)
                                .then(function () {
                                    // Password reset email sent.
                                    sentFirebaseResetEmail = true;
                                    $('#pw_reset_result p').html('');
                                    $('#pw_reset_result p').html(`An email has been sent to ${$('#mepr_user_or_email').val()} with instructions on how to reset your password.`).css({ 'color': '#4BB543' });
                                    // $('#mepr_forgot_password_form').submit(); This sends password reset link through wordpress (not needed since we only allow signing in with firebase)
                                })
                                .catch(function (error) {
                                    var timeoutLength = 3000;
                                    console.warn(error);
                                    var errMsg = "Error sending password reset email.";

                                    // Set error.code specific error messages
                                    if (error.code == "auth/too-many-requests") {
                                        errMsg = "Too many reset attempts, please try again later.";
                                    } else if (error.code == "auth/invalid-email") {
                                        errMsg = "The email address is badly formatted.";
                                    } else if (error.code == "auth/user-not-found") {
                                        errMsg = `Email address not found.</br></br>If you believe this is a mistake, please contact <a href="mailto:thepondsupport@howtohockey.com">thepondsupport@howtohockey.com</a>`;
                                        timeoutLength = 10000;
                                    }

                                    $('#pw_reset_result p').html('');
                                    $('#pw_reset_result p').html(errMsg).css({ 'color': '#cc3333' });
                                    $('#mepr_forgot_password_form #wp-submit').attr('disabled', true);

                                    setTimeout(() => {
                                        $('#pw_reset_result p').html('').css({ 'color': '#000' });
                                        $('#mepr_forgot_password_form #wp-submit').attr('disabled', false);
                                        $('#mepr_forgot_password_form #wp-submit').removeAttr('style');
                                    }, timeoutLength);
                                });
                        }
                    } else {
                        $('#pw_reset_result p').html('').css({ 'color': '#000' });
                        $('#mepr_forgot_password_form #wp-submit').attr('disabled', false);
                        $('#mepr_forgot_password_form #wp-submit').removeAttr('style');
                    }
                });
            }

            /* Email update */
            if ($('#mepr_account_form').length == 1 && $('#mepr_account_form #user_email').length == 1) {
                var passwordProviders = user.providerData.filter((p) => p.providerId == "password");
                if (user == null || passwordProviders.length < 1) {
                    $(('<p id="email-update-disabled-message" style="margin: 0;">You must <a href="/account/?action=newpassword">set a password</a> before you can update your email address.</p>')).insertAfter('#mepr_account_form #user_email');
                    $('#mepr_account_form #user_email').attr('disabled', true).removeAttr('id');
                }

                var emailUpdated = false;
                var errorMsg = "Error updating email address. Please contact thepondsupport@howtohockey.com.";


                $('#mepr_account_form').submit((e) => {
                    if (!emailUpdated && $('#mepr_account_form #user_email').length == 1) {
                        e.preventDefault();
                        $('#error-msg').hide();
                        // Get the email address
                        var newEmail = $('#mepr_account_form #user_email').val();
                        // Update it if it's changed
                        if (auth.currentUser.email != newEmail) {
                            // update the user's email address in firebase and continue submitting the form
                            auth.currentUser.updateEmail(newEmail).then(() => {
                                emailUpdated = true;
                                console.log(`Email updated to: ${auth.currentUser.email}`);
                                $('#mepr_account_form').submit();
                            }).catch((error) => {
                                console.log(error);
                                errorMsg = `Error updating email address. ${error}`;
                                $('#firebase-err-msg').html(errorMsg);
                                $('#error-msg').show();
                            });
                        } else {
                            emailUpdated = true;
                            $('#mepr_account_form').submit();
                        }
                    }
                });
            }

            /* Password update */
            if ($('#mepr-newpassword-form').length == 1) {
                $('#mepr-newpassword-form').submit((e) => {
                    e.preventDefault();

                    if (auth.currentUser != null) {
                        var $newPass = $('#mepr-newpassword-form #mepr-new-password');
                        var $confirmPass = $('#mepr-newpassword-form #mepr-confirm-password');
                        var passwordStrength = $('#mepr-newpassword-form .mp-password-strength-display').text();
                        if (!$confirmPass.hasClass('invalid') &&
                            $confirmPass.val() != null &&
                            $confirmPass.val() == $newPass.val() &&
                            (passwordStrength.toLowerCase() == "strong" || passwordStrength.toLowerCase() == "very strong" || passwordStrength.toLowerCase() == "unbreakable")) {
                            // Get the password
                            var newPass = $confirmPass.val();
                            // update the user's password in firebase and continue submitting the form
                            auth.currentUser.updatePassword(newPass).then(() => {
                                console.log(`Password updated for: ${auth.currentUser.displayName}`);
                                $('#error-msg').hide();
                                $('#success-msg').show();

                                // Update the fb_user cookie so that the user can change their email address without having to logout/login again
                                var fbUser = JSON.parse(getCookie('fb_user'));
                                fbUser.providerData.push({ "providerId": "password" });
                                setCookie("fb_user", JSON.stringify(fbUser), 90);
                            }).catch((error) => {
                                $('#success-msg').hide();
                                $('#firebase-err-msg').text(error);
                                $('#error-msg').show();
                            });
                        } else {
                            console.log("Invalid password.");
                            var url = window.location.href;
                            url = new URL(url);
                            var error = url.searchParams.get("error");
                            if (error == null) {
                                window.location.href = window.location.href + "&error=weak";
                            }
                        }
                    } else {
                        $('#success-msg').hide();
                        $('#firebase-err-msg').text("This action is sensitive and your session is stale. Please logout, login, and try again.");
                        $('#error-msg').show();
                    }
                });
            }
        }
    });
})(jQuery);

function setCookie(name, value, minutes) {
    var expires = "";
    if (minutes) {
        var date = new Date();
        date.setTime(date.getTime() + (minutes * 60 * 1000));
        expires = "; expires=" + date.toUTCString();
    }
    document.cookie = name + "=" + (value || "") + expires + "; path=/";
}
function getCookie(name) {
    var nameEQ = name + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ') c = c.substring(1, c.length);
        if (c.indexOf(nameEQ) == 0) return c.substring(nameEQ.length, c.length);
    }
    return null;
}
function eraseCookie(name) {
    document.cookie = name + '=; Path=/; Expires=Thu, 01 Jan 1970 00:00:01 GMT;';
}