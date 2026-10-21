<?php
defined('ABSPATH') || exit;

const THEPOND_ANALYTICS_DATA_API = 'https://analyticsdata.googleapis.com/v1beta/';
const THEPOND_ANALYTICS_ADMIN_API = 'https://analyticsadmin.googleapis.com/v1beta/';
const THEPOND_ANALYTICS_CACHE_GROUP = 'thepond_analytics_cache_keys';

/**
 * Custom dimensions the reports query. Event-scoped visitor_type reflects who
 * the visitor was at the time of each event, so signups aren't relabelled
 * as members after the fact.
 */
function thepond_analytics_custom_dimensions() {
    return array(
        array('visitor_type', 'Visitor type', 'EVENT', 'prospect, member, former_member'),
        array('member_status', 'Member status', 'USER', 'Latest visitor type for the user'),
        array('page_type', 'Pond page type', 'EVENT', 'landing, register, login, member_dashboard, content...'),
        array('cta', 'CTA label', 'EVENT', 'Text of the landing page link that was clicked'),
        array('cta_group', 'CTA group', 'EVENT', 'signup, plans, free_lessons, login, support, navigation, other'),
        array('cta_location', 'CTA location', 'EVENT', 'Landing page section containing the link'),
        array('section', 'Landing section', 'EVENT', 'Landing page section scrolled into view'),
        array('plan', 'Membership plan', 'EVENT', 'MemberPress membership slug'),
        array('signup_method', 'Signup method', 'EVENT', 'google, facebook or email'),
        array('method', 'Login method', 'EVENT', 'google, facebook or email'),
        array('content_type', 'Content type', 'EVENT', 'Post type of the member content'),
        array('content_title', 'Content title', 'EVENT', 'Title of the member content'),
        array('course', 'Course', 'EVENT', 'LearnDash course for a lesson or topic'),
        array('dashboard_area', 'Dashboard area', 'EVENT', 'Member dashboard section that was clicked'),
        array('faq_question', 'FAQ question', 'EVENT', 'FAQ question that was opened'),
    );
}

function thepond_analytics_key_events() {
    return array('purchase', 'begin_checkout', 'sign_up');
}

function thepond_analytics_service_account() {
    if (defined('THEPOND_ANALYTICS_SERVICE_ACCOUNT')) {
        $source = THEPOND_ANALYTICS_SERVICE_ACCOUNT;
        $json = is_readable($source) ? file_get_contents($source) : $source;
        $data = json_decode((string) $json, true);
    } else {
        $data = get_option(THEPOND_ANALYTICS_SA_OPTION);
    }
    if (!is_array($data) || empty($data['client_email']) || empty($data['private_key'])) {
        return null;
    }
    return $data;
}

function thepond_analytics_google_connected() {
    return thepond_analytics_service_account() && thepond_analytics_setting('property_id');
}

function thepond_analytics_base64url($data) {
    return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
}

function thepond_analytics_jwt(array $account, $scope, $now) {
    $header = thepond_analytics_base64url(wp_json_encode(array('alg' => 'RS256', 'typ' => 'JWT')));
    $claims = thepond_analytics_base64url(wp_json_encode(array(
        'iss' => $account['client_email'],
        'scope' => $scope,
        'aud' => 'https://oauth2.googleapis.com/token',
        'iat' => $now,
        'exp' => $now + HOUR_IN_SECONDS,
    )));
    $signature = '';
    if (!openssl_sign($header . '.' . $claims, $signature, $account['private_key'], 'sha256WithRSAEncryption')) {
        return new WP_Error('jwt_sign_failed', __('Could not sign the Google login request with the service account key.', 'thepond-analytics'));
    }
    return $header . '.' . $claims . '.' . thepond_analytics_base64url($signature);
}

