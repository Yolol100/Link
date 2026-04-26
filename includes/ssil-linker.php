<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function auto_link_block_filter(string $block_content, array $block): string
{
    $skip_blocks = ['core/code', 'core/preformatted', 'core/html', 'core/button', 'core/buttons'];
    if (!empty($block['blockName']) && in_array($block['blockName'], $skip_blocks, true)) {
        return $block_content;
    }
    $filtered = auto_link_content_filter($block_content);
    return is_string($filtered) ? $filtered : $block_content;
}

function auto_link_content_filter($content)
{
    if (!is_string($content) || $content === '') {
        return $content;
    }

    if (is_admin() || is_feed() || wp_doing_ajax() || wp_is_json_request()) {
        return $content;
    }

    if (function_exists('is_preview') && is_preview()) {
        return $content;
    }

    $post_id = get_the_ID();
    $settings = get_settings();

    if ($settings['enabled'] !== '1') {
        return $content;
    }

    if ($post_id > 0) {
        if (in_array((int) $post_id, (array) $settings['exclude_ids'], true)) {
            return $content;
        }
        $post_type = get_post_type($post_id);
        if ($post_type && !empty($settings['post_types']) && !in_array($post_type, (array) $settings['post_types'], true)) {
            return $content;
        }
    }

    $links = get_prepared_links($settings);
    if (empty($links)) {
        return $content;
    }

    foreach ($links as $link) {
        if (stripos($content, $link['keyword']) !== false) {
            return link_text_nodes($content, $links, $settings);
        }
    }

    return $content;
}

function get_prepared_links(array $settings): array
{
    static $cache = [];
    $cache_key = md5(wp_json_encode([$settings, get_option(OPTION_LINKS, [])]));
    if (isset($cache[$cache_key])) {
        return $cache[$cache_key];
    }

    $blacklist = array_map('mb_strtolower', (array) $settings['blacklist_keywords']);
    $prepared = [];
    foreach (get_links() as $keyword => $info) {
        if ($keyword === '' || in_array(mb_strtolower($keyword), $blacklist, true)) {
            continue;
        }
        $prepared[] = [
            'keyword' => (string) $keyword,
            'url' => (string) ($info['url'] ?? ''),
            'nofollow' => !empty($info['nofollow']),
            'target_blank' => !empty($info['target_blank']),
            'priority' => (int) ($info['priority'] ?? 0),
            'length' => mb_strlen((string) $keyword),
        ];
    }

    usort($prepared, static function (array $a, array $b): int {
        if ($a['priority'] !== $b['priority']) {
            return $b['priority'] <=> $a['priority'];
        }
        return $b['length'] <=> $a['length'];
    });

    $cache[$cache_key] = $prepared;
    return $prepared;
}

function link_text_nodes(string $html, array $links, array $settings): string
{
    if (!class_exists('\\DOMDocument')) {
        return link_text_segments_fallback($html, $links, $settings);
    }

    $max_links = max(1, (int) $settings['max_keywords']);
    $total = 0;
    $per_keyword = [];

    $internal_errors = libxml_use_internal_errors(true);
    $dom = new \DOMDocument('1.0', get_bloginfo('charset') ?: 'UTF-8');
    $wrapped = '<!DOCTYPE html><html><body><div id="ssil-root">' . $html . '</div></body></html>';
    $loaded = $dom->loadHTML('<?xml encoding="utf-8" ?>' . $wrapped, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

    if (!$loaded) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);
        return link_text_segments_fallback($html, $links, $settings);
    }

    $xpath = new \DOMXPath($dom);
    $nodes = $xpath->query('//text()[normalize-space(.) != ""]');
    if (!$nodes) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);
        return $html;
    }

    foreach (iterator_to_array($nodes) as $node) {
        if (!$node instanceof \DOMText || $total >= $max_links) {
            break;
        }
        if (should_skip_text_node($node, $settings)) {
            continue;
        }

        $text = $node->nodeValue ?? '';
        foreach ($links as $link) {
            if ($total >= $max_links) {
                break 2;
            }
            $keyword = (string) $link['keyword'];
            if (($per_keyword[$keyword] ?? 0) >= 1 || stripos($text, $keyword) === false) {
                continue;
            }

            $pattern = '/(?<![\pL\pN])(' . preg_quote($keyword, '/') . ')(?![\pL\pN])/iu';
            if (!preg_match($pattern, $text)) {
                continue;
            }

            $fragment = build_replacement_fragment($dom, $text, $pattern, $link);
            if (!$fragment) {
                continue;
            }

            $node->parentNode?->replaceChild($fragment, $node);
            $total++;
            $per_keyword[$keyword] = ($per_keyword[$keyword] ?? 0) + 1;
            break;
        }
    }

    $root = $dom->getElementById('ssil-root');
    if (!$root) {
        libxml_clear_errors();
        libxml_use_internal_errors($internal_errors);
        return $html;
    }

    $output = '';
    foreach ($root->childNodes as $child) {
        $output .= $dom->saveHTML($child);
    }

    libxml_clear_errors();
    libxml_use_internal_errors($internal_errors);
    return $output !== '' ? $output : $html;
}

