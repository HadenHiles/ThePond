<?php
defined('ABSPATH') || exit;

const THEPOND_ANALYTICS_OPTION = 'thepond_analytics';
const THEPOND_ANALYTICS_SA_OPTION = 'thepond_analytics_service_account';
const THEPOND_ANALYTICS_PAGE_TYPES = array(
    'landing' => 'Landing page',
    'register' => 'Registration / checkout',
    'login' => 'Login',
    'member_dashboard' => 'Member dashboard',
    'content' => 'Courses, lessons and skills',
    'support' => 'Support',
);

add_action('admin_init', 'thepond_analytics_register_setting');
add_action('admin_menu', 'thepond_analytics_settings_menu');
add_action('admin_post_thepond_analytics_test', 'thepond_analytics_handle_test');
add_action('admin_post_thepond_analytics_setup', 'thepond_analytics_handle_setup');
add_filter('plugin_action_links_' . plugin_basename(THEPOND_ANALYTICS_FILE), 'thepond_analytics_action_links');
foreach (array(THEPOND_ANALYTICS_OPTION, THEPOND_ANALYTICS_SA_OPTION) as $thepond_analytics_option) {
    add_action('add_option_' . $thepond_analytics_option, 'thepond_analytics_flush_object_cache');
    add_action('update_option_' . $thepond_analytics_option, 'thepond_analytics_flush_object_cache');
    add_action('delete_option_' . $thepond_analytics_option, 'thepond_analytics_flush_object_cache');
}
unset($thepond_analytics_option);

/**
 * W3 Total Cache skips its object cache on wp-admin pages but uses it for
 * AJAX, cron and the front end, so settings saved in wp-admin would otherwise
 * stay stale (or missing) everywhere else until the cache expires.
 */
function thepond_analytics_flush_object_cache() {
    foreach (array(THEPOND_ANALYTICS_OPTION, THEPOND_ANALYTICS_SA_OPTION, 'alloptions', 'notoptions') as $key) {
        wp_cache_delete($key, 'options');
    }
    if (function_exists('w3tc_objectcache_flush')) {
        w3tc_objectcache_flush();
    }
}

function thepond_analytics_defaults() {
    return array(
        'enabled' => 1,
        'measurement_id' => 'G-L6H4NC8NPG',
        'property_id' => '253885403',
        'api_secret' => '',
        'clarity_id' => '',
        'clarity_pages' => array('landing', 'register', 'login', 'member_dashboard'),
        'replace_ga_plugin' => 1,
    );
}

function thepond_analytics_settings() {
    $saved = get_option(THEPOND_ANALYTICS_OPTION, array());
    return array_merge(thepond_analytics_defaults(), is_array($saved) ? $saved : array());
}

function thepond_analytics_setting($key) {
    $settings = thepond_analytics_settings();
    return isset($settings[$key]) ? $settings[$key] : null;
}

function thepond_analytics_update_settings(array $changes) {
    update_option(THEPOND_ANALYTICS_OPTION, array_merge(thepond_analytics_settings(), $changes));
}

function thepond_analytics_capability() {
    return apply_filters('thepond_analytics_capability', 'manage_options');
}

/**
 * Staff (editors and admins) are excluded from every report so testing and
 * content editing never inflate member or prospect numbers.
 */
function thepond_analytics_is_staff($user_id) {
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        return false;
    }
    return (bool) apply_filters('thepond_analytics_is_staff', user_can($user_id, 'edit_others_posts'), $user_id);
}

function thepond_analytics_is_member($user_id) {
    $user_id = (int) $user_id;
    if ($user_id <= 0) {
        return false;
    }
    if (class_exists('MeprUser')) {
        $user = new MeprUser($user_id);
        return (bool) $user->is_active();
    }
    return user_can($user_id, 'memberpress_authorized');
}

function thepond_analytics_visitor_type($user_id) {
    if ((int) $user_id <= 0) {
        return 'prospect';
    }
    $cached = wp_cache_get('vt_' . $user_id, 'thepond_analytics');
    if (is_string($cached) && $cached !== '') {
        return $cached;
    }
    if (thepond_analytics_is_staff($user_id)) {
        $type = 'staff';
    } else {
        $type = thepond_analytics_is_member($user_id) ? 'member' : 'former_member';
    }
    wp_cache_set('vt_' . $user_id, $type, 'thepond_analytics', 10 * MINUTE_IN_SECONDS);
    return $type;
}

