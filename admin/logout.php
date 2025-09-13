<?php
require_once __DIR__ . '/../config.php';
if (session_status() === PHP_SESSION_NONE) {
  session_name(SESSION_NAME);
  session_start();
}
$_SESSION = [];
session_destroy();
header('Location: ' . app_url() . '/admin/login.php');
exit;
