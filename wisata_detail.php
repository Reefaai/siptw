<?php
require_once 'koneksi.php';
session_start();

// 1. Cek Login (Tetap pertahankan keamanan)
if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

// 2. Ambil ID & Data dari Database
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  die("<div class='container mt-5 alert alert-danger'>Data wisata tidak ditemukan! <a href='wisata_list.php'>Kembali</a></div>");
}

// 3. Persiapan Data Tampilan (Agar tidak error jika data kosong)
$gambar_url = $data['gambar'] ? "uploads/" . $data['gambar'] : "https://via.placeholder.com/1200x600?text=No+Image";
$harga = number_format($data['harga_tiket'] ?? 0, 0, ',', '.'); // Default 0 jika kolom belum diisi
$jam_buka = $data['jam_operasional'] ?? '08:00 - 17:00';
$map_link = $data['link_google_map'] ?? '#';

// Explode fasilitas menjadi array (jika kolom fasilitas diisi: "WiFi,Parkir,Mushola")
$fasilitas_array = !empty($data['fasilitas']) ? explode(',', $data['fasilitas']) : ['Area Parkir', 'Toilet Umum', 'Spot Foto'];
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($data['nama_wisata']); ?> - Detail Wisata</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="style.css" rel="stylesheet">

  <style>
    /* Custom CSS untuk Hero Image */
    .hero-header {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('<?= $gambar_url; ?>');
      background-position: center;
      background-size: cover;
      height: 400px;
      display: flex;
      align-items: center;
      justify-content: center;
      color: white;
      text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
      margin-bottom: -50px;
      /* Agar konten naik sedikit */
      border-radius: 0 0 20px 20px;
    }

    .main-content {
      position: relative;
      z-index: 10;
      /* Agar tampil di atas background */
    }

    .card-info {
      border-top: 4px solid #0d6efd;
      /* Aksen biru di atas card */
    }

    .facility-icon {
      font-size: 1.2rem;
      width: 40px;
      height: 40px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      background-color: #e9ecef;
      border-radius: 50%;
      color: #0d6efd;
      margin-right: 10px;
    }
  </style>
</head>

