<?php
require_once 'koneksi.php';
session_start();

// Cek Login Admin
if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
  header("Location: login.php");
  exit;
}

// --- LOGIC PHP ---

// 1. Tambah User Baru
if (isset($_POST['tambah'])) {
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Enkripsi password
  $level    = mysqli_real_escape_string($conn, $_POST['level']);

  // Cek apakah username sudah ada?
  $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username'");
  if (mysqli_num_rows($check) > 0) {
    $_SESSION['pesan'] = "Gagal! Username sudah digunakan.";
    $_SESSION['tipe'] = "danger";
  } else {
    mysqli_query($conn, "INSERT INTO users (username, password, level) VALUES ('$username', '$password', '$level')");
    $_SESSION['pesan'] = "User baru berhasil ditambahkan!";
    $_SESSION['tipe'] = "success";
  }
  header("Location: user_manage.php");
  exit;
}

// 2. Edit User
if (isset($_POST['edit'])) {
  $id       = intval($_POST['id']);
  $username = mysqli_real_escape_string($conn, $_POST['username']);
  $level    = mysqli_real_escape_string($conn, $_POST['level']);

  // Cek jika password diisi (berarti mau ganti password)
  if (!empty($_POST['password'])) {
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $query = "UPDATE users SET username='$username', password='$password', level='$level' WHERE id='$id'";
  } else {
    // Jika kosong, update data lain saja tanpa ubah password
    $query = "UPDATE users SET username='$username', level='$level' WHERE id='$id'";
  }

  if (mysqli_query($conn, $query)) {
    $_SESSION['pesan'] = "Data user berhasil diperbarui!";
    $_SESSION['tipe'] = "primary";
  }
  header("Location: user_manage.php");
  exit;
}

// 3. Hapus User
if (isset($_GET['hapus'])) {
  $id = intval($_GET['hapus']);

  // Cek agar tidak menghapus diri sendiri
  $me = $_SESSION['username'];
  $target_query = mysqli_query($conn, "SELECT username FROM users WHERE id='$id'");
  $target = mysqli_fetch_assoc($target_query);

  if ($target && $target['username'] == $me) {
    $_SESSION['pesan'] = "Bahaya! Anda tidak bisa menghapus akun sendiri.";
    $_SESSION['tipe'] = "danger";
  } else {
    mysqli_query($conn, "DELETE FROM users WHERE id='$id'");
    $_SESSION['pesan'] = "User berhasil dihapus!";
    $_SESSION['tipe'] = "warning";
  }
  header("Location: user_manage.php");
  exit;
}

// Ambil Data Users
$result = mysqli_query($conn, "SELECT * FROM users ORDER BY level ASC, username ASC");
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Kelola User - SIPTW</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f0f2f5;
    }

    /* Navbar (Konsisten) */
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

    /* Components */
    .card-manage {
      border: none;
      border-radius: 15px;
      box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
      background: white;
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

    .avatar-circle {
      width: 40px;
      height: 40px;
      background-color: #e9ecef;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
      color: #555;
      margin-right: 15px;
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
          <li class="nav-item"><a class="nav-link" href="kategori_manage.php">Kategori</a></li>
          <li class="nav-item"><a class="nav-link active" href="user_manage.php">Users</a></li>
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

    <div class="d-flex justify-content-between align-items-center mb-4">
      <h3 class="fw-bold mb-0 text-dark">Manajemen Pengguna</h3>
      <button class="btn btn-primary rounded-pill px-4 shadow-sm fw-bold" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-user-plus me-2"></i> Tambah User
      </button>
    </div>

    <div class="card card-manage overflow-hidden">
      <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
          <thead class="table-light small text-uppercase text-muted">
            <tr>
              <th class="py-3 ps-4">User Info</th>
              <th class="py-3 text-center">Level Akses</th>
              <th class="py-3 text-center">Aksi</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = mysqli_fetch_assoc($result)):
              // Initials untuk Avatar
              $initial = strtoupper(substr($row['username'], 0, 1));
              $is_admin = ($row['level'] == 'admin');
            ?>
              <tr>
                <td class="ps-4">
                  <div class="d-flex align-items-center">
                    <div class="avatar-circle shadow-sm text-white <?= $is_admin ? 'bg-warning' : 'bg-success'; ?>">
                      <?= $initial; ?>
                    </div>
                    <div>
                      <span class="d-block fw-bold text-dark"><?= htmlspecialchars($row['username']); ?></span>
                      <small class="text-muted">ID: #<?= $row['id']; ?></small>
                    </div>
                  </div>
                </td>
                <td class="text-center">
                  <?php if ($is_admin): ?>
                    <span class="badge bg-warning text-dark rounded-pill px-3"><i class="fas fa-crown me-1"></i> Administrator</span>
                  <?php else: ?>
                    <span class="badge bg-success-subtle text-success rounded-pill px-3"><i class="fas fa-user me-1"></i> User Biasa</span>
                  <?php endif; ?>
                </td>
                <td class="text-center">
                  <button class="btn btn-outline-primary btn-action btn-sm" data-bs-toggle="modal" data-bs-target="#editModal<?= $row['id']; ?>" title="Edit User">
                    <i class="fas fa-cog"></i>
                  </button>

                  <?php if ($row['username'] == $_SESSION['username']): ?>
                    <button class="btn btn-secondary btn-action btn-sm" disabled title="Tidak bisa hapus diri sendiri"><i class="fas fa-trash"></i></button>
                  <?php else: ?>
                    <a href="?hapus=<?= $row['id']; ?>" class="btn btn-outline-danger btn-action btn-sm" onclick="return confirm('Yakin ingin menghapus user ini?')" title="Hapus User">
                      <i class="fas fa-trash"></i>
                    </a>
                  <?php endif; ?>
                </td>
              </tr>

              <div class="modal fade" id="editModal<?= $row['id']; ?>" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content border-0 shadow">
                    <form method="post">
                      <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold">Edit User: <?= htmlspecialchars($row['username']); ?></h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body p-4">
                        <input type="hidden" name="id" value="<?= $row['id']; ?>">

                        <div class="mb-3">
                          <label class="form-label fw-bold">Username</label>
                          <input type="text" name="username" value="<?= htmlspecialchars($row['username']); ?>" class="form-control" required>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-bold">Reset Password</label>
                          <input type="password" name="password" class="form-control" placeholder="(Kosongkan jika tidak ingin mengubah)">
                          <small class="text-muted">Isi hanya jika ingin mengganti password user ini.</small>
                        </div>

                        <div class="mb-3">
                          <label class="form-label fw-bold">Level Akses</label>
                          <select name="level" class="form-select">
                            <option value="user" <?= ($row['level'] == 'user') ? 'selected' : ''; ?>>User Biasa</option>
                            <option value="admin" <?= ($row['level'] == 'admin') ? 'selected' : ''; ?>>Administrator</option>
                          </select>
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

  <div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content border-0 shadow">
        <form method="post">
          <div class="modal-header bg-success text-white">
            <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i> Tambah User Baru</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body p-4">
            <div class="mb-3">
              <label class="form-label fw-bold">Username</label>
              <input type="text" name="username" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label fw-bold">Level Akses</label>
              <select name="level" class="form-select">
                <option value="user">User Biasa</option>
                <option value="admin">Administrator</option>
              </select>
            </div>
          </div>
          <div class="modal-footer bg-light">
            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
            <button type="submit" name="tambah" class="btn btn-success btn-sm px-4 fw-bold">Tambah User</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <footer class="text-center py-4 text-muted mt-5 border-top bg-white">
    <small>&copy; <?= date('Y'); ?> SIPTW Admin Panel.</small>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>