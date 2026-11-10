<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: ../index.php?pesan=belum_login");
    exit;
}

require_once __DIR__ . '/../koneksi.php';

$koneksi =$koneksi ?? null;
if (!$koneksi || !($koneksi instanceof mysqli)) {
    die('Koneksi database tidak tersedia.');
}

// Mengambil ringkasan data penting
$query_menu = mysqli_query($koneksi, "SELECT COUNT(*) as total_menu FROM menu");
$data_menu = mysqli_fetch_assoc($query_menu);
$total_menu =$data_menu['total_menu'];

$total_transaksi = 0;
$total_pendapatan = 0;
$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) as total_trx, SUM(total_harga) as pendapatan FROM transaksi");
if ($query_transaksi) {
    $data_trx = mysqli_fetch_assoc($query_transaksi);
    $total_transaksi =$data_trx['total_trx'];
    $total_pendapatan =$data_trx['pendapatan'] ?? 0;
}

$query_riwayat = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | RPL Coffee &amp; Eatery</title>
    <link rel="icon" type="image/png" href="../logo/LOGO.png">
    <link rel="apple-touch-icon" href="../logo/LOGO.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="theme.css" rel="stylesheet">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        
        <!-- Header Sambutan -->
        <div class="mb-4">
            <h4 class="fw-bold text-dark mb-1">Dashboard</h4>
            <p class="text-muted small mb-0">Selamat datang, <strong><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></strong></p>
        </div>

        <!-- Kartu Statistik -->
        <div class="row mb-4">
            <!-- Card 1: Total Menu -->
            <div class="col-md-4 mb-3">
                <div class="card berry-card-primary stat-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase fw-semibold fs-7 mb-1">Total Menu</h6>
                                <h3 class="fw-bold mb-0"><?= $total_menu; ?></h3>
                            </div>
                            <div class="stat-icon" aria-hidden="true">MENU</div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light border-opacity-10">
                            <a href="menu.php" class="text-decoration-none small fw-semibold">Kelola Menu &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Total Transaksi -->
            <div class="col-md-4 mb-3">
                <div class="card berry-card-success stat-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase fw-semibold fs-7 mb-1">Total Transaksi</h6>
                                <h3 class="fw-bold mb-0"><?= $total_transaksi; ?></h3>
                            </div>
                            <div class="stat-icon" aria-hidden="true">TRX</div>
                        </div>
                        <div class="mt-3 pt-3 border-top border-light border-opacity-10">
                            <a href="riwayat.php" class="text-decoration-none small fw-semibold">Lihat Riwayat &rarr;</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 3: Total Pendapatan -->
            <div class="col-md-4 mb-3">
                <div class="card berry-card-dark stat-card h-100">
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h6 class="text-uppercase fw-semibold fs-7 mb-1">Pendapatan</h6>
                                <h3 class="fw-bold mb-0 fs-4">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                            </div>
                            <div class="stat-icon" aria-hidden="true">Rp</div>
                        </div>
                        <div class="mt-3 pt-3 border-top small">
                            Total Penjualan
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat Transaksi Terbaru -->
        <div class="card shadow-sm">
            <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold mb-0 text-dark fs-6">Transaksi Terbaru</h5>
                <a href="riwayat.php" class="btn btn-light btn-sm text-primary fw-semibold">Lihat Semua</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="ps-4" width="5%">No</th>
                                <th>Kode</th>
                                <th>Waktu</th>
                                <th>Metode</th>
                                <th class="pe-4 text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (mysqli_num_rows($query_riwayat) > 0) : ?>
                                <?php $no = 1; while ($t = mysqli_fetch_assoc($query_riwayat)) : ?>
                                    <tr>
                                        <td class="ps-4"><?= $no++; ?></td>
                                        <td><?= htmlspecialchars($t['kode_transaksi'] ?? $t['id']); ?></td>
                                        <td><?= htmlspecialchars($t['tanggal'] ?? '-'); ?></td>
                                        <td><?= htmlspecialchars($t['metode_pembayaran'] ?? '-'); ?></td>
                                        <td class="pe-4 text-end">Rp <?= number_format((float) ($t['total_harga'] ?? 0), 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else : ?>
                                <tr>
                                    <td colspan="5" class="py-4 text-center text-muted">Belum ada transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>