<?php
if (!defined('ABSPATH')) exit;

/**
 * Toont suggesties voor nieuwe keywords op basis van post/page content.
 * Print een formulier met checkboxen om ze toe te voegen en actieknoppen.
 *
 * @param int $min_length Minimaal aantal tekens per keyword (standaard 4)
 * @param int $min_count Minimaal aantal keren dat het woord voorkomt (standaard 3)
 * @param int $max_results Maximaal aantal suggesties om te tonen (standaard 30)
 */
function ssil_show_suggested_keywords($min_length = 4, $min_count = 3, $max_results = 30) {
    $existing_keywords = get_option('ssil_links', []);
    if (!is_array($existing_keywords)) $existing_keywords = [];
    $existing_keywords_lower = array_map('strtolower', array_keys($existing_keywords));

    $args = [
        'post_type'      => ['post', 'page'],
        'posts_per_page' => 250,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ];
    $posts = get_posts($args);

    $word_counts = [];

    foreach ($posts as $post_id) {
        $content = strip_tags(get_post_field('post_content', $post_id));
        $words = preg_split('/[\s,.\'";:\?\!\(\)\[\]\{\}]+/u', strtolower($content), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            if (mb_strlen($word) < $min_length) continue;
            if (preg_match('/^\d+$/', $word)) continue;
            if (in_array($word, ['https', 'http', 'www', 'nbsp'], true)) continue;
            if (in_array($word, $existing_keywords_lower, true)) continue;
            if (!isset($word_counts[$word])) $word_counts[$word] = 0;
            $word_counts[$word]++;
        }
    }

    arsort($word_counts);

    $suggestions = array_filter($word_counts, fn($count) => $count >= $min_count);
    $suggestions = array_slice($suggestions, 0, $max_results, true);

    if (empty($suggestions)) {
        echo '<div class="yoast-alert-error yoast-mb-4"><p>Geen suggesties gevonden. Probeer eventueel andere instellingen.</p></div>';
        return;
    }

    // Print het formulier (los van de functie als je wilt)
    ?>
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="yoast-form yoast-mb-4 ssil-suggest-form">
        <h3 class="yoast-mb-2">Suggesties voor nieuwe keywords</h3>
        <table class="yoast-table widefat yoast-mb-4 ssil-suggest-table">
            <thead>
                <tr>
                    <th class="yoast-label" style="width: 42px;">Kies</th>
                    <th class="yoast-label">Keyword</th>
                    <th class="yoast-label">Aantal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suggestions as $word => $count): ?>
                    <tr>
                        <td><input type="checkbox" name="ssil_new_keywords[]" value="<?php echo esc_attr($word); ?>" class="yoast-checkbox"></td>
                        <td><?php echo esc_html($word); ?></td>
                        <td><?php echo intval($count); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php wp_nonce_field('ssil_suggest_keywords', 'ssil_suggest_keywords_nonce'); ?>
        <input type="hidden" name="action" value="ssil_add_suggested_keywords" />

        <div class="ssil-suggest-actions">
            <button type="submit" name="ssil_add_suggested_keywords" class="yoast-button-primary ssil-btn-add-suggested">
                Toevoegen als keywords
            </button>
            <button type="button" id="ssil-suggest-close" class="yoast-button-primary ssil-btn-close-suggest">
                Sluit suggesties
            </button>
        </div>
    </form>
    <?php
}

// Handler om de geselecteerde suggesties als nieuwe keywords toe te voegen.
add_action('admin_post_ssil_add_suggested_keywords', function() {
    if (!current_user_can('manage_options')) wp_die('Geen toegang');
    if (!isset($_POST['ssil_suggest_keywords_nonce']) || !wp_verify_nonce($_POST['ssil_suggest_keywords_nonce'], 'ssil_suggest_keywords')) wp_die('Nonce error!');

    $new_keywords = isset($_POST['ssil_new_keywords']) ? (array) $_POST['ssil_new_keywords'] : [];
    $links = get_option('ssil_links', []);

    foreach ($new_keywords as $keyword) {
        $keyword = sanitize_text_field($keyword);
        if (!isset($links[$keyword])) {
            $links[$keyword] = ''; // Lege URL, moet admin handmatig invullen
        }
    }

    update_option('ssil_links', $links);

    wp_redirect(admin_url('admin.php?page=ssil_keywords&ssil_added=1'));
    exit;
});
?>