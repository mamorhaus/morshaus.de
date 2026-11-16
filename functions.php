<?php
if (!defined('ABSPATH')) exit;

define('MORSHAUS_PILOT_VERSION', '0.1.1');

function morshaus_pilot_setup() {
    load_theme_textdomain('morshaus-pilot', get_template_directory() . '/languages');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo', array('height'=>180,'width'=>520,'flex-height'=>true,'flex-width'=>true));
    add_theme_support('html5', array('search-form','comment-form','comment-list','gallery','caption','style','script'));
    add_theme_support('responsive-embeds');
    add_theme_support('align-wide');
    register_nav_menus(array('primary'=>'Hauptnavigation','footer'=>'Footernavigation'));
}
add_action('after_setup_theme', 'morshaus_pilot_setup');

function morshaus_pilot_assets() {
    $dir = get_template_directory();
    $uri = get_template_directory_uri();
    foreach (array('tokens','base','components','front-page') as $name) {
        $path = $dir . '/assets/css/' . $name . '.css';
        wp_enqueue_style('morshaus-' . $name, $uri . '/assets/css/' . $name . '.css', array(), file_exists($path) ? filemtime($path) : MORSHAUS_PILOT_VERSION);
    }
    $script = $dir . '/assets/js/navigation.js';
    wp_enqueue_script('morshaus-navigation', $uri . '/assets/js/navigation.js', array(), file_exists($script) ? filemtime($script) : MORSHAUS_PILOT_VERSION, true);
}
add_action('wp_enqueue_scripts', 'morshaus_pilot_assets');

/** Liest für den Pilot bewusst die bisherigen Twenty-Seventeen-Einstellungen. */
function morshaus_pilot_legacy_mods() {
    static $mods = null;
    if (is_array($mods)) return $mods;

    global $wpdb;
    $mods = array();
    $rows = $wpdb->get_results(
        "SELECT option_name, option_value FROM {$wpdb->options} WHERE option_name LIKE 'theme_mods_%'",
        ARRAY_A
    );

    /* Child-Theme-Werte sollen Parent-Werte überschreiben. */
    usort($rows, function ($a, $b) {
        $a_child = strpos($a['option_name'], 'twentyseventeen-child') !== false ? 1 : 0;
        $b_child = strpos($b['option_name'], 'twentyseventeen-child') !== false ? 1 : 0;
        return $a_child <=> $b_child;
    });

    foreach ($rows as $row) {
        $value = maybe_unserialize($row['option_value']);
        if (!is_array($value)) continue;
        $relevant = strpos($row['option_name'], 'twentyseventeen') !== false;
        if (!$relevant) {
            foreach (array('custom_logo','header_image','panel_1','panel_2','panel_3') as $key) {
                if (array_key_exists($key, $value)) { $relevant = true; break; }
            }
        }
        if ($relevant) $mods = array_replace($mods, $value);
    }

    return $mods;
}
function morshaus_pilot_legacy_mod($key, $default = null) {
    $mods = morshaus_pilot_legacy_mods();
    return array_key_exists($key, $mods) ? $mods[$key] : $default;
}
function morshaus_pilot_logo_url() {
    $id = absint(morshaus_pilot_legacy_mod('custom_logo', 0));
    return $id ? wp_get_attachment_image_url($id, 'full') : '';
}
function morshaus_pilot_header_url() {
    $value = morshaus_pilot_legacy_mod('header_image', '');
    if (is_numeric($value)) return wp_get_attachment_image_url(absint($value), 'full');
    if (is_string($value) && $value !== 'remove-header' && $value !== 'random-default-image') return $value;
    return '';
}
function morshaus_pilot_panel_ids() {
    $ids = array();
    for ($i=1; $i<=7; $i++) {
        $id = absint(morshaus_pilot_legacy_mod('panel_' . $i, 0));
        if ($id && get_post_status($id) === 'publish') $ids[] = $id;
    }
    return array_values(array_unique($ids));
}
function morshaus_pilot_menu_fallback() {
    echo '<ul class="mh-menu">';
    wp_list_pages(array('title_li'=>'','depth'=>1));
    echo '</ul>';
}
function morshaus_pilot_body_classes($classes) {
    $classes[] = 'mh-theme';
    if (is_front_page()) $classes[] = 'mh-front-page';
    return $classes;
}
add_filter('body_class', 'morshaus_pilot_body_classes');
