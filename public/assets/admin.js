$(function () {
  const csrf = $('meta[name=csrf]').attr('content');
  const api = (a, d = {}) => $.ajax({ url: '/admin/api.php', method: 'POST', contentType: 'application/json',
    headers: { 'X-CSRF': csrf }, data: JSON.stringify({ a, ...d }) })
    .fail(x => $('#msg').text(x.responseJSON?.error || 'Error inesperado'));
  const esc = s => $('<i>').text(s ?? '').html();
  const form = $f => Object.fromEntries(new FormData($f[0]));
  const fecha = d => d ? d.split('-').reverse().join('/') : '';
  const estado = (on, a, b) => `<span class="tag ${on ? 'on' : 'off'}">${on ? a : b}</span>`;
  const toggle = (t, x) => `<button class="btn ghost sm" data-tg="${t}" data-id="${x.id}" data-on="${!x.activo}">${x.activo ? 'Desactivar' : 'Activar'}</button>`;

  $('#burger').on('click', () => $('body').toggleClass('nav-open'));
  $(document).on('click', '[data-tg]', function () {
    api('toggle', { table: $(this).data('tg'), id: $(this).data('id'), activo: $(this).data('on') }).done(() => location.reload());
  });
  const save = (act, $f, reload) => $f.on('submit', ev => { ev.preventDefault(); $('#msg').text('');
    api(act, form($f)).done(() => { $f[0].reset(); $f.find('[name=id]').val(''); reload(); }); });
  const fill = ($f, x, keys) => keys.forEach(k => $f.find('[name=' + k + ']').val(x[k] ?? ''));

  /* ---------- Sedes ---------- */
  if ($('#fSede').length) {
    let sedes = [];
    const load = () => api('sedes').done(r => { sedes = r;
      $('#tSedes').html(r.map(x => `<tr><td><b>${esc(x.nombre)}</b></td><td>${esc(x.razon_social)}</td><td>${x.ruc}</td><td>${esc(x.domicilio)}</td><td>${esc(x.email)}</td>
        <td>${estado(x.activo, 'Activa', 'Inactiva')}</td><td><button class="btn ghost sm" data-ed="${x.id}">Editar</button> ${toggle('sedes', x)}</td></tr>`).join('')
        || '<tr><td colspan="7">Aún no hay sedes. Agrega la primera con el formulario.</td></tr>'); });
    save('sede_save', $('#fSede'), load);
    $(document).on('click', '[data-ed]', function () {
      fill($('#fSede'), sedes.find(s => s.id == $(this).data('ed')), ['id', 'nombre', 'razon_social', 'ruc', 'domicilio', 'email']);
      window.scrollTo({ top: 0, behavior: 'smooth' }); });
    load();
  }

  /* ---------- Locales ---------- */
  if ($('#fLocal').length) {
    let locales = [];
    const load = () => $.when(api('sedes'), api('locales')).done((s, l) => { locales = l[0];
      $('#fLocal [name=sede_id]').html('<option value="">Selecciona una sede</option>' + s[0].map(x => `<option value="${x.id}">${esc(x.nombre)}</option>`).join(''));
      $('#tLocales').html(locales.map(x => `<tr><td><b>${esc(x.serie)}</b></td><td>${esc(x.nombre)}</td><td>${esc(x.sedes.nombre)}</td><td>${esc(x.direccion)}</td><td>${esc(x.email)}</td>
        <td>${x.contador}</td><td>${estado(x.activo, 'Activo', 'Inactivo')}</td><td><button class="btn ghost sm" data-ed="${x.id}">Editar</button> ${toggle('locales', x)}</td></tr>`).join('')
        || '<tr><td colspan="8">Aún no hay locales. Crea una sede y luego su primer local.</td></tr>'); });
    save('local_save', $('#fLocal'), load);
    $(document).on('click', '[data-ed]', function () {
      fill($('#fLocal'), locales.find(s => s.id == $(this).data('ed')), ['id', 'sede_id', 'serie', 'nombre', 'direccion', 'email']);
      window.scrollTo({ top: 0, behavior: 'smooth' }); });
    load();
  }

  /* ---------- Reclamos ---------- */
  if ($('#flt').length) {
    let page = 0, rows = [], cur = null;
    api('stats').done(s => $('#stats [data-k]').each(function () { $(this).text(s[$(this).data('k')]); }));
    api('locales').done(l => $('#flt [name=local_id]').append(l.map(x => `<option value="${x.id}">${esc(x.serie)} · ${esc(x.nombre)}</option>`)));
    const load = () => api('reclamos', { ...form($('#flt')), page }).done(r => { rows = r.rows;
      $('#rows').html(rows.map((x, i) => `<tr><td><b>${x.numero}</b></td><td>${fecha(x.fecha)}</td><td>${esc(x.locales.sedes.nombre)}<br><small>${esc(x.locales.nombre)}</small></td>
        <td>${esc(x.nombre)}<br><small>${esc(x.dni_ce)}</small></td><td>${x.tipo}</td>
        <td><span class="tag ${x.estado == 'pendiente' ? 'pen' : 'ate'}">${x.estado}</span></td><td>${fecha(x.fecha_limite)}</td>
        <td><button class="btn ghost sm" data-i="${i}">Ver</button></td></tr>`).join('') || '<tr><td colspan="8">Sin registros para estos filtros.</td></tr>');
      $('#pg').text('Página ' + (page + 1)); $('#prev').prop('disabled', page == 0); $('#next').prop('disabled', !r.more); });
    $('#flt').on('submit', ev => { ev.preventDefault(); page = 0; load(); });
    $('#prev').on('click', () => { page--; load(); }); $('#next').on('click', () => { page++; load(); });
    $('#rows').on('click', '[data-i]', function () {
      cur = rows[$(this).data('i')]; $('#msg').text('');
      $('#dt').text(`${cur.tipo.toUpperCase()} N° ${cur.numero}`);
      $('#dbody').html(`<p><b>${esc(cur.nombre)}</b> · ${esc(cur.dni_ce)}<br>${esc(cur.domicilio)}<br>${esc(cur.telefono) || 'Sin teléfono'} · ${esc(cur.email) || 'Sin correo'}
        ${cur.menor_edad ? '<br>Apoderado: ' + esc(cur.apoderado) : ''}</p>
        <p><b>${cur.bien_tipo}</b> · S/ ${Number(cur.monto).toFixed(2)}<br>${esc(cur.descripcion)}</p>
        <p><b>Detalle:</b> ${esc(cur.detalle)}<br><b>Pedido:</b> ${esc(cur.pedido)}</p>`);
      $('#fd [name=estado]').val(cur.estado); $('#fd [name=observaciones]').val(cur.observaciones ?? '');
      $('#fd [name=fecha_respuesta]').val(cur.fecha_respuesta ?? ''); $('#pdf').attr('href', 'pdf.php?id=' + cur.id);
      $('#sc').prop('disabled', !cur.email).prop('checked', !!cur.email);
      $('#det')[0].showModal(); });
    $('#close').on('click', () => $('#det')[0].close());
    $('#fd').on('submit', ev => { ev.preventDefault();
      api('reclamo_update', { id: cur.id, ...form($('#fd')) }).done(() => { $('#det')[0].close(); load(); }); });
    $('#send').on('click', function () { const $b = $(this).prop('disabled', true).text('Enviando…'); $('#msg').text('');
      api('reclamo_send', { id: cur.id, consumidor: $('#sc').is(':checked'), proveedor: $('#sp').is(':checked') })
        .done(r => $('#msg').css('color', 'var(--green)').text(r.enviados.length ? 'Enviado a: ' + r.enviados.join(', ') : 'No hay destinatarios con correo registrado.'))
        .always(() => $b.prop('disabled', false).text('Enviar')); });
    load();
  }
});
