<?php
// config.php

// === Basic site config ===
$SITE_URL = rtrim(
  (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
  .'://'.($_SERVER['HTTP_HOST'] ?? 'localhost')
  .rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'),
  '/'
);

function app_url(): string {
  $base = rtrim(
    (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
    .'://'.($_SERVER['HTTP_HOST'] ?? 'localhost')
    .rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'),
    '/'
  );
  // buang /admin di ekor jika ada
  return rtrim(preg_replace('~\/admin$~', '', $base), '/');
}

// === Settings helper ===
function get_setting($key, $default="") {
  $db = get_db();
  $st = $db->prepare("SELECT value FROM settings WHERE name=? LIMIT 1");
  $st->execute([$key]);
  return $st->fetchColumn() ?: $default;
}


// === DB config ===
// Laragon default: user=root, password '' (kosong).
// Kalau kamu memang pakai password 'root', isi 'root' di DB_PASS dan konsisten di semua file.
define('DB_DSN',  'mysql:host=127.0.0.1;dbname=flipbook;charset=utf8mb4');
define('DB_USER', 'root');
define('DB_PASS', 'root'); // ganti '' jika MySQL-mu tanpa password

// Session
define('SESSION_NAME', 'flipbook_admin');

// Upload
$MAX_UPLOAD = 50 * 1024 * 1024; // 50 MB

// Helper PDO singleton
function get_db(): PDO {
  static $pdo = null;
  if ($pdo === null) {
    $pdo = new PDO(DB_DSN, DB_USER, DB_PASS, [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
  }
  return $pdo;
}
