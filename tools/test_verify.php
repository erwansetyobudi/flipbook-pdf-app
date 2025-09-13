<?php
require_once __DIR__ . '/../config.php';

$db = get_db();
$st = $db->prepare("SELECT password_hash FROM users WHERE username='admin'");
$st->execute();
$hash = $st->fetchColumn();

var_dump([
  'len'   => strlen($hash),
  'match' => password_verify('admin123', $hash),
  'algo'  => password_get_info($hash),
]);
