<?php
if (!defined('ABSPATH')) exit;

// AJAX handler voor URL suggesties
add_action('wp_ajax_ssil_suggest_url', function() {
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Geen toegang');
        wp_die();
    }
    $keyword = isset($_GET['keyword']) ? sanitize_text_field(wp_unslash($_GET['keyword'])) : '';
    if (!$keyword) {
        wp_send_json_error('Geen keyword');
        wp_die();
    }
    $args = [
        'post_type'      => ['post', 'page'],
        's'              => $keyword,
        'posts_per_page' => 1,
        'post_status'    => 'publish',
    ];
    $query = new WP_Query($args);
    if ($query->have_posts()) {
        $query->the_post();
        wp_send_json_success(['url' => get_permalink()]);
    } else {
        wp_send_json_success(['url' => '']);
    }
    wp_die();
});
?>