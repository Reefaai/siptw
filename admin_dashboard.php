<?php
require_once 'koneksi.php';

session_start();

if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

// Hitung total
$total_wisata = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM wisata"))['total'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE level='user'"))['total'];
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE level='admin'"))['total'];

// Statistik kategori wisata
$kategori_result = mysqli_query($conn, "SELECT kategori, COUNT(*) AS jumlah FROM wisata GROUP BY kategori");
$kategori = [];
$jumlah = [];
while ($row = mysqli_fetch_assoc($kategori_result)) {
  $kategori[] = $row['kategori'];
  $jumlah[] = $row['jumlah'];
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Dashboard Admin - SIPTW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link href="style.css" rel="stylesheet">
  <style>
    .card-stat {
      border-left: 6px solid;
      border-radius: 12px;
      transition: 0.3s;
    }

    .card-stat:hover {
      transform: scale(1.03);
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
      <a class="navbar-brand fw-bold" href="#">SIPTW</a>
      <ul class="navbar-nav ms-auto">
        <li class="nav-item"><a class="nav-link" href="wisata_list.php">Kelola Wisata</a></li>
        <li class="nav-item"><a class="nav-link" href="kategori_manage.php">Kelola Kategori</a></li>
        <li class="nav-item"><a class="nav-link" href="user_manage.php">Kelola User</a></li>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-5">
    <h2 class="text-center mb-4 text-primary">Dashboard Administrator</h2>

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card card-stat shadow-sm border-0" style="border-left-color:#0d6efd;">
          <div class="card-body">
            <h5 class="card-title text-primary">Total Wisata</h5>
            <p class="fs-2 fw-bold"><?= $total_wisata; ?></p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-stat shadow-sm border-0" style="border-left-color:#198754;">
          <div class="card-body">
            <h5 class="card-title text-success">Total User</h5>
            <p class="fs-2 fw-bold"><?= $total_user; ?></p>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card card-stat shadow-sm border-0" style="border-left-color:#ffc107;">
          <div class="card-body">
            <h5 class="card-title text-warning">Total Admin</h5>
            <p class="fs-2 fw-bold"><?= $total_admin; ?></p>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="text-center text-primary mb-3">📊 Statistik Kategori Wisata</h5>
            <canvas id="chartKategori"></canvas>
          </div>
        </div>
      </div>

      <div class="col-lg-6">
        <div class="card shadow-sm border-0">
          <div class="card-body">
            <h5 class="text-center text-success mb-3">👥 Perbandingan User & Admin</h5>
            <canvas id="chartUser"></canvas>
          </div>
        </div>
      </div>
    </div>

    <hr class="my-5">

    <div class="text-center">
      <a href="wisata_list.php" class="btn btn-primary btn-lg m-2">🗺️ Kelola Tempat Wisata</a>
      <a href="kategori_manage.php" class="btn btn-warning btn-lg m-2">🏷️ Kelola Kategori</a>
      <a href="user_manage.php" class="btn btn-success btn-lg m-2">👥 Kelola User</a>
    </div>
  </div>

  <script>
    const ctx1 = document.getElementById('chartKategori');
    if (ctx1) {
      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: <?= json_encode($kategori); ?>,
          datasets: [{
            label: 'Jumlah Wisata',
            data: <?= json_encode($jumlah); ?>,
            backgroundColor: 'rgba(13,110,253,0.7)',
            borderColor: '#0d6efd',
            borderWidth: 1
          }]
        },
        options: {
          responsive: true,
          scales: {
            y: {
              beginAtZero: true
            }
          }
        }
      });
    }

    const ctx2 = document.getElementById('chartUser');
    if (ctx2) {
      new Chart(ctx2, {
        type: 'doughnut',
        data: {
          labels: ['User', 'Admin'],
          datasets: [{
            data: [<?= $total_user; ?>, <?= $total_admin; ?>],
            backgroundColor: ['#198754', '#ffc107']
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              position: 'bottom'
            }
          }
        }
      });
    }
  </script>

</body>

</html>