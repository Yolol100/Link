<?php
if (!defined('ABSPATH')) exit;

// Logica/data ophalen
require_once SSIL_PATH . 'includes/ssil-report-logic.php';
// View generator
require_once SSIL_PATH . 'admin/ssil-report-view.php';

// Controller
function ssil_render_report_page() {
    $stats = ssil_get_report_stats();
    ssil_render_report_view($stats);
    // JS registreren (alleen op deze admin-pagina)
    add_action('admin_footer', function() {
        ?>
        <script src="<?php echo esc_url(SSIL_URL . 'assets/js/ssil-report.js'); ?>"></script>
        <?php
    });
}