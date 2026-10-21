<?php
defined('ABSPATH') || exit;

const THEPOND_ANALYTICS_PANELS = array('signups', 'revenue', 'activity', 'landing', 'engagement');

add_action('wp_dashboard_setup', 'thepond_analytics_dashboard_widgets');
add_action('admin_menu', 'thepond_analytics_report_menu', 30);
add_action('admin_enqueue_scripts', 'thepond_analytics_admin_assets');
add_action('wp_ajax_thepond_analytics_panel', 'thepond_analytics_ajax_panel');
add_action('mepr-report-footer', 'thepond_analytics_mepr_report_footer');

function thepond_analytics_report_url() {
    return class_exists('MeprDb') ? admin_url('admin.php?page=thepond-analytics') : admin_url('index.php?page=thepond-analytics');
}

function thepond_analytics_report_menu() {
    $title = __('Growth & Engagement', 'thepond-analytics');
    if (class_exists('MeprDb')) {
        add_submenu_page('memberpress', $title, $title, thepond_analytics_capability(), 'thepond-analytics', 'thepond_analytics_render_report');
    } else {
        add_dashboard_page($title, $title, thepond_analytics_capability(), 'thepond-analytics', 'thepond_analytics_render_report');
    }
}

function thepond_analytics_admin_assets($hook) {
    $screen = function_exists('get_current_screen') ? get_current_screen() : null;
    $ours = $hook === 'index.php' || ($screen && strpos((string) $screen->id, 'thepond-analytics') !== false);
    if (!$ours || !current_user_can(thepond_analytics_capability())) {
        return;
    }
    $base = plugins_url('assets/', THEPOND_ANALYTICS_FILE);
    $dir = THEPOND_ANALYTICS_DIR . 'assets/';
    wp_enqueue_style('thepond-analytics-admin', $base . 'admin.css', array(), filemtime($dir . 'admin.css'));
    wp_enqueue_script('thepond-analytics-admin', $base . 'admin.js', array(), filemtime($dir . 'admin.js'), true);
    wp_localize_script('thepond-analytics-admin', 'pondAnalyticsAdmin', array(
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('thepond_analytics_panel'),
        'error' => __('Could not load this report. Try refreshing the page.', 'thepond-analytics'),
    ));
}

function thepond_analytics_dashboard_widgets() {
    if (!current_user_can(thepond_analytics_capability())) {
        return;
    }
    $widgets = array(
        'signups' => __('The Pond · New visitors & signups', 'thepond-analytics'),
        'revenue' => __('The Pond · Members & revenue', 'thepond-analytics'),
        'activity' => __('The Pond · Member activity', 'thepond-analytics'),
    );
    foreach ($widgets as $panel => $title) {
        wp_add_dashboard_widget('thepond_analytics_' . $panel, $title, function () use ($panel) {
            thepond_analytics_panel_shell($panel, true);
        });
    }
}

function thepond_analytics_range_select($days = 30) {
    $html = '<select class="tpa-range" aria-label="' . esc_attr__('Time period', 'thepond-analytics') . '">';
    foreach (array(7 => __('Last 7 days', 'thepond-analytics'), 30 => __('Last 30 days', 'thepond-analytics'), 90 => __('Last 90 days', 'thepond-analytics')) as $value => $label) {
        $html .= sprintf('<option value="%d"%s>%s</option>', $value, selected($days, $value, false), esc_html($label));
    }
    return $html . '</select>';
}

function thepond_analytics_panel_shell($panel, $widget = false) {
    if ($widget) {
        echo '<div class="tpa-widget-head">' . thepond_analytics_range_select() . '<a href="' . esc_url(thepond_analytics_report_url()) . '">' . esc_html__('Full report →', 'thepond-analytics') . '</a></div>';
    }
    printf(
        '<div class="tpa-panel" data-panel="%s" data-days="30" aria-live="polite"><p class="tpa-loading">%s</p></div>',
        esc_attr($panel),
        esc_html__('Loading…', 'thepond-analytics')
    );
}

