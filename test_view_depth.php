<?php
$lines = file('c:/laragon/www/GENIE/resources/views/core/document_show.blade.php');

$depth = 0;
for ($i = 5; $i <= 265; $i++) {
    $line = $lines[$i];
    $lineNum = $i + 1;
    
    // Check included files
    if (preg_match('/@include\([\'"]([^\'"]+)[\'"]\)/', $line, $incM)) {
        echo "Line $lineNum: @include({$incM[1]})\n";
        $incPath = 'c:/laragon/www/GENIE/resources/views/' . str_replace('.', '/', $incM[1]) . '.blade.php';
        if (file_exists($incPath)) {
            $incContent = file_get_contents($incPath);
            preg_match_all('/<div[\s>]/', $incContent, $mOpen);
            preg_match_all('/<\/div>/', $incContent, $mClose);
            $incDiff = count($mOpen[0]) - count($mClose[0]);
            echo "   Included file diff: $incDiff (open: " . count($mOpen[0]) . ", close: " . count($mClose[0]) . ")\n";
            $depth += $incDiff;
        }
    }
    
    preg_match_all('/<div[\s>]/', $line, $mOpen);
    preg_match_all('/<\/div>/', $line, $mClose);
    $diff = count($mOpen[0]) - count($mClose[0]);
    $depth += $diff;
    if ($diff != 0) {
        echo "Line $lineNum: diff $diff -> current depth: $depth (" . trim($line) . ")\n";
    }
}
echo "Total depth before line 266: $depth\n";
