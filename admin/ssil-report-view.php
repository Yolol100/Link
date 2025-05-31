<?php
if (!defined('ABSPATH')) exit;

function ssil_render_report_view($stats) {
    ?>
    <div class="ssil-wrap">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>

        <div class="ssil-report-actions">
            <button type="button" id="ssil-copy-report" class="ssil-btn ssil-btn-primary ssil-btn-copy">Kopieer als tabel</button>
        </div>

        <table class="ssil-table widefat ssil-report-table" id="ssil-report-table">
            <thead>
                <tr><th>Statistiek</th><th>Waarde</th></tr>
            </thead>
            <tbody>
                <tr><td>Unieke keywords</td><td><?php echo esc_html($stats['unique_keywords']); ?></td></tr>
                <tr><td>Actieve keywords</td><td><?php echo esc_html($stats['active_keywords_count']); ?></td></tr>
                <tr><td>Niet-actieve keywords</td><td><?php echo esc_html($stats['inactive_keywords_count']); ?></td></tr>
                <tr><td>Gelinkte posts</td><td><?php echo esc_html($stats['posts_with_links']); ?></td></tr>
                <tr><td>Posts zonder interne link</td><td><?php echo esc_html($stats['posts_without_links']); ?></td></tr>
                <tr><td>Totaal aantal posts</td><td><?php echo esc_html($stats['total_posts']); ?></td></tr>
                <tr><td>Totaal aantal links geplaatst</td><td><?php echo esc_html($stats['total_links_placed']); ?></td></tr>
                <tr><td>Gemiddeld aantal links per post</td><td><?php echo esc_html($stats['avg_links_per_post']); ?></td></tr>
                <tr><td>Meest gelinkte keyword</td><td><?php echo esc_html($stats['most_linked_keyword']); ?></td></tr>
                <tr><td>Meest gelinkte URL</td><td><span title="<?php echo esc_attr($stats['most_linked_url']); ?>"><?php echo esc_html($stats['most_linked_url']); ?></span></td></tr>
                <tr><td>Links met nofollow</td><td><?php echo esc_html($stats['nofollow_count']); ?></td></tr>
                <tr><td>Links met target="_blank"</td><td><?php echo esc_html($stats['blank_count']); ?></td></tr>
                <tr><td>Keyword met hoogste prioriteit</td><td><?php echo esc_html($stats['priority_kw']); ?></td></tr>
                <tr><td>Laatst gewijzigd</td><td><?php echo esc_html($stats['last_update']); ?></td></tr>
            </tbody>
        </table>

        <h3>Posts met de meeste interne links</h3>
        <table class="ssil-table widefat ssil-report-table">
            <thead><tr><th>Post</th><th>Aantal links</th></tr></thead>
            <tbody>
                <?php
                $top = 0;
                arsort($stats['post_link_counts']);
                foreach ($stats['post_link_counts'] as $post_id => $count) {
                    if (++$top > 10) break;
                    if ($count == 0) continue;
                    echo '<tr><td><a href="' . get_edit_post_link($post_id) . '" target="_blank">' . get_the_title($post_id) . '</a></td><td>' . intval($count) . '</td></tr>';
                }
                if ($top == 0) echo '<tr><td colspan="2">Geen gelinkte posts.</td></tr>';
                ?>
            </tbody>
        </table>

        <h3>Top 10 keywords (op aantal links)</h3>
        <table class="ssil-table widefat ssil-report-table">
            <thead><tr><th>Keyword</th><th>Aantal links</th><th>In posts</th></tr></thead>
            <tbody>
                <?php
                $top = 0;
                arsort($stats['keyword_usage']);
                foreach ($stats['keyword_usage'] as $kw => $count) {
                    if (++$top > 10) break;
                    echo '<tr>
                        <td>' . esc_html($kw) . '</td>
                        <td>' . esc_html($count) . '</td>
                        <td>' . count(array_unique($stats['keyword_post_usage'][$kw])) . '</td>
                    </tr>';
                }
                if (empty($stats['keyword_usage'])) {
                    echo '<tr><td colspan="3">Nog geen links gevonden.</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <h3>Details per keyword</h3>
        <table class="ssil-table widefat ssil-report-table">
            <thead>
                <tr>
                    <th>Keyword</th>
                    <th>Aantal links</th>
                    <th>Prioriteit</th>
                    <th>nofollow</th>
                    <th>_blank</th>
                    <th>In posts</th>
                </tr>
            </thead>
            <tbody>
                <?php
                foreach ($stats['keyword_usage'] as $kw => $count):
                    // Haal de keyword data uit links
                    $info = isset($GLOBALS['ssil_links'][$kw]) ? $GLOBALS['ssil_links'][$kw] : [];
                    $prio = is_array($info) ? ($info['priority'] ?? 0) : 0;
                    $nofollow = is_array($info) && !empty($info['nofollow']) ? 'ja' : '';
                    $blank = is_array($info) && !empty($info['target_blank']) ? 'ja' : '';
                    $posts = isset($stats['keyword_post_usage'][$kw]) ? $stats['keyword_post_usage'][$kw] : [];
                    ?>
                    <tr>
                        <td><?php echo esc_html($kw); ?></td>
                        <td><?php echo esc_html($count); ?></td>
                        <td><?php echo esc_html($prio); ?></td>
                        <td><?php echo esc_html($nofollow); ?></td>
                        <td><?php echo esc_html($blank); ?></td>
                        <td>
                            <?php
                            if ($posts) {
                                $show = 0;
                                foreach ($posts as $post_id) {
                                    if (++$show > 5) { echo '...'; break; }
                                    echo '<a href="' . get_edit_post_link($post_id) . '" target="_blank">' . get_the_title($post_id) . '</a><br>';
                                }
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3>Kwaliteitscontrole</h3>
        <ul class="ssil-quality-list">
            <li>
                <strong>Dubbele of overlappende keywords:</strong>
                <?php
                if (!empty($stats['overlapping_keywords'])) {
                    foreach ($stats['overlapping_keywords'] as $pair) {
                        echo '<span class="ssil-badge ssil-badge-danger" style="margin-right:9px;">' . esc_html($pair[0]) . ' ↔ ' . esc_html($pair[1]) . '</span>';
                    }
                } else {
                    echo 'Geen overlappende keywords gevonden.';
                }
                ?>
            </li>
            <li>
                <strong>Dode links:</strong>
                <?php
                if (!empty($stats['dead_links'])) {
                    foreach ($stats['dead_links'] as $url) {
                        echo '<span class="ssil-badge ssil-badge-danger" style="margin-right:9px;">' . esc_html($url) . '</span>';
                    }
                } else {
                    echo 'Geen dode links gevonden (controle optioneel, standaard uit).</li>';
                }
                ?>
        </ul>
    </div>
    <?php
}
?>