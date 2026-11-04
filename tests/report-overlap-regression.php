<?php
declare(strict_types=1);

// ASCII-only fixture fallback for CLI environments without mbstring.
if (!function_exists('mb_stripos')) {
    function mb_stripos(string $haystack, string $needle, int $offset = 0): int|false {
        return stripos($haystack, $needle, $offset);
    }
}

define('ABSPATH', __DIR__);
require_once dirname(__DIR__) . '/includes/ssil-report-logic.php';

$fixtures = [
    [[], []],
    [['alpha', 'alphabet'], [['alphabet', 'alpha']]],
    [
        ['0', '00', '0', '000'],
        [0 => ['00', '0'], 2 => ['000', '0'], 3 => ['000', '00']],
    ],
    [
        ['abc', 'ABC'],
        [['abc', 'ABC'], ['ABC', 'abc']],
    ],
];

foreach ($fixtures as $index => [$keywords, $expected]) {
    $actual = \Webactueel\SSIL\find_overlapping_keywords($keywords);
    if ($actual !== $expected) {
        fwrite(STDERR, "Keyword overlap fixture {$index} failed.\n");
        exit(1);
    }
}

echo "Keyword overlap behavior: OK\n";
