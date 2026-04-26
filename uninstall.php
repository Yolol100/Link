<?php
/**
 * Uninstall cleanup for Super Simple Internal Links.
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

$settings = get_option('ssil_settings', []);
$delete_data = is_array($settings) && !empty($settings['delete_data_on_uninstall']);

if (!$delete_data) {
    return;
}

delete_option('ssil_settings');
delete_option('ssil_links');
delete_option('ssil_links_last_modified');
delete_option('ssil_db_version');

global $wpdb;
$logs_table = $wpdb->prefix . 'ssil_logs';
$wpdb->query("DROP TABLE IF EXISTS {$logs_table}");
