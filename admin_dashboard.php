<?php
require_once 'koneksi.php';
session_start();

// --- 1. CEK KEAMANAN ---
if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

// --- 2. QUERY DATA STATISTIK (KPIs) ---
$total_wisata = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM wisata"))['total'];
$total_user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE level='user'"))['total'];
$total_admin = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE level='admin'"))['total'];
$total_kategori = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori"))['total'];

// --- 3. QUERY UNTUK CHART ---

// A. Bar Chart: Kategori
$kategori_result = mysqli_query($conn, "SELECT kategori, COUNT(*) AS jumlah FROM wisata GROUP BY kategori");
$label_kat = [];
$data_kat = [];
while ($row = mysqli_fetch_assoc($kategori_result)) {
  $label_kat[] = $row['kategori'];
  $data_kat[] = $row['jumlah'];
}

// B. (BARU) Line Chart: Tren Kunjungan 7 Hari ke Depan
// Mengambil jumlah tiket berdasarkan tanggal kunjungan
$query_kunjungan = "SELECT tanggal_kunjungan, SUM(jumlah_tiket) as total_pengunjung
                    FROM transaksi
                    WHERE tanggal_kunjungan >= CURDATE() AND tanggal_kunjungan <= DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                    GROUP BY tanggal_kunjungan ORDER BY tanggal_kunjungan ASC";
$res_kunjungan = mysqli_query($conn, $query_kunjungan);
$label_tgl_kunjungan = [];
$data_total_pengunjung = [];
while ($row = mysqli_fetch_assoc($res_kunjungan)) {
  $label_tgl_kunjungan[] = date('d M', strtotime($row['tanggal_kunjungan'])); // Format: 24 Nov
  $data_total_pengunjung[] = $row['total_pengunjung'];
}

// C. (BARU) Line Chart: Tren Pendapatan 7 Hari Terakhir
// Mengambil total harga berdasarkan tanggal transaksi dibuat
$query_pendapatan = "SELECT DATE(created_at) as tgl_transaksi, SUM(total_harga) as total_uang
                     FROM transaksi
                     WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                     GROUP BY DATE(created_at) ORDER BY DATE(created_at) ASC";
$res_pendapatan = mysqli_query($conn, $query_pendapatan);
$label_tgl_bayar = [];
$data_total_pendapatan = [];
while ($row = mysqli_fetch_assoc($res_pendapatan)) {
  $label_tgl_bayar[] = date('d M', strtotime($row['tgl_transaksi']));
  $data_total_pendapatan[] = $row['total_uang'];
}

