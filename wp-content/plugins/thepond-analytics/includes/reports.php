<?php
defined('ABSPATH') || exit;

const THEPOND_ANALYTICS_FUNNEL_EVENTS = array('landing_view', 'landing_cta_click', 'begin_checkout', 'checkout_submit', 'sign_up', 'purchase', 'login');
const THEPOND_ANALYTICS_NON_MEMBERS = array('prospect', 'former_member');

function thepond_analytics_allowed_days($days) {
    $days = (int) $days;
    return in_array($days, array(7, 30, 90), true) ? $days : 30;
}

/**
 * Current and previous periods of equal length, ending today, in the site's
 * timezone. UTC bounds are used for MemberPress tables, which store GMT.
 */
function thepond_analytics_range($days) {
    $days = thepond_analytics_allowed_days($days);
    $timezone = wp_timezone();
    $end = new DateTimeImmutable('today', $timezone);
    $start = $end->modify('-' . ($days - 1) . ' days');
    $prev_end = $start->modify('-1 day');
    $prev_start = $prev_end->modify('-' . ($days - 1) . ' days');
    $utc = new DateTimeZone('UTC');
    $to_utc = function (DateTimeImmutable $date, $end_of_day = false) use ($utc) {
        $date = $end_of_day ? $date->setTime(23, 59, 59) : $date->setTime(0, 0, 0);
        return $date->setTimezone($utc)->format('Y-m-d H:i:s');
    };

    return array(
        'days' => $days,
        'start' => $start,
        'end' => $end,
        'prev_start' => $prev_start,
        'prev_end' => $prev_end,
        'start_utc' => $to_utc($start),
        'end_utc' => $to_utc($end, true),
        'prev_start_utc' => $to_utc($prev_start),
        'prev_end_utc' => $to_utc($prev_end, true),
        'ga' => array('startDate' => $start->format('Y-m-d'), 'endDate' => $end->format('Y-m-d')),
        'ga_prev' => array('startDate' => $prev_start->format('Y-m-d'), 'endDate' => $prev_end->format('Y-m-d')),
    );
}

function thepond_analytics_range_dates(array $range) {
    $dates = array();
    for ($date = $range['start']; $date <= $range['end']; $date = $date->modify('+1 day')) {
        $dates[] = $date->format('Y-m-d');
    }
    return $dates;
}

function thepond_analytics_cached($key, callable $callback, $force = false, $ttl = 900) {
    $key = 'thepond_analytics_local_' . md5($key);
    if (!$force) {
        $cached = get_transient($key);
        if ($cached !== false) {
            return $cached;
        }
    }
    $value = $callback();
    set_transient($key, $value, $ttl);
    thepond_analytics_remember_cache_key($key);
    return $value;
}

function thepond_analytics_change($current, $previous) {
    if (!$previous) {
        return null;
    }
    return ($current - $previous) / $previous;
}

function thepond_analytics_rate($part, $whole) {
    return $whole > 0 ? $part / $whole : null;
}

/* ---------- GA filter helpers ---------- */

function thepond_analytics_ga_in($field, array $values) {
    return array('filter' => array('fieldName' => $field, 'inListFilter' => array('values' => array_values($values))));
}

function thepond_analytics_ga_eq($field, $value) {
    return array('filter' => array('fieldName' => $field, 'stringFilter' => array('matchType' => 'EXACT', 'value' => $value)));
}

function thepond_analytics_ga_and(array ...$expressions) {
    return array('andGroup' => array('expressions' => $expressions));
}

function thepond_analytics_ga_or(array ...$expressions) {
    return array('orGroup' => array('expressions' => $expressions));
}

