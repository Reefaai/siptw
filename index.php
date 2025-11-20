<?php
require_once 'koneksi.php';
session_start();

// Ambil 3 Data Wisata Terbaru untuk ditampilkan di Homepage
$query_terbaru = mysqli_query($conn, "SELECT * FROM wisata ORDER BY id DESC LIMIT 3");
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>SIPTW - Jelajahi Keindahan Alam</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9f9;
    }

    /* --- NAVBAR --- */
    .navbar {
      padding: 15px 0;
      background: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }

    .navbar-brand {
      font-weight: 700;
      font-size: 1.5rem;
      color: #0d6efd !important;
    }

    .nav-link {
      font-weight: 500;
      color: #555 !important;
      margin-left: 15px;
    }

    .nav-link:hover {
      color: #0d6efd !important;
    }

    .nav-link-2 {
      font-weight: 500;
      color: #555 !important;
      margin-left: 15px;
    }

    .nav-link-2:hover {
      color: white !important;
    }

    .btn-nav {
      border-radius: 20px;
      padding: 8px 25px;
    }

    /* --- HERO SECTION --- */
    .hero-section {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.6)), url('https://images.unsplash.com/photo-1507525428034-b723cf961d3e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1920&q=80');
      background-size: cover;
      background-position: center;
      height: 80vh;
      /* Tinggi layar 80% */
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      border-radius: 0 0 50px 50px;
      /* Lengkungan bawah unik */
      margin-bottom: 50px;
    }

    .hero-title {
      font-size: 3.5rem;
      font-weight: 700;
      margin-bottom: 20px;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.5);
    }

    .search-box {
      background: white;
      padding: 10px;
      border-radius: 50px;
      display: flex;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
      max-width: 600px;
      margin: 0 auto;
    }

    .search-input {
      border: none;
      padding: 15px 25px;
      width: 100%;
      border-radius: 50px;
      outline: none;
    }

    /* --- CARD WISATA --- */
    .card-wisata {
      border: none;
      border-radius: 15px;
      overflow: hidden;
      transition: transform 0.3s, box-shadow 0.3s;
      height: 100%;
      background: white;
    }

    .card-wisata:hover {
      transform: translateY(-10px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .card-img-top {
      height: 220px;
      object-fit: cover;
    }

    .card-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(255, 255, 255, 0.9);
      padding: 5px 15px;
      border-radius: 20px;
      font-weight: 600;
      font-size: 0.8rem;
      color: #0d6efd;
    }

    /* --- FEATURES --- */
    .feature-box {
      padding: 40px 20px;
      background: white;
      border-radius: 20px;
      text-align: center;
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      transition: 0.3s;
    }

    .feature-box:hover {
      transform: translateY(-5px);
    }

    .feature-icon {
      font-size: 2.5rem;
      color: #0d6efd;
      margin-bottom: 20px;
    }

    /* --- FOOTER --- */
    footer {
      background: #2c3e50;
      color: white;
      padding: 60px 0 30px;
      margin-top: 80px;
    }

    .footer-link {
      color: #bdc3c7;
      text-decoration: none;
      display: block;
      margin-bottom: 10px;
      transition: 0.3s;
    }

    .footer-link:hover {
      color: white;
      padding-left: 5px;
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="index.php"><i class="fas fa-map-marked-alt"></i> SIPTW</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto align-items-center">
          <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="wisata_list.php">Destinasi</a></li>

          <?php if (!isset($_SESSION['username'])): ?>
            <li class="nav-item ms-2">
              <a class="nav-link-2 btn btn-primary btn-nav text-white px-4" href="login.php">Login</a>
            </li>
            <li class="nav-item ms-2">
              <a class="nav-link-2 btn btn-primary btn-nav text-white px-4" href="register.php">Daftar</a>
            </li>
          <?php else: ?>
            <li class="nav-item dropdown ms-3">
              <a class="nav-link dropdown-toggle fw-bold text-dark" href="#" role="button" data-bs-toggle="dropdown">
                Halo, <?= htmlspecialchars($_SESSION['username']); ?>
              </a>
              <ul class="dropdown-menu dropdown-menu-end border-0 shadow">
                <?php if ($_SESSION['level'] == 'admin'): ?>
                  <li><a class="dropdown-item" href="admin_dashboard.php"><i class="fas fa-tachometer-alt me-2"></i> Dashboard Admin</a></li>
                <?php else: ?>
                  <li><a class="dropdown-item" href="user_dashboard.php"><i class="fas fa-user me-2"></i> Dashboard User</a></li>
                <?php endif; ?>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li><a class="dropdown-item text-danger" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
              </ul>
            </li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero-section">
    <div class="container">
      <h1 class="hero-title">Jelajahi Keindahan<br>Alam Indonesia</h1>
      <p class="lead mb-4 opacity-75">Temukan destinasi wisata impianmu dan buat kenangan tak terlupakan.</p>

      <form action="wisata_list.php" method="GET">
        <div class="search-box">
          <input type="text" name="search" class="search-input" placeholder="Cari destinasi ....">
          <button type="submit" class="btn btn-primary rounded-pill px-4 m-1 fw-bold">Cari </button>
        </div>
      </form>
    </div>
  </header>

  <div class="container my-5">
    <div class="d-flex justify-content-between align-items-end mb-4">
      <div>
        <h6 class="text-primary fw-bold text-uppercase ls-2">Destinasi Terbaru</h6>
        <h2 class="fw-bold">Jelajahi Tempat Baru</h2>
      </div>
      <a href="wisata_list.php" class="btn btn-outline-primary rounded-pill">Lihat Semua <i class="fas fa-arrow-right ms-1"></i></a>
    </div>

    <div class="row g-4">
      <?php if (mysqli_num_rows($query_terbaru) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($query_terbaru)):
          $gambar = $row['gambar'] ? "uploads/" . $row['gambar'] : "https://via.placeholder.com/400x250?text=No+Image";
        ?>
          <div class="col-md-4">
            <div class="card card-wisata shadow-sm h-100">
              <div class="position-relative">
                <img src="<?= $gambar; ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama_wisata']); ?>">
                <span class="card-badge shadow-sm"><i class="fas fa-tag me-1"></i> <?= htmlspecialchars($row['kategori']); ?></span>
              </div>
              <div class="card-body p-4">
                <h5 class="card-title fw-bold mb-1"><?= htmlspecialchars($row['nama_wisata']); ?></h5>
                <p class="text-muted small mb-3"><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= htmlspecialchars($row['lokasi']); ?></p>
                <p class="card-text text-secondary">
                  <?= substr(strip_tags($row['deskripsi']), 0, 90); ?>...
                </p>
              </div>
              <div class="card-footer bg-white border-0 p-4 pt-0 d-flex justify-content-between align-items-center">
                <?php if (isset($row['harga_tiket']) && $row['harga_tiket'] > 0): ?>
                  <span class="fw-bold text-primary">Rp <?= number_format($row['harga_tiket'], 0, ',', '.'); ?></span>
                <?php else: ?>
                  <span class="fw-bold text-success">Tiket Terjangkau</span>
                <?php endif; ?>

                <a href="wisata_detail.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary rounded-pill px-3">Detail</a>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      <?php else: ?>
        <div class="col-12 text-center py-5">
          <img src="https://cdn-icons-png.flaticon.com/512/4076/4076432.png" width="100" class="mb-3 opacity-50">
          <h5 class="text-muted">Belum ada data wisata yang ditambahkan.</h5>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <section class="bg-light py-5 mt-5">
    <div class="container">
      <div class="text-center mb-5">
        <h2 class="fw-bold">Kenapa Memilih SIPTW?</h2>
        <p class="text-muted">Kami menyediakan informasi wisata terbaik untuk liburan Anda.</p>
      </div>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="feature-box h-100">
            <div class="feature-icon"><i class="fas fa-check-circle"></i></div>
            <h4 class="fw-bold">Informasi Lengkap</h4>
            <p class="text-muted">Detail lokasi, harga tiket, fasilitas, hingga peta lokasi tersedia lengkap.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box h-100">
            <div class="feature-icon"><i class="fas fa-images"></i></div>
            <h4 class="fw-bold">Galeri Foto HD</h4>
            <p class="text-muted">Lihat keindahan destinasi melalui galeri foto berkualitas tinggi sebelum berkunjung.</p>
          </div>
        </div>
        <div class="col-md-4">
          <div class="feature-box h-100">
            <div class="feature-icon"><i class="fas fa-users"></i></div>
            <h4 class="fw-bold">Akses Mudah</h4>
            <p class="text-muted">Platform yang ramah pengguna, mudah diakses di mana saja dan kapan saja.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <footer>
    <div class="container">
      <div class="row g-4">
        <div class="col-md-4">
          <h4 class="fw-bold mb-4"><i class="fas fa-map-marked-alt"></i> SIPTW</h4>
          <p class="text-white-50">Sistem Informasi Pengelolaan Tempat Wisata adalah platform nomor 1 untuk menemukan surga tersembunyi di Indonesia.</p>
        </div>
        <div class="col-md-2 offset-md-2">
          <h5 class="fw-bold mb-3">Menu</h5>
          <a href="index.php" class="footer-link">Beranda</a>
          <a href="wisata_list.php" class="footer-link">Destinasi</a>
          <a href="login.php" class="footer-link">Masuk</a>
        </div>
        <div class="col-md-4">
          <h5 class="fw-bold mb-3">Hubungi Kami</h5>
          <p class="mb-2"><i class="fas fa-envelope me-2"></i> support@siptw.com</p>
          <p class="mb-2"><i class="fas fa-phone me-2"></i> +62 812-3456-7890</p>
          <div class="mt-3">
            <a href="#" class="text-white me-3 fs-5"><i class="fab fa-facebook"></i></a>
            <a href="#" class="text-white me-3 fs-5"><i class="fab fa-instagram"></i></a>
            <a href="#" class="text-white fs-5"><i class="fab fa-twitter"></i></a>
          </div>
        </div>
      </div>
      <hr class="border-secondary my-4">
      <div class="text-center text-white-50">
        <small>&copy; <?= date('Y'); ?> SIPTW. All Rights Reserved.</small>
      </div>
    </div>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>