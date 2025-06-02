<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Handles the export trigger when the form is submitted in admin.
 *
 * @return void
 */
function maybe_handle_export(): void
{
    // Only handle POST requests in admin
    if (
        is_admin() &&
        isset($_POST['ssil_export']) &&
        check_admin_referer('ssil_bulk_export', 'ssil_bulk_export_nonce')
    ) {
        handle_export();
    }
}
add_action('init', __NAMESPACE__ . '\\maybe_handle_export');

/**
 * Outputs CSV for all keyword-to-URL mappings and forces download.
 *
 * @return void
 */
function handle_export(): void
{
    $links = get_option('ssil_links', []);

    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="keywords-export.csv"');
    header('Pragma: no-cache');
    header('Expires: 0');

    $output = fopen('php://output', 'w');
    if (!$output) {
        wp_die(__('Kan exportbestand niet openen.', 'ssil'));
    }

    // CSV header
    fputcsv($output, ['keyword', 'url', 'nofollow', 'target_blank', 'priority']);

    foreach ($links as $keyword => $info) {
        $url = is_array($info) ? ($info['url'] ?? '') : $info;
        // Indien URL relatief is, omzetten naar volledig
        if (str_starts_with($url, '/')) {
            $url = get_site_url() . $url;
        }
        $nofollow     = is_array($info) && !empty($info['nofollow']) ? '1' : '0';
        $target_blank = is_array($info) && !empty($info['target_blank']) ? '1' : '0';
        $priority     = is_array($info) ? (int)($info['priority'] ?? 0) : 0;

        fputcsv($output, [$keyword, $url, $nofollow, $target_blank, $priority]);
    }

    fclose($output);
    exit;
}