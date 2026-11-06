<?php
use Dompdf\Dompdf;
class PdfService {
  static function render(array $r): string {
    $l = $r['locales']; $s = $l['sedes'];
    $b = fn($v) => e($v) ?: '&nbsp;';
    $fmt = fn($d) => $d ? date('d/m/Y', strtotime($d)) : '';
    $x = fn($c) => $c ? '[ X ]' : '[&nbsp;&nbsp;&nbsp;]';
    $html = '<style>body{font:11px DejaVu Sans,sans-serif}table{width:100%;border-collapse:collapse;margin-bottom:6px}
    td,th{border:1px solid #ccc5b8;padding:5px;vertical-align:top;text-align:left}.h{background:#e8e2d6;font-weight:bold}.t{font-size:15px;text-align:center;background:#1a1612;color:#f5f0e8;border-bottom:3px solid #c0392b}</style>
    <table><tr><th class="t">LIBRO DE RECLAMACIONES</th><th class="t">HOJA DE RECLAMACIÓN<br>N° ' . e(Reclamo::numero($r)) . '</th></tr>
    <tr><td colspan="2">FECHA: ' . $fmt($r['fecha']) . '</td></tr>
    <tr><td colspan="2"><b>PROVEEDOR:</b> ' . $b($s['razon_social']) . ' &nbsp; <b>RUC:</b> ' . $b($s['ruc']) . '<br><b>DOMICILIO:</b> ' . $b($s['domicilio']) . '<br><b>SEDE:</b> ' . $b($s['nombre']) . ' &nbsp; <b>LOCAL:</b> ' . $b($l['nombre'] . ' - ' . $l['direccion']) . '</td></tr></table>
    <table><tr><td class="h" colspan="2">1. IDENTIFICACIÓN DEL CONSUMIDOR RECLAMANTE</td></tr>
    <tr><td colspan="2"><b>NOMBRE:</b> ' . $b($r['nombre']) . '</td></tr>
    <tr><td colspan="2"><b>DNI / CE:</b> ' . $b($r['dni_ce']) . '</td></tr>
    <tr><td colspan="2"><b>DOMICILIO:</b> ' . $b($r['domicilio']) . '</td></tr>
    <tr><td><b>TELÉFONO:</b> ' . $b($r['telefono']) . '</td><td><b>E-MAIL:</b> ' . $b($r['email']) . '</td></tr>
    <tr><td colspan="2"><b>SI ES MENOR DE EDAD, NOMBRE DEL PADRE, MADRE O APODERADO:</b> ' . $b($r['menor_edad'] ? $r['apoderado'] : '') . '</td></tr>
    <tr><td class="h" colspan="2">2. IDENTIFICACIÓN DEL BIEN CONTRATADO</td></tr>
    <tr><td>' . $x($r['bien_tipo'] === 'producto') . ' PRODUCTO &nbsp; ' . $x($r['bien_tipo'] === 'servicio') . ' SERVICIO</td><td><b>MONTO RECLAMADO:</b> S/ ' . number_format((float)$r['monto'], 2) . '</td></tr>
    <tr><td colspan="2"><b>DESCRIPCIÓN:</b> ' . nl2br($b($r['descripcion'])) . '</td></tr>
    <tr><td class="h">3. DETALLE DE LA RECLAMACIÓN Y PEDIDO DEL CONSUMIDOR</td><td>' . $x($r['tipo'] === 'reclamo') . ' RECLAMO<sup>1</sup> &nbsp; ' . $x($r['tipo'] === 'queja') . ' QUEJA<sup>2</sup></td></tr>
    <tr><td colspan="2"><b>DETALLE:</b> ' . nl2br($b($r['detalle'])) . '</td></tr>
    <tr><td colspan="2"><b>PEDIDO:</b> ' . nl2br($b($r['pedido'])) . '</td></tr>
    <tr><td class="h">4. OBSERVACIONES Y ACCIONES ADOPTADAS POR EL PROVEEDOR</td><td class="h">FIRMA DEL CONSUMIDOR</td></tr>
    <tr><td style="height:70px">' . nl2br($b($r['observaciones'] ?? '')) . '<br><br><b>FECHA DE COMUNICACIÓN DE LA RESPUESTA:</b> ' . $fmt($r['fecha_respuesta']) . '</td><td></td></tr>
    <tr><td></td><td class="h">FIRMA DEL PROVEEDOR</td></tr></table>
    <p style="font-size:9px"><sup>1</sup> <b>RECLAMO:</b> Disconformidad relacionada a los productos o servicios.<br>
    <sup>2</sup> <b>QUEJA:</b> Disconformidad no relacionada a los productos o servicios; o, malestar o descontento respecto a la atención al público.<br>
    * La formulación del reclamo no impide acudir a otras vías de solución de controversias ni es requisito previo para interponer una denuncia ante el INDECOPI.<br>
    * El proveedor debe dar respuesta al reclamo o queja en un plazo no mayor a quince (15) días hábiles, el cual es improrrogable.</p>
    <p style="text-align:center;font-weight:bold">HOJA DE RECLAMACIÓN VIRTUAL</p>';
    $p = new Dompdf(['defaultFont' => 'DejaVu Sans']);
    $p->loadHtml('<meta charset="utf-8">' . $html); $p->setPaper('A4'); $p->render();
    return $p->output();
  }
}
