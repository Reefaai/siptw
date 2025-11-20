<?php
require_once 'koneksi.php';
session_start();

// Cek Login & Level Admin
if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

// --- 1. QUERY DATA STATISTIK ---
$total_wisata_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM wisata");
$total_wisata = mysqli_fetch_assoc($total_wisata_res)['total'];

$total_user_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE level='user'");
$total_user = mysqli_fetch_assoc($total_user_res)['total'];

$total_admin_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE level='admin'");
$total_admin = mysqli_fetch_assoc($total_admin_res)['total'];

$total_kat_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori");
$total_kategori = mysqli_fetch_assoc($total_kat_res)['total'];

// --- 2. QUERY CHART (Kategori) ---
$kategori_result = mysqli_query($conn, "SELECT kategori, COUNT(*) AS jumlah FROM wisata GROUP BY kategori");
$label_kat = [];
$data_kat = [];
while ($row = mysqli_fetch_assoc($kategori_result)) {
  $label_kat[] = $row['kategori'];
  $data_kat[] = $row['jumlah'];
}

// --- 3. QUERY TERBARU (5 Data Terakhir) ---
$terbaru_result = mysqli_query($conn, "SELECT * FROM wisata ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin - SIPTW</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
    }

    /* Navbar */
    .navbar {
      background: white;
      box-shadow: 0 2px 15px rgba(0, 0, 0, 0.04);
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

    .nav-link:hover,
    .nav-link.active {
      color: #0d6efd !important;
    }

    /* Stats Cards */
    .stat-card {
      border: none;
      border-radius: 15px;
      color: white;
      transition: transform 0.3s;
      overflow: hidden;
      position: relative;
    }

    .stat-card:hover {
      transform: translateY(-5px);
    }

    .stat-card .icon-bg {
      position: absolute;
      right: 20px;
      top: 50%;
      transform: translateY(-50%);
      font-size: 3.5rem;
      opacity: 0.3;
    }

    .bg-gradient-blue {
      background: linear-gradient(45deg, #0d6efd, #0dcaf0);
    }

    .bg-gradient-green {
      background: linear-gradient(45deg, #198754, #20c997);
    }

    .bg-gradient-orange {
      background: linear-gradient(45deg, #fd7e14, #ffc107);
    }

    .bg-gradient-purple {
      background: linear-gradient(45deg, #6f42c1, #a060f8);
    }

    /* Content Cards */
    .content-card {
      background: white;
      border-radius: 15px;
      border: none;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    .table-img {
      width: 50px;
      height: 40px;
      object-fit: cover;
      border-radius: 6px;
    }

    /* FIX CHART SIZING */
    .chart-container {
      position: relative;
      height: 300px;
      /* Tinggi dipaksa agar tidak meledak */
      width: 100%;
    }

    .chart-container-doughnut {
      position: relative;
      height: 250px;
      /* Tinggi khusus untuk Pie Chart */
      width: 100%;
      display: flex;
      justify-content: center;
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg sticky-top">
    <div class="container">
      <a class="navbar-brand" href="admin_dashboard.php"><i class="fas fa-user-shield me-2"></i> Admin Panel</a>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link active" href="admin_dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="wisata_list.php">Kelola Wisata</a></li>
          <li class="nav-item"><a class="nav-link" href="kategori_manage.php">Kategori</a></li>
          <li class="nav-item"><a class="nav-link" href="user_manage.php">Users</a></li>
          <li class="nav-item ms-3"><a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold text-dark mb-0">Dashboard Overview</h2>
        <p class="text-muted">Selamat datang kembali, <strong><?= htmlspecialchars($_SESSION['username']); ?></strong>!</p>
      </div>
      <a href="wisata_add.php" class="btn btn-primary rounded-pill px-4 shadow-sm"><i class="fas fa-plus me-2"></i> Tambah Wisata</a>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-blue h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 opacity-75">Total Wisata</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_wisata; ?></h2>
            <i class="fas fa-map-marked-alt icon-bg"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-purple h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 opacity-75">Total Kategori</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_kategori; ?></h2>
            <i class="fas fa-tags icon-bg"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-green h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 opacity-75">Pengguna</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_user; ?></h2>
            <i class="fas fa-users icon-bg"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-orange h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 opacity-75">Admin</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_admin; ?></h2>
            <i class="fas fa-user-shield icon-bg"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">

      <div class="col-lg-7">

        <div class="card content-card p-4 mb-4">
          <h5 class="fw-bold text-primary mb-3"><i class="fas fa-chart-bar me-2"></i> Statistik Kategori</h5>
          <div class="chart-container">
            <canvas id="chartKategori"></canvas>
          </div>
        </div>

        <div class="card content-card p-4">
          <h5 class="fw-bold text-success mb-3"><i class="fas fa-chart-pie me-2"></i> Komposisi Pengguna</h5>
          <div class="chart-container-doughnut">
            <canvas id="chartUser"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-5">
        <div class="card content-card p-0 overflow-hidden h-100">
          <div class="p-4 border-bottom bg-light">
            <h5 class="fw-bold mb-0 text-dark"><i class="fas fa-history me-2"></i> Baru Ditambahkan</h5>
          </div>
          <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
              <thead class="table-light small text-uppercase">
                <tr>
                  <th class="ps-4">Wisata</th>
                  <th>Kategori</th>
                  <th>Harga</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($terbaru_result) > 0): ?>
                  <?php while ($t = mysqli_fetch_assoc($terbaru_result)):
                    $img = $t['gambar'] ? "uploads/" . $t['gambar'] : "https://via.placeholder.com/50";
                  ?>
                    <tr>
                      <td class="ps-4">
                        <div class="d-flex align-items-center">
                          <img src="<?= $img; ?>" class="table-img me-3 shadow-sm">
                          <div>
                            <span class="d-block fw-bold text-dark small"><?= htmlspecialchars($t['nama_wisata']); ?></span>
                            <small class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($t['lokasi']); ?></small>
                          </div>
                        </div>
                      </td>
                      <td><span class="badge bg-primary-subtle text-primary rounded-pill"><?= htmlspecialchars($t['kategori']); ?></span></td>
                      <td class="fw-bold text-secondary small">Rp <?= number_format($t['harga_tiket'] ?? 0, 0, ',', '.'); ?></td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" class="text-center py-4 text-muted">Belum ada data.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="p-3 text-center border-top bg-light mt-auto">
            <a href="wisata_list.php" class="text-decoration-none fw-bold small">Lihat Semua Data <i class="fas fa-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer class="text-center py-4 text-muted mt-5 border-top bg-white">
    <small>&copy; <?= date('Y'); ?> SIPTW Admin Panel.</small>
  </footer>

  <script>
    // 1. Chart Bar Kategori
    const ctx1 = document.getElementById('chartKategori');
    if (ctx1) {
      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: <?= json_encode($label_kat); ?>,
          datasets: [{
            label: 'Jumlah Wisata',
            data: <?= json_encode($data_kat); ?>,
            backgroundColor: [
              'rgba(13, 110, 253, 0.7)', 'rgba(25, 135, 84, 0.7)',
              'rgba(255, 193, 7, 0.7)', 'rgba(220, 53, 69, 0.7)', 'rgba(13, 202, 240, 0.7)'
            ],
            borderRadius: 5,
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false, // PENTING: Agar mengikuti tinggi container
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              grid: {
                display: false
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          }
        }
      });
    }

    // 2. Chart Doughnut User
    const ctx2 = document.getElementById('chartUser');
    if (ctx2) {
      new Chart(ctx2, {
        type: 'doughnut',
        data: {
          labels: ['User Biasa', 'Admin'],
          datasets: [{
            data: [<?= $total_user; ?>, <?= $total_admin; ?>],
            backgroundColor: ['#198754', '#ffc107'],
            hoverOffset: 4
          }]
        },
        options: {
          responsive: true,
          maintainAspectRatio: false, // PENTING: Agar ukuran tidak meledak
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>