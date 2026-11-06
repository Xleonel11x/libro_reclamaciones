<?php
// Uso: php scripts/create_admin.php correo@x.com "Nombre" "ClaveSegura"
require __DIR__ . '/../config/bootstrap.php';
if (PHP_SAPI !== 'cli' || $argc < 4) exit("Uso: php scripts/create_admin.php email nombre clave\n");
Supabase::insert('admins', ['email' => strtolower($argv[1]), 'nombre' => $argv[2],
  'password_hash' => password_hash($argv[3], PASSWORD_DEFAULT)]);
echo "Administrador creado.\n";
