<?php
if (!defined('ABSPATH')) exit;

function ssil_render_settings_page() {
    $ssil_enabled = get_option('ssil_enabled', '1');
    $ssil_max_keywords = get_option('ssil_max_keywords', 5);
    $ssil_exclude_ids = get_option('ssil_exclude_ids', '');
    $ssil_exclude_classes = get_option('ssil_exclude_classes', '');
    $ssil_blacklist_keywords = get_option('ssil_blacklist_keywords', '');
    $ssil_post_types = get_option('ssil_post_types', ['post', 'page']);
    $ssil_taxonomies = get_option('ssil_taxonomies', ['category', 'post_tag', 'product_cat']);

    ?>
    <div class="yoast-card yoast-p-4">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>
        <form class="yoast-form ssil-instellingen-form" method="post" action="options.php" autocomplete="off">
            <?php settings_fields('ssil_settings_group'); ?>
            <?php do_settings_sections('ssil_settings_group'); ?>
            <table class="yoast-table yoast-mb-4 yoast-settings-table">
                <tr>
                    <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_enabled">Activeer interne links</label></th>
                    <td>
                        <input type="checkbox" id="ssil_enabled" name="ssil_enabled" class="yoast-checkbox" value="1" <?php checked($ssil_enabled, '1'); ?> />
                        <span class="yoast-text-muted">Schakel automatische interne linking in of uit.</span>
                    </td>
                </tr>
                <tr>
                    <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_max_keywords">Max keywords per pagina</label></th>
                    <td>
                        <input type="number" id="ssil_max_keywords" name="ssil_max_keywords" class="yoast-input" value="<?php echo esc_attr($ssil_max_keywords); ?>" min="1" required style="width:100%;" />
                    </td>
                </tr>
                <tr>
                    <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_exclude_ids">Uitsluiten (post/page IDs, komma-gescheiden)</label></th>
                    <td>
                        <input type="text" id="ssil_exclude_ids" name="ssil_exclude_ids" class="yoast-input" value="<?php echo esc_attr($ssil_exclude_ids); ?>" placeholder="Bijv. 10,12,15" style="width:100%;" />
                    </td>
                </tr>
                <tr>
                    <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_exclude_classes">Uitsluiten CSS-klassen (komma-gescheiden)</label></th>
                    <td>
                        <input type="text" id="ssil_exclude_classes" name="ssil_exclude_classes" class="yoast-input" value="<?php echo esc_attr($ssil_exclude_classes); ?>" placeholder="Bijv. .no-link,.footer-link" style="width:100%;" />
                    </td>
                </tr>
                <tr>
                    <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_blacklist_keywords">Blacklist Keywords (komma-gescheiden)</label></th>
                    <td>
                        <input type="text" id="ssil_blacklist_keywords" name="ssil_blacklist_keywords" class="yoast-input" value="<?php echo esc_attr($ssil_blacklist_keywords); ?>" placeholder="Bijv. kopen,gratis,download" style="width:100%;" />
                    </td>
                </tr>
                <tr>
                    <th class="yoast-font-bold yoast-label" scope="row">Ondersteunde post types</th>
                    <td>
                        <?php
                        $all_types = get_post_types(['public' => true], 'objects');
                        foreach ($all_types as $type) {
                            ?>
                            <label class="yoast-checkbox-label yoast-mr-2">
                                <input type="checkbox" class="yoast-checkbox" name="ssil_post_types[]" value="<?php echo esc_attr($type->name); ?>" <?php if (is_array($ssil_post_types) && in_array($type->name, $ssil_post_types)) echo 'checked'; ?> />
                                <?php echo esc_html($type->labels->singular_name); ?>
                            </label>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="yoast-font-bold yoast-label" scope="row">Ondersteunde taxonomieën</th>
                    <td>
                        <?php
                        $all_taxonomies = get_taxonomies(['public' => true], 'objects');
                        foreach ($all_taxonomies as $tax) {
                            ?>
                            <label class="yoast-checkbox-label yoast-mr-2">
                                <input type="checkbox" class="yoast-checkbox" name="ssil_taxonomies[]" value="<?php echo esc_attr($tax->name); ?>" <?php if (is_array($ssil_taxonomies) && in_array($tax->name, $ssil_taxonomies)) echo 'checked'; ?> />
                                <?php echo esc_html($tax->labels->singular_name); ?>
                                <span class="yoast-text-muted" style="font-size: 0.85em;">(categorie/taxonomie)</span>
                            </label>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <div class="ssil-settings-btn-row">
                <button type="submit" class="yoast-button-primary ssil-settings-save-btn">
                    <span class="ssil-btn-text">Opslaan</span>
                    <span class="ssil-btn-spinner" style="display:none;vertical-align:middle;margin-left:8px;">
                        <span class="yoast-spinner"></span>
                    </span>
                </button>
            </div>
        </form>
    </div>
    <?php
}
?>