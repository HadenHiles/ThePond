<?php
defined('ABSPATH') || exit;

/**
 * Small server-rendered SVG charts. No external chart library, so nothing
 * extra loads in wp-admin and the widgets work offline.
 */
function thepond_analytics_chart(array $dates, array $series, array $options = array()) {
    $options = array_merge(array('height' => 150, 'type' => 'line', 'label' => ''), $options);
    $width = 600;
    $height = (int) $options['height'];
    $pad_left = 34;
    $pad_bottom = 20;
    $pad_top = 8;
    $plot_w = $width - $pad_left - 6;
    $plot_h = $height - $pad_bottom - $pad_top;
    $count = max(1, count($dates));

    $max = 0;
    foreach ($series as $item) {
        foreach ($dates as $date) {
            $max = max($max, (float) ($item['values'][$date] ?? 0));
        }
    }
    $max = thepond_analytics_nice_max($max);

    $svg = sprintf(
        '<svg class="tpa-chart" viewBox="0 0 %1$d %2$d" role="img" aria-label="%3$s" preserveAspectRatio="none">',
        $width,
        $height,
        esc_attr($options['label'])
    );
    foreach (array(0, 0.5, 1) as $fraction) {
        $y = $pad_top + $plot_h - $plot_h * $fraction;
        $svg .= sprintf('<line x1="%d" x2="%d" y1="%.1f" y2="%.1f" class="tpa-grid"/>', $pad_left, $width - 6, $y, $y);
        $svg .= sprintf('<text x="%d" y="%.1f" class="tpa-axis" text-anchor="end">%s</text>', $pad_left - 5, $y + 4, esc_html(thepond_analytics_short_number($max * $fraction)));
    }

    $step = $plot_w / $count;
    foreach ($series as $index => $item) {
        $color = $item['color'] ?? '#2271b1';
        if ($options['type'] === 'bar') {
            $bar_w = max(1, $step * 0.7 / max(1, count($series)));
            foreach ($dates as $i => $date) {
                $value = (float) ($item['values'][$date] ?? 0);
                $bar_h = $max ? $plot_h * $value / $max : 0;
                $x = $pad_left + $step * $i + $step * 0.15 + $bar_w * $index;
                $svg .= sprintf(
                    '<rect x="%.1f" y="%.1f" width="%.1f" height="%.1f" fill="%s" rx="1"><title>%s: %s %s</title></rect>',
                    $x,
                    $pad_top + $plot_h - $bar_h,
                    $bar_w,
                    $bar_h,
                    esc_attr($color),
                    esc_html(thepond_analytics_date_label($date)),
                    esc_html(number_format_i18n($value)),
                    esc_html($item['name'])
                );
            }
            continue;
        }
        $points = array();
        foreach ($dates as $i => $date) {
            $value = (float) ($item['values'][$date] ?? 0);
            $points[] = sprintf('%.1f,%.1f', $pad_left + $step * $i + $step / 2, $pad_top + $plot_h - ($max ? $plot_h * $value / $max : 0));
        }
        $svg .= sprintf('<polyline points="%s" fill="none" stroke="%s" stroke-width="2" vector-effect="non-scaling-stroke"/>', implode(' ', $points), esc_attr($color));
        foreach ($dates as $i => $date) {
            list($x, $y) = explode(',', $points[$i]);
            $svg .= sprintf(
                '<circle cx="%s" cy="%s" r="6" fill="transparent"><title>%s: %s %s</title></circle>',
                $x,
                $y,
                esc_html(thepond_analytics_date_label($date)),
                esc_html(number_format_i18n((float) ($item['values'][$date] ?? 0))),
                esc_html($item['name'])
            );
        }
    }

    $label_every = max(1, (int) ceil($count / 6));
    foreach ($dates as $i => $date) {
        if ($i % $label_every !== 0) {
            continue;
        }
        $svg .= sprintf('<text x="%.1f" y="%d" class="tpa-axis" text-anchor="middle">%s</text>', $pad_left + $step * $i + $step / 2, $height - 4, esc_html(thepond_analytics_date_label($date)));
    }
    $svg .= '</svg>';

    if (count($series) > 1) {
        $svg .= '<p class="tpa-legend">';
        foreach ($series as $item) {
            $svg .= sprintf('<span><i style="background:%s"></i>%s</span>', esc_attr($item['color'] ?? '#2271b1'), esc_html($item['name']));
        }
        $svg .= '</p>';
    }
    return $svg;
}

function thepond_analytics_nice_max($value) {
    if ($value <= 0) {
        return 1;
    }
    $magnitude = pow(10, floor(log10($value)));
    foreach (array(1, 2, 2.5, 5, 10) as $multiple) {
        if ($value <= $multiple * $magnitude) {
            return $multiple * $magnitude;
        }
    }
    return 10 * $magnitude;
}

function thepond_analytics_short_number($value) {
    if ($value >= 1000) {
        return round($value / 1000, 1) . 'k';
    }
    return (string) round($value, $value < 10 && floor($value) != $value ? 1 : 0);
}

function thepond_analytics_date_label($date) {
    $time = strtotime($date . ' 12:00:00');
    return $time ? wp_date('M j', $time) : $date;
}

/**
 * Horizontal bars for funnels and breakdowns. Each step shows its share of
 * the first step so "how many made it this far" reads at a glance.
 */
function thepond_analytics_bars(array $items, array $options = array()) {
    $options = array_merge(array('relative_to_first' => false, 'format' => 'number'), $options);
    if (!$items) {
        return '<p class="tpa-muted">' . esc_html__('No data for this period yet.', 'thepond-analytics') . '</p>';
    }
    $values = array_column($items, 'value');
    $base = $options['relative_to_first'] ? (float) reset($values) : (float) max($values);
    $html = '<ul class="tpa-bars">';
    foreach ($items as $item) {
        $value = (float) $item['value'];
        $share = $base > 0 ? min(1, $value / $base) : 0;
        $note = $item['note'] ?? ($options['relative_to_first'] && $base > 0 ? thepond_analytics_percent($value / $base) : '');
        $html .= sprintf(
            '<li><span class="tpa-bar-label">%s</span><span class="tpa-bar-track"><span class="tpa-bar-fill" style="width:%.1f%%"></span></span><span class="tpa-bar-value">%s%s</span></li>',
            esc_html($item['label']),
            $share * 100,
            esc_html($options['format'] === 'money' ? thepond_analytics_money($value) : number_format_i18n($value)),
            $note !== '' ? ' <small>' . esc_html($note) . '</small>' : ''
        );
    }
    return $html . '</ul>';
}

function thepond_analytics_percent($fraction, $decimals = 1) {
    if ($fraction === null) {
        return '–';
    }
    $value = $fraction * 100;
    return number_format_i18n($value, $value >= 10 || floor($value) == $value ? 0 : $decimals) . '%';
}

function thepond_analytics_money($amount, $symbol = null) {
    $symbol = $symbol ?? (class_exists('MeprOptions') ? MeprOptions::fetch()->currency_symbol : '$');
    return $symbol . number_format_i18n((float) $amount, 0);
}
