<?php
/**
 * Plugin Name: Super Simple Internal Links
 * Plugin URI: https://webactueel.nl/
 * Description: Koppel keywords aan URL's en plaats automatisch veilige interne links in WordPress-content.
 * Version: 2.1.0
 * Author: Webactueel
 * Author URI: https://webactueel.nl/
 * Text Domain: super-simple-internal-links
 * Domain Path: /languages
 * Requires at least: 6.0
 * Requires PHP: 8.0
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

define('SSIL_VERSION', '2.1.0');
define('SSIL_PATH', plugin_dir_path(__FILE__));
define('SSIL_URL', plugin_dir_url(__FILE__));
define('SSIL_FILE', __FILE__);

require_once SSIL_PATH . 'includes/helpers.php';
require_once SSIL_PATH . 'includes/activation.php';
require_once SSIL_PATH . 'includes/instellingen-plugin.php';
require_once SSIL_PATH . 'includes/ssil-linker.php';
require_once SSIL_PATH . 'includes/ssil-logs.php';
require_once SSIL_PATH . 'includes/ssil-admin-actions.php';
require_once SSIL_PATH . 'includes/ssil-suggest.php';
require_once SSIL_PATH . 'includes/ssil-import.php';
require_once SSIL_PATH . 'includes/ssil-export.php';
require_once SSIL_PATH . 'includes/ssil-keywords-edit.php';
require_once SSIL_PATH . 'includes/ssil-report-logic.php';

require_once SSIL_PATH . 'admin/ssil-keywords-page.php';
require_once SSIL_PATH . 'admin/ssil-report-view.php';
require_once SSIL_PATH . 'admin/ssil-report-page.php';
require_once SSIL_PATH . 'admin/ssil-bulk-page.php';

register_activation_hook(__FILE__, __NAMESPACE__ . '\plugin_activation');

add_action('plugins_loaded', static function (): void {
    load_plugin_textdomain(TEXT_DOMAIN, false, dirname(plugin_basename(SSIL_FILE)) . '/languages');
});

add_action('admin_menu', static function (): void {
    add_menu_page(
        __('Interne links', TEXT_DOMAIN),
        __('Interne links', TEXT_DOMAIN),
        'manage_options',
        'ssil_main',
        __NAMESPACE__ . '\render_settings_page',
        'dashicons-admin-links',
        50
    );

    add_submenu_page('ssil_main', __('Instellingen', TEXT_DOMAIN), __('Instellingen', TEXT_DOMAIN), 'manage_options', 'ssil_main', __NAMESPACE__ . '\render_settings_page');
    add_submenu_page('ssil_main', __('Keywords', TEXT_DOMAIN), __('Keywords', TEXT_DOMAIN), 'manage_options', 'ssil_keywords', __NAMESPACE__ . '\keywords_page');
    add_submenu_page('ssil_main', __('Logs', TEXT_DOMAIN), __('Logs', TEXT_DOMAIN), 'manage_options', 'ssil_logs', __NAMESPACE__ . '\render_logs_page');
    add_submenu_page('ssil_main', __('Rapportage', TEXT_DOMAIN), __('Rapportage', TEXT_DOMAIN), 'manage_options', 'ssil_report', __NAMESPACE__ . '\render_report_page');
    add_submenu_page('ssil_main', __('Bulk import/export', TEXT_DOMAIN), __('Bulk import/export', TEXT_DOMAIN), 'manage_options', 'ssil_bulk', __NAMESPACE__ . '\bulk_page');
});

add_action('admin_enqueue_scripts', static function ($hook): void {
    $page = isset($_GET['page']) ? sanitize_key((string) wp_unslash($_GET['page'])) : '';
    if (!in_array($page, ['ssil_main', 'ssil_keywords', 'ssil_logs', 'ssil_report', 'ssil_bulk'], true)) {
        return;
    }

    $css = SSIL_PATH . 'assets/css/style.css';
    $js = SSIL_PATH . 'assets/js/admin.js';
    wp_enqueue_style('ssil-admin-style', SSIL_URL . 'assets/css/style.css', [], file_exists($css) ? (string) filemtime($css) : SSIL_VERSION);
    wp_enqueue_script('ssil-admin-js', SSIL_URL . 'assets/js/admin.js', ['jquery'], file_exists($js) ? (string) filemtime($js) : SSIL_VERSION, true);
    wp_localize_script('ssil-admin-js', 'ssil_ajax', [
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('ssil_admin_ajax'),
    ]);
});

add_action('init', __NAMESPACE__ . '\register_content_filters');

function register_content_filters(): void
{
    $settings = get_settings();

    add_filter('the_content', __NAMESPACE__ . '\auto_link_content_filter', 12);
    add_filter('term_description', __NAMESPACE__ . '\auto_link_content_filter', 12);
    add_filter('render_block', __NAMESPACE__ . '\auto_link_block_filter', 12, 2);

    if (!empty($settings['link_titles'])) {
        add_filter('the_title', __NAMESPACE__ . '\auto_link_content_filter', 12);
    }

    if (!empty($settings['link_widgets'])) {
        add_filter('widget_text', __NAMESPACE__ . '\auto_link_content_filter', 12);
    }

    if (!empty($settings['link_woocommerce']) && class_exists('WooCommerce')) {
        add_filter('woocommerce_short_description', __NAMESPACE__ . '\auto_link_content_filter', 12);
        add_filter('woocommerce_product_description', __NAMESPACE__ . '\auto_link_content_filter', 12);
    }

    if (!empty($settings['link_acf']) && function_exists('acf')) {
        add_filter('acf/format_value/type=text', __NAMESPACE__ . '\auto_link_content_filter', 12, 3);
        add_filter('acf/format_value/type=textarea', __NAMESPACE__ . '\auto_link_content_filter', 12, 3);
        add_filter('acf/format_value/type=wysiwyg', __NAMESPACE__ . '\auto_link_content_filter', 12, 3);
    }

    if (!empty($settings['link_elementor']) && did_action('elementor/loaded')) {
        add_filter('elementor/widget/render_content', __NAMESPACE__ . '\auto_link_content_filter', 12);
    }
}
