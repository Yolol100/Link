<?php
if (!defined('ABSPATH')) exit;

// Keyword toevoegen
add_action('admin_post_ssil_add_link', function() {
    if (!current_user_can('manage_options') || !check_admin_referer('ssil_add_link_nonce')) {
        wp_die('Geen toegang');
    }

    $new_word = isset($_POST['new_word']) ? sanitize_text_field($_POST['new_word']) : '';
    $new_url  = isset($_POST['new_url'])  ? esc_url_raw($_POST['new_url']) : '';
    $nofollow = !empty($_POST['nofollow']);
    $target_blank = !empty($_POST['target_blank']);
    $priority = isset($_POST['priority']) ? (int)$_POST['priority'] : 0;

    if ($new_word && $new_url) {
        $links = get_option('ssil_links', []);

        // Zorg dat $links een array is
        if (!is_array($links)) $links = [];

        // Controleer op duplicaat keyword
        if (array_key_exists($new_word, $links)) {
            wp_redirect(admin_url('admin.php?page=ssil_keywords&ssil_error=duplicate'));
            exit;
        } else {
            // Nieuw: sla keyword als array op
            $links[$new_word] = [
                'url' => $new_url,
                'nofollow' => $nofollow,
                'target_blank' => $target_blank,
                'priority' => $priority
            ];
            update_option('ssil_links', $links);
        }
    }

    wp_redirect(admin_url('admin.php?page=ssil_keywords'));
    exit;
});

// Keyword verwijderen
add_action('admin_post_ssil_delete_link', function() {
    if (!current_user_can('manage_options')) {
        wp_die('Geen toegang');
    }
    $word = isset($_POST['delete_link']) ? sanitize_text_field($_POST['delete_link']) : '';
    if ($word && check_admin_referer('ssil_delete_link_' . $word)) {
        $links = get_option('ssil_links', []);
        if (!is_array($links)) $links = [];
        if (isset($links[$word])) {
            unset($links[$word]);
            update_option('ssil_links', $links);
        }
    }
    wp_redirect(admin_url('admin.php?page=ssil_keywords'));
    exit;
});