<?php
/**
 * Auth Helper - Sistema de autenticación híbrido (sistema + JWT)
 */
class Auth {
    /**
     * Login del admin - genera JWT y cookie
     */
    public static function loginAdmin(string $id, string $nombre): void {
        $csrf = bin2hex(random_bytes(16));
        $token = JWT::generate(['admin' => ['id' => $id, 'nombre' => $nombre], 'csrf' => $csrf]);
        JWT::setAuthCookie($token);
        // También mantener sesión PHP para compatibilidad (local)
        $_SESSION['admin'] = ['id' => $id, 'nombre' => $nombre, 'jwt' => $token];
        $_SESSION['csrf'] = $csrf; // CSRF en sesión para compatibilidad local
        // También guardar token en sesión para uso posterior
        $_SESSION['jwt_token'] = $token;
    }

    /**
     * Obtener token JWT desde sesión (para uso en login.php)
     */
    public static function getJwtTokenFromSession(): ?string {
        return $_SESSION['jwt_token'] ?? null;
    }

    /**
     * Logout del admin - elimina JWT y sesión
     */
    public static function logoutAdmin(): void {
        JWT::clearAuthCookie();
        session_unset();
        session_destroy();
    }

    /**
     * Verificar si el admin está autenticado (JWT o sesión)
     */
    public static function isAdmin(): ?array {
        // Primero verificar JWT (funciona en serverless)
        $jwtAdmin = JWT::isAdmin();
        if ($jwtAdmin) {
            return $jwtAdmin['admin'];
        }

        // Fallback a sesión PHP (para compatibilidad local)
        if (!empty($_SESSION['admin'])) {
            return $_SESSION['admin'];
        }

        return null;
    }

    /**
     * Obtener CSRF del token JWT actual
     */
    public static function getJwtCsrf(): ?string {
        $token = JWT::getToken();
        if (!$token) return null;
        $jwtData = JWT::verify($token);
        return $jwtData['csrf'] ?? null;
    }

    /**
     * Requerir autenticación - lanzar error si no está autenticado
     */
    public static function requireAdmin(bool $json = true): void {
        $admin = self::isAdmin();
        if ($admin) {
            return;
        }

        if ($json) {
            json_out(['error' => 'No autorizado'], 401);
        }
        header('Location: login.php');
        exit;
    }

    /**
     * Verificar CSRF - prioriza JWT (serverless), fallback a sesión (local)
     */
    public static function checkCsrf(): bool {
        $sentCsrf = $_SERVER['HTTP_X_CSRF'] ?? '';

        // 1. Intentar validar contra CSRF en JWT (serverless Vercel)
        $jwtCsrf = self::getJwtCsrf();
        if ($jwtCsrf !== null) {
            return hash_equals($jwtCsrf, $sentCsrf);
        }

        // 2. Fallback a CSRF de sesión (desarrollo local Apache/Nginx)
        return hash_equals(csrf(), $sentCsrf);
    }

    /**
     * Generar meta tag CSRF para JavaScript (lee del JWT o sesión)
     */
    public static function csrfMeta(): string {
        $csrf = self::getJwtCsrf() ?? csrf();
        return '<meta name="csrf" content="' . e($csrf) . '">';
    }
}