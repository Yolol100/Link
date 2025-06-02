<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Toont suggesties voor nieuwe keywords op basis van content.
 *
 * @param int $min_length   Minimaal aantal tekens per keyword (standaard 4)
 * @param int $min_count    Minimaal aantal keer dat het woord voorkomt (standaard 3)
 * @param int $max_results  Maximaal aantal suggesties (standaard 30)
 * @return void
 */
function show_suggested_keywords(int $min_length = 4, int $min_count = 3, int $max_results = 30): void
{
    // Bestaande keywords ophalen en lowercase maken
    $existing_keywords = get_option('ssil_links', []);
    if (!is_array($existing_keywords)) {
        $existing_keywords = [];
    }
    $existing_keywords_lower = array_map('strtolower', array_keys($existing_keywords));

    // Posts ophalen
    $args = [
        'post_type'      => ['post', 'page'],
        'posts_per_page' => 250,
        'post_status'    => 'publish',
        'fields'         => 'ids',
    ];
    $posts = get_posts($args);

    $word_counts = [];

    // Woorden tellen per post
    foreach ($posts as $post_id) {
        $content = strip_tags((string)get_post_field('post_content', $post_id));
        $words = preg_split('/[\s,.\'";:\?\!\(\)\[\]\{\}]+/u', mb_strtolower($content), -1, PREG_SPLIT_NO_EMPTY);
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

    $suggestions = array_filter($word_counts, static fn($count) => $count >= $min_count);
    $suggestions = array_slice($suggestions, 0, $max_results, true);

    if (empty($suggestions)) {
        echo '<div class="yoast-alert-error yoast-mb-4"><p>Geen suggesties gevonden. Probeer eventueel andere instellingen.</p></div>';
        return;
    }
    ?>
    <form id="ssil-suggestions-form" class="yoast-table-wrap yoast-mb-4 ssil-suggest-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
        <h3 class="yoast-mb-2">Suggesties voor nieuwe keywords</h3>
        <table class="yoast-table widefat">
            <thead>
                <tr>
                    <th class="yoast-label" style="width: 42px;">Kies</th>
                    <th class="yoast-label">Keyword</th>
                    <th class="yoast-label">Aantal</th>
                    <th class="yoast-label">URL</th>
                    <th class="yoast-label">Actie</th>
                </tr>
            </thead>
            <tbody>
            <?php
            $idx = 0;
            foreach ($suggestions as $word => $count):
            ?>
                <tr>
                    <td>
                        <input type="checkbox" class="ssil-suggest-checkbox" name="bulk_keywords[]" value="<?php echo esc_attr($word); ?>">
                    </td>
                    <td><?php echo esc_html($word); ?></td>
                    <td><?php echo intval($count); ?></td>
                    <td>
                        <input type="url" name="url_<?php echo esc_attr($idx); ?>" class="yoast-input ssil-suggest-url" placeholder="https://jouw-url.nl">
                    </td>
                    <td>
                        <button type="button" class="yoast-button-primary ssil-suggest-add-btn"
                                data-keyword="<?php echo esc_attr($word); ?>"
                                data-row="<?php echo esc_attr($idx); ?>">
                            Toevoegen
                        </button>
                    </td>
                </tr>
            <?php
                $idx++;
            endforeach;
            ?>
            </tbody>
        </table>

        <?php wp_nonce_field('ssil_add_link_nonce', '_wpnonce'); ?>

        <div class="ssil-suggest-actions yoast-mt-2">
            <button type="button" id="ssil-bulk-suggest-add" class="yoast-button-primary" style="margin-right: 1.2em;">Bulk toevoegen</button>
            <button type="button" id="ssil-suggest-close" class="yoast-button-primary ssil-btn-close-suggest">Sluit suggesties</button>
        </div>
    </form>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Individueel toevoegen
        document.querySelectorAll('.ssil-suggest-add-btn').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const rowIdx = btn.getAttribute('data-row');
                const keyword = btn.getAttribute('data-keyword');
                const urlInput = document.querySelector('input[name="url_' + rowIdx + '"]');
                if (!urlInput || !urlInput.value.trim()) {
                    alert('Vul een geldige URL in.');
                    urlInput && urlInput.focus();
                    return;
                }
                // Maak en submit form
                const form = document.createElement('form');
                form.method = 'post';
                form.action = '<?php echo esc_url(admin_url('admin-post.php')); ?>';
                form.innerHTML = `
                    <input type="hidden" name="action" value="ssil_add_link">
                    <input type="hidden" name="new_word" value="${keyword}">
                    <input type="hidden" name="new_url" value="${urlInput.value.trim()}">
                    <?php echo wp_nonce_field('ssil_add_link_nonce', '_wpnonce', true, false); ?>
                `;
                document.body.appendChild(form);
                form.submit();
            });
        });

        // Bulk toevoegen
        document.getElementById('ssil-bulk-suggest-add').addEventListener('click', function() {
            const checked = document.querySelectorAll('.ssil-suggest-checkbox:checked');
            if (!checked.length) return alert('Selecteer minimaal één keyword.');
            // Verzamel keywords met hun urls
            const data = [];
            checked.forEach(cb => {
                const row = cb.closest('tr');
                const keyword = cb.value;
                const urlInput = row.querySelector('.ssil-suggest-url');
                const url = urlInput ? urlInput.value.trim() : '';
                if (url) data.push({keyword, url});
            });
            if (!data.length) return alert('Vul voor alle geselecteerde keywords een geldige URL in.');
            // Maak en submit bulk form
            const form = document.createElement('form');
            form.method = 'post';
            form.action = '<?php echo esc_url(admin_url('admin-post.php')); ?>';
            form.innerHTML = `<input type="hidden" name="action" value="ssil_bulk_add_links">
                <?php echo wp_nonce_field('ssil_bulk_add_links_nonce', '_wpnonce', true, false); ?>`;
            data.forEach((pair, i) => {
                form.innerHTML += `<input type="hidden" name="bulk_keywords[]" value="${pair.keyword}">`;
                form.innerHTML += `<input type="hidden" name="bulk_urls[]" value="${pair.url}">`;
            });
            document.body.appendChild(form);
            form.submit();
        });

        // Sluit suggesties knop (indien gewenst, hier alleen verbergen)
        const closeBtn = document.getElementById('ssil-suggest-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                const form = document.querySelector('.ssil-suggest-form');
                if (form) form.style.display = 'none';
            });
        }
    });
    </script>
    <?php
}