function thepond_analytics_google_token() {
    $account = thepond_analytics_service_account();
    if (!$account) {
        return new WP_Error('no_service_account', __('Add a Google service account key in Settings → Pond Analytics.', 'thepond-analytics'));
    }
    $cache_key = 'thepond_analytics_token_' . md5($account['client_email'] . ($account['private_key_id'] ?? ''));
    $token = get_transient($cache_key);
    if ($token) {
        return $token;
    }
    $jwt = thepond_analytics_jwt($account, 'https://www.googleapis.com/auth/analytics.edit https://www.googleapis.com/auth/analytics.readonly', time());
    if (is_wp_error($jwt)) {
        return $jwt;
    }
    $response = wp_remote_post('https://oauth2.googleapis.com/token', array(
        'timeout' => 15,
        'body' => array(
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion' => $jwt,
        ),
    ));
    if (is_wp_error($response)) {
        return $response;
    }
    $body = json_decode(wp_remote_retrieve_body($response), true);
    if (empty($body['access_token'])) {
        $detail = $body['error_description'] ?? ($body['error'] ?? 'HTTP ' . wp_remote_retrieve_response_code($response));
        return new WP_Error('google_token', sprintf(__('Google rejected the service account: %s', 'thepond-analytics'), $detail));
    }
    set_transient($cache_key, $body['access_token'], max(60, (int) ($body['expires_in'] ?? 3600) - 300));
    return $body['access_token'];
}

function thepond_analytics_google_request($method, $url, $body = null) {
    $token = thepond_analytics_google_token();
    if (is_wp_error($token)) {
        return $token;
    }
    $args = array(
        'method' => $method,
        'timeout' => 20,
        'headers' => array(
            'Authorization' => 'Bearer ' . $token,
            'Content-Type' => 'application/json',
        ),
    );
    if ($body !== null) {
        $args['body'] = wp_json_encode($body);
    }
    $response = wp_remote_request($url, $args);
    if (is_wp_error($response)) {
        return $response;
    }
    $code = (int) wp_remote_retrieve_response_code($response);
    $data = json_decode(wp_remote_retrieve_body($response), true);
    if ($code < 200 || $code >= 300) {
        $message = $data['error']['message'] ?? ('HTTP ' . $code);
        return new WP_Error('google_api', $message, array('status' => $code));
    }
    return is_array($data) ? $data : array();
}

function thepond_analytics_property_path() {
    return 'properties/' . preg_replace('/\D/', '', (string) thepond_analytics_setting('property_id'));
}

/**
 * Runs up to five Data API reports in one request. Results are cached for
 * three hours; GA data takes a few hours to settle anyway.
 */
function thepond_analytics_batch_reports(array $requests, $force = false) {
    $key = 'thepond_analytics_ga_' . md5(thepond_analytics_property_path() . wp_json_encode($requests));
    if (!$force) {
        $cached = get_transient($key);
        if ($cached !== false) {
            return $cached;
        }
    }
    $response = thepond_analytics_google_request(
        'POST',
        THEPOND_ANALYTICS_DATA_API . thepond_analytics_property_path() . ':batchRunReports',
        array('requests' => array_values($requests))
    );
    if (is_wp_error($response)) {
        return $response;
    }
    $reports = array();
    foreach ($response['reports'] ?? array() as $index => $report) {
        $reports[] = thepond_analytics_report_rows($report);
    }
    set_transient($key, $reports, 3 * HOUR_IN_SECONDS);
    thepond_analytics_remember_cache_key($key);
    return $reports;
}

function thepond_analytics_remember_cache_key($key) {
    $keys = get_option(THEPOND_ANALYTICS_CACHE_GROUP, array());
    $keys = is_array($keys) ? $keys : array();
    $keys[$key] = time();
    // Keep the index bounded; stale entries expire on their own.
    if (count($keys) > 200) {
        asort($keys);
        $keys = array_slice($keys, -200, null, true);
    }
    update_option(THEPOND_ANALYTICS_CACHE_GROUP, $keys, false);
}

function thepond_analytics_flush_cache() {
    $keys = get_option(THEPOND_ANALYTICS_CACHE_GROUP, array());
    foreach (is_array($keys) ? array_keys($keys) : array() as $key) {
        delete_transient($key);
    }
    delete_option(THEPOND_ANALYTICS_CACHE_GROUP);
}

