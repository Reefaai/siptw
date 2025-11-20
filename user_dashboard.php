<?php
session_start();
if (!isset($_SESSION['level']) || $_SESSION['level'] != 'user') {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Dashboard User - SIPTW</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container">
    <a class="navbar-brand" href="index.php">🌴 SIPTW</a>
    <ul class="navbar-nav ms-auto">
      <li class="nav-item"><a class="nav-link" href="wisata_list.php">Daftar Wisata</a></li>
      <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
    </ul>
  </div>
</nav>
<div class="container mt-5">
  <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
  <p>Anda login sebagai User biasa.</p>
  <a href="wisata_list.php" class="btn btn-success">Lihat Daftar Wisata</a>
</div>
</body>
</html>