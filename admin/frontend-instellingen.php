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
    <div class="ssil-wrap">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>
        <form class="ssil-form" method="post" action="options.php">
            <?php settings_fields('ssil_settings_group'); ?>
            <?php do_settings_sections('ssil_settings_group'); ?>
            <table class="ssil-table">
                <tr>
                    <th class="ssil-label" scope="row"><label for="ssil_enabled">Activeer interne links</label></th>
                    <td>
                        <input type="checkbox" id="ssil_enabled" name="ssil_enabled" value="1" <?php checked($ssil_enabled, '1'); ?> />
                        <span class="ssil-description">Schakel automatische interne linking in of uit.</span>
                    </td>
                </tr>
                <tr>
                    <th class="ssil-label" scope="row"><label for="ssil_max_keywords">Max keywords per pagina</label></th>
                    <td>
                        <input type="number" id="ssil_max_keywords" name="ssil_max_keywords" class="ssil-input" value="<?php echo esc_attr($ssil_max_keywords); ?>" min="1" />
                    </td>
                </tr>
                <tr>
                    <th class="ssil-label" scope="row"><label for="ssil_exclude_ids">Uitsluiten (post/page IDs, komma-gescheiden)</label></th>
                    <td>
                        <input type="text" id="ssil_exclude_ids" name="ssil_exclude_ids" class="ssil-input" value="<?php echo esc_attr($ssil_exclude_ids); ?>" />
                    </td>
                </tr>
                <tr>
                    <th class="ssil-label" scope="row"><label for="ssil_exclude_classes">Uitsluiten CSS-klassen (komma-gescheiden)</label></th>
                    <td>
                        <input type="text" id="ssil_exclude_classes" name="ssil_exclude_classes" class="ssil-input" value="<?php echo esc_attr($ssil_exclude_classes); ?>" />
                    </td>
                </tr>
                <tr>
                    <th class="ssil-label" scope="row"><label for="ssil_blacklist_keywords">Blacklist Keywords (komma-gescheiden)</label></th>
                    <td>
                        <input type="text" id="ssil_blacklist_keywords" name="ssil_blacklist_keywords" class="ssil-input" value="<?php echo esc_attr($ssil_blacklist_keywords); ?>" />
                    </td>
                </tr>
                <tr>
                    <th class="ssil-label" scope="row">Ondersteunde post types</th>
                    <td>
                        <?php
                        $all_types = get_post_types(['public' => true], 'objects');
                        foreach ($all_types as $type) {
                            ?>
                            <label class="ssil-checkbox-label">
                                <input type="checkbox" class="ssil-checkbox" name="ssil_post_types[]" value="<?php echo esc_attr($type->name); ?>" <?php if (is_array($ssil_post_types) && in_array($type->name, $ssil_post_types)) echo 'checked'; ?> />
                                <?php echo esc_html($type->labels->singular_name); ?>
                            </label><br>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <th class="ssil-label" scope="row">Ondersteunde taxonomieën</th>
                    <td>
                        <?php
                        $all_taxonomies = get_taxonomies(['public' => true], 'objects');
                        foreach ($all_taxonomies as $tax) {
                            ?>
                            <label class="ssil-checkbox-label">
                                <input type="checkbox" class="ssil-checkbox" name="ssil_taxonomies[]" value="<?php echo esc_attr($tax->name); ?>" <?php if (is_array($ssil_taxonomies) && in_array($tax->name, $ssil_taxonomies)) echo 'checked'; ?> />
                                <?php echo esc_html($tax->labels->singular_name); ?> <span style="font-size: 0.85em; color: #666;">(categorie/taxonomie)</span>
                            </label><br>
                            <?php
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <div class="ssil-submit-wrap">
                <button type="submit" class="ssil-submit-btn">Opslaan</button>
            </div>
        </form>
    </div>
    <?php
}