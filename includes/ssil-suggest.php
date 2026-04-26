<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

function show_suggested_keywords(int $min_length = 4, int $min_count = 3, int $max_results = 30): void
{
    require_manage_options();

    $settings = get_settings();
    $post_types = !empty($settings['post_types']) ? (array) $settings['post_types'] : ['post', 'page'];
    $existing_keywords = array_map('mb_strtolower', array_keys(get_links()));

    $posts = get_posts([
        'post_type' => $post_types,
        'posts_per_page' => 150,
        'post_status' => 'publish',
        'fields' => 'ids',
        'no_found_rows' => true,
    ]);

    $stop_words = [
        'https', 'http', 'www', 'nbsp', 'voor', 'met', 'van', 'een', 'het', 'de', 'en', 'of', 'op', 'in', 'te', 'is', 'zijn', 'aan', 'als', 'bij', 'dat', 'dit', 'die', 'naar', 'door', 'over', 'uit', 'jouw', 'onze', 'wordt', 'worden', 'meer', 'ook',
    ];
    $word_counts = [];

    foreach ($posts as $post_id) {
        $content = wp_strip_all_tags((string) get_post_field('post_content', (int) $post_id));
        $words = preg_split('/[^\pL\pN\-]+/u', mb_strtolower($content), -1, PREG_SPLIT_NO_EMPTY);
        if (!$words) {
            continue;
        }
        foreach ($words as $word) {
            $word = sanitize_keyword((string) $word);
            if (mb_strlen($word) < $min_length) {
                continue;
            }
            if (preg_match('/^\d+$/', $word)) {
                continue;
            }
            if (in_array($word, $stop_words, true) || in_array(mb_strtolower($word), $existing_keywords, true)) {
                continue;
            }
            $word_counts[$word] = ($word_counts[$word] ?? 0) + 1;
        }
    }

    arsort($word_counts);
    $suggestions = array_filter($word_counts, static fn($count): bool => (int) $count >= $min_count);
    $suggestions = array_slice($suggestions, 0, $max_results, true);

    if (empty($suggestions)) {
        echo '<div class="notice notice-info"><p>' . esc_html__('Geen suggesties gevonden. Voeg meer content toe of verlaag de minimale frequentie.', TEXT_DOMAIN) . '</p></div>';
        return;
    }
    ?>
    <form id="ssil-suggestions-form" class="yoast-table-wrap yoast-mb-4 ssil-suggest-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <input type="hidden" name="action" value="ssil_bulk_add_links">
        <?php wp_nonce_field('ssil_bulk_add_links_nonce'); ?>
        <h3 class="yoast-mb-2"><?php esc_html_e('Suggesties voor nieuwe keywords', TEXT_DOMAIN); ?></h3>
        <p class="description"><?php esc_html_e('Selecteer alleen keywords die echt als interne link bedoeld zijn en vul per gekozen keyword een geldige URL in.', TEXT_DOMAIN); ?></p>
        <table class="yoast-table widefat">
            <thead>
                <tr>
                    <th class="yoast-label" style="width:42px;"><?php esc_html_e('Kies', TEXT_DOMAIN); ?></th>
                    <th class="yoast-label"><?php esc_html_e('Keyword', TEXT_DOMAIN); ?></th>
                    <th class="yoast-label"><?php esc_html_e('Aantal', TEXT_DOMAIN); ?></th>
                    <th class="yoast-label"><?php esc_html_e('URL', TEXT_DOMAIN); ?></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($suggestions as $word => $count) : ?>
                <tr>
                    <td>
                        <input type="checkbox" class="ssil-suggest-checkbox" name="bulk_keywords[]" value="<?php echo esc_attr($word); ?>" aria-label="<?php echo esc_attr(sprintf(__('Keyword %s selecteren', TEXT_DOMAIN), $word)); ?>">
                    </td>
                    <td><?php echo esc_html($word); ?></td>
                    <td><?php echo esc_html((string) absint($count)); ?></td>
                    <td>
                        <label class="screen-reader-text" for="ssil_bulk_url_<?php echo esc_attr(md5((string) $word)); ?>"><?php echo esc_html(sprintf(__('URL voor %s', TEXT_DOMAIN), $word)); ?></label>
                        <input id="ssil_bulk_url_<?php echo esc_attr(md5((string) $word)); ?>" type="url" name="bulk_urls[<?php echo esc_attr($word); ?>]" class="yoast-input ssil-suggest-url" placeholder="https://jouw-url.nl">
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>

        <div class="ssil-suggest-actions yoast-mt-2">
            <button type="submit" class="yoast-button-primary" style="margin-right:1.2em;"><?php esc_html_e('Geselecteerde toevoegen', TEXT_DOMAIN); ?></button>
            <a class="yoast-button-primary ssil-btn-close-suggest" href="<?php echo esc_url(admin_url('admin.php?page=ssil_keywords')); ?>"><?php esc_html_e('Sluit suggesties', TEXT_DOMAIN); ?></a>
        </div>
    </form>
    <?php
}

function handle_bulk_add_links(): void
{
    require_manage_options();
    check_admin_referer('ssil_bulk_add_links_nonce');

    $post = wp_unslash($_POST);
    $keywords = isset($post['bulk_keywords']) && is_array($post['bulk_keywords']) ? $post['bulk_keywords'] : [];
    $urls = isset($post['bulk_urls']) && is_array($post['bulk_urls']) ? $post['bulk_urls'] : [];

    if (!$keywords || !$urls) {
        admin_redirect('ssil_keywords', ['ssil_error' => 'invalid']);
    }

    $links = get_links();
    $added = 0;
    foreach ($keywords as $raw_keyword) {
        $keyword = sanitize_keyword((string) $raw_keyword);
        if ($keyword === '') {
            continue;
        }
        $url = isset($urls[$keyword]) ? sanitize_link_url((string) $urls[$keyword]) : '';
        if ($url === '') {
            continue;
        }
        $links[$keyword] = [
            'url' => $url,
            'nofollow' => false,
            'target_blank' => false,
            'priority' => 0,
        ];
        $added++;
    }

    if ($added < 1) {
        admin_redirect('ssil_keywords', ['ssil_error' => 'invalid']);
    }

    update_links($links);
    admin_redirect('ssil_keywords', ['ssil_success' => 'bulk_added', 'ssil_added' => (string) $added]);
}
add_action('admin_post_ssil_bulk_add_links', __NAMESPACE__ . '\\handle_bulk_add_links');
