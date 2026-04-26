<?php
defined('ABSPATH') || exit;
?>

<div class="yoast-card yoast-p-4">
    <?php include SSIL_PATH . 'admin/tabs.php'; ?>
    <form class="yoast-form ssil-instellingen-form" method="post" autocomplete="off">
        <?php wp_nonce_field('ssil_settings_save', 'ssil_settings_nonce'); ?>
        <table class="yoast-table yoast-mb-4 yoast-settings-table">
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_enabled">Activeer interne links</label></th>
                <td>
                    <input type="checkbox" id="ssil_enabled" name="ssil_enabled" class="yoast-checkbox" value="1" <?php checked($settings['enabled'], '1'); ?> />
                    <span class="yoast-text-muted">Schakel automatische interne linking in of uit.</span>
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_max_keywords">Max keywords per pagina</label></th>
                <td>
                    <input type="number" id="ssil_max_keywords" name="ssil_max_keywords" class="yoast-input" style="width:100%;" value="<?php echo esc_attr($settings['max_keywords']); ?>" min="1" required />
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_exclude_ids">Uitsluiten (post/page IDs, komma-gescheiden)</label></th>
                <td>
                    <input type="text" id="ssil_exclude_ids" name="ssil_exclude_ids" class="yoast-input" style="width:100%;" value="<?php echo esc_attr(implode(',', (array)$settings['exclude_ids'])); ?>" placeholder="Bijv. 10,12,15" />
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_exclude_classes">Uitsluiten CSS-klassen (komma-gescheiden)</label></th>
                <td>
                    <input type="text" id="ssil_exclude_classes" name="ssil_exclude_classes" class="yoast-input" style="width:100%;" value="<?php echo esc_attr(implode(',', (array)$settings['exclude_classes'])); ?>" placeholder="Bijv. .no-link,.footer-link" />
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row"><label for="ssil_blacklist_keywords">Blacklist Keywords (komma-gescheiden)</label></th>
                <td>
                    <input type="text" id="ssil_blacklist_keywords" name="ssil_blacklist_keywords" class="yoast-input" style="width:100%;" value="<?php echo esc_attr(implode(',', (array)$settings['blacklist_keywords'])); ?>" placeholder="Bijv. kopen,gratis,download" />
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row">Ondersteunde post types</th>
                <td>
                    <?php
                    $all_types = get_post_types(['public' => true], 'objects');
                    foreach ($all_types as $type) : ?>
                        <label class="yoast-checkbox-label yoast-mr-2">
                            <input type="checkbox" class="yoast-checkbox" name="ssil_post_types[]" value="<?php echo esc_attr($type->name); ?>" <?php checked(is_array($settings['post_types']) && in_array($type->name, $settings['post_types'], true)); ?> />
                            <?php echo esc_html($type->labels->singular_name); ?>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row">Ondersteunde taxonomieën</th>
                <td>
                    <?php
                    $all_taxonomies = get_taxonomies(['public' => true], 'objects');
                    foreach ($all_taxonomies as $tax) : ?>
                        <label class="yoast-checkbox-label yoast-mr-2">
                            <input type="checkbox" class="yoast-checkbox" name="ssil_taxonomies[]" value="<?php echo esc_attr($tax->name); ?>" <?php checked(is_array($settings['taxonomies']) && in_array($tax->name, $settings['taxonomies'], true)); ?> />
                            <?php echo esc_html($tax->labels->singular_name); ?>
                            <span class="yoast-text-muted" style="font-size: 0.85em;">(categorie/taxonomie)</span>
                        </label>
                    <?php endforeach; ?>
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row">Linklocaties</th>
                <td>
                    <div class="ssil-linklocaties-options">
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_exclude_headings" value="1" <?php checked(!empty($settings['exclude_headings'])); ?> /> Geen headings linken</label>
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_exclude_quotes" value="1" <?php checked(!empty($settings['exclude_quotes'])); ?> /> Geen quotes linken</label>
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_exclude_lists" value="1" <?php checked(!empty($settings['exclude_lists'])); ?> /> Geen lijsten linken</label>
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_link_titles" value="1" <?php checked(!empty($settings['link_titles'])); ?> /> Ook titels linken</label>
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_link_widgets" value="1" <?php checked(!empty($settings['link_widgets'])); ?> /> Ook widgets linken</label>
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_link_woocommerce" value="1" <?php checked(!empty($settings['link_woocommerce'])); ?> /> WooCommerce-beschrijvingen linken</label>
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_link_acf" value="1" <?php checked(!empty($settings['link_acf'])); ?> /> ACF-velden linken</label>
                        <label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_link_elementor" value="1" <?php checked(!empty($settings['link_elementor'])); ?> /> Elementor widget-output linken</label>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="yoast-font-bold yoast-label" scope="row">Uninstall</th>
                <td><label class="yoast-checkbox-label"><input type="checkbox" class="yoast-checkbox" name="ssil_delete_data_on_uninstall" value="1" <?php checked(!empty($settings['delete_data_on_uninstall'])); ?> /> Verwijder plugindata bij uninstall</label></td>
            </tr>
        </table>
        <div class="yoast-mt-4" style="text-align: right;">
            <button type="submit" name="ssil_save_settings" class="yoast-button-primary ssil-settings-save-btn">
                <span class="ssil-btn-text">Opslaan</span>
                <span class="ssil-btn-spinner" style="display:none;vertical-align:middle;margin-left:8px;">
                    <span class="yoast-spinner"></span>
                </span>
            </button>
        </div>
    </form>
</div>
