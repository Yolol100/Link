<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

if (!function_exists(__NAMESPACE__ . '\\keywords_page')) {

    function keywords_page(): void
    {
        require_manage_options();

        $edit_keyword = null;
        if (isset($_GET['edit'])) {
            $edit_keyword = sanitize_keyword((string) wp_unslash($_GET['edit']));
        }

        $action = isset($_GET['ssil_action']) ? sanitize_key((string) wp_unslash($_GET['ssil_action'])) : '';
        $search = isset($_GET['ssil_search']) ? sanitize_text_field((string) wp_unslash($_GET['ssil_search'])) : '';
        $show_suggestions = ($action === 'suggest_keywords');
        $success = isset($_GET['ssil_success']) ? sanitize_key((string) wp_unslash($_GET['ssil_success'])) : '';
        $error = isset($_GET['ssil_error']) ? sanitize_key((string) wp_unslash($_GET['ssil_error'])) : '';
        ?>
        <div class="yoast-card yoast-p-4">
            <?php include SSIL_PATH . 'admin/tabs.php'; ?>

            <?php if ($success !== '') : ?>
                <div class="notice notice-success"><p><?php esc_html_e('Actie succesvol uitgevoerd.', TEXT_DOMAIN); ?></p></div>
            <?php elseif ($error !== '') : ?>
                <div class="notice notice-error"><p><?php esc_html_e('Actie mislukt. Controleer keyword, URL en rechten.', TEXT_DOMAIN); ?></p></div>
            <?php endif; ?>

            <div class="yoast-flex yoast-justify-between yoast-mb-4 yoast-keywords-toolbar">
                <?php if (!$show_suggestions) : ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=ssil_keywords&ssil_action=suggest_keywords')); ?>"
                       class="yoast-button-primary yoast-mr-2 yoast-btn-suggest">
                        <?php esc_html_e('Suggesties tonen', TEXT_DOMAIN); ?>
                    </a>
                <?php endif; ?>

                <form method="get" class="yoast-form yoast-keywords-searchform">
                    <input type="hidden" name="page" value="ssil_keywords" />
                    <label for="ssil_search" class="screen-reader-text"><?php esc_html_e('Zoek keyword', TEXT_DOMAIN); ?></label>
                    <input type="search" id="ssil_search" name="ssil_search"
                           value="<?php echo esc_attr($search); ?>"
                           placeholder="<?php echo esc_attr__('Zoek keyword...', TEXT_DOMAIN); ?>" class="yoast-input yoast-search-input" />
                    <button type="submit" class="yoast-button-primary yoast-btn-search"><?php esc_html_e('Zoeken', TEXT_DOMAIN); ?></button>
                </form>
            </div>

            <?php if ($show_suggestions) : ?>
                <div id="ssil-suggest-wrap" class="yoast-mb-4">
                    <?php show_suggested_keywords(); ?>
                </div>
            <?php endif; ?>

            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="yoast-form yoast-keywords-form yoast-mb-4">
                <input type="hidden" name="action" value="ssil_add_link">
                <?php wp_nonce_field('ssil_add_link_nonce'); ?>
                <div class="yoast-flex yoast-keywords-row yoast-gap-2">
                    <label for="ssil_new_word" class="screen-reader-text"><?php esc_html_e('Keyword', TEXT_DOMAIN); ?></label>
                    <input id="ssil_new_word" type="text" name="new_word" placeholder="<?php echo esc_attr__('Woord', TEXT_DOMAIN); ?>" required class="yoast-input yoast-input-text" autocomplete="off" maxlength="190">
                    <label for="ssil_new_url" class="screen-reader-text"><?php esc_html_e('URL', TEXT_DOMAIN); ?></label>
                    <input id="ssil_new_url" type="url" name="new_url" placeholder="https://voorbeeld.nl" required class="yoast-input yoast-input-url" autocomplete="off">
                    <label><input type="checkbox" name="nofollow" class="yoast-checkbox"> <?php esc_html_e('nofollow', TEXT_DOMAIN); ?></label>
                    <label><input type="checkbox" name="target_blank" class="yoast-checkbox" checked> <?php esc_html_e('_blank', TEXT_DOMAIN); ?></label>
                    <button type="submit" class="yoast-button-primary yoast-submit-btn"><?php esc_html_e('Toevoegen', TEXT_DOMAIN); ?></button>
                </div>
            </form>

            <table id="ssil-keywords-table" class="yoast-table widefat yoast-mb-4">
                <thead>
                    <tr>
                        <th class="yoast-label"><span class="screen-reader-text"><?php esc_html_e('Selecteren', TEXT_DOMAIN); ?></span></th>
                        <th class="yoast-label"><?php esc_html_e('Woord', TEXT_DOMAIN); ?></th>
                        <th class="yoast-label"><?php esc_html_e('URL', TEXT_DOMAIN); ?></th>
                        <th class="yoast-label"><?php esc_html_e('nofollow', TEXT_DOMAIN); ?></th>
                        <th class="yoast-label"><?php esc_html_e('_blank', TEXT_DOMAIN); ?></th>
                        <th class="yoast-label"><?php esc_html_e('Prioriteit', TEXT_DOMAIN); ?></th>
                        <th class="yoast-label"><?php esc_html_e('Actie', TEXT_DOMAIN); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $links = get_links();

                    if ($search !== '') {
                        $search_lc = mb_strtolower($search);
                        $links = array_filter($links, static function ($info, $keyword) use ($search_lc): bool {
                            return str_contains(mb_strtolower((string) $keyword), $search_lc)
                                || (is_array($info) && str_contains(mb_strtolower((string) ($info['url'] ?? '')), $search_lc));
                        }, ARRAY_FILTER_USE_BOTH);
                    }

                    if ($links) {
                        ksort($links, SORT_NATURAL | SORT_FLAG_CASE);
                    }

                    if ($links) :
                        foreach ($links as $word => $info) :
                            $url = (string) ($info['url'] ?? '');
                            $nofollow = !empty($info['nofollow']);
                            $target_blank = !empty($info['target_blank']);
                            $priority = (int) ($info['priority'] ?? 0);
                            $is_editing = ($edit_keyword === (string) $word);

                            if ($is_editing) : ?>
                                <tr class="ssil-row-edit">
                                    <td></td>
                                    <td colspan="6">
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="yoast-form ssil-inline-edit-form">
                                            <input type="hidden" name="action" value="ssil_edit_link">
                                            <input type="hidden" name="old_word" value="<?php echo esc_attr($word); ?>">
                                            <?php wp_nonce_field('ssil_edit_link_nonce'); ?>
                                            <label>
                                                <?php esc_html_e('Woord', TEXT_DOMAIN); ?>
                                                <input type="text" name="edit_word" value="<?php echo esc_attr($word); ?>" class="yoast-input" required maxlength="190">
                                            </label>
                                            <label>
                                                <?php esc_html_e('URL', TEXT_DOMAIN); ?>
                                                <input type="url" name="edit_url" value="<?php echo esc_attr($url); ?>" class="yoast-input" required>
                                            </label>
                                            <label><input type="checkbox" name="edit_nofollow" class="yoast-checkbox" value="1" <?php checked($nofollow); ?>> <?php esc_html_e('nofollow', TEXT_DOMAIN); ?></label>
                                            <label><input type="checkbox" name="edit_target_blank" class="yoast-checkbox" value="1" <?php checked($target_blank); ?>> <?php esc_html_e('_blank', TEXT_DOMAIN); ?></label>
                                            <label>
                                                <?php esc_html_e('Prioriteit', TEXT_DOMAIN); ?>
                                                <input type="number" name="edit_priority" value="<?php echo esc_attr((string) $priority); ?>" class="yoast-input" style="width:90px;">
                                            </label>
                                            <button type="submit" class="yoast-button-primary"><?php esc_html_e('Opslaan', TEXT_DOMAIN); ?></button>
                                            <a href="<?php echo esc_url(remove_query_arg('edit')); ?>" class="yoast-btn-cancel"><?php esc_html_e('Annuleren', TEXT_DOMAIN); ?></a>
                                        </form>
                                    </td>
                                </tr>
                            <?php else : ?>
                                <tr>
                                    <td><input type="checkbox" class="ssil-bulk-checkbox" data-keyword="<?php echo esc_attr($word); ?>"></td>
                                    <td><?php echo esc_html($word); ?></td>
                                    <td>
                                        <?php if ($url !== '') : ?>
                                            <a href="<?php echo esc_url($url); ?>" target="_blank" rel="noopener noreferrer" class="yoast-link"><?php echo esc_html($url); ?></a>
                                        <?php else : ?>
                                            <span class="yoast-text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo $nofollow ? '<span class="yoast-badge yoast-badge-warning">nofollow</span>' : '-'; ?></td>
                                    <td><?php echo $target_blank ? '<span class="yoast-badge yoast-badge-info">_blank</span>' : '-'; ?></td>
                                    <td><?php echo esc_html((string) $priority); ?></td>
                                    <td>
                                        <a href="<?php echo esc_url(add_query_arg('edit', rawurlencode((string) $word))); ?>" class="yoast-btn-edit"><?php esc_html_e('Bewerken', TEXT_DOMAIN); ?></a>
                                        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="yoast-form yoast-delete-form" style="display:inline-block" onsubmit="return confirm('<?php echo esc_js(__('Weet je zeker dat je deze koppeling wilt verwijderen?', TEXT_DOMAIN)); ?>');">
                                            <input type="hidden" name="action" value="ssil_delete_link">
                                            <input type="hidden" name="delete_link" value="<?php echo esc_attr($word); ?>">
                                            <?php wp_nonce_field('ssil_delete_link_' . $word); ?>
                                            <button type="submit" class="yoast-button-primary yoast-btn-delete"><?php esc_html_e('Verwijderen', TEXT_DOMAIN); ?></button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endif;
                        endforeach;
                    else : ?>
                        <tr>
                            <td colspan="7" class="yoast-text-muted"><?php esc_html_e('Nog geen koppelingen.', TEXT_DOMAIN); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php
    }
}