function thepond_analytics_ga_request($dates, array $dimensions, array $metrics, $filter = null, $limit = 0, $order_metric = '') {
    $request = array(
        'dateRanges' => array($dates),
        'dimensions' => array_map(function ($name) {
            return array('name' => $name);
        }, $dimensions),
        'metrics' => array_map(function ($name) {
            return array('name' => $name);
        }, $metrics),
    );
    if ($filter) {
        $request['dimensionFilter'] = $filter;
    }
    if ($limit) {
        $request['limit'] = $limit;
    }
    if ($order_metric) {
        $request['orderBys'] = array(array('metric' => array('metricName' => $order_metric), 'desc' => true));
    }
    return $request;
}

/**
 * Sums a metric over rows matching every dimension value in $where.
 */
function thepond_analytics_sum(array $rows, $metric, array $where = array()) {
    $total = 0;
    foreach ($rows as $row) {
        foreach ($where as $dimension => $accepted) {
            $value = $row[$dimension] ?? '';
            if (is_array($accepted) ? !in_array($value, $accepted, true) : $value !== $accepted) {
                continue 2;
            }
        }
        $total += $row[$metric] ?? 0;
    }
    return $total;
}

/* ---------- GA reports ---------- */

function thepond_analytics_ga_funnel(array $range, $force = false) {
    $visitor = 'customEvent:visitor_type';
    $events = thepond_analytics_ga_in('eventName', THEPOND_ANALYTICS_FUNNEL_EVENTS);
    $plan_clicks = thepond_analytics_ga_and(
        thepond_analytics_ga_eq('eventName', 'landing_cta_click'),
        thepond_analytics_ga_in('customEvent:cta_group', array('signup', 'plans'))
    );
    $login_clicks = thepond_analytics_ga_and(
        thepond_analytics_ga_eq('eventName', 'landing_cta_click'),
        thepond_analytics_ga_eq('customEvent:cta_group', 'login')
    );
    $reports = thepond_analytics_batch_reports(array(
        thepond_analytics_ga_request($range['ga'], array('eventName', $visitor), array('totalUsers', 'eventCount'), $events),
        thepond_analytics_ga_request($range['ga_prev'], array('eventName', $visitor), array('totalUsers', 'eventCount'), $events),
        thepond_analytics_ga_request($range['ga'], array($visitor), array('totalUsers'), $plan_clicks),
        thepond_analytics_ga_request($range['ga_prev'], array($visitor), array('totalUsers'), $plan_clicks),
        thepond_analytics_ga_request($range['ga'], array($visitor), array('totalUsers'), $login_clicks),
    ), $force);
    if (is_wp_error($reports)) {
        return $reports;
    }
    list($current, $previous, $plans, $plans_prev, $logins) = array_pad($reports, 5, array());

    $non_members = array($visitor => THEPOND_ANALYTICS_NON_MEMBERS);
    $step = function ($rows, $event, $who = null) use ($visitor) {
        $where = array('eventName' => $event);
        if ($who) {
            $where[$visitor] = $who;
        }
        return thepond_analytics_sum($rows, 'totalUsers', $where);
    };

    $landing = $step($current, 'landing_view', THEPOND_ANALYTICS_NON_MEMBERS);
    $landing_prev = $step($previous, 'landing_view', THEPOND_ANALYTICS_NON_MEMBERS);
    // Logged-out members on a new device look like prospects until they click Login.
    $unrecognized_logins = thepond_analytics_sum($logins, 'totalUsers', $non_members);
    $prospects = max(0, $landing - $unrecognized_logins);

    return array(
        'landing' => $landing,
        'landing_prev' => $landing_prev,
        'prospects' => $prospects,
        'plan_clickers' => thepond_analytics_sum($plans, 'totalUsers', $non_members),
        'plan_clickers_prev' => thepond_analytics_sum($plans_prev, 'totalUsers', $non_members),
        'checkout' => $step($current, 'begin_checkout', THEPOND_ANALYTICS_NON_MEMBERS),
        'checkout_prev' => $step($previous, 'begin_checkout', THEPOND_ANALYTICS_NON_MEMBERS),
        'submitted' => $step($current, 'checkout_submit', THEPOND_ANALYTICS_NON_MEMBERS),
        'purchases' => $step($current, 'purchase'),
        'member_landing' => $step($current, 'landing_view', 'member'),
        'member_login_clicks' => thepond_analytics_sum($logins, 'totalUsers', array($visitor => 'member')),
        'unrecognized_logins' => $unrecognized_logins,
        'logins' => thepond_analytics_sum($current, 'eventCount', array('eventName' => 'login')),
    );
}