function thepond_analytics_render_report() {
    if (!current_user_can(thepond_analytics_capability())) {
        return;
    }
    $settings = thepond_analytics_settings();
    $ga_url = 'https://analytics.google.com/analytics/web/#/p' . rawurlencode($settings['property_id']) . '/reports/intelligenthome';
    ?>
    <div class="wrap tpa-report">
        <h1><?php esc_html_e('Growth & Engagement', 'thepond-analytics'); ?></h1>
        <p class="tpa-intro"><?php esc_html_e('How new visitors find The Pond and become members, and how members use it once they join. Staff accounts are never counted.', 'thepond-analytics'); ?></p>
        <div class="tpa-toolbar">
            <?php echo thepond_analytics_range_select(); ?>
            <button type="button" class="button tpa-refresh"><?php esc_html_e('Refresh data', 'thepond-analytics'); ?></button>
            <a class="button" href="<?php echo esc_url($ga_url); ?>" target="_blank" rel="noopener"><?php esc_html_e('Open Google Analytics', 'thepond-analytics'); ?></a>
            <?php if (!empty($settings['clarity_id'])) : ?>
                <a class="button" href="<?php echo esc_url('https://clarity.microsoft.com/projects/view/' . rawurlencode($settings['clarity_id']) . '/dashboard'); ?>" target="_blank" rel="noopener"><?php esc_html_e('Watch session recordings', 'thepond-analytics'); ?></a>
            <?php endif; ?>
            <a class="button" href="<?php echo esc_url(admin_url('admin.php?page=memberpress-reports')); ?>"><?php esc_html_e('MemberPress reports', 'thepond-analytics'); ?></a>
            <a href="<?php echo esc_url(thepond_analytics_settings_url()); ?>"><?php esc_html_e('Settings', 'thepond-analytics'); ?></a>
        </div>
        <?php if (!thepond_analytics_google_connected()) : ?>
            <div class="notice notice-warning inline"><p><?php
                printf(
                    /* translators: %s: settings link */
                    esc_html__('Google Analytics isn\'t connected yet, so landing page numbers are hidden. Add the service account in %s.', 'thepond-analytics'),
                    '<a href="' . esc_url(thepond_analytics_settings_url()) . '">' . esc_html__('Settings → Pond Analytics', 'thepond-analytics') . '</a>'
                );
            ?></p></div>
        <?php endif; ?>

        <h2><?php esc_html_e('New visitors & signups', 'thepond-analytics'); ?></h2>
        <div class="tpa-grid">
            <div class="tpa-card"><?php thepond_analytics_panel_shell('signups'); ?></div>
            <div class="tpa-card"><?php thepond_analytics_panel_shell('revenue'); ?></div>
        </div>
        <div class="tpa-card tpa-wide"><?php thepond_analytics_panel_shell('landing'); ?></div>

        <h2><?php esc_html_e('Member activity', 'thepond-analytics'); ?></h2>
        <div class="tpa-grid">
            <div class="tpa-card"><?php thepond_analytics_panel_shell('activity'); ?></div>
            <div class="tpa-card"><?php thepond_analytics_panel_shell('engagement'); ?></div>
        </div>
    </div>
    <?php
}

function thepond_analytics_ajax_panel() {
    if (!current_user_can(thepond_analytics_capability())) {
        wp_send_json_error(null, 403);
    }
    check_ajax_referer('thepond_analytics_panel', 'nonce');
    $panel = sanitize_key($_POST['panel'] ?? '');
    if (!in_array($panel, THEPOND_ANALYTICS_PANELS, true)) {
        wp_send_json_error(null, 400);
    }
    $range = thepond_analytics_range((int) ($_POST['days'] ?? 30));
    $force = !empty($_POST['refresh']);
    wp_send_json_success(array('html' => call_user_func('thepond_analytics_panel_' . $panel, $range, $force)));
}

function thepond_analytics_mepr_report_footer() {
    static $printed = false;
    if ($printed || !current_user_can(thepond_analytics_capability())) {
        return;
    }
    $printed = true;
    printf(
        '<p class="tpa-mepr-link"><a class="button" href="%s">%s</a> %s</p>',
        esc_url(thepond_analytics_report_url()),
        esc_html__('Growth & Engagement report', 'thepond-analytics'),
        esc_html__('See where new members come from and how members use The Pond.', 'thepond-analytics')
    );
}

/* ---------- Rendering helpers ---------- */

