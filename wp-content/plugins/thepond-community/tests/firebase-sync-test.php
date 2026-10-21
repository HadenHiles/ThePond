<?php
define('ABSPATH', dirname(__DIR__, 4) . '/');
define('MINUTE_IN_SECONDS', 60);
require ABSPATH . 'wp-includes/plugin.php';
require ABSPATH . 'wp-includes/class-wp-error.php';

class Thepond_Test_Json extends Exception {
    public $success;
    public $data;
    public $status;
    public function __construct($success, $data, $status) {
        parent::__construct('json');
        $this->success = $success;
        $this->data = $data;
        $this->status = $status;
    }
}

$checks = 0;
$requests = array();
$responses = array();
$meta = array();
$transients = array();
$logged_in = false;
$failed_logins = 0;
$users = array(
    'member@example.com' => (object) array('ID' => 7, 'user_email' => 'member@example.com', 'user_pass' => 'hash:correct-horse'),
    'quote@example.com' => (object) array('ID' => 8, 'user_email' => 'quote@example.com', 'user_pass' => 'hash:it\\\'s-secret'),
    'short@example.com' => (object) array('ID' => 9, 'user_email' => 'short@example.com', 'user_pass' => 'hash:abc'),
);

function check($condition, $message) {
    global $checks;
    if (!$condition) {
        throw new RuntimeException($message);
    }
    $checks++;
}
function get_option($key) { return $key === 'mo_firebase_auth_api_key' ? 'browser-key' : false; }
function home_url($path = '') { return 'https://thepond.example' . $path; }
function wp_json_encode($data) { return json_encode($data); }
function is_wp_error($thing) { return $thing instanceof WP_Error; }
function wp_remote_post($url, $args) {
    global $requests, $responses;
    $requests[] = array('url' => $url, 'args' => $args, 'body' => json_decode($args['body'], true));
    $next = array_shift($responses);
    return $next ?: array('code' => 200, 'body' => '{}');
}
function wp_remote_retrieve_body($response) { return $response['body']; }
function wp_remote_retrieve_response_code($response) { return $response['code']; }
function __($text) { return $text; }
function is_user_logged_in() { global $logged_in; return $logged_in; }
function wp_unslash($value) { return stripslashes($value); }
function wp_slash($value) { return addslashes($value); }
function get_userdata($id) {
    global $users;
    foreach ($users as $user) {
        if ($user->ID === $id) {
            return $user;
        }
    }
    return false;
}
function get_user_by($field, $value) { global $users; return $users[strtolower($value)] ?? false; }
function is_email($email) { return (bool) filter_var($email, FILTER_VALIDATE_EMAIL); }
function sanitize_email($email) { return trim($email); }
function update_user_meta($id, $key, $value) { global $meta; $meta[$id][$key] = $value; }
function delete_user_meta($id, $key) { global $meta; unset($meta[$id][$key]); }
ini_set('error_log', '/dev/null');
function get_transient($key) { global $transients; return $transients[$key] ?? false; }
function set_transient($key, $value) { global $transients; $transients[$key] = $value; }
function wp_check_password($password, $hash) { return $hash === 'hash:' . $password; }
function wp_generate_password() { return 'random-generated-password'; }
function check_ajax_referer($action, $field) {
    check($action === 'thepond-firebase-recover' && $_POST[$field] === 'nonce', 'Recovery nonce not verified');
}
function wp_send_json_success($data) { throw new Thepond_Test_Json(true, $data, 200); }
function wp_send_json_error($data, $status) { throw new Thepond_Test_Json(false, $data, $status); }
add_action('wp_login_failed', function () { global $failed_logins; $failed_logins++; });

require dirname(__DIR__) . '/portal/firebase-sync.php';

function firebase_error($message) {
    return array('code' => 400, 'body' => json_encode(array('error' => array('message' => $message))));
}
function recover($post) {
    global $requests;
    $requests = array();
    $_POST = array_merge(array('nonce' => 'nonce'), $post);
    try {
        thepond_firebase_recover();
    } catch (Thepond_Test_Json $response) {
        return $response;
    }
    throw new RuntimeException('Recovery did not respond');
}

// Sign-up validation enforces Firebase's minimum password length.
$_POST = array('mepr_user_password' => 'abc');
$errors = apply_filters('mepr-validate-signup', array());
check(isset($errors['mepr_user_password']), 'Short sign-up password accepted');
$_POST = array('mepr_user_password' => 'abcdef');
check(apply_filters('mepr-validate-signup', array()) === array(), 'Valid sign-up password rejected');
$logged_in = true;
$_POST = array('mepr_user_password' => 'abc');
check(apply_filters('mepr-validate-signup', array()) === array(), 'Logged-in purchases must not be validated');
$logged_in = false;

