<?php

$dir = __DIR__ . '/../resources/views';
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));

foreach ($iterator as $file) {
    if (!$file->isFile() || !str_ends_with($file->getFilename(), '.blade.php')) {
        continue;
    }

    $c = file_get_contents($file->getRealPath());
    if (preg_match_all('/x-data\s*=\s*[\'"]([a-zA-Z0-9_]+)\s*\(/', $c, $m)) {
        $relativePath = str_replace('\\', '/', substr($file->getRealPath(), strlen(realpath($dir)) + 1));
        echo $relativePath . ': ' . implode(', ', array_unique($m[1])) . "\n";
    }
}
