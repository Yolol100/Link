<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

const TEXT_DOMAIN = 'super-simple-internal-links';
const OPTION_SETTINGS = 'ssil_settings';
const OPTION_LINKS = 'ssil_links';
const DB_VERSION = '2.0.0';

function default_settings(): array
{
    return [
        'enabled' => '1',
        'max_keywords' => 5,
        'exclude_ids' => [],
        'exclude_classes' => [],
        'blacklist_keywords' => [],
        'post_types' => ['post', 'page'],
        'taxonomies' => ['category', 'post_tag', 'product_cat'],
        'exclude_headings' => true,
        'exclude_quotes' => true,
        'exclude_lists' => true,
        'link_titles' => false,
        'link_widgets' => false,
        'link_acf' => false,
        'link_elementor' => false,
        'link_woocommerce' => true,
        'delete_data_on_uninstall' => false,
    ];
}

function get_settings(): array
{
    $settings = get_option(OPTION_SETTINGS, []);
    if (!is_array($settings)) {
        $settings = [];
    }
    return array_merge(default_settings(), sanitize_settings($settings));
}

function sanitize_settings($settings): array
{
    $settings = is_array($settings) ? $settings : [];
    $public_post_types = array_keys(get_post_types(['public' => true], 'names')) ?: ['post', 'page'];
    $public_taxonomies = array_keys(get_taxonomies(['public' => true], 'names')) ?: ['category', 'post_tag'];

    $post_types = array_values(array_intersect(sanitize_key_list($settings['post_types'] ?? ['post', 'page']), $public_post_types));
    if (!$post_types) {
        $post_types = array_values(array_intersect(['post', 'page'], $public_post_types)) ?: ['post'];
    }

    $taxonomies = array_values(array_intersect(sanitize_key_list($settings['taxonomies'] ?? ['category', 'post_tag', 'product_cat']), $public_taxonomies));
    if (!$taxonomies) {
        $taxonomies = array_values(array_intersect(['category', 'post_tag'], $public_taxonomies));
    }

    return [
        'enabled' => !empty($settings['enabled']) ? '1' : '0',
        'max_keywords' => max(1, min(100, absint($settings['max_keywords'] ?? 5))),
        'exclude_ids' => sanitize_int_list($settings['exclude_ids'] ?? []),
        'exclude_classes' => sanitize_class_list($settings['exclude_classes'] ?? []),
        'blacklist_keywords' => sanitize_text_list($settings['blacklist_keywords'] ?? []),
        'post_types' => $post_types,
        'taxonomies' => $taxonomies,
        'exclude_headings' => !empty($settings['exclude_headings']),
        'exclude_quotes' => !empty($settings['exclude_quotes']),
        'exclude_lists' => !empty($settings['exclude_lists']),
        'link_titles' => !empty($settings['link_titles']),
        'link_widgets' => !empty($settings['link_widgets']),
        'link_acf' => !empty($settings['link_acf']),
        'link_elementor' => !empty($settings['link_elementor']),
        'link_woocommerce' => !empty($settings['link_woocommerce']),
        'delete_data_on_uninstall' => !empty($settings['delete_data_on_uninstall']),
    ];
}

function sanitize_links_option($links): array
{
    if (!is_array($links)) {
        return [];
    }

    $clean = [];
    foreach ($links as $keyword => $info) {
        $keyword = sanitize_keyword((string) $keyword);
        if ($keyword === '') {
            continue;
        }

        $url = is_array($info) ? (string) ($info['url'] ?? '') : (string) $info;
        $url = sanitize_link_url($url);
        if ($url === '') {
            continue;
        }

        $clean[$keyword] = [
            'url' => $url,
            'nofollow' => !empty($info['nofollow']),
            'target_blank' => !empty($info['target_blank']),
            'priority' => is_array($info) ? max(-100, min(100, (int) ($info['priority'] ?? 0))) : 0,
        ];
    }

    return $clean;
}

function sanitize_keyword(string $keyword): string
{
    $keyword = wp_strip_all_tags($keyword);
    $keyword = sanitize_text_field($keyword);
    $keyword = trim(preg_replace('/\s+/u', ' ', $keyword) ?: '');
    return mb_substr($keyword, 0, 190);
}

function sanitize_link_url(string $url): string
{
    $url = trim(wp_unslash($url));
    if ($url === '') {
        return '';
    }

    if (str_starts_with($url, '//')) {
        return '';
    }

    if (str_starts_with($url, '/') || str_starts_with($url, '#')) {
        return esc_url_raw($url);
    }

    $clean = esc_url_raw($url, ['http', 'https', 'mailto', 'tel']);
    if ($clean === '') {
        return '';
    }

    if (preg_match('/^https?:\/\//i', $clean) && !wp_http_validate_url($clean)) {
        return '';
    }

    return $clean;
}

function sanitize_int_list($value): array
{
    if (is_string($value)) {
        $value = explode(',', $value);
    }
    if (!is_array($value)) {
        return [];
    }
    $items = array_map('absint', $value);
    return array_values(array_filter(array_unique($items)));
}

function sanitize_class_list($value): array
{
    if (is_string($value)) {
        $value = explode(',', $value);
    }
    if (!is_array($value)) {
        return [];
    }
    $items = [];
    foreach ($value as $class) {
        $class = trim((string) $class);
        $class = ltrim($class, '.');
        $class = sanitize_html_class($class);
        if ($class !== '') {
            $items[] = $class;
        }
    }
    return array_values(array_unique($items));
}

function sanitize_text_list($value): array
{
    if (is_string($value)) {
        $value = explode(',', $value);
    }
    if (!is_array($value)) {
        return [];
    }
    $items = [];
    foreach ($value as $item) {
        $item = sanitize_keyword((string) $item);
        if ($item !== '') {
            $items[] = $item;
        }
    }
    return array_values(array_unique($items));
}

function sanitize_key_list($value): array
{
    if (!is_array($value)) {
        return [];
    }
    return array_values(array_unique(array_filter(array_map('sanitize_key', $value))));
}

function get_links(): array
{
    return sanitize_links_option(get_option(OPTION_LINKS, []));
}

function update_links(array $links): void
{
    update_option(OPTION_LINKS, sanitize_links_option($links), false);
    update_option('ssil_links_last_modified', current_time('mysql', true), false);
}

function admin_redirect(string $page, array $args = []): void
{
    $args = array_merge(['page' => $page], $args);
    wp_safe_redirect(add_query_arg($args, admin_url('admin.php')));
    exit;
}

function require_manage_options(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(esc_html__('Geen toegang.', TEXT_DOMAIN));
    }
}

function csv_safe_cell($value): string
{
    $value = (string) $value;
    if ($value !== '' && preg_match('/^[=+\-@]/', $value)) {
        return "'" . $value;
    }
    return $value;
}