/**
 * Flattens a GA report into rows keyed by dimension/metric name.
 */
function thepond_analytics_report_rows(array $report) {
    $dimensions = array_map(function ($header) {
        return $header['name'];
    }, $report['dimensionHeaders'] ?? array());
    $metrics = array_map(function ($header) {
        return $header['name'];
    }, $report['metricHeaders'] ?? array());
    $rows = array();
    foreach ($report['rows'] ?? array() as $row) {
        $item = array();
        foreach ($dimensions as $i => $name) {
            $item[$name] = $row['dimensionValues'][$i]['value'] ?? '';
        }
        foreach ($metrics as $i => $name) {
            $item[$name] = (float) ($row['metricValues'][$i]['value'] ?? 0);
        }
        $rows[] = $item;
    }
    return $rows;
}

function thepond_analytics_test_connection() {
    $lines = array();
    if (!thepond_analytics_service_account()) {
        return array('ok' => false, 'lines' => array(__('No service account key has been saved yet.', 'thepond-analytics')));
    }
    $metadata = thepond_analytics_google_request('GET', THEPOND_ANALYTICS_ADMIN_API . thepond_analytics_property_path());
    if (is_wp_error($metadata)) {
        return array('ok' => false, 'lines' => array(sprintf(__('Could not open the GA property: %s', 'thepond-analytics'), $metadata->get_error_message())));
    }
    $lines[] = sprintf(__('Connected to "%s".', 'thepond-analytics'), $metadata['displayName'] ?? thepond_analytics_property_path());
    $report = thepond_analytics_batch_reports(array(array(
        'dateRanges' => array(array('startDate' => '7daysAgo', 'endDate' => 'today')),
        'metrics' => array(array('name' => 'activeUsers')),
    )), true);
    if (is_wp_error($report)) {
        return array('ok' => false, 'lines' => array_merge($lines, array(sprintf(__('Reading reports failed: %s', 'thepond-analytics'), $report->get_error_message()))));
    }
    $users = (int) ($report[0][0]['activeUsers'] ?? 0);
    $lines[] = sprintf(__('Reports work: %d visitors in the last 7 days.', 'thepond-analytics'), $users);
    $lines[] = thepond_analytics_setting('api_secret')
        ? __('Signup tracking (Measurement Protocol) is configured.', 'thepond-analytics')
        : __('Signup tracking still needs a Measurement Protocol secret. Run "Set up Google Analytics".', 'thepond-analytics');
    return array('ok' => true, 'lines' => $lines);
}

