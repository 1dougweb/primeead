<?php

/**
 * Laravel Framework Root Index
 * 
 * This file redirects all requests to the public folder
 * for proper Laravel application serving.
 */

// Check if we're accessing a static file (CSS, JS, images, etc.)
$requestUri = $_SERVER['REQUEST_URI'];
$staticExtensions = ['css', 'js', 'png', 'jpg', 'jpeg', 'gif', 'svg', 'ico', 'webp', 'woff', 'woff2', 'ttf', 'otf', 'pdf'];
$extension = pathinfo(parse_url($requestUri, PHP_URL_PATH), PATHINFO_EXTENSION);

if (in_array(strtolower($extension), $staticExtensions)) {
    // Check if file exists in public directory
    $publicFile = __DIR__ . '/public' . $requestUri;
    if (file_exists($publicFile)) {
        // Serve the static file with proper MIME type
        $mimeTypes = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'ico' => 'image/x-icon',
            'webp' => 'image/webp',
            'woff' => 'font/woff',
            'woff2' => 'font/woff2',
            'ttf' => 'font/ttf',
            'otf' => 'font/otf',
            'pdf' => 'application/pdf'
        ];
        
        $mimeType = $mimeTypes[$extension] ?? 'application/octet-stream';
        header('Content-Type: ' . $mimeType);
        header('Content-Length: ' . filesize($publicFile));
        readfile($publicFile);
        exit;
    }
}

// For all other requests, load Laravel's public/index.php
require_once __DIR__ . '/public/index.php'; 