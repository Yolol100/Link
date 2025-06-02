<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Voeg een log toe aan de logs-tabel.
 *
 * @param string      $keyword
 * @param string      $url
 * @param int|null    $page_id
 * @param string      $page_title
 * @return void
 */
function add_log(string $keyword, string $url, ?int $page_id = null, string $page_title = ''): void
{
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_logs';

    $wpdb->insert($table, [
        'keyword'    => $keyword,
        'url'        => $url,
        'page_id'    => $page_id,
        'page_title' => $page_title,
        'deleted_at' => current_time('mysql')
    ]);
}

/**
 * Sla een logregel op in de logs-tabel (alias/future-proof).
 *
 * @param string   $keyword
 * @param string   $url
 * @param int|null $page_id
 * @param string   $page_title
 * @return void
 */
function save_log(string $keyword, string $url, ?int $page_id, string $page_title): void
{
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_logs';

    $wpdb->insert($table, [
        'keyword'    => $keyword,
        'url'        => $url,
        'page_id'    => $page_id,
        'page_title' => $page_title,
        'deleted_at' => current_time('mysql')
    ]);
}

/**
 * Haal logs op (standaard laatste 50).
 *
 * @param int $limit
 * @return array<int, object>
 */
function get_logs(int $limit = 50): array
{
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_logs';
    $limit = max(1, $limit); // Fallback
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table ORDER BY deleted_at DESC LIMIT %d", $limit
        )
    );
    return is_array($results) ? $results : [];
}

/**
 * Render de logs-pagina in het admin dashboard.
 *
 * @return void
 */
function render_logs_page(): void
{
    include SSIL_PATH . 'admin/logs-table.php';
}