<?php
require_once 'koneksi.php';
session_start();

// --- 1. LOGIK PHP (JANGAN DIHAPUS) ---

// Cek Login (Simpan ID wisata jika belum login)
if (!isset($_SESSION['username'])) {
  $_SESSION['redirect_after_login'] = "wisata_detail.php?id=" . $_GET['id'];
}

// Ambil ID User (Jika sudah login)
$id_user_login = 0;
if (isset($_SESSION['username'])) {
  $username = $_SESSION['username'];
  $user_query = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
  $user_data = mysqli_fetch_assoc($user_query);
  $id_user_login = $user_data['id'];
}

// Ambil Data Wisata
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  die("Data tidak ditemukan!");
}

// Persiapan Data Tampilan
$gambar_url = $data['gambar'] ? "uploads/" . $data['gambar'] : "https://via.placeholder.com/1920x600?text=No+Image";
$harga = $data['harga_tiket'];
$harga_fmt = number_format($harga, 0, ',', '.');
$jam_buka = $data['jam_operasional'] ?? '08:00 - 17:00';
$fasilitas_array = !empty($data['fasilitas']) ? explode(',', $data['fasilitas']) : ['Parkir Area', 'Toilet', 'Spot Foto'];

// --- PROSES PEMESANAN TIKET ---
if (isset($_POST['pesan_tiket'])) {
  // Cek Login Lagi saat Submit
  if (!isset($_SESSION['username'])) {
    echo "<script>alert('Silakan login terlebih dahulu untuk memesan tiket.'); window.location='login.php';</script>";
    exit;
  }

  $tgl_kunjungan = $_POST['tanggal'];
  $jml_tiket     = intval($_POST['jumlah']);

  if ($jml_tiket < 1) {
    $error = "Minimal pemesanan 1 tiket.";
  } elseif (empty($tgl_kunjungan)) {
    $error = "Tanggal kunjungan harus diisi.";
  } else {
    $total_bayar = $jml_tiket * $harga;
    $kode_trx = "TRX-" . time() . "-" . $id_user_login;

    $query_insert = "INSERT INTO transaksi (kode_transaksi, id_user, id_wisata, tanggal_kunjungan, jumlah_tiket, total_harga, status) 
                         VALUES ('$kode_trx', '$id_user_login', '$id', '$tgl_kunjungan', '$jml_tiket', '$total_bayar', 'confirmed')";

    if (mysqli_query($conn, $query_insert)) {
      echo "<script>alert('Pemesanan Berhasil!'); window.location.href='tiket_saya.php';</script>";
      exit;
    } else {
      $error = "Gagal memproses pesanan: " . mysqli_error($conn);
    }
  }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($data['nama_wisata']); ?> - Detail Wisata</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

  <style>
    @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9f9;
    }

    /* --- HERO BANNER & NAVBAR --- */
    .hero-header {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.7)), url('<?= $gambar_url; ?>');
      background-position: center;
      background-size: cover;
      height: 60vh;
      /* Tinggi Banner Besar */
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-align: center;
      margin-bottom: -80px;
      /* Agar konten masuk sedikit ke atas banner */
      position: relative;
    }

    /* Navbar Transparan ke Solid */
    .navbar {
      transition: all 0.4s ease;
      padding: 15px 0;
    }

    .navbar-transparent {
      background: transparent !important;
      box-shadow: none;
    }

    .navbar-transparent .nav-link,
    .navbar-transparent .navbar-brand {
      color: white !important;
    }

    .navbar-solid {
      background: white !important;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
      padding: 15px 0;
    }

    .navbar-solid .nav-link,
    .navbar-solid {
      color: #333 !important;
    }

    .nav-link-2:hover {
      color: white !important;
    }

    .nav-link:hover {
      color: #0d6efd !important;
    }

    .navbar-brand {
      color: #0d6efd
    }

    .navbar-brand:hover {
      color: #0d6efd
    }

    /* --- CONTENT STYLES --- */
    .main-content {
      position: relative;
      z-index: 10;
    }

    .info-box {
      background: white;
      padding: 30px;
      border-radius: 15px;
      box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .facility-box {
      background: #fff;
      border: 1px solid #eee;
      padding: 15px;
      border-radius: 10px;
      text-align: center;
      transition: 0.3s;
    }

    .facility-box:hover {
      transform: translateY(-3px);
      box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
      border-color: #0d6efd;
    }

    .facility-icon {
      font-size: 1.5rem;
      color: #0d6efd;
      margin-bottom: 10px;
      display: block;
    }

    .price-tag {
      font-size: 1.8rem;
      font-weight: 700;
      color: #0d6efd;
    }

    .gallery-img {
      width: 100%;
      height: 150px;
      object-fit: cover;
      border-radius: 10px;
      transition: transform 0.3s;
      cursor: pointer;
    }

    .gallery-img:hover {
      transform: scale(1.03);
    }
  </style>
</head>

<body>

  <nav class="navbar navbar-expand-lg fixed-top navbar-transparent" id="mainNav">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php"><i class="fas fa-map-marked-alt"></i> SIPTW</a>
      <button class="navbar-toggler bg-light" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
        <span class="navbar-toggler-icon"></span>
      </button>
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
          <li class="nav-item"><a class="nav-link" href="wisata_list.php">Destinasi</a></li>
          <li class="nav-item"><a class="nav-link" href="tiket_saya.php">Tiket Saya</a></li>
          <?php if (isset($_SESSION['username'])): ?>
            <li class="nav-item fw-bold"><a class="nav-link nav-link-2 btn btn-danger text-white px-3 ms-2 rounded-pill" href="logout.php">Logout</a></li>
          <?php else: ?>
            <li class="nav-item fw-bold"><a class="nav-link nav-link-2 btn btn-primary text-white px-3 ms-2 rounded-pill" href="login.php">Login</a></li>
          <?php endif; ?>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero-header">
    <div class="container">
      <span class="badge bg-warning text-dark mb-2 fs-6 px-3 py-2 rounded-pill"><?= htmlspecialchars($data['kategori']); ?></span>
      <h1 class="display-3 fw-bold"><?= htmlspecialchars($data['nama_wisata']); ?></h1>
      <p class="lead"><i class="fas fa-map-marker-alt me-2"></i> <?= htmlspecialchars($data['lokasi']); ?></p>
    </div>
  </header>

  <div class="container main-content pb-5">
    <div class="row">

      <div class="col-lg-8">
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
          <h4 class="fw-bold text-primary mb-3">Tentang Destinasi</h4>
          <p class="text-secondary" style="line-height: 1.8; text-align: justify;">
            <?= nl2br(htmlspecialchars($data['deskripsi'])); ?>
          </p>
        </div>

        <div class="mb-4">
          <h5 class="fw-bold mb-3 text-dark"><i class="fas fa-concierge-bell me-2 text-primary"></i>Fasilitas Tersedia</h5>
          <div class="row g-3">
            <?php foreach ($fasilitas_array as $f): ?>
              <div class="col-6 col-md-4">
                <div class="facility-box">
                  <i class="fas fa-check-circle facility-icon"></i>
                  <span class="fw-bold small"><?= trim($f); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 p-4 mt-4">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold m-0 text-primary"><i class="fas fa-images me-2"></i>Galeri Foto</h5>
          </div>
          <div class="row g-2">
            <div class="col-md-12 mb-2">
              <img src="<?= $gambar_url; ?>" class="img-fluid rounded shadow-sm w-100" style="height: 350px; object-fit: cover;" alt="Foto Utama">
            </div>

            <?php
            $sql_galeri = mysqli_query($conn, "SELECT * FROM wisata_galeri WHERE id_wisata='$id'");
            if (mysqli_num_rows($sql_galeri) > 0) {
              while ($foto = mysqli_fetch_assoc($sql_galeri)) {
            ?>
                <div class="col-6 col-md-4">
                  <a href="uploads/<?= $foto['nama_file']; ?>" target="_blank">
                    <img src="uploads/<?= $foto['nama_file']; ?>" class="gallery-img shadow-sm" alt="Galeri">
                  </a>
                </div>
            <?php
              }
            } else {
              echo '<div class="col-12 text-center text-muted small py-3">Belum ada foto tambahan.</div>';
            }
            ?>
          </div>
        </div>
      </div>

      <div class="col-lg-4 mt-4 mt-lg-0">
        <div class="info-box position-sticky" style="top: 100px;">

          <div class="text-center border-bottom pb-3 mb-3">
            <small class="text-muted text-uppercase fw-bold ls-1">Harga Tiket Masuk</small>
            <div class="price-tag my-1">Rp <?= $harga_fmt; ?></div>
            <small class="text-muted">per orang</small>
          </div>

          <form method="post">
            <div class="mb-3">
              <label class="form-label fw-bold small">Tanggal Kunjungan</label>
              <input type="date" name="tanggal" class="form-control" required min="<?= date('Y-m-d'); ?>">
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold small">Jumlah Tiket</label>
              <div class="input-group">
                <span class="input-group-text bg-white"><i class="fas fa-user-friends"></i></span>
                <input type="number" name="jumlah" id="jumlah" class="form-control" min="1" value="1" required>
              </div>
            </div>

            <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded mb-3">
              <span class="fw-bold small">Total Estimasi</span>
              <span class="fw-bold text-primary fs-5" id="total_display">Rp <?= $harga_fmt; ?></span>
            </div>

            <button type="submit" name="pesan_tiket" class="btn btn-primary w-100 btn-lg fw-bold shadow-sm rounded-pill">
              Pesan Tiket <i class="fas fa-arrow-right ms-2"></i>
            </button>
          </form>

          <div class="mt-4 pt-3 border-top">
            <h6 class="fw-bold mb-3">Informasi Penting</h6>

            <div class="d-flex align-items-start mb-3">
              <i class="fas fa-clock text-warning mt-1 me-3"></i>
              <div>
                <small class="fw-bold d-block text-dark">Jam Operasional</small>
                <span class="text-muted small"><?= htmlspecialchars($jam_buka); ?></span>
              </div>
            </div>

            <div class="d-flex align-items-start mb-3">
              <i class="fas fa-map-marker-alt text-danger mt-1 me-3"></i>
              <div>
                <small class="fw-bold d-block text-dark">Alamat Lokasi</small>
                <span class="text-muted small"><?= htmlspecialchars($data['lokasi']); ?></span>
              </div>
            </div>

            <?php if (!empty($data['link_google_map'])): ?>
              <a href="<?= htmlspecialchars($data['link_google_map']); ?>" target="_blank" class="btn btn-outline-secondary w-100 btn-sm">
                <i class="fas fa-map-marked-alt me-2"></i> Buka di Google Maps
              </a>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>
  </div>

  <script>
    // 1. Navbar Scroll Effect
    const navbar = document.getElementById('mainNav');
    window.addEventListener('scroll', () => {
      if (window.scrollY > 50) {
        navbar.classList.remove('navbar-transparent');
        navbar.classList.add('navbar-solid');
      } else {
        navbar.classList.add('navbar-transparent');
        navbar.classList.remove('navbar-solid');
      }
    });

    // 2. Hitung Total Harga Otomatis
    const hargaTiket = <?= $harga; ?>;
    const inputJumlah = document.getElementById('jumlah');
    const displayTotal = document.getElementById('total_display');

    inputJumlah.addEventListener('input', function() {
      let qty = parseInt(this.value);
      if (isNaN(qty) || qty < 1) qty = 1;
      let total = qty * hargaTiket;
      displayTotal.innerText = 'Rp ' + total.toLocaleString('id-ID');
    });
  </script>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>