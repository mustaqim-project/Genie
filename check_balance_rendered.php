<?php
$html = file_get_contents(__DIR__ . '/rendered_document_show.html');

preg_match_all('/<div[\s>]/', $html, $mOpen);
preg_match_all('/<\/div>/', $html, $mClose);
echo "RENDERED HTML: <div> open: " . count($mOpen[0]) . ", close: " . count($mClose[0]) . ", diff: " . (count($mOpen[0]) - count($mClose[0])) . "\n";

preg_match_all('/<form[\s>]/', $html, $mOpen);
preg_match_all('/<\/form>/', $html, $mClose);
echo "RENDERED HTML: <form> open: " . count($mOpen[0]) . ", close: " . count($mClose[0]) . ", diff: " . (count($mOpen[0]) - count($mClose[0])) . "\n";

preg_match_all('/<button[\s>]/', $html, $mOpen);
preg_match_all('/<\/button>/', $html, $mClose);
echo "RENDERED HTML: <button> open: " . count($mOpen[0]) . ", close: " . count($mClose[0]) . ", diff: " . (count($mOpen[0]) - count($mClose[0])) . "\n";
