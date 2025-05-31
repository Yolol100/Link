<?php
if (!defined('ABSPATH')) exit;

// Make sure WordPress admin environment is fully loaded
add_action('init', function() {
    // Check if the export button is clicked and nonce is valid
    if (isset($_POST['ssil_export']) && check_admin_referer('ssil_bulk_export', 'ssil_bulk_export_nonce')) {
        ssil_handle_export();  // Call the export function if button is clicked
    }
});

function ssil_handle_export() {
    // Get the keyword-to-URL mappings from the database
    $links = get_option('ssil_links', []);

    // Set headers for the CSV file
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="keywords-export.csv"');

    // Open PHP's output stream for writing CSV
    $output = fopen('php://output', 'w');

    // Write the CSV header
    fputcsv($output, ['keyword', 'url', 'nofollow', 'target_blank', 'priority']);
    
    // Process each keyword and URL mapping and write to CSV
    foreach ($links as $k => $info) {
        // Extract URL and other settings
        $url = is_array($info) ? ($info['url'] ?? '') : $info;
        
        // If the URL is relative (e.g., "/page"), convert it to a full URL
        if (strpos($url, '/') === 0) {
            // Make it a full URL by prepending the site URL
            $url = get_site_url() . $url;
        }

        // Check for additional settings (nofollow, target_blank, priority)
        $nofollow = is_array($info) ? (!empty($info['nofollow']) ? '1' : '0') : '0';
        $target_blank = is_array($info) ? (!empty($info['target_blank']) ? '1' : '0') : '0';
        $priority = is_array($info) ? (int)($info['priority'] ?? 0) : 0;

        // Write the row for each keyword mapping
        fputcsv($output, [$k, $url, $nofollow, $target_blank, $priority]);
    }

    // Close the output stream to finish writing
    fclose($output);
    exit;
}