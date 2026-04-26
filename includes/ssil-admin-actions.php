<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function handle_add_link(): void
{
    require_manage_options();
    check_admin_referer('ssil_add_link_nonce');

    $post = wp_unslash($_POST);
    $new_word = isset($post['new_word']) ? sanitize_keyword((string) $post['new_word']) : '';
    $new_url = isset($post['new_url']) ? sanitize_link_url((string) $post['new_url']) : '';
    $nofollow = !empty($post['nofollow']);
    $target_blank = !empty($post['target_blank']);
    $priority = isset($post['priority']) ? (int) $post['priority'] : 0;

    if ($new_word === '' || $new_url === '') {
        admin_redirect('ssil_keywords', ['ssil_error' => 'invalid']);
    }

    $links = get_links();
    if (array_key_exists($new_word, $links)) {
        admin_redirect('ssil_keywords', ['ssil_error' => 'duplicate']);
    }

    $links[$new_word] = [
        'url' => $new_url,
        'nofollow' => $nofollow,
        'target_blank' => $target_blank,
        'priority' => $priority,
    ];
    update_links($links);
    admin_redirect('ssil_keywords', ['ssil_success' => 'added']);
}
add_action('admin_post_ssil_add_link', __NAMESPACE__ . '\handle_add_link');

function handle_delete_link(): void
{
    require_manage_options();

    $post = wp_unslash($_POST);
    $word = isset($post['delete_link']) ? sanitize_keyword((string) $post['delete_link']) : '';
    if ($word === '') {
        admin_redirect('ssil_keywords', ['ssil_error' => 'invalid']);
    }

    check_admin_referer('ssil_delete_link_' . $word);

    $links = get_links();
    if (isset($links[$word])) {
        $url = (string) ($links[$word]['url'] ?? '');
        if (function_exists(__NAMESPACE__ . '\save_log')) {
            save_log($word, $url, null, '');
        }
        unset($links[$word]);
        update_links($links);
    }

    admin_redirect('ssil_keywords', ['ssil_success' => 'deleted']);
}
add_action('admin_post_ssil_delete_link', __NAMESPACE__ . '\handle_delete_link');
