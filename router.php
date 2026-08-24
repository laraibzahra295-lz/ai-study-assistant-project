<?php
/**
 * Router for PHP built-in server:  php -S localhost:8000 router.php
 */

$reqUri = $_SERVER['REQUEST_URI'] ?? '/';
$path   = parse_url($reqUri, PHP_URL_PATH) ?: '/';

if ($path === '/' || $path === '') $path = '/index.html';

$docroot  = __DIR__;
$candidate = $docroot . $path;

// Pretty URLs: /about -> /about.html
if (!file_exists($candidate) && file_exists($candidate . '.html')) {
    $candidate .= '.html';
}

// Directory? -> index.html
if (is_dir($candidate)) {
    $candidate = rtrim($candidate, '/') . '/index.html';
}

// Path traversal guard
$real = realpath($candidate);
if ($real === false || !str_starts_with($real, realpath($docroot))) {
    http_response_code(404);
    readfile($docroot . '/404.html');
    return true;
}

// PHP file → execute
if (str_ends_with($real, '.php')) {
    $_SERVER['SCRIPT_FILENAME'] = $real;
    chdir(dirname($real));
    require $real;
    return true;
}

// Static file → serve
$mime = [
    'html' => 'text/html; charset=utf-8',
    'css'  => 'text/css; charset=utf-8',
    'js'   => 'application/javascript; charset=utf-8',
    'json' => 'application/json; charset=utf-8',
    'png'  => 'image/png',
    'jpg'  => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'svg'  => 'image/svg+xml',
    'ico'  => 'image/x-icon',
    'webp' => 'image/webp',
    'txt'  => 'text/plain; charset=utf-8',
];
$ext = strtolower(pathinfo($real, PATHINFO_EXTENSION));
header('Content-Type: ' . ($mime[$ext] ?? 'application/octet-stream'));
header('Cache-Control: no-store');
readfile($real);
return true;
