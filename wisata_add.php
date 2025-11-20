<?php
require_once 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

$kategori_result = mysqli_query($conn, "SELECT * FROM kategori");

if (isset($_POST['tambah'])) {
  $nama = mysqli_real_escape_string($conn, $_POST['nama_wisata']);
  $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
  $gambar = '';

  if (!empty($_FILES['gambar']['name'])) {
    $gambar = time() . '_' . basename($_FILES['gambar']['name']);
    move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar);
  }

  $query = "INSERT INTO wisata (nama_wisata, lokasi, deskripsi, gambar, kategori) 
              VALUES ('$nama', '$lokasi', '$deskripsi', '$gambar', '$kategori')";

  if (mysqli_query($conn, $query)) {
    header("Location: wisata_list.php");
    exit;
  } else {
    $error = "Gagal menambahkan data!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Wisata - SIPTW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

  <nav class="navbar navbar-dark bg-primary">
    <div class="container">
      <a class="navbar-brand" href="admin_dashboard.php">🌴 SIPTW Admin</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </nav>

  <div class="container mt-5">
    <div class="card shadow">
      <div class="card-body">
        <h3 class="text-center text-primary mb-4">Tambah Tempat Wisata</h3>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Nama Wisata</label>
            <input type="text" name="nama_wisata" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <input type="text" name="lokasi" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-select" required>
              <option value="">-- Pilih Kategori --</option>
              <?php while ($kat = mysqli_fetch_assoc($kategori_result)) { ?>
                <option value="<?= htmlspecialchars($kat['nama_kategori']); ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" rows="5" class="form-control" required></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Gambar</label>
            <input type="file" name="gambar" class="form-control">
          </div>
          <button type="submit" name="tambah" class="btn btn-success w-100">Simpan</button>
        </form>
      </div>
    </div>
  </div>

</body>

</html>