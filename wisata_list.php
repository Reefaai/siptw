<?php
require_once 'koneksi.php';
session_start();

if (!isset($_SESSION['username'])) {
  header("Location: login.php");
  exit;
}

$level = $_SESSION['level'];
$keyword = isset($_GET['search']) ? $_GET['search'] : '';

if ($keyword) {
  global $conn;

  $keyword_safe = mysqli_real_escape_string($conn, $keyword);
  $result = mysqli_query($conn, "SELECT * FROM wisata 
        WHERE nama_wisata LIKE '%$keyword_safe%' OR lokasi LIKE '%$keyword_safe%' 
        ORDER BY id DESC");
} else {
  $result = mysqli_query($conn, "SELECT * FROM wisata ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Wisata - SIPTW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body>

  <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="index.php">🌴 SIPTW</a>
      <ul class="navbar-nav ms-auto">
        <?php if ($level == 'admin'): ?>
          <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard Admin</a></li>
          <li class="nav-item"><a class="nav-link" href="wisata_add.php">Tambah Wisata</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="user_dashboard.php">Dashboard User</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
      </ul>
    </div>
  </nav>

  <div class="container my-4">
    <h2 class="text-center text-primary mb-4">Daftar Tempat Wisata</h2>

    <form class="d-flex mb-4" method="get">
      <input class="form-control me-2" type="text" name="search" placeholder="Cari nama atau lokasi..." value="<?= htmlspecialchars($keyword) ?>">
      <button class="btn btn-primary" type="submit">Cari</button>
    </form>

    <?php if ($level == 'admin'): ?>
      <table class="table table-bordered table-hover shadow-sm">
        <thead class="table-primary text-center">
          <tr>
            <th>No</th>
            <th>Nama Wisata</th>
            <th>Lokasi</th>
            <th>Deskripsi</th>
            <th>Gambar</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php $no = 1;
          while ($row = mysqli_fetch_assoc($result)): ?>
            <tr>
              <td><?= $no++; ?></td>
              <td><?= htmlspecialchars($row['nama_wisata']); ?></td>
              <td><?= htmlspecialchars($row['lokasi']); ?></td>
              <td><?= htmlspecialchars(substr($row['deskripsi'], 0, 60)); ?>...</td>
              <td>
                <?php if ($row['gambar']): ?>
                  <img src="uploads/<?= $row['gambar']; ?>" width="100">
                <?php else: ?>
                  <span class="text-muted">Tidak ada</span>
                <?php endif; ?>
              </td>
              <td class="text-center">
                <a href="wisata_edit.php?id=<?= $row['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="wisata_delete.php?id=<?= $row['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Hapus data ini?')">Hapus</a>
              </td>
            </tr>
          <?php endwhile; ?>
        </tbody>
      </table>

    <?php else: ?>
      <div class="row row-cols-1 row-cols-md-3 g-4">
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
          <div class="col">
            <div class="card h-100 shadow-sm">
              <a href="wisata_detail.php?id=<?= $row['id']; ?>" class="text-decoration-none text-dark">
                <?php if ($row['gambar']): ?>
                  <img src="uploads/<?= $row['gambar']; ?>" class="card-img-top" alt="<?= htmlspecialchars($row['nama_wisata']); ?>">
                <?php else: ?>
                  <img src="https://via.placeholder.com/400x250?text=No+Image" class="card-img-top">
                <?php endif; ?>
                <div class="card-body">
                  <h5 class="card-title text-primary"><?= htmlspecialchars($row['nama_wisata']); ?></h5>
                  <p class="card-text"><strong>Lokasi:</strong> <?= htmlspecialchars($row['lokasi']); ?></p>
                  <p class="card-text"><?= nl2br(htmlspecialchars(substr($row['deskripsi'], 0, 100))); ?>...</p>
                </div>
              </a>
            </div>
          </div>
        <?php endwhile; ?>
      </div>
    <?php endif; ?>
  </div>

</body>

</html>