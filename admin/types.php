<?php
require_once __DIR__ . '/guard.php';
require_once __DIR__ . '/../config.php';

try { $db = get_db(); }
catch (PDOException $e) { die("Koneksi DB gagal: " . htmlspecialchars($e->getMessage())); }

$msg = "";

// === Handle Tambah ===
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['add_type'])) {
  $name = trim($_POST['name'] ?? '');
  if ($name!=="") {
    $st = $db->prepare("INSERT INTO doc_types(name) VALUES(?)");
    $st->execute([$name]);
    $msg = "Jenis dokumen berhasil ditambahkan.";
  }
}

// Ambil setting perpustakaan
$library_name = get_setting('library_name','Perpustakaan Flipbook');
$tagline      = get_setting('tagline','Melayani dengan sepenuh hati');

// === Handle Update ===
if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['edit_type'])) {
  $id   = (int)($_POST['id'] ?? 0);
  $name = trim($_POST['name'] ?? '');
  if ($id && $name!=="") {
    $st = $db->prepare("UPDATE doc_types SET name=? WHERE id=?");
    $st->execute([$name,$id]);
    $msg = "Jenis dokumen berhasil diperbarui.";
  }
}

// === Handle Delete ===
if (isset($_GET['delete'])) {
  $id = (int)$_GET['delete'];
  $st = $db->prepare("DELETE FROM doc_types WHERE id=?");
  $st->execute([$id]);
  $msg = "Jenis dokumen berhasil dihapus.";
}

// === Ambil data ===
$types = $db->query("SELECT * FROM doc_types ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard - <?= htmlspecialchars($library_name) ?></title>
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
  <!-- Main Sidebar Container -->
  <?php include 'menus.php'; ?>


  <!-- Content Wrapper -->
  <div class="content-wrapper">
        <section class="content-header">
     
    </section>
    <section class="content-header">
      <div class="container-fluid">
        <h1>Jenis Dokumen</h1>
        <?php if ($msg): ?>
          <div class="alert alert-info mt-2"><?= htmlspecialchars($msg) ?></div>
        <?php endif; ?>
      </div>
    </section>

    <section class="content">
      <div class="container-fluid">
        <div class="card card-primary">
          <div class="card-header"><h3 class="card-title">Tambah Jenis Dokumen</h3></div>
          <form method="post">
            <div class="card-body">
              <div class="form-group">
                <label>Nama Jenis</label>
                <input type="text" name="name" class="form-control" required>
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" name="add_type" class="btn btn-primary">Tambah</button>
            </div>
          </form>
        </div>

        <div class="card">
          <div class="card-header"><h3 class="card-title">Daftar Jenis Dokumen</h3></div>
          <div class="card-body table-responsive p-0">
            <table class="table table-bordered table-hover">
              <thead>
                <tr>
                  <th style="width:70px">ID</th>
                  <th>Nama</th>
                  <th style="width:160px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($types as $t): ?>
                  <tr>
                    <td><?= $t['id'] ?></td>
                    <td><?= htmlspecialchars($t['name']) ?></td>
                    <td>
                      <!-- Form edit inline -->
                      <form method="post" class="d-inline-block">
                        <input type="hidden" name="id" value="<?= $t['id'] ?>">
                        <input type="text" name="name" value="<?= htmlspecialchars($t['name']) ?>" class="form-control form-control-sm d-inline-block" style="width:120px">
                        <button type="submit" name="edit_type" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></button>
                      </form>
                      <a href="?delete=<?= $t['id'] ?>" onclick="return confirm('Hapus jenis ini?')" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></a>
                    </td>
                  </tr>
                <?php endforeach; ?>
                <?php if (empty($types)): ?>
                  <tr><td colspan="3" class="text-center">Belum ada jenis dokumen.</td></tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="main-footer text-center">
    <strong>&copy; <?= date('Y') ?> Flipbook Admin</strong>
  </footer>
</div>

<!-- JS -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/js/adminlte.min.js"></script>
</body>
</html>
