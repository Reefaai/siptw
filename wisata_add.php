<?php
require_once 'koneksi.php';
session_start();

// Cek Login
if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

$kategori_result = mysqli_query($conn, "SELECT * FROM kategori");

if (isset($_POST['tambah'])) {
  // 1. Ambil data teks dari form
  $nama       = mysqli_real_escape_string($conn, $_POST['nama_wisata']);
  $lokasi     = mysqli_real_escape_string($conn, $_POST['lokasi']);
  $kategori   = mysqli_real_escape_string($conn, $_POST['kategori']);
  $deskripsi  = mysqli_real_escape_string($conn, $_POST['deskripsi']);

  // Data tambahan untuk template baru
  $harga      = !empty($_POST['harga_tiket']) ? intval($_POST['harga_tiket']) : 0;
  $jam        = mysqli_real_escape_string($conn, $_POST['jam_operasional']);
  $map        = mysqli_real_escape_string($conn, $_POST['link_google_map']);
  $fasilitas  = mysqli_real_escape_string($conn, $_POST['fasilitas']);

  // 2. Insert Data Utama dulu (Tanpa gambar) untuk mendapatkan ID
  $query_insert = "INSERT INTO wisata (nama_wisata, lokasi, deskripsi, kategori, harga_tiket, jam_operasional, link_google_map, fasilitas) 
                   VALUES ('$nama', '$lokasi', '$deskripsi', '$kategori', '$harga', '$jam', '$map', '$fasilitas')";

  if (mysqli_query($conn, $query_insert)) {
    $last_id = mysqli_insert_id($conn); // Ambil ID wisata yang baru saja dibuat

    // 3. PROSES UPLOAD BANYAK GAMBAR
    // Cek apakah ada file yang dipilih
    if (!empty($_FILES['foto']['name'][0])) {
      $total_files = count($_FILES['foto']['name']);

      // Looping semua file yang diupload
      for ($i = 0; $i < $total_files; $i++) {
        $tmp_name = $_FILES['foto']['tmp_name'][$i];
        $filename = $_FILES['foto']['name'][$i];

        // Buat nama unik: waktu_urutan_namafile
        $new_filename = time() . '_' . $i . '_' . $filename;

        // Upload file ke folder 'uploads'
        if (move_uploaded_file($tmp_name, 'uploads/' . $new_filename)) {

          // LOGIKA UTAMA:
          if ($i === 0) {
            // === FILE PERTAMA JADI HERO BANNER ===
            // Update tabel 'wisata' kolom 'gambar'
            mysqli_query($conn, "UPDATE wisata SET gambar='$new_filename' WHERE id='$last_id'");
          } else {
            // === FILE SISANYA JADI GALERI ===
            // Insert ke tabel 'wisata_galeri'
            mysqli_query($conn, "INSERT INTO wisata_galeri (id_wisata, nama_file) VALUES ('$last_id', '$new_filename')");
          }
        }
      }
    }

    header("Location: wisata_list.php");
    exit;
  } else {
    $error = "Gagal menambahkan data: " . mysqli_error($conn);
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tambah Wisata Baru</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

  <div class="container mt-5 mb-5">
    <div class="card shadow-lg">
      <div class="card-header bg-primary text-white">
        <h4 class="mb-0 text-center">Tambah Wisata Baru</h4>
      </div>
      <div class="card-body">
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="post" enctype="multipart/form-data">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Nama Wisata</label>
              <input type="text" name="nama_wisata" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Kategori</label>
              <select name="kategori" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php while ($kat = mysqli_fetch_assoc($kategori_result)) { ?>
                  <option value="<?= htmlspecialchars($kat['nama_kategori']); ?>">
                    <?= htmlspecialchars($kat['nama_kategori']); ?>
                  </option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Lokasi</label>
              <input type="text" name="lokasi" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Harga Tiket (Rp)</label>
              <input type="number" name="harga_tiket" class="form-control" placeholder="0">
            </div>
          </div>

          <div class="mb-4 p-3 bg-warning-subtle border border-warning rounded">
            <label class="form-label fw-bold text-dark">Upload Foto Wisata</label>
            <input type="file" name="foto[]" class="form-control" multiple required>
            <div class="form-text text-muted">
              <i class="bi bi-info-circle"></i>
              <strong>Tips:</strong> Tahan tombol <code>CTRL</code> (di Windows) atau <code>Command</code> (di Mac) untuk memilih banyak foto sekaligus.<br>
              ⭐ <strong>Foto yang pertama Anda pilih</strong> akan otomatis menjadi <strong>Hero Banner</strong> (Sampul Utama). Sisanya akan masuk ke Galeri.
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label fw-bold">Deskripsi Lengkap</label>
            <textarea name="deskripsi" rows="5" class="form-control" required></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Jam Operasional</label>
              <input type="text" name="jam_operasional" class="form-control" placeholder="08:00 - 17:00">
            </div>
            <div class="col-md-4">
              <label class="form-label">Link Google Maps</label>
              <input type="text" name="link_google_map" class="form-control" placeholder="https://maps.app.goo.gl/...">
            </div>
            <div class="col-md-4">
              <label class="form-label">Fasilitas</label>
              <input type="text" name="fasilitas" class="form-control" placeholder="WiFi, Parkir, Toilet">
            </div>
          </div>

          <div class="d-grid gap-2 mt-4">
            <button type="submit" name="tambah" class="btn btn-primary btn-lg">Simpan Semua Data</button>
            <a href="wisata_list.php" class="btn btn-outline-secondary">Batal</a>
          </div>

        </form>
      </div>
    </div>
  </div>

</body>

</html>