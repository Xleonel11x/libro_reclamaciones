<?php
require_once __DIR__ . '/../../config/bootstrap.php';
Auth::logoutAdmin();
header('Location: login.php');
exit;
