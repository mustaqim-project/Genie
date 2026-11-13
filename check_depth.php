<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');
$lines = explode("\n", $html);

// Let's track line by line between line 1685 and line 3375
$depth = 0;
for ($i = 1686; $i < 3372; $i++) {
    $lineNum = $i + 1;
    $line = $lines[$i];
    
    // Count opening divs and closing divs
    preg_match_all('/<div[\s>]/i', $line, $openM);
    preg_match_all('/<\/div>/i', $line, $closeM);
    
    $openCount = count($openM[0]);
    $closeCount = count($closeM[0]);
    
    $depth += $openCount - $closeCount;
    
    if ($depth <= 0) {
        echo "Depth hit $depth on line $lineNum!\n";
        echo "Line text: " . trim($line) . "\n";
        echo "Context:\n";
        for ($c = max(0, $i - 3); $c <= min(count($lines) - 1, $i + 3); $c++) {
            echo ($c + 1) . ": " . $lines[$c];
        }
        break;
    }
}
echo "Finished checking up to line 3372. Final depth: $depth\n";
