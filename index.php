<?php
require_once __DIR__ . '/config.php';

try { 
  $db = get_db(); 
}
catch (PDOException $e) { 
  die("Koneksi DB gagal: " . htmlspecialchars($e->getMessage())); 
}

// Ambil setting perpustakaan
$library_name = get_setting('library_name','Perpustakaan Flipbook');
$tagline      = get_setting('tagline','Melayani dengan sepenuh hati');


// Helpers
$BASE = function_exists('app_url')
  ? rtrim(app_url(), '/')
  : rtrim((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
      .'://'.($_SERVER['HTTP_HOST'] ?? 'localhost')
      .rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\'), '/');

function u($s){ return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); }
function build_url($overrides = []) {
  $p = array_merge($_GET, $overrides);
  foreach ($p as $k => $v) if ($v === '' || $v === null) unset($p[$k]);
  return basename($_SERVER['PHP_SELF']).(empty($p)?'':'?'.http_build_query($p));
}

// Inputs
$q       = trim($_GET['q'] ?? '');
$sort    = $_GET['sort'] ?? 'new'; // new|old|title
$page    = max(1, (int)($_GET['page'] ?? 1));
$limit   = 24;
$offset  = ($page - 1) * $limit;
$type_id = !empty($_GET['type_id']) ? (int)$_GET['type_id'] : null;

// Ambil maksimal 7 jenis untuk navbar
$types = $db->query("SELECT * FROM doc_types ORDER BY name LIMIT 7")->fetchAll(PDO::FETCH_ASSOC);

// Build SQL
$where = [];
$args  = [];

if ($q !== '') {
  $where[] = 'd.title LIKE ?';
  $args[]  = "%{$q}%";
}
if ($type_id) {
  $where[] = 'd.type_id = ?';
  $args[]  = $type_id;
}
$whereSQL = $where ? ('WHERE '.implode(' AND ', $where)) : '';

$orderSQL = 'ORDER BY d.id DESC';
if ($sort === 'old')   $orderSQL = 'ORDER BY d.id ASC';
if ($sort === 'title') $orderSQL = 'ORDER BY d.title ASC';

// Count
$st = $db->prepare("SELECT COUNT(*) FROM documents d $whereSQL");
$st->execute($args);
$total = (int)$st->fetchColumn();
$pages = max(1, (int)ceil($total / $limit));

// Data
$sql = "SELECT d.id, d.title, d.slug, d.filename, d.created_at, d.cover, d.description, t.name AS type_name
        FROM documents d
        LEFT JOIN doc_types t ON d.type_id = t.id
        $whereSQL
        $orderSQL
        LIMIT $limit OFFSET $offset";
$st = $db->prepare($sql);
$st->execute($args);
$docs = $st->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($library_name) ?></title>
  <link rel="stylesheet" href="assets/style.css">
</head>
<body>

<div class="topbar">
  <div class="wrap inner">
    <div class="brand">
      <a href="index.php"><img src="assets/book.svg" alt="">
      <span><?= htmlspecialchars($library_name) ?></span></a>
    </div>

    <form class="searchbar" action="<?= u(basename($_SERVER['PHP_SELF'])) ?>" method="get">
      <input type="text" name="q" placeholder="Cari judul dokumen…" value="<?= u($q) ?>">
      <select name="sort" onchange="this.form.submit()">
        <option value="new"   <?= $sort==='new'?'selected':'' ?>>Terbaru</option>
        <option value="old"   <?= $sort==='old'?'selected':'' ?>>Terlama</option>
        <option value="title" <?= $sort==='title'?'selected':'' ?>>Judul (A–Z)</option>
      </select>
      <button type="submit">Cari</button>
    </form>
  </div>
</div>

<header class="hero">
  <div class="overlay">
    <div class="wrap">
      <h1><?= htmlspecialchars($library_name) ?></h1>
      <p><?= htmlspecialchars($tagline) ?></p>
    </div>
  </div>
</header>

<nav class="tabs">
  <div class="wrap">
    <a href="<?= u(build_url(['page'=>1,'type_id'=>null])) ?>" class="<?= !$type_id ? 'active' : '' ?>">Semua</a>
    <?php foreach ($types as $t): ?>
      <a href="<?= u(build_url(['page'=>1,'type_id'=>$t['id']])) ?>" 
         class="<?= ($type_id==$t['id'] ? 'active' : '') ?>">
         <?= htmlspecialchars($t['name']) ?>
      </a>
    <?php endforeach; ?>
  </div>
</nav>

<main class="wrap section">
  <h2>
    <?php 
      if ($q !== '') {
        echo 'Hasil pencarian';
      } elseif ($type_id) {
        $tn = $db->prepare("SELECT name FROM doc_types WHERE id=?");
        $tn->execute([$type_id]);
        echo 'Jenis: '.htmlspecialchars($tn->fetchColumn() ?? '-');
      } else {
        echo 'New arrivals';
      }
    ?>
  </h2>

  <?php if ($total === 0): ?>
    <p>Tidak ada dokumen untuk ditampilkan.</p>
  <?php else: ?>
    <div class="grid">
      <?php foreach ($docs as $d): ?>
        <article class="book">
          <a class="cover" href="<?= $BASE . '/view.php?d=' . urlencode($d['slug']) ?>" 
             aria-label="Buka <?= u($d['title']) ?>"
             style="background-image:url('<?= $d['cover'] ? 'uploads/covers/'.u($d['cover']) : 'assets/cover-placeholder.png' ?>'); background-size:cover;">
          </a>
          <div class="info">
            <div class="title" title="<?= u($d['title']) ?>"><?= u($d['title']) ?></div>
            <div class="meta"><?= date('d M Y', strtotime($d['created_at'])) ?><?= $d['type_name'] ? ' · '.u($d['type_name']) : '' ?></div>
            <a class="btn" href="<?= $BASE . '/view.php?d=' . urlencode($d['slug']) ?>">Buka</a>
          </div>
        </article>
      <?php endforeach; ?>
    </div>

    <?php if ($pages > 1): ?>
      <div class="pager" role="navigation" aria-label="Pagination">
        <?php
          $window = 2;
          $start = max(1, $page - $window);
          $end   = min($pages, $page + $window);
          if ($start > 1) echo '<a href="'.u(build_url(['page'=>1,'type_id'=>$type_id])).'">&laquo; Awal</a>';
          if ($page > 1)  echo '<a href="'.u(build_url(['page'=>$page-1,'type_id'=>$type_id])).'">&lsaquo; Prev</a>';
          for ($i=$start; $i<=$end; $i++) {
            if ($i == $page) echo '<span class="active">'.$i.'</span>';
            else echo '<a href="'.u(build_url(['page'=>$i,'type_id'=>$type_id])).'">'.$i.'</a>';
          }
          if ($page < $pages) echo '<a href="'.u(build_url(['page'=>$page+1,'type_id'=>$type_id])).'">Next &rsaquo;</a>';
          if ($end < $pages) echo '<a href="'.u(build_url(['page'=>$pages,'type_id'=>$type_id])).'">Akhir &raquo;</a>';
        ?>
      </div>
    <?php endif; ?>
  <?php endif; ?>
</main>

<footer>
  &copy; <?= date('Y') ?> Perpustakaan Flipbook.
</footer>

</body>
</html>