function thepond_analytics_register_setting() {
    register_setting('thepond_analytics', THEPOND_ANALYTICS_OPTION, array(
        'type' => 'array',
        'sanitize_callback' => 'thepond_analytics_sanitize_settings',
        'default' => thepond_analytics_defaults(),
    ));
}

function thepond_analytics_sanitize_settings($input) {
    $input = is_array($input) ? $input : array();
    $current = thepond_analytics_settings();
    $clean = $current;

    $clean['enabled'] = empty($input['enabled']) ? 0 : 1;
    $clean['replace_ga_plugin'] = empty($input['replace_ga_plugin']) ? 0 : 1;

    $measurement_id = strtoupper(trim((string) ($input['measurement_id'] ?? '')));
    if ($measurement_id === '' || preg_match('/^G-[A-Z0-9]{4,20}$/', $measurement_id)) {
        $clean['measurement_id'] = $measurement_id;
    } else {
        add_settings_error(THEPOND_ANALYTICS_OPTION, 'measurement_id', __('The measurement ID should look like G-XXXXXXX.', 'thepond-analytics'));
    }

    $property_id = preg_replace('/\D/', '', (string) ($input['property_id'] ?? ''));
    $clean['property_id'] = $property_id;

    $secret = trim((string) ($input['api_secret'] ?? ''));
    $clean['api_secret'] = preg_match('/^[A-Za-z0-9_\-]{0,64}$/', $secret) ? $secret : $current['api_secret'];

    $clarity = strtolower(trim((string) ($input['clarity_id'] ?? '')));
    if ($clarity === '' || preg_match('/^[a-z0-9]{6,20}$/', $clarity)) {
        $clean['clarity_id'] = $clarity;
    } else {
        add_settings_error(THEPOND_ANALYTICS_OPTION, 'clarity_id', __('The Clarity project ID should only contain letters and numbers.', 'thepond-analytics'));
    }

    $pages = isset($input['clarity_pages']) && is_array($input['clarity_pages']) ? $input['clarity_pages'] : array();
    $clean['clarity_pages'] = array_values(array_intersect(array_keys(THEPOND_ANALYTICS_PAGE_TYPES), $pages));

    if (!empty($input['remove_service_account'])) {
        delete_option(THEPOND_ANALYTICS_SA_OPTION);
        thepond_analytics_flush_cache();
    } elseif (!empty($input['service_account_json'])) {
        // options.php already unslashes input; unslashing again would strip the key's \n escapes.
        $result = thepond_analytics_store_service_account($input['service_account_json']);
        if (is_wp_error($result)) {
            add_settings_error(THEPOND_ANALYTICS_OPTION, 'service_account', $result->get_error_message());
        }
    }

    return $clean;
}

function thepond_analytics_store_service_account($json) {
    $data = json_decode(trim((string) $json), true);
    if (!is_array($data) || empty($data['client_email']) || empty($data['private_key']) || ($data['type'] ?? '') !== 'service_account') {
        return new WP_Error('invalid_service_account', __('That does not look like a Google service account JSON key. Download a new JSON key from Google Cloud and paste the whole file.', 'thepond-analytics'));
    }
    if (!function_exists('openssl_pkey_get_private') || !openssl_pkey_get_private($data['private_key'])) {
        return new WP_Error('invalid_private_key', __('The service account private key could not be read.', 'thepond-analytics'));
    }
    $keep = array_intersect_key($data, array_flip(array('type', 'project_id', 'private_key_id', 'private_key', 'client_email', 'token_uri')));
    if (get_option(THEPOND_ANALYTICS_SA_OPTION) === false) {
        add_option(THEPOND_ANALYTICS_SA_OPTION, $keep, '', false);
    } else {
        update_option(THEPOND_ANALYTICS_SA_OPTION, $keep, false);
    }
    thepond_analytics_flush_cache();
    return true;
}

function thepond_analytics_settings_menu() {
    add_options_page(
        __('Pond Analytics', 'thepond-analytics'),
        __('Pond Analytics', 'thepond-analytics'),
        thepond_analytics_capability(),
        'thepond-analytics-settings',
        'thepond_analytics_render_settings'
    );
}

