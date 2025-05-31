<?php
if (!defined('ABSPATH')) exit;

// Huidige pagina bepalen voor actieve tab
$current = isset($_GET['page']) ? sanitize_text_field($_GET['page']) : '';

$tabs = [
    'ssil_main'     => ['title' => 'Instellingen',        'url' => admin_url('admin.php?page=ssil_main')],
    'ssil_keywords' => ['title' => 'Keywords',            'url' => admin_url('admin.php?page=ssil_keywords')],
    'ssil_logs'     => ['title' => 'Logs',                'url' => admin_url('admin.php?page=ssil_logs')],
    'ssil_report'   => ['title' => 'Rapportage',          'url' => admin_url('admin.php?page=ssil_report')],
    'ssil_bulk'     => ['title' => 'Bulk Import/Export',  'url' => admin_url('admin.php?page=ssil_bulk')],
];
?>
<nav class="nav-tab-wrapper" style="margin-bottom:32px;">
    <?php foreach ($tabs as $slug => $tab) : ?>
        <a href="<?php echo esc_url($tab['url']); ?>"
           class="nav-tab<?php echo $current === $slug ? ' nav-tab-active' : ''; ?>">
            <?php echo esc_html($tab['title']); ?>
        </a>
    <?php endforeach; ?>
</nav>