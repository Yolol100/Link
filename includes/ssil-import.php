<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function handle_import(array $file): void
{
    require_manage_options();

    if (empty($file['tmp_name']) || !isset($file['error']) || (int) $file['error'] !== UPLOAD_ERR_OK) {
        admin_redirect('ssil_bulk', ['ssil_bulk_error' => 'upload']);
    }

    $size = isset($file['size']) ? (int) $file['size'] : 0;
    if ($size <= 0 || $size > 2 * MB_IN_BYTES) {
        admin_redirect('ssil_bulk', ['ssil_bulk_error' => 'size']);
    }

    $name = isset($file['name']) ? sanitize_file_name((string) $file['name']) : '';
    if (strtolower(pathinfo($name, PATHINFO_EXTENSION)) !== 'csv') {
        admin_redirect('ssil_bulk', ['ssil_bulk_error' => 'type']);
    }

    $handle = fopen((string) $file['tmp_name'], 'r');
    if (!$handle) {
        admin_redirect('ssil_bulk', ['ssil_bulk_error' => 'read']);
    }

    $links = get_links();
    $row_number = 0;
    $imported = 0;

    while (($row = fgetcsv($handle)) !== false) {
        $row_number++;
        if ($row_number === 1 && isset($row[0]) && strtolower(trim((string) $row[0])) === 'keyword') {
            continue;
        }
        if (count($row) < 2) {
            continue;
        }

        $keyword = sanitize_keyword((string) ($row[0] ?? ''));
        $url = sanitize_link_url((string) ($row[1] ?? ''));
        if ($keyword === '' || $url === '') {
            continue;
        }

        $links[$keyword] = [
            'url' => $url,
            'nofollow' => !empty($row[2]) && in_array(strtolower((string) $row[2]), ['1', 'true', 'yes', 'ja'], true),
            'target_blank' => !empty($row[3]) && in_array(strtolower((string) $row[3]), ['1', 'true', 'yes', 'ja'], true),
            'priority' => isset($row[4]) ? (int) $row[4] : 0,
        ];
        $imported++;
    }

    fclose($handle);
    update_links($links);
    admin_redirect('ssil_bulk', ['ssil_bulk_success' => (string) $imported]);
}
