<?php

$controllers = array_merge(
    glob(__DIR__ . '/../app/Http/Controllers/*.php'),
    glob(__DIR__ . '/../app/Http/Controllers/**/*.php')
);

$views = [];
foreach ($controllers as $file) {
    $content = file_get_contents($file);
    if (preg_match_all('/view\s*\(\s*[\'"]([^\'"]+)[\'"]/', $content, $matches)) {
        foreach ($matches[1] as $v) {
            if (!str_contains($v, '$')) {
                $views[$v][] = basename($file);
            }
        }
    }
}

$missing = [];
$existing = [];

foreach ($views as $v => $ctrls) {
    $path = __DIR__ . '/../resources/views/' . str_replace('.', '/', $v) . '.blade.php';
    if (!file_exists($path)) {
        $missing[$v] = [
            'expected_path' => $path,
            'controllers' => array_values(array_unique($ctrls))
        ];
    } else {
        $existing[$v] = $path;
    }
}

echo "=== VIEW AUDIT SCAN RESULTS ===\n";
echo "Total Unique Views Referenced in Controllers: " . count($views) . "\n";
echo "Existing Views: " . count($existing) . "\n";
echo "Missing Views: " . count($missing) . "\n";

if (!empty($missing)) {
    echo "\n--- LIST OF MISSING VIEWS ---\n";
    foreach ($missing as $viewName => $details) {
        echo "- {$viewName} => Used in: " . implode(', ', $details['controllers']) . "\n";
        echo "  Path: {$details['expected_path']}\n";
    }
} else {
    echo "\nAll referenced views are 100% PRESENT on disk!\n";
}
