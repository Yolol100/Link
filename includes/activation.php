<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function plugin_activation(): void
{
    create_logs_table();
    update_option('ssil_db_version', DB_VERSION, false);

    if (!get_option(OPTION_SETTINGS, false)) {
        update_option(OPTION_SETTINGS, default_settings(), false);
    }

    if (!get_option(OPTION_LINKS, false)) {
        update_option(OPTION_LINKS, [], false);
    }
}

function create_logs_table(): void
{
    global $wpdb;

    $table_name = $wpdb->prefix . 'ssil_logs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$table_name} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        keyword VARCHAR(255) NOT NULL,
        url TEXT NOT NULL,
        page_id BIGINT UNSIGNED DEFAULT NULL,
        page_title VARCHAR(255) DEFAULT NULL,
        deleted_at DATETIME DEFAULT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id),
        KEY keyword (keyword(191)),
        KEY page_id (page_id)
    ) {$charset_collate};";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta($sql);
}
