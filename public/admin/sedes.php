<?php require __DIR__ . '/_layout.php'; admin_head('Sedes', 'sedes.php'); ?>
<div class="card"><div class="card-h">Datos de la sede (proveedor)</div><div class="card-b">
  <form id="fSede" class="filters"><input type="hidden" name="id">
    <input name="nombre" placeholder="Nombre de la sede" required>
    <input name="razon_social" placeholder="Razón social" required>
    <input name="ruc" placeholder="RUC (11 dígitos)" required maxlength="11" inputmode="numeric">
    <input name="domicilio" placeholder="Domicilio" required>
    <input name="email" type="email" placeholder="E-mail de avisos (opcional)">
    <button class="btn red">Guardar sede</button><button type="reset" class="btn ghost">Nueva</button>
  </form><p class="msg" id="msg"></p></div></div>
<div class="card"><div class="tw"><table><thead><tr><th>Sede</th><th>Razón social</th><th>RUC</th><th>Domicilio</th><th>E-mail</th><th>Estado</th><th></th></tr></thead><tbody id="tSedes"></tbody></table></div></div>
<?php admin_foot(); ?>
