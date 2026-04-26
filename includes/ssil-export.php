<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function maybe_handle_export(): void
{
    if (!is_admin() || !isset($_POST['ssil_export'])) {
        return;
    }
    require_manage_options();
    check_admin_referer('ssil_bulk_export', 'ssil_bulk_export_nonce');
    handle_export();
}
add_action('admin_init', __NAMESPACE__ . '\maybe_handle_export');

function handle_export(): void
{
    require_manage_options();

    if (headers_sent()) {
        wp_die(esc_html__('CSV-export kan niet starten omdat er al output is verzonden.', TEXT_DOMAIN));
    }

    nocache_headers();
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="keywords-export.csv"');

    $output = fopen('php://output', 'w');
    if (!$output) {
        wp_die(esc_html__('Kan exportbestand niet openen.', TEXT_DOMAIN));
    }

    fputcsv($output, ['keyword', 'url', 'nofollow', 'target_blank', 'priority']);
    foreach (get_links() as $keyword => $info) {
        fputcsv($output, [
            csv_safe_cell($keyword),
            csv_safe_cell((string) ($info['url'] ?? '')),
            !empty($info['nofollow']) ? '1' : '0',
            !empty($info['target_blank']) ? '1' : '0',
            (string) (int) ($info['priority'] ?? 0),
        ]);
    }

    fclose($output);
    exit;
}
