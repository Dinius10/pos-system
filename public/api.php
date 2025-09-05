<?php
/**
 * API Routes Handler
 */

require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

// Detect base path dynamically
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
$basePath = rtrim($scriptName, '/') . '/api';

// Remove base path from URL
if (strpos($path, $basePath) === 0) {
    $path = '/' . ltrim(substr($path, strlen($basePath)), '/');
}

// Remove trailing slash
$path = rtrim($path, '/');

try {
    switch (true) {
        // Auth routes
        case $path === '/auth/login' && $method === 'POST':
            (new AuthController())->login();
            break;

        case $path === '/auth/logout' && $method === 'POST':
            (new AuthController())->logout();
            break;

        case $path === '/auth/check' && $method === 'GET':
            (new AuthController())->checkSession();
            break;

        // Product routes
        case $path === '/products' && $method === 'GET':
            (new ProductController())->search();
            break;

        case $path === '/products' && $method === 'POST':
            (new ProductController())->create();
            break;

        case $path === '/products/update' && $method === 'POST':
            (new ProductController())->update();
            break;

        case $path === '/products/delete' && $method === 'POST':
            (new ProductController())->delete();
            break;

        case strpos($path, '/products/') === 0 && $method === 'GET':
            (new ProductController())->getProduct();
            break;

        // Client routes
        case $path === '/clients' && $method === 'GET':
            (new ClientController())->search();
            break;

        case $path === '/clients' && $method === 'POST':
            (new ClientController())->create();
            break;

        case $path === '/clients/update' && $method === 'POST':
            (new ClientController())->update();
            break;

        case $path === '/clients/delete' && $method === 'POST':
            (new ClientController())->delete();
            break;

        case strpos($path, '/clients/') === 0 && $method === 'GET':
            (new ClientController())->getClient();
            break;

        // Sale routes
        case $path === '/sales' && $method === 'POST':
            (new SaleController())->store();
            break;

        case strpos($path, '/sales/') === 0 && strpos($path, '/invoice') !== false:
            (new SaleController())->generatePDF();
            break;

        default:
            jsonResponse(['error' => 'Endpoint no encontrado'], 404);
            break;
    }
} catch (Exception $e) {
    jsonResponse([
        'success' => false,
        'message' => $e->getMessage()
    ], 500);
}
