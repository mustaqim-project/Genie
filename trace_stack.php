<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');
$lines = explode("\n", $html);
$stack = [];

foreach ($lines as $lineIdx => $line) {
    $lineNum = $lineIdx + 1;
    
    preg_match_all('/<(\/?[a-z0-9]+)([^>]*)>/i', $line, $matches, PREG_SET_ORDER);
    foreach ($matches as $m) {
        $tag = strtolower($m[1]);
        $attrs = $m[2];
        
        if (in_array($tag, ['img', 'input', 'br', 'hr', 'meta', 'link'])) continue;
        if (substr(rtrim($attrs), -1) === '/') continue;
        
        if ($tag[0] === '/') {
            $closeTag = substr($tag, 1);
            
            // Check if line 1687 is in stack
            $mainDivIdx = -1;
            foreach ($stack as $idx => $item) {
                if ($item['line'] === 1687) {
                    $mainDivIdx = $idx;
                    break;
                }
            }
            
            for ($k = count($stack) - 1; $k >= 0; $k--) {
                if ($stack[$k]['tag'] === $closeTag) {
                    if ($mainDivIdx !== -1 && $k <= $mainDivIdx) {
                        echo "!!! LINE 1687 REMOVED FROM STACK ON LINE $lineNum BY </$closeTag> (matched with {$stack[$k]['line']}: <{$stack[$k]['tag']}>) !!!\n";
                        echo "Line text: " . trim($line) . "\n";
                        echo "Context lines:\n";
                        for ($c = max(0, $lineIdx - 5); $c <= min(count($lines)-1, $lineIdx + 5); $c++) {
                            echo ($c + 1) . ": " . $lines[$c];
                        }
                        exit;
                    }
                    $stack = array_slice($stack, 0, $k);
                    break;
                }
            }
        } else {
            $stack[] = [
                'tag' => $tag,
                'line' => $lineNum,
                'attrs' => $attrs,
            ];
        }
    }
}
