<?php
$lines = file(__DIR__ . '/rendered_document_show.html');

echo "=== LINE 1680-1705 ===\n";
for ($i = 1680; $i <= 1705; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}

echo "\n=== LINE 2240-2260 ===\n";
for ($i = 2240; $i <= 2260; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}

echo "\n=== LINE 2300-2320 ===\n";
for ($i = 2300; $i <= 2320; $i++) {
    if (isset($lines[$i])) echo ($i + 1) . ": " . $lines[$i];
}
