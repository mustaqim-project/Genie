<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');
$lines = explode("\n", $html);

foreach ($lines as $idx => $line) {
    if (strpos($line, 'activeTab') !== false) {
        $lineNum = $idx + 1;
        echo "Line $lineNum: " . trim($line) . "\n";
    }
}
