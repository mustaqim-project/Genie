<?php
$lines = file(__DIR__ . '/rendered_document_show.html');
for ($i = 4735; $i <= 4790; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
