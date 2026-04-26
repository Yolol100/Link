<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function handle_inline_edit(): void
{
    require_manage_options();
    check_admin_referer('ssil_edit_link_nonce');

    $post = wp_unslash($_POST);

    $old_word = isset($post['old_word']) ? sanitize_keyword((string) $post['old_word']) : '';
    $edit_word = isset($post['edit_word']) ? sanitize_keyword((string) $post['edit_word']) : '';
    $edit_url = isset($post['edit_url']) ? sanitize_link_url((string) $post['edit_url']) : '';
    $edit_nofollow = !empty($post['edit_nofollow']);
    $edit_target_blank = !empty($post['edit_target_blank']);
    $priority = isset($post['edit_priority']) ? (int) $post['edit_priority'] : 0;

    if ($old_word === '' || $edit_word === '' || $edit_url === '') {
        admin_redirect('ssil_keywords', ['ssil_error' => 'invalid']);
    }

    $links = get_links();
    if (!isset($links[$old_word])) {
        admin_redirect('ssil_keywords', ['ssil_error' => 'not_found']);
    }

    if ($old_word !== $edit_word && isset($links[$edit_word])) {
        admin_redirect('ssil_keywords', ['ssil_error' => 'duplicate']);
    }

    if ($old_word !== $edit_word) {
        unset($links[$old_word]);
    }

    $links[$edit_word] = [
        'url' => $edit_url,
        'nofollow' => $edit_nofollow,
        'target_blank' => $edit_target_blank,
        'priority' => $priority,
    ];

    update_links($links);
    admin_redirect('ssil_keywords', ['ssil_success' => 'updated']);
}
add_action('admin_post_ssil_edit_link', __NAMESPACE__ . '\\handle_inline_edit');
