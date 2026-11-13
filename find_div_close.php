<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');

// Let's find all occurrences of activeTab
preg_match_all('/activeTab[a-zA-Z0-9_.]*/', $html, $matches, PREG_OFFSET_CAPTURE);
echo "Occurrences of activeTab: " . count($matches[0]) . "\n";
foreach ($matches[0] as $m) {
    $offset = $m[1];
    $lineNum = substr_count(substr($html, 0, $offset), "\n") + 1;
    $snippet = substr($html, max(0, $offset - 40), 100);
    echo "Line $lineNum: " . str_replace(["\r", "\n"], " ", $snippet) . "\n";
}
