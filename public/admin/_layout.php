<?php
require_once __DIR__ . '/../../config/bootstrap.php';

function admin_head(string $title, string $active): void {
  require_admin(false);
  $nav = ['index.php' => ['📋', 'Reclamos'], 'sedes.php' => ['🏢', 'Sedes'], 'locales.php' => ['📍', 'Locales']]; ?>
<!doctype html><html lang="es"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<meta name="csrf" content="<?= csrf() ?>"><title><?= e($title) ?> · Libro de Reclamaciones</title>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:wght@400;600&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="../assets/admin.css"></head>
<body>
<aside id="side">
  <div class="logo">Libro de Reclamaciones<small>Panel administrador</small></div>
  <nav>
    <?php foreach ($nav as $href => [$ico, $label]): ?>
      <a href="<?= $href ?>" class="<?= $href === $active ? 'on' : '' ?>"><span><?= $ico ?></span><?= $label ?></a>
    <?php endforeach; ?>
    <a href="../index.php" target="_blank"><span>🔗</span>Ver formulario</a>
  </nav>
  <div class="user"><strong><?= e($_SESSION['admin']['nombre']) ?></strong><a href="logout.php">Cerrar sesión</a></div>
</aside>
<div class="main">
  <header class="top"><button id="burger" aria-label="Menú">☰</button><h1><?= e($title) ?></h1></header>
  <main class="content">
<?php }

function admin_foot(): void { ?>
  </main></div>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="../assets/admin.js"></script></body></html>
<?php }