function thepond_analytics_ga_landing(array $range, $force = false) {
    $visitor = 'customEvent:visitor_type';
    $non_member = thepond_analytics_ga_in($visitor, THEPOND_ANALYTICS_NON_MEMBERS);
    $landing_or_purchase = thepond_analytics_ga_or(
        thepond_analytics_ga_and(thepond_analytics_ga_eq('eventName', 'landing_view'), $non_member),
        thepond_analytics_ga_eq('eventName', 'purchase')
    );
    $reports = thepond_analytics_batch_reports(array(
        thepond_analytics_ga_request($range['ga'], array('customEvent:cta_group', 'customEvent:cta'), array('totalUsers', 'eventCount'), thepond_analytics_ga_and(thepond_analytics_ga_eq('eventName', 'landing_cta_click'), $non_member), 15, 'eventCount'),
        thepond_analytics_ga_request($range['ga'], array('sessionDefaultChannelGroup', 'eventName'), array('totalUsers'), $landing_or_purchase, 40),
        thepond_analytics_ga_request($range['ga'], array('date', 'eventName'), array('totalUsers'), $landing_or_purchase, 400),
        thepond_analytics_ga_request($range['ga'], array('newVsReturning'), array('totalUsers'), thepond_analytics_ga_and(thepond_analytics_ga_eq('eventName', 'landing_view'), $non_member)),
        thepond_analytics_ga_request($range['ga'], array('customEvent:section'), array('totalUsers'), thepond_analytics_ga_and(thepond_analytics_ga_eq('eventName', 'landing_section_view'), $non_member), 20),
    ), $force);
    if (is_wp_error($reports)) {
        return $reports;
    }
    list($ctas, $channels, $trend, $returning, $sections) = array_pad($reports, 5, array());

    $sources = array();
    foreach ($channels as $row) {
        $name = $row['sessionDefaultChannelGroup'] ?: 'Unassigned';
        if (!isset($sources[$name])) {
            $sources[$name] = array('visitors' => 0, 'signups' => 0);
        }
        $sources[$name][$row['eventName'] === 'purchase' ? 'signups' : 'visitors'] += $row['totalUsers'];
    }
    uasort($sources, function ($a, $b) {
        return $b['visitors'] <=> $a['visitors'];
    });

    $daily = array('visitors' => array(), 'signups' => array());
    foreach ($trend as $row) {
        $date = substr($row['date'], 0, 4) . '-' . substr($row['date'], 4, 2) . '-' . substr($row['date'], 6, 2);
        $series = $row['eventName'] === 'purchase' ? 'signups' : 'visitors';
        $daily[$series][$date] = ($daily[$series][$date] ?? 0) + $row['totalUsers'];
    }

    $section_users = array();
    foreach ($sections as $row) {
        $section_users[$row['customEvent:section']] = $row['totalUsers'];
    }

    return array(
        'ctas' => $ctas,
        'sources' => $sources,
        'daily' => $daily,
        'new' => thepond_analytics_sum($returning, 'totalUsers', array('newVsReturning' => 'new')),
        'returning' => thepond_analytics_sum($returning, 'totalUsers', array('newVsReturning' => 'returning')),
        'sections' => $section_users,
    );
}

