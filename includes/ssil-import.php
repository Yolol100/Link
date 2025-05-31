<?php
if (!defined('ABSPATH')) exit;

function ssil_handle_import($filename) {
    // Check if the file exists and is readable
    if (!file_exists($filename) || !is_readable($filename)) {
        wp_redirect(admin_url('admin.php?page=ssil_bulk&ssil_bulk_error=1'));
        exit;
    }

    // Read all rows from the CSV file
    $rows = array_map('str_getcsv', file($filename));

    // Get the current links stored in the WordPress database
    $links = get_option('ssil_links', []);

    // Skip the header row (the first row in the CSV)
    $header = array_shift($rows);

    // Loop through each row and process the data
    foreach ($rows as $row) {
        // Ensure the row has at least 5 columns: keyword, url, nofollow, target_blank, priority
        if (count($row) < 5) {
            continue;  // Skip rows with insufficient data
        }

        // Extract and sanitize the data from the row
        $word = isset($row[0]) ? trim($row[0]) : '';  // First column: keyword
        $url  = isset($row[1]) ? trim($row[1]) : '';  // Second column: URL
        $nofollow = !empty($row[2]) && strtolower($row[2]) === '1'; // Third column: nofollow
        $target_blank = !empty($row[3]) && strtolower($row[3]) === '1'; // Fourth column: target_blank
        $priority = isset($row[4]) ? (int)$row[4] : 0;  // Fifth column: priority

        // Only proceed if we have both a keyword and URL
        if ($word && $url) {
            // Sanitize the URL to prevent XSS or invalid URLs
            $url = esc_url_raw($url);

            // Add the link to the existing links array
            $links[$word] = [
                'url' => $url,
                'nofollow' => $nofollow,
                'target_blank' => $target_blank,
                'priority' => $priority
            ];
        }
    }

    // Update the links in the WordPress database
    update_option('ssil_links', $links);

    // Redirect back to the bulk page with success message
    wp_redirect(admin_url('admin.php?page=ssil_bulk&ssil_bulk_success=1'));
    exit;
}