// Server-side sign-up creates the Firebase user with the unslashed password.
$requests = array();
$_POST = array('mepr_user_password' => addslashes('it\'s-secret'));
do_action('user_register', 8);
check(count($requests) === 1 && strpos($requests[0]['url'], 'accounts:signUp?key=browser-key') !== false, 'Sign-up did not call Firebase');
check($requests[0]['body'] === array('email' => 'quote@example.com', 'password' => 'it\'s-secret', 'returnSecureToken' => false), 'Sign-up sent the wrong Firebase payload');
check($requests[0]['args']['headers']['Referer'] === 'https://thepond.example/', 'Referrer-restricted API keys would reject sign-up');

$responses = array(firebase_error('EMAIL_EXISTS'));
$_POST = array('mepr_user_password' => 'correct-horse');
do_action('user_register', 7);
check(empty($meta[7]['thepond_firebase_sync_error']), 'Existing Firebase user recorded as a sync failure');

$responses = array(firebase_error('OPERATION_NOT_ALLOWED'));
do_action('user_register', 7);
check(($meta[7]['thepond_firebase_sync_error'] ?? '') === 'OPERATION_NOT_ALLOWED', 'Sync failure not recorded');

$requests = array();
$_POST = array();
do_action('user_register', 7);
check($requests === array(), 'Non-MemberPress registrations must not call Firebase');

// Login recovery requires the WordPress password.
$response = recover(array('mode' => 'login', 'email' => 'member@example.com', 'password' => 'wrong'));
check(!$response->success && $response->status === 403 && $requests === array() && $failed_logins === 1, 'Wrong password reached Firebase');

$transients = array();
$response = recover(array('mode' => 'login', 'email' => 'member@example.com', 'password' => 'correct-horse'));
check($response->success && $response->data === array('created' => true, 'reset_sent' => false), 'Login recovery did not create the user');
check($requests[0]['body']['password'] === 'correct-horse' && empty($meta[7]['thepond_firebase_sync_error']), 'Login recovery used the wrong password');

$transients = array();
$response = recover(array('mode' => 'login', 'email' => 'quote@example.com', 'password' => addslashes('it\'s-secret')));
check($response->success && $requests[0]['body']['password'] === 'it\'s-secret', 'MemberPress slashed password hashes not accepted');

$transients = array();
$responses = array(firebase_error('EMAIL_EXISTS'));
$response = recover(array('mode' => 'login', 'email' => 'member@example.com', 'password' => 'correct-horse'));
check($response->success && $response->data['created'] === false && count($requests) === 1, 'Existing Firebase user not left untouched');

$transients = array();
$response = recover(array('mode' => 'login', 'email' => 'short@example.com', 'password' => 'abc'));
check($response->success && $response->data['reset_sent'] === true, 'Short legacy password did not trigger reset');
check($requests[0]['body']['password'] === 'random-generated-password' &&
    $requests[1]['body'] === array('requestType' => 'PASSWORD_RESET', 'email' => 'short@example.com'), 'Short password recovery sent the wrong requests');

// Reset recovery creates the user with a random password and sends a Firebase reset email.
$transients = array();
$response = recover(array('mode' => 'reset', 'email' => 'member@example.com'));
check($response->success && $response->data['reset_sent'] === true, 'Reset recovery failed');
check(strpos($requests[1]['url'], 'accounts:sendOobCode') !== false && $requests[0]['body']['password'] === 'random-generated-password', 'Reset recovery sent the wrong requests');

$transients = array();
$response = recover(array('mode' => 'reset', 'email' => 'nobody@example.com'));
check(!$response->success && $response->status === 404 && $requests === array(), 'Unknown email created a Firebase user');

$transients = array();
$responses = array(array('code' => 200, 'body' => '{}'), firebase_error('RESET_PASSWORD_EXCEED_LIMIT'));
$response = recover(array('mode' => 'reset', 'email' => 'member@example.com'));
check(!$response->success && $response->status === 502, 'Reset email failure reported as success');

$transients = array();
for ($i = 0; $i < 5; $i++) {
    recover(array('mode' => 'reset', 'email' => 'nobody@example.com'));
}
$response = recover(array('mode' => 'reset', 'email' => 'nobody@example.com'));
check(!$response->success && $response->status === 429, 'Recovery is not rate limited');

echo "PASS: {$checks} Firebase sync checks\n";
