<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

/**
 * Helper: Toon waarde alleen als die niet leeg/onbekend is
 *
 * @param mixed $val
 * @return bool
 */
function ssil_is_visible($val): bool {
    return !empty($val) && strtolower(trim((string)$val)) !== 'onbekend';
}

/**
 * Render het rapportage-overzicht van SSIL.
 *
 * @param array<string, mixed> $stats
 * @return void
 */
function render_report_view(array $stats): void
{
    $ssil_links = get_option('ssil_links', []);
    ?>
    <div class="yoast-card yoast-p-4">
        <?php include SSIL_PATH . 'admin/tabs.php'; ?>

        <div class="yoast-report-actions yoast-mb-4">
            <button type="button" id="ssil-copy-report" class="yoast-button-primary yoast-btn-copy">Kopieer als tabel</button>
        </div>

        <!-- Algemene statistieken -->
        <table class="yoast-table widefat yoast-report-table yoast-mb-4" id="ssil-report-table">
            <thead>
                <tr><th class="yoast-label">Statistiek</th><th class="yoast-label">Waarde</th></tr>
            </thead>
            <tbody>
                <?php if (ssil_is_visible($stats['unique_keywords'] ?? null)) : ?>
                    <tr><td>Unieke keywords</td><td><?php echo esc_html((string)($stats['unique_keywords'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['active_keywords_count'] ?? null)) : ?>
                    <tr><td>Actieve keywords</td><td><?php echo esc_html((string)($stats['active_keywords_count'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['inactive_keywords_count'] ?? null)) : ?>
                    <tr><td>Niet-actieve keywords</td><td><?php echo esc_html((string)($stats['inactive_keywords_count'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['posts_with_links'] ?? null)) : ?>
                    <tr><td>Gelinkte posts</td><td><?php echo esc_html((string)($stats['posts_with_links'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['posts_without_links'] ?? null)) : ?>
                    <tr><td>Posts zonder interne link</td><td><?php echo esc_html((string)($stats['posts_without_links'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['total_posts'] ?? null)) : ?>
                    <tr><td>Totaal aantal posts</td><td><?php echo esc_html((string)($stats['total_posts'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['total_links_placed'] ?? null)) : ?>
                    <tr><td>Totaal aantal links geplaatst</td><td><?php echo esc_html((string)($stats['total_links_placed'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['avg_links_per_post'] ?? null)) : ?>
                    <tr><td>Gemiddeld aantal links per post</td><td><?php echo esc_html((string)($stats['avg_links_per_post'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['most_linked_keyword'] ?? null)) : ?>
                    <tr><td>Meest gelinkte keyword</td><td><?php echo esc_html((string)($stats['most_linked_keyword'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['most_linked_url'] ?? null)) : ?>
                    <tr><td>Meest gelinkte URL</td><td><span title="<?php echo esc_attr((string)($stats['most_linked_url'] ?? '')); ?>" class="yoast-text-muted"><?php echo esc_html((string)($stats['most_linked_url'] ?? '')); ?></span></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['nofollow_count'] ?? null)) : ?>
                    <tr><td>Links met nofollow</td><td><?php echo esc_html((string)($stats['nofollow_count'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['blank_count'] ?? null)) : ?>
                    <tr><td>Links met target="_blank"</td><td><?php echo esc_html((string)($stats['blank_count'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['priority_kw'] ?? null)) : ?>
                    <tr><td>Keyword met hoogste prioriteit</td><td><?php echo esc_html((string)($stats['priority_kw'] ?? '')); ?></td></tr>
                <?php endif; ?>
                <?php if (ssil_is_visible($stats['last_update'] ?? null)) : ?>
                    <tr><td>Laatst gewijzigd</td><td><?php echo esc_html((string)($stats['last_update'] ?? '')); ?></td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Posts met de meeste interne links -->
        <?php
        $show_post_links = false;
        if (!empty($stats['post_link_counts']) && is_array($stats['post_link_counts'])) {
            foreach ($stats['post_link_counts'] as $cnt) { if ($cnt > 0) { $show_post_links = true; break; } }
        }
        if ($show_post_links): ?>
            <h3 class="yoast-mb-2">Posts met de meeste interne links</h3>
            <table class="yoast-table widefat yoast-report-table yoast-mb-4">
                <thead><tr><th class="yoast-label">Post</th><th class="yoast-label">Aantal links</th></tr></thead>
                <tbody>
                    <?php
                    $top = 0;
                    arsort($stats['post_link_counts']);
                    foreach ($stats['post_link_counts'] as $post_id => $count) {
                        if ($count == 0) continue;
                        if (++$top > 10) break;
                        echo '<tr><td><a href="' . esc_url(get_edit_post_link((int)$post_id)) . '" target="_blank" rel="noopener" class="yoast-link">' . esc_html(get_the_title((int)$post_id)) . '</a></td><td>' . intval($count) . '</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Top 10 keywords -->
        <?php if (!empty($stats['keyword_usage'])): ?>
            <h3 class="yoast-mb-2">Top 10 keywords (op aantal links)</h3>
            <table class="yoast-table widefat yoast-report-table yoast-mb-4">
                <thead><tr><th class="yoast-label">Keyword</th><th class="yoast-label">Aantal links</th><th class="yoast-label">In posts</th></tr></thead>
                <tbody>
                    <?php
                    $top = 0;
                    arsort($stats['keyword_usage']);
                    foreach ($stats['keyword_usage'] as $kw => $count) {
                        if (++$top > 10) break;
                        $posts = isset($stats['keyword_post_usage'][$kw]) ? count(array_unique($stats['keyword_post_usage'][$kw])) : 0;
                        echo '<tr>
                            <td>' . esc_html($kw) . '</td>
                            <td>' . esc_html((string)$count) . '</td>
                            <td>' . esc_html((string)$posts) . '</td>
                        </tr>';
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <!-- Details per keyword -->
        <?php if (!empty($stats['keyword_usage'])): ?>
            <h3 class="yoast-mb-2">Details per keyword</h3>
            <table class="yoast-table widefat yoast-report-table yoast-mb-4">
                <thead>
                    <tr>
                        <th class="yoast-label">Keyword</th>
                        <th class="yoast-label">Aantal links</th>
                        <th class="yoast-label">Prioriteit</th>
                        <th class="yoast-label">nofollow</th>
                        <th class="yoast-label">_blank</th>
                        <th class="yoast-label">In posts</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach ($stats['keyword_usage'] as $kw => $count):
                        $info = isset($ssil_links[$kw]) ? $ssil_links[$kw] : [];
                        $prio = is_array($info) ? ($info['priority'] ?? 0) : 0;
                        $nofollow = is_array($info) && !empty($info['nofollow']) ? '<span class="yoast-badge yoast-badge-warning">ja</span>' : '-';
                        $blank = is_array($info) && !empty($info['target_blank']) ? '<span class="yoast-badge yoast-badge-info">ja</span>' : '-';
                        $posts = isset($stats['keyword_post_usage'][$kw]) ? $stats['keyword_post_usage'][$kw] : [];
                        ?>
                        <tr>
                            <td><?php echo esc_html($kw); ?></td>
                            <td><?php echo esc_html((string)$count); ?></td>
                            <td><?php echo esc_html((string)$prio); ?></td>
                            <td><?php echo $nofollow; ?></td>
                            <td><?php echo $blank; ?></td>
                            <td>
                                <?php
                                if ($posts) {
                                    $show = 0;
                                    foreach ($posts as $post_id) {
                                        if (++$show > 5) { echo '...'; break; }
                                        echo '<a href="' . esc_url(get_edit_post_link((int)$post_id)) . '" target="_blank" rel="noopener" class="yoast-link">' . esc_html(get_the_title((int)$post_id)) . '</a><br>';
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
        <?php endif; ?>

        <!-- Kwaliteitscontrole -->
        <?php
        $show_overlap = !empty($stats['overlapping_keywords']);
        $show_dead = !empty($stats['dead_links']);
        if ($show_overlap || $show_dead): ?>
            <h3 class="yoast-mb-2">Kwaliteitscontrole</h3>
            <ul class="yoast-quality-list">
                <?php if ($show_overlap): ?>
                    <li>
                        <strong>Dubbele of overlappende keywords:</strong>
                        <?php
                        foreach ($stats['overlapping_keywords'] as $pair) {
                            echo '<span class="yoast-badge yoast-badge-error yoast-mr-2">' . esc_html($pair[0] ?? '') . ' ↔ ' . esc_html($pair[1] ?? '') . '</span>';
                        }
                        ?>
                    </li>
                <?php endif; ?>
                <?php if ($show_dead): ?>
                    <li>
                        <strong>Dode links:</strong>
                        <?php
                        foreach ($stats['dead_links'] as $url) {
                            echo '<span class="yoast-badge yoast-badge-error yoast-mr-2">' . esc_html((string)$url) . '</span>';
                        }
                        ?>
                    </li>
                <?php endif; ?>
            </ul>
        <?php endif; ?>
    </div>
    <?php
}