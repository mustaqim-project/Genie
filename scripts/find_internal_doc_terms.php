<?php

$dir = __DIR__ . '/../resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
$matches = [];

$terms = ['PRD', 'BRD', 'FSD', 'SRS', 'F15', 'F16', 'F0', 'F1', 'F2', 'F3', 'F4', 'F5', 'F6', 'F7', 'F8', 'F9', 'F10', 'F11', 'F12', 'F13'];

foreach ($iterator as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $filePath = $file->getRealPath();
    $lines = file($filePath);

    foreach ($lines as $lineNum => $line) {
        // Cek PRD, BRD, FSD, SRS dulu
        foreach (['PRD', 'BRD', 'FSD', 'SRS'] as $docTerm) {
            if (preg_match('/\b' . $docTerm . '\b/i', $line)) {
                $matches[] = [
                    'file' => str_replace('\\', '/', substr($filePath, strlen(realpath($dir)) + 1)),
                    'line' => $lineNum + 1,
                    'term' => $docTerm,
                    'text' => trim($line),
                ];
            }
        }
        // Cek pola F15, F16, F8, F7 di judul/badge/teks UI
        if (preg_match('/\b(F[0-9]{1,2})\b/', $line, $fMatch)) {
            // Hindari kelas CSS seperti from-, focus-, dll
            if (!preg_match('/class=.*' . $fMatch[1] . '/', $line)) {
                $matches[] = [
                    'file' => str_replace('\\', '/', substr($filePath, strlen(realpath($dir)) + 1)),
                    'line' => $lineNum + 1,
                    'term' => $fMatch[1],
                    'text' => trim($line),
                ];
            }
        }
    }
}

echo "=== SCAN FOR PRD / BRD / FSD & FEATURE CODES IN BLADE VIEWS ===\n";
echo "Total occurrences found: " . count($matches) . "\n\n";

$byFile = [];
foreach ($matches as $m) {
    $byFile[$m['file']][] = $m;
}

foreach ($byFile as $file => $items) {
    echo "📄 {$file} (" . count($items) . " occurrences):\n";
    foreach ($items as $item) {
        echo "   Line {$item['line']} [{$item['term']}]: {$item['text']}\n";
    }
    echo "\n";
}
