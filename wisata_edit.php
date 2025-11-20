<?php
require_once 'koneksi.php';
session_start();

// Cek Login
if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// --- PROSES HAPUS SATUAN FOTO GALERI (Via Link) ---
if (isset($_GET['hapus_galeri'])) {
  $id_galeri = intval($_GET['hapus_galeri']);

  // Ambil nama file dulu untuk dihapus dari folder
  $q_cek = mysqli_query($conn, "SELECT nama_file FROM wisata_galeri WHERE id='$id_galeri'");
  $data_img = mysqli_fetch_assoc($q_cek);

  if ($data_img) {
    $path = 'uploads/' . $data_img['nama_file'];
    if (file_exists($path)) {
      unlink($path); // Hapus file fisik
    }
    // Hapus data di database
    mysqli_query($conn, "DELETE FROM wisata_galeri WHERE id='$id_galeri'");
  }

  // Redirect agar URL bersih
  header("Location: wisata_edit.php?id=$id");
  exit;
}

// --- AMBIL DATA WISATA UTAMA ---
$result = mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'");
$data = mysqli_fetch_assoc($result);
if (!$data) {
  die("Data tidak ditemukan!");
}

$kategori_result = mysqli_query($conn, "SELECT * FROM kategori");

// --- PROSES UPDATE DATA ---
if (isset($_POST['update'])) {
  $nama       = mysqli_real_escape_string($conn, $_POST['nama_wisata']);
  $lokasi     = mysqli_real_escape_string($conn, $_POST['lokasi']);
  $kategori   = mysqli_real_escape_string($conn, $_POST['kategori']);
  $deskripsi  = mysqli_real_escape_string($conn, $_POST['deskripsi']);

  $harga      = !empty($_POST['harga_tiket']) ? intval($_POST['harga_tiket']) : 0;
  $jam        = mysqli_real_escape_string($conn, $_POST['jam_operasional']);
  $map        = mysqli_real_escape_string($conn, $_POST['link_google_map']);
  $fasilitas  = mysqli_real_escape_string($conn, $_POST['fasilitas']);

  // 1. Update Teks Dulu
  $query_update = "UPDATE wisata 
                     SET nama_wisata='$nama', lokasi='$lokasi', deskripsi='$deskripsi', 
                         kategori='$kategori', harga_tiket='$harga', jam_operasional='$jam', 
                         link_google_map='$map', fasilitas='$fasilitas' 
                     WHERE id='$id'";

  if (mysqli_query($conn, $query_update)) {

    // 2. PROSES FILE (Logika: File #1 Ganti Banner, Sisanya Masuk Galeri)
    if (!empty($_FILES['foto']['name'][0])) {
      $total_files = count($_FILES['foto']['name']);

      for ($i = 0; $i < $total_files; $i++) {
        $tmp_name = $_FILES['foto']['tmp_name'][$i];
        $filename = $_FILES['foto']['name'][$i];
        $new_filename = time() . '_' . $i . '_' . $filename;

        if (move_uploaded_file($tmp_name, 'uploads/' . $new_filename)) {

          if ($i === 0) {
            // === FILE PERTAMA: GANTI HERO BANNER ===

            // Hapus gambar lama fisik jika ada
            if (!empty($data['gambar']) && file_exists('uploads/' . $data['gambar'])) {
              unlink('uploads/' . $data['gambar']);
            }

            // Update Database
            mysqli_query($conn, "UPDATE wisata SET gambar='$new_filename' WHERE id='$id'");
          } else {
            // === FILE SISANYA: TAMBAH KE GALERI ===
            mysqli_query($conn, "INSERT INTO wisata_galeri (id_wisata, nama_file) VALUES ('$id', '$new_filename')");
          }
        }
      }
    }

    header("Location: wisata_list.php");
    exit;
  } else {
    $error = "Gagal update data: " . mysqli_error($conn);
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <title>Edit Wisata - SIPTW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-light">

  <div class="container mt-5 mb-5">
    <div class="card shadow-lg">
      <div class="card-header bg-warning text-dark">
        <h4 class="mb-0 text-center fw-bold">Edit Data Wisata</h4>
      </div>
      <div class="card-body">
        <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>

        <form method="post" enctype="multipart/form-data">

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Nama Wisata</label>
              <input type="text" name="nama_wisata" class="form-control" value="<?= htmlspecialchars($data['nama_wisata']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Kategori</label>
              <select name="kategori" class="form-select" required>
                <option value="">-- Pilih Kategori --</option>
                <?php while ($kat = mysqli_fetch_assoc($kategori_result)) { ?>
                  <option value="<?= htmlspecialchars($kat['nama_kategori']); ?>" <?= ($data['kategori'] == $kat['nama_kategori']) ? 'selected' : ''; ?>>
                    <?= htmlspecialchars($kat['nama_kategori']); ?>
                  </option>
                <?php } ?>
              </select>
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label fw-bold">Lokasi</label>
              <input type="text" name="lokasi" class="form-control" value="<?= htmlspecialchars($data['lokasi']); ?>" required>
            </div>
            <div class="col-md-6">
              <label class="form-label fw-bold">Harga Tiket (Rp)</label>
              <input type="number" name="harga_tiket" class="form-control" value="<?= $data['harga_tiket']; ?>">
            </div>
          </div>

          <div class="card bg-light border-warning mb-4">
            <div class="card-body">
              <h5 class="card-title fw-bold text-warning-emphasis"><i class="fas fa-images"></i> Pengaturan Gambar</h5>

              <div class="mb-3">
                <label class="form-label fw-bold">Hero Banner (Utama) Saat Ini:</label><br>
                <?php if ($data['gambar']): ?>
                  <img src="uploads/<?= $data['gambar']; ?>" class="img-thumbnail rounded shadow-sm" style="height: 150px; object-fit: cover;">
                <?php else: ?>
                  <span class="text-muted fst-italic">Belum ada gambar utama.</span>
                <?php endif; ?>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Galeri Foto Saat Ini:</label>
                <div class="d-flex flex-wrap gap-2">
                  <?php
                  $q_galeri = mysqli_query($conn, "SELECT * FROM wisata_galeri WHERE id_wisata='$id'");
                  if (mysqli_num_rows($q_galeri) > 0) {
                    while ($g = mysqli_fetch_assoc($q_galeri)):
                  ?>
                      <div class="position-relative">
                        <img src="uploads/<?= $g['nama_file']; ?>" class="img-thumbnail" style="width: 100px; height: 100px; object-fit: cover;">
                        <a href="wisata_edit.php?id=<?= $id; ?>&hapus_galeri=<?= $g['id']; ?>"
                          class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger text-decoration-none"
                          onclick="return confirm('Hapus foto galeri ini?')">
                          <i class="fas fa-times"></i>
                        </a>
                      </div>
                  <?php
                    endwhile;
                  } else {
                    echo '<small class="text-muted d-block">Belum ada foto galeri.</small>';
                  }
                  ?>
                </div>
              </div>

              <hr>

              <div class="mb-0">
                <label class="form-label fw-bold">Upload File Baru (Update/Tambah)</label>
                <input type="file" name="foto[]" class="form-control" multiple>
                <div class="form-text text-danger">
                  <strong>PERHATIAN:</strong><br>
                  1. File <strong>pertama</strong> yang Anda pilih akan <strong>MENGGANTI</strong> Hero Banner (Gambar Utama) di atas.<br>
                  2. File <strong>kedua dan seterusnya</strong> akan <strong>DITAMBAHKAN</strong> ke daftar Galeri.<br>
                  3. Jika tidak ingin mengubah gambar, biarkan kosong.
                </div>
              </div>
            </div>
          </div>
          <div class="mb-3">
            <label class="form-label fw-bold">Deskripsi Lengkap</label>
            <textarea name="deskripsi" rows="5" class="form-control" required><?= htmlspecialchars($data['deskripsi']); ?></textarea>
          </div>

          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Jam Operasional</label>
              <input type="text" name="jam_operasional" class="form-control" value="<?= htmlspecialchars($data['jam_operasional']); ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Link Google Maps</label>
              <input type="text" name="link_google_map" class="form-control" value="<?= htmlspecialchars($data['link_google_map']); ?>">
            </div>
            <div class="col-md-4">
              <label class="form-label">Fasilitas</label>
              <input type="text" name="fasilitas" class="form-control" value="<?= htmlspecialchars($data['fasilitas']); ?>">
            </div>
          </div>

          <div class="d-grid gap-2 mt-4">
            <button type="submit" name="update" class="btn btn-warning btn-lg fw-bold">Simpan Perubahan</button>
            <a href="wisata_list.php" class="btn btn-outline-secondary">Batal</a>
          </div>

        </form>
      </div>
    </div>
  </div>

</body>

</html>