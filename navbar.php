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



?>

<!-- Topbar -->
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
