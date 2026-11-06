<?php
require __DIR__ . '/../../config/bootstrap.php';
require_admin(false);
$r = is_uuid($_GET['id'] ?? '') ? Reclamo::find('id=eq.' . $_GET['id']) : null;
if (!$r) { http_response_code(404); exit('No encontrado'); }
header('Content-Type: application/pdf');
header('Content-Disposition: inline; filename="reclamo-' . Reclamo::numero($r) . '.pdf"');
echo PdfService::render($r);
