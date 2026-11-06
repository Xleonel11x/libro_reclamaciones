<?php
require_once __DIR__ . '/../../config/bootstrap.php';
Auth::requireAdmin();
if (!Auth::checkCsrf()) json_out(['error' => 'Sesión inválida, recarga la página'], 419);
$d = json_decode(file_get_contents('php://input'), true) ?? [];
$s = fn($k) => trim((string)($d[$k] ?? ''));
$bad = fn($m) => json_out(['error' => $m], 422);

try {
  switch ($d['a'] ?? '') {
    case 'sedes':
      json_out(Supabase::select('sedes?select=*&order=razon_social'));

    case 'sede_save':
      if (!preg_match('/^\d{11}$/', $s('ruc'))) $bad('El RUC debe tener 11 dígitos');
      if ($s('nombre') === '' || $s('razon_social') === '' || $s('domicilio') === '') $bad('Completa nombre, razón social y domicilio');
      if ($s('email') !== '' && !filter_var($s('email'), FILTER_VALIDATE_EMAIL)) $bad('E-mail inválido');
      $row = ['nombre' => $s('nombre'), 'razon_social' => $s('razon_social'), 'ruc' => $s('ruc'), 'domicilio' => $s('domicilio'), 'email' => $s('email') ?: null];
      is_uuid($s('id')) ? Supabase::update('sedes', 'id=eq.' . $s('id'), $row) : Supabase::insert('sedes', $row);
      json_out(['ok' => true]);

    case 'locales':
      json_out(Supabase::select('locales?select=*,sedes(nombre)&order=nombre'));

    case 'local_save':
      if (!is_uuid($s('sede_id'))) $bad('Selecciona una sede');
      if ($s('nombre') === '' || $s('direccion') === '') $bad('Completa nombre y dirección');
      if (!preg_match('/^[A-Za-z0-9]{1,6}$/', $s('serie'))) $bad('La serie debe tener de 1 a 6 letras o números');
      if ($s('email') !== '' && !filter_var($s('email'), FILTER_VALIDATE_EMAIL)) $bad('E-mail inválido');
      $row = ['sede_id' => $s('sede_id'), 'serie' => strtoupper($s('serie')), 'nombre' => $s('nombre'), 'direccion' => $s('direccion'), 'email' => $s('email') ?: null];
      is_uuid($s('id')) ? Supabase::update('locales', 'id=eq.' . $s('id'), $row) : Supabase::insert('locales', $row);
      json_out(['ok' => true]);

    case 'toggle':
      if (!in_array($s('table'), ['sedes', 'locales'], true) || !is_uuid($s('id'))) $bad('Datos inválidos');
      Supabase::update($s('table'), 'id=eq.' . $s('id'), ['activo' => (bool)($d['activo'] ?? false)]);
      json_out(['ok' => true]);

    case 'stats':
      $hoy = date('Y-m-d'); $o = ['total' => 0, 'pendientes' => 0, 'atendidos' => 0, 'vencidos' => 0];
      foreach (Supabase::select('reclamos?select=estado,fecha_limite&limit=10000') as $x) {
        $o['total']++; $x['estado'] === 'atendido' ? $o['atendidos']++ : $o['pendientes']++;
        if ($x['estado'] === 'pendiente' && $x['fecha_limite'] && $x['fecha_limite'] < $hoy) $o['vencidos']++;
      }
      json_out($o);

    case 'reclamos':
      $q = ['select=*,locales(nombre,serie,sedes(nombre))', 'order=created_at.desc'];
      $page = max(0, (int)($d['page'] ?? 0));
      $q[] = 'limit=21'; $q[] = 'offset=' . ($page * 20);
      if (is_uuid($s('local_id'))) $q[] = 'local_id=eq.' . $s('local_id');
      if (in_array($s('tipo'), ['reclamo', 'queja'], true)) $q[] = 'tipo=eq.' . $s('tipo');
      if (in_array($s('estado'), ['pendiente', 'atendido'], true)) $q[] = 'estado=eq.' . $s('estado');
      if ($s('desde') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $s('desde'))) $q[] = 'fecha=gte.' . $s('desde');
      if ($s('hasta') && preg_match('/^\d{4}-\d{2}-\d{2}$/', $s('hasta'))) $q[] = 'fecha=lte.' . $s('hasta');
      if ($s('q') !== '') { $t = preg_replace('/[^\p{L}\p{N} @.\-]/u', '', $s('q')); $q[] = 'or=' . rawurlencode("(nombre.ilike.*$t*,dni_ce.ilike.*$t*,email.ilike.*$t*)"); }
      $rows = Supabase::select('reclamos?' . implode('&', $q));
      foreach ($rows as &$r) $r['numero'] = Reclamo::numero($r);
      json_out(['rows' => array_slice($rows, 0, 20), 'more' => count($rows) > 20]);

    case 'reclamo_update':
      if (!is_uuid($s('id')) || !in_array($s('estado'), ['pendiente', 'atendido'], true)) $bad('Datos inválidos');
      $fr = preg_match('/^\d{4}-\d{2}-\d{2}$/', $s('fecha_respuesta')) ? $s('fecha_respuesta') : null;
      Supabase::update('reclamos', 'id=eq.' . $s('id'), ['estado' => $s('estado'), 'observaciones' => $s('observaciones') ?: null, 'fecha_respuesta' => $fr]);
      json_out(['ok' => true]);

    case 'reclamo_send':
      $r = is_uuid($s('id')) ? Reclamo::find('id=eq.' . $s('id')) : null;
      if (!$r) $bad('Reclamo no encontrado');
      $c = !empty($d['consumidor']); $p = !empty($d['proveedor']);
      if (!$c && !$p) $bad('Elige al menos un destinatario');
      $ok = Reclamo::notify($r, $c, $p);
      json_out(['ok' => true, 'enviados' => $ok]);

    default: $bad('Acción inválida');
  }
} catch (Throwable $e) { error_log($e->getMessage()); json_out(['error' => $e->getMessage()], 500); }
