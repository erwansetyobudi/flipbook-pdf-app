<?php
require_once __DIR__ . '/guard.php';

try {
  $db = get_db();
} catch (PDOException $e) {
  die("Koneksi DB gagal: " . htmlspecialchars($e->getMessage()));
}

$UPLOAD_DIR = __DIR__ . '/../uploads';
$COVER_DIR  = __DIR__ . '/../uploads/covers';

$id  = (int)($_GET['id'] ?? 0);
$msg = '';

$st = $db->prepare("SELECT * FROM documents WHERE id = ?");
$st->execute([$id]);
$doc = $st->fetch(PDO::FETCH_ASSOC);

if (!$doc) {
  die("Dokumen tidak ditemukan.");
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $title = trim($_POST['title'] ?? $doc['title']);
  $desc  = trim($_POST['description'] ?? $doc['description']);

  // cover baru
  $coverFile = $doc['cover'];
  if (!empty($_FILES['cover']['name']) && $_FILES['cover']['error'] === UPLOAD_ERR_OK) {
    $ext = strtolower(pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION));
    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
      $coverFile = $doc['slug'] . '-' . time() . '.' . $ext;
      move_uploaded_file($_FILES['cover']['tmp_name'], $COVER_DIR . '/' . $coverFile);
    }
  }

  // pdf baru
  $pdfFile = $doc['filename'];
  if (!empty($_FILES['pdf']['name']) && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
    $pdfFile = $doc['slug'] . '-' . time() . '.pdf';
    move_uploaded_file($_FILES['pdf']['tmp_name'], $UPLOAD_DIR . '/' . $pdfFile);
  }

  $upd = $db->prepare("UPDATE documents SET title=?, description=?, cover=?, filename=? WHERE id=?");
  $upd->execute([$title, $desc, $coverFile, $pdfFile, $id]);
  $msg = "Dokumen berhasil diperbarui.";

  // refresh data
  $st->execute([$id]);
  $doc = $st->fetch(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Edit Dokumen - Flipbook Admin</title>
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
          <li class="nav-item"><a href="edit.php?id=<?= $id ?>" class="nav-link active"><i class="nav-icon fas fa-edit"></i><p>Edit</p></a></li>
        </ul>
      </nav>
    </div>
  </aside>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid"><h1>Edit Dokumen</h1></div>
    </section>

    <section class="content">
      <div class="container-fluid">

        <?php if ($msg): ?>
          <div class="alert alert-success"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>

        <div class="card card-primary">
          <div class="card-header"><h3 class="card-title">Form Edit Dokumen</h3></div>
          <form method="post" enctype="multipart/form-data">
            <div class="card-body">
              <div class="form-group">
                <label>Judul</label>
                <input type="text" name="title" class="form-control" value="<?= htmlspecialchars($doc['title']) ?>">
              </div>
              <div class="form-group">
                <label>Deskripsi</label>
                <textarea name="description" class="form-control" rows="3"><?= htmlspecialchars($doc['description']) ?></textarea>
              </div>
              <div class="form-group">
                <label>Cover (opsional, biarkan kosong jika tidak ingin ganti)</label><br>
                <?php if ($doc['cover']): ?>
                  <img src="../uploads/covers/<?= htmlspecialchars($doc['cover']) ?>" alt="Cover" style="width:120px;height:auto;margin-bottom:10px;">
                <?php endif; ?>
                <input type="file" name="cover" class="form-control-file" accept="image/*">
              </div>
              <div class="form-group">
                <label>File PDF (opsional, biarkan kosong jika tidak ingin ganti)</label><br>
                <a href="../uploads/<?= htmlspecialchars($doc['filename']) ?>" target="_blank">Lihat PDF Lama</a><br>
                <input type="file" name="pdf" class="form-control-file" accept="application/pdf">
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
              <a href="index.php" class="btn btn-secondary">Kembali</a>
            </div>
          </form>
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
