<?php
defined('ABSPATH') || exit;

add_action('mepr-signup', 'thepond_analytics_capture_signup', 5);
add_action('mepr-event-transaction-completed', 'thepond_analytics_transaction_completed');

/**
 * Read the GA client/session cookies plus our first-touch cookie. Runs during
 * the checkout request, the last moment the visitor's browser is present
 * before Stripe Checkout takes over.
 */
function thepond_analytics_read_cookies(array $cookies, $measurement_id) {
    $data = array();

    if (!empty($cookies['_ga']) && preg_match('/^GA\d\.\d+\.(\d+)\.(\d+)$/', (string) $cookies['_ga'], $m)) {
        $data['client_id'] = $m[1] . '.' . $m[2];
        $data['first_visit'] = (int) $m[2];
    }

    $session_cookie = '_ga_' . preg_replace('/^G-/', '', (string) $measurement_id);
    if (!empty($cookies[$session_cookie])) {
        $raw = (string) $cookies[$session_cookie];
        if (preg_match('/^GS1\.\d+\.(\d+)\.(\d+)\./', $raw, $m)) {
            $data['session_id'] = $m[1];
            $data['session_number'] = (int) $m[2];
        } elseif (preg_match('/^GS2\.\d+\.(.+)$/', $raw, $m)) {
            foreach (explode('$', $m[1]) as $part) {
                $key = substr($part, 0, 1);
                $value = substr($part, 1);
                if ($key === 's' && ctype_digit($value)) {
                    $data['session_id'] = $value;
                } elseif ($key === 'o' && ctype_digit($value)) {
                    $data['session_number'] = (int) $value;
                }
            }
        }
    }

    if (!empty($cookies['pond_src'])) {
        $src = json_decode(stripslashes(rawurldecode((string) $cookies['pond_src'])), true);
        if (is_array($src)) {
            $first = isset($src['f']) && is_array($src['f']) ? $src['f'] : array();
            $data['source'] = thepond_analytics_clean_token($first['s'] ?? '', 100);
            $data['medium'] = thepond_analytics_clean_token($first['m'] ?? '', 100);
            $data['campaign'] = thepond_analytics_clean_token($first['c'] ?? '', 150);
            $data['referrer'] = thepond_analytics_clean_token($first['r'] ?? '', 190);
            $data['first_touch'] = isset($first['t']) ? (int) $first['t'] : 0;
            $data['site_visits'] = isset($src['v']) ? max(0, (int) $src['v']) : 0;
            $method = thepond_analytics_clean_token($src['sm'] ?? '', 20);
            if (in_array($method, array('google', 'facebook', 'email'), true)) {
                $data['signup_method'] = $method;
            }
        }
    }

    return $data;
}

function thepond_analytics_clean_token($value, $length) {
    $value = strtolower(trim((string) $value));
    $value = preg_replace('/[^a-z0-9._\-\/ ]/', '', $value);
    return substr($value, 0, $length);
}

/**
 * Plain-language channel for the first visit, so the widgets can say
 * "YouTube" or "How To Hockey" instead of GA's referral jargon.
 */
function thepond_analytics_channel(array $data) {
    $source = $data['source'] ?? '';
    $medium = $data['medium'] ?? '';
    $referrer = $data['referrer'] ?? '';
    $origin = $source !== '' ? $source : $referrer;

    if ($source === '' && $medium === '' && $referrer === '') {
        return isset($data['first_touch']) || isset($data['site_visits']) ? 'Direct' : 'Unknown';
    }
    if (preg_match('/youtube|youtu\.be/', $origin)) {
        return 'YouTube';
    }
    if (preg_match('/(^|\.)howtohockey\.com$|^howtohockey/', $origin) && strpos($origin, 'thepond.') !== 0) {
        return 'How To Hockey';
    }
    if (preg_match('/^(cpc|ppc|paid|paidsocial|paid_social|display)/', $medium)) {
        return preg_match('/facebook|instagram|fb|ig|meta|tiktok/', $origin) ? 'Paid social' : 'Paid search & ads';
    }
    if ($medium === 'email' || preg_match('/mail|newsletter|getresponse/', $origin)) {
        return 'Email';
    }
    if ($medium === 'social' || preg_match('/facebook|instagram|t\.co|twitter|x\.com|tiktok|reddit|linkedin|pinterest|fb\.|lnkd/', $origin)) {
        return 'Social';
    }
    if (preg_match('/google\.|bing\.|yahoo\.|duckduckgo|ecosia|baidu|yandex|^google$|^bing$/', $origin)) {
        return 'Search';
    }
    return 'Referral';
}

