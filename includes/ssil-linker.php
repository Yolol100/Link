<?php
if (!defined('ABSPATH')) exit;

// Functie om ACF-velden te filteren
function ssil_acf_filter($value, $post_id, $field) {
    if (!$value) return $value;
    return ssil_auto_link_content_filter($value, $post_id);
}

// Functie om de inhoud te doorzoeken en keywords te vervangen door de juiste links
function ssil_auto_link_content_filter($content) {
    global $post;  // Verkrijg het globale post object

    // Verkrijg de post_id vanuit het globale post object
    $post_id = $post ? $post->ID : 0;

    if (!$post_id) return $content;  // Stop als er geen post ID is

    // Haal de blacklist van keywords op
    $blacklist = array_map('trim', explode(',', get_option('ssil_blacklist_keywords', '')));

    // Haal de posttypes op (bijv. posts, pagina's)
    $post_types = get_option('ssil_post_types', ['post', 'page']);
    if (!in_array(get_post_type($post_id), $post_types)) return $content;

    // Haal de keyword-URL mappings op uit de database
    $links = get_option('ssil_links', []);
    if (!$links || !is_array($links)) return $content;

    // Haal de maximale links op die per inhoud mogen worden toegevoegd
    $max_links = (int) get_option('ssil_max_links', 5);
    $link_count = 0;

    // Loop door de links en vervang de keywords door de juiste links
    foreach ($links as $keyword => $info) {
        if ($link_count >= $max_links) break;  // Stop na het maximale aantal links

        // Als het keyword in de inhoud voorkomt, vervang het
        if (strpos($content, $keyword) !== false && !in_array($keyword, $blacklist)) {
            // Vervang het keyword door de link
            $content = str_replace($keyword, '<a href="' . esc_url($info['url']) . '" target="' . (!empty($info['target_blank']) ? '_blank' : '_self') . '" rel="' . (!empty($info['nofollow']) ? 'nofollow' : '') . '">' . $keyword . '</a>', $content);
            $link_count++;

            // Log de vervangingen
            ssil_insert_log_data([  // Insert into the correct table (wp_ssil_logs)
                [
                    'keyword' => $keyword,
                    'url' => $info['url'],
                    'page_id' => $post_id,
                    'page_title' => get_the_title($post_id),
                    'deleted_at' => current_time('mysql')
                ]
            ]);
        }
    }

    return $content;
}

// Functie om loggegevens in de database in te voegen (corrected)
function ssil_insert_log_data($log_data) {
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_logs';  // Correct table name

    foreach ($log_data as $log) {
        // Check if the log already exists (based on a unique combination of fields)
        $existing_log = $wpdb->get_row(
            $wpdb->prepare("SELECT * FROM $table WHERE keyword = %s AND url = %s AND page_id = %d", 
                $log['keyword'], $log['url'], $log['page_id'])
        );

        if ($existing_log) {
            continue;  // Skip if the log already exists
        }

        $log['deleted_at'] = $log['deleted_at'] ? $log['deleted_at'] : current_time('mysql');

        // Insert the log if it's new
        $wpdb->insert(
            $table,
            array(
                'keyword' => $log['keyword'],
                'url' => $log['url'],
                'page_id' => $log['page_id'],
                'page_title' => $log['page_title'],
                'deleted_at' => $log['deleted_at'],
            )
        );
    }
}
?>