<?php
namespace FazCookie\Frontend {
    class Frontend {
        public function enqueue() {
            \wp_scripts()->queue[] = 'faz-config';
            \wp_scripts()->queue[] = 'faz-runtime';
            \wp_scripts()->queue[] = 'faz-a11y';
            \wp_styles()->queue[] = 'faz-css';
        }
        public function defaults() { echo 'consent-defaults;'; }
        public function styles() { echo 'placeholder-css;'; }
        public function banner() { echo 'banner;'; }
    }
}

namespace {
    define('ABSPATH', dirname(__DIR__, 4) . '/');
    require ABSPATH . 'wp-includes/plugin.php';
    require ABSPATH . 'wp-includes/class-wp-dependency.php';
    require ABSPATH . 'wp-includes/class-wp-dependencies.php';
    require ABSPATH . 'wp-content/themes/buddyboss-theme-child-1.0.0/inc/landing.php';

    // Use WordPress's real dependency resolver, replacing only HTML emission.
    class LandingDependencies extends WP_Dependencies {
        public function do_item($handle, $group = false) {
            if ($handle === 'faz-a11y' && $group === 0) {
                return false;
            }
            echo $handle . ';';
            return true;
        }
    }
    class UnrelatedPlugin {
        public static function render() { throw new \RuntimeException('Unrelated plugin executed'); }
    }
    $scripts = new LandingDependencies();
    $styles = new LandingDependencies();
    function wp_scripts() { global $scripts; return $scripts; }
    function wp_styles() { global $styles; return $styles; }
    function wp_print_styles($handles) { wp_styles()->do_items($handles); }
    function check($condition, $message) {
        if (!$condition) {
            throw new \RuntimeException($message);
        }
    }
    function output($hook) {
        ob_start();
        do_action($hook);
        return ob_get_clean();
    }

    $scripts->add('unrelated-script', '/portal.js');
    $styles->add('unrelated-style', '/portal.css');
    $scripts->enqueue('unrelated-script');
    $styles->enqueue('unrelated-style');
    check(output('thepond_landing_head') === '', 'Inactive FAZ must not flush unrelated assets');
    check(output('thepond_landing_footer') === '', 'Inactive FAZ must not print footer assets');

    $faz = new \FazCookie\Frontend\Frontend();
    add_action('wp_enqueue_scripts', array($faz, 'enqueue'), 1);
    // Add out of priority order to verify the consent defaults still print first.
    add_action('wp_head', array($faz, 'styles'), 10);
    add_action('wp_head', array($faz, 'defaults'), 0);
    add_action('wp_footer', array($faz, 'banner'));
    foreach (array('wp_enqueue_scripts', 'wp_head', 'wp_footer') as $hook) {
        add_action($hook, function () { throw new \RuntimeException('Unrelated hook executed'); });
        add_action($hook, array('UnrelatedPlugin', 'render'));
    }
    $scripts->add('faz-config', '/faz-config.js');
    $scripts->add('faz-runtime', '/faz.js', array('faz-config'));
    $scripts->add('faz-a11y', '/a11y.js', array('faz-runtime'));
    $styles->add('faz-css', '/faz.css');

    check(output('thepond_landing_head') === 'consent-defaults;placeholder-css;faz-css;faz-config;faz-runtime;',
        'FAZ head must preserve priorities, config dependencies and styles without unrelated assets');
    check(output('thepond_landing_footer') === 'banner;faz-a11y;',
        'FAZ banner and footer scripts must render without duplicating head scripts');
    check(!in_array('unrelated-script', $scripts->done, true) && !in_array('unrelated-style', $styles->done, true),
        'Portal assets must stay unprinted');

    $template = file_get_contents(ABSPATH . 'wp-content/themes/buddyboss-theme-child-1.0.0/home.php');
    $head = strpos($template, "do_action('thepond_landing_head')");
    $footer = strpos($template, "do_action('thepond_landing_footer')");
    check($head !== false && $head < strpos($template, '</head>'),
        'Landing head bridge must render inside the head');
    check($footer !== false && $footer < strpos($template, '</body>'),
        'Landing footer bridge must render inside the body');
    echo "FAZ landing integration checks passed.\n";
}
