<?php
class Reclamo {
  const SELECT = 'select=*,locales(serie,nombre,direccion,email,sedes(nombre,razon_social,ruc,domicilio,email))';

  static function find(string $filter): ?array {
    return Supabase::select('reclamos?' . self::SELECT . "&$filter&limit=1")[0] ?? null;
  }
  static function numero(array $r): string {
    return strtoupper($r['locales']['serie'] ?? '') . '-' . sprintf('%04d', $r['correlativo']);
  }
  /** Fecha + 15 días hábiles (excluye sábado y domingo; no considera feriados). */
  static function fechaLimite(string $fecha, int $dias = 15): string {
    $d = new DateTime($fecha);
    while ($dias > 0) { $d->modify('+1 day'); if ($d->format('N') < 6) $dias--; }
    return $d->format('Y-m-d');
  }
  /** Envía el PDF al consumidor y/o al local. Devuelve los correos notificados. */
  static function notify(array $r, bool $consumidor, bool $proveedor): array {
    $pdf = PdfService::render($r); $n = self::numero($r);
    $file = "reclamo-$n.pdf"; $ok = [];
    $html = fn($t) => "<p>$t</p><p><b>N° " . e($n) . '</b> · ' . e($r['locales']['sedes']['razon_social'])
      . '</p><p>Adjuntamos la Hoja de Reclamación en PDF. El proveedor debe responder en un plazo máximo de 15 días hábiles.</p>';
    $dest = [];
    if ($consumidor && !empty($r['email'])) $dest[] = [$r['email'], 'Hemos registrado tu ' . $r['tipo'], 'Hola ' . e($r['nombre']) . ', hemos recibido tu ' . $r['tipo'] . '.'];
    if ($proveedor) foreach (array_unique(array_filter([$r['locales']['email'], $r['locales']['sedes']['email']])) as $m)
      $dest[] = [$m, "Nuevo {$r['tipo']} N° $n", 'Se registró un nuevo ' . $r['tipo'] . ' en el local ' . e($r['locales']['nombre']) . '.'];
    foreach ($dest as [$to, $subj, $msg]) { Mailer::send($to, $subj, $html($msg), $pdf, $file); $ok[] = $to; }
    if ($ok && $consumidor) Supabase::update('reclamos', 'id=eq.' . $r['id'], ['email_enviado' => true]);
    return $ok;
  }
}
