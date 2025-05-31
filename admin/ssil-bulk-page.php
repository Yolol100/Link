<?php
if (!defined('ABSPATH')) exit;

require_once SSIL_PATH . 'includes/ssil-import.php';
require_once SSIL_PATH . 'includes/ssil-export.php';

function ssil_bulk_page() {
    // === Feedback alerts (optioneel, deze kun je vervangen door JS toasts als je wilt) ===
    if (isset($_GET['ssil_bulk_success'])) {
        echo '<div class="yoast-alert-success yoast-mb-4"><p>Bulk actie succesvol uitgevoerd.</p></div>';
    } elseif (isset($_GET['ssil_bulk_error'])) {
        echo '<div class="yoast-alert-error yoast-mb-4"><p>Bulk actie mislukt, controleer je bestand.</p></div>';
    }

    // === Import verwerking ===
    if (isset($_POST['ssil_import']) && isset($_FILES['csv_file']['tmp_name']) && check_admin_referer('ssil_bulk_import', 'ssil_bulk_import_nonce')) {
        ssil_handle_import($_FILES['csv_file']['tmp_name']);
    }

    // === Export verwerking ===
    if (isset($_POST['ssil_export']) && check_admin_referer('ssil_bulk_export', 'ssil_bulk_export_nonce')) {
        ssil_handle_export();
    }
    ?>
    <div class="yoast-card yoast-p-4">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>

        <div class="yoast-flex yoast-gap-4 yoast-bulk-row">
            <!-- Import Form -->
            <form method="post" enctype="multipart/form-data" class="yoast-form yoast-bulk-form yoast-mb-0" id="ssil-bulk-import-form" autocomplete="off">
                <input type="file" name="csv_file" accept=".csv" required class="yoast-input yoast-input-csv">
                <button type="submit" name="ssil_import" class="yoast-button-primary yoast-mr-2 ssil-import-btn">
                    <span class="ssil-btn-text">Importeren</span>
                    <span class="ssil-btn-spinner" style="display:none;vertical-align:middle;margin-left:8px;">
                        <span class="yoast-spinner"></span>
                    </span>
                </button>
                <?php wp_nonce_field('ssil_bulk_import', 'ssil_bulk_import_nonce'); ?>
            </form>

            <!-- Export Form -->
            <form method="post" class="yoast-form yoast-bulk-form yoast-mb-0" id="ssil-bulk-export-form" autocomplete="off">
                <button type="submit" name="ssil_export" class="yoast-button-primary ssil-export-btn">
                    <span class="ssil-btn-text">Exporteren</span>
                    <span class="ssil-btn-spinner" style="display:none;vertical-align:middle;margin-left:8px;">
                        <span class="yoast-spinner"></span>
                    </span>
                </button>
                <?php wp_nonce_field('ssil_bulk_export', 'ssil_bulk_export_nonce'); ?>
            </form>
        </div>
    </div>
    <?php
}
?>