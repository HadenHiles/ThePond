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

            /* Password reset */
            if ($('#mepr_forgot_password_form').length == 1) {
                $('#mepr_forgot_password_form').submit((e) => {
                    e.preventDefault();
                    $('#mepr_forgot_password_form #wp-submit').attr('disabled', true);

                    if ($('#mepr_user_or_email').val().length > 0) {
                        auth.sendPasswordResetEmail($('#mepr_user_or_email').val(), null)
                            .then(function () {
                                // Password reset email sent.
                                $('#pw_reset_result p').html(`An email has been sent to ${$('#mepr_user_or_email').val()} with instructions on how to reset your password.`).css({ 'color': '#4BB543' });
                                $('#mepr_forgot_password_form').submit();
                            })
                            .catch(function (error) {
                                var errMsg = "Error sending password reset email.";

                                // Set error.code specific error messages
                                if (error.code == "auth/too-many-requests") {
                                    errMsg = "Too many reset attempts, please try again later.";
                                }

                                $('#pw_reset_result p').html('');
                                $('#pw_reset_result p').html(errMsg).css({ 'color': '#cc3333' });
                                $('#mepr_forgot_password_form #wp-submit').attr('disabled', true);

                                setTimeout(() => {
                                    $('#pw_reset_result p').html('').css({ 'color': '#000' });
                                    $('#mepr_forgot_password_form #wp-submit').attr('disabled', false);
                                }, 10000);
                            });
                    }
                });
            }

            /* Email update */
            if ($('#mepr_account_form').length == 1) {
                $('#mepr_account_form').submit((e) => {
                    // Get the email address
                    var newEmail = $('#mepr_account_form #user_email').val();
                    // Update it if it's changed
                    if (auth.currentUser.email != newEmail) {
                        // update the user's email address in firebase and continue submitting the form
                        auth.currentUser.updateEmail(newEmail).then(() => {
                            console.log(`Email updated to: ${auth.currentUser.email}`);
                            $('#mepr_account_form').submit();
                        });
                    }
                });
            }

            /* Password update */
            if ($('#mepr-newpassword-form').length == 1) {
                $('#mepr-newpassword-form').submit((e) => {
                    e.preventDefault();

                    var $newPass = $('#mepr-newpassword-form #mepr-new-password');
                    var $confirmPass = $('#mepr-newpassword-form #mepr_user_password_confirm');
                    var passwordStrength = $('#mepr-newpassword-form .mp-password-strength-display').text();
                    if (!$confirmPass.hasClass('invalid') && 
                        $confirmPass.val() != null && 
                        $confirmPass.val() == $newPass.val() &&
                        (passwordStrength.toLowerCase() == "medium" || passwordStrength.toLowerCase() == "strong")) {
                        // Get the password
                        var newPass = $confirmPass.val();
                        // update the user's password in firebase and continue submitting the form
                        auth.currentUser.updatePassword(newPass).then(() => {
                            console.log(`Password updated for: ${auth.currentUser.displayName}`);
                            $('#mepr-newpassword-form').submit();
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
                });
            }
        }
    });
})(jQuery);