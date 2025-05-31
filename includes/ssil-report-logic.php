<?php

if (!defined('ABSPATH')) exit;

function ssil_get_report_stats() {
    // Retrieve the links from the options
    $links = get_option('ssil_links', []);

    // Ensure that $links is always an array
    if (!is_array($links)) {
        $links = [];
    }

    // Fetch all published posts and pages
    $all_posts = get_posts([
        'post_type'      => ['post', 'page'],
        'posts_per_page' => -1,
        'post_status'    => 'publish'
    ]);
    $total_posts = count($all_posts);

    // Initialize statistics arrays
    $linked_posts         = [];
    $post_link_counts     = [];
    $keyword_usage        = [];
    $keyword_post_usage   = [];
    $url_usage            = [];
    $overlapping_keywords = [];
    $dead_links           = [];
    $active_keywords      = [];
    $inactive_keywords    = [];

    // Extract the keyword array from links
    $keyword_array = array_keys($links);

    // Search content for all posts
    foreach ($all_posts as $post) {
        $content = strtolower(get_post_field('post_content', $post->ID));
        $found = false;
        foreach ($links as $kw => $info) {
            // Extract URL from the info
            $url = is_array($info) ? ($info['url'] ?? '') : $info;
            if (!$url) continue;

            // Find overlapping keywords
            foreach ($keyword_array as $kw2) {
                if ($kw !== $kw2 && stripos($kw, $kw2) !== false) {
                    $overlapping_keywords[] = [$kw, $kw2];
                }
            }

            // Count how many times the keyword appears in the content
            if (strpos($content, strtolower($kw)) !== false) {
                $linked_posts[$post->ID] = true;
                $count = substr_count($content, strtolower($kw));
                if (!isset($keyword_usage[$kw])) $keyword_usage[$kw] = 0;
                $keyword_usage[$kw] += $count;
                if (!isset($keyword_post_usage[$kw])) $keyword_post_usage[$kw] = [];
                $keyword_post_usage[$kw][] = $post->ID;
                $post_link_counts[$post->ID] = ($post_link_counts[$post->ID] ?? 0) + $count;
                $url_usage[$url] = ($url_usage[$url] ?? 0) + $count;
                $found = true;
            }
        }
        if (!$found) $post_link_counts[$post->ID] = 0;
    }

    // Statistics
    $unique_keywords      = count($links);
    $posts_with_links     = count($linked_posts);
    $posts_without_links  = $total_posts - $posts_with_links;
    $total_links_placed   = array_sum($keyword_usage);

    // Average number of links per post
    $avg = $total_posts ? round($total_links_placed / $total_posts, 2) : 0;

    // Find the most linked keyword
    $most_linked_keyword = '';
    $max_links = 0;
    foreach ($keyword_usage as $kw => $count) {
        if ($count > $max_links) {
            $most_linked_keyword = $kw;
            $max_links = $count;
        }
    }

    // Find the most linked URL
    $most_linked_url = '';
    $max_url = 0;
    foreach ($url_usage as $url => $count) {
        if ($count > $max_url) {
            $most_linked_url = $url;
            $max_url = $count;
        }
    }

    // Count nofollow, target=_blank, and highest priority links
    $nofollow_count = 0;
    $blank_count    = 0;
    $highest_priority = 0;
    $priority_kw      = '';
    foreach ($links as $kw => $info) {
        if (is_array($info)) {
            if (!empty($info['nofollow'])) $nofollow_count++;
            if (!empty($info['target_blank'])) $blank_count++;
            if (($info['priority'] ?? 0) > $highest_priority) {
                $highest_priority = $info['priority'];
                $priority_kw = $kw;
            }
        }
    }

    // Last update timestamp
    $last_update = get_option('ssil_links_last_modified', 'Onbekend');

    // Active/inactive keywords
    $active_keywords   = array_keys(array_filter($keyword_usage));
    $inactive_keywords = array_diff(array_keys($links), $active_keywords);

    // Remove duplicate overlapping keywords
    $overlapping_keywords = array_map("unserialize", array_unique(array_map("serialize", $overlapping_keywords)));

    // Optional: Quality control - Dead links
    /*
    foreach ($links as $info) {
        $url = is_array($info) ? ($info['url'] ?? '') : $info;
        if ($url && !ssil_check_url_alive($url)) {
            $dead_links[] = $url;
        }
    }
    */

    // Return the report data
    return [
        // General statistics
        'unique_keywords'        => $unique_keywords,
        'active_keywords_count'  => count($active_keywords),
        'inactive_keywords_count'=> count($inactive_keywords),
        'posts_with_links'       => $posts_with_links,
        'posts_without_links'    => $posts_without_links,
        'total_posts'            => $total_posts,
        'total_links_placed'     => $total_links_placed,
        'avg_links_per_post'     => $avg,
        'most_linked_keyword'    => $most_linked_keyword,
        'most_linked_url'        => $most_linked_url,
        'nofollow_count'         => $nofollow_count,
        'blank_count'            => $blank_count,
        'priority_kw'            => $priority_kw,
        'last_update'            => $last_update,

        // Details & per keyword/post
        'post_link_counts'       => $post_link_counts,    // [post_id => #links]
        'keyword_usage'          => $keyword_usage,       // [kw => total #]
        'keyword_post_usage'     => $keyword_post_usage,  // [kw => [post_id, ...]]
        'url_usage'              => $url_usage,           // [url => #]
        'active_keywords_list'   => $active_keywords,     // [kw, ...]
        'inactive_keywords_list' => $inactive_keywords,   // [kw, ...]
        'overlapping_keywords'   => $overlapping_keywords,// [[kw1,kw2],...]
        'dead_links'             => $dead_links,          // [url, ...] (optional)
    ];
}

// (Optional) Dead link checker (use for report export or after manual click)
function ssil_check_url_alive($url) {
    $response = wp_remote_head($url, ['timeout' => 5]);
    return is_array($response) && !is_wp_error($response) && isset($response['response']['code']) && $response['response']['code'] < 400;
}