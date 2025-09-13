  <aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index.php" class="brand-link">
      <img src="../assets/book.svg" alt="Logo" class="brand-image img-circle elevation-3" style="opacity:.8">
      <span class="brand-text font-weight-light"><?= htmlspecialchars($library_name) ?></span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
      <!-- Sidebar Menu -->
      <nav class="mt-2">
        <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu">
          <li class="nav-item">
            <a href="index.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='index.php'?'active':'' ?>">
              <i class="nav-icon fas fa-book"></i>
              <p>Beranda</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="document.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='document.php'?'active':'' ?>">
              <i class="nav-icon fas fa-book"></i>
              <p>Dokumen</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="types.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='types.php'?'active':'' ?>">
              <i class="nav-icon fas fa-tags"></i>
              <p>Jenis Dokumen</p>
            </a>
          </li>
          <li class="nav-item">
            <a href="settings.php" class="nav-link <?= basename($_SERVER['PHP_SELF'])==='settings.php'?'active':'' ?>">
              <i class="nav-icon fas fa-cogs"></i><p>Pengaturan Sistem</p>
            </a>
          </li>
        </ul>
      </nav>
      
    </div>
    <!-- /.sidebar -->
  </aside>