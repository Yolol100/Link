<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Handles adding a new keyword-link mapping via admin action.
 *
 * @return void
 */
function handle_add_link(): void
{
    if (
        !current_user_can('manage_options') ||
        !check_admin_referer('ssil_add_link_nonce')
    ) {
        wp_die(__('Geen toegang', 'ssil'));
    }

    $new_word     = isset($_POST['new_word']) ? sanitize_text_field($_POST['new_word']) : '';
    $new_url      = isset($_POST['new_url'])  ? esc_url_raw($_POST['new_url']) : '';
    $nofollow     = !empty($_POST['nofollow']);
    $target_blank = !empty($_POST['target_blank']);

    if ($new_word && $new_url) {
        $links = get_option('ssil_links', []);
        if (!is_array($links)) {
            $links = [];
        }

        if (array_key_exists($new_word, $links)) {
            wp_safe_redirect(admin_url('admin.php?page=ssil_keywords&ssil_error=duplicate'));
            exit;
        }

        $links[$new_word] = [
            'url'         => $new_url,
            'nofollow'    => $nofollow,
            'target_blank'=> $target_blank,
        ];
        update_option('ssil_links', $links);
    }

    wp_safe_redirect(admin_url('admin.php?page=ssil_keywords'));
    exit;
}
add_action('admin_post_ssil_add_link', __NAMESPACE__ . '\\handle_add_link');

/**
 * Handles deletion of a keyword-link mapping via admin action, with logging.
 *
 * @return void
 */
function handle_delete_link(): void
{
    if (!current_user_can('manage_options')) {
        wp_die(__('Geen toegang', 'ssil'));
    }

    $word = isset($_POST['delete_link']) ? sanitize_text_field($_POST['delete_link']) : '';

    if ($word && check_admin_referer('ssil_delete_link_' . $word)) {
        $links = get_option('ssil_links', []);
        if (!is_array($links)) {
            $links = [];
        }

        if (isset($links[$word])) {
            $url        = is_array($links[$word]) ? ($links[$word]['url'] ?? '') : $links[$word];
            $page_id    = null;
            $page_title = '';

            // Directe logging (functie kan elders in namespace zijn)
            if (function_exists(__NAMESPACE__ . '\\ssil_save_log')) {
                call_user_func(__NAMESPACE__ . '\\ssil_save_log', $word, $url, $page_id, $page_title);
            } elseif (function_exists('ssil_save_log')) {
                // fallback voor niet-namespaced legacy
                ssil_save_log($word, $url, $page_id, $page_title);
            }

            unset($links[$word]);
            update_option('ssil_links', $links);
        }
    }

    wp_safe_redirect(admin_url('admin.php?page=ssil_keywords'));
    exit;
}
add_action('admin_post_ssil_delete_link', __NAMESPACE__ . '\\handle_delete_link');