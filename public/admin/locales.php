<?php require __DIR__ . '/_layout.php'; admin_head('Locales', 'locales.php'); ?>
<div class="card"><div class="card-h">Datos del local</div><div class="card-b">
  <form id="fLocal" class="filters"><input type="hidden" name="id">
    <select name="sede_id" required></select>
    <input name="serie" placeholder="Serie (ej. L001)" required maxlength="6">
    <input name="nombre" placeholder="Nombre del local" required>
    <input name="direccion" placeholder="Dirección" required>
    <input name="email" type="email" placeholder="E-mail del local (opcional)">
    <button class="btn red">Guardar local</button><button type="reset" class="btn ghost">Nuevo</button>
  </form><p class="msg" id="msg"></p></div></div>
<div class="card"><div class="tw"><table><thead><tr><th>Serie</th><th>Local</th><th>Sede</th><th>Dirección</th><th>E-mail</th><th>Hojas</th><th>Estado</th><th></th></tr></thead><tbody id="tLocales"></tbody></table></div></div>
<?php admin_foot(); ?>
