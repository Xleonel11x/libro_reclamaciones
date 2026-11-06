<?php // Descarga pública del PDF con token no adivinable (se entrega al consumidor al registrar)
require_once __DIR__ . '/../config/bootstrap.php';
$t = $_GET['t'] ?? '';
$r = is_uuid($t) ? Reclamo::find('token=eq.' . $t) : null;
if (!$r) { http_response_code(404); exit('No encontrado'); }
header('Content-Type: application/pdf');
header('Content-Disposition: ' . (isset($_GET['download']) ? 'attachment' : 'inline') . '; filename="reclamo-' . Reclamo::numero($r) . '.pdf"');
echo PdfService::render($r);
