<?php
$lines = file(__DIR__ . '/rendered_document_show.html');
foreach ($lines as $i => $line) {
    if (strpos($line, 'previewModalOpen') !== false) {
        echo "Line " . ($i + 1) . ": " . trim($line) . "\n";
    }
}