// --- 4. QUERY DATA TERBARU ---
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
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
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
      opacity: 0.2;
    }

    .bg-gradient-blue {
      background: linear-gradient(135deg, #0d6efd, #0dcaf0);
    }

    .bg-gradient-green {
      background: linear-gradient(135deg, #198754, #20c997);
    }

    .bg-gradient-orange {
      background: linear-gradient(135deg, #fd7e14, #ffc107);
    }

    .bg-gradient-purple {
      background: linear-gradient(135deg, #6f42c1, #a060f8);
    }

    /* Content & Charts */
    .content-card {
      background: white;
      border-radius: 15px;
      border: none;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
    }

    /* Container untuk mengatur tinggi chart */
    .chart-container-bar {
      position: relative;
      height: 300px;
      width: 100%;
    }

    .chart-container-doughnut {
      position: relative;
      height: 250px;
      width: 100%;
      display: flex;
      justify-content: center;
    }

    /* (BARU) Container untuk Line Chart */
    .chart-container-line {
      position: relative;
      height: 350px;
      width: 100%;
    }

    .table-img {
      width: 50px;
      height: 40px;
      object-fit: cover;
      border-radius: 6px;
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
          <li class="nav-item ms-3"><a class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold" href="logout.php">Logout <i class="fas fa-sign-out-alt ms-1"></i></a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container my-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h2 class="fw-bold text-dark mb-0">Dashboard Overview</h2>
        <p class="text-muted">Selamat datang, <strong>Administrator <?= htmlspecialchars($_SESSION['username']); ?></strong>!</p>
      </div>
      <a href="wisata_add.php" class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold"><i class="fas fa-plus me-2"></i> Tambah Wisata Baru</a>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-blue h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 small opacity-75 fw-bold">Total Wisata</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_wisata; ?></h2>
            <i class="fas fa-map-marked-alt icon-bg"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-purple h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 small opacity-75 fw-bold">Total Kategori</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_kategori; ?></h2>
            <i class="fas fa-tags icon-bg"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-green h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 small opacity-75 fw-bold">Pengguna Terdaftar</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_user; ?></h2>
            <i class="fas fa-users icon-bg"></i>
          </div>
        </div>
      </div>
      <div class="col-md-3">
        <div class="card stat-card bg-gradient-orange h-100 p-3">
          <div class="card-body">
            <h6 class="text-uppercase mb-1 small opacity-75 fw-bold">Administrator</h6>
            <h2 class="fw-bold display-5 mb-0"><?= $total_admin; ?></h2>
            <i class="fas fa-user-shield icon-bg"></i>
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-lg-6">
        <div class="card content-card p-4 h-100">
          <h5 class="fw-bold text-primary mb-3"><i class="fas fa-calendar-alt me-2"></i> Tren Kunjungan (7 Hari ke Depan)</h5>
          <div class="chart-container-line">
            <canvas id="chartKunjungan"></canvas>
          </div>
        </div>
      </div>
      <div class="col-lg-6">
        <div class="card content-card p-4 h-100">
          <h5 class="fw-bold text-success mb-3"><i class="fas fa-money-bill-wave me-2"></i> Tren Penjualan Tiket (7 Hari Terakhir)</h5>
          <div class="chart-container-line">
            <canvas id="chartPendapatan"></canvas>
          </div>
        </div>
      </div>
    </div>


    <div class="row g-4">

      <div class="col-lg-7">

        <div class="card content-card p-4 mb-4">
          <h5 class="fw-bold text-primary mb-3"><i class="fas fa-chart-bar me-2"></i> Statistik Kategori Wisata</h5>
          <div class="chart-container-bar">
            <canvas id="chartKategori"></canvas>
          </div>
        </div>

        <div class="card content-card p-4">
          <h5 class="fw-bold text-warning mb-3"><i class="fas fa-chart-pie me-2"></i> Komposisi Pengguna</h5>
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
                  <th class="text-end pe-4">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php if (mysqli_num_rows($terbaru_result) > 0): ?>
                  <?php while ($t = mysqli_fetch_assoc($terbaru_result)):
                    $img = $t['gambar'] ? "uploads/" . $t['gambar'] : "https://via.placeholder.com/50x40?text=No+Img";
                  ?>
                    <tr>
                      <td class="ps-4">
                        <div class="d-flex align-items-center">
                          <img src="<?= $img; ?>" class="table-img me-3 shadow-sm" alt="Thumb">
                          <div>
                            <span class="d-block fw-bold text-dark text-truncate" style="max-width: 150px;"><?= htmlspecialchars($t['nama_wisata']); ?></span>
                            <small class="text-muted" style="font-size: 11px;"><i class="fas fa-map-marker-alt me-1"></i> <?= htmlspecialchars($t['lokasi']); ?></small>
                          </div>
                        </div>
                      </td>
                      <td><span class="badge bg-primary-subtle text-primary rounded-pill px-2"><?= htmlspecialchars($t['kategori']); ?></span></td>
                      <td class="text-end pe-4">
                        <a href="wisata_edit.php?id=<?= $t['id']; ?>" class="btn btn-sm btn-outline-warning rounded-circle" title="Edit"><i class="fas fa-pen" style="font-size: 0.8rem"></i></a>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="3" class="text-center py-5 text-muted fst-italic">Belum ada data wisata yang ditambahkan.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
          <div class="p-3 text-center border-top bg-white mt-auto">
            <a href="wisata_list.php" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-4">Lihat Semua Data <i class="fas fa-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>

    </div>
  </div>

  <footer class="text-center py-4 text-muted mt-5 border-top bg-white">
    <small class="fw-bold">&copy; <?= date('Y'); ?> SIPTW Admin Panel. Dibuat dengan Bootstrap 5 & Chart.js</small>
  </footer>


  <script>
    Chart.defaults.maintainAspectRatio = false;
    Chart.defaults.plugins.legend.position = 'bottom';
    Chart.defaults.font.family = "'Poppins', sans-serif";

    // --- 1. CHART KUNJUNGAN (Line Chart) ---
    const ctxKunjungan = document.getElementById('chartKunjungan');
    if (ctxKunjungan) {
      new Chart(ctxKunjungan, {
        type: 'line',
        data: {
          labels: <?= json_encode($label_tgl_kunjungan); ?>,
          datasets: [{
            label: 'Estimasi Pengunjung',
            data: <?= json_encode($data_total_pengunjung); ?>,
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            fill: true, // Area di bawah garis diwarnai
            tension: 0.4 // Garis melengkung halus
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
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

    // --- 2. CHART PENDAPATAN (Line Chart - Area) ---
    const ctxPendapatan = document.getElementById('chartPendapatan');
    if (ctxPendapatan) {
      new Chart(ctxPendapatan, {
        type: 'line',
        data: {
          labels: <?= json_encode($label_tgl_bayar); ?>,
          datasets: [{
            label: 'Total Pendapatan (Rp)',
            data: <?= json_encode($data_total_pendapatan); ?>,
            borderColor: '#198754',
            backgroundColor: 'rgba(25, 135, 84, 0.2)',
            fill: true,
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              // Format sumbu Y jadi Rupiah
              ticks: {
                callback: function(value) {
                  return 'Rp ' + value.toLocaleString('id-ID');
                }
              }
            },
            x: {
              grid: {
                display: false
              }
            }
          },
          plugins: {
            tooltip: {
              callbacks: {
                // Format tooltip jadi Rupiah
                label: function(context) {
                  let label = context.dataset.label || '';
                  if (label) {
                    label += ': ';
                  }
                  if (context.parsed.y !== null) {
                    label += new Intl.NumberFormat('id-ID', {
                      style: 'currency',
                      currency: 'IDR'
                    }).format(context.parsed.y);
                  }
                  return label;
                }
              }
            }
          }
        }
      });
    }

    // --- 3. CHART KATEGORI (Bar) ---
    const ctx1 = document.getElementById('chartKategori');
    if (ctx1) {
      new Chart(ctx1, {
        type: 'bar',
        data: {
          labels: <?= json_encode($label_kat); ?>,
          datasets: [{
            label: 'Jumlah Wisata',
            data: <?= json_encode($data_kat); ?>,
            backgroundColor: ['rgba(13, 110, 253, 0.8)', 'rgba(25, 135, 84, 0.8)', 'rgba(255, 193, 7, 0.8)', 'rgba(220, 53, 69, 0.8)', 'rgba(13, 202, 240, 0.8)', 'rgba(111, 66, 193, 0.8)'],
            borderRadius: 8,
            borderWidth: 0
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: {
              display: false
            }
          },
          scales: {
            y: {
              beginAtZero: true,
              ticks: {
                precision: 0
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

    // --- 4. CHART USER (Doughnut) ---
    const ctx2 = document.getElementById('chartUser');
    if (ctx2) {
      new Chart(ctx2, {
        type: 'doughnut',
        data: {
          labels: ['User Biasa', 'Administrator'],
          datasets: [{
            data: [<?= $total_user; ?>, <?= $total_admin; ?>],
            backgroundColor: ['#198754', '#fd7e14'],
            borderWidth: 3,
            borderColor: '#ffffff',
            hoverOffset: 10
          }]
        },
        options: {
          responsive: true,
          cutout: '60%'
        }
      });
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>