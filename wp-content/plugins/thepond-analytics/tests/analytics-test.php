<?php
// Run: docker run --rm -v "$PWD":/app -w /app php:8.2-cli php wp-content/plugins/thepond-analytics/tests/analytics-test.php
define('ABSPATH', dirname(__DIR__, 4) . '/');
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);
require ABSPATH . 'wp-includes/plugin.php';
require ABSPATH . 'wp-includes/class-wp-error.php';

$checks = 0;
function check($condition, $message) {
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
function __($text) { return $text; }
function esc_html__($text) { return htmlspecialchars($text); }
function esc_html($text) { return htmlspecialchars((string) $text); }
function esc_attr($text) { return htmlspecialchars((string) $text, ENT_QUOTES); }
function esc_url($url) { return $url; }
function wp_json_encode($data) { return json_encode($data); }
function is_wp_error($thing) { return $thing instanceof WP_Error; }
function wp_timezone() { return new DateTimeZone('America/Toronto'); }
function number_format_i18n($number, $decimals = 0) { return number_format($number, $decimals); }
function wp_date($format, $timestamp = null) { return gmdate($format, $timestamp ?? time()); }

require dirname(__DIR__) . '/includes/conversions.php';
require dirname(__DIR__) . '/includes/google.php';
require dirname(__DIR__) . '/includes/charts.php';
require dirname(__DIR__) . '/includes/reports.php';

// GA cookies: legacy GS1 and current GS2 session formats.
$src = rawurlencode(json_encode(array('f' => array('s' => 'YouTube', 'm' => 'video', 'c' => 'Spring<script>', 'r' => '', 't' => 1700000000), 'v' => 3, 'sm' => 'google')));
$data = thepond_analytics_read_cookies(array(
    '_ga' => 'GA1.1.123456789.1700000000',
    '_ga_L6H4NC8NPG' => 'GS1.1.1712345678.4.1.1712345999.0.0.0',
    'pond_src' => $src,
), 'G-L6H4NC8NPG');
check($data['client_id'] === '123456789.1700000000', 'client id is parsed from _ga');
check($data['first_visit'] === 1700000000, 'first visit is parsed from _ga');
check($data['session_id'] === '1712345678' && $data['session_number'] === 4, 'GS1 session is parsed');
check($data['source'] === 'youtube' && $data['medium'] === 'video', 'source is lowercased');
check($data['campaign'] === 'springscript', 'campaign is sanitized');
check($data['site_visits'] === 3 && $data['signup_method'] === 'google', 'visits and signup method are read');

$gs2 = thepond_analytics_read_cookies(array('_ga_L6H4NC8NPG' => 'GS2.1.s1712345678$o12$g1$t1712345999$j60$l0$h0'), 'G-L6H4NC8NPG');
check($gs2['session_id'] === '1712345678' && $gs2['session_number'] === 12, 'GS2 session is parsed');
check(!isset($gs2['client_id']) && !isset($gs2['source']), 'missing cookies stay empty');

$bad = thepond_analytics_read_cookies(array('pond_src' => '{not json', '_ga' => 'garbage'), 'G-L6H4NC8NPG');
check($bad === array(), 'bad cookies are ignored');
$bad_method = thepond_analytics_read_cookies(array('pond_src' => rawurlencode('{"sm":"twitter"}')), 'G-X');
check(!isset($bad_method['signup_method']), 'unknown signup method is ignored');

// Channels.
$channels = array(
    array(array('source' => 'youtube', 'medium' => 'video'), 'YouTube'),
    array(array('referrer' => 'm.youtube.com'), 'YouTube'),
    array(array('referrer' => 'howtohockey.com'), 'How To Hockey'),
    array(array('referrer' => 'www.howtohockey.com'), 'How To Hockey'),
    array(array('source' => 'facebook', 'medium' => 'cpc'), 'Paid social'),
    array(array('source' => 'google', 'medium' => 'cpc'), 'Paid search & ads'),
    array(array('source' => 'newsletter', 'medium' => 'email'), 'Email'),
    array(array('referrer' => 'l.facebook.com'), 'Social'),
    array(array('referrer' => 'www.google.com'), 'Search'),
    array(array('referrer' => 'hockeyforum.example'), 'Referral'),
    array(array('source' => '', 'medium' => '', 'referrer' => '', 'site_visits' => 1), 'Direct'),
    array(array(), 'Unknown'),
);
foreach ($channels as $case) {
    check(thepond_analytics_channel($case[0]) === $case[1], 'channel ' . $case[1] . ' got ' . thepond_analytics_channel($case[0]));
}

// Measurement Protocol purchase.
$payload = thepond_analytics_purchase_payload(array(
    'client_id' => '',
    'session_id' => '1712345678',
    'user_id' => 42,
    'transaction_id' => 'mepr-9',
    'value' => '19.999',
    'currency' => 'CAD',
    'plan_id' => 'monthly',
    'plan_name' => 'Monthly',
    'signup_method' => '',
    'rejoined' => true,
    'timestamp' => 1712345678,
));
$event = $payload['events'][0];
check(preg_match('/^\d+\.1712345678$/', $payload['client_id']) === 1, 'fallback client id is stable');
check($payload['user_id'] === '42' && $payload['timestamp_micros'] === 1712345678000000, 'user and timestamp are set');
check($event['name'] === 'purchase' && $event['params']['value'] === 20.0, 'purchase value is rounded');
check($event['params']['visitor_type'] === 'former_member' && $event['params']['signup_method'] === 'unknown', 'rejoin and method defaults');
check($event['params']['items'][0]['item_id'] === 'monthly' && $event['params']['session_id'] === '1712345678', 'items and session');

// Service account JWT.
$key = openssl_pkey_new(array('private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA));
openssl_pkey_export($key, $pem);
$jwt = thepond_analytics_jwt(array('client_email' => 'pond@example.iam.gserviceaccount.com', 'private_key' => $pem), 'scope-a scope-b', 1000);
$parts = explode('.', $jwt);
check(count($parts) === 3, 'jwt has three parts');
$decode = function ($part) { return base64_decode(strtr($part, '-_', '+/') . str_repeat('=', (4 - strlen($part) % 4) % 4)); };
$claims = json_decode($decode($parts[1]), true);
check($claims['iss'] === 'pond@example.iam.gserviceaccount.com' && $claims['exp'] === 4600 && $claims['scope'] === 'scope-a scope-b', 'jwt claims');
check(strpbrk($jwt, '+/=') === false, 'jwt is base64url');
$public = openssl_pkey_get_details($key)['key'];
check(openssl_verify($parts[0] . '.' . $parts[1], $decode($parts[2]), $public, OPENSSL_ALGO_SHA256) === 1, 'jwt signature verifies');
check(is_wp_error(@thepond_analytics_jwt(array('client_email' => 'x', 'private_key' => 'not a key'), 's', 1)), 'bad key returns an error');

// GA report rows and sums.
$rows = thepond_analytics_report_rows(array(
    'dimensionHeaders' => array(array('name' => 'eventName'), array('name' => 'customEvent:visitor_type')),
    'metricHeaders' => array(array('name' => 'totalUsers')),
    'rows' => array(
        array('dimensionValues' => array(array('value' => 'landing_view'), array('value' => 'prospect')), 'metricValues' => array(array('value' => '10'))),
        array('dimensionValues' => array(array('value' => 'landing_view'), array('value' => 'member')), 'metricValues' => array(array('value' => '4'))),
        array('dimensionValues' => array(array('value' => 'landing_view'), array('value' => 'former_member')), 'metricValues' => array(array('value' => '2'))),
    ),
));
check(count($rows) === 3 && $rows[0]['totalUsers'] === 10.0, 'report rows are flattened');
check(thepond_analytics_sum($rows, 'totalUsers') == 16, 'sum all');
check(thepond_analytics_sum($rows, 'totalUsers', array('customEvent:visitor_type' => array('prospect', 'former_member'))) == 12, 'sum filtered');
check(thepond_analytics_report_rows(array()) === array(), 'empty report');

// Date ranges.
$range = thepond_analytics_range(30);
check($range['days'] === 30 && count(thepond_analytics_range_dates($range)) === 30, '30 day range');
check($range['prev_end']->modify('+1 day') == $range['start'], 'previous period ends before range');
check(thepond_analytics_range(12345)['days'] === thepond_analytics_allowed_days(12345), 'unknown range falls back');
check(strtotime($range['start_utc'] . ' UTC') < strtotime($range['end_utc'] . ' UTC'), 'utc bounds ordered');

// Charts.
check(thepond_analytics_nice_max(0) == 1 && thepond_analytics_nice_max(7) == 10 && thepond_analytics_nice_max(130) == 200 && thepond_analytics_nice_max(240) == 250, 'nice max');
$bars = thepond_analytics_bars(array(array('label' => 'Visited <b>', 'value' => 100), array('label' => 'Signed up', 'value' => 5)), array('relative_to_first' => true));
check(strpos($bars, 'Visited &lt;b&gt;') !== false && strpos($bars, '<b>') === false, 'bar labels are escaped');
check(strpos($bars, '5') !== false, 'bars show values');
$chart = thepond_analytics_chart(array('2024-01-01', '2024-01-02'), array(array('name' => 'Signups', 'values' => array('2024-01-01' => 1, '2024-01-02' => 3))));
check(strpos($chart, '<svg') !== false && strpos($chart, '<polyline') !== false, 'chart renders svg');

echo "OK: {$checks} checks passed\n";
