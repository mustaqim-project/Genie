<?php
$lines = file(__DIR__ . '/rendered_document_show.html');

$stack = [];
for ($lineIdx = 1686; $lineIdx < 3400; $lineIdx++) {
    $lineNum = $lineIdx + 1;
    $text = $lines[$lineIdx];
    
    // Find all tags in this line
    preg_match_all('/<(\/?[a-zA-Z0-9]+)[^>]*>/', $text, $matches, PREG_OFFSET_CAPTURE);
    foreach ($matches[1] as $idx => $m) {
        $tag = strtolower($m[0]);
        $fullTag = $matches[0][$idx][0];
        
        // self closing tags
        if (in_array($tag, ['img', 'input', 'br', 'hr', 'meta', 'link'])) {
            continue;
        }
        if (substr($fullTag, -2) === '/>') {
            continue;
        }
        
        if ($tag[0] === '/') {
            $closeTag = substr($tag, 1);
            if (empty($stack)) {
                echo "UNEXPECTED CLOSING TAG </$closeTag> on line $lineNum!\n";
            } else {
                $last = array_pop($stack);
                if ($last['tag'] !== $closeTag) {
                    echo "TAG MISMATCH on line $lineNum: expected </{$last['tag']}> (opened at {$last['line']}), got </$closeTag>!\n";
                }
                if ($last['line'] === 1687) {
                    echo ">>> THE MAIN DIV (LINE 1687) WAS CLOSED BY </$closeTag> ON LINE $lineNum! <<<\n";
                    echo "Content on line $lineNum: " . trim($text) . "\n";
                    echo "Previous 3 lines:\n";
                    echo ($lineNum - 3) . ": " . $lines[$lineIdx - 3];
                    echo ($lineNum - 2) . ": " . $lines[$lineIdx - 2];
                    echo ($lineNum - 1) . ": " . $lines[$lineIdx - 1];
                    echo ($lineNum) . ": " . $lines[$lineIdx];
                    break 2;
                }
            }
        } else {
            $stack[] = ['tag' => $tag, 'line' => $lineNum, 'full' => substr($fullTag, 0, 50)];
        }
    }
}
