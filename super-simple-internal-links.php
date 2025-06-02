<?php
/*
Plugin Name: Super Simple Internal Links
Description: Simpel keywords aan URLs koppelen en automatisch linken in content.
Version: 1.3
Author: Jouw Naam
Text Domain: super-simple-internal-links
*/

declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

// Constants
define('SSIL_PATH', plugin_dir_path(__FILE__));
define('SSIL_URL', plugin_dir_url(__FILE__));

// Vertalingen (optioneel)
load_plugin_textdomain('super-simple-internal-links', false, dirname(plugin_basename(__FILE__)) . '/languages');

// Activatie
require_once SSIL_PATH . 'includes/activation.php';
register_activation_hook(__FILE__, __NAMESPACE__ . '\\create_logs_table');
register_activation_hook(__FILE__, __NAMESPACE__ . '\\create_plugin_data_table');

// Includes logica, helpers, admin
require_once SSIL_PATH . 'includes/instellingen-plugin.php';
require_once SSIL_PATH . 'includes/ssil-linker.php';
require_once SSIL_PATH . 'includes/ssil-admin-actions.php';
require_once SSIL_PATH . 'includes/ssil-logs.php';
require_once SSIL_PATH . 'includes/ssil-ajax.php';
require_once SSIL_PATH . 'includes/ssil-suggest.php';
require_once SSIL_PATH . 'includes/ssil-import.php';
require_once SSIL_PATH . 'includes/ssil-export.php';
require_once SSIL_PATH . 'includes/ssil-keywords-edit.php';

// Rapportage logica en view
require_once SSIL_PATH . 'includes/ssil-report-logic.php';
require_once SSIL_PATH . 'admin/ssil-report-view.php';

// Admin UI
require_once SSIL_PATH . 'admin/ssil-keywords-page.php';
require_once SSIL_PATH . 'admin/ssil-report-page.php';
require_once SSIL_PATH . 'admin/ssil-bulk-page.php';

// Admin menu structuur
add_action('admin_menu', function() {
    add_menu_page(
        'Interne Links',
        'Interne Links',
        'manage_options',
        'ssil_main',
        __NAMESPACE__ . '\\render_settings_page',  // Hier wordt het instellingenformulier getoond
        'dashicons-admin-links',
        50
    );
    add_submenu_page('ssil_main', 'Instellingen', 'Instellingen', 'manage_options', 'ssil_main', __NAMESPACE__ . '\\render_settings_page');
    add_submenu_page('ssil_main', 'Keywords', 'Keywords', 'manage_options', 'ssil_keywords', __NAMESPACE__ . '\\keywords_page');
    add_submenu_page('ssil_main', 'Logs', 'Logs', 'manage_options', 'ssil_logs', __NAMESPACE__ . '\\render_logs_page');
    add_submenu_page('ssil_main', 'Rapportage', 'Rapportage', 'manage_options', 'ssil_report', __NAMESPACE__ . '\\render_report_page');
    add_submenu_page('ssil_main', 'Bulk Import/Export', 'Bulk Import/Export', 'manage_options', 'ssil_bulk', __NAMESPACE__ . '\\bulk_page');
});

// Styles/scripts alleen op pluginpagina’s
add_action('admin_enqueue_scripts', function($hook) {
    if (isset($_GET['page']) && in_array($_GET['page'], [
        'ssil_main', 'ssil_keywords', 'ssil_logs', 'ssil_report', 'ssil_bulk'
    ], true)) {
        wp_enqueue_style('ssil-admin-style', SSIL_URL . 'assets/css/style.css');
        wp_enqueue_script('ssil-admin-js', SSIL_URL . 'assets/js/admin.js', ['jquery'], false, true);
        wp_localize_script('ssil-admin-js', 'ssil_ajax', ['ajaxurl' => admin_url('admin-ajax.php')]);
    }
});

// Filters voor de verschillende contenttypes

// WordPress content
add_filter('the_content', __NAMESPACE__ . '\\auto_link_content_filter', 12);
add_filter('the_title', __NAMESPACE__ . '\\auto_link_content_filter', 12);
add_filter('widget_text', __NAMESPACE__ . '\\auto_link_content_filter', 12);
add_filter('widget_title', __NAMESPACE__ . '\\auto_link_content_filter', 12);

// WooCommerce
add_filter('woocommerce_short_description', __NAMESPACE__ . '\\auto_link_content_filter', 12);
add_filter('woocommerce_product_description', __NAMESPACE__ . '\\auto_link_content_filter', 12);

// Taxonomie-beschrijvingen
add_filter('term_description', function($desc, $term_id, $taxonomy) {
    return auto_link_content_filter($desc);
}, 12, 3);

// ACF & custom fields
add_filter('acf/format_value/type=text', __NAMESPACE__ . '\\auto_link_content_filter', 12, 3);
add_filter('acf/format_value/type=textarea', __NAMESPACE__ . '\\auto_link_content_filter', 12, 3);
add_filter('acf/format_value/type=wysiwyg', __NAMESPACE__ . '\\auto_link_content_filter', 12, 3);

// Elementor
add_filter('elementor/widget/render_content', function($content) {
    if (is_admin() || empty($content)) return $content;
    return auto_link_content_filter($content);
}, 12);

// Gutenberg block output (excl. code/pre/html blocks)
add_filter('render_block', function($block_content, $block) {
    if (isset($block['blockName']) && in_array($block['blockName'], [
        'core/code', 'core/preformatted', 'core/html'
    ], true)) {
        return $block_content;
    }
    return auto_link_content_filter($block_content);
}, 12, 2);