function thepond_analytics_tile($label, $value, $help = '', $change = null, $lower_is_better = false) {
    $html = '<div class="tpa-tile"><span class="tpa-tile-label">' . esc_html($label) . '</span><strong class="tpa-tile-value">' . esc_html($value) . '</strong>';
    if ($change !== null) {
        $good = $lower_is_better ? $change < 0 : $change > 0;
        $class = abs($change) < 0.005 ? 'flat' : ($good ? 'up' : 'down');
        $arrow = $change > 0 ? '▲' : ($change < 0 ? '▼' : '•');
        $html .= sprintf(
            '<span class="tpa-change tpa-%s" title="%s">%s %s</span>',
            $class,
            esc_attr__('Compared with the previous period of the same length', 'thepond-analytics'),
            $arrow,
            esc_html(thepond_analytics_percent(abs($change), 0))
        );
    }
    if ($help !== '') {
        $html .= '<span class="tpa-tile-help">' . esc_html($help) . '</span>';
    }
    return $html . '</div>';
}

function thepond_analytics_tiles(array $tiles) {
    return '<div class="tpa-tiles">' . implode('', $tiles) . '</div>';
}

function thepond_analytics_notice($message, $type = 'info') {
    return '<p class="tpa-note tpa-note-' . esc_attr($type) . '">' . wp_kses($message, array('a' => array('href' => array()), 'strong' => array())) . '</p>';
}

function thepond_analytics_ga_problem($error) {
    if (!thepond_analytics_google_connected()) {
        return thepond_analytics_notice(sprintf(
            /* translators: %s: settings URL */
            __('Connect Google Analytics in <a href="%s">Settings → Pond Analytics</a> to see landing page numbers.', 'thepond-analytics'),
            esc_url(thepond_analytics_settings_url())
        ));
    }
    $message = $error->get_error_message();
    if (stripos($message, 'customEvent') !== false || stripos($message, 'customUser') !== false) {
        return thepond_analytics_notice(sprintf(
            /* translators: %s: settings URL */
            __('Google Analytics is connected but not set up for these reports yet. Click "Set up Google Analytics" in <a href="%s">Settings → Pond Analytics</a>.', 'thepond-analytics'),
            esc_url(thepond_analytics_settings_url())
        ), 'warning');
    }
    return thepond_analytics_notice(esc_html(sprintf(__('Google Analytics: %s', 'thepond-analytics'), $message)), 'warning');
}

function thepond_analytics_table(array $headers, array $rows) {
    if (!$rows) {
        return '<p class="tpa-muted">' . esc_html__('No data for this period yet.', 'thepond-analytics') . '</p>';
    }
    $html = '<table class="widefat striped tpa-table"><thead><tr>';
    foreach ($headers as $header) {
        $html .= '<th>' . esc_html($header) . '</th>';
    }
    $html .= '</tr></thead><tbody>';
    foreach ($rows as $row) {
        $html .= '<tr>';
        foreach ($row as $cell) {
            $html .= '<td>' . $cell . '</td>';
        }
        $html .= '</tr>';
    }
    return $html . '</tbody></table>';
}

function thepond_analytics_period_label(array $range) {
    /* translators: %d: number of days */
    return sprintf(_n('last %d day', 'last %d days', $range['days'], 'thepond-analytics'), $range['days']);
}

/* ---------- Panels ---------- */

