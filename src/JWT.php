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
     * Obtener token desde cookie, header o meta (para fallback en serverless)
     */
    public static function getToken(): ?string {
        // 1. Primero buscar en cookie (PHP superglobal)
        if (isset($_COOKIE['auth_token'])) {
            return $_COOKIE['auth_token'];
        }

        // 2. Si $_COOKIE no está disponible, leer del header Cookie manualmente
        // (problema conocido en vercel-php serverless)
        if (isset($_SERVER['HTTP_COOKIE'])) {
            $cookies = [];
            foreach (explode(';', $_SERVER['HTTP_COOKIE']) as $cookie) {
                $partes = explode('=', $cookie, 2);
                if (count($partes) === 2) {
                    $cookies[trim($partes[0])] = trim($partes[1]);
                }
            }
            if (isset($cookies['auth_token'])) {
                return $cookies['auth_token'];
            }
        }

        // 3. Buscar en header Authorization (para AJAX con Bearer token)
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if (stripos($auth, 'Bearer ') === 0) {
                return substr($auth, 7);
            }
        }
        // También buscar en PHP_AUTH_TOKEN (auto-agregado por PHP)
        if (isset($_SERVER['PHP_AUTH_TOKEN'])) {
            return $_SERVER['PHP_AUTH_TOKEN'];
        }

        // 4. FALLBACK: Leer del query string (solo para debugging/environments with cookie issues)
        // NOTA: Este es un workaround de última instancia para Vercel serverless
        if (isset($_GET['auth_token'])) {
            return $_GET['auth_token'];
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

        // Para Vercel serverless, omitir domain para que el navegador lo asigne automáticamente
        // Esto evita problemas con dominios dinámicos como myapp.vercel.app
        $params = [
            'expires' => time() + $ttl,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        // setcookie() con params array: el primer parámetro es el nombre, segundo el valor,
        // y el tercer parámetro es el array de opciones (no acepta expires como 3er param)
        setcookie('auth_token', $token, $params);
    }

    /**
     * Borrar cookie de autenticación
     */
    public static function clearAuthCookie(): void {
        $params = [
            'expires' => time() - 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Lax',
        ];

        setcookie('auth_token', '', $params);
    }
}