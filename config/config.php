<?php
/**
 * Application Configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set timezone
date_default_timezone_set('America/La_Paz');

// Detect base URL dynamically
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? "https" : "http");
$host = $_SERVER['HTTP_HOST'];
$scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('BASE_URL', rtrim($protocol . "://" . $host . $scriptName, '/') . '/');

// Application constants
define('APP_NAME', 'Sistema POS');
define('APP_VERSION', '1.0.0');

// Security constants
define('HASH_ALGO', 'sha256');
define('ENCRYPTION_KEY', 'pos-system-key-2024');

// File upload constants
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB

// Pagination
define('RECORDS_PER_PAGE', 10);

// Tax rate (13% IVA Bolivia)
define('TAX_RATE', 0.13);

// Error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Autoloader for classes
spl_autoload_register(function ($class) {
    $directories = [
        __DIR__ . '/../models/',
        __DIR__ . '/../controllers/',
        __DIR__ . '/../libraries/'
    ];
    
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// Helper functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function getCurrentUser() {
    if (!isLoggedIn()) return null;
    return [
        'id' => $_SESSION['user_id'],
        'username' => $_SESSION['username'],
        'full_name' => $_SESSION['full_name'],
        'role' => $_SESSION['role']
    ];
}

function hasRole($role) {
    $user = getCurrentUser();
    return $user && $user['role'] === $role;
}

function isAdmin() {
    return hasRole('admin');
}

function formatCurrency($amount) {
    return 'Bs. ' . number_format($amount, 2);
}

function formatDate($date) {
    return date('d/m/Y H:i', strtotime($date));
}

function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function generateCode($prefix, $length = 6) {
    $number = str_pad(rand(1, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
    return $prefix . $number;
}

function jsonResponse($data, $status = 200) {
    http_response_code($status);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit();
}
