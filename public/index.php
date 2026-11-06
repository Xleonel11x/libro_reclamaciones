<?php require_once __DIR__ . '/../config/bootstrap.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8"/>
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<meta name="csrf" content="<?= csrf() ?>"/>
<title>Libro de Reclamaciones — GRUPO SIAS SAC</title>
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link href="https://fonts.googleapis.com/css2?family=EB+Garamond:ital,wght@0,400;0,600;1,400&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet"/>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<link rel="stylesheet" href="assets/public.css"/>
</head>
<body>

<!-- Header -->
<header class="site-header">
  <div class="brand">
    GRUPO SIAS SAC
    <small>RUC 20445475549 · Av. José Gálvez Nro. 698</small>
  </div>
  <span class="header-badge">Libro de Reclamaciones</span>
</header>

<main class="container">

  <!-- ── PASO 1: Sede / Local ────────────────────────────────────── -->
  <div id="paso-sede">
    <div class="paso-header">
      <div class="paso-step active" id="step1">
        <span class="bubble">1</span>Seleccionar Local
      </div>
      <div class="paso-arrow"></div>
      <div class="paso-step" id="step2">
        <span class="bubble">2</span>Llenar Reclamación
      </div>
    </div>

    <div class="selector-card">
      <div class="selector-card-header">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
          <polyline points="9 22 9 12 15 12 15 22"/>
        </svg>
        Seleccione la Sede y Local de Atención
      </div>

      <div class="selector-card-body">
        <div class="selector-grid">

          <!-- Sede -->
          <div>
            <label class="select-label" for="sel-sede">Sede <span class="req">*</span></label>
            <select id="sel-sede" class="styled-select" disabled>
              <option value="">— Cargando sedes… —</option>
            </select>
            <div class="local-loading" id="sede-loading">
              <span class="mini-spin"></span> Cargando sedes…
            </div>
            <div class="local-error" id="sede-error">No se pudieron cargar las sedes.</div>
          </div>

          <!-- Local -->
          <div>
            <label class="select-label" for="sel-local">Local <span class="req">*</span></label>
            <select id="sel-local" class="styled-select" disabled>
              <option value="">— Primero seleccione una sede —</option>
            </select>
            <div class="local-loading" id="local-loading">
              <span class="mini-spin"></span> Cargando locales…
            </div>
            <div class="local-error" id="local-error">No se pudieron cargar los locales.</div>
          </div>

        </div>

        <!-- Chip confirmación -->
        <div class="local-chip" id="local-chip">
          <span class="chip-icon">📍</span>
          <div class="chip-text">
            <strong id="chip-nombre">—</strong>
            <span id="chip-dir">—</span>
          </div>
        </div>

        <!-- Chip número de hoja (aparece al obtener correlativo) -->
        <div class="local-chip" id="chip-numero-wrap" style="display:none;background:#fdf3e7;border-color:#e8c07a;color:#7a4f00;margin-top:8px">
          <span class="chip-icon">🏷️</span>
          <div class="chip-text">
            <strong>Número de Hoja asignado</strong>
            <span id="chip-numero" style="font-size:1rem;font-weight:700;letter-spacing:.05em">—</span>
          </div>
        </div>

        <!-- Botón Siguiente -->
        <div class="btn-siguiente-wrap">
          <button type="button" id="btn-siguiente">
            Continuar con la Reclamación
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="9 18 15 12 9 6"/>
            </svg>
          </button>
        </div>
      </div>
    </div>
  </div><!-- /paso-sede -->

  <!-- ── Success Screen ─────────────────────────────────────────── -->
  <div class="sheet" id="success-screen">
    <div class="checkmark">✓</div>
    <h2>Reclamación Registrada</h2>
    <p>Tu reclamación ha sido ingresada al sistema correctamente.</p>
    <p>Recibirás una respuesta en un plazo máximo de <strong>15 días hábiles</strong>.</p>
    <div><span class="numero-hoja">N° <span id="numero-generado">—</span></span></div>

    <!-- Aviso correo -->
    <p id="aviso-email" style="display:none;margin-top:10px;font-size:.82rem;color:var(--ink-lt)">
      Se enviará una copia al correo: <strong id="aviso-email-dir">—</strong>
    </p>

    <div style="display:flex;gap:12px;justify-content:center;flex-wrap:wrap;margin-top:20px">

      <!-- Botón principal: descarga + envía email -->
      <button class="btn-pdf" id="btn-descargar-pdf">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
          <polyline points="7 10 12 15 17 10"/>
          <line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        <span class="pdf-btn-text">Descargar PDF y Enviar al Correo</span>
      </button>

      <!-- Botón nuevo: INACTIVO hasta que se descargue el PDF -->
      <button class="btn-nuevo" id="btn-nuevo" disabled title="Primero descargue el PDF">
        Registrar otra reclamación
      </button>
    </div>

    <!-- Estado del envío -->
    <div id="email-status" style="display:none;margin-top:14px;padding:10px 20px;border-radius:4px;font-size:.82rem;text-align:center;max-width:480px;margin-left:auto;margin-right:auto"></div>
  </div>

  <!-- ── Formulario (oculto hasta seleccionar local) ────────────── -->
  <form id="form-reclamacion" class="sheet" novalidate style="display:none;margin-top:24px">

    <!-- Title Bar -->
    <div class="sheet-titlebar">
      <div class="main-title">Libro de Reclamaciones</div>
      <div class="sub-title">
        <span>Hoja de Reclamación</span>
        <strong>N° —</strong>
      </div>
    </div>

    <!-- Proveedor -->
    <div class="provider-row">
      <div class="pfield"><label>Proveedor</label><span>GRUPO SIAS SAC</span></div>
      <div class="pfield"><label>RUC</label><span>20445475549</span></div>
      <div class="pfield"><label>Domicilio</label><span>Av. José Gálvez Nro. 698</span></div>
    </div>

    <!-- Resumen sede/local -->
    <div class="sede-resumen">
      <div class="sr-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
        </svg>
        Sede: <strong id="resumen-sede">—</strong>
      </div>
      <span class="sr-sep">|</span>
      <div class="sr-item">
        <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round">
          <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
        </svg>
        Local: <strong id="resumen-local">—</strong>
      </div>
      <button type="button" class="btn-cambiar" id="btn-cambiar">← Cambiar</button>
    </div>

    <!-- Sección 1: Consumidor -->
    <div class="section">
      <div class="section-header"><span class="sec-num">1</span> Identificación del Consumidor Reclamante</div>
      <div class="section-body">
        <div class="field-row cols-2-1">
          <div class="field">
            <label for="nombre">Nombre y Apellidos <span class="req">*</span></label>
            <input type="text" id="nombre" name="nombre" placeholder="Nombre completo" autocomplete="name"/>
            <span class="err-msg" id="err-nombre">Este campo es requerido.</span>
          </div>
          <div class="field">
            <label for="dni_ce">DNI / CE <span class="req">*</span></label>
            <input type="text" id="dni_ce" name="dni_ce" placeholder="Ej. 12345678" maxlength="20"/>
            <span class="err-msg" id="err-dni_ce">Este campo es requerido.</span>
          </div>
        </div>
        <div class="field-row cols-1">
          <div class="field">
            <label for="domicilio">Domicilio <span class="req">*</span></label>
            <input type="text" id="domicilio" name="domicilio" placeholder="Dirección completa" autocomplete="street-address"/>
            <span class="err-msg" id="err-domicilio">Este campo es requerido.</span>
          </div>
        </div>
        <div class="field-row cols-2">
          <div class="field">
            <label for="telefono">Teléfono</label>
            <input type="tel" id="telefono" name="telefono" placeholder="Ej. 999 888 777" autocomplete="tel"/>
          </div>
          <div class="field">
            <label for="email">Correo Electrónico</label>
            <input type="email" id="email" name="email" placeholder="correo@ejemplo.com" autocomplete="email"/>
          </div>
        </div>
        <div class="field-row cols-1" id="row-apoderado" style="display:none">
          <div class="field">
            <label for="apoderado">Nombre del Padre, Madre o Apoderado</label>
            <input type="text" id="apoderado" name="apoderado" placeholder="Solo si el reclamante es menor de edad"/>
          </div>
        </div>
        <div style="margin-top:6px">
          <label style="display:flex;align-items:center;gap:8px;cursor:pointer;font-size:.82rem;color:var(--ink-mid)">
            <input type="checkbox" id="check-menor" style="width:15px;height:15px;cursor:pointer;accent-color:var(--red)"/>
            El reclamante es menor de edad
          </label>
        </div>
      </div>
    </div>

    <!-- Sección 2: Bien -->
    <div class="section">
      <div class="section-header"><span class="sec-num">2</span> Identificación del Bien Contratado</div>
      <div class="section-body">
        <div class="field-row cols-1">
          <div class="field">
            <label>Tipo de Bien <span class="req">*</span></label>
            <div class="toggle-group">
              <button type="button" class="toggle-btn active" data-val="producto">Producto</button>
              <button type="button" class="toggle-btn" data-val="servicio">Servicio</button>
            </div>
            <input type="hidden" id="tipo_bien" name="tipo_bien" value="producto"/>
          </div>
        </div>
        <div class="field-row cols-2">
          <div class="field">
            <label for="descripcion">Descripción del Producto / Servicio</label>
            <input type="text" id="descripcion" name="descripcion" placeholder="Ej. Seguro vehicular"/>
          </div>
          <div class="field">
            <label for="monto_reclamado">Monto Reclamado (S/)</label>
            <input type="number" id="monto_reclamado" name="monto_reclamado" placeholder="0.00" min="0" step="0.01"/>
          </div>
        </div>
      </div>
    </div>

    <!-- Sección 3: Detalle -->
    <div class="section">
      <div class="section-header"><span class="sec-num">3</span> Detalle de la Reclamación y Pedido del Consumidor</div>
      <div class="section-body">
        <div class="field-row cols-1">
          <div class="field">
            <label>Tipo <span class="req">*</span></label>
            <div class="radio-group" id="tipo-reclamo-group">
              <label class="radio-label checked" data-val="reclamo">
                <input type="radio" name="tipo_reclamo" value="reclamo" checked/>
                <span class="dot"></span>Reclamo <sup>1</sup>
              </label>
              <label class="radio-label" data-val="queja">
                <input type="radio" name="tipo_reclamo" value="queja"/>
                <span class="dot"></span>Queja <sup>2</sup>
              </label>
            </div>
          </div>
        </div>
        <div class="inline-divider">Descripción</div>
        <div class="field-row cols-1">
          <div class="field">
            <label for="detalle">Detalle de la Reclamación <span class="req">*</span></label>
            <textarea id="detalle" name="detalle" rows="5" placeholder="Describa con detalle el motivo de su reclamación o queja…"></textarea>
            <span class="err-msg" id="err-detalle">Este campo es requerido.</span>
          </div>
        </div>
        <div class="field-row cols-1">
          <div class="field">
            <label for="pedido">Pedido del Consumidor <span class="req">*</span></label>
            <textarea id="pedido" name="pedido" rows="3" placeholder="Indique qué solución o acción solicita al proveedor…"></textarea>
            <span class="err-msg" id="err-pedido">Este campo es requerido.</span>
          </div>
        </div>
        <div class="field-row cols-1">
          <div class="field">
            <label for="fecha">Fecha de la Reclamación</label>
            <input type="date" id="fecha" name="fecha" readonly/>
          </div>
        </div>
      </div>
    </div>

    <!-- Footnotes -->
    <div class="footnotes">
      <div><sup>1</sup> <strong>RECLAMO:</strong> Disconformidad relacionada a los productos o servicios.</div>
      <div><sup>2</sup> <strong>QUEJA:</strong> Disconformidad no relacionada a los productos o servicios; o, malestar o descontento respecto a la atención al público.</div>
    </div>

    <!-- Footer -->
    <div class="sheet-footer">
      <p class="legal-note">
        * La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el <strong>INDECOPI</strong>.<br/>
        * El proveedor debe dar respuesta en un plazo no mayor a <strong>quince (15) días hábiles</strong>, el cual es improrrogable.
      </p>
      <button type="submit" class="btn-submit" id="btn-submit">
        <div class="spinner"></div>
        <span class="btn-text">Enviar Reclamación</span>
      </button>
    </div>

  </form>