function should_skip_text_node(\DOMText $node, array $settings): bool
{
    $skip_tags = ssil_skip_tags($settings);
    $classes = (array) $settings['exclude_classes'];
    $parent = $node->parentNode;
    while ($parent instanceof \DOMElement) {
        if (in_array(strtolower($parent->tagName), $skip_tags, true)) {
            return true;
        }
        $class_attr = $parent->getAttribute('class');
        if ($class_attr !== '' && $classes) {
            $element_classes = preg_split('/\s+/', $class_attr) ?: [];
            foreach ($classes as $class) {
                if (in_array($class, $element_classes, true)) {
                    return true;
                }
            }
        }
        $parent = $parent->parentNode;
    }

    return false;
}

function ssil_skip_tags(array $settings): array
{
    $skip_tags = ['a', 'button', 'script', 'style', 'textarea', 'select', 'option', 'code', 'pre', 'kbd', 'samp', 'svg', 'canvas', 'noscript', 'input', 'label'];
    if (!empty($settings['exclude_headings'])) {
        $skip_tags = array_merge($skip_tags, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6']);
    }
    if (!empty($settings['exclude_quotes'])) {
        $skip_tags = array_merge($skip_tags, ['blockquote', 'q']);
    }
    if (!empty($settings['exclude_lists'])) {
        $skip_tags = array_merge($skip_tags, ['ul', 'ol', 'li']);
    }
    return array_values(array_unique($skip_tags));
}

function build_replacement_fragment(\DOMDocument $dom, string $text, string $pattern, array $link): ?\DOMDocumentFragment
{
    $parts = preg_split($pattern, $text, 2, PREG_SPLIT_DELIM_CAPTURE);
    if (!$parts || count($parts) < 3) {
        return null;
    }

    $fragment = $dom->createDocumentFragment();
    if ($parts[0] !== '') {
        $fragment->appendChild($dom->createTextNode($parts[0]));
    }

    $anchor = $dom->createElement('a');
    $anchor->setAttribute('href', esc_url((string) $link['url']));
    $anchor->setAttribute('class', 'ssil-linked');
    if (!empty($link['target_blank'])) {
        $anchor->setAttribute('target', '_blank');
    }

    $rel = [];
    if (!empty($link['nofollow'])) {
        $rel[] = 'nofollow';
    }
    if (!empty($link['target_blank'])) {
        $rel[] = 'noopener';
        $rel[] = 'noreferrer';
    }
    if ($rel) {
        $anchor->setAttribute('rel', implode(' ', array_unique($rel)));
    }
    $anchor->appendChild($dom->createTextNode($parts[1]));
    $fragment->appendChild($anchor);

    if ($parts[2] !== '') {
        $fragment->appendChild($dom->createTextNode($parts[2]));
    }

    return $fragment;
}

function link_text_segments_fallback(string $html, array $links, array $settings): string
{
    $max_links = max(1, (int) $settings['max_keywords']);
    $total = 0;
    $per_keyword = [];
    $skip_tags = ssil_skip_tags($settings);
    $exclude_classes = (array) $settings['exclude_classes'];
    $stack = [];
    $output = '';

    $tokens = preg_split('/(<[^>]+>)/', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!$tokens) {
        return $html;
    }

    foreach ($tokens as $token) {
        if ($token === '') {
            continue;
        }

        if ($token[0] === '<') {
            $tag = ssil_parse_tag_token($token, $skip_tags, $exclude_classes);
            if ($tag['type'] === 'open') {
                $stack[] = $tag['skip'];
            } elseif ($tag['type'] === 'close' && $stack) {
                array_pop($stack);
            }
            $output .= $token;
            continue;
        }

        if ($total < $max_links && !in_array(true, $stack, true)) {
            do {
                $before = $token;
                $token = ssil_replace_first_keyword_in_text($token, $links, $total, $per_keyword, $max_links);
            } while ($total < $max_links && $token !== $before);
        }
        $output .= $token;
    }

    return $output;
}

function ssil_parse_tag_token(string $token, array $skip_tags, array $exclude_classes): array
{
    if (preg_match('/^<\s*\/\s*([a-z0-9:-]+)/i', $token)) {
        return ['type' => 'close', 'skip' => false];
    }

    if (!preg_match('/^<\s*([a-z0-9:-]+)/i', $token, $matches)) {
        return ['type' => 'other', 'skip' => false];
    }

    $tag = strtolower($matches[1]);
    $self_closing = (bool) preg_match('/\/\s*>$/', $token) || in_array($tag, ['br', 'hr', 'img', 'input', 'meta', 'link'], true);
    $skip = in_array($tag, $skip_tags, true);

    if (!$skip && $exclude_classes && preg_match('/\sclass\s*=\s*(["\'])(.*?)\1/is', $token, $class_matches)) {
        $classes = preg_split('/\s+/', trim($class_matches[2])) ?: [];
        foreach ($exclude_classes as $class) {
            if (in_array($class, $classes, true)) {
                $skip = true;
                break;
            }
        }
    }

    return ['type' => $self_closing ? 'other' : 'open', 'skip' => $skip];
}

function ssil_replace_first_keyword_in_text(string $text, array $links, int &$total, array &$per_keyword, int $max_links): string
{
    foreach ($links as $link) {
        if ($total >= $max_links) {
            return $text;
        }
        $keyword = (string) $link['keyword'];
        if ($keyword === '' || ($per_keyword[$keyword] ?? 0) >= 1 || stripos($text, $keyword) === false) {
            continue;
        }

        $pattern = '/(?<![\pL\pN])(' . preg_quote($keyword, '/') . ')(?![\pL\pN])/iu';
        $replaced = preg_replace_callback($pattern, static function (array $matches) use ($link, &$total, &$per_keyword, $keyword, $max_links): string {
            if ($total >= $max_links || ($per_keyword[$keyword] ?? 0) >= 1) {
                return $matches[0];
            }
            $total++;
            $per_keyword[$keyword] = ($per_keyword[$keyword] ?? 0) + 1;
            return ssil_anchor_html((string) $matches[1], $link);
        }, $text, 1);

        if (is_string($replaced) && $replaced !== $text) {
            return $replaced;
        }
    }

    return $text;
}

function ssil_anchor_html(string $label, array $link): string
{
    $attrs = [
        'href' => esc_url((string) $link['url']),
        'class' => 'ssil-linked',
    ];
    if (!empty($link['target_blank'])) {
        $attrs['target'] = '_blank';
    }

    $rel = [];
    if (!empty($link['nofollow'])) {
        $rel[] = 'nofollow';
    }
    if (!empty($link['target_blank'])) {
        $rel[] = 'noopener';
        $rel[] = 'noreferrer';
    }
    if ($rel) {
        $attrs['rel'] = implode(' ', array_unique($rel));
    }

    $html = '<a';
    foreach ($attrs as $name => $value) {
        $html .= ' ' . esc_attr($name) . '="' . esc_attr($value) . '"';
    }
    $html .= '>' . esc_html($label) . '</a>';
    return $html;
}
