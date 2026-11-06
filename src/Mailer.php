<?php
use PHPMailer\PHPMailer\PHPMailer;
class Mailer {
  static function send(string $to, string $subject, string $html, string $pdf, string $filename): void {
    $m = new PHPMailer(true);
    $m->isSMTP(); $m->Host = env('SMTP_HOST'); $m->SMTPAuth = true;
    $m->Username = env('SMTP_USER'); $m->Password = env('SMTP_PASS');
    $m->SMTPSecure = env('SMTP_SECURE', 'tls'); $m->Port = (int)env('SMTP_PORT', 587);
    $m->CharSet = 'UTF-8';
    $m->setFrom(env('MAIL_FROM'), env('MAIL_FROM_NAME', 'Libro de Reclamaciones'));
    $m->addAddress($to); $m->Subject = $subject; $m->isHTML(true); $m->Body = $html;
    $m->addStringAttachment($pdf, $filename, 'base64', 'application/pdf');
    $m->send();
  }
}
