<?php
require_once 'koneksi.php';
session_start();

if (!isset($_SESSION['username']) || $_SESSION['level'] != 'admin') {
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id']);

// ambil data untuk hapus gambar juga
$result = mysqli_query($conn, "SELECT * FROM wisata WHERE id='$id'");
$data = mysqli_fetch_assoc($result);

if ($data) {
    if (!empty($data['gambar']) && file_exists('uploads/' . $data['gambar'])) {
        unlink('uploads/' . $data['gambar']);
    }

    mysqli_query($conn, "DELETE FROM wisata WHERE id='$id'");
}

header("Location: wisata_list.php");
exit;