function thepond_analytics_ga_engagement(array $range, $force = false) {
    $member = thepond_analytics_ga_eq('customUser:member_status', 'member');
    $reports = thepond_analytics_batch_reports(array(
        thepond_analytics_ga_request($range['ga'], array(), array('activeUsers', 'userEngagementDuration', 'sessions', 'screenPageViews'), $member),
        thepond_analytics_ga_request($range['ga'], array('eventName'), array('eventCount', 'totalUsers'), thepond_analytics_ga_and(thepond_analytics_ga_in('eventName', array('video_start', 'video_complete')), $member)),
        thepond_analytics_ga_request($range['ga'], array('customEvent:method'), array('eventCount'), thepond_analytics_ga_eq('eventName', 'login')),
        thepond_analytics_ga_request($range['ga'], array('customEvent:dashboard_area'), array('eventCount', 'totalUsers'), thepond_analytics_ga_eq('eventName', 'dashboard_click'), 10, 'eventCount'),
        thepond_analytics_ga_request($range['ga'], array('deviceCategory'), array('activeUsers'), $member),
    ), $force);
    if (is_wp_error($reports)) {
        return $reports;
    }
    list($totals, $videos, $logins, $areas, $devices) = array_pad($reports, 5, array());
    $total = $totals[0] ?? array();
    $users = (float) ($total['activeUsers'] ?? 0);

    $login_methods = array();
    foreach ($logins as $row) {
        $login_methods[$row['customEvent:method'] ?: 'unknown'] = $row['eventCount'];
    }
    $device_users = array();
    foreach ($devices as $row) {
        $device_users[$row['deviceCategory']] = $row['activeUsers'];
    }

    return array(
        'members' => $users,
        'minutes_per_member' => $users ? ($total['userEngagementDuration'] ?? 0) / 60 / $users : null,
        'sessions_per_member' => $users ? ($total['sessions'] ?? 0) / $users : null,
        'video_starts' => thepond_analytics_sum($videos, 'eventCount', array('eventName' => 'video_start')),
        'video_completes' => thepond_analytics_sum($videos, 'eventCount', array('eventName' => 'video_complete')),
        'login_methods' => $login_methods,
        'dashboard_areas' => $areas,
        'devices' => $device_users,
    );
}

/* ---------- MemberPress ---------- */

function thepond_analytics_mepr_available() {
    return class_exists('MeprDb') && class_exists('MeprReports') && class_exists('MeprTransaction');
}

/**
 * New signups are the first completed payment of a subscription (or a one-time
 * purchase), matching what GA records as a purchase.
 */
function thepond_analytics_new_signups($start_utc, $end_utc) {
    global $wpdb;
    $mepr_db = new MeprDb();
    $txns = $mepr_db->transactions;
    return $wpdb->get_results($wpdb->prepare(
        "SELECT t.id, t.user_id, t.product_id, t.total, t.created_at,
            EXISTS (SELECT 1 FROM $txns p WHERE p.user_id = t.user_id AND p.id < t.id AND p.status IN ('complete','refunded') AND p.txn_type = 'payment') AS paid_before
         FROM $txns t
         WHERE t.status = 'complete' AND t.txn_type = 'payment' AND t.created_at BETWEEN %s AND %s
           AND (t.subscription_id = 0 OR NOT EXISTS (
                SELECT 1 FROM $txns e WHERE e.subscription_id = t.subscription_id AND e.id < t.id AND e.status IN ('complete','refunded') AND e.txn_type = 'payment'))",
        $start_utc,
        $end_utc
    ), ARRAY_A);
}

function thepond_analytics_active_member_sql() {
    global $wpdb;
    $mepr_db = new MeprDb();
    $now = class_exists('MeprUtils') ? MeprUtils::db_now() : gmdate('Y-m-d H:i:s');
    $lifetime = class_exists('MeprUtils') ? MeprUtils::db_lifetime() : '0000-00-00 00:00:00';
    return $wpdb->prepare(
        "SELECT DISTINCT tr.user_id FROM {$mepr_db->transactions} tr
         WHERE (tr.expires_at >= %s OR tr.expires_at IS NULL OR tr.expires_at = %s) AND tr.status IN ('complete','confirmed')",
        $now,
        $lifetime
    );
}

