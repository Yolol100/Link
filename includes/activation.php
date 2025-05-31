<?php
if (!defined('ABSPATH')) exit;

// Start output buffering to capture any unwanted output
ob_start();

// Functie die zowel de logtabel als de plugin data tabel aanmaakt bij activatie
function ssil_plugin_activation() {
    ssil_create_logs_table();  // Maak de logtabel aan
    ssil_create_plugin_data_table();  // Maak de plugin data tabel aan
}

// Functie om de logtabel aan te maken bij pluginactivatie
function ssil_create_logs_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_logs';  // Tabelnaam
    $charset_collate = $wpdb->get_charset_collate();  // Haal de charset op voor de tabel

    // SQL-query voor het maken van de tabel
    $sql = "CREATE TABLE IF NOT EXISTS $table (
        id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,  -- Auto increment id voor elke log
        keyword VARCHAR(255) NOT NULL,  -- Het keyword dat gelinkt is
        url TEXT NOT NULL,  -- De URL van het keyword
        page_id BIGINT UNSIGNED DEFAULT NULL,  -- De ID van de pagina (optioneel)
        page_title VARCHAR(255) DEFAULT NULL,  -- De titel van de pagina (optioneel)
        deleted_at DATETIME DEFAULT NULL,  -- Het moment dat de log verwijderd werd (optioneel)
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,  -- Het moment dat de log werd aangemaakt
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- Het moment van de laatste update
    ) $charset_collate;";

    // Voer de query uit om de tabel aan te maken
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);  // Voer de query uit om de tabel aan te maken
}

// Functie om de plugin data tabel aan te maken bij pluginactivatie
function ssil_create_plugin_data_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'ssil_plugin_data';  // Tabelnaam
    $charset_collate = $wpdb->get_charset_collate();  // Haal de charset op voor de tabel

    // Check if the table exists first
    if ($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
        // SQL-query voor het maken van de tabel
        $sql = "CREATE TABLE IF NOT EXISTS $table (
            id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,  -- Auto increment id voor elke record
            ssil_max_links INT DEFAULT 3,  -- Maximum links per post/page
            ssil_exclude_ids TEXT,  -- Comma-separated list of excluded post/page IDs
            ssil_exclude_classes TEXT,  -- Comma-separated list of excluded CSS classes
            ssil_blacklist_keywords TEXT,  -- Comma-separated list of blacklisted keywords
            ssil_post_types TEXT,  -- Supported post types (e.g., post, page)
            ssil_taxonomies TEXT,  -- Supported taxonomies (e.g., category, post_tag)
            ssil_logs TEXT,  -- Column to store serialized logs (missing column)
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,  -- Timestamp when the record was created
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP  -- Timestamp when the record was last updated
        ) $charset_collate;";

        // Voer de query uit om de tabel aan te maken of bij te werken
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);  // Voer de query uit om de tabel aan te maken of bij te werken
    }

    // Zorg ervoor dat de kolom 'ssil_logs' bestaat, zo nodig toevoegen
    $wpdb->query("ALTER TABLE $table ADD COLUMN IF NOT EXISTS ssil_logs TEXT;");
}

// Activatiehook die de tabellen maakt bij pluginactivatie
register_activation_hook(__FILE__, 'ssil_plugin_activation');

// End output buffering and discard any output
ob_end_clean();
?>