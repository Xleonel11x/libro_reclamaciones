<?php
/**
 * Vercel PHP entry point
 * Routes requests to the appropriate handler in public/
 */

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Static assets - let Vercel handle them
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2)$/', $uri)) {
    return false; // Let Vercel serve static files
}

// Map routes to our public/ structure
$routes = [
    '/api/' => '/public/api.php',
    '/pdf/' => '/public/pdf.php',
    '/admin/' => '/public/admin/',
    '/' => '/public/index.php',
];

$target = null;
foreach ($routes as $prefix => $dest) {
    if (str_starts_with($uri, $prefix)) {
        $target = $dest;
        break;
    }
}

if (!$target) {
    $target = '/public/index.php';
}

// Handle admin sub-routes
if ($target === '/public/admin/') {
    $adminPath = substr($uri, strlen('/admin'));
    if ($adminPath === '' || $adminPath === '/') $adminPath = '/index.php';
    $target = '/public/admin' . $adminPath;
}

// Build the actual file path - Vercel deploys to /var/task, so public/ is sibling of api/
$baseDir = dirname(__DIR__); // /var/task
$file = $baseDir . $target;

// Handle directory index
if (is_dir($file)) {
    $file .= '/index.php';
}

if (!is_file($file)) {
    http_response_code(404);
    echo "Not Found: $uri";
    exit;
}

// Set up the request as if it came directly to the file
$_SERVER['SCRIPT_FILENAME'] = $file;
$_SERVER['SCRIPT_NAME'] = $target;
$_SERVER['PHP_SELF'] = $target;

// Preserve query string
if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
    // Already set by Vercel
}

// Include the target file
require $file;