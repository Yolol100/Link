<?php
if (!defined('ABSPATH')) exit;

require_once SSIL_PATH . 'includes/ssil-import.php';
require_once SSIL_PATH . 'includes/ssil-export.php';

function ssil_bulk_page() {
    // Show feedback messages
    if (isset($_GET['ssil_bulk_success'])) {
        echo '<div class="notice notice-success is-dismissible"><p>Bulk actie succesvol uitgevoerd.</p></div>';
    } elseif (isset($_GET['ssil_bulk_error'])) {
        echo '<div class="notice notice-error is-dismissible"><p>Bulk actie mislukt, controleer je bestand.</p></div>';
    }

    // === Import verwerking ===
    if (isset($_POST['ssil_import']) && isset($_FILES['csv_file']['tmp_name']) && check_admin_referer('ssil_bulk_import', 'ssil_bulk_import_nonce')) {
        ssil_handle_import($_FILES['csv_file']['tmp_name']);
    }

    // === Export verwerking ===
    // Check if the export button has been clicked
    if (isset($_POST['ssil_export']) && check_admin_referer('ssil_bulk_export', 'ssil_bulk_export_nonce')) {
        ssil_handle_export();  // Execute export function if the button is clicked
    }

    ?>
    <div class="ssil-wrap">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>

        <div class="ssil-bulk-row">
            <!-- Import Form -->
            <form method="post" enctype="multipart/form-data" class="ssil-form ssil-bulk-form" style="margin-bottom:0;">
                <input type="file" name="csv_file" accept=".csv" required class="ssil-input ssil-input-csv">
                <button type="submit" name="ssil_import" class="ssil-btn ssil-btn-primary">Importeren</button>
                <?php wp_nonce_field('ssil_bulk_import', 'ssil_bulk_import_nonce'); ?>
            </form>

            <!-- Export Form -->
            <form method="post" class="ssil-form ssil-bulk-form" style="margin-bottom:0;">
                <button type="submit" name="ssil_export" class="ssil-btn ssil-btn-primary">Exporteren</button>
                <?php wp_nonce_field('ssil_bulk_export', 'ssil_bulk_export_nonce'); ?>
            </form>
        </div>
    </div>
    <?php
}
?>