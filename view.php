<?php
require_once __DIR__ . '/config.php';

// tambahkan default supaya navbar tidak error
$q    = $_GET['q'] ?? '';
$sort = $_GET['sort'] ?? 'new';
if (!function_exists('u')) {
  function u($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
}

$slug = trim($_GET['d'] ?? '');
if ($slug === '') { http_response_code(400); echo 'Parameter tidak valid'; exit; }

try {
  $db = get_db();
  $st = $db->prepare("
    SELECT d.id, d.title, d.slug, d.filename, d.description, d.cover, d.created_at,
           t.name AS type_name
    FROM documents d
    LEFT JOIN doc_types t ON d.type_id = t.id
    WHERE d.slug = ? LIMIT 1
  ");
  $st->execute([$slug]);
  $doc = $st->fetch(PDO::FETCH_ASSOC);
  if (!$doc) { http_response_code(404); echo 'Dokumen tidak ditemukan'; exit; }
} catch (PDOException $e) {
  http_response_code(500);
  echo 'DB error: ' . htmlspecialchars($e->getMessage());
  exit;
}

$pdfUrl = 'uploads/' . $doc['filename']; // path relatif ke webroot
?>


<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($doc['title']) ?> — Flipbook</title>

  <link rel="stylesheet" href="assets/style.css">

  <!-- DearFlip CSS -->
  <link rel="stylesheet" href="assets/vendor/dflip/css/dflip.min.css">
  <link rel="stylesheet" href="assets/vendor/dflip/css/themify-icons.min.css">

  <style>
    body { background:#f5f5f7; font-family: system-ui, sans-serif; }
    .viewer-wrap { max-width: 1100px; margin: 24px auto; padding: 0 12px; }
    .doc-meta { margin-bottom: 20px; }
    .doc-meta img { max-width:200px; float:left; margin:0 20px 20px 0; border-radius:4px; }
    .doc-meta h1 { margin-top:0; }
    .doc-meta .info { font-size: 14px; color:#555; margin:6px 0; }
    .doc-meta .desc { margin-top:12px; white-space:pre-line; }
    .toolbar { display:flex; gap:8px; align-items:center; margin:20px 0; }
    .spacer { flex:1; }
    #flipbook { width: 100%; min-height: 600px; }
    .link { text-decoration: underline; }
    .clear { clear:both; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>

  <div class="viewer-wrap">

    <!-- Meta dokumen -->
    <div class="doc-meta">
      <?php if (!empty($doc['cover'])): ?>
        <img height="100px" src="uploads/covers/<?= htmlspecialchars($doc['cover']) ?>" alt="Cover <?= htmlspecialchars($doc['title']) ?>">
      <?php endif; ?>
      <h1><?= htmlspecialchars($doc['title']) ?></h1>
      <div class="info">
        <?php if (!empty($doc['type_name'])): ?>
          <strong>Jenis:</strong> <?= htmlspecialchars($doc['type_name']) ?> &nbsp; | &nbsp;
        <?php endif; ?>
        <strong>Tanggal unggah:</strong> <?= date('d M Y', strtotime($doc['created_at'])) ?>
      </div>
      <?php if (!empty($doc['description'])): ?>
        <div class="desc"><?= nl2br(htmlspecialchars($doc['description'])) ?></div>
      <?php endif; ?>
      <div class="clear"></div>
    </div>

    <!-- Toolbar -->
    <div class="toolbar">
      <div class="spacer"></div>
      <a class="link" href="<?= htmlspecialchars($pdfUrl) ?>" target="_blank" rel="noopener">Unduh PDF</a>
    </div>

    <!-- Flipbook -->
    <div id="flipbook" class="_df_book" source="<?= htmlspecialchars($pdfUrl, ENT_QUOTES) ?>"></div>
  </div>

  <!-- jQuery -->
  <script src="assets/vendor/dflip/js/libs/jquery.min.js"></script>
  <!-- DearFlip JS -->
  <script src="assets/vendor/dflip/js/dflip.min.js"></script>
</body>
</html>
