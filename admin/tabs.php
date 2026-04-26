<?php
declare(strict_types=1);

defined('ABSPATH') || exit;

$current = '';
if (isset($_GET['page'])) {
    $current = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string) wp_unslash($_GET['page']));
}

$tabs = [
    'ssil_main'     => ['title' => __('Instellingen', 'super-simple-internal-links'),       'url' => admin_url('admin.php?page=ssil_main')],
    'ssil_keywords' => ['title' => __('Keywords', 'super-simple-internal-links'),           'url' => admin_url('admin.php?page=ssil_keywords')],
    'ssil_logs'     => ['title' => __('Logs', 'super-simple-internal-links'),               'url' => admin_url('admin.php?page=ssil_logs')],
    'ssil_report'   => ['title' => __('Rapportage', 'super-simple-internal-links'),         'url' => admin_url('admin.php?page=ssil_report')],
    'ssil_bulk'     => ['title' => __('Bulk Import/Export', 'super-simple-internal-links'), 'url' => admin_url('admin.php?page=ssil_bulk')],
];

if (!array_key_exists($current, $tabs)) {
    $current = '';
}
?>
<nav class="yoast-tabs yoast-mb-4" aria-label="<?php echo esc_attr__('Plugin navigatie', 'super-simple-internal-links'); ?>">
    <?php foreach ($tabs as $slug => $tab) : ?>
        <a href="<?php echo esc_url($tab['url']); ?>"
           class="yoast-tab<?php echo ($current === $slug) ? ' yoast-tab-active' : ''; ?>"
           <?php echo ($current === $slug) ? 'aria-current="page"' : ''; ?>>
            <?php echo esc_html($tab['title']); ?>
        </a>
    <?php endforeach; ?>
</nav>
