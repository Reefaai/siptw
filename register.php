<?php
require_once 'koneksi.php';


if (isset($_POST['register'])) {
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
  $level = 'user';

  $username_safe = mysqli_real_escape_string($conn, $username);
  $check = mysqli_query($conn, "SELECT * FROM users WHERE username='$username_safe'");
  if (mysqli_num_rows($check) > 0) {
    $error = "Username sudah digunakan!";
  } else {
    $password_safe = mysqli_real_escape_string($conn, $password);
    $query = "INSERT INTO users (username, password, level) VALUES ('$username_safe', '$password_safe', '$level')";
    if (mysqli_query($conn, $query)) {
      header("Location: login.php?registered=1");
      exit;
    } else {
      $error = "Gagal registrasi!";
    }
  }
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Register - SIPTW</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="style.css" rel="stylesheet">
</head>

<body class="bg-light">

  <div class="container mt-5">
    <div class="row justify-content-center">
      <div class="col-md-4">
        <div class="card shadow">
          <div class="card-body">
            <h3 class="text-center text-success">Daftar User</h3>
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
              <button type="submit" name="register" class="btn btn-success w-100">Daftar</button>
            </form>
            <div class="text-center mt-3">
              <a href="login.php">Sudah punya akun? Login</a><br>
              <a href="index.php">← Kembali ke Beranda</a>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

</body>

</html>