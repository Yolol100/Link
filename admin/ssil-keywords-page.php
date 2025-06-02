<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

if (!function_exists(__NAMESPACE__ . '\\keywords_page')) {

    function keywords_page(): void
    {
        $edit_keyword = null;
        if (isset($_GET['edit'])) {
            $edit_keyword = sanitize_text_field((string)$_GET['edit']);
        } elseif (isset($_POST['edit_keyword'])) {
            $edit_keyword = sanitize_text_field((string)$_POST['edit_keyword']);
        }
        ?>
        <div class="yoast-card yoast-p-4">
            <?php include SSIL_PATH . 'admin/tabs.php'; ?>

            <div class="yoast-flex yoast-justify-between yoast-mb-4 yoast-keywords-toolbar">
                <a href="<?php echo esc_url(admin_url('admin.php?page=ssil_keywords&ssil_action=suggest_keywords')); ?>"
                   class="yoast-button-primary yoast-mr-2 yoast-btn-suggest"
                   style="<?php echo (isset($_GET['ssil_action']) && $_GET['ssil_action'] === 'suggest_keywords') ? 'display:none;' : ''; ?>">
                   Suggesties tonen
                </a>
                <form method="get" class="yoast-form yoast-keywords-searchform">
                    <input type="hidden" name="page" value="ssil_keywords" />
                    <input type="text" name="ssil_search"
                           value="<?php echo isset($_GET['ssil_search']) ? esc_attr((string)$_GET['ssil_search']) : ''; ?>"
                           placeholder="Zoek keyword..." class="yoast-input yoast-search-input" />
                    <button type="submit" class="yoast-button-primary yoast-btn-search">Zoeken</button>
                </form>
            </div>

            <?php if (isset($_GET['ssil_action']) && $_GET['ssil_action'] === 'suggest_keywords') : ?>
            <div id="ssil-suggest-wrap" class="yoast-mb-4">
                <?php
                    require_once SSIL_PATH . 'includes/ssil-suggest.php';
                    if (function_exists(__NAMESPACE__ . '\\show_suggested_keywords')) {
                        show_suggested_keywords();
                    } elseif (function_exists('ssil_show_suggested_keywords')) {
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

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="yoast-form yoast-keywords-form yoast-mb-4">
                <input type="hidden" name="action" value="ssil_add_link">
                <?php wp_nonce_field('ssil_add_link_nonce'); ?>
                <div class="yoast-flex yoast-keywords-row yoast-gap-2">
                    <input type="text" name="new_word" placeholder="Woord" required class="yoast-input yoast-input-text" autocomplete="off">
                    <input type="url" name="new_url" placeholder="https://voorbeeld.nl" required class="yoast-input yoast-input-url" autocomplete="off">
                    <label><input type="checkbox" name="nofollow" class="yoast-checkbox"> nofollow</label>
                    <label><input type="checkbox" name="target_blank" class="yoast-checkbox" checked> _blank</label>
                    <button type="submit" class="yoast-button-primary yoast-submit-btn">
                        <span class="ssil-btn-text">Toevoegen</span>
                        <span class="ssil-btn-spinner" style="display:none;vertical-align:middle;margin-left:8px;">
                            <span class="yoast-spinner"></span>
                        </span>
                    </button>
                </div>
            </form>

            <table id="ssil-keywords-table" class="yoast-table widefat yoast-mb-4">
                <thead>
                    <tr>
                        <th class="yoast-label"><input type="checkbox" id="ssil-select-all"></th>
                        <th class="yoast-label">Woord</th>
                        <th class="yoast-label">URL</th>
                        <th class="yoast-label">nofollow</th>
                        <th class="yoast-label">_blank</th>
                        <th class="yoast-label">Actie</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $links = get_option('ssil_links', []);

                    if (!empty($_GET['ssil_search'])) {
                        $search = mb_strtolower(sanitize_text_field((string)$_GET['ssil_search']));
                        $links = array_filter($links, static function($k) use ($search) {
                            return strpos(mb_strtolower($k), $search) !== false;
                        }, ARRAY_FILTER_USE_KEY);
                    }

                    if (is_array($links)) {
                        ksort($links);
                    }

                    if ($links && is_array($links)):
                        foreach ($links as $word => $info):
                            $url          = is_array($info) ? ($info['url'] ?? '') : $info;
                            $nofollow     = is_array($info) ? (!empty($info['nofollow']) ? 'nofollow' : '') : '';
                            $target_blank = is_array($info) ? (!empty($info['target_blank']) ? '_blank' : '') : '';
                            $is_editing   = ($edit_keyword === $word);

                            if ($is_editing): ?>
                            <tr class="ssil-row-edit">
                                <td></td>
                                <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="yoast-form ssil-inline-edit-form">
                                <td>
                                    <input type="text" name="edit_word" value="<?php echo esc_attr($word); ?>" class="yoast-input" required style="width:110px;">
                                    <input type="hidden" name="old_word" value="<?php echo esc_attr($word); ?>">
                                </td>
                                <td>
                                    <input type="url" name="edit_url" value="<?php echo esc_attr($url); ?>" class="yoast-input" style="width:210px;" required>
                                </td>
                                <td>
                                    <input type="checkbox" name="edit_nofollow" class="yoast-checkbox" value="1" <?php checked($nofollow, 'nofollow'); ?>>
                                </td>
                                <td>
                                    <input type="checkbox" name="edit_target_blank" class="yoast-checkbox" value="1" <?php checked($target_blank, '_blank'); ?>>
                                </td>
                                <td>
                                    <input type="hidden" name="action" value="ssil_edit_link">
                                    <?php wp_nonce_field('ssil_edit_link_nonce'); ?>
                                    <button type="submit" class="yoast-button-primary" title="Opslaan">✔️</button>
                                    <a href="<?php echo esc_url(remove_query_arg('edit')); ?>" class="yoast-btn-cancel" title="Annuleren" style="margin-left:5px;">✖️</a>
                                </td>
                                </form>
                            </tr>
                            <?php else: ?>
                            <tr>
                                <td><input type="checkbox" class="ssil-bulk-checkbox" data-keyword="<?php echo esc_attr($word); ?>"></td>
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
                                <td>
                                    <a href="<?php echo esc_url(add_query_arg('edit', rawurlencode($word))); ?>" class="yoast-btn-edit" title="Bewerken">Bewerken</a>
                                    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="yoast-form yoast-delete-form" style="display:inline-block" onsubmit="return confirm('Weet je zeker dat je deze koppeling wilt verwijderen?');">
                                        <input type="hidden" name="action" value="ssil_delete_link">
                                        <input type="hidden" name="delete_link" value="<?php echo esc_attr($word); ?>">
                                        <?php wp_nonce_field('ssil_delete_link_' . $word); ?>
                                        <button type="submit" class="yoast-button-primary yoast-btn-delete">Verwijderen</button>
                                    </form>
                                </td>
                            </tr>
                            <?php
                            endif;
                        endforeach;
                    else: ?>
                        <tr>
                            <td colspan="6" class="yoast-text-muted">Nog geen koppelingen.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Bulk acties -->
            <div class="yoast-flex yoast-justify-end yoast-gap-2">
                <button id="ssil-bulk-delete" class="yoast-button-primary yoast-btn-delete" disabled style="display:none;">Geselecteerde verwijderen</button>
            </div>
        </div>
        <?php
    }

}