<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function get_report_stats(): array
{
    require_manage_options();
    $links = get_links();
    $settings = get_settings();
    $post_types = !empty($settings['post_types']) ? $settings['post_types'] : ['post', 'page'];
    $paged = isset($_GET['ssil_report_page']) ? max(1, absint(wp_unslash($_GET['ssil_report_page']))) : 1;
    $per_page = 100;

    $query = new \WP_Query([
        'post_type' => $post_types,
        'post_status' => 'publish',
        'fields' => 'ids',
        'posts_per_page' => $per_page,
        'paged' => $paged,
        'no_found_rows' => false,
    ]);

    $post_ids = array_map('absint', $query->posts);
    $total_posts = (int) $query->found_posts;
    $linked_posts = [];
    $post_link_counts = [];
    $keyword_usage = [];
    $keyword_post_usage = [];
    $url_usage = [];

    foreach ($post_ids as $post_id) {
        $content = mb_strtolower(wp_strip_all_tags((string) get_post_field('post_content', $post_id)));
        $found = false;
        foreach ($links as $kw => $info) {
            $kw_lc = mb_strtolower((string) $kw);
            if ($kw_lc === '' || !str_contains($content, $kw_lc)) {
                continue;
            }
            $count = substr_count($content, $kw_lc);
            $url = (string) ($info['url'] ?? '');
            $linked_posts[$post_id] = true;
            $keyword_usage[$kw] = ($keyword_usage[$kw] ?? 0) + $count;
            $keyword_post_usage[$kw][] = $post_id;
            $post_link_counts[$post_id] = ($post_link_counts[$post_id] ?? 0) + $count;
            if ($url !== '') {
                $url_usage[$url] = ($url_usage[$url] ?? 0) + $count;
            }
            $found = true;
        }
        if (!$found) {
            $post_link_counts[$post_id] = 0;
        }
    }

    $total_links_placed = array_sum($keyword_usage);
    arsort($keyword_usage);
    arsort($url_usage);
    $active_keywords = array_keys(array_filter($keyword_usage));
    $inactive_keywords = array_diff(array_keys($links), $active_keywords);

    $nofollow_count = 0;
    $blank_count = 0;
    $highest_priority = PHP_INT_MIN;
    $priority_kw = '';
    foreach ($links as $kw => $info) {
        if (!empty($info['nofollow'])) {
            $nofollow_count++;
        }
        if (!empty($info['target_blank'])) {
            $blank_count++;
        }
        $priority = (int) ($info['priority'] ?? 0);
        if ($priority > $highest_priority) {
            $highest_priority = $priority;
            $priority_kw = (string) $kw;
        }
    }

    return [
        'unique_keywords' => count($links),
        'active_keywords_count' => count($active_keywords),
        'inactive_keywords_count' => count($inactive_keywords),
        'posts_with_links' => count($linked_posts),
        'posts_without_links' => max(0, $total_posts - count($linked_posts)),
        'total_posts' => $total_posts,
        'total_links_placed' => $total_links_placed,
        'avg_links_per_post' => $total_posts ? round($total_links_placed / $total_posts, 2) : 0,
        'most_linked_keyword' => array_key_first($keyword_usage) ?: '',
        'most_linked_url' => array_key_first($url_usage) ?: '',
        'nofollow_count' => $nofollow_count,
        'blank_count' => $blank_count,
        'priority_kw' => $priority_kw,
        'last_update' => get_option('ssil_links_last_modified', __('Onbekend', TEXT_DOMAIN)),
        'post_link_counts' => $post_link_counts,
        'keyword_usage' => $keyword_usage,
        'keyword_post_usage' => $keyword_post_usage,
        'url_usage' => $url_usage,
        'active_keywords_list' => $active_keywords,
        'inactive_keywords_list' => $inactive_keywords,
        'overlapping_keywords' => find_overlapping_keywords(array_keys($links)),
        'dead_links' => [],
        'pagination' => ['current' => $paged, 'per_page' => $per_page, 'total_pages' => max(1, (int) $query->max_num_pages)],
    ];
}

function find_overlapping_keywords(array $keywords): array
{
    $pairs = [];
    foreach ($keywords as $a) {
        foreach ($keywords as $b) {
            if ($a !== $b && $a !== '' && $b !== '' && mb_stripos((string) $a, (string) $b) !== false) {
                $pairs[] = [(string) $a, (string) $b];
            }
        }
    }
    return array_map('unserialize', array_unique(array_map('serialize', $pairs)));
}

function check_url_alive(string $url): bool
{
    $response = wp_remote_head($url, ['timeout' => 5, 'redirection' => 2]);
    return is_array($response) && !is_wp_error($response) && isset($response['response']['code']) && (int) $response['response']['code'] < 400;
}
