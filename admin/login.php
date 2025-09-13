<?php
require_once __DIR__ . '/../config.php';

if (session_status() === PHP_SESSION_NONE) {
  session_name(SESSION_NAME);
  session_start();
}

// helper setting
if (!function_exists('get_setting')) {
  function get_setting($k,$d=''){ $db=get_db(); $st=$db->prepare("SELECT value FROM settings WHERE name=?"); $st->execute([$k]); return $st->fetchColumn()?:$d; }
}
$library_name = get_setting('library_name','Perpustakaan');
$tagline      = get_setting('tagline','Melayani dengan sepenuh hati');

$err = '';
if ($_SERVER['REQUEST_METHOD']==='POST') {
  $u = trim($_POST['username'] ?? '');
  $p = $_POST['password'] ?? '';
  try {
    $db = get_db();
    $st = $db->prepare('SELECT id,username,password_hash FROM users WHERE username=? LIMIT 1');
    $st->execute([$u]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row && password_verify($p,$row['password_hash'])) {
      $_SESSION['uid']=(int)$row['id']; $_SESSION['uname']=$row['username'];
      header('Location: '.app_url().'/admin/index.php'); exit;
    } else { $err='Username atau password salah'; }
  } catch(PDOException $e){ $err='DB error: '.htmlspecialchars($e->getMessage()); }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <title>Masuk — <?= htmlspecialchars($library_name) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- AdminLTE & deps -->
  <link rel="stylesheet" href="../adminlte/plugins/fontawesome-free/css/all.min.css">
  <link rel="stylesheet" href="../adminlte/plugins/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="../adminlte/dist/css/adminlte.min.css">

  <style>
    html,body { height:100%; }
    .bg-cover { background-position:center; background-size:cover; background-repeat:no-repeat; }
    .login-col { max-width: 480px; }
    .muted { color:#6b7280; }
  </style>
</head>
<body class="hold-transition">

<!-- LAYOUT: 2 kolom full tinggi layar -->
<div class="container-fluid min-vh-100">
  <div class="row no-gutters min-vh-100">

    <!-- Kiri: form -->
    <div class="col-12 col-md-5 d-flex align-items-center justify-content-center py-4 py-md-0">
      <div class="w-100 px-3 login-col">
        <a href="../" class="text-muted d-inline-flex align-items-center mb-3">
          <i class="fas fa-arrow-left mr-2"></i> Kembali ke beranda
        </a>

        <div class="card shadow-sm">
          <div class="card-body">
            <h4 class="mb-2">Sign In</h4>
            <p class="mb-4 muted">
              Selamat datang di <strong><?= htmlspecialchars($library_name) ?></strong>.
              <?= $tagline ? htmlspecialchars($tagline).'. ' : '' ?>Silakan masukkan kredensial untuk melanjutkan.
            </p>

            <?php if ($err): ?>
              <div class="alert alert-danger"><?= htmlspecialchars($err) ?></div>
            <?php endif; ?>

            <form method="post" autocomplete="off">
              <div class="input-group mb-3">
                <input type="text" name="username" class="form-control" placeholder="Username"
                       value="<?= htmlspecialchars($_POST['username'] ?? '') ?>" required>
                <div class="input-group-append">
                  <div class="input-group-text"><span class="fas fa-user"></span></div>
                </div>
              </div>

              <div class="input-group mb-3">
                <input type="password" name="password" class="form-control" placeholder="Password" required>
                <div class="input-group-append">
                  <div class="input-group-text"><span class="fas fa-lock"></span></div>
                </div>
              </div>

              <div class="d-flex align-items-center mb-3">
                <div class="icheck-primary">
                  <input type="checkbox" id="remember"><label for="remember"> Remember me</label>
                </div>
                <button class="btn btn-primary ml-auto px-4">Log In</button>
              </div>
            </form>

            <div class="text-center text-muted my-3">— or Move to —</div>
            <div class="d-flex justify-content-center">
              <a href="index.php" class="btn btn-success btn-sm mr-2">Home</a>
     
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Kanan: gambar -->
    <div class="col-md-7 d-none d-md-block p-0">
      <!-- ganti path gambar di bawah sesuai file kamu -->
      <div class="h-100 w-100 bg-cover" style="background-image:url('../assets/login-bg.jpg');"></div>
    </div>

  </div>
</div>

<!-- JS -->
<script src="../adminlte/plugins/jquery/jquery.min.js"></script>
<script src="../adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="../adminlte/dist/js/adminlte.min.js"></script>
</body>
</html>
