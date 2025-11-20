<?php
require_once 'koneksi.php';
require_once 'config.php';
session_start();

if (isset($_POST['login'])) {
  global $conn;

  $username = $_POST['username'];
  $password = $_POST['password'];

  $username_safe = mysqli_real_escape_string($conn, $username);
  $query = mysqli_query($conn, "SELECT * FROM users WHERE username='$username_safe'");
  $user = mysqli_fetch_assoc($query);

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['username'] = $user['username'];
    $_SESSION['level'] = $user['level'];

    if ($user['level'] == 'admin') {
      header("Location: admin_dashboard.php");
    } else {
      header("Location: user_dashboard.php");
    }
    exit;
  } else {
    $error = "Username atau password salah!";
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - SIPTW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-body">
            <h3 class="text-center text-primary">Login</h3>
            <?php if (isset($_GET['registered'])) echo "<div class='alert alert-success'>Registrasi berhasil. Silakan login.</div>"; ?>
            <?php if (isset($error)) echo "<div class='alert alert-danger'>$error</div>"; ?>
            <form method="post">
              <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
              </div>
              <button type="submit" name="login" class="btn btn-primary w-100">Masuk</button>
            </form>
            <div class="text-center mt-3">
              <a href="register.php">Belum punya akun? Daftar</a><br>
              <a href="index.php">← Kembali ke Beranda</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>