function thepond_analytics_panel_signups(array $range, $force = false) {
    $members = thepond_analytics_member_stats($range, $force);
    $funnel = thepond_analytics_google_connected() ? thepond_analytics_ga_funnel($range, $force) : new WP_Error('not_connected', '');
    $signups = is_wp_error($members) ? 0 : $members['signups'];
    $html = '';

    if (is_wp_error($funnel)) {
        $html .= thepond_analytics_tiles(array(
            thepond_analytics_tile(__('New signups', 'thepond-analytics'), number_format_i18n($signups), '', is_wp_error($members) ? null : thepond_analytics_change($signups, $members['signups_prev'])),
        ));
        return $html . thepond_analytics_ga_problem($funnel);
    }

    $prospects = $funnel['prospects'];
    $click_rate = thepond_analytics_rate($funnel['plan_clickers'], $prospects);
    $click_rate_prev = thepond_analytics_rate($funnel['plan_clickers_prev'], $funnel['landing_prev']);
    $conversion = thepond_analytics_rate($signups, $prospects);

    $html .= thepond_analytics_tiles(array(
        thepond_analytics_tile(__('New visitors', 'thepond-analytics'), number_format_i18n($prospects), __('Non-members who landed on the home page', 'thepond-analytics'), thepond_analytics_change($funnel['landing'], $funnel['landing_prev'])),
        thepond_analytics_tile(__('Click-through rate', 'thepond-analytics'), thepond_analytics_percent($click_rate), __('Clicked to see plans or start signing up', 'thepond-analytics'), $click_rate !== null && $click_rate_prev ? ($click_rate - $click_rate_prev) / $click_rate_prev : null),
        thepond_analytics_tile(__('New signups', 'thepond-analytics'), number_format_i18n($signups), __('Paid memberships started', 'thepond-analytics'), is_wp_error($members) ? null : thepond_analytics_change($signups, $members['signups_prev'])),
        thepond_analytics_tile(__('Visitor → member', 'thepond-analytics'), thepond_analytics_percent($conversion), __('New signups ÷ new visitors', 'thepond-analytics')),
    ));

    $html .= '<h4>' . esc_html__('Signup funnel', 'thepond-analytics') . '</h4>';
    $html .= thepond_analytics_bars(array(
        array('label' => __('Visited the home page', 'thepond-analytics'), 'value' => $prospects),
        array('label' => __('Clicked plans or a signup button', 'thepond-analytics'), 'value' => $funnel['plan_clickers']),
        array('label' => __('Opened a signup page', 'thepond-analytics'), 'value' => $funnel['checkout']),
        array('label' => __('Submitted the signup form', 'thepond-analytics'), 'value' => $funnel['submitted']),
        array('label' => __('Became a member', 'thepond-analytics'), 'value' => $signups),
    ), array('relative_to_first' => true));

    $html .= thepond_analytics_notice(sprintf(
        /* translators: 1: visitors, 2: click rate, 3: conversion rate, 4: period */
        __('In the %4$s, <strong>%1$s</strong> new people visited the home page. <strong>%2$s</strong> of them clicked through to the plans and <strong>%3$s</strong> became members.', 'thepond-analytics'),
        number_format_i18n($prospects),
        thepond_analytics_percent($click_rate),
        thepond_analytics_percent($conversion),
        thepond_analytics_period_label($range)
    ));

    $html .= '<h4>' . esc_html__('Returning members on the home page', 'thepond-analytics') . '</h4>';
    $html .= thepond_analytics_notice(sprintf(
        /* translators: 1: members, 2: login clickers, 3: unrecognized login clickers */
        __('%1$s members came back to the home page and %2$s of them clicked Login. Another %3$s people clicked Login on a device we hadn\'t seen them on before. They aren\'t counted as new visitors.', 'thepond-analytics'),
        number_format_i18n($funnel['member_landing']),
        number_format_i18n($funnel['member_login_clicks']),
        number_format_i18n($funnel['unrecognized_logins'])
    ));
    return $html;
}

