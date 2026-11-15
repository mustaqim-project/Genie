<?php

$controllers = glob(__DIR__ . '/../app/Http/Controllers/*.php');
$results = [];

foreach ($controllers as $file) {
    $name = basename($file);
    if ($name === 'Controller.php') continue;

    $content = file_get_contents($file);
    $hasInterface = str_contains($content, 'HasMiddleware');
    $hasMiddlewareMethod = preg_match('/function\s+middleware\s*\(/', $content);
    $hasConstruct = preg_match('/function\s+__construct\s*\(/', $content) && str_contains($content, 'middleware(');

    preg_match_all('/permission:([a-zA-Z0-9_\-\.\*\|]+)/', $content, $pMatches);
    preg_match_all('/role:([a-zA-Z0-9_\-\.\*\|]+)/', $content, $rMatches);

    $results[$name] = [
        'has_interface' => $hasInterface,
        'has_method' => (bool)$hasMiddlewareMethod,
        'has_construct' => (bool)$hasConstruct,
        'permissions' => array_unique($pMatches[1] ?? []),
        'roles' => array_unique($rMatches[1] ?? []),
    ];
}

echo json_encode($results, JSON_PRETTY_PRINT);
