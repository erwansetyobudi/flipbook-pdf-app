<?php
require_once __DIR__ . '/guard.php';

try {
  $db = get_db();
} catch (PDOException $e) {
  die("Koneksi DB gagal: " . htmlspecialchars($e->getMessage()));
}

$UPLOAD_DIR = __DIR__ . '/../uploads';
$COVER_DIR  = __DIR__ . '/../uploads/covers';
@mkdir($UPLOAD_DIR, 0775, true);
@mkdir($COVER_DIR, 0775, true);

$msg = '';

function slugify($s) {
  $s = strtolower(trim($s));
  $s = preg_replace('~[^a-z0-9]+~', '-', $s);
  $s = trim($s, '-');
  return $s ?: bin2hex(random_bytes(4));
}

// Ambil setting perpustakaan
$library_name = get_setting('library_name','Perpustakaan Flipbook');
$tagline      = get_setting('tagline','Melayani dengan sepenuh hati');

// === Handle Upload ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
  $title = trim($_POST['title'] ?? '');
  $desc  = trim($_POST['description'] ?? '');
  $type_id = !empty($_POST['type_id']) ? (int)$_POST['type_id'] : null;
  $slug  = slugify($title ?: pathinfo($_FILES['pdf']['name'], PATHINFO_FILENAME));

  // cek slug unik
  $i = 1; $base = $slug;
  $st = $db->prepare('SELECT 1 FROM documents WHERE slug = ?');
  while (true) {
    $st->execute([$slug]);
    if (!$st->fetch()) break;
    $slug = $base . '-' . (++$i);
  }

  // upload PDF
  $pdfFile = null;
  if ($_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
    $pdfFile = $slug . '-' . time() . '.pdf';
    move_uploaded_file($_FILES['pdf']['tmp_name'], $UPLOAD_DIR . '/' . $pdfFile);
  }

  // upload cover (opsional)
  $coverFile = null;
  if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
      $coverFile = $slug . '-' . time() . '.' . $ext;
      move_uploaded_file($_FILES['cover']['tmp_name'], $COVER_DIR . '/' . $coverFile);
    }
  }

  if ($pdfFile) {
    $ins = $db->prepare("INSERT INTO documents(title, slug, filename, description, cover, type_id) VALUES(?,?,?,?,?,?)");
    $ins->execute([$title, $slug, $pdfFile, $desc, $coverFile, $type_id]);
    $msg = "Dokumen berhasil diunggah.";
  } else {
    $msg = "Upload gagal.";
  }
}

// ambil data
$sql = "SELECT d.*, t.name AS type_name
        FROM documents d
        LEFT JOIN doc_types t ON d.type_id = t.id
        ORDER BY d.id DESC";
$docs = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);

// ambil jenis
$types = $db->query("SELECT * FROM doc_types ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard - Flipbook Admin</title>
  <link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../adminlte/plugins/bootstrap/css/bootstrap.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item">
        <a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a>
      </li>
      <li class="nav-item d-none d-sm-inline-block">
        <a href="index.php" class="nav-link">Unggah Dokumen</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Keluar</a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
      <img src="../assets/book.svg" alt="Logo" class="brand-image img-circle elevation-3" style="opacity:.8">
      <span class="brand-text font-weight-light">Flipbook Admin</span>
    </a>

  <!-- Sidebar -->
  <!-- Main Sidebar Container -->
  <?php include 'menus.php'; ?>
</aside>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid"><h1>Dashboard</h1></div>
    </section>

    <section class="content">
      <div class="container-fluid">

        <?php if ($msg): ?>
          <div class="alert alert-info"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <!-- Upload Form -->
        <div class="card card-primary">
          <div class="card-header"><h3 class="card-title">Unggah Dokumen Baru</h3></div>
          <form method="post" enctype="multipart/form-data">
            <div class="card-body">
              <div class="form-group">
                <label>Judul</label>
                <input type="text" name="title" class="form-control" placeholder="Judul dokumen">
              </div>
              <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat"></textarea>
              </div>
              <div class="form-group">
                <label>Jenis Dokumen</label>
                <select name="type_id" class="form-control">
                  <option value="">-- Pilih Jenis --</option>
                  <?php foreach ($types as $t): ?>
                    <option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                  <?php endforeach; ?>
                </select>
              </div>
              <div class="form-group">
                <label>Cover (opsional)</label>
                <input type="file" name="cover" class="form-control-file" accept="image/*">
              </div>
              <div class="form-group">
                <label>File PDF</label>
                <input type="file" name="pdf" class="form-control-file" accept="application/pdf" required>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> Unggah</button>
            </div>
          </form>
        </div>

        <!-- Tabel Dokumen -->
        <div class="card">
          <div class="card-header"><h3 class="card-title">Daftar Dokumen</h3></div>
          <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                <tr>
                  <th>Cover</th>
                  <th>Judul</th>
                  <th>Jenis</th>
                  <th>Slug</th>
                  <th>Tanggal</th>
                  <th>Aksi</th>
                </tr>
              </thead>
              <tbody>
              <?php foreach ($docs as $d): ?>
                <tr>
                  <td>
                    <?php if ($d['cover']): ?>
                      <img src="../uploads/covers/<?= htmlspecialchars($d['cover']) ?>" alt="" style="width:60px;height:auto;">
                    <?php else: ?>
                      <span class="text-muted">-</span>
                    <?php endif; ?>
                  </td>
                  <td><?= htmlspecialchars($d['title']) ?></td>
                  <td><?= htmlspecialchars($d['type_name'] ?? '-') ?></td>
                  <td><code><?= htmlspecialchars($d['slug']) ?></code></td>
                  <td><?= $d['created_at'] ?></td>
                  <td>
                    <a href="../view.php?d=<?= urlencode($d['slug']) ?>" class="btn btn-sm btn-success" target="_blank"><i class="fas fa-eye"></i></a>
                    <a href="edit.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                    <a href="delete.php?id=<?= $d['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Hapus dokumen ini?')"><i class="fas fa-trash"></i></a>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </div>

  <footer class="main-footer text-center">
    <strong>&copy; <?= date('Y') ?> Flipbook Admin.</strong>
  </footer>
</div>

<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>
