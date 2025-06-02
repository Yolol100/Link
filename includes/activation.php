<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

// === PLUGIN ACTIVATION HOOK ===

/**
 * Plugin activation callback.
 * Creates both the log and plugin data tables.
 *
 * @return void
 */
function plugin_activation(): void
{
    create_logs_table();
    create_plugin_data_table();
}
register_activation_hook(__FILE__, __NAMESPACE__ . '\\plugin_activation');

// === TABLE CREATION ===

/**
 * Create the log table for SSIL.
 *
 * @global \wpdb $wpdb
 * @return void
 */
function create_logs_table(): void
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ssil_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$table_name} (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    keyword VARCHAR(255) NOT NULL,
    url TEXT NOT NULL,
    page_id BIGINT UNSIGNED DEFAULT NULL,
    page_title VARCHAR(255) DEFAULT NULL,
    deleted_at DATETIME DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) {$charset_collate};
SQL;

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    \dbDelta($sql);
}

/**
 * Create the plugin data table for SSIL, and ensure the 'ssil_logs' column exists.
 *
 * @global \wpdb $wpdb
 * @return void
 */
function create_plugin_data_table(): void
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ssil_plugin_data';
    $charset_collate = $wpdb->get_charset_collate();

    if ($wpdb->get_var($wpdb->prepare(
        "SHOW TABLES LIKE %s", $table_name
    )) !== $table_name) {
        $sql = <<<SQL
CREATE TABLE IF NOT EXISTS {$table_name} (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    ssil_max_links INT DEFAULT 3,
    ssil_exclude_ids TEXT,
    ssil_exclude_classes TEXT,
    ssil_blacklist_keywords TEXT,
    ssil_post_types TEXT,
    ssil_taxonomies TEXT,
    ssil_logs TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) {$charset_collate};
SQL;
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        \dbDelta($sql);
    }

    // Make sure the 'ssil_logs' column exists (idempotent in MySQL 8.2+)
    $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN IF NOT EXISTS ssil_logs TEXT;");
}