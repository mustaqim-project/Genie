<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');

// Let's check DOM parsing using DOMDocument
libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$errors = libxml_get_errors();
echo "Total libxml errors: " . count($errors) . "\n";
foreach (array_slice($errors, 0, 15) as $err) {
    echo "Line {$err->line}: {$err->message}";
}

// Let's find the element with x-data="{ activeTab"
$xpath = new DOMXPath($dom);
$nodes = $xpath->query('//*[@x-data]');
echo "Total elements with x-data: " . $nodes->length . "\n";
foreach ($nodes as $node) {
    $xdata = $node->getAttribute('x-data');
    if (strpos($xdata, 'activeTab') !== false) {
        echo "Found activeTab x-data node: <{$node->nodeName} class='{$node->getAttribute('class')}'>\n";
        echo "Child elements count: " . $node->childNodes->length . "\n";
        // Check if button with activeTab is inside this node
        $buttons = $xpath->query('.//button[contains(@class, "activeTab") or contains(@click, "activeTab") or contains(@x-show, "activeTab")]', $node);
        echo "Buttons with activeTab inside this node: " . $buttons->length . "\n";
    }
}
