<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function register_settings(): void
{
    register_setting('ssil_settings_group', OPTION_SETTINGS, [
        'type' => 'array',
        'show_in_rest' => false,
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_settings',
    ]);

    register_setting('ssil_settings_group', OPTION_LINKS, [
        'type' => 'array',
        'show_in_rest' => false,
        'sanitize_callback' => __NAMESPACE__ . '\sanitize_links_option',
    ]);
}
add_action('admin_init', __NAMESPACE__ . '\register_settings');

function handle_settings_save(): array
{
    $settings = get_settings();

    if (!isset($_POST['ssil_save_settings'])) {
        return $settings;
    }

    require_manage_options();
    check_admin_referer('ssil_settings_save', 'ssil_settings_nonce');

    $post = wp_unslash($_POST);
    $new_settings = [
        'enabled' => isset($post['ssil_enabled']) ? '1' : '0',
        'max_keywords' => $post['ssil_max_keywords'] ?? 5,
        'exclude_ids' => $post['ssil_exclude_ids'] ?? [],
        'exclude_classes' => $post['ssil_exclude_classes'] ?? [],
        'blacklist_keywords' => $post['ssil_blacklist_keywords'] ?? [],
        'post_types' => isset($post['ssil_post_types']) && is_array($post['ssil_post_types']) ? $post['ssil_post_types'] : ['post', 'page'],
        'taxonomies' => isset($post['ssil_taxonomies']) && is_array($post['ssil_taxonomies']) ? $post['ssil_taxonomies'] : ['category', 'post_tag'],
        'exclude_headings' => isset($post['ssil_exclude_headings']),
        'exclude_quotes' => isset($post['ssil_exclude_quotes']),
        'exclude_lists' => isset($post['ssil_exclude_lists']),
        'link_titles' => isset($post['ssil_link_titles']),
        'link_widgets' => isset($post['ssil_link_widgets']),
        'link_acf' => isset($post['ssil_link_acf']),
        'link_elementor' => isset($post['ssil_link_elementor']),
        'link_woocommerce' => isset($post['ssil_link_woocommerce']),
        'delete_data_on_uninstall' => isset($post['ssil_delete_data_on_uninstall']),
    ];

    $settings = array_merge(default_settings(), sanitize_settings($new_settings));
    update_option(OPTION_SETTINGS, $settings, false);

    echo '<div class="notice notice-success"><p>' . esc_html__('Instellingen opgeslagen.', TEXT_DOMAIN) . '</p></div>';
    return $settings;
}

function render_settings_page(): void
{
    require_manage_options();
    $settings = handle_settings_save();
    include SSIL_PATH . 'admin/frontend-instellingen.php';
}
