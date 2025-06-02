<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Register plugin settings.
 */
function register_settings(): void
{
    register_setting('ssil_settings_group', 'ssil_settings', [
        'type' => 'array',
        'show_in_rest' => false,
        'sanitize_callback' => null,
    ]);

    register_setting('ssil_settings_group', 'ssil_links', [
        'type' => 'array',
        'show_in_rest' => false,
        'sanitize_callback' => null,
    ]);

    add_settings_section(
        'ssil_exclusions_section',
        'Uitsluitingen voor automatische linking',
        null,
        'ssil_settings_group'
    );

    register_setting('ssil_settings_group', 'ssil_settings_exclude_headings');
    register_setting('ssil_settings_group', 'ssil_settings_exclude_quotes');
    register_setting('ssil_settings_group', 'ssil_settings_exclude_lists');
}

add_action('admin_init', __NAMESPACE__ . '\\register_settings');

/**
 * Verwerk het opslaan van de instellingen.
 *
 * @return array Geüpdatete settings array.
 */
function handle_settings_save(): array
{
    $defaults = [
        'enabled'            => '1',
        'max_keywords'       => 5,
        'exclude_ids'        => [],
        'exclude_classes'    => [],
        'blacklist_keywords' => [],
        'post_types'         => ['post', 'page'],
        'taxonomies'         => ['category', 'post_tag', 'product_cat'],
    ];

    $settings = get_option('ssil_settings', []);
    $settings = array_merge($defaults, is_array($settings) ? $settings : []);

    if (isset($_POST['ssil_save_settings'])) {
        check_admin_referer('ssil_settings_save', 'ssil_settings_nonce');

        $new_settings = [];
        $new_settings['enabled']      = (isset($_POST['ssil_enabled']) && $_POST['ssil_enabled'] === '1') ? '1' : '0';
        $new_settings['max_keywords'] = isset($_POST['ssil_max_keywords']) ? max(1, intval($_POST['ssil_max_keywords'])) : 5;

        $new_settings['exclude_ids'] = !empty($_POST['ssil_exclude_ids'])
            ? array_filter(array_map('trim', explode(',', (string)$_POST['ssil_exclude_ids'])))
            : [];

        $new_settings['exclude_classes'] = !empty($_POST['ssil_exclude_classes'])
            ? array_filter(array_map('trim', explode(',', (string)$_POST['ssil_exclude_classes'])))
            : [];

        $new_settings['blacklist_keywords'] = !empty($_POST['ssil_blacklist_keywords'])
            ? array_filter(array_map('trim', explode(',', (string)$_POST['ssil_blacklist_keywords'])))
            : [];

        $new_settings['post_types'] = (isset($_POST['ssil_post_types']) && is_array($_POST['ssil_post_types']))
            ? array_map('sanitize_text_field', $_POST['ssil_post_types'])
            : ['post', 'page'];

        $new_settings['taxonomies'] = (isset($_POST['ssil_taxonomies']) && is_array($_POST['ssil_taxonomies']))
            ? array_map('sanitize_text_field', $_POST['ssil_taxonomies'])
            : ['category', 'post_tag', 'product_cat'];

        update_option('ssil_settings', $new_settings);
        $settings = array_merge($defaults, $new_settings);

        echo '<div class="yoast-alert-success yoast-mb-4"><p>Instellingen opgeslagen</p></div>';
    }

    return $settings;
}

/**
 * Render de instellingenpagina.
 */
function render_settings_page(): void
{
    $settings = handle_settings_save();
    extract(['settings' => $settings]);
    include SSIL_PATH . 'admin/frontend-instellingen.php';
}