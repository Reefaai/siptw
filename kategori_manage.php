<?php
require_once 'koneksi.php';
session_start();

// Cek Login Admin
if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

// --- LOGIC PHP ---

// 1. Tambah Kategori
if (isset($_POST['tambah'])) {
  $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
  if (!empty($nama)) {
    mysqli_query($conn, "INSERT INTO kategori (nama_kategori) VALUES ('$nama')");
    $_SESSION['pesan'] = "Kategori berhasil ditambahkan!";
    $_SESSION['tipe'] = "success";
  }
  header("Location: kategori_manage.php");
  exit;
}

// 2. Edit Kategori
if (isset($_POST['edit'])) {
  $id = intval($_POST['id']);
  $nama = mysqli_real_escape_string($conn, $_POST['nama_kategori']);
  if (!empty($nama)) {
    mysqli_query($conn, "UPDATE kategori SET nama_kategori='$nama' WHERE id='$id'");
    $_SESSION['pesan'] = "Kategori berhasil diperbarui!";
    $_SESSION['tipe'] = "primary";
  }
  header("Location: kategori_manage.php");
  exit;
}

// 3. Hapus Kategori
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);

  // Cek apakah kategori sedang dipakai di wisata? (Opsional, biar aman)
  $cek = mysqli_query($conn, "SELECT * FROM wisata WHERE kategori = (SELECT nama_kategori FROM kategori WHERE id='$id')");
  if (mysqli_num_rows($cek) > 0) {
    $_SESSION['pesan'] = "Gagal! Kategori ini sedang digunakan oleh data wisata.";
    $_SESSION['tipe'] = "danger";
  } else {
    mysqli_query($conn, "DELETE FROM kategori WHERE id='$id'");
    $_SESSION['pesan'] = "Kategori berhasil dihapus!";
    $_SESSION['tipe'] = "warning";
  }
  header("Location: kategori_manage.php");
  exit;
}

// Ambil Data
$result = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola Kategori - SIPTW</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
    }

    /* Navbar Styles (Konsisten) */
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

    /* Card Styles */
    .card-manage {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      background: white;
    }

    /* Table Styles */
    .table-hover tbody tr:hover {
      background-color: #f8f9fa;
    }

    .btn-action {
      width: 35px;
      height: 35px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      margin: 0 3px;
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
          <li class="nav-item"><a class="nav-link" href="admin_dashboard.php">Dashboard</a></li>
          <li class="nav-item"><a class="nav-link" href="wisata_list.php">Kelola Wisata</a></li>
          <li class="nav-item"><a class="nav-link active" href="kategori_manage.php">Kategori</a></li>
          <li class="nav-item"><a class="nav-link" href="user_manage.php">Users</a></li>
          <li class="nav-item ms-3"><a class="btn btn-outline-danger btn-sm rounded-pill px-3" href="logout.php">Logout</a></li>
        </ul>
      </div>
    </div>
  </nav>

  <div class="container my-5">

    <?php if (isset($_SESSION['pesan'])): ?>
      <div class="alert alert-<?= $_SESSION['tipe']; ?> alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
        <i class="fas fa-info-circle me-2"></i> <?= $_SESSION['pesan']; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      </div>
      <?php unset($_SESSION['pesan']);
      unset($_SESSION['tipe']); ?>
    <?php endif; ?>

    <div class="row justify-content-center">
      <div class="col-lg-8">

        <div class="d-flex justify-content-between align-items-center mb-4">
          <h3 class="fw-bold mb-0 text-dark">Manajemen Kategori</h3>
          <span class="badge bg-primary rounded-pill px-3 py-2">Total: <?= mysqli_num_rows($result); ?></span>
        </div>

        <div class="card card-manage mb-4">
          <div class="card-body p-4">
            <h6 class="fw-bold text-muted mb-3 text-uppercase small">Tambah Kategori Baru</h6>
            <form method="post">
              <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fas fa-tag text-muted"></i></span>
                <input type="text" name="nama_kategori" class="form-control border-start-0 bg-light" placeholder="Contoh: Wisata Kuliner..." required>
                <button type="submit" name="tambah" class="btn btn-primary px-4 fw-bold"><i class="fas fa-plus me-2"></i> Simpan</button>
              </div>
            </form>
          </div>
        </div>

        <div class="card card-manage overflow-hidden">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
              <thead class="table-light small text-uppercase text-muted">
                <tr>
                  <th class="py-3 ps-4" width="10%">No</th>
                  <th class="py-3">Nama Kategori</th>
                  <th class="py-3 text-center" width="25%">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $no = 1;
                while ($row = mysqli_fetch_assoc($result)):
                ?>
                  <tr>
                    <td class="ps-4 fw-bold text-muted"><?= $no++; ?></td>
                    <td class="fw-bold text-dark">
                      <span class="badge bg-primary-subtle text-primary rounded-pill px-3">
                        <?= htmlspecialchars($row['nama_kategori']); ?>
                      </span>
                    </td>
                    <td class="text-center">
                      <button class="btn btn-warning btn-action btn-sm text-dark shadow-sm"
                        data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>"
                        title="Edit">
                        <i class="fas fa-pen" style="font-size: 0.8rem;"></i>
                      </button>

                      <a href="?hapus=<?= $row['id']; ?>"
                        class="btn btn-danger btn-action btn-sm text-white shadow-sm"
                        onclick="return confirm('Yakin ingin menghapus kategori ini? Data wisata yang menggunakan kategori ini mungkin akan error.')"
                        title="Hapus">
                        <i class="fas fa-trash" style="font-size: 0.8rem;"></i>
                      </a>
                    </td>
                  </tr>

                  <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                      <div class="modal-content border-0 shadow">
                        <form method="post">
                          <div class="modal-header bg-primary text-white">
                            <h5 class="modal-title fw-bold"><i class="fas fa-edit me-2"></i> Edit Kategori</h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                          </div>
                          <div class="modal-body p-4">
                            <input type="hidden" name="id" value="<?= $row['id']; ?>">
                            <div class="mb-3">
                              <label class="form-label fw-bold">Nama Kategori</label>
                              <input type="text" name="nama_kategori" value="<?= htmlspecialchars($row['nama_kategori']); ?>" class="form-control" required>
                            </div>
                          </div>
                          <div class="modal-footer bg-light">
                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="edit" class="btn btn-primary btn-sm px-4 fw-bold">Simpan Perubahan</button>
                          </div>
                        </form>
                      </div>
                    </div>
                  </div>
                <?php endwhile; ?>
              </tbody>
            </table>
          </div>
        </div>

      </div>
    </div>
  </div>

  <footer class="text-center py-4 text-muted mt-5 border-top bg-white">
    <small>&copy; <?= date('Y'); ?> SIPTW Admin Panel.</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>