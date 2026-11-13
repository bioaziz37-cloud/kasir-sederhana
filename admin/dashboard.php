<?php
require_once __DIR__ . '/../koneksi.php';
require_login();

// 1. Data total menu
$total_menu = 0;
$query_menu = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM menu");
if ($query_menu && ($row = mysqli_fetch_assoc($query_menu))) {
    $total_menu = (int) $row['total'];
}

// 2. Data transaksi & pendapatan
$total_transaksi = 0;
$total_pendapatan = 0;
$query_transaksi = mysqli_query($koneksi, "SELECT COUNT(*) as total_trx, COALESCE(SUM(total_harga), 0) as pendapatan FROM transaksi");
if ($query_transaksi && ($row = mysqli_fetch_assoc($query_transaksi))) {
    $total_transaksi = (int) $row['total_trx'];
    $total_pendapatan = (float) $row['pendapatan'];
}

// 3. 5 Transaksi terbaru
$query_riwayat = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id DESC LIMIT 5");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - RPL Coffee &amp; Eatery</title>
    <link rel="icon" type="image/png" href="../logo/LOGO.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="theme.css" rel="stylesheet">
</head>
<body>

    <?php include __DIR__ . '/sidebar.php'; ?>

    <main class="main-content">
        <header class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-subtitle">Selamat datang kembali, <strong><?= e($_SESSION['nama_lengkap'] ?? 'Staf'); ?></strong>.</p>
            </div>
            <a href="kasir.php" class="btn-clean btn-clean-primary">Buka Kasir</a>
        </header>

        <section class="row g-3 mb-4">
            <div class="col-md-4">
                <div class="stat-box">
                    <div>
                        <div class="stat-label">Total Menu Aktif</div>
                        <div class="stat-value num"><?= number_format($total_menu, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-footer">
                        <a href="menu.php">Kelola daftar menu &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-box">
                    <div>
                        <div class="stat-label">Total Transaksi</div>
                        <div class="stat-value num"><?= number_format($total_transaksi, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-footer">
                        <a href="riwayat.php">Lihat semua riwayat &rarr;</a>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="stat-box">
                    <div>
                        <div class="stat-label">Total Penjualan</div>
                        <div class="stat-value num text-dark">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></div>
                    </div>
                    <div class="stat-footer">
                        Akumulasi keseluruhan transaksi
                    </div>
                </div>
            </div>
        </section>

        <section class="card-clean">
            <div class="card-clean-header">
                <h2 class="card-clean-title">Transaksi Terbaru</h2>
                <a href="riwayat.php" class="btn-clean btn-clean-secondary btn-clean-sm">Lihat Semua</a>
            </div>
            <div class="table-responsive">
                <table class="table-clean">
                    <thead>
                        <tr>
                            <th style="width: 60px;">No</th>
                            <th>Kode Transaksi</th>
                            <th>Nama Pemesan</th>
                            <th>Waktu</th>
                            <th>Metode</th>
                            <th class="text-end">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_riwayat && mysqli_num_rows($query_riwayat) > 0) : ?>
                            <?php $no = 1; while ($trx = mysqli_fetch_assoc($query_riwayat)) : ?>
                                <tr>
                                    <td class="text-secondary"><?= $no++; ?></td>
                                    <td class="fw-semibold">
                                        <a href="cetak_struk.php?kode=<?= urlencode($trx['kode_transaksi']); ?>" target="_blank" class="text-decoration-none text-dark">
                                            <?= e($trx['kode_transaksi']); ?>
                                        </a>
                                    </td>
                                    <td><?= e($trx['nama_pemesan'] ?: '-'); ?></td>
                                    <td class="text-secondary"><?= e($trx['tanggal']); ?></td>
                                    <td>
                                        <span class="badge-clean <?= ($trx['metode_pembayaran'] === 'QRIS') ? 'drink' : 'neutral'; ?>">
                                            <?= e($trx['metode_pembayaran'] ?? 'Tunai'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end fw-semibold num">
                                        Rp <?= number_format((float) $trx['total_harga'], 0, ',', '.'); ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Belum ada riwayat transaksi.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
