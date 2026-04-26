<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function bulk_page(): void
{
    require_manage_options();

    if (isset($_POST['ssil_import'])) {
        check_admin_referer('ssil_bulk_import', 'ssil_bulk_import_nonce');
        $file = isset($_FILES['csv_file']) && is_array($_FILES['csv_file']) ? $_FILES['csv_file'] : [];
        handle_import($file);
    }

    $success = isset($_GET['ssil_bulk_success']) ? absint($_GET['ssil_bulk_success']) : 0;
    $error = isset($_GET['ssil_bulk_error']) ? sanitize_key((string) wp_unslash($_GET['ssil_bulk_error'])) : '';
    ?>
    <div class="yoast-card yoast-p-4">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>

        <?php if ($success > 0) : ?>
            <div class="notice notice-success"><p><?php echo esc_html(sprintf(_n('%d rij geimporteerd.', '%d rijen geimporteerd.', $success, TEXT_DOMAIN), $success)); ?></p></div>
        <?php elseif ($error !== '') : ?>
            <div class="notice notice-error"><p><?php esc_html_e('Bulkactie mislukt. Controleer het CSV-bestand, de bestandsgrootte en je rechten.', TEXT_DOMAIN); ?></p></div>
        <?php endif; ?>

        <div class="yoast-flex yoast-gap-4 yoast-bulk-row">
            <form method="post" enctype="multipart/form-data" class="yoast-form yoast-bulk-form yoast-mb-0" id="ssil-bulk-import-form" autocomplete="off">
                <label for="ssil_csv_file" class="screen-reader-text"><?php esc_html_e('CSV-bestand importeren', TEXT_DOMAIN); ?></label>
                <input type="file" id="ssil_csv_file" name="csv_file" accept=".csv,text/csv" required class="yoast-input yoast-input-csv">
                <?php wp_nonce_field('ssil_bulk_import', 'ssil_bulk_import_nonce'); ?>
                <button type="submit" name="ssil_import" class="yoast-button-primary yoast-mr-2 ssil-import-btn">
                    <?php esc_html_e('Importeren', TEXT_DOMAIN); ?>
                </button>
            </form>

            <form method="post" class="yoast-form yoast-bulk-form yoast-mb-0" id="ssil-bulk-export-form" autocomplete="off">
                <?php wp_nonce_field('ssil_bulk_export', 'ssil_bulk_export_nonce'); ?>
                <button type="submit" name="ssil_export" class="yoast-button-primary ssil-export-btn">
                    <?php esc_html_e('Exporteren', TEXT_DOMAIN); ?>
                </button>
            </form>
        </div>

        <p class="description">
            <?php esc_html_e('CSV-kolommen: keyword, url, nofollow, target_blank, priority. Maximale bestandsgrootte: 2 MB.', TEXT_DOMAIN); ?>
        </p>
    </div>
    <?php
}
