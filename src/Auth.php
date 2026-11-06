<?php
/**
 * Auth Helper - Sistema de autenticación híbrido (sistema + JWT)
 */
class Auth {
    /**
     * Login del admin - genera JWT y cookie
     */
    public static function loginAdmin(string $id, string $nombre): void {
        $token = JWT::generate(['admin' => ['id' => $id, 'nombre' => $nombre]]);
        JWT::setAuthCookie($token);
        // También mantener sesión PHP para compatibilidad
        $_SESSION['admin'] = ['id' => $id, 'nombre' => $nombre, 'jwt' => $token];
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

        // Fallback a sesión PHP (para compatibilidad)
        if (!empty($_SESSION['admin'])) {
            return $_SESSION['admin'];
        }

        return null;
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
     * Verificar CSRF usando JWT
     */
    public static function checkCsrf(): bool {
        $token = JWT::getToken();
        if ($token) {
            $jwtData = JWT::verify($token);
            if ($jwtData && isset($jwtData['csrf'])) {
                return hash_equals($jwtData['csrf'], $_SERVER['HTTP_X_CSRF'] ?? '');
            }
        }

        // Fallback a CSRF de sesión
        return hash_equals(csrf(), $_SERVER['HTTP_X_CSRF'] ?? '');
    }
}