function thepond_analytics_panel_revenue(array $range, $force = false) {
    $stats = thepond_analytics_member_stats($range, $force);
    if (is_wp_error($stats)) {
        return thepond_analytics_notice(esc_html($stats->get_error_message()), 'warning');
    }
    $symbol = $stats['currency'];
    $html = thepond_analytics_tiles(array(
        thepond_analytics_tile(__('Active members', 'thepond-analytics'), number_format_i18n($stats['active']), __('Paying right now', 'thepond-analytics')),
        thepond_analytics_tile(__('New signups', 'thepond-analytics'), number_format_i18n($stats['signups']), '', thepond_analytics_change($stats['signups'], $stats['signups_prev'])),
        thepond_analytics_tile(__('Revenue', 'thepond-analytics'), thepond_analytics_money($stats['revenue'], $symbol), __('All completed payments', 'thepond-analytics'), thepond_analytics_change($stats['revenue'], $stats['revenue_prev'])),
        thepond_analytics_tile(__('Cancellations', 'thepond-analytics'), number_format_i18n($stats['cancellations']), sprintf(__('%s of active members', 'thepond-analytics'), thepond_analytics_percent(thepond_analytics_rate($stats['cancellations'], $stats['active']))), thepond_analytics_change($stats['cancellations'], $stats['cancellations_prev']), true),
    ));

    $html .= '<h4>' . esc_html__('New signups per day', 'thepond-analytics') . '</h4>';
    $html .= thepond_analytics_chart(array_keys($stats['daily']), array(
        array('name' => __('signups', 'thepond-analytics'), 'values' => $stats['daily'], 'color' => '#2271b1'),
    ), array('type' => 'bar', 'height' => 120, 'label' => __('New signups per day', 'thepond-analytics')));

    if ($stats['plans']) {
        $html .= '<h4>' . esc_html__('Plans chosen', 'thepond-analytics') . '</h4>';
        $items = array();
        foreach ($stats['plans'] as $plan => $count) {
            $items[] = array('label' => $plan, 'value' => $count, 'note' => thepond_analytics_percent($count / max(1, $stats['signups'])));
        }
        $html .= thepond_analytics_bars($items);
    }

    $facts = array(
        sprintf(__('Average lifetime value: <strong>%s</strong> per member', 'thepond-analytics'), esc_html(thepond_analytics_money($stats['lifetime_value'], $symbol))),
        sprintf(__('<strong>%s</strong> of members renew at least once', 'thepond-analytics'), esc_html(thepond_analytics_percent($stats['rebill_rate'] / 100))),
        sprintf(__('Refunds this period: <strong>%s</strong>', 'thepond-analytics'), esc_html(thepond_analytics_money($stats['refunds'], $symbol))),
    );
    if ($stats['returning_signups']) {
        $facts[] = sprintf(__('<strong>%s</strong> of the new signups had paid before (rejoining or changing plans)', 'thepond-analytics'), number_format_i18n($stats['returning_signups']));
    }
    $html .= '<ul class="tpa-facts"><li>' . implode('</li><li>', array_map(function ($fact) {
        return wp_kses($fact, array('strong' => array()));
    }, $facts)) . '</li></ul>';
    return $html . '<p class="tpa-more"><a href="' . esc_url(admin_url('admin.php?page=memberpress-reports')) . '">' . esc_html__('MemberPress reports →', 'thepond-analytics') . '</a></p>';
}

function thepond_analytics_panel_activity(array $range, $force = false) {
    $stats = thepond_analytics_activity_stats($range, $force);
    if (is_wp_error($stats)) {
        return thepond_analytics_notice(esc_html($stats->get_error_message()), 'warning');
    }
    $html = thepond_analytics_tiles(array(
        thepond_analytics_tile(__('Active members', 'thepond-analytics'), number_format_i18n($stats['active']), __('Paying right now', 'thepond-analytics')),
        thepond_analytics_tile(__('Used The Pond this week', 'thepond-analytics'), $stats['seen_7'] === null ? '–' : thepond_analytics_percent(thepond_analytics_rate($stats['seen_7'], $stats['active'])), $stats['seen_7'] === null ? '' : sprintf(__('%s members in the last 7 days', 'thepond-analytics'), number_format_i18n($stats['seen_7']))),
        thepond_analytics_tile(__('Lessons viewed per member', 'thepond-analytics'), $stats['views_per_member'] === null ? '–' : number_format_i18n($stats['views_per_member'], 1), __('Average for members who visited', 'thepond-analytics')),
        thepond_analytics_tile(__('Inactive 30+ days', 'thepond-analytics'), $stats['at_risk'] === null ? '–' : number_format_i18n($stats['at_risk']), __('Paying but not logging in. They may cancel.', 'thepond-analytics')),
    ));

    $html .= '<h4>' . esc_html__('Members using The Pond each day', 'thepond-analytics') . '</h4>';
    $html .= thepond_analytics_chart(array_keys($stats['daily']), array(
        array('name' => __('members', 'thepond-analytics'), 'values' => $stats['daily'], 'color' => '#00a32a'),
    ), array('height' => 120, 'label' => __('Members using The Pond each day', 'thepond-analytics')));

    if ($stats['logging_since'] && $stats['logging_since'] > $range['start']->format('Y-m-d')) {
        $html .= thepond_analytics_notice(esc_html(sprintf(
            __('Lesson views have been recorded since %s, so earlier days show zero.', 'thepond-analytics'),
            thepond_analytics_date_label($stats['logging_since'])
        )));
    }

    $html .= '<h4>' . esc_html__('Most-used content', 'thepond-analytics') . '</h4>';
    $items = array();
    foreach (array_slice($stats['top'], 0, 5) as $item) {
        $items[] = array('label' => $item['title'], 'value' => (int) $item['members'], 'note' => __('members', 'thepond-analytics'));
    }
    $html .= thepond_analytics_bars($items);

    if ($stats['completions']) {
        $labels = array('lesson' => __('lessons', 'thepond-analytics'), 'topic' => __('topics', 'thepond-analytics'), 'course' => __('courses', 'thepond-analytics'), 'quiz' => __('quizzes', 'thepond-analytics'));
        $parts = array();
        foreach ($labels as $type => $label) {
            if (!empty($stats['completions'][$type])) {
                $parts[] = number_format_i18n($stats['completions'][$type]) . ' ' . $label;
            }
        }
        $html .= thepond_analytics_notice(esc_html(sprintf(__('Completed in this period: %s.', 'thepond-analytics'), implode(', ', $parts))));
    }
    return $html;
}

