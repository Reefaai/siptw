<?php
require_once 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$result = mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

if (!$data) {
  die("Data tidak ditemukan!");
}

$kategori_result = mysqli_query($conn, "SELECT * FROM kategori");

if (isset($_POST['update'])) {
  $nama = mysqli_real_escape_string($conn, $_POST['nama_wisata']);
  $lokasi = mysqli_real_escape_string($conn, $_POST['lokasi']);
  $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
  $kategori = mysqli_real_escape_string($conn, $_POST['kategori']);
  $gambar = $data['gambar'];

  if (!empty($_FILES['gambar']['name'])) {
    $gambar = time() . '_' . basename($_FILES['gambar']['name']);
    move_uploaded_file($_FILES['gambar']['tmp_name'], 'uploads/' . $gambar);

    if (!empty($data['gambar']) && file_exists('uploads/' . $data['gambar'])) {
      unlink('uploads/' . $data['gambar']);
    }
  }

  $query = "UPDATE wisata 
              SET nama_wisata='$nama', lokasi='$lokasi', deskripsi='$deskripsi', gambar='$gambar', kategori='$kategori' 
              WHERE id='$id'";

  if (mysqli_query($conn, $query)) {
    header("Location: wisata_list.php");
    exit;
  } else {
    $error = "Gagal mengupdate data!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Edit Wisata - SIPTW</title>
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
        <h3 class="text-center text-warning mb-4">Edit Data Wisata</h3>
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
        <form method="post" enctype="multipart/form-data">
          <div class="mb-3">
            <label class="form-label">Nama Wisata</label>
            <input type="text" name="nama_wisata" class="form-control" value="<?= htmlspecialchars($data['nama_wisata']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Lokasi</label>
            <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($data['lokasi']); ?>" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Kategori</label>
            <select name="kategori" class="form-select" required>
              <option value="">-- Pilih Kategori --</option>
              <?php while ($kat = mysqli_fetch_assoc($kategori_result)) { ?>
                <option value="<?= htmlspecialchars($kat['nama_kategori']); ?>" <?= ($data['kategori'] == $kat['nama_kategori']) ? 'selected' : ''; ?>><?= htmlspecialchars($kat['nama_kategori']); ?></option>
              <?php } ?>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Deskripsi</label>
            <textarea name="deskripsi" rows="5" class="form-control" required><?= htmlspecialchars($data['deskripsi']); ?></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Gambar Saat Ini</label><br>
            <?php if ($data['gambar']): ?>
              <img src="uploads/<?= $data['gambar']; ?>" width="150" class="rounded shadow-sm mb-2">
            <?php else: ?>
              <p class="text-muted">Belum ada gambar</p>
            <?php endif; ?>
            <input type="file" name="gambar" class="form-control mt-2">
          </div>
          <button type="submit" name="update" class="btn btn-warning w-100">Simpan Perubahan</button>
        </form>
      </div>
    </div>
  </div>

</body>

</html>