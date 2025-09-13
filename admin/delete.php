<?php
// admin/delete.php
require_once __DIR__ . '/guard.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: index.php'); exit;
}

if (!hash_equals($_SESSION['csrf'] ?? '', $_POST['csrf'] ?? '')) {
  $_SESSION['flash_msg'] = 'Sesi kadaluarsa. Coba lagi.';
  header('Location: index.php'); exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
  $_SESSION['flash_msg'] = 'Parameter tidak valid.';
  header('Location: index.php'); exit;
}

try {
  $db = get_db();
  $st = $db->prepare('SELECT id, filename FROM documents WHERE id = ?');
  $st->execute([$id]);
  $doc = $st->fetch(PDO::FETCH_ASSOC);

  if (!$doc) {
    $_SESSION['flash_msg'] = 'Dokumen tidak ditemukan.';
    header('Location: index.php'); exit;
  }

  // hapus file fisik
  $path = __DIR__ . '/../uploads/' . $doc['filename'];
  if (is_file($path)) { @unlink($path); }

  // hapus record
  $del = $db->prepare('DELETE FROM documents WHERE id = ?');
  $del->execute([$id]);

  $_SESSION['flash_msg'] = 'Dokumen sudah dihapus.';
} catch (Throwable $e) {
  $_SESSION['flash_msg'] = 'Gagal menghapus: ' . $e->getMessage();
}

header('Location: index.php'); exit;
