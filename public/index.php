<?php
/**
 * Main Entry Point
 */

require_once __DIR__ . '/../config/config.php';

// Route handling
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);

// Detect base path dynamically
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($scriptName, '/');

// Remove base path from URL
if (strpos($path, $basePath) === 0) {
    $path = '/' . ltrim(substr($path, strlen($basePath)), '/');
}

// Remove trailing slash for consistency
$path = rtrim($path, '/');

// Default to dashboard or login
if ($path === '' || $path === '/index.php') {
    if (isLoggedIn()) {
        $path = '/dashboard';
    } else {
        $path = '/login';
    }
}

// Route to appropriate file
switch (true) {
    case $path === '/login':
    case strpos($path, '/auth') === 0:
        include __DIR__ . '/../views/auth/login.php';
        break;

    case $path === '/dashboard':
    case strpos($path, '/dashboard') === 0:
        include __DIR__ . '/../views/dashboard/index.php';
        break;

    case strpos($path, '/products') === 0:
        include __DIR__ . '/../views/products/index.php';
        break;

    case strpos($path, '/clients') === 0:
        include __DIR__ . '/../views/clients/index.php';
        break;

    case strpos($path, '/sales/create') === 0:
        include __DIR__ . '/../views/sales/create.php';
        break;

    case strpos($path, '/sales') === 0:
        include __DIR__ . '/../views/sales/index.php';
        break;

    case strpos($path, '/reports') === 0:
        include __DIR__ . '/../views/reports/index.php';
        break;

    case strpos($path, '/api') === 0:
        include __DIR__ . '/api.php';
        break;

    default:
        http_response_code(404);
        echo '404 - Página no encontrada';
        break;
}
