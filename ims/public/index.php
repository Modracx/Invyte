<?php

/**
 * Invyte - Professional Edition
 * Invyte is an open-source inventory management system designed for Magento 2
 * Version: 1.0.0
 * Kenneth D'silva (Modracx), Copyright (c) March 2026
 * Licensed under the MIT License – https://opensource.org/licenses/MIT
 */


// Determine if this is an API request
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '/');

// Strip /ims prefix if present (when served via Apache Alias/symlink under /ims)
$basePath = '/ims';
if (str_starts_with($uri, $basePath . '/') || $uri === $basePath) {
    $relativePath = substr($uri, strlen($basePath)) ?: '/';
} else {
    $relativePath = $uri; // Direct access (dev server or direct /public/ URL)
}
if ($relativePath === '' || $relativePath === false) $relativePath = '/';

// Serve static assets directly
if ($relativePath !== '/' && file_exists(__DIR__ . $relativePath)) {
    return false;
}

// Route API requests to Lumen
if (str_starts_with($relativePath, '/api/')) {
    // Strip /ims prefix so Lumen router sees /api/... instead of /ims/api/...
    $_SERVER['REQUEST_URI'] = $relativePath . (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '');
    $app = require __DIR__ . '/../bootstrap/app.php';
    $app->run();
    return;
}

// Serve SPA for all other routes
require __DIR__ . '/spa.html.php';
