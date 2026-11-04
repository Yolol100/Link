<?php
/**
 * Isolated WordPress regression for CSV export and the two log entrypoints.
 * This file runs only in the disposable CI WordPress/MySQL installation.
 */
$cases = [
    ['=1+2', true],
    ['+3', true],
    ['-4', true],
    ['@SUM(1)', true],
    ["  \t=1+2", true],
    ["\r+3", true],
    ["\n@SUM(1)", true],
    ["\t=1+2", true],
    ['＝1+2', true],
    ['＋3', true],
    ['－4', true],
    ['＠SUM(1)', true],
    ['Normal keyword', false],
    [' A normal word', false],
];
foreach ($cases as $case) {
    [$original, $escape] = $case;
    $actual = \Webactueel\SSIL\csv_safe_cell($original);
    $expected = $escape ? "'" . $original : $original;
    if ($actual !== $expected) {
        throw new \RuntimeException('Unsafe CSV cell for ' . json_encode($original, JSON_UNESCAPED_UNICODE));
    }
    $stream = fopen('php://temp', 'w+');
    fputcsv($stream, [$actual]);
    rewind($stream);
    $row = fgetcsv($stream);
    fclose($stream);
    if (!is_array($row) || $row[0] !== $expected) {
        throw new \RuntimeException('CSV cell did not survive fputcsv/fgetcsv for ' . json_encode($original, JSON_UNESCAPED_UNICODE));
    }
}

global $wpdb;
$table = $wpdb->prefix . 'ssil_logs';
$prefix = 'ssil-ci-only-' . wp_generate_uuid4();
try {
    \Webactueel\SSIL\add_log($prefix . '-first', 'https://example.org/first', null, 'Sample');
    \Webactueel\SSIL\save_log($prefix . '-second', 'https://example.org/second', null, 'Sample');
    $rows = $wpdb->get_results($wpdb->prepare(
        "SELECT keyword, url, page_title FROM {$table} WHERE keyword IN (%s, %s) ORDER BY keyword ASC",
        $prefix . '-first',
        $prefix . '-second'
    ), ARRAY_A);
    if (count($rows) !== 2 || $rows[0]['keyword'] !== $prefix . '-first' || $rows[1]['keyword'] !== $prefix . '-second') {
        throw new \RuntimeException('Log public entrypoints no longer have matching side effects.');
    }
} finally {
    $wpdb->delete($table, ['keyword' => $prefix . '-first']);
    $wpdb->delete($table, ['keyword' => $prefix . '-second']);
}

echo "SSIL CSV formula and log alias regression: OK\n";
