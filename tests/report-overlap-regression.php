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

// Pin old and new behavior, including stable sparse indexes, against a
// deterministic varied corpus. The legacy reference handles only test strings.
$legacy = static function (array $keywords): array {
    $pairs = [];
    foreach ($keywords as $a) {
        foreach ($keywords as $b) {
            if ($a !== $b && $a !== '' && $b !== '' && mb_stripos((string) $a, (string) $b) !== false) {
                $pairs[] = [(string) $a, (string) $b];
            }
        }
    }
    return array_map('unserialize', array_unique(array_map('serialize', $pairs)));
};

mt_srand(177);
$characters = 'abcABC01:_';
for ($case = 0; $case < 1000; $case++) {
    $keywords = [];
    $length = mt_rand(1, 12);
    for ($index = 0; $index < $length; $index++) {
        $keyword = '';
        $size = mt_rand(0, 5);
        for ($character = 0; $character < $size; $character++) {
            $keyword .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        $keywords[] = $keyword;
    }
    if (\Webactueel\SSIL\find_overlapping_keywords($keywords) !== $legacy($keywords)) {
        fwrite(STDERR, "Keyword overlap parity failed at corpus case {$case}.\n");
        exit(1);
    }
}

echo "Keyword overlap behavior: OK\n";
