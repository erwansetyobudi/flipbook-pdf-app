<?php
require __DIR__ . '/config.php';
if (!is_dir(dirname($DB_PATH))) { mkdir(dirname($DB_PATH), 0775, true); }
$db = new PDO('sqlite:' . $DB_PATH);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


$db->exec('CREATE TABLE IF NOT EXISTS users (
id INTEGER PRIMARY KEY AUTOINCREMENT,
username TEXT UNIQUE NOT NULL,
password_hash TEXT NOT NULL,
created_at TEXT DEFAULT CURRENT_TIMESTAMP
)');


$db->exec('CREATE TABLE IF NOT EXISTS documents (
id INTEGER PRIMARY KEY AUTOINCREMENT,
title TEXT NOT NULL,
slug TEXT UNIQUE NOT NULL,
filename TEXT NOT NULL,
pages INTEGER DEFAULT 0,
created_at TEXT DEFAULT CURRENT_TIMESTAMP
)');


// seed admin if not exists
$st = $db->prepare('SELECT COUNT(*) FROM users');
if ($st->execute() && $st->fetchColumn() == 0) {
$hash = password_hash($BOOTSTRAP_ADMIN['password_plain'], PASSWORD_DEFAULT);
$ins = $db->prepare('INSERT INTO users(username, password_hash) VALUES(?, ?)');
$ins->execute([$BOOTSTRAP_ADMIN['username'], $hash]);
}


echo "OK: database ready\n";