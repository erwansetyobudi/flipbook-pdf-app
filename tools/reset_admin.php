<?php
require_once __DIR__ . '/../config.php';

try {
  $db = get_db();
  $user = 'admin';
  $new  = 'admin123'; // ganti kalau mau password lain

  $hash = password_hash($new, PASSWORD_DEFAULT);

  // insert kalau belum ada, update kalau sudah ada
  $sql = "INSERT INTO users (username, password_hash)
          VALUES (?, ?)
          ON DUPLICATE KEY UPDATE password_hash = VALUES(password_hash)";
  $st  = $db->prepare($sql);
  $st->execute([$user, $hash]);

  // sanity check
  $st = $db->prepare("SELECT username, LENGTH(password_hash) len FROM users WHERE username=?");
  $st->execute([$user]);
  $row = $st->fetch();
  echo "OK. User={$row['username']} len={$row['len']} (password di-set ulang)\n";
} catch (Throwable $e) {
  echo "ERR: " . $e->getMessage();
}
