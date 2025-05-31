<?php
if (!defined('ABSPATH')) exit;

// Functie om een log toe te voegen
function ssil_add_log($keyword, $url, $page_id = null, $page_title = '') {
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_logs';  // Correct table name

    $wpdb->insert($table, [
        'keyword'    => $keyword,
        'url'        => $url,
        'page_id'    => $page_id,
        'page_title' => $page_title,
        'deleted_at' => current_time('mysql')
    ]);
}

// Opslaan van logs in de juiste tabel (wp_ssil_logs)
function ssil_save_log($keyword, $url, $page_id, $page_title) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'ssil_logs';  // Correct table name

    // Log data
    $log_data = [
        'keyword' => $keyword,
        'url' => $url,
        'date_time' => current_time('mysql'),
        'page_id' => $page_id,
        'page_title' => $page_title
    ];

    // Insert the log data into wp_ssil_logs
    $wpdb->insert($table_name, [
        'keyword'    => $log_data['keyword'],
        'url'        => $log_data['url'],
        'page_id'    => $log_data['page_id'],
        'page_title' => $log_data['page_title'],
        'deleted_at' => current_time('mysql')  // Correct timestamp field
    ]);
}

// Ophalen van logs (laatste 50 als voorbeeld)
function ssil_get_logs($limit = 50) {
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_logs';
    return $wpdb->get_results("SELECT * FROM $table ORDER BY deleted_at DESC LIMIT $limit");
}

// Render functie voor logs pagina
function ssil_render_logs_page() {
    include SSIL_PATH . 'admin/logs-table.php';
}
?>