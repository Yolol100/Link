<?php
declare(strict_types=1);

defined('ABSPATH') || exit;

// Bepaal huidige pagina veilig (zelfs bij rare querystrings)
$current = '';
if (isset($_GET['page'])) {
    $current = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$_GET['page']);
}

$tabs = [
    'ssil_main'     => ['title' => 'Instellingen',        'url' => admin_url('admin.php?page=ssil_main')],
    'ssil_keywords' => ['title' => 'Keywords',            'url' => admin_url('admin.php?page=ssil_keywords')],
    'ssil_logs'     => ['title' => 'Logs',                'url' => admin_url('admin.php?page=ssil_logs')],
    'ssil_report'   => ['title' => 'Rapportage',          'url' => admin_url('admin.php?page=ssil_report')],
    'ssil_bulk'     => ['title' => 'Bulk Import/Export',  'url' => admin_url('admin.php?page=ssil_bulk')],
];

// Zet $current leeg als hij niet in tabs voorkomt (gebeurt alleen bij vreemde URL)
if (!array_key_exists($current, $tabs)) {
    $current = '';
}
?>
<nav class="yoast-tabs yoast-mb-4">
    <?php foreach ($tabs as $slug => $tab): ?>
        <a href="<?php echo esc_url($tab['url']); ?>"
           class="yoast-tab<?php echo ($current === $slug) ? ' yoast-tab-active' : ''; ?>">
            <?php echo esc_html($tab['title']); ?>
        </a>
    <?php endforeach; ?>
</nav>