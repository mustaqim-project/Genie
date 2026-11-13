<?php
$lines = file(__DIR__ . '/rendered_document_show.html');
for ($i = 2170; $i <= 2215; $i++) {
    if (isset($lines[$i])) {
        echo ($i + 1) . ": " . $lines[$i];
    }
}
