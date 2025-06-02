<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Genereert uitgebreide rapportage-statistieken voor alle interne links.
 *
 * @return array<string, mixed>
 */
function get_report_stats(): array
{
    $links = get_option('ssil_links', []);
    if (!is_array($links)) {
        $links = [];
    }

    $all_posts = get_posts([
        'post_type'      => ['post', 'page'],
        'posts_per_page' => -1,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ]);
    $total_posts = count($all_posts);

    $linked_posts         = [];
    $post_link_counts     = [];
    $keyword_usage        = [];
    $keyword_post_usage   = [];
    $url_usage            = [];
    $overlapping_keywords = [];
    $dead_links           = [];
    
    $keyword_array = array_keys($links);

    foreach ($all_posts as $post_id) {
        $content = strtolower((string) get_post_field('post_content', $post_id));
        $found = false;

        foreach ($links as $kw => $info) {
            $url = is_array($info) ? ($info['url'] ?? '') : $info;
            if (!$url) {
                continue;
            }

            // Overlappende keywords zoeken
            foreach ($keyword_array as $kw2) {
                if ($kw !== $kw2 && stripos($kw, $kw2) !== false) {
                    $overlapping_keywords[] = [$kw, $kw2];
                }
            }

            if (strpos($content, strtolower((string) $kw)) !== false) {
                $linked_posts[$post_id] = true;
                $count = substr_count($content, strtolower((string) $kw));
                $keyword_usage[$kw] = ($keyword_usage[$kw] ?? 0) + $count;
                $keyword_post_usage[$kw][] = $post_id;
                $post_link_counts[$post_id] = ($post_link_counts[$post_id] ?? 0) + $count;
                $url_usage[$url] = ($url_usage[$url] ?? 0) + $count;
                $found = true;
            }
        }

        if (!$found) {
            $post_link_counts[$post_id] = 0;
        }
    }

    $unique_keywords      = count($links);
    $posts_with_links     = count($linked_posts);
    $posts_without_links  = $total_posts - $posts_with_links;
    $total_links_placed   = array_sum($keyword_usage);
    $avg_links_per_post   = $total_posts ? round($total_links_placed / $total_posts, 2) : 0;

    // Meest gelinkte keyword
    $most_linked_keyword = '';
    $max_links = 0;
    foreach ($keyword_usage as $kw => $count) {
        if ($count > $max_links) {
            $most_linked_keyword = $kw;
            $max_links = $count;
        }
    }

    // Meest gelinkte URL
    $most_linked_url = '';
    $max_url = 0;
    foreach ($url_usage as $url => $count) {
        if ($count > $max_url) {
            $most_linked_url = $url;
            $max_url = $count;
        }
    }

    $nofollow_count = 0;
    $blank_count = 0;
    $highest_priority = 0;
    $priority_kw = '';
    foreach ($links as $kw => $info) {
        if (is_array($info)) {
            if (!empty($info['nofollow'])) {
                $nofollow_count++;
            }
            if (!empty($info['target_blank'])) {
                $blank_count++;
            }
            if (($info['priority'] ?? 0) > $highest_priority) {
                $highest_priority = $info['priority'];
                $priority_kw = $kw;
            }
        }
    }

    $last_update = get_option('ssil_links_last_modified', 'Onbekend');

    $active_keywords = array_keys(array_filter($keyword_usage));
    $inactive_keywords = array_diff(array_keys($links), $active_keywords);

    // Dubbele overlappende keywords verwijderen
    $overlapping_keywords = array_map('unserialize', array_unique(array_map('serialize', $overlapping_keywords)));

    return [
        'unique_keywords'         => $unique_keywords,
        'active_keywords_count'   => count($active_keywords),
        'inactive_keywords_count' => count($inactive_keywords),
        'posts_with_links'        => $posts_with_links,
        'posts_without_links'     => $posts_without_links,
        'total_posts'             => $total_posts,
        'total_links_placed'      => $total_links_placed,
        'avg_links_per_post'      => $avg_links_per_post,
        'most_linked_keyword'     => $most_linked_keyword,
        'most_linked_url'         => $most_linked_url,
        'nofollow_count'          => $nofollow_count,
        'blank_count'             => $blank_count,
        'priority_kw'             => $priority_kw,
        'last_update'             => $last_update,
        'post_link_counts'        => $post_link_counts,
        'keyword_usage'           => $keyword_usage,
        'keyword_post_usage'      => $keyword_post_usage,
        'url_usage'               => $url_usage,
        'active_keywords_list'    => $active_keywords,
        'inactive_keywords_list'  => $inactive_keywords,
        'overlapping_keywords'    => $overlapping_keywords,
        'dead_links'              => $dead_links,
    ];
}

/**
 * Controleer of een URL werkt (HTTP status < 400).
 *
 * @param string $url
 * @return bool
 */
function check_url_alive(string $url): bool
{
    $response = wp_remote_head($url, ['timeout' => 5]);
    return is_array($response)
        && !is_wp_error($response)
        && isset($response['response']['code'])
        && (int)$response['response']['code'] < 400;
}