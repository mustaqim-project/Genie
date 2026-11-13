<?php
$lines = file(__DIR__ . '/rendered_document_show.html');
for ($i = 1684; $i <= 1705; $i++) {
    echo ($i + 1) . ": " . $lines[$i];
}
