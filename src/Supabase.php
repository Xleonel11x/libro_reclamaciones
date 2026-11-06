<?php
class Supabase {
  static function req(string $m, string $path, ?array $body = null): array {
    $k = env('SUPABASE_SERVICE_KEY');
    $ch = curl_init(rtrim(env('SUPABASE_URL'), '/') . '/rest/v1/' . $path);
    curl_setopt_array($ch, [
      CURLOPT_CUSTOMREQUEST => $m, CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 20,
      CURLOPT_HTTPHEADER => ["apikey: $k", "Authorization: Bearer $k", 'Content-Type: application/json', 'Prefer: return=representation'],
      CURLOPT_POSTFIELDS => $body !== null ? json_encode($body) : null,
    ]);
    $r = curl_exec($ch); $c = curl_getinfo($ch, CURLINFO_HTTP_CODE); curl_close($ch);
    $d = json_decode((string)$r, true);
    if ($c >= 400 || $r === false) throw new RuntimeException($d['message'] ?? 'Error de conexión con Supabase');
    return is_array($d) ? $d : [];
  }
  static function select(string $p): array { return self::req('GET', $p); }
  static function insert(string $t, array $row): array { return self::req('POST', $t, $row)[0] ?? []; }
  static function update(string $t, string $filter, array $row): array { return self::req('PATCH', "$t?$filter", $row); }
}
