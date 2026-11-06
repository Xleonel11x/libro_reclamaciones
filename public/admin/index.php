<?php require __DIR__ . '/_layout.php'; admin_head('Reclamos', 'index.php'); ?>
<div class="stats" id="stats">
  <div class="stat"><b data-k="total">–</b>Total</div>
  <div class="stat gold"><b data-k="pendientes">–</b>Pendientes</div>
  <div class="stat green"><b data-k="atendidos">–</b>Atendidos</div>
  <div class="stat red"><b data-k="vencidos">–</b>Plazo vencido</div>
</div>
<div class="card">
  <form id="flt" class="filters">
    <input name="q" placeholder="Nombre, DNI/CE o correo">
    <select name="local_id"><option value="">Todos los locales</option></select>
    <select name="tipo"><option value="">Reclamo y queja</option><option value="reclamo">Reclamo</option><option value="queja">Queja</option></select>
    <select name="estado"><option value="">Todos los estados</option><option value="pendiente">Pendiente</option><option value="atendido">Atendido</option></select>
    <input type="date" name="desde" aria-label="Desde"><input type="date" name="hasta" aria-label="Hasta">
    <button class="btn">Filtrar</button>
  </form>
  <div class="tw"><table><thead><tr><th>N°</th><th>Fecha</th><th>Sede / Local</th><th>Consumidor</th><th>Tipo</th><th>Estado</th><th>Límite</th><th></th></tr></thead><tbody id="rows"></tbody></table></div>
  <div class="pager"><button class="btn ghost" id="prev">← Anterior</button><span id="pg"></span><button class="btn ghost" id="next">Siguiente →</button></div>
</div>
<dialog id="det"><form id="fd">
  <h2 id="dt"></h2><div id="dbody"></div>
  <div class="grid2">
    <label class="f">Estado<select name="estado"><option value="pendiente">Pendiente</option><option value="atendido">Atendido</option></select></label>
    <label class="f">Fecha de comunicación de la respuesta<input type="date" name="fecha_respuesta"></label>
  </div>
  <label class="f">Observaciones y acciones adoptadas por el proveedor<textarea name="observaciones" rows="3"></textarea></label>
  <div class="send"><b>Enviar PDF por e-mail a:</b>
    <label><input type="checkbox" id="sc" checked> Consumidor</label>
    <label><input type="checkbox" id="sp"> Proveedor (local / sede)</label>
    <button type="button" class="btn ghost" id="send">Enviar</button></div>
  <p class="msg" id="msg"></p>
  <div class="actions"><button class="btn">Guardar cambios</button><a class="btn ghost" id="pdf" target="_blank">Ver PDF</a><button type="button" class="btn ghost" id="close">Cerrar</button></div>
</form></dialog>
<?php admin_foot(); ?>
