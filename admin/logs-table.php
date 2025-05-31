<?php
if (!defined('ABSPATH')) exit;
$logs = ssil_get_logs(100); // laatste 100 logs
?>
<div class="yoast-card yoast-p-4">
    <?php include SSIL_PATH . 'admin/tabs.php'; ?>
    <table class="yoast-table yoast-mb-4 widefat" id="ssil-logs-table">
        <thead>
            <tr>
                <th class="yoast-label">Keyword</th>
                <th class="yoast-label">URL</th>
                <th class="yoast-label">Datum & tijd</th>
                <th class="yoast-label">Pagina ID</th>
                <th class="yoast-label">Paginatitel</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs): foreach($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->keyword); ?></td>
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
                        echo $log->deleted_at
                            ? esc_html(date('d-m-Y H:i', strtotime($log->deleted_at)))
                            : '<span class="yoast-text-muted">-</span>';
                        ?>
                    </td>
                    <td>
                        <?php echo $log->page_id ? esc_html($log->page_id) : '<span class="yoast-text-muted">-</span>'; ?>
                    </td>
                    <td>
                        <?php echo $log->page_title ? esc_html($log->page_title) : '<span class="yoast-text-muted">-</span>'; ?>
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