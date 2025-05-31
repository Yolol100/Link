<?php
if (!defined('ABSPATH')) exit;

// Registreer alle plugin-instellingen
add_action('admin_init', function() {
    register_setting('ssil_settings_group', 'ssil_enabled');
    register_setting('ssil_settings_group', 'ssil_max_keywords');
    register_setting('ssil_settings_group', 'ssil_exclude_ids');
    register_setting('ssil_settings_group', 'ssil_exclude_classes');
    register_setting('ssil_settings_group', 'ssil_blacklist_keywords');
    register_setting('ssil_settings_group', 'ssil_post_types', ['type' => 'array']);
    register_setting('ssil_settings_group', 'ssil_taxonomies', ['type' => 'array']);
    register_setting('ssil_settings_group', 'ssil_links');
});