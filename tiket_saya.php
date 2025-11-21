<?php
require_once 'koneksi.php';
session_start();

// Cek Login
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit;
}

$username = $_SESSION['username'];

// Ambil ID User
$user_query = mysqli_query($conn, "SELECT id FROM users WHERE username='$username'");
$user = mysqli_fetch_assoc($user_query);
$id_user = $user['id'];

// Query Ambil Data Transaksi JOIN dengan Data Wisata
$query = "SELECT t.*, w.nama_wisata, w.lokasi, w.gambar 
          FROM transaksi t 
          JOIN wisata w ON t.id_wisata = w.id 
          WHERE t.id_user = '$id_user' 
          ORDER BY t.created_at DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tiket Saya - SIPTW</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&family=Courier+Prime:wght@400;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f2f5;
        }

        /* Navbar */
        .navbar {
            background: white;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
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

        .nav-link:hover {
            color: #0d6efd !important;
        }

        /* --- PERBAIKAN CSS TIKET --- */
        .ticket-container {
            margin-bottom: 30px;
            filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.1));
            /* Shadow dipindah ke luar agar radius aman */
        }

        .ticket-card {
            display: flex;
            flex-direction: column;
            background: transparent;
            /* Transparan agar radius anak elemen terlihat */
        }

        /* Tampilan Desktop (Horizontal) */
        @media (min-width: 768px) {
            .ticket-card {
                flex-direction: row;
            }

            /* Bagian Kiri (Biru) */
            .ticket-left {
                border-radius: 20px 0 0 20px !important;
                /* Rounded Kiri Atas & Bawah */
                border-right: 3px dashed rgba(255, 255, 255, 0.4);
                /* Garis Sobekan Vertikal */
                min-width: 220px;
            }

            /* Bagian Kanan (Putih) */
            .ticket-right {
                border-radius: 0 20px 20px 0 !important;
                /* Rounded Kanan Atas & Bawah */
            }
        }

        /* Tampilan Mobile (Vertikal) */
        @media (max-width: 767px) {
            .ticket-left {
                border-radius: 20px 20px 0 0 !important;
                border-bottom: 3px dashed rgba(255, 255, 255, 0.4);
                /* Garis Sobekan Horizontal */
                padding: 20px !important;
            }

            .ticket-right {
                border-radius: 0 0 20px 20px !important;
            }
        }

        .ticket-left {
            background: linear-gradient(135deg, #0d6efd, #0dcaf0);
            color: white;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .ticket-right {
            flex: 1;
            background: white;
            padding: 30px;
            position: relative;
            z-index: 1;
        }

        .ticket-code {
            font-family: 'Courier Prime', monospace;
            letter-spacing: 2px;
            background: #f8f9fa;
            padding: 8px 12px;
            border-radius: 8px;
            font-weight: bold;
            color: #333;
            font-size: 1rem;
            border: 1px dashed #ccc;
            display: inline-block;
        }

        .qr-placeholder {
            width: 90px;
            height: 90px;
            background-color: white;
            padding: 5px;
            border: 1px solid #eee;
            border-radius: 8px;
        }

        .qr-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 15px;
            border-radius: 50px;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 15px;
        }

        .status-confirmed {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffecb5;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-map-marked-alt"></i> SIPTW</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="wisata_list.php">Destinasi</a></li>
                    <li class="nav-item"><a class="nav-link active text-primary" href="tiket_saya.php">Tiket Saya</a></li>
                    <!-- <li class="nav-item"><a class="nav-link text-danger ms-2" href="logout.php">Logout</a></li> -->
                    <a class="nav-link text-danger" href="logout.php"><i class="fas fa-sign-out-alt"></i></a>

                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="fw-bold mb-4 text-dark"><i class="fas fa-ticket-alt me-2 text-primary"></i>Tiket Saya</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <div class="row">
                <?php while ($row = mysqli_fetch_assoc($result)):
                    $statusClass = ($row['status'] == 'confirmed') ? 'status-confirmed' : 'status-pending';
                    $tgl_hari = date('d', strtotime($row['tanggal_kunjungan']));
                    $tgl_bulan = date('M Y', strtotime($row['tanggal_kunjungan']));
                ?>
                    <div class="col-lg-10 offset-lg-1">

                        <div class="ticket-container">
                            <div class="ticket-card" id="tiket-<?= $row['id']; ?>">

                                <div class="ticket-left">
                                    <h1 class="display-3 fw-bold mb-0"><?= $tgl_hari; ?></h1>
                                    <span class="text-uppercase fw-bold ls-1"><?= $tgl_bulan; ?></span>
                                    <div class="mt-4 small opacity-75 border-top border-white pt-3 w-100">
                                        <i class="fas fa-clock me-1"></i> 08:00 - 17:00
                                    </div>
                                </div>

                                <div class="ticket-right">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <h4 class="fw-bold text-primary mb-1 text-uppercase"><?= htmlspecialchars($row['nama_wisata']); ?></h4>
                                            <p class="text-muted small"><i class="fas fa-map-marker-alt me-1 text-danger"></i> <?= htmlspecialchars($row['lokasi']); ?></p>
                                        </div>
                                        <span class="status-badge <?= $statusClass; ?>"><?= $row['status']; ?></span>
                                    </div>

                                    <div class="row mt-3 align-items-center">
                                        <div class="col-md-4 mb-3">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Kode Booking</small>
                                            <div class="ticket-code mt-1"><?= $row['kode_transaksi']; ?></div>
                                        </div>
                                        <div class="col-6 col-md-4 mb-3">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Jumlah Tiket</small>
                                            <span class="fw-bold fs-5 text-dark"><?= $row['jumlah_tiket']; ?> Orang</span>
                                        </div>
                                        <div class="col-6 col-md-4 mb-3">
                                            <small class="text-muted d-block text-uppercase fw-bold" style="font-size: 10px;">Total Bayar</small>
                                            <span class="fw-bold text-success fs-5">Rp <?= number_format($row['total_harga'], 0, ',', '.'); ?></span>
                                        </div>
                                    </div>

                                    <hr class="my-2 border-light">

                                    <div class="d-flex justify-content-between align-items-end">
                                        <div>
                                            <small class="text-muted fst-italic" style="font-size: 11px; line-height: 1.4;">
                                                *Simpan gambar ini sebagai bukti.<br>Tunjukkan kepada petugas di loket.
                                            </small>
                                        </div>
                                        <div class="qr-placeholder">
                                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= $row['kode_transaksi']; ?>" class="qr-img" alt="QR">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end mb-5">
                            <button onclick="downloadTiket(<?= $row['id']; ?>, '<?= $row['kode_transaksi']; ?>')" class="btn btn-primary rounded-pill px-4 shadow-sm">
                                <i class="fas fa-download me-2"></i> Download Tiket (PNG)
                            </button>
                        </div>

                    </div>
                <?php endwhile; ?>
            </div>

        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-ticket-alt fa-5x text-muted opacity-25 mb-3"></i>
                <h4 class="text-muted">Belum ada tiket yang dipesan.</h4>
                <a href="wisata_list.php" class="btn btn-primary rounded-pill px-4 mt-3">Cari Destinasi</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function downloadTiket(id, kode) {
            const tiketElement = document.getElementById('tiket-' + id);

            // Opsi html2canvas untuk hasil lebih tajam
            html2canvas(tiketElement, {
                scale: 2,
                backgroundColor: null, // Transparan agar sudut rounded tidak kotak putih
                useCORS: true // Agar gambar QR code terload
            }).then(canvas => {
                const link = document.createElement('a');
                link.download = 'Tiket-' + kode + '.png';
                link.href = canvas.toDataURL("image/png");
                link.click();
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>