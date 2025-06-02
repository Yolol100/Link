<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Verwerkt inline edit van een keyword in de keywords-tabel.
 */
function handle_inline_edit()
{
    // Alleen POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action']) || $_POST['action'] !== 'ssil_edit_link') {
        return;
    }
    // Check nonce
    check_admin_referer('ssil_edit_link_nonce');

    // Haal originele en nieuwe waardes
    $old_word = isset($_POST['old_word']) ? sanitize_text_field((string)$_POST['old_word']) : '';
    $edit_word = isset($_POST['edit_word']) ? sanitize_text_field((string)$_POST['edit_word']) : '';
    $edit_url = isset($_POST['edit_url']) ? esc_url_raw((string)$_POST['edit_url']) : '';
    $edit_nofollow = !empty($_POST['edit_nofollow']) ? 1 : 0;
    $edit_target_blank = !empty($_POST['edit_target_blank']) ? 1 : 0;

    // Validatie
    if ($edit_word === '' || $edit_url === '') {
        add_action('admin_notices', function() {
            echo '<div class="yoast-alert-error"><p>Keyword en URL mogen niet leeg zijn.</p></div>';
        });
        return;
    }

    // Ophalen en updaten van bestaande keywords
    $links = get_option('ssil_links', []);
    if (!isset($links[$old_word])) {
        add_action('admin_notices', function() {
            echo '<div class="yoast-alert-error"><p>Keyword niet gevonden.</p></div>';
        });
        return;
    }

    // Verwijder oude als keyword is gewijzigd
    if ($old_word !== $edit_word && isset($links[$old_word])) {
        unset($links[$old_word]);
    }

    // Update/invul nieuwe waarde
    $links[$edit_word] = [
        'url'          => $edit_url,
        'nofollow'     => $edit_nofollow,
        'target_blank' => $edit_target_blank,
    ];

    update_option('ssil_links', $links);
    update_option('ssil_links_last_modified', current_time('mysql', 1));

    // Successmelding voor toast/alert
    add_action('admin_notices', function() {
        echo '<div class="yoast-alert-success"><p>Koppeling bijgewerkt.</p></div>';
    });

    // Redirect terug naar pagina zonder ?edit
    $url = remove_query_arg(['edit'], $_SERVER['HTTP_REFERER'] ?? admin_url('admin.php?page=ssil_keywords'));
    wp_safe_redirect($url);
    exit;
}

// Haak deze in WordPress admin_init
add_action('admin_init', __NAMESPACE__ . '\\handle_inline_edit');