function thepond_analytics_settings_url() {
    return admin_url('options-general.php?page=thepond-analytics-settings');
}

function thepond_analytics_action_links($links) {
    array_unshift(
        $links,
        '<a href="' . esc_url(thepond_analytics_report_url()) . '">' . esc_html__('Reports', 'thepond-analytics') . '</a>',
        '<a href="' . esc_url(thepond_analytics_settings_url()) . '">' . esc_html__('Settings', 'thepond-analytics') . '</a>'
    );
    return $links;
}

function thepond_analytics_render_settings() {
    if (!current_user_can(thepond_analytics_capability())) {
        return;
    }
    $settings = thepond_analytics_settings();
    $account = thepond_analytics_service_account();
    $name = THEPOND_ANALYTICS_OPTION;
    ?>
    <div class="wrap thepond-analytics-settings">
        <h1><?php esc_html_e('Pond Analytics', 'thepond-analytics'); ?></h1>
        <p><?php esc_html_e('Tracks landing page clicks, signups and member activity, then summarizes it on the Dashboard and under MemberPress → Growth & Engagement. Staff accounts are never tracked.', 'thepond-analytics'); ?></p>
        <?php settings_errors(THEPOND_ANALYTICS_OPTION); ?>
        <?php thepond_analytics_tool_notice(); ?>
        <form method="post" action="options.php">
            <?php settings_fields('thepond_analytics'); ?>
            <h2><?php esc_html_e('Tracking', 'thepond-analytics'); ?></h2>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><?php esc_html_e('Enable tracking', 'thepond-analytics'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[enabled]" value="1" <?php checked($settings['enabled']); ?>> <?php esc_html_e('Load Google Analytics and Clarity after visitors accept cookies', 'thepond-analytics'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tpa-measurement"><?php esc_html_e('GA4 measurement ID', 'thepond-analytics'); ?></label></th>
                    <td><input id="tpa-measurement" class="regular-text" name="<?php echo esc_attr($name); ?>[measurement_id]" value="<?php echo esc_attr($settings['measurement_id']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Old GA plugin', 'thepond-analytics'); ?></th>
                    <td><label><input type="checkbox" name="<?php echo esc_attr($name); ?>[replace_ga_plugin]" value="1" <?php checked($settings['replace_ga_plugin']); ?>> <?php esc_html_e('Stop the "GA Google Analytics" plugin from adding a second tag (prevents double-counted page views)', 'thepond-analytics'); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tpa-clarity"><?php esc_html_e('Clarity project ID', 'thepond-analytics'); ?></label></th>
                    <td>
                        <input id="tpa-clarity" class="regular-text" name="<?php echo esc_attr($name); ?>[clarity_id]" value="<?php echo esc_attr($settings['clarity_id']); ?>">
                        <p class="description"><?php esc_html_e('From clarity.microsoft.com → Settings → Overview. Leave empty to turn session recordings off.', 'thepond-analytics'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e('Record sessions on', 'thepond-analytics'); ?></th>
                    <td><fieldset>
                        <?php foreach (THEPOND_ANALYTICS_PAGE_TYPES as $type => $label) : ?>
                            <label style="display:block"><input type="checkbox" name="<?php echo esc_attr($name); ?>[clarity_pages][]" value="<?php echo esc_attr($type); ?>" <?php checked(in_array($type, $settings['clarity_pages'], true)); ?>> <?php echo esc_html($label); ?></label>
                        <?php endforeach; ?>
                    </fieldset></td>
                </tr>
            </table>

            <h2><?php esc_html_e('Google Analytics connection', 'thepond-analytics'); ?></h2>
            <p><?php esc_html_e('Used to read reports for the dashboard widgets and to record completed signups (including Stripe payments that finish off-site).', 'thepond-analytics'); ?></p>
            <table class="form-table" role="presentation">
                <tr>
                    <th scope="row"><label for="tpa-property"><?php esc_html_e('GA4 property ID', 'thepond-analytics'); ?></label></th>
                    <td><input id="tpa-property" class="regular-text" name="<?php echo esc_attr($name); ?>[property_id]" value="<?php echo esc_attr($settings['property_id']); ?>"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tpa-sa"><?php esc_html_e('Service account', 'thepond-analytics'); ?></label></th>
                    <td>
                        <?php if ($account) : ?>
                            <p><strong><?php echo esc_html($account['client_email']); ?></strong>
                            <?php if (defined('THEPOND_ANALYTICS_SERVICE_ACCOUNT')) : ?>(<?php esc_html_e('from wp-config.php', 'thepond-analytics'); ?>)<?php endif; ?></p>
                            <?php if (!defined('THEPOND_ANALYTICS_SERVICE_ACCOUNT')) : ?>
                                <label><input type="checkbox" name="<?php echo esc_attr($name); ?>[remove_service_account]" value="1"> <?php esc_html_e('Remove this key', 'thepond-analytics'); ?></label>
                            <?php endif; ?>
                            <p class="description"><?php esc_html_e('Paste a new JSON key below to replace it.', 'thepond-analytics'); ?></p>
                        <?php endif; ?>
                        <textarea id="tpa-sa" class="large-text code" rows="4" name="<?php echo esc_attr($name); ?>[service_account_json]" placeholder="{ &quot;type&quot;: &quot;service_account&quot;, ... }" autocomplete="off"></textarea>
                        <p class="description"><?php esc_html_e('The key is stored privately and never shown again. Give this account Editor access to the GA4 property.', 'thepond-analytics'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><label for="tpa-secret"><?php esc_html_e('Measurement Protocol secret', 'thepond-analytics'); ?></label></th>
                    <td>
                        <input id="tpa-secret" class="regular-text" name="<?php echo esc_attr($name); ?>[api_secret]" value="<?php echo esc_attr($settings['api_secret']); ?>" autocomplete="off">
                        <p class="description"><?php esc_html_e('"Set up Google Analytics" below creates this automatically.', 'thepond-analytics'); ?></p>
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>

        <h2><?php esc_html_e('Tools', 'thepond-analytics'); ?></h2>
        <p>
            <?php thepond_analytics_tool_button('thepond_analytics_test', __('Test connection', 'thepond-analytics')); ?>
            <?php thepond_analytics_tool_button('thepond_analytics_setup', __('Set up Google Analytics', 'thepond-analytics')); ?>
        </p>
        <p class="description"><?php esc_html_e('Setup creates the custom dimensions the reports use, marks signups as key events and creates the Measurement Protocol secret. It is safe to run more than once.', 'thepond-analytics'); ?></p>
    </div>
    <?php
}

function thepond_analytics_tool_button($action, $label) {
    ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:8px">
        <input type="hidden" name="action" value="<?php echo esc_attr($action); ?>">
        <?php wp_nonce_field($action); ?>
        <?php submit_button($label, 'secondary', 'submit', false); ?>
    </form>
    <?php
}

function thepond_analytics_check_tool_request($action) {
    if (!current_user_can(thepond_analytics_capability())) {
        wp_die(esc_html__('You do not have permission to do that.', 'thepond-analytics'), 403);
    }
    check_admin_referer($action);
}

function thepond_analytics_finish_tool(array $lines, $ok) {
    set_transient('thepond_analytics_notice_' . get_current_user_id(), array('ok' => (bool) $ok, 'lines' => $lines), 5 * MINUTE_IN_SECONDS);
    wp_safe_redirect(thepond_analytics_settings_url());
    exit;
}

function thepond_analytics_handle_test() {
    thepond_analytics_check_tool_request('thepond_analytics_test');
    $result = thepond_analytics_test_connection();
    thepond_analytics_finish_tool($result['lines'], $result['ok']);
}

function thepond_analytics_handle_setup() {
    thepond_analytics_check_tool_request('thepond_analytics_setup');
    $result = thepond_analytics_ga_setup();
    thepond_analytics_finish_tool($result['lines'], $result['ok']);
}

// Printed by the settings page itself: some admin plugins strip admin_notices callbacks.
function thepond_analytics_tool_notice() {
    $key = 'thepond_analytics_notice_' . get_current_user_id();
    $notice = get_transient($key);
    if (!$notice) {
        return;
    }
    delete_transient($key);
    printf('<div class="notice notice-%s is-dismissible"><ul>', $notice['ok'] ? 'success' : 'error');
    foreach ($notice['lines'] as $line) {
        echo '<li>' . esc_html($line) . '</li>';
    }
    echo '</ul></div>';
}
