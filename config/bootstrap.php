<?php
/**
 * Bootstrap - Works both for local development (Apache/Nginx) and Vercel serverless
 */
declare(strict_types=1);

// Prevent double loading
if (defined('BOOTSTRAP_LOADED')) exit;
define('BOOTSTRAP_LOADED', true);

// Load composer autoload (may already be loaded by api/index.php in Vercel)
$autoloadPath = dirname(__DIR__) . '/vendor/autoload.php';
if (is_file($autoloadPath) && !class_exists(\Composer\Autoload\ClassLoader::class)) {
    require_once $autoloadPath;
}

// Load environment variables
$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    foreach (parse_ini_file($envPath) ?: [] as $k => $v) $_ENV[$k] = $v;
}

// Autoload src classes
spl_autoload_register(fn($c) => is_file($f = __DIR__ . "/../src/$c.php") && require $f);

// Timezone Perú
date_default_timezone_set('America/Lima');

// Session configuration (Vercel: serverless, local: Apache)
if (PHP_SAPI !== 'cli') {
    // Verificar si estamos en Vercel (serverless)
    $isVercel = getenv('VERCEL') === '1' || isset($_SERVER['VERCEL']) || isset($_SERVER['HTTP_X_FORWARDED_PROTO']);

    // Determinar si la conexión es segura
    $isSecure = $isVercel ||
                 isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ||
                 isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https';

    // Configurar parámetros de cookie DE ANTES de session_start()
    $host = $_SERVER['HTTP_HOST'] ?? null;
    session_set_cookie_params([
        'lifetime' => 1440,
        'path' => '/',
        'domain' => $host,
        'secure' => $isSecure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    ini_set('session.cookie_httponly', '1');
    ini_set('session.use_strict_mode', '1');

    if ($isVercel) {
        // Vercel serverless: use /tmp for session files
        ini_set('session.save_path', '/tmp');
        ini_set('session.gc_maxlifetime', '1440');
    }

    session_start();
}

function env(string $k, $d = null) { return $_ENV[$k] ?? $d; }
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function json_out($d, int $c = 200): never {
    if (session_status() === PHP_SESSION_ACTIVE) {
        session_write_close();
    }
    http_response_code($c);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($d, JSON_UNESCAPED_UNICODE);
    exit;
}
function csrf(): string { return $_SESSION['csrf'] ??= bin2hex(random_bytes(16)); }
function check_csrf(): void {
    if (!hash_equals(csrf(), $_SERVER['HTTP_X_CSRF'] ?? '')) {
        json_out(['error' => 'Sesión inválida, recarga la página'], 419);
    }
}
function require_admin(bool $json = true): void {
    if (!empty($_SESSION['admin'])) return;
    if ($json) {
        json_out(['error' => 'No autorizado'], 401);
    }
    header('Location: login.php');
    exit;
}
function is_uuid($v): bool {
    return (bool)preg_match('/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i', (string)$v);
}