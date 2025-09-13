<?php
require_once __DIR__ . '/guard.php';
require_once __DIR__ . '/../config.php';

try { $db = get_db(); }
catch (PDOException $e) { die("Koneksi DB gagal: " . htmlspecialchars($e->getMessage())); }

$msg = "";

// Buat tabel settings jika belum ada
$db->exec("CREATE TABLE IF NOT EXISTS settings (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) UNIQUE,
  value TEXT
) ENGINE=InnoDB");


function set_setting($key, $value) {
  global $db;
  $st = $db->prepare("INSERT INTO settings(name,value) VALUES(?,?)
    ON DUPLICATE KEY UPDATE value=VALUES(value)");
  $st->execute([$key,$value]);
}

// === Handle Save ===
if ($_SERVER['REQUEST_METHOD']==='POST') {
  set_setting('library_name', trim($_POST['library_name'] ?? ''));
  set_setting('tagline', trim($_POST['tagline'] ?? ''));
  set_setting('address', trim($_POST['address'] ?? ''));
  set_setting('email', trim($_POST['email'] ?? ''));
  set_setting('phone', trim($_POST['phone'] ?? ''));
  $msg = "Pengaturan berhasil disimpan.";
}

// Load values
$library_name = get_setting('library_name','Perpustakaan');
$tagline      = get_setting('tagline','Melayani dengan sepenuh hati');
$address      = get_setting('address','');
$email        = get_setting('email','');
$phone        = get_setting('phone','');
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Pengaturan Sistem</title>
  <link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
  <link rel="stylesheet" href="../adminlte/plugins/bootstrap/css/bootstrap.min.css">
</head>
<body class="hold-transition sidebar-mini layout-fixed">
<div class="wrapper">

  <!-- Navbar -->
  <nav class="main-header navbar navbar-expand navbar-white navbar-light">
    <ul class="navbar-nav">
      <li class="nav-item"><a class="nav-link" data-widget="pushmenu" href="#"><i class="fas fa-bars"></i></a></li>
      <li class="nav-item d-none d-sm-inline-block"><a href="index.php" class="nav-link">Dashboard</a></li>
    </ul>
    <ul class="navbar-nav ml-auto">
      <li class="nav-item"><a href="logout.php" class="nav-link text-danger"><i class="fas fa-sign-out-alt"></i> Keluar</a></li>
    </ul>
  </nav>

  <!-- Sidebar -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <a href="index.php" class="brand-link">
      <img src="../assets/book.svg" alt="Logo" class="brand-image img-circle elevation-3" style="opacity:.8">
      <span class="brand-text font-weight-light">Flipbook Admin</span>
    </a>
    <div class="sidebar">
      <?php include 'menus.php'; ?>
    </div>
  </aside>

  <!-- Content -->
  <div class="content-wrapper">
    <section class="content-header">
      <div class="container-fluid"><h1>Pengaturan Sistem</h1></div>
    </section>
    <section class="content">
      <div class="container-fluid">
        <?php if ($msg): ?><div class="alert alert-success"><?= htmlspecialchars($msg) ?></div><?php endif; ?>

        <div class="card card-primary">
          <div class="card-header"><h3 class="card-title">Informasi Perpustakaan</h3></div>
          <form method="post">
            <div class="card-body">
              <div class="form-group">
                <label>Nama Perpustakaan</label>
                <input type="text" name="library_name" class="form-control" value="<?= htmlspecialchars($library_name) ?>" required>
              </div>
              <div class="form-group">
                <label>Tagline</label>
                <input type="text" name="tagline" class="form-control" value="<?= htmlspecialchars($tagline) ?>">
              </div>
              <div class="form-group">
                <label>Alamat</label>
                <textarea name="address" class="form-control"><?= htmlspecialchars($address) ?></textarea>
              </div>
              <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>">
              </div>
              <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="phone" class="form-control" value="<?= htmlspecialchars($phone) ?>">
              </div>
            </div>
            <div class="card-footer">
              <button type="submit" class="btn btn-primary">Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="main-footer text-center">
    <strong>&copy; <?= date('Y') ?> Flipbook Admin</strong>
  </footer>
</div>

<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>
