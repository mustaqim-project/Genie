<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');

// Let's tokenize all HTML tags across the entire string
preg_match_all('/<(\/?[a-z0-9]+)([^>]*)>/is', $html, $matches, PREG_OFFSET_CAPTURE);

$stack = [];
foreach ($matches[0] as $idx => $m) {
    $fullTag = $m[0];
    $offset = $m[1];
    $lineNum = substr_count(substr($html, 0, $offset), "\n") + 1;
    $tagName = strtolower($matches[1][$idx][0]);
    $attrs = $matches[2][$idx][0];
    
    if (in_array($tagName, ['img', 'input', 'br', 'hr', 'meta', 'link'])) continue;
    if (substr(rtrim($attrs), -1) === '/') continue;
    
    if ($tagName[0] === '/') {
        $closeTag = substr($tagName, 1);
        
        $mainIdx = -1;
        foreach ($stack as $sIdx => $item) {
            if ($item['isMain']) {
                $mainIdx = $sIdx;
                break;
            }
        }
        
        // Find matching tag from top of stack
        for ($k = count($stack) - 1; $k >= 0; $k--) {
            if ($stack[$k]['tag'] === $closeTag) {
                if ($mainIdx !== -1 && $k <= $mainIdx) {
                    echo "!!! THE MAIN activeTab DIV WAS CLOSED ON LINE $lineNum BY </$closeTag> !!!\n";
                    echo "Matched opening tag: <{$stack[$k]['tag']}> at line {$stack[$k]['line']}\n";
                    $lineSnippet = substr($html, max(0, $offset - 150), 300);
                    echo "Snippet around line $lineNum:\n$lineSnippet\n";
                    exit;
                }
                $stack = array_slice($stack, 0, $k);
                break;
            }
        }
    } else {
        $isMain = (strpos($attrs, 'activeTab') !== false && strpos($attrs, 'previewModalOpen') !== false);
        if ($isMain) {
            echo ">>> MAIN DIV PUSHED AT LINE $lineNum <<<\n";
        }
        $stack[] = [
            'tag' => $tagName,
            'line' => $lineNum,
            'isMain' => $isMain,
            'full' => substr($fullTag, 0, 50)
        ];
    }
}
