<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: ../index.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
$koneksi = $GLOBALS['koneksi'];

$tanggal_mulai = $_GET['tanggal_mulai'] ?? '';
$tanggal_selesai = $_GET['tanggal_selesai'] ?? '';

$query_transaksi = !empty($tanggal_mulai) && !empty($tanggal_selesai)
    ? mysqli_query($koneksi, "SELECT * FROM transaksi WHERE DATE(tanggal) BETWEEN '$tanggal_mulai' AND '$tanggal_selesai' ORDER BY id DESC")
    : mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi | RPL Coffee &amp; Eatery</title>
    <link rel="icon" type="image/png" href="../logo/LOGO.png">
    <link rel="apple-touch-icon" href="../logo/LOGO.png">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="theme.css" rel="stylesheet">
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="main-content">
        <!-- Judul Halaman Tanpa Kotak -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold text-dark mb-1">Riwayat Transaksi Penjualan</h4>
                <p class="text-muted small mb-0">Pantau dan telusuri data transaksi penjualan toko.</p>
            </div>
            <span class="text-muted small fw-semibold">Panel Admin</span>
        </div>

        <div class="container-fluid p-0">
            <!-- Form Filter Tanggal -->
            <div class="card shadow-sm mb-4">
                <div class="card-body">
                    <form method="GET" action="" class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label for="tanggal_mulai" class="form-label small fw-bold">Dari Tanggal:</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_mulai" name="tanggal_mulai" value="<?= $tanggal_mulai; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label for="tanggal_selesai" class="form-label small fw-bold">Sampai Tanggal:</label>
                            <input type="date" class="form-control form-control-sm" id="tanggal_selesai" name="tanggal_selesai" value="<?= $tanggal_selesai; ?>" required>
                        </div>
                        <div class="col-md-4 d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm w-100">Filter Data</button>
                            <a href="riwayat.php" class="btn btn-secondary btn-sm w-100">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabel Data Riwayat -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Kode Transaksi</th>
                                    <th>Nama Pemesan</th>
                                    <th>Tanggal & Waktu</th>
                                    <th>Metode</th>
                                    <th>Total Harga</th>
                                    <th>Bayar</th>
                                    <th>Kembalian</th>
                                    <th width="18%" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($query_transaksi) > 0) : ?>
                                    <?php $no = 1; while ($t = mysqli_fetch_assoc($query_transaksi)) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><strong><?= $t['kode_transaksi']; ?></strong></td>
                                            <td><?= htmlspecialchars($t['nama_pemesan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></td>
                                            <td><?= $t['tanggal']; ?></td>
                                            <td><span class="badge bg-secondary"><?= $t['metode_pembayaran'] ?? 'Tunai'; ?></span></td>
                                            <td>Rp <?= number_format($t['total_harga'], 0, ',', '.'); ?></td>
                                            <td>Rp <?= number_format($t['bayar'], 0, ',', '.'); ?></td>
                                            <td>Rp <?= number_format($t['kembalian'], 0, ',', '.'); ?></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-info btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalDetail" 
                                                    data-kode="<?= $t['kode_transaksi']; ?>"
                                                    data-pemesan="<?= htmlspecialchars($t['nama_pemesan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?>"
                                                    data-tanggal="<?= $t['tanggal']; ?>"
                                                    data-total="<?= number_format($t['total_harga'], 0, ',', '.'); ?>"
                                                    data-bayar="<?= number_format($t['bayar'], 0, ',', '.'); ?>"
                                                    data-kembalian="<?= number_format($t['kembalian'], 0, ',', '.'); ?>">
                                                    Detail
                                                </button>
                                                <a href="cetak_struk.php?kode=<?= $t['kode_transaksi']; ?>" target="_blank" class="btn btn-dark btn-sm">Cetak</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-3">Tidak ada riwayat transaksi pada rentang tanggal tersebut.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL DETAIL -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header bg-dark text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="modal-title fw-bold fs-6" id="modalDetailLabel">Rincian Transaksi: <span id="det-kode" class="text-warning"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Nama Pemesan:</p>
                            <h6 id="det-pemesan" class="fw-bold text-dark"></h6>
                        </div>
                        <div class="col-md-6">
                            <p class="mb-1 text-muted small">Tanggal Transaksi:</p>
                            <h6 id="det-tanggal" class="fw-bold text-dark"></h6>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <p class="mb-1 text-muted small">Total Belanja:</p>
                            <h6 class="fw-bold text-primary">Rp <span id="det-total"></span></h6>
                        </div>
                    </div>

                    <h6 class="fw-bold mb-2 text-dark fs-6">Daftar Menu yang Dibeli:</h6>
                    <div id="det-tabel-item" class="table-responsive mb-3">
                        <p class="text-muted">Memuat rincian...</p>
                    </div>

                    <hr class="text-muted">
                    <div class="row text-end">
                        <div class="col-12">
                            <span class="me-3 text-secondary">Bayar: Rp <strong class="text-dark" id="det-bayar"></strong></span>
                            <span class="text-secondary">Kembalian: Rp <strong class="text-dark" id="det-kembalian"></strong></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                    <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalDetail = document.getElementById('modalDetail');
        modalDetail.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            
            document.getElementById('det-kode').innerText = button.getAttribute('data-kode');
            document.getElementById('det-pemesan').innerText = button.getAttribute('data-pemesan');
            document.getElementById('det-tanggal').innerText = button.getAttribute('data-tanggal');
            document.getElementById('det-total').innerText = button.getAttribute('data-total');
            document.getElementById('det-bayar').innerText = button.getAttribute('data-bayar');
            document.getElementById('det-kembalian').innerText = button.getAttribute('data-kembalian');

            const kodeTrx = button.getAttribute('data-kode');
            const containerItem = document.getElementById('det-tabel-item');
            containerItem.innerHTML = '<p class="text-muted">Memuat rincian...</p>';

            fetch('get_detail_transaksi.php?kode=' + kodeTrx)
                .then(response => response.text())
                .then(data => {
                    containerItem.innerHTML = data;
                });
        });
    </script>
</body>
</html>