function thepond_analytics_member_stats(array $range, $force = false) {
    if (!thepond_analytics_mepr_available()) {
        return new WP_Error('no_memberpress', __('MemberPress is not active.', 'thepond-analytics'));
    }
    return thepond_analytics_cached('members:' . $range['start_utc'] . $range['end_utc'], function () use ($range) {
        global $wpdb;
        $mepr_db = new MeprDb();
        $timezone = wp_timezone();

        $signups = thepond_analytics_new_signups($range['start_utc'], $range['end_utc']);
        $prev_signups = thepond_analytics_new_signups($range['prev_start_utc'], $range['prev_end_utc']);
        $daily = array_fill_keys(thepond_analytics_range_dates($range), 0);
        $plans = array();
        $returning = 0;
        foreach ($signups as $row) {
            $day = (new DateTimeImmutable($row['created_at'], new DateTimeZone('UTC')))->setTimezone($timezone)->format('Y-m-d');
            if (isset($daily[$day])) {
                $daily[$day]++;
            }
            $plan = get_the_title((int) $row['product_id']) ?: __('Unknown plan', 'thepond-analytics');
            $plans[$plan] = ($plans[$plan] ?? 0) + 1;
            $returning += (int) $row['paid_before'];
        }
        arsort($plans);

        $start = $range['start'];
        $end = $range['end'];
        $revenue = (float) MeprReports::get_date_range_revenue($start, $end);
        $revenue_prev = (float) MeprReports::get_date_range_revenue($range['prev_start'], $range['prev_end']);
        $refunds = (float) MeprReports::get_date_range_refunds($start, $end);
        $active = (int) MeprReports::get_active_members_count();
        $cancellations = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$mepr_db->events} WHERE event = 'subscription-stopped' AND created_at BETWEEN %s AND %s",
            $range['start_utc'],
            $range['end_utc']
        ));
        $cancellations_prev = (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$mepr_db->events} WHERE event = 'subscription-stopped' AND created_at BETWEEN %s AND %s",
            $range['prev_start_utc'],
            $range['prev_end_utc']
        ));

        return array(
            'active' => $active,
            'signups' => count($signups),
            'signups_prev' => count($prev_signups),
            'returning_signups' => $returning,
            'daily' => $daily,
            'plans' => $plans,
            'revenue' => $revenue,
            'revenue_prev' => $revenue_prev,
            'refunds' => $refunds,
            'cancellations' => $cancellations,
            'cancellations_prev' => $cancellations_prev,
            'lifetime_value' => (float) MeprReports::get_average_lifetime_value(),
            'rebill_rate' => (float) MeprReports::get_percentage_members_who_rebill(),
            'currency' => class_exists('MeprOptions') ? MeprOptions::fetch()->currency_symbol : '$',
        );
    }, $force);
}

function thepond_analytics_signup_sources(array $range, $force = false) {
    return thepond_analytics_cached('sources:' . $range['start_utc'] . $range['end_utc'], function () use ($range) {
        global $wpdb;
        $table = $wpdb->prefix . 'pond_signups';
        $rows = $wpdb->get_results($wpdb->prepare(
            "SELECT channel, signup_method, site_visits, ga_sessions, days_to_convert, rejoined FROM $table WHERE created_at BETWEEN %s AND %s",
            $range['start_utc'],
            $range['end_utc']
        ), ARRAY_A);
        $first = $wpdb->get_var("SELECT MIN(created_at) FROM $table");

        $channels = array();
        $methods = array();
        $days = array();
        $came_back = 0;
        $visits = array();
        foreach ($rows as $row) {
            $channel = $row['channel'] ?: 'Unknown';
            $channels[$channel] = ($channels[$channel] ?? 0) + 1;
            if ($row['signup_method']) {
                $methods[$row['signup_method']] = ($methods[$row['signup_method']] ?? 0) + 1;
            }
            if ($row['days_to_convert'] !== null) {
                $days[] = (int) $row['days_to_convert'];
            }
            $sessions = max((int) $row['site_visits'], (int) $row['ga_sessions']);
            if ($sessions > 0) {
                $visits[] = $sessions;
                $came_back += $sessions > 1 ? 1 : 0;
            }
        }
        arsort($channels);
        arsort($methods);
        sort($days);

        return array(
            'total' => count($rows),
            'channels' => $channels,
            'methods' => $methods,
            'tracked_visits' => count($visits),
            'came_back' => $came_back,
            'avg_visits' => $visits ? array_sum($visits) / count($visits) : null,
            'median_days' => $days ? $days[(int) floor((count($days) - 1) / 2)] : null,
            'same_day' => count(array_filter($days, function ($d) {
                return $d === 0;
            })),
            'with_days' => count($days),
            'since' => $first,
        );
    }, $force);
}

