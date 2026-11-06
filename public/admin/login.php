<?php require_once __DIR__ . '/../../config/bootstrap.php';
$err = '';
$tokenParaCliente = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!hash_equals(csrf(), $_POST['csrf'] ?? '')) $err = 'Sesión inválida, recarga la página.';
  elseif (($_SESSION['fails'] ?? 0) >= 5 && time() - ($_SESSION['fail_t'] ?? 0) < 300) $err = 'Demasiados intentos. Espera 5 minutos.';
  else {
    $u = Supabase::select('admins?select=*&email=eq.' . urlencode(strtolower(trim($_POST['email'] ?? ''))) . '&limit=1')[0] ?? null;
    if ($u && password_verify($_POST['password'] ?? '', $u['password_hash'])) {
      // Login exitoso - usar JWT para Vercel serverless
      $csrf = $_SESSION['csrf'] ?? bin2hex(random_bytes(16));
      $token = JWT::generate(['admin' => ['id' => $u['id'], 'nombre' => $u['nombre']], 'csrf' => $csrf], 86400);
      JWT::setAuthCookie($token);
      // Guardar en sesión para compatibilidad local
      $_SESSION['admin'] = ['id' => $u['id'], 'nombre' => $u['nombre'], 'jwt' => $token];
      $_SESSION['csrf'] = $csrf;
      $_SESSION['jwt_token'] = $token;
      // Token para pasar al cliente (necesario para Vercel serverless)
      $tokenParaCliente = $token;
    }
    $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1; $_SESSION['fail_t'] = time();
    $err = 'Correo o contraseña incorrectos.';
  }
}
?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Acceso administrador</title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css"><script src="https://code.jquery.com/jquery-3.7.1.min.js"></script></head>
<body class="login"><form method="post" class="card login-card">
  <div class="card-h">Acceso al panel</div>
  <div class="card-b">
    <input type="hidden" name="csrf" value="<?= csrf() ?>">
    <label class="f">Correo<input type="email" name="email" required autofocus></label>
    <label class="f">Contraseña<input type="password" name="password" required></label>
    <p class="msg"><?= e($err) ?></p>
    <button class="btn red">Ingresar</button>
  </div>
<?php if ($tokenParaCliente): ?>
<script>
$(function() {
  // Guardar token en localStorage para uso en peticiones AJAX posteriores
  localStorage.setItem('auth_token', '<?= addslashes($tokenParaCliente) ?>');
  localStorage.removeItem('auth_token_expired'); // Limpiar flag de expiración
  // Redirigir al panel principal
  window.location.href = 'index.php';
});
</script>
<?php endif; ?>
</body></html>