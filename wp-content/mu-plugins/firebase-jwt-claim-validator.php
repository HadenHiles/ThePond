<?php

/**
 * Plugin Name: The Pond — Firebase JWT Claim Validator
 * Description: Validates Firebase ID token claims (exp, iss, aud) before the
 *              miniOrange Firebase plugin processes them. Closes the missing
 *              claim-validation gap without modifying the licensed plugin.
 */

defined('WPINC') || die;

// Allow override via wp-config.php if the project ever changes.
if (! defined('THEPOND_FIREBASE_PROJECT_ID')) {
    define('THEPOND_FIREBASE_PROJECT_ID', 'the-pond-app');
}

/**
 * Run at priority 5 — before miniOrange hooks at the default priority 10.
 * Rejects tokens that fail claim validation before signature verification runs.
 */
add_action('init', 'thepond_prevalidate_firebase_jwt', 5);

function thepond_prevalidate_firebase_jwt() {
    if (! isset($_POST['fb_jwt'])) {
        return;
    }

    $raw_jwt = sanitize_text_field(wp_unslash($_POST['fb_jwt']));

    // 'empty_string' signals a client-side Firebase auth failure; let miniOrange handle it.
    if ($raw_jwt === 'empty_string') {
        return;
    }

    $parts = explode('.', $raw_jwt);
    if (count($parts) !== 3) {
        thepond_firebase_reject_jwt('Malformed JWT: expected 3 segments, got ' . count($parts));
        return;
    }

    // Decode claims — signature is intentionally verified later by miniOrange via openssl_verify.
    $payload = json_decode(
        base64_decode(strtr($parts[1], '-_', '+/')),
        true
    );

    if (! is_array($payload)) {
        thepond_firebase_reject_jwt('JWT payload could not be decoded.');
        return;
    }

    $now    = time();
    $proj   = THEPOND_FIREBASE_PROJECT_ID;
    $issuer = 'https://securetoken.google.com/' . $proj;

    // Expiry — most critical: prevents replay of intercepted or stolen tokens.
    if (empty($payload['exp']) || (int) $payload['exp'] < $now) {
        thepond_firebase_reject_jwt('Token is expired (exp=' . ($payload['exp'] ?? 'missing') . ').');
        return;
    }

    // Clock skew tolerance: reject tokens issued more than 5 minutes in the future.
    if (isset($payload['iat']) && (int) $payload['iat'] > $now + 300) {
        thepond_firebase_reject_jwt('Token iat is in the future (iat=' . $payload['iat'] . ').');
        return;
    }

    // Issuer must be this project's Firebase securetoken endpoint.
    $token_iss = $payload['iss'] ?? '';
    if ($token_iss !== $issuer) {
        thepond_firebase_reject_jwt('Invalid issuer: ' . $token_iss);
        return;
    }

    // Audience must match this project ID — prevents cross-project token abuse.
    $token_aud = $payload['aud'] ?? '';
    if ($token_aud !== $proj) {
        thepond_firebase_reject_jwt('Invalid audience: ' . $token_aud);
        return;
    }

    // All claims valid — miniOrange will complete RSA signature verification and log the user in.
}

function thepond_firebase_reject_jwt($reason) {
    error_log('[The Pond] Firebase JWT pre-validation rejected: ' . $reason);

    // Return the user to wherever they came from; the login page JS will display the error UI.
    $back = wp_get_referer();
    if (! $back) {
        $back = home_url('/login/');
    }
    wp_safe_redirect($back);
    exit;
}
