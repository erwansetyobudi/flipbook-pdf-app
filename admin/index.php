<?php
require_once __DIR__ . '/guard.php';
require_once __DIR__ . '/../config.php';

try {
  $db = get_db();
} catch (PDOException $e) {
  die("Koneksi DB gagal: " . htmlspecialchars($e->getMessage()));
}

// Ambil setting perpustakaan
if (!function_exists('get_setting')) {
  function get_setting($key, $default="") {
    $db = get_db();
    $st = $db->prepare("SELECT value FROM settings WHERE name=? LIMIT 1");
    $st->execute([$key]);
    return $st->fetchColumn() ?: $default;
  }
}
$library_name = get_setting('library_name','Perpustakaan Flipbook');
$tagline      = get_setting('tagline','Melayani dengan sepenuh hati');

// Ringkasan
$totalDocs  = (int) $db->query("SELECT COUNT(*) FROM documents")->fetchColumn();
$totalTypes = (int) $db->query("SELECT COUNT(*) FROM doc_types")->fetchColumn();

// Statistik per jenis
$byTypes = $db->query("
  SELECT t.id, t.name, COUNT(d.id) AS total
  FROM doc_types t
  LEFT JOIN documents d ON d.type_id = t.id
  GROUP BY t.id, t.name
  ORDER BY t.name
")->fetchAll(PDO::FETCH_ASSOC);

// Dokumen tanpa jenis
$unassigned = (int) $db->query("SELECT COUNT(*) FROM documents WHERE type_id IS NULL")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Dashboard - <?= htmlspecialchars($library_name) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- AdminLTE & deps -->
  <link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../adminlte/plugins/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">
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
  <!-- Sidebar -->
  <!-- Main Sidebar Container -->
  <?php include 'menus.php'; ?>


  <!-- Content -->
  <div class="content-wrapper">
    <!-- Header -->
    <section class="content-header">
      <div class="container-fluid">
        <h1>Dashboard</h1>
        <p class="text-muted mb-0"><?= htmlspecialchars($library_name) ?> — <?= htmlspecialchars($tagline) ?></p>
      </div>
    </section>

    <!-- Body -->
    <section class="content">
      <div class="container-fluid">

        <!-- Small boxes (Stat box) -->
        <div class="row">
          <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
              <div class="inner">
                <h3><?= number_format($totalDocs) ?></h3>
                <p>Total Dokumen</p>
              </div>
              <div class="icon"><i class="fas fa-file-pdf"></i></div>
            </div>
          </div>
          <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
              <div class="inner">
                <h3><?= number_format($totalTypes) ?></h3>
                <p>Total Jenis</p>
              </div>
              <div class="icon"><i class="fas fa-tags"></i></div>
            </div>
          </div>
        </div>

        <!-- Tabel statistik jenis -->
        <div class="card">
          <div class="card-header">
            <h3 class="card-title">Statistik Dokumen per Jenis</h3>
            <div class="card-tools">
              <a href="types.php" class="btn btn-sm btn-primary">
                <i class="fas fa-tags"></i> Kelola Jenis
              </a>
            </div>
          </div>
          <div class="card-body table-responsive p-0">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th style="width:70%">Jenis Dokumen</th>
                  <th class="text-right" style="width:30%">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($byTypes as $row): ?>
                  <tr>
                    <td><?= htmlspecialchars($row['name']) ?></td>
                    <td class="text-right"><strong><?= (int)$row['total'] ?></strong></td>
                  </tr>
                <?php endforeach; ?>
                <tr>
                  <td><em>Tanpa Jenis</em></td>
                  <td class="text-right"><strong><?= $unassigned ?></strong></td>
                </tr>
                <tr>
                  <td><strong>Total</strong></td>
                  <td class="text-right"><strong><?= $totalDocs ?></strong></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Tips navigasi -->
        <div class="alert alert-light border">
          <i class="fas fa-info-circle"></i>
          Untuk menambah dokumen baru, buka menu <strong>Dokumen</strong> &rarr; unggah file PDF.
          Untuk menambah/ubah jenis, buka <strong>Jenis Dokumen</strong>.
        </div>

      </div>
    </section>
  </div>

  <!-- Footer -->
  <footer class="main-footer text-center">
    <strong>&copy; <?= date('Y') ?> Flipbook Admin.</strong>
  </footer>
</div>

<!-- JS -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>