function thepond_analytics_ga_setup() {
    $lines = array();
    $ok = true;
    $property = thepond_analytics_property_path();

    $existing = thepond_analytics_google_request('GET', THEPOND_ANALYTICS_ADMIN_API . $property . '/customDimensions?pageSize=200');
    if (is_wp_error($existing)) {
        return array('ok' => false, 'lines' => array(sprintf(__('Could not read custom dimensions: %s. Make sure the service account has Editor access to the property.', 'thepond-analytics'), $existing->get_error_message())));
    }
    $have = array();
    foreach ($existing['customDimensions'] ?? array() as $dimension) {
        $have[$dimension['scope'] . ':' . $dimension['parameterName']] = true;
    }
    $created = 0;
    foreach (thepond_analytics_custom_dimensions() as $dimension) {
        list($parameter, $label, $scope, $description) = $dimension;
        if (isset($have[$scope . ':' . $parameter])) {
            continue;
        }
        $result = thepond_analytics_google_request('POST', THEPOND_ANALYTICS_ADMIN_API . $property . '/customDimensions', array(
            'parameterName' => $parameter,
            'displayName' => $label,
            'scope' => $scope,
            'description' => $description,
        ));
        if (is_wp_error($result)) {
            $ok = false;
            $lines[] = sprintf(__('Custom dimension "%1$s" failed: %2$s', 'thepond-analytics'), $label, $result->get_error_message());
        } else {
            $created++;
        }
    }
    $lines[] = sprintf(__('Custom dimensions ready (%d created).', 'thepond-analytics'), $created);

    $key_events = thepond_analytics_google_request('GET', THEPOND_ANALYTICS_ADMIN_API . $property . '/keyEvents?pageSize=200');
    $have_events = array();
    if (!is_wp_error($key_events)) {
        foreach ($key_events['keyEvents'] ?? array() as $event) {
            $have_events[$event['eventName']] = true;
        }
    }
    foreach (thepond_analytics_key_events() as $event_name) {
        if (isset($have_events[$event_name])) {
            continue;
        }
        $result = thepond_analytics_google_request('POST', THEPOND_ANALYTICS_ADMIN_API . $property . '/keyEvents', array(
            'eventName' => $event_name,
            'countingMethod' => 'ONCE_PER_EVENT',
        ));
        if (is_wp_error($result)) {
            $ok = false;
            $lines[] = sprintf(__('Key event "%1$s" failed: %2$s', 'thepond-analytics'), $event_name, $result->get_error_message());
        }
    }
    $lines[] = __('Key events ready: purchase, begin_checkout, sign_up.', 'thepond-analytics');

    $secret = thepond_analytics_setup_mp_secret($property);
    if (is_wp_error($secret)) {
        $ok = false;
        $lines[] = sprintf(__('Measurement Protocol secret: %s', 'thepond-analytics'), $secret->get_error_message());
    } else {
        $lines[] = $secret;
    }

    thepond_analytics_flush_cache();
    return array('ok' => $ok, 'lines' => $lines);
}

function thepond_analytics_setup_mp_secret($property) {
    $measurement_id = thepond_analytics_setting('measurement_id');
    $streams = thepond_analytics_google_request('GET', THEPOND_ANALYTICS_ADMIN_API . $property . '/dataStreams?pageSize=50');
    if (is_wp_error($streams)) {
        return $streams;
    }
    $stream = null;
    foreach ($streams['dataStreams'] ?? array() as $candidate) {
        if (($candidate['webStreamData']['measurementId'] ?? '') === $measurement_id) {
            $stream = $candidate['name'];
        }
    }
    if (!$stream) {
        return new WP_Error('no_stream', sprintf(__('No web stream with measurement ID %s was found in this property.', 'thepond-analytics'), $measurement_id));
    }

    $list = thepond_analytics_google_request('GET', THEPOND_ANALYTICS_ADMIN_API . $stream . '/measurementProtocolSecrets');
    if (!is_wp_error($list)) {
        foreach ($list['measurementProtocolSecrets'] ?? array() as $existing) {
            if (($existing['displayName'] ?? '') === 'The Pond signups' && !empty($existing['secretValue'])) {
                thepond_analytics_update_settings(array('api_secret' => $existing['secretValue']));
                return __('Measurement Protocol secret already existed and is saved.', 'thepond-analytics');
            }
        }
    }

    thepond_analytics_google_request('POST', THEPOND_ANALYTICS_ADMIN_API . $property . ':acknowledgeUserDataCollection', array(
        'acknowledgement' => 'I acknowledge that I have the necessary privacy disclosures and rights from my end users for the collection and processing of their data, including the association of such data with the visitation information Google Analytics collects from my site and/or app property.',
    ));
    $created = thepond_analytics_google_request('POST', THEPOND_ANALYTICS_ADMIN_API . $stream . '/measurementProtocolSecrets', array(
        'displayName' => 'The Pond signups',
    ));
    if (is_wp_error($created)) {
        return $created;
    }
    if (empty($created['secretValue'])) {
        return new WP_Error('no_secret', __('Google did not return a secret value.', 'thepond-analytics'));
    }
    thepond_analytics_update_settings(array('api_secret' => $created['secretValue']));
    return __('Measurement Protocol secret created and saved.', 'thepond-analytics');
}
