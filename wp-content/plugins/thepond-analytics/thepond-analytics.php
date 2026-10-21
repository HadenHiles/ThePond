<?php
/**
 * Plugin Name: The Pond Analytics
 * Description: Landing page, signup and member engagement analytics for The Pond. Connects GA4, Microsoft Clarity and MemberPress reports to plain-language dashboard widgets.
 * Version: 1.0.0
 * Requires at least: 6.6
 * Requires PHP: 7.4
 * Text Domain: thepond-analytics
 */

defined('ABSPATH') || exit;

define('THEPOND_ANALYTICS_VERSION', '1.0.0');
define('THEPOND_ANALYTICS_DB_VERSION', '1');
define('THEPOND_ANALYTICS_FILE', __FILE__);
define('THEPOND_ANALYTICS_DIR', __DIR__ . '/');

require_once THEPOND_ANALYTICS_DIR . 'includes/settings.php';
require_once THEPOND_ANALYTICS_DIR . 'includes/tracking.php';
require_once THEPOND_ANALYTICS_DIR . 'includes/conversions.php';
require_once THEPOND_ANALYTICS_DIR . 'includes/activity.php';
require_once THEPOND_ANALYTICS_DIR . 'includes/google.php';
require_once THEPOND_ANALYTICS_DIR . 'includes/reports.php';
require_once THEPOND_ANALYTICS_DIR . 'includes/charts.php';
require_once THEPOND_ANALYTICS_DIR . 'includes/dashboard.php';

register_activation_hook(__FILE__, 'thepond_analytics_activate');
register_deactivation_hook(__FILE__, 'thepond_analytics_deactivate');
add_action('plugins_loaded', 'thepond_analytics_maybe_upgrade');

function thepond_analytics_activate() {
    thepond_analytics_install_tables();
    if (!wp_next_scheduled('thepond_analytics_daily')) {
        wp_schedule_event(time() + HOUR_IN_SECONDS, 'daily', 'thepond_analytics_daily');
    }
}

function thepond_analytics_deactivate() {
    wp_clear_scheduled_hook('thepond_analytics_daily');
}

// FTP deploys skip the activation hook, so keep the schema current on load too.
function thepond_analytics_maybe_upgrade() {
    if (get_option('thepond_analytics_db_version') !== THEPOND_ANALYTICS_DB_VERSION) {
        thepond_analytics_activate();
    }
}

function thepond_analytics_install_tables() {
    global $wpdb;
    require_once ABSPATH . 'wp-admin/includes/upgrade.php';

    $charset = $wpdb->get_charset_collate();
    dbDelta("CREATE TABLE {$wpdb->prefix}pond_content_views (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  user_id bigint(20) unsigned NOT NULL,
  post_id bigint(20) unsigned NOT NULL,
  post_type varchar(40) NOT NULL DEFAULT '',
  view_date date NOT NULL,
  views int(10) unsigned NOT NULL DEFAULT 1,
  last_viewed datetime NOT NULL,
  PRIMARY KEY  (id),
  UNIQUE KEY user_post_day (user_id,post_id,view_date),
  KEY view_date (view_date),
  KEY post_id (post_id)
) $charset;");

    dbDelta("CREATE TABLE {$wpdb->prefix}pond_signups (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  txn_id bigint(20) unsigned NOT NULL,
  user_id bigint(20) unsigned NOT NULL,
  product_id bigint(20) unsigned NOT NULL DEFAULT 0,
  amount decimal(16,2) NOT NULL DEFAULT 0.00,
  created_at datetime NOT NULL,
  source varchar(100) NOT NULL DEFAULT '',
  medium varchar(100) NOT NULL DEFAULT '',
  campaign varchar(150) NOT NULL DEFAULT '',
  referrer varchar(190) NOT NULL DEFAULT '',
  channel varchar(40) NOT NULL DEFAULT '',
  signup_method varchar(20) NOT NULL DEFAULT '',
  rejoined tinyint(1) NOT NULL DEFAULT 0,
  site_visits int(10) unsigned NOT NULL DEFAULT 0,
  ga_sessions int(10) unsigned NOT NULL DEFAULT 0,
  days_to_convert int(10) unsigned DEFAULT NULL,
  ga_client_id varchar(64) NOT NULL DEFAULT '',
  ga_sent tinyint(1) NOT NULL DEFAULT 0,
  PRIMARY KEY  (id),
  UNIQUE KEY txn_id (txn_id),
  KEY created_at (created_at),
  KEY user_id (user_id)
) $charset;");

    update_option('thepond_analytics_db_version', THEPOND_ANALYTICS_DB_VERSION, false);
}
