<?php
declare(strict_types=1);

namespace Webactueel\SSIL;

defined('ABSPATH') || exit;

// Haal de laatste 100 logs op via namespaced functie
$logs = function_exists(__NAMESPACE__ . '\\get_logs') ? get_logs(100) : (function_exists('ssil_get_logs') ? ssil_get_logs(100) : []);

?>
<div class="yoast-card yoast-p-4">
    <?php include SSIL_PATH . 'admin/tabs.php'; ?>
    <table class="yoast-table yoast-mb-4 widefat" id="ssil-logs-table">
        <thead>
            <tr>
                <th class="yoast-label">Keyword</th>
                <th class="yoast-label">URL</th>
                <th class="yoast-label">Datum &amp; tijd</th>
                <th class="yoast-label">Pagina ID</th>
                <th class="yoast-label">Paginatitel</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($logs)): foreach($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->keyword ?? ''); ?></td>
                    <td>
                        <?php if (!empty($log->url)): ?>
                            <a href="<?php echo esc_url($log->url); ?>" target="_blank" rel="noopener" class="yoast-link">
                                <?php echo esc_html($log->url); ?>
                            </a>
                        <?php else: ?>
                            <span class="yoast-text-muted">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php
                        if (!empty($log->deleted_at)) {
                            // Zet UTC om naar lokale tijdzone van WordPress
                            $local_time = get_date_from_gmt($log->deleted_at, 'd-m-Y H:i');
                            echo esc_html($local_time);
                        } else {
                            echo '<span class="yoast-text-muted">-</span>';
                        }
                        ?>
                    </td>
                    <td>
                        <?php echo !empty($log->page_id) ? esc_html((string)$log->page_id) : '<span class="yoast-text-muted">-</span>'; ?>
                    </td>
                    <td>
                        <?php echo !empty($log->page_title) ? esc_html($log->page_title) : '<span class="yoast-text-muted">-</span>'; ?>
                    </td>
                </tr>
            <?php endforeach; else: ?>
                <tr>
                    <td colspan="5" class="yoast-text-muted">Nog geen logs gevonden.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>