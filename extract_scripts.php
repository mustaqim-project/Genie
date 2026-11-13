<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');

preg_match_all('/<script\b[^>]*>(.*?)<\/script>/is', $html, $matches, PREG_OFFSET_CAPTURE);

echo "Total script tags found: " . count($matches[0]) . "\n";
foreach ($matches[0] as $i => $m) {
    $scriptContent = $matches[1][$i][0];
    $offset = $m[1];
    $lineNum = substr_count(substr($html, 0, $offset), "\n") + 1;
    $tag = $m[0];
    
    // Check if external script
    if (preg_match('/src=["\']([^"\']+)["\']/i', $tag, $srcM)) {
        echo "Script #$i at line $lineNum: External -> {$srcM[1]}\n";
    } else {
        echo "Script #$i at line $lineNum: Inline (" . strlen($scriptContent) . " chars)\n";
        file_put_contents(__DIR__ . "/script_{$i}.js", $scriptContent);
    }
}
