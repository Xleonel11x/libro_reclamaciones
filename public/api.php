<?php
require_once __DIR__ . '/../config/bootstrap.php';
$act = $_GET['action'] ?? '';
$in  = json_decode(file_get_contents('php://input'), true) ?? [];
$ok   = fn($d = []) => json_out(['success' => true, 'data' => $d]);
$fail = fn($m, $c = 422) => json_out(['success' => false, 'message' => $m], $c);
$s    = fn($k) => trim((string)($in[$k] ?? ''));
if ($_SERVER['REQUEST_METHOD'] === 'POST') check_csrf();

try {
  switch ($act) {
    case 'sedes':
      $ok(Supabase::select('sedes?select=id,nombre,razon_social,ruc,domicilio&activo=eq.true&order=nombre'));

    case 'locales':
      if (!is_uuid($_GET['sede'] ?? '')) $fail('Sede inválida');
      $ok(Supabase::select('locales?select=id,nombre,direccion&activo=eq.true&order=nombre&sede_id=eq.' . $_GET['sede']));

    case 'previsualizacion': // número que se asignará (el definitivo lo fija la base de datos al guardar)
      $l = is_uuid($_GET['local'] ?? '') ? (Supabase::select('locales?select=serie,contador&activo=eq.true&id=eq.' . $_GET['local'])[0] ?? null) : null;
      if (!$l) $fail('Local no disponible');
      $c = (int)$l['contador'] + 1; $serie = strtoupper($l['serie']);
      $ok(['serie' => $serie, 'correlativo' => $c, 'numero' => sprintf('%s-%04d', $serie, $c), 'anio' => (int)date('Y')]);

    case 'guardar':
      $err = [];
      foreach (['nombre', 'dni_ce', 'domicilio', 'detalle', 'pedido'] as $k) if ($s($k) === '') $err[] = $k;
      if ($err) $fail('Completa los campos requeridos');
      if (!is_uuid($s('local_id'))) $fail('Selecciona un local');
      if (!preg_match('/^[A-Za-z0-9]{6,20}$/', $s('dni_ce'))) $fail('DNI / CE inválido');
      if ($s('email') !== '' && !filter_var($s('email'), FILTER_VALIDATE_EMAIL)) $fail('Correo electrónico inválido');
      $monto = $s('monto_reclamado') === '' ? 0 : $s('monto_reclamado');
      if (!is_numeric($monto) || $monto < 0) $fail('Monto inválido');
      if (!in_array($s('tipo_bien'), ['producto', 'servicio'], true) || !in_array($s('tipo_reclamo'), ['reclamo', 'queja'], true)) $fail('Datos inválidos');
      if (!Supabase::select('locales?select=id,sedes!inner(activo)&activo=eq.true&sedes.activo=eq.true&id=eq.' . $s('local_id'))) $fail('El local seleccionado no está disponible');
      $hoy = date('Y-m-d');
      $row = Supabase::insert('reclamos', [
        'local_id' => $s('local_id'), 'fecha' => $hoy, 'fecha_limite' => Reclamo::fechaLimite($hoy),
        'nombre' => $s('nombre'), 'dni_ce' => $s('dni_ce'), 'domicilio' => $s('domicilio'),
        'telefono' => $s('telefono') ?: null, 'email' => strtolower($s('email')) ?: null,
        'menor_edad' => $s('apoderado') !== '', 'apoderado' => $s('apoderado') ?: null,
        'bien_tipo' => $s('tipo_bien'), 'monto' => (float)$monto, 'descripcion' => $s('descripcion'),
        'tipo' => $s('tipo_reclamo'), 'detalle' => $s('detalle'), 'pedido' => $s('pedido'),
      ]);
      $r = Reclamo::find('id=eq.' . $row['id']);
      try { Reclamo::notify($r, false, true); } catch (Throwable $e) { error_log('Aviso al proveedor: ' . $e->getMessage()); }
      $ok(['numero' => Reclamo::numero($r), 'serie' => strtoupper($r['locales']['serie']), 'correlativo' => $r['correlativo'],
           'fecha' => date('d/m/Y'), 'token' => $r['token']]);

    case 'enviar_correo': // copia al consumidor (una sola vez por reclamo)
      $r = is_uuid($s('token')) ? Reclamo::find('token=eq.' . $s('token')) : null;
      if (!$r) $fail('Reclamo no encontrado', 404);
      if (empty($r['email'])) $fail('El reclamo no tiene correo registrado');
      if (!$r['email_enviado']) Reclamo::notify($r, true, false);
      $ok(['email' => $r['email']]);

    default: $fail('Acción inválida', 400);
  }
} catch (Throwable $e) { error_log($e->getMessage()); $fail('Ocurrió un error. Inténtalo nuevamente.', 500); }
