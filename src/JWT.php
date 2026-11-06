<?php
/**
 * JWT Helper para autenticación stateless en Vercel serverless
 */
class JWT {
    private static ?string $secret = null;

    private static function getSecret(): string {
        if (self::$secret === null) {
            self::$secret = env('JWT_SECRET') ?: 'cambiar-a-una-clave-secreta-muy-larga-en-produccion';
        }
        return self::$secret;
    }

    /**
     * Generar token JWT para el admin
     */
    public static function generate(array $payload, int $ttl = 86400): string {
        $now = time();
        $exp = $now + $ttl;

        $header = base64_encode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $payloadEnc = base64_encode(json_encode(array_merge($payload, ['iat' => $now, 'exp' => $exp])));

        $signature = hash_hmac('sha256', "$header.$payloadEnc", self::getSecret(), true);
        $sigEnc = base64_encode($signature);

        return "$header.$payloadEnc.$sigEnc";
    }

    /**
     * Verificar y decodificar token JWT
     */
    public static function verify(string $token): ?array {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return null;

        [$header, $payload, $signature] = $parts;

        $expectedSig = hash_hmac('sha256', "$header.$payload", self::getSecret(), true);
        $expectedSigEnc = base64_encode($expectedSig);

        if (!hash_equals($expectedSigEnc, $signature)) return null;

        $payloadDec = json_decode(base64_decode($payload), true);
        if (!$payloadDec || (isset($payloadDec['exp']) && $payloadDec['exp'] < time())) return null;

        return $payloadDec;
    }

    /**
     * Obtener token desde cookie o header Authorization
     */
    public static function getToken(): ?string {
        // Primero buscar en cookie
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }
        // Luego buscar en header Authorization (para AJAX)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (stripos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
        }
        return null;
    }

    /**
     * Verificar si el admin está autenticado
     */
    public static function isAdmin(): ?array {
        $token = self::getToken();
        if (!$token) return null;

        $payload = self::verify($token);
        if (!$payload || !isset($payload['admin'])) return null;

        return $payload;
    }

    /**
     * Establecer cookie de autenticación
     */
    public static function setAuthCookie(string $token, int $ttl = 86400): void {
        $secure = env('APP_URL') && stripos(env('APP_URL'), 'https://') === 0;
        setcookie('auth_token', $token, $ttl, '/', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null, $secure, true, 'Lax');
    }

    /**
     * Borrar cookie de autenticación
     */
    public static function clearAuthCookie(): void {
        setcookie('auth_token', '', time() - 3600, '/', isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null, true, true, 'Lax');
    }
}