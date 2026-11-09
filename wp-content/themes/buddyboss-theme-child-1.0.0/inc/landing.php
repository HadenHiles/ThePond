<?php
defined('ABSPATH') || exit;

add_action('thepond_landing_head', 'pond_landing_faz_head', 0);
add_action('thepond_landing_footer', 'pond_landing_faz_footer');

/**
 * Replay only FAZ's registered callbacks, using its already initialized objects.
 * The standalone landing page must not enqueue the member portal's assets.
 */
function pond_landing_faz_action($hook) {
    global $wp_filter;
    if (empty($wp_filter[$hook])) {
        return;
    }
    foreach ($wp_filter[$hook]->callbacks as $callbacks) {
        foreach ($callbacks as $entry) {
            $callback = $entry['function'];
            if (!is_array($callback)) {
                continue;
            }
            $owner = is_object($callback[0]) ? get_class($callback[0]) : $callback[0];
            if (strpos($owner, 'FazCookie\\') === 0) {
                call_user_func($callback);
            }
        }
    }
}

function pond_landing_faz_head() {
    global $pond_landing_faz_scripts;
    $scripts = wp_scripts();
    $styles = wp_styles();
    $previous_scripts = $scripts->queue;
    $previous_styles = $styles->queue;

    pond_landing_faz_action('wp_enqueue_scripts');
    pond_landing_faz_action('wp_head');

    $pond_landing_faz_scripts = array_values(array_diff($scripts->queue, $previous_scripts));
    $style_handles = array_values(array_diff($styles->queue, $previous_styles));
    if ($style_handles) {
        wp_print_styles($style_handles);
    }
    if ($pond_landing_faz_scripts) {
        $scripts->do_items($pond_landing_faz_scripts, 0);
    }
}

function pond_landing_faz_footer() {
    global $pond_landing_faz_scripts;
    pond_landing_faz_action('wp_footer');
    if (!empty($pond_landing_faz_scripts)) {
        wp_scripts()->do_items($pond_landing_faz_scripts, 1);
    }
}