function thepond_analytics_panel_landing(array $range, $force = false) {
    $html = '<h3>' . esc_html__('Home page details', 'thepond-analytics') . '</h3>';
    $landing = thepond_analytics_google_connected() ? thepond_analytics_ga_landing($range, $force) : new WP_Error('not_connected', '');

    if (is_wp_error($landing)) {
        $html .= thepond_analytics_ga_problem($landing);
    } else {
        $dates = thepond_analytics_range_dates($range);
        $html .= '<h4>' . esc_html__('New visitors and signups per day', 'thepond-analytics') . '</h4>';
        $html .= thepond_analytics_chart($dates, array(
            array('name' => __('new visitors', 'thepond-analytics'), 'values' => $landing['daily']['visitors'], 'color' => '#2271b1'),
            array('name' => __('signups', 'thepond-analytics'), 'values' => $landing['daily']['signups'], 'color' => '#d63638'),
        ), array('label' => __('New visitors and signups per day', 'thepond-analytics')));

        $seen = $landing['new'] + $landing['returning'];
        $html .= thepond_analytics_notice(sprintf(
            /* translators: 1: percent first time, 2: percent returning */
            __('<strong>%1$s</strong> of new visitors were here for the first time. <strong>%2$s</strong> had been here before without joining, for example after an ad, an email or another YouTube video.', 'thepond-analytics'),
            thepond_analytics_percent(thepond_analytics_rate($landing['new'], $seen)),
            thepond_analytics_percent(thepond_analytics_rate($landing['returning'], $seen))
        ));

        $html .= '<div class="tpa-columns"><div>';
        $html .= '<h4>' . esc_html__('Where new visitors came from', 'thepond-analytics') . '</h4>';
        $rows = array();
        foreach (array_slice($landing['sources'], 0, 8, true) as $name => $source) {
            $rows[] = array(esc_html($name), number_format_i18n($source['visitors']), number_format_i18n($source['signups']), esc_html(thepond_analytics_percent(thepond_analytics_rate($source['signups'], $source['visitors']))));
        }
        $html .= thepond_analytics_table(array(__('Source', 'thepond-analytics'), __('Visitors', 'thepond-analytics'), __('Signups', 'thepond-analytics'), __('Conversion', 'thepond-analytics')), $rows);
        $html .= '</div><div>';

        $html .= '<h4>' . esc_html__('How far down the page they got', 'thepond-analytics') . '</h4>';
        $sections = array(
            'skills' => __('What you\'ll learn', 'thepond-analytics'),
            'free-lessons' => __('Free lessons', 'thepond-analytics'),
            'pricingSect' => __('Membership plans', 'thepond-analytics'),
            'faq' => __('Questions (bottom of page)', 'thepond-analytics'),
        );
        $items = array(array('label' => __('Arrived', 'thepond-analytics'), 'value' => $seen));
        foreach ($sections as $key => $label) {
            $items[] = array('label' => $label, 'value' => (int) ($landing['sections'][$key] ?? 0));
        }
        $html .= thepond_analytics_bars($items, array('relative_to_first' => true));
        $html .= '</div></div>';

        $html .= '<h4>' . esc_html__('What new visitors clicked', 'thepond-analytics') . '</h4>';
        $groups = array(
            'signup' => __('Start signup', 'thepond-analytics'),
            'plans' => __('See plans', 'thepond-analytics'),
            'free_lessons' => __('Free lessons', 'thepond-analytics'),
            'login' => __('Login', 'thepond-analytics'),
            'support' => __('Help', 'thepond-analytics'),
            'navigation' => __('Page navigation', 'thepond-analytics'),
        );
        $rows = array();
        foreach ($landing['ctas'] as $row) {
            $group = $row['customEvent:cta_group'];
            $rows[] = array(esc_html($row['customEvent:cta'] ?: '–'), esc_html($groups[$group] ?? ucfirst($group)), number_format_i18n($row['totalUsers']), number_format_i18n($row['eventCount']));
        }
        $html .= thepond_analytics_table(array(__('Button or link', 'thepond-analytics'), __('Type', 'thepond-analytics'), __('People', 'thepond-analytics'), __('Clicks', 'thepond-analytics')), $rows);
    }

    $sources = thepond_analytics_signup_sources($range, $force);
    $html .= '<h4>' . esc_html__('How new members found The Pond', 'thepond-analytics') . '</h4>';
    if (!$sources['total']) {
        $html .= '<p class="tpa-muted">' . esc_html__('No signups have been recorded with source details in this period yet. Details are recorded for each new signup from now on.', 'thepond-analytics') . '</p>';
        return $html;
    }
    $items = array();
    foreach ($sources['channels'] as $channel => $count) {
        $items[] = array('label' => $channel, 'value' => $count, 'note' => thepond_analytics_percent($count / $sources['total']));
    }
    $html .= '<div class="tpa-columns"><div>' . thepond_analytics_bars($items) . '</div><div><ul class="tpa-facts">';
    if ($sources['tracked_visits']) {
        $html .= '<li>' . wp_kses(sprintf(
            /* translators: 1: percent, 2: average visits */
            __('<strong>%1$s</strong> of new members visited more than once before joining (average <strong>%2$s</strong> visits)', 'thepond-analytics'),
            thepond_analytics_percent($sources['came_back'] / $sources['tracked_visits']),
            number_format_i18n($sources['avg_visits'], 1)
        ), array('strong' => array())) . '</li>';
    }
    if ($sources['median_days'] !== null) {
        $html .= '<li>' . wp_kses(sprintf(
            /* translators: 1: median days, 2: same day percent */
            __('Typical time from first visit to joining: <strong>%1$s days</strong>. <strong>%2$s</strong> joined on their first day.', 'thepond-analytics'),
            number_format_i18n($sources['median_days']),
            thepond_analytics_percent($sources['same_day'] / max(1, $sources['with_days']))
        ), array('strong' => array())) . '</li>';
    }
    foreach ($sources['methods'] as $method => $count) {
        $html .= '<li>' . esc_html(sprintf(__('Signed up with %1$s: %2$s', 'thepond-analytics'), ucfirst($method), number_format_i18n($count))) . '</li>';
    }
    return $html . '</ul></div></div>';
}