<body class="bg-light">

  <nav class="navbar navbar-expand-lg navbar-dark bg-transparent position-absolute w-100" style="z-index: 99;">
    <div class="container">
      <a class="navbar-brand fw-bold" href="index.php">🌴 SIPTW</a>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto">
          <li class="nav-item"><a class="nav-link text-white" href="wisata_list.php">Kembali ke Daftar</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <header class="hero-header text-center">
    <div class="container">
      <h1 class="display-4 fw-bold"><?= htmlspecialchars($data['nama_wisata']); ?></h1>
      <p class="lead"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($data['lokasi']); ?> | <span class="badge bg-warning text-dark"><?= htmlspecialchars($data['kategori']); ?></span></p>
    </div>
  </header>

  <div class="container main-content pb-5">
    <div class="row">

      <div class="col-lg-8 mb-4">
        <div class="card shadow-sm border-0 rounded-4 p-4 mb-4">
          <h4 class="fw-bold text-primary mb-3">Tentang Destinasi Ini</h4>
          <p class="text-secondary" style="line-height: 1.8; text-align: justify;">
            <?= nl2br(htmlspecialchars($data['deskripsi'])); ?>
          </p>

          <hr class="my-4">

          <h5 class="fw-bold mb-3">Fasilitas Tersedia</h5>
          <div class="row g-3">
            <?php foreach ($fasilitas_array as $fasilitas): ?>
              <div class="col-md-6">
                <div class="d-flex align-items-center">
                  <div class="facility-icon"><i class="fas fa-check"></i></div>
                  <span><?= trim(htmlspecialchars($fasilitas)); ?></span>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        </div>

        <div class="card shadow-sm border-0 rounded-4 p-4">
          <!-- <h4 class="fw-bold text-primary mb-3">Galeri Foto</h4> -->
          <div class="row g-2">
            <div class="col-md-12">
              <!-- <img src="<?= $gambar_url; ?>" class="img-fluid rounded mb-2 w-100" style="height: 300px; object-fit: cover;"> -->
            </div>
            <div class="card shadow-sm border-0 rounded-4 p-4 mt-4">
              <h4 class="fw-bold text-primary mb-3">Galeri Foto</h4>
              <div class="row g-2">

                <div class="col-md-12">
                  <img src="uploads/<?= $data['gambar']; ?>" class="img-fluid rounded mb-2 w-100 shadow-sm" style="height: 400px; object-fit: cover;" alt="Foto Utama">
                </div>

                <?php
                // Query ambil foto tambahan
                $sql_galeri = mysqli_query($conn, "SELECT * FROM wisata_galeri WHERE id_wisata='$id'");

                // Cek jika ada foto galeri
                if (mysqli_num_rows($sql_galeri) > 0) {
                  while ($foto = mysqli_fetch_assoc($sql_galeri)) {
                ?>
                    <div class="col-6 col-md-4 col-lg-3">
                      <a href="uploads/<?= $foto['nama_file']; ?>" target="_blank">
                        <img src="uploads/<?= $foto['nama_file']; ?>" class="img-fluid rounded shadow-sm w-100" style="height: 150px; object-fit: cover; transition: transform 0.3s;" onmouseover="this.style.transform='scale(1.05)'" onmouseout="this.style.transform='scale(1)'">
                      </a>
                    </div>
                <?php
                  }
                } else {
                  // Pesan jika tidak ada foto tambahan
                  echo '<div class="col-12"><small class="text-muted">Belum ada foto tambahan di galeri.</small></div>';
                }
                ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg-4">
        <div class="card shadow card-info border-0 rounded-4 sticky-top" style="top: 20px;">
          <div class="card-body p-4">
            <h5 class="fw-bold mb-4">Informasi Praktis</h5>

            <div class="d-flex align-items-center mb-3">
              <div class="fs-2 text-success me-3"><i class="fas fa-tag"></i></div>
              <div>
                <small class="text-muted d-block">Harga Tiket Masuk</small>
                <span class="fw-bold fs-5">Rp <?= $harga; ?></span>
              </div>
            </div>

            <div class="d-flex align-items-center mb-3">
              <div class="fs-2 text-primary me-3"><i class="fas fa-clock"></i></div>
              <div>
                <small class="text-muted d-block">Jam Operasional</small>
                <span class="fw-bold"><?= htmlspecialchars($jam_buka); ?></span>
              </div>
            </div>

            <div class="d-grid gap-2 mt-4">
              <?php if ($data['link_google_map']): ?>
                <a href="<?= htmlspecialchars($data['link_google_map']); ?>" target="_blank" class="btn btn-primary">
                  <i class="fas fa-location-arrow"></i> Lihat di Google Maps
                </a>
              <?php else: ?>
                <button class="btn btn-secondary" disabled>Maps Tidak Tersedia</button>
              <?php endif; ?>

              <a href="wisata_list.php" class="btn btn-outline-secondary">Kembali ke Daftar</a>
            </div>

            <div class="mt-4 rounded overflow-hidden">
              <iframe
                width="100%"
                height="200"
                frameborder="0"
                style="border:0"
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d158858.47340002653!2d-0.2416813!3d51.5287718!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x487604b900d26973%3A0x4291f3172409ea92!2sLondon%2C%20UK!5e0!3m2!1sen!2sid!4v1626160000000!5m2!1sen!2sid"
                allowfullscreen>
              </iframe>
              <small class="text-muted text-center d-block mt-1">*Peta hanya ilustrasi</small>
            </div>

          </div>
        </div>
      </div>
    </div>
  </div>

  <footer class="bg-white text-center py-4 mt-5 border-top">
    <p class="mb-0 text-muted">© <?= date('Y'); ?> SIPTW - Sistem Informasi Pariwisata</p>
  </footer>

</body>

</html>