<?php
if (!defined('ABSPATH')) exit;

function ssil_keywords_page() {
    ?>
    <div class="ssil-wrap">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>

        <!-- Suggesties + zoekveld flexbox -->
        <div class="ssil-keywords-toolbar">
            <a href="<?php echo admin_url('admin.php?page=ssil_keywords&ssil_action=suggest_keywords'); ?>"
               class="ssil-btn ssil-btn-primary ssil-btn-suggest"
               style="<?php echo (isset($_GET['ssil_action']) && $_GET['ssil_action'] === 'suggest_keywords') ? 'display:none;' : ''; ?>">
               Suggesties tonen
            </a>
            <form method="get" class="ssil-keywords-searchform">
                <input type="hidden" name="page" value="ssil_keywords" />
                <input type="text" name="ssil_search"
                       value="<?php echo isset($_GET['ssil_search']) ? esc_attr($_GET['ssil_search']) : ''; ?>"
                       placeholder="Zoek keyword..." class="ssil-input ssil-search-input" />
                <button type="submit" class="ssil-btn ssil-btn-primary ssil-btn-search">Zoeken</button>
            </form>
        </div>

        <?php if (isset($_GET['ssil_action']) && $_GET['ssil_action'] === 'suggest_keywords') : ?>
        <div id="ssil-suggest-wrap" style="margin-bottom:20px;">
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
                    // Verberg suggesties en laat de suggestieknop weer zien
                    var suggestWrap = document.getElementById('ssil-suggest-wrap');
                    if(suggestWrap) suggestWrap.style.display = 'none';
                    var suggestBtn = document.querySelector('.ssil-btn-suggest');
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

        <!-- Formulier om nieuwe keyword-url toe te voegen -->
        <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="ssil-form ssil-keywords-form">
            <input type="hidden" name="action" value="ssil_add_link">
            <?php wp_nonce_field('ssil_add_link_nonce'); ?>
            <div class="ssil-keywords-row">
                <input type="text" name="new_word" placeholder="Woord" required class="ssil-input ssil-input-text">
                <input type="url" name="new_url" placeholder="https://voorbeeld.nl" required class="ssil-input ssil-input-url">
                <label><input type="checkbox" name="nofollow"> nofollow</label>
                <label><input type="checkbox" name="target_blank" checked> _blank</label>
                <input type="number" name="priority" placeholder="Prioriteit" min="0" max="100" value="0" style="width: 80px;">
                <button type="submit" class="ssil-btn ssil-btn-primary ssil-submit-btn">Toevoegen</button>
            </div>
        </form>

        <table class="ssil-table widefat">
            <thead>
                <tr>
                    <th class="ssil-th">Woord</th>
                    <th class="ssil-th">URL</th>
                    <th class="ssil-th">nofollow</th>
                    <th class="ssil-th">_blank</th>
                    <th class="ssil-th">Prioriteit</th>
                    <th class="ssil-th">Actie</th>
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
                        <tr class="ssil-tr">
                            <td class="ssil-td"><?php echo esc_html($word); ?></td>
                            <td class="ssil-td">
                                <a href="<?php echo esc_url($url); ?>" target="_blank" class="ssil-link">
                                    <?php echo esc_html($url); ?>
                                </a>
                            </td>
                            <td class="ssil-td"><?php echo $nofollow; ?></td>
                            <td class="ssil-td"><?php echo $target_blank; ?></td>
                            <td class="ssil-td"><?php echo $priority; ?></td>
                            <td class="ssil-td">
                                <form method="post" action="<?php echo admin_url('admin-post.php'); ?>" class="ssil-delete-form">
                                    <input type="hidden" name="action" value="ssil_delete_link">
                                    <input type="hidden" name="delete_link" value="<?php echo esc_attr($word); ?>">
                                    <?php wp_nonce_field('ssil_delete_link_' . $word); ?>
                                    <button type="submit" class="ssil-btn ssil-btn-delete" onclick="return confirm('Weet je zeker dat je deze koppeling wilt verwijderen?');">Verwijderen</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach;
                else: ?>
                    <tr class="ssil-tr">
                        <td class="ssil-td" colspan="6">Nog geen koppelingen.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
}
?>