<?php
declare(strict_types=1);
require __DIR__ . '/../vendor/autoload.php';

// Load .env file (works both locally and in Vercel)
$envPath = __DIR__ . '/../.env';
if (is_file($envPath)) {
    foreach (parse_ini_file($envPath) ?: [] as $k => $v) {
        $_ENV[$k] = $v;
    }
}

// Autoload src classes
spl_autoload_register(fn($c) => is_file($f = __DIR__ . "/../src/$c.php") && require $f);

// Timezone Perú
date_default_timezone_set('America/Lima');

// Session configuration for serverless (Vercel)
// Note: File-based sessions don't persist in serverless.
// For production, use Supabase Auth or Vercel KV (Redis).
if (PHP_SAPI !== 'cli') {
    ini_set('session.save_path', '/tmp');
    ini_set('session.gc_maxlifetime', '1440');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', isset($_SERVER['HTTPS']) ? '1' : '0');
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.use_strict_mode', '1');
    session_start();
}

function env(string $k, $d = null) { return $_ENV[$k] ?? $d; }
function e($s): string { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function json_out($d, int $c = 200): never {
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
    if ($json) json_out(['error' => 'No autorizado'], 401);
    header('Location: login.php');
    exit;
}
function is_uuid($v): bool {
    return (bool)preg_match('/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/i', (string)$v);
}