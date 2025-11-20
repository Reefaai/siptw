<?php
require_once 'koneksi.php';
session_start();

// Cek Login (Sesuai logic lama)
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$level = $_SESSION['level'];
$keyword = isset($_GET['search']) ? $_GET['search'] : '';

// Logic Pencarian
if ($keyword) {
  $keyword_safe = mysqli_real_escape_string($conn, $keyword);
  $query = "SELECT * FROM wisata 
            WHERE nama_wisata LIKE '%$keyword_safe%' 
            OR lokasi LIKE '%$keyword_safe%' 
            OR kategori LIKE '%$keyword_safe%'
            ORDER BY id DESC";
} else {
  $query = "SELECT * FROM wisata ORDER BY id DESC";
}

$result = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Destinasi - SIPTW</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
    }

    /* Navbar styling (Konsisten dengan Index) */
    .navbar {
      background: white;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
      padding: 15px 0;
    }

    .navbar-brand {
      font-weight: 700;
      color: #0d6efd !important;
    }

    .nav-link {
      font-weight: 500;
      color: #555 !important;
    }

    .nav-link:hover {
      color: #0d6efd !important;
    }

    /* Page Header */
    .page-header {
      background: linear-gradient(135deg, #0d6efd 0%, #0dcaf0 100%);
      color: white;
      padding: 60px 0;
      margin-bottom: 40px;
      border-radius: 0 0 30px 30px;
      text-align: center;
    }

    /* Card Styling (User View) */
    .card-wisata {
      border: none;
      border-radius: 15px;
      transition: all 0.3s ease;
      background: white;
      overflow: hidden;
      height: 100%;
    }

    .card-wisata:hover {
      transform: translateY(-5px);
      box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
    }

    .card-img-wrapper {
      position: relative;
      height: 200px;
      overflow: hidden;
    }

    .card-img-top {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s;
    }

    .card-wisata:hover .card-img-top {
      transform: scale(1.1);
    }

    .category-badge {
      position: absolute;
      top: 15px;
      right: 15px;
      background: rgba(255, 255, 255, 0.95);
      color: #0d6efd;
      padding: 5px 12px;
      border-radius: 20px;
      font-size: 0.8rem;
      font-weight: 600;
      box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
    }

    /* Table Styling (Admin View) */
    .table-custom thead {
      background-color: #0d6efd;
      color: white;
    }

    .table-custom th {
      font-weight: 600;
      border: none;
    }

    .table-custom td {
      vertical-align: middle;
    }

    .img-thumbnail-admin {
      width: 80px;
      height: 60px;
      object-fit: cover;
      border-radius: 8px;
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
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>

          <?php if ($level == 'admin'): ?>
            <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard Admin</a></li>
            <li class="nav-item"><a class="nav-link btn btn-primary text-white px-3 ms-2 rounded-pill" href="wisata_add.php"><i class="fas fa-plus me-1"></i> Tambah Wisata</a></li>
          <?php else: ?>
            <li class="nav-item"><a class="nav-link active text-primary" href="wisata_list.php">Destinasi</a></li>
            <!-- <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Dashboard User</a></li> -->
          <?php endif; ?>

          <li class="nav-item ms-2">
            <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i></a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="page-header">
    <div class="container">
      <h2 class="fw-bold mb-3">Temukan Destinasi Impianmu</h2>
      <p class="mb-4 opacity-75">Jelajahi ratusan tempat wisata menarik di seluruh Indonesia</p>

      <div class="row justify-content-center">
        <div class="col-md-6">
          <form action="" method="get">
            <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
              <input type="text" name="search" class="form-control border-0 rounded-pill ps-4" placeholder="Cari pantai, gunung, atau kota..." value="<?= htmlspecialchars($keyword) ?>">
              <button class="btn btn-primary rounded-pill px-4 fw-bold m-1" type="submit">Cari</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>

  <div class="container mb-5">

    <?php if ($keyword): ?>
      <div class="alert alert-info border-0 bg-white shadow-sm rounded-3 mb-4">
        <i class="fas fa-search me-2"></i> Menampilkan hasil pencarian untuk: <strong>"<?= htmlspecialchars($keyword); ?>"</strong>
        <a href="wisata_list.php" class="float-end text-decoration-none">Reset</a>
      </div>
    <?php endif; ?>

    <?php if ($level == 'admin'): ?>
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0 overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover table-custom mb-0">
              <thead>
                <tr>
                  <th class="ps-4 py-3">No</th>
                  <th>Foto</th>
                  <th>Info Wisata</th>
                  <th>Kategori</th>
                  <th>Harga</th>
                  <th class="text-center">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                if (mysqli_num_rows($result) > 0):
                  while ($row = mysqli_fetch_assoc($result)):
                    $gambar = $row['gambar'] ? "uploads/" . $row['gambar'] : "https://via.placeholder.com/100x80?text=No+Img";
                ?>
                    <tr>
                      <td class="ps-4"><?= $no++; ?></td>
                      <td>
                        <img src="<?= $gambar; ?>" class="img-thumbnail-admin shadow-sm">
                      </td>
                      <td>
                        <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($row['nama_wisata']); ?></h6>
                        <small class="text-muted"><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= htmlspecialchars($row['lokasi']); ?></small>
                      </td>
                      <td><span class="badge bg-info text-dark"><?= htmlspecialchars($row['kategori']); ?></span></td>
                      <td>Rp <?= number_format($row['harga_tiket'] ?? 0, 0, ',', '.'); ?></td>
                      <td class="text-center">
                        <a href="wisata_edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm rounded-circle shadow-sm me-1" title="Edit"><i class="fas fa-pen"></i></a>
                        <a href="wisata_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm rounded-circle shadow-sm" onclick="return confirm('Hapus data ini?')" title="Hapus"><i class="fas fa-trash"></i></a>
                      </td>
                    </tr>
                  <?php endwhile;
                else: ?>
                  <tr>
                    <td colspan="6" class="text-center py-5 text-muted">Tidak ada data wisata ditemukan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>
      </div>

    <?php else: ?>
      <div class="row g-4">
        <?php
        if (mysqli_num_rows($result) > 0):
          while ($row = mysqli_fetch_assoc($result)):
            $gambar = $row['gambar'] ? "uploads/" . $row['gambar'] : "https://via.placeholder.com/400x250?text=No+Image";
        ?>
            <div class="col-md-6 col-lg-4">
              <div class="card card-wisata shadow-sm h-100">
                <div class="card-img-wrapper">
                  <img src="<?= $gambar; ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama_wisata']); ?>">
                  <span class="category-badge shadow-sm">
                    <i class="fas fa-tag me-1"></i> <?= htmlspecialchars($row['kategori']); ?>
                  </span>
                </div>

                <div class="card-body p-4">
                  <div class="d-flex justify-content-between align-items-start mb-2">
                    <h5 class="card-title fw-bold text-dark mb-0"><?= htmlspecialchars($row['nama_wisata']); ?></h5>
                  </div>

                  <p class="text-muted small mb-3">
                    <i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= htmlspecialchars($row['lokasi']); ?>
                  </p>

                  <p class="card-text text-secondary small" style="line-height: 1.6;">
                    <?= substr(strip_tags($row['deskripsi']), 0, 90); ?>...
                  </p>

                  <hr class="border-secondary opacity-10 my-3">

                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <small class="text-muted d-block" style="font-size: 11px;">Harga Tiket</small>
                      <span class="fw-bold text-primary">Rp <?= number_format($row['harga_tiket'] ?? 0, 0, ',', '.'); ?></span>
                    </div>
                    <a href="wisata_detail.php?id=<?= $row['id']; ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                      Detail <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                  </div>
                </div>
              </div>
            </div>
          <?php endwhile;
        else: ?>
          <div class="col-12 text-center py-5">
            <div class="text-muted">
              <i class="fas fa-search fa-3x mb-3 opacity-50"></i>
              <h5>Tidak ada wisata yang ditemukan.</h5>
              <p>Coba kata kunci lain atau reset pencarian.</p>
              <a href="wisata_list.php" class="btn btn-secondary btn-sm mt-2">Reset Pencarian</a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    <?php endif; ?>

  </div>

  <footer class="text-center py-4 text-muted mt-auto border-top">
    <small>&copy; <?= date('Y'); ?> SIPTW - Sistem Informasi Pariwisata</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>