</main>

<div id="toast"></div>

<script>
$(function () {

  $.ajaxSetup({ headers: { 'X-CSRF': $('meta[name=csrf]').attr('content') } });

  const API = 'api.php';
  const hoy = new Date().toISOString().split('T')[0];
  $('#fecha').val(hoy);

  // Estado global
  let selectedSede   = { id: '', nombre: '' };
  let selectedLocal  = { id: '', nombre: '', direccion: '' };
  let selectedNumero = { serie: '', correlativo: 0, numero: '', anio: 0 };
  let lastReclamo    = {}; // datos completos del último reclamo registrado

  // ── Helpers UI ──────────────────────────────────────────────────
  function toast(msg, type = 'success') {
    $('#toast').text(msg).removeClass('success error').addClass(type + ' show');
    setTimeout(() => $('#toast').removeClass('show'), 4000);
  }

  function setLoading($sel, $loader, $err, on) {
    $loader.css('display', on ? 'flex' : 'none');
    $err.hide();
    $sel.prop('disabled', on);
  }

  // Actualiza cabecera y fila de proveedor según la sede elegida
  function setProveedor(s) {
    const $b = $('.brand'); $b.contents().first()[0].nodeValue = '\n    ' + s.razon_social + '\n    ';
    $b.find('small').text('RUC ' + s.ruc + ' · ' + s.domicilio);
    const $p = $('.provider-row .pfield span');
    $p.eq(0).text(s.razon_social); $p.eq(1).text(s.ruc); $p.eq(2).text(s.domicilio);
  }

  // ── PASO 1: Cargar Sedes (Supabase vía PHP) ────────────────────────────
  function cargarSedes() {
    const $sel = $('#sel-sede');
    const $loader = $('#sede-loading');
    const $err    = $('#sede-error');

    $loader.css('display', 'flex');
    $sel.prop('disabled', true).html('<option value="">— Cargando… —</option>');

    $.ajax({
      url: API,
      method: 'GET',
      data: { action: 'sedes' },
      dataType: 'json',
      success: function (res) {
        $loader.hide();
        if (!res.success || !res.data.length) {
          $err.show();
          return;
        }
        let opts = '<option value="">— Seleccione una sede —</option>';
        window.SEDES = res.data;
        res.data.forEach(function (s) {
          opts += `<option value="${s.id}">${s.nombre}</option>`;
        });
        $sel.html(opts).prop('disabled', false);
      },
      error: function () {
        $loader.hide();
        $err.show();
        // Reintentar en 5 s
        setTimeout(cargarSedes, 5000);
      }
    });
  }

  // ── Cambio de Sede → cargar Locales desde MySQL ─────────────────
  $('#sel-sede').on('change', function () {
    const sedeId = $(this).val();
    const sedeNombre = $(this).find('option:selected').text();

    selectedSede  = { id: sedeId, nombre: sedeNombre };
    const sd = (window.SEDES || []).find(x => x.id === sedeId); if (sd) setProveedor(sd);
    selectedLocal = { id: '', nombre: '', direccion: '' };

    const $local  = $('#sel-local');
    const $loader = $('#local-loading');
    const $err    = $('#local-error');

    $('#local-chip').hide();
    $('#btn-siguiente').removeClass('visible');

    if (!sedeId) {
      $local.prop('disabled', true).html('<option value="">— Primero seleccione una sede —</option>');
      return;
    }

    setLoading($local, $loader, $err, true);
    $local.html('<option value="">— Cargando locales… —</option>');

    $.ajax({
      url: API,
      method: 'GET',
      data: { action: 'locales', sede: sedeId },
      dataType: 'json',
      success: function (res) {
        setLoading($local, $loader, $err, false);
        if (!res.success || !res.data.length) {
          $err.text('No hay locales disponibles para esta sede.').show();
          $local.html('<option value="">— Sin locales disponibles —</option>');
          return;
        }
        let opts = '<option value="">— Seleccione un local —</option>';
        res.data.forEach(function (l) {
          opts += `<option value="${l.id}" data-nombre="${l.nombre}" data-dir="${l.direccion}">${l.nombre}</option>`;
        });
        $local.html(opts).prop('disabled', false);
      },
      error: function (xhr) {
        setLoading($local, $loader, $err, false);
        const msg = xhr.responseJSON?.message || 'Error al cargar los locales.';
        $err.text(msg).show();
        $local.html('<option value="">— Error al cargar —</option>');
      }
    });
  });

  // ── Cambio de Local → chip + botón Siguiente ────────────────────
  $('#sel-local').on('change', function () {
    const $opt = $(this).find('option:selected');
    const id   = $(this).val();

    if (!id) {
      selectedLocal = { id: '', nombre: '', direccion: '' };
      $('#local-chip').hide();
      $('#btn-siguiente').removeClass('visible');
      return;
    }

    selectedLocal = {
      id:        id,
      nombre:    $opt.data('nombre'),
      direccion: $opt.data('dir'),
    };

    $('#chip-nombre').text(selectedLocal.nombre);
    $('#chip-dir').text(selectedLocal.direccion);
    $('#local-chip').css('display', 'flex');
    $('#btn-siguiente').addClass('visible');
  });

  // ── Botón Siguiente → obtener serie+correlativo y revelar formulario ──
  $('#btn-siguiente').on('click', function () {
    if (!selectedSede.id || !selectedLocal.id) return;

    const $btn = $(this);
    $btn.prop('disabled', true).text('Cargando…');

    // Llamada AJAX: obtener serie y número correlativo del local
    $.ajax({
      url: 'api.php',
      method: 'GET',
      data: { action: 'previsualizacion', local: selectedLocal.id },
      dataType: 'json',
      success: function (res) {
        $btn.prop('disabled', false).addClass('visible');
        $btn.html('Continuar con la Reclamación <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>');

        if (!res.success) { toast(res.message || 'No se pudo obtener el número.', 'error'); return; }

        // Guardar datos del número en estado global
        selectedNumero = {
          serie:       res.data.serie,
          correlativo: res.data.correlativo,
          numero:      res.data.numero,
          anio:        res.data.anio,
        };

        // Actualizar encabezado del formulario
        $('#resumen-sede').text(selectedSede.nombre);
        $('#resumen-local').text(selectedLocal.nombre);
        $('.sheet-titlebar .sub-title strong').html(
          'N° <span class="hoja-serie">' + res.data.serie + '</span>'
          + '-<span class="hoja-correlativo">' + String(res.data.correlativo).padStart(4,'0') + '</span>'
        );

        // Mostrar badge informativo en el chip
        $('#chip-numero').text(res.data.numero).parent().css('display','flex');

        // Revelar formulario con animación
        $('#paso-sede').slideUp(300, function () {
          const $form = $('#form-reclamacion');
          $form.addClass('slide-down').fadeIn(350, function () {
            $form.removeClass('slide-down');
            $('html,body').animate({ scrollTop: $form.offset().top - 20 }, 300);
          });
        });

        $('#step1').removeClass('active').addClass('done').find('.bubble').html('✓');
        $('#step2').addClass('active');
      },
      error: function (xhr) {
        $btn.prop('disabled', false);
        toast(xhr.responseJSON?.message || 'Error al obtener el número de hoja.', 'error');
      }
    });
  });

  // ── Botón Cambiar → volver al Paso 1 ────────────────────────────
  $('#btn-cambiar').on('click', function () {
    $('#form-reclamacion').fadeOut(250, function () {
      // Resetear número
      selectedNumero = { serie: '', correlativo: 0, numero: '', anio: 0 };
      $('#chip-numero-wrap').hide();
      $('.sheet-titlebar .sub-title strong').html('N° —');
      $('#paso-sede').slideDown(300, function () {
        $('html,body').animate({ scrollTop: 0 }, 200);
      });
    });
    $('#step1').addClass('active').removeClass('done').find('.bubble').text('1');
    $('#step2').removeClass('active');
  });

  // ── Toggle menor de edad ─────────────────────────────────────────
  $('#check-menor').on('change', function () {
    $('#row-apoderado').toggle(this.checked);
    if (!this.checked) $('#apoderado').val('');
  });

  // ── Toggle Producto / Servicio ───────────────────────────────────
  $('.toggle-btn').on('click', function () {
    $('.toggle-btn').removeClass('active');
    $(this).addClass('active');
    $('#tipo_bien').val($(this).data('val'));
  });

  // ── Radio Reclamo / Queja ────────────────────────────────────────
  $('#tipo-reclamo-group .radio-label').on('click', function () {
    $('#tipo-reclamo-group .radio-label').removeClass('checked');
    $(this).addClass('checked');
    $(this).find('input[type="radio"]').prop('checked', true);
  });

  // ── Validación ───────────────────────────────────────────────────
  function validateField(id) {
    const $el = $('#' + id);
    const ok  = $el.val().trim() !== '';
    $el.toggleClass('is-invalid', !ok);
    $('#err-' + id).toggleClass('show', !ok);
    return ok;
  }

  $('input,textarea').on('input', function () {
    const id = $(this).attr('id');
    $(this).removeClass('is-invalid');
    $('#err-' + id).removeClass('show');
  });

  // ── Submit ───────────────────────────────────────────────────────
  $('#form-reclamacion').on('submit', function (e) {
    e.preventDefault();

    const req = ['nombre','dni_ce','domicilio','detalle','pedido'];
    if ($('#check-menor').is(':checked')) req.push('apoderado');
    const valid = req
      .map(validateField).every(Boolean);

    if (!valid) {
      toast('Por favor complete los campos requeridos.', 'error');
      $('html,body').animate({ scrollTop: $('.is-invalid').first().offset().top - 100 }, 300);
      return;
    }

    const $btn = $('#btn-submit').prop('disabled', true).addClass('loading');

    const payload = {
      sede_id:         selectedSede.id,
      sede_nombre:     selectedSede.nombre,
      local_id:        selectedLocal.id,
      local_nombre:    selectedLocal.nombre,
      nombre:          $('#nombre').val().trim(),
      dni_ce:          $('#dni_ce').val().trim(),
      domicilio:       $('#domicilio').val().trim(),
      telefono:        $('#telefono').val().trim(),
      email:           $('#email').val().trim(),
      apoderado:       $('#apoderado').val().trim(),
      tipo_bien:       $('#tipo_bien').val(),
      descripcion:     $('#descripcion').val().trim(),
      monto_reclamado: $('#monto_reclamado').val(),
      tipo_reclamo:    $('input[name="tipo_reclamo"]:checked').val(),
      detalle:         $('#detalle').val().trim(),
      pedido:          $('#pedido').val().trim(),
      fecha:           $('#fecha').val(),
    };

    $.ajax({
      url: API + '?action=guardar',
      method: 'POST',
      contentType: 'application/json',
      data: JSON.stringify(payload),
      dataType: 'json',
      success: function (res) {
        if (res.success) {
          // Guardar snapshot completo para el PDF
          lastReclamo = {
            ...payload,
            numero:      res.data.numero,
            serie:       res.data.serie,
            correlativo: res.data.correlativo,
            fecha:       res.data.fecha,
            token:       res.data.token,
            sede_nombre: selectedSede.nombre,
            local_nombre:selectedLocal.nombre,
            local_dir:   selectedLocal.direccion,
          };
          $('#numero-generado').text(res.data.numero);

          // Mostrar aviso de correo si hay email
          if (payload.email) {
            $('#aviso-email-dir').text(payload.email);
            $('#aviso-email').show();
          } else {
            // Sin email: solo descarga, sin envío
            $('.pdf-btn-text').text('Descargar PDF');
          }

          // Mantener btn-nuevo deshabilitado hasta que descarguen el PDF
          $('#btn-nuevo').prop('disabled', true);
          $('#email-status').hide();

          $('#form-reclamacion').fadeOut(300, function () {
            $('#success-screen').fadeIn(400);
          });
        } else {
          toast(res.message || 'Ocurrió un error.', 'error');
        }
      },
      error: function (xhr) {
        toast(xhr.responseJSON?.message || 'Error al conectar con el servidor.', 'error');
      },
      complete: function () {
        $btn.prop('disabled', false).removeClass('loading');
      }
    });
  });

  // ── Reiniciar todo ───────────────────────────────────────────────
  $('#btn-nuevo').on('click', function () {
    $('#success-screen').fadeOut(300, function () {
      selectedSede   = { id: '', nombre: '' };
      selectedLocal  = { id: '', nombre: '', direccion: '' };
      selectedNumero = { serie: '', correlativo: 0, numero: '', anio: 0 };
      $('#form-reclamacion')[0].reset();
      $('.toggle-btn').removeClass('active');
      $('.toggle-btn[data-val="producto"]').addClass('active');
      $('#tipo_bien').val('producto');
      $('#tipo-reclamo-group .radio-label').removeClass('checked');
      $('#tipo-reclamo-group .radio-label[data-val="reclamo"]').addClass('checked');
      $('input[name="tipo_reclamo"][value="reclamo"]').prop('checked', true);
      $('#row-apoderado').hide();
      $('#fecha').val(hoy);
      $('input,textarea,select').removeClass('is-invalid');
      $('.err-msg').removeClass('show');
      $('#sel-local').prop('disabled', true).html('<option value="">— Primero seleccione una sede —</option>');
      $('#sel-sede').val('');
      $('#local-chip').hide();
      $('#chip-numero-wrap').hide();
      $('.sheet-titlebar .sub-title strong').html('N° —');
      $('#btn-siguiente').removeClass('visible');
      $('#step1').addClass('active').removeClass('done').find('.bubble').text('1');
      $('#step2').removeClass('active');
      $('#form-reclamacion').hide();
      $('#paso-sede').show();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  // ── Descargar PDF (generado en el servidor) y enviar al correo ───
  $('#btn-descargar-pdf').on('click', function () {
    if (!lastReclamo.token) { toast('No hay datos para generar el PDF.', 'error'); return; }
    const $btn = $(this), $status = $('#email-status');
    const mail = (lastReclamo.email || '').trim();
    const txt = mail ? 'Descargar PDF y Enviar al Correo' : 'Descargar PDF';
    const st = c => $status.removeClass('email-status-ok email-status-warn email-status-err').addClass(c).show();
    const safe = s => $('<i>').text(s).html();

    const link = document.createElement('a');
    link.href = 'pdf.php?t=' + lastReclamo.token + '&download=1';
    document.body.appendChild(link); link.click(); link.remove();
    $('#btn-nuevo').prop('disabled', false);

    if (!mail) {
      st('email-status-warn').html('⚠️ No se registró correo electrónico — solo se descargó el PDF.');
      toast('PDF descargado correctamente.', 'success'); return;
    }
    $btn.prop('disabled', true).addClass('loading-pdf'); $('.pdf-btn-text').text('Enviando al correo…');
    $.ajax({ url: API + '?action=enviar_correo', method: 'POST', contentType: 'application/json',
      data: JSON.stringify({ token: lastReclamo.token }), dataType: 'json' })
    .done(() => { st('email-status-ok').html('✔ PDF descargado y enviado a <strong>' + safe(mail) + '</strong>'); toast('PDF enviado a ' + mail, 'success'); })
    .fail(xhr => st('email-status-warn').html('⚠️ PDF descargado, pero no se pudo enviar el correo: ' + safe(xhr.responseJSON?.message || 'error de conexión.')))
    .always(() => { $btn.prop('disabled', false).removeClass('loading-pdf'); $('.pdf-btn-text').text(txt); });
  });

  // ── Botón: Registrar otra reclamación ───────────────────────────
  $('#btn-nuevo').on('click', function () {
    $('#success-screen').fadeOut(300, function () {
      // Reset estado global
      selectedSede   = { id: '', nombre: '' };
      selectedLocal  = { id: '', nombre: '', direccion: '' };
      selectedNumero = { serie: '', correlativo: 0, numero: '', anio: 0 };
      lastReclamo    = {};

      // Reset formulario
      $('#form-reclamacion')[0].reset();
      $('.toggle-btn').removeClass('active');
      $('.toggle-btn[data-val="producto"]').addClass('active');
      $('#tipo_bien').val('producto');
      $('#tipo-reclamo-group .radio-label').removeClass('checked');
      $('#tipo-reclamo-group .radio-label[data-val="reclamo"]').addClass('checked');
      $('input[name="tipo_reclamo"][value="reclamo"]').prop('checked', true);
      $('#row-apoderado').hide();
      $('#fecha').val(hoy);
      $('input,textarea,select').removeClass('is-invalid');
      $('.err-msg').removeClass('show');

      // Reset paso 1 selector
      $('#sel-local').prop('disabled', true).html('<option value="">— Primero seleccione una sede —</option>');
      $('#sel-sede').val('');
      $('#local-chip').hide();
      $('#chip-numero-wrap').hide();
      $('.sheet-titlebar .sub-title strong').html('N° —');
      $('#btn-siguiente').removeClass('visible');

      // Reset indicadores de paso
      $('#step1').addClass('active').removeClass('done').find('.bubble').text('1');
      $('#step2').removeClass('active');

      // Reset success screen para próxima vez
      $('#btn-nuevo').prop('disabled', true);
      $('#email-status').hide();
      $('#aviso-email').hide();
      $('.pdf-btn-text').text('Descargar PDF y Enviar al Correo');

      // Mostrar paso 1
      $('#form-reclamacion').hide();
      $('#paso-sede').show();
      window.scrollTo({ top: 0, behavior: 'smooth' });
    });
  });

  // ── Arranque: cargar sedes ───────────────────────────────────────
  cargarSedes();

});
</script>
</body>
</html>