/* ---------- Member activity ---------- */

function thepond_analytics_last_activity_table() {
    global $wpdb;
    $table = $wpdb->base_prefix . 'bp_activity';
    $exists = $wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($table)));
    return $exists === $table ? $table : null;
}

function thepond_analytics_activity_stats(array $range, $force = false) {
    if (!thepond_analytics_mepr_available()) {
        return new WP_Error('no_memberpress', __('MemberPress is not active.', 'thepond-analytics'));
    }
    return thepond_analytics_cached('activity:' . $range['start_utc'] . $range['end_utc'], function () use ($range) {
        global $wpdb;
        $views = $wpdb->prefix . 'pond_content_views';
        $active_sql = thepond_analytics_active_member_sql();
        $active = (int) $wpdb->get_var("SELECT COUNT(*) FROM ($active_sql) a");
        $start = $range['start']->format('Y-m-d');
        $end = $range['end']->format('Y-m-d');

        $seen = array('7' => null, '30' => null);
        $bp = thepond_analytics_last_activity_table();
        if ($bp) {
            foreach (array(7, 30) as $window) {
                $seen[(string) $window] = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT a.user_id) FROM ($active_sql) a
                     JOIN $bp b ON b.user_id = a.user_id AND b.component = 'members' AND b.type = 'last_activity'
                     WHERE b.date_recorded >= %s",
                    gmdate('Y-m-d H:i:s', time() - $window * DAY_IN_SECONDS)
                ));
            }
        } else {
            foreach (array(7, 30) as $window) {
                $seen[(string) $window] = (int) $wpdb->get_var($wpdb->prepare(
                    "SELECT COUNT(DISTINCT v.user_id) FROM $views v JOIN ($active_sql) a ON a.user_id = v.user_id WHERE v.view_date >= %s",
                    wp_date('Y-m-d', time() - ($window - 1) * DAY_IN_SECONDS)
                ));
            }
        }

        $daily_rows = $wpdb->get_results($wpdb->prepare(
            "SELECT view_date, COUNT(DISTINCT user_id) AS members FROM $views WHERE view_date BETWEEN %s AND %s GROUP BY view_date",
            $start,
            $end
        ), ARRAY_A);
        $daily = array_fill_keys(thepond_analytics_range_dates($range), 0);
        foreach ($daily_rows as $row) {
            $daily[$row['view_date']] = (int) $row['members'];
        }

        $totals = $wpdb->get_row($wpdb->prepare(
            "SELECT COUNT(DISTINCT user_id) AS members, COALESCE(SUM(CASE WHEN post_type <> 'page' THEN views ELSE 0 END), 0) AS content_views,
                    COUNT(DISTINCT CONCAT(user_id, '-', view_date)) AS member_days
             FROM $views WHERE view_date BETWEEN %s AND %s",
            $start,
            $end
        ), ARRAY_A);
        $members = (int) ($totals['members'] ?? 0);

        $top = $wpdb->get_results($wpdb->prepare(
            "SELECT post_id, post_type, COUNT(DISTINCT user_id) AS members, SUM(views) AS views FROM $views
             WHERE view_date BETWEEN %s AND %s AND post_type <> 'page'
             GROUP BY post_id, post_type ORDER BY members DESC, views DESC LIMIT 10",
            $start,
            $end
        ), ARRAY_A);
        foreach ($top as &$item) {
            $item['title'] = get_the_title((int) $item['post_id']) ?: '#' . $item['post_id'];
            $item['url'] = get_permalink((int) $item['post_id']);
            $type = get_post_type_object($item['post_type']);
            $item['type_label'] = $type ? $type->labels->singular_name : $item['post_type'];
        }
        unset($item);

        $types = $wpdb->get_results($wpdb->prepare(
            "SELECT post_type, SUM(views) AS views FROM $views WHERE view_date BETWEEN %s AND %s AND post_type <> 'page' GROUP BY post_type ORDER BY views DESC",
            $start,
            $end
        ), ARRAY_A);
        $type_views = array();
        foreach ($types as $row) {
            $type = get_post_type_object($row['post_type']);
            $type_views[$type ? $type->labels->name : $row['post_type']] = (int) $row['views'];
        }

        $completions = array();
        $learndash = $wpdb->prefix . 'learndash_user_activity';
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $wpdb->esc_like($learndash))) === $learndash) {
            $rows = $wpdb->get_results($wpdb->prepare(
                "SELECT activity_type, COUNT(*) AS total FROM $learndash
                 WHERE activity_status = 1 AND activity_completed BETWEEN %d AND %d AND activity_type IN ('course','lesson','topic','quiz')
                 GROUP BY activity_type",
                strtotime($range['start_utc'] . ' UTC'),
                strtotime($range['end_utc'] . ' UTC')
            ), ARRAY_A);
            foreach ($rows as $row) {
                $completions[$row['activity_type']] = (int) $row['total'];
            }
        }

        $first_view = $wpdb->get_var("SELECT MIN(view_date) FROM $views");

        return array(
            'active' => $active,
            'seen_7' => $seen['7'],
            'seen_30' => $seen['30'],
            'at_risk' => $seen['30'] !== null ? max(0, $active - $seen['30']) : null,
            'daily' => $daily,
            'members_viewing' => $members,
            'views_per_member' => $members ? (int) $totals['content_views'] / $members : null,
            'days_per_member' => $members ? (int) $totals['member_days'] / $members : null,
            'top' => $top,
            'types' => $type_views,
            'completions' => $completions,
            'logging_since' => $first_view,
        );
    }, $force);
}

