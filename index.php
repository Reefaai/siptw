<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SIPTW - Sistem Informasi Pengelolaan Tempat Wisata</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">SIPTW</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <?php if (!isset($_SESSION['username'])): ?>
            <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
            <li class="nav-item"><a class="nav-link" href="register.php">Register</a></li>
          <?php else: ?>
            <?php if ($_SESSION['level'] == 'admin'): ?>
              <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard Admin</a></li>
            <?php else: ?>
              <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Dashboard User</a></li>
            <?php endif; ?>
            <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container text-center mt-5">
    <h1 class="text-primary fw-bold">Sistem Informasi Pengelolaan Tempat Wisata</h1>
    <p class="mt-3 fs-5 text-secondary">Selamat datang di portal informasi wisata.</p>

    <img src="https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=900" class="img-fluid rounded-4 shadow mt-3" style="max-width:600px;">

    <div class="mt-4">
      <a href="wisata_list.php" class="btn btn-success btn-lg">Lihat Daftar Wisata</a>
    </div>
  </div>

  <footer class="text-center mt-5 text-muted">
    <p>© <?php echo date("Y"); ?> SIPTW</p>
  </footer>

</body>

</html>