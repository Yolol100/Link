<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

// Logica/data ophalen
require_once SSIL_PATH . 'includes/ssil-report-logic.php';
// View generator
require_once SSIL_PATH . 'admin/ssil-report-view.php';

/**
 * Render het rapportage-overzicht voor SSIL.
 *
 * @return void
 */
function render_report_page(): void
{
    $stats = function_exists(__NAMESPACE__ . '\\get_report_stats') ? get_report_stats() : (function_exists('ssil_get_report_stats') ? ssil_get_report_stats() : []);
    if (function_exists(__NAMESPACE__ . '\\render_report_view')) {
        render_report_view($stats);
    } elseif (function_exists('ssil_render_report_view')) {
        ssil_render_report_view($stats);
    }

    // JS alleen op deze adminpagina laden
    add_action('admin_footer', static function () {
        ?>
        <script src="<?php echo esc_url(SSIL_URL . 'assets/js/ssil-report.js'); ?>"></script>
        <?php
    });
}