function thepond_analytics_capture_signup($txn) {
    if (!is_object($txn) || empty($txn->user_id)) {
        return;
    }
    $data = thepond_analytics_read_cookies($_COOKIE, thepond_analytics_setting('measurement_id'));
    $data['captured_at'] = time();
    update_user_meta((int) $txn->user_id, '_thepond_analytics_signup', $data);
}

function thepond_analytics_is_first_payment($txn) {
    if (!class_exists('MeprTransaction') || $txn->txn_type !== MeprTransaction::$payment_str) {
        return false;
    }
    if (empty($txn->subscription_id)) {
        return true;
    }
    // Counts refunded payments too, so a renewal after a refunded first payment is not a new signup.
    return (int) $txn->subscription_payment_index() === 1;
}

function thepond_analytics_transaction_completed($event) {
    $txn = is_object($event) && method_exists($event, 'get_data') ? $event->get_data() : null;
    if (!$txn || !is_a($txn, 'MeprTransaction') || empty($txn->id) || !thepond_analytics_is_first_payment($txn)) {
        return;
    }
    if (thepond_analytics_is_staff((int) $txn->user_id)) {
        return;
    }
    thepond_analytics_record_signup($txn);
}

function thepond_analytics_record_signup($txn) {
    global $wpdb;
    $table = $wpdb->prefix . 'pond_signups';
    if ($wpdb->get_var($wpdb->prepare("SELECT id FROM $table WHERE txn_id = %d", $txn->id))) {
        return false;
    }

    $user_id = (int) $txn->user_id;
    $data = get_user_meta($user_id, '_thepond_analytics_signup', true);
    $data = is_array($data) ? $data : array();
    $created = $txn->created_at ? strtotime($txn->created_at . ' UTC') : time();
    $first = array_filter(array($data['first_touch'] ?? 0, $data['first_visit'] ?? 0));
    $days = $first ? max(0, (int) floor(($created - min($first)) / DAY_IN_SECONDS)) : null;
    $rejoined = (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE user_id = %d AND txn_id <> %d",
        $user_id,
        $txn->id
    )) > 0 || thepond_analytics_had_earlier_payment($txn);

    $row = array(
        'txn_id' => (int) $txn->id,
        'user_id' => $user_id,
        'product_id' => (int) $txn->product_id,
        'amount' => (float) $txn->total,
        'created_at' => gmdate('Y-m-d H:i:s', $created),
        'source' => $data['source'] ?? '',
        'medium' => $data['medium'] ?? '',
        'campaign' => $data['campaign'] ?? '',
        'referrer' => $data['referrer'] ?? '',
        'channel' => thepond_analytics_channel($data),
        'signup_method' => $data['signup_method'] ?? '',
        'rejoined' => $rejoined ? 1 : 0,
        'site_visits' => (int) ($data['site_visits'] ?? 0),
        'ga_sessions' => (int) ($data['session_number'] ?? 0),
        'days_to_convert' => $days,
        'ga_client_id' => $data['client_id'] ?? '',
        'ga_sent' => 0,
    );
    $wpdb->insert($table, $row);

    $sent = thepond_analytics_send_purchase($txn, $data, $row);
    if ($sent) {
        $wpdb->update($table, array('ga_sent' => 1), array('txn_id' => (int) $txn->id));
    }
    if (method_exists($txn, 'update_meta')) {
        $txn->update_meta('_thepond_analytics', array_merge($data, array('channel' => $row['channel'], 'ga_sent' => $sent)));
    }
    return true;
}

