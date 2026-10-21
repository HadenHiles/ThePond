<?php
/**
 * Keeps Firebase Authentication in step with WordPress/MemberPress accounts.
 *
 * Email/password sign-ups used to create the Firebase user in the browser, which
 * silently failed for short passwords, blocked requests and some checkout paths,
 * leaving members with a WordPress account they could not sign in to.
 */
defined('ABSPATH') || exit;

const THEPOND_FIREBASE_MIN_PASSWORD = 6;

add_filter('mepr-validate-signup', 'thepond_firebase_validate_signup');
add_action('user_register', 'thepond_firebase_sync_signup', 20);
add_action('wp_ajax_thepond_firebase_recover', 'thepond_firebase_recover');
add_action('wp_ajax_nopriv_thepond_firebase_recover', 'thepond_firebase_recover');

function thepond_firebase_request($method, $body) {
    $key = get_option('mo_firebase_auth_api_key');
    if (empty($key)) {
        return new WP_Error('thepond_firebase_config', 'The Firebase API key is not configured.');
    }
    $response = wp_remote_post('https://identitytoolkit.googleapis.com/v1/accounts:' . $method . '?key=' . rawurlencode($key), array(
        'timeout' => 10,
        // Browser API keys are commonly restricted by HTTP referrer.
        'headers' => array('Content-Type' => 'application/json', 'Referer' => home_url('/')),
        'body' => wp_json_encode($body),
    ));
    if (is_wp_error($response)) {
        return $response;
    }
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if (wp_remote_retrieve_response_code($response) !== 200) {
        $message = isset($data['error']['message']) ? (string) $data['error']['message'] : 'HTTP ' . wp_remote_retrieve_response_code($response);
        return new WP_Error('thepond_firebase_api', $message);
    }
    return is_array($data) ? $data : array();
}

/**
 * Returns true when a Firebase user was created, false when one already exists.
 */
function thepond_firebase_ensure_user($email, $password) {
    $result = thepond_firebase_request('signUp', array('email' => $email, 'password' => $password, 'returnSecureToken' => false));
    if (!is_wp_error($result)) {
        return true;
    }
    return $result->get_error_message() === 'EMAIL_EXISTS' ? false : $result;
}

function thepond_firebase_send_password_reset($email) {
    $result = thepond_firebase_request('sendOobCode', array('requestType' => 'PASSWORD_RESET', 'email' => $email));
    return is_wp_error($result) ? $result : true;
}

function thepond_firebase_password_length($password) {
    return function_exists('mb_strlen') ? mb_strlen($password, 'UTF-8') : strlen($password);
}

function thepond_firebase_validate_signup($errors) {
    if (is_user_logged_in() || !isset($_POST['mepr_user_password']) || !is_string($_POST['mepr_user_password']) ||
        isset($errors['mepr_user_password'])) {
        return $errors;
    }
    $password = wp_unslash($_POST['mepr_user_password']);
    if ($password !== '' && thepond_firebase_password_length($password) < THEPOND_FIREBASE_MIN_PASSWORD) {
        $errors['mepr_user_password'] = sprintf(
            __('Your password must be at least %d characters long.', 'thepond-community'),
            THEPOND_FIREBASE_MIN_PASSWORD
        );
    }
    return $errors;
}

function thepond_firebase_sync_signup($user_id) {
    if (empty($_POST['mepr_user_password']) || !is_string($_POST['mepr_user_password'])) {
        return;
    }
    $user = get_userdata($user_id);
    if (!$user || !is_email($user->user_email)) {
        return;
    }
    // Firebase receives the password exactly as typed so browser sign-in matches.
    $result = thepond_firebase_ensure_user($user->user_email, wp_unslash($_POST['mepr_user_password']));
    if (is_wp_error($result)) {
        update_user_meta($user_id, 'thepond_firebase_sync_error', $result->get_error_message());
        error_log(sprintf('The Pond: Firebase account for user %d was not created: %s', $user_id, $result->get_error_message()));
    }
}

function thepond_firebase_rate_limited($bucket) {
    $key = 'thepond_fb_recover_' . md5($bucket);
    $attempts = (int) get_transient($key);
    if ($attempts >= 5) {
        return true;
    }
    set_transient($key, $attempts + 1, 15 * MINUTE_IN_SECONDS);
    return false;
}

function thepond_firebase_check_password($user, $password) {
    if (wp_check_password($password, $user->user_pass, $user->ID)) {
        return true;
    }
    // MemberPress hashes the slashed $_POST value, so passwords with quotes or backslashes differ.
    $slashed = wp_slash($password);
    return $slashed !== $password && wp_check_password($slashed, $user->user_pass, $user->ID);
}

/**
 * Self-heals members whose Firebase account is missing, either from the login form
 * (verified against their WordPress password) or the forgot-password form.
 */
function thepond_firebase_recover() {
    check_ajax_referer('thepond-firebase-recover', 'nonce');
    $email = isset($_POST['email']) && is_string($_POST['email']) ? sanitize_email(wp_unslash($_POST['email'])) : '';
    $mode = isset($_POST['mode']) && $_POST['mode'] === 'login' ? 'login' : 'reset';
    $ip = isset($_SERVER['REMOTE_ADDR']) ? (string) $_SERVER['REMOTE_ADDR'] : '';
    if (!is_email($email)) {
        wp_send_json_error(array('code' => 'invalid_email'), 400);
    }
    if (thepond_firebase_rate_limited('ip:' . $ip) || thepond_firebase_rate_limited('email:' . strtolower($email))) {
        wp_send_json_error(array('code' => 'rate_limited'), 429);
    }
    $user = get_user_by('email', $email);
    if (!$user) {
        wp_send_json_error(array('code' => 'not_found'), 404);
    }

    if ($mode === 'login') {
        $password = isset($_POST['password']) && is_string($_POST['password']) ? wp_unslash($_POST['password']) : '';
        if ($password === '' || !thepond_firebase_check_password($user, $password)) {
            do_action('wp_login_failed', $email, new WP_Error('incorrect_password'));
            wp_send_json_error(array('code' => 'invalid_credentials'), 403);
        }
        if (thepond_firebase_password_length($password) < THEPOND_FIREBASE_MIN_PASSWORD) {
            // Firebase cannot store this password; issue a reset so the member can choose a new one.
            $created = thepond_firebase_ensure_user($user->user_email, wp_generate_password(32));
            $sent = is_wp_error($created) ? $created : thepond_firebase_send_password_reset($user->user_email);
            if (is_wp_error($sent)) {
                error_log('The Pond: Firebase recovery failed for user ' . $user->ID . ': ' . $sent->get_error_message());
                wp_send_json_error(array('code' => 'firebase_error'), 502);
            }
            wp_send_json_success(array('created' => $created, 'reset_sent' => true));
        }
        $created = thepond_firebase_ensure_user($user->user_email, $password);
    } else {
        $created = thepond_firebase_ensure_user($user->user_email, wp_generate_password(32));
        if (!is_wp_error($created)) {
            $sent = thepond_firebase_send_password_reset($user->user_email);
            if (is_wp_error($sent)) {
                $created = $sent;
            }
        }
    }

    if (is_wp_error($created)) {
        error_log('The Pond: Firebase recovery failed for user ' . $user->ID . ': ' . $created->get_error_message());
        wp_send_json_error(array('code' => 'firebase_error'), 502);
    }
    delete_user_meta($user->ID, 'thepond_firebase_sync_error');
    wp_send_json_success(array('created' => $created, 'reset_sent' => $mode === 'reset'));
}
