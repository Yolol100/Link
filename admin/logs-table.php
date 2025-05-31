<?php
if (!defined('ABSPATH')) exit;
$logs = ssil_get_logs(100); // laatste 100 logs
?>
<div class="ssil-wrap">
    <?php include SSIL_PATH . 'admin/tabs.php'; ?>
    <table class="ssil-table widefat" id="ssil-logs-table">
        <thead>
            <tr>
                <th>Keyword</th>
                <th>URL</th>
                <th>Datum & tijd</th>
                <th>Pagina ID</th>
                <th>Paginatitel</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($logs): foreach($logs as $log): ?>
                <tr>
                    <td><?php echo esc_html($log->keyword); ?></td>
                    <td><a href="<?php echo esc_url($log->url); ?>" target="_blank"><?php echo esc_html($log->url); ?></a></td>
                    <td><?php echo date('d-m-Y H:i', strtotime($log->deleted_at)); ?></td>
                    <td><?php echo esc_html($log->page_id ? $log->page_id : '-'); ?></td>
                    <td><?php echo esc_html($log->page_title ? $log->page_title : '-'); ?></td>
                </tr>
            <?php endforeach; else: ?>
                <tr><td colspan="5">Nog geen logs gevonden.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>