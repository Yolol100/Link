<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Filter de content en vervang keywords met links volgens mapping en instellingen.
 *
 * @param string $content De originele post content.
 * @return string De aangepaste post content.
 */
function auto_link_content_filter(string $content): string
{
    global $post;
    $post_id = ($post && property_exists($post, 'ID')) ? (int)$post->ID : 0;
    if (!$post_id) {
        return $content;
    }

    $settings = get_option('ssil_settings', []);
    $settings = array_merge([
        'enabled'            => '1',
        'max_keywords'       => 5,
        'exclude_ids'        => [],
        'exclude_classes'    => [],
        'blacklist_keywords' => [],
        'post_types'         => ['post', 'page'],
        'taxonomies'         => ['category', 'post_tag', 'product_cat'],
        'exclude_headings'   => true,
        'exclude_quotes'     => true,
        'exclude_lists'      => true,
    ], is_array($settings) ? $settings : []);

    if (empty($settings['enabled']) || $settings['enabled'] !== '1') {
        return $content;
    }
    if (!empty($settings['exclude_ids']) && in_array($post_id, (array)$settings['exclude_ids'], true)) {
        return $content;
    }
    if (!empty($settings['post_types']) && !in_array(get_post_type($post_id), (array)$settings['post_types'], true)) {
        return $content;
    }

    $links = get_option('ssil_links', []);
    if (!$links || !is_array($links)) {
        return $content;
    }

    // Houd bij hoeveel links per keyword geplaatst zijn in deze post
    $keyword_link_counts = [];

    $max_global_links = !empty($settings['max_keywords']) ? (int)$settings['max_keywords'] : 5;
    $total_link_count = 0;

    // Hulpfunctie om niet te linken in headings, quotes, of lijsten
    $should_exclude_block = function ($content) use ($settings) {
        if ($settings['exclude_headings'] && preg_match('/<h[1-6][^>]*>/', $content)) {
            return true;
        }
        if ($settings['exclude_quotes'] && preg_match('/<(blockquote|q)[^>]*>/', $content)) {
            return true;
        }
        if ($settings['exclude_lists'] && preg_match('/<(ul|ol|li)[^>]*>/', $content)) {
            return true;
        }
        return false;
    };

    foreach ($links as $keyword => $info) {
        if ($total_link_count >= $max_global_links) {
            break;
        }

        if (
            is_string($keyword) && $keyword !== '' &&
            strpos($content, $keyword) !== false &&
            !in_array($keyword, (array)$settings['blacklist_keywords'], true)
        ) {
            $url          = is_array($info) && !empty($info['url'])           ? $info['url']          : '';
            $nofollow     = is_array($info) && !empty($info['nofollow'])      ? 'nofollow'            : '';
            $target       = is_array($info) && !empty($info['target_blank'])  ? '_blank'              : '_self';

            if (!$url) {
                continue;
            }

            // Zorg dat we niet linken in headings/quotes/lijsten als ingesteld
            if ($should_exclude_block($content)) {
                continue;
            }

            // Bouw link (zonder extra class)
            $rel_attr = $nofollow ? ' rel="nofollow"' : '';
            $link = sprintf(
                '<a href="%s" target="%s"%s>%s</a>',
                esc_url($url),
                esc_attr($target),
                $rel_attr,
                esc_html($keyword)
            );

            // Vervang het keyword in content
            $content = str_replace($keyword, $link, $content);

            // Tel links
            $keyword_link_counts[$keyword] = ($keyword_link_counts[$keyword] ?? 0) + 1;
            $total_link_count++;
        }
    }

    return $content;
}