/**
 * Paying members with no activity in 30+ days, longest-idle first, so the
 * coach can reach out before they cancel.
 */
function thepond_analytics_at_risk_members($limit = 25) {
    global $wpdb;
    if (!thepond_analytics_mepr_available()) {
        return array();
    }
    $bp = thepond_analytics_last_activity_table();
    $active_sql = thepond_analytics_active_member_sql();
    $cutoff = gmdate('Y-m-d H:i:s', time() - 30 * DAY_IN_SECONDS);
    if ($bp) {
        $sql = "SELECT u.ID, u.display_name, u.user_email, u.user_registered, MAX(b.date_recorded) AS last_seen
                FROM ($active_sql) a JOIN {$wpdb->users} u ON u.ID = a.user_id
                LEFT JOIN $bp b ON b.user_id = a.user_id AND b.component = 'members' AND b.type = 'last_activity'
                GROUP BY u.ID HAVING last_seen IS NULL OR last_seen < %s
                ORDER BY last_seen IS NULL DESC, last_seen ASC LIMIT %d";
    } else {
        $views = $wpdb->prefix . 'pond_content_views';
        $sql = "SELECT u.ID, u.display_name, u.user_email, u.user_registered, MAX(v.last_viewed) AS last_seen
                FROM ($active_sql) a JOIN {$wpdb->users} u ON u.ID = a.user_id
                LEFT JOIN $views v ON v.user_id = a.user_id
                GROUP BY u.ID HAVING last_seen IS NULL OR last_seen < %s
                ORDER BY last_seen IS NULL DESC, last_seen ASC LIMIT %d";
    }
    return $wpdb->get_results($wpdb->prepare($sql, $cutoff, $limit), ARRAY_A);
}
