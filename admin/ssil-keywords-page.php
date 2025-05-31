<?php
if (!defined('ABSPATH')) exit;

function ssil_keywords_page() {
    ?>
    <div class="yoast-card yoast-p-4">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>

        <div class="yoast-flex yoast-justify-between yoast-mb-4 yoast-keywords-toolbar">
            <a href="<?php echo admin_url('admin.php?page=ssil_keywords&ssil_action=suggest_keywords'); ?>"
               class="yoast-button-primary yoast-mr-2 yoast-btn-suggest"
               style="<?php echo (isset($_GET['ssil_action']) && $_GET['ssil_action'] === 'suggest_keywords') ? 'display:none;' : ''; ?>">
               Suggesties tonen
            </a>
            <form method="get" class="yoast-form yoast-keywords-searchform">
                <input type="hidden" name="page" value="ssil_keywords" />
                <input type="text" name="ssil_search"
                       value="<?php echo isset($_GET['ssil_search']) ? esc_attr($_GET['ssil_search']) : ''; ?>"
                       placeholder="Zoek keyword..." class="yoast-input yoast-search-input" />
                <button type="submit" class="yoast-button-primary yoast-btn-search">Zoeken</button>
            </form>
        </div>

        <?php if (isset($_GET['ssil_action']) && $_GET['ssil_action'] === 'suggest_keywords') : ?>
        <div id="ssil-suggest-wrap" class="yoast-mb-4">
            <?php
                require_once SSIL_PATH . 'includes/ssil-suggest.php';
                if (function_exists('ssil_show_suggested_keywords')) {
                    ssil_show_suggested_keywords();
                }
            ?>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            var closeBtn = document.getElementById('ssil-suggest-close');
            if(closeBtn) {
                closeBtn.addEventListener('click', function() {
                    var suggestWrap = document.getElementById('ssil-suggest-wrap');
                    if(suggestWrap) suggestWrap.style.display = 'none';
                    var suggestBtn = document.querySelector('.yoast-btn-suggest');
                    if(suggestBtn) suggestBtn.style.display = 'inline-block';
                    // Optioneel: URL-param verwijderen
                    if(window.history.replaceState) {
                        var url = new URL(window.location.href);
                        url.searchParams.delete('ssil_action');
                        window.history.replaceState({}, document.title, url.toString());
                    }
                });
            }
        });
        </script>
        <?php endif; ?>

        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="yoast-form yoast-keywords-form yoast-mb-4">
            <input type="hidden" name="action" value="ssil_add_link">
            <?php wp_nonce_field('ssil_add_link_nonce'); ?>
            <div class="yoast-flex yoast-keywords-row yoast-gap-2">
                <input type="text" name="new_word" placeholder="Woord" required class="yoast-input yoast-input-text" autocomplete="off">
                <input type="url" name="new_url" placeholder="https://voorbeeld.nl" required class="yoast-input yoast-input-url" autocomplete="off">
                <label><input type="checkbox" name="nofollow" class="yoast-checkbox"> nofollow</label>
                <label><input type="checkbox" name="target_blank" class="yoast-checkbox" checked> _blank</label>
                <input type="number" name="priority" placeholder="Prioriteit" min="0" max="100" value="0" class="yoast-input yoast-input-priority" style="width:80px;">
                <button type="submit" class="yoast-button-primary yoast-submit-btn">
                    <span class="ssil-btn-text">Toevoegen</span>
                    <span class="ssil-btn-spinner" style="display:none;vertical-align:middle;margin-left:8px;">
                        <span class="yoast-spinner"></span>
                    </span>
                </button>
            </div>
        </form>

        <table class="yoast-table widefat yoast-mb-4">
            <thead>
                <tr>
                    <th class="yoast-label">Woord</th>
                    <th class="yoast-label">URL</th>
                    <th class="yoast-label">nofollow</th>
                    <th class="yoast-label">_blank</th>
                    <th class="yoast-label">Prioriteit</th>
                    <th class="yoast-label">Actie</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $links = get_option('ssil_links', []);

                // Filter links op zoekterm, indien aanwezig
                if (!empty($_GET['ssil_search'])) {
                    $search = strtolower(sanitize_text_field($_GET['ssil_search']));
                    $links = array_filter($links, function($k) use ($search) {
                        return strpos(strtolower($k), $search) !== false;
                    }, ARRAY_FILTER_USE_KEY);
                }

                // Sorteer op prioriteit (hoogste eerst)
                if (is_array($links)) {
                    uasort($links, function($a, $b) {
                        $pa = is_array($a) ? ($a['priority'] ?? 0) : 0;
                        $pb = is_array($b) ? ($b['priority'] ?? 0) : 0;
                        return $pb <=> $pa;
                    });
                }

                if ($links && is_array($links)):
                    foreach ($links as $word => $info):
                        $url          = is_array($info) ? ($info['url'] ?? '') : $info;
                        $nofollow     = is_array($info) ? (!empty($info['nofollow']) ? 'nofollow' : '') : '';
                        $target_blank = is_array($info) ? (!empty($info['target_blank']) ? '_blank' : '') : '';
                        $priority     = is_array($info) ? intval($info['priority'] ?? 0) : 0;
                        ?>
                        <tr>
                            <td><?php echo esc_html($word); ?></td>
                            <td>
                                <?php if ($url): ?>
                                    <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener" class="yoast-link"><?php echo esc_html($url); ?></a>
                                <?php else: ?>
                                    <span class="yoast-text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo $nofollow ? '<span class="yoast-badge yoast-badge-warning">nofollow</span>' : '-'; ?></td>
                            <td><?php echo $target_blank ? '<span class="yoast-badge yoast-badge-info">_blank</span>' : '-'; ?></td>
                            <td><?php echo $priority; ?></td>
                            <td>
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="yoast-form yoast-delete-form" onsubmit="return confirm('Weet je zeker dat je deze koppeling wilt verwijderen?');">
                                    <input type="hidden" name="action" value="ssil_delete_link">
                                    <input type="hidden" name="delete_link" value="<?php echo esc_attr($word); ?>">
                                    <?php wp_nonce_field('ssil_delete_link_' . $word); ?>
                                    <button type="submit" class="yoast-button-primary yoast-btn-delete">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr>
                        <td colspan="6" class="yoast-text-muted">Nog geen koppelingen.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}