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

// === Handle Upload ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
  $title = trim($_POST['title'] ?? '');
  $desc  = trim($_POST['description'] ?? '');
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
    $ins = $db->prepare("INSERT INTO documents(title, slug, filename, description, cover) VALUES (?,?,?,?,?)");
    $ins->execute([$title, $slug, $pdfFile, $desc, $coverFile]);
    $msg = "Dokumen berhasil diunggah.";
  } else {
    $msg = "Upload gagal.";
  }
}

// ambil data
$docs = $db->query("SELECT * FROM documents ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
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
        <a href="index.php" class="nav-link">Dashboard</a>
      </li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item">
        <a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Keluar</a>
      </li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <span class="brand-text font-weight-light">Flipbook Admin</span>
    </a>
    <div class="sidebar">
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column">
          <li class="nav-item"><a href="index.php" class="nav-link active"><i class="nav-icon fas fa-book"></i><p>Tambah Dokumen</p></a></li>
          <li class="nav-item"><a href="document-list.php" class="nav-link active"><i class="nav-icon fas fa-book"></i><p>Daftar Dokumen</p></a></li>
        </ul>
      </nav>
    </div>
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


        <!-- Tabel Dokumen -->
        <div class="card">
          <div class="card-header"><h3 class="card-title">Daftar Dokumen</h3></div>
          <div class="card-body table-responsive p-0">
            <table class="table table-hover text-nowrap">
              <thead>
                <tr>
                  <th>Cover</th>
                  <th>Judul</th>
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
