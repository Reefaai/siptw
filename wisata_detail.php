<?php
require_once 'koneksi.php';
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$id = intval($_GET['id']);
$result = mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  die("Data wisata tidak ditemukan!");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($data['nama_wisata']); ?> - SIPTW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">🌴 SIPTW</a>
      <ul class="navbar-nav ms-auto">
        <?php if ($_SESSION['level'] == 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard Admin</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Dashboard User</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container mt-4 mb-5">
    <div class="card shadow-lg">
      <?php if ($data['gambar']): ?>
        <img src="uploads/<?= $data['gambar']; ?>" class="card-img-top" alt="<?= htmlspecialchars($data['nama_wisata']); ?>">
      <?php else: ?>
        <img src="https://via.placeholder.com/800x400?text=No+Image" class="card-img-top">
      <?php endif; ?>

      <div class="card-body">
        <h2 class="card-title text-primary"><?= htmlspecialchars($data['nama_wisata']); ?></h2>
        <h5 class="text-muted">📍 Lokasi: <?= htmlspecialchars($data['lokasi']); ?> | 🏷️ Kategori: <?= htmlspecialchars($data['kategori']); ?></h5>
        <hr>
        <p class="card-text fs-5"><?= nl2br(htmlspecialchars($data['deskripsi'])); ?></p>
        <a href="wisata_list.php" class="btn btn-outline-primary mt-3">← Kembali ke Daftar</a>
      </div>
    </div>
  </div>

</body>

</html>