function thepond_analytics_panel_engagement(array $range, $force = false) {
    $html = '<h3>' . esc_html__('How members use The Pond', 'thepond-analytics') . '</h3>';
    $ga = thepond_analytics_google_connected() ? thepond_analytics_ga_engagement($range, $force) : new WP_Error('not_connected', '');
    if (is_wp_error($ga)) {
        $html .= thepond_analytics_ga_problem($ga);
    } else {
        $video_rate = thepond_analytics_rate($ga['video_completes'], $ga['video_starts']);
        $html .= thepond_analytics_tiles(array(
            thepond_analytics_tile(__('Time on site per member', 'thepond-analytics'), $ga['minutes_per_member'] === null ? '–' : sprintf(__('%s min', 'thepond-analytics'), number_format_i18n($ga['minutes_per_member'], 0)), sprintf(__('Total active time in the %s', 'thepond-analytics'), thepond_analytics_period_label($range))),
            thepond_analytics_tile(__('Visits per member', 'thepond-analytics'), $ga['sessions_per_member'] === null ? '–' : number_format_i18n($ga['sessions_per_member'], 1)),
            thepond_analytics_tile(__('Videos watched to the end', 'thepond-analytics'), thepond_analytics_percent($video_rate), sprintf(__('%s video plays', 'thepond-analytics'), number_format_i18n($ga['video_starts']))),
        ));
        if ($ga['devices']) {
            $items = array();
            foreach ($ga['devices'] as $device => $users) {
                $items[] = array('label' => ucfirst($device), 'value' => $users, 'note' => thepond_analytics_percent($users / max(1, array_sum($ga['devices']))));
            }
            $html .= '<h4>' . esc_html__('Devices members use', 'thepond-analytics') . '</h4>' . thepond_analytics_bars($items);
        }
        if ($ga['dashboard_areas']) {
            $items = array();
            foreach ($ga['dashboard_areas'] as $row) {
                $items[] = array('label' => ucwords(str_replace(array('-', '_'), ' ', $row['customEvent:dashboard_area'] ?: 'other')), 'value' => $row['eventCount']);
            }
            $html .= '<h4>' . esc_html__('Most-clicked dashboard areas', 'thepond-analytics') . '</h4>' . thepond_analytics_bars($items);
        }
        if ($ga['login_methods']) {
            $items = array();
            foreach ($ga['login_methods'] as $method => $count) {
                $items[] = array('label' => ucfirst($method), 'value' => $count);
            }
            $html .= '<h4>' . esc_html__('How members log in', 'thepond-analytics') . '</h4>' . thepond_analytics_bars($items);
        }
    }

    $stats = thepond_analytics_activity_stats($range, $force);
    if (!is_wp_error($stats)) {
        $rows = array();
        foreach ($stats['top'] as $item) {
            $title = $item['url'] ? '<a href="' . esc_url($item['url']) . '" target="_blank" rel="noopener">' . esc_html($item['title']) . '</a>' : esc_html($item['title']);
            $rows[] = array($title, esc_html($item['type_label']), number_format_i18n($item['members']), number_format_i18n($item['views']));
        }
        $html .= '<h4>' . esc_html__('Top 10 content', 'thepond-analytics') . '</h4>';
        $html .= thepond_analytics_table(array(__('Content', 'thepond-analytics'), __('Type', 'thepond-analytics'), __('Members', 'thepond-analytics'), __('Views', 'thepond-analytics')), $rows);
        if ($stats['types']) {
            $items = array();
            foreach ($stats['types'] as $label => $views) {
                $items[] = array('label' => $label, 'value' => $views);
            }
            $html .= '<h4>' . esc_html__('Views by content type', 'thepond-analytics') . '</h4>' . thepond_analytics_bars($items);
        }
        if ($stats['days_per_member'] !== null) {
            $html .= thepond_analytics_notice(esc_html(sprintf(
                __('Members who visited came back on %1$s different days on average in the %2$s.', 'thepond-analytics'),
                number_format_i18n($stats['days_per_member'], 1),
                thepond_analytics_period_label($range)
            )));
        }
    }

    $at_risk = thepond_analytics_at_risk_members();
    $html .= '<h4>' . esc_html__('Paying members who haven\'t visited in 30+ days', 'thepond-analytics') . '</h4>';
    $rows = array();
    foreach ($at_risk as $member) {
        $last = $member['last_seen'] ? human_time_diff(strtotime($member['last_seen'] . ' UTC')) . ' ' . __('ago', 'thepond-analytics') : __('Never', 'thepond-analytics');
        $rows[] = array(
            '<a href="' . esc_url(admin_url('user-edit.php?user_id=' . (int) $member['ID'])) . '">' . esc_html($member['display_name']) . '</a>',
            '<a href="mailto:' . esc_attr($member['user_email']) . '">' . esc_html($member['user_email']) . '</a>',
            esc_html($last),
        );
    }
    $html .= thepond_analytics_table(array(__('Member', 'thepond-analytics'), __('Email', 'thepond-analytics'), __('Last seen', 'thepond-analytics')), $rows);
    return $html;
}