function thepond_analytics_had_earlier_payment($txn) {
    global $wpdb;
    if (!class_exists('MeprDb')) {
        return false;
    }
    $mepr_db = new MeprDb();
    return (int) $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM {$mepr_db->transactions} WHERE user_id = %d AND id < %d AND status IN ('complete','confirmed') AND txn_type = 'payment' AND total > 0",
        $txn->user_id,
        $txn->id
    )) > 0;
}

function thepond_analytics_purchase_payload(array $args) {
    $client_id = $args['client_id'] ?: sprintf('%u.%d', crc32('pond-user-' . $args['user_id']), (int) $args['timestamp']);
    $params = array(
        'transaction_id' => $args['transaction_id'],
        'value' => round((float) $args['value'], 2),
        'currency' => $args['currency'],
        'plan' => $args['plan_id'],
        'signup_method' => $args['signup_method'] ?: 'unknown',
        'visitor_type' => $args['rejoined'] ? 'former_member' : 'prospect',
        'engagement_time_msec' => 1,
        'items' => array(array(
            'item_id' => $args['plan_id'],
            'item_name' => $args['plan_name'],
            'item_category' => 'membership',
            'price' => round((float) $args['value'], 2),
            'quantity' => 1,
        )),
    );
    if (!empty($args['session_id'])) {
        $params['session_id'] = (string) $args['session_id'];
    }
    return array(
        'client_id' => $client_id,
        'user_id' => (string) $args['user_id'],
        'timestamp_micros' => (int) $args['timestamp'] * 1000000,
        'events' => array(array('name' => 'purchase', 'params' => $params)),
    );
}

function thepond_analytics_send_purchase($txn, array $data, array $row) {
    $settings = thepond_analytics_settings();
    if (empty($settings['enabled']) || empty($settings['measurement_id']) || empty($settings['api_secret'])) {
        return false;
    }
    $payload = thepond_analytics_purchase_payload(array(
        'client_id' => $data['client_id'] ?? '',
        'session_id' => $data['session_id'] ?? '',
        'user_id' => (int) $txn->user_id,
        'transaction_id' => 'mepr-' . (int) $txn->id,
        'value' => (float) $txn->total,
        'currency' => class_exists('MeprOptions') ? MeprOptions::fetch()->currency_code : 'USD',
        'plan_id' => (string) get_post_field('post_name', (int) $txn->product_id),
        'plan_name' => (string) get_the_title((int) $txn->product_id),
        'signup_method' => $row['signup_method'],
        'rejoined' => !empty($row['rejoined']),
        // GA only accepts Measurement Protocol timestamps up to 72 hours old.
        'timestamp' => max(time() - 71 * HOUR_IN_SECONDS, min(time(), strtotime($row['created_at'] . ' UTC'))),
    ));
    $url = add_query_arg(array(
        'measurement_id' => rawurlencode($settings['measurement_id']),
        'api_secret' => rawurlencode($settings['api_secret']),
    ), 'https://www.google-analytics.com/mp/collect');
    $response = wp_remote_post($url, array(
        'timeout' => 5,
        'headers' => array('Content-Type' => 'application/json'),
        'body' => wp_json_encode($payload),
    ));
    $code = is_wp_error($response) ? 0 : (int) wp_remote_retrieve_response_code($response);
    if ($code < 200 || $code >= 300) {
        error_log('[thepond-analytics] Measurement Protocol purchase failed for transaction ' . (int) $txn->id . ' (HTTP ' . $code . ')');
        return false;
    }
    return true;
}
