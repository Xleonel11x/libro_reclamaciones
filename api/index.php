<?php
/**
 * Vercel PHP entry point for Libro de Reclamaciones
 *
 * vercel-php@0.7.4 runtime:
 * - Runs composer install during build
 * - autoload is at /vendor/autoload.php
 */

// Load config/bootstrap (handles both local and Vite environments)
require_once __DIR__ . '/../config/bootstrap.php';

// Parse the request URI
$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH);

// Map routes to handlers
$routes = [
    '/api' => __DIR__ . '/../public/api.php',
    '/pdf' => __DIR__ . '/../public/pdf.php',
    '/admin/login' => __DIR__ . '/../public/admin/login.php',
    '/admin/logout' => __DIR__ . '/../public/admin/logout.php',
    '/admin/sedes' => __DIR__ . '/../public/admin/sedes.php',
    '/admin/locales' => __DIR__ . '/../public/admin/locales.php',
    '/admin/index' => __DIR__ . '/../public/admin/index.php',
    '/admin/api' => __DIR__ . '/../public/admin/api.php',
    '/admin/pdf' => __DIR__ . '/../public/admin/pdf.php',
];

// Find matching route
$handler = null;
foreach ($routes as $route => $file) {
    if (strpos($path, $route) === 0) {
        $handler = $file;
        break;
    }
}

// Default to main index
if (!$handler || !is_file($handler)) {
    $handler = __DIR__ . '/../public/index.php';
}

// Set SCRIPT_FILENAME for routing inside the app
$_SERVER['SCRIPT_FILENAME'] = $handler;
$_SERVER['SCRIPT_NAME'] = $path;
$_SERVER['PHP_SELF'] = $path;

// Include the handler
require $handler;