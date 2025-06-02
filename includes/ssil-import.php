<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Importeert keyword-URL mappings uit een CSV-bestand.
 *
 * @param string $filename Pad naar het CSV-bestand.
 * @return void
 */
function handle_import(string $filename): void
{
    // Controleer of het bestand bestaat en leesbaar is
    if (!file_exists($filename) || !is_readable($filename)) {
        wp_safe_redirect(admin_url('admin.php?page=ssil_bulk&ssil_bulk_error=1'));
        exit;
    }

    $rows = array_map('str_getcsv', file($filename) ?: []);

    // Haal huidige links uit de database
    $links = get_option('ssil_links', []);
    if (!is_array($links)) {
        $links = [];
    }

    // Sla de header (eerste rij) over
    $header = array_shift($rows);

    foreach ($rows as $row) {
        // Zorg voor minimaal 5 kolommen
        if (count($row) < 5) {
            continue;
        }

        $word        = isset($row[0]) ? trim($row[0]) : '';
        $url         = isset($row[1]) ? trim($row[1]) : '';
        $nofollow    = !empty($row[2]) && strtolower((string)$row[2]) === '1';
        $target_blank= !empty($row[3]) && strtolower((string)$row[3]) === '1';
        $priority    = isset($row[4]) ? (int)$row[4] : 0;

        if ($word && $url) {
            $url = esc_url_raw($url);
            $links[$word] = [
                'url'         => $url,
                'nofollow'    => $nofollow,
                'target_blank'=> $target_blank,
                'priority'    => $priority,
            ];
        }
    }

    update_option('ssil_links', $links);

    wp_safe_redirect(admin_url('admin.php?page=ssil_bulk&ssil_bulk_success=1'));
    exit;
}