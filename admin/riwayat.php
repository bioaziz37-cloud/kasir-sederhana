<?php
require_once __DIR__ . '/../koneksi.php';
require_login();

$tanggal_mulai = trim((string) ($_GET['tanggal_mulai'] ?? ''));
$tanggal_selesai = trim((string) ($_GET['tanggal_selesai'] ?? ''));

if (!empty($tanggal_mulai) && !empty($tanggal_selesai)) {
    $stmt = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE DATE(tanggal) BETWEEN ? AND ? ORDER BY id DESC");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ss", $tanggal_mulai, $tanggal_selesai);
        mysqli_stmt_execute($stmt);
        $query_transaksi = mysqli_stmt_get_result($stmt);
    } else {
        $query_transaksi = false;
    }
} else {
    $query_transaksi = mysqli_query($koneksi, "SELECT * FROM transaksi ORDER BY id DESC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Transaksi - RPL Coffee &amp; Eatery</title>
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
                <h1 class="page-title">Riwayat Transaksi</h1>
                <p class="page-subtitle">Pantau seluruh catatan transaksi penjualan dan detail pesanan.</p>
            </div>
        </header>

        <!-- Filter Tanggal -->
        <section class="card-clean mb-4">
            <div class="card-clean-body py-3">
                <form method="GET" action="" class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label for="tanggal_mulai" class="form-label">Mulai Tanggal</label>
                        <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?= e($tanggal_mulai); ?>" required>
                    </div>
                    <div class="col-md-4">
                        <label for="tanggal_selesai" class="form-label">Sampai Tanggal</label>
                        <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?= e($tanggal_selesai); ?>" required>
                    </div>
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" class="btn-clean btn-clean-primary w-100">Filter Data</button>
                        <a href="riwayat.php" class="btn-clean btn-clean-secondary w-100">Reset</a>
                    </div>
                </form>
            </div>
        </section>

        <!-- Tabel Transaksi -->
        <section class="card-clean">
            <div class="card-clean-header">
                <h2 class="card-clean-title">Daftar Penjualan</h2>
            </div>
            <div class="table-responsive">
                <table class="table-clean">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Kode Transaksi</th>
                            <th>Pemesan</th>
                            <th>Tanggal &amp; Waktu</th>
                            <th>Metode</th>
                            <th class="text-end">Total</th>
                            <th class="text-end">Bayar</th>
                            <th class="text-end">Kembalian</th>
                            <th style="width: 150px;" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($query_transaksi && mysqli_num_rows($query_transaksi) > 0) : ?>
                            <?php $no = 1; while ($t = mysqli_fetch_assoc($query_transaksi)) : ?>
                                <tr>
                                    <td class="text-secondary"><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= e($t['kode_transaksi']); ?></td>
                                    <td><?= e($t['nama_pemesan'] ?: '-'); ?></td>
                                    <td class="text-secondary"><?= e($t['tanggal']); ?></td>
                                    <td>
                                        <span class="badge-clean <?= ($t['metode_pembayaran'] === 'QRIS') ? 'drink' : 'neutral'; ?>">
                                            <?= e($t['metode_pembayaran'] ?? 'Tunai'); ?>
                                        </span>
                                    </td>
                                    <td class="text-end num fw-semibold">Rp <?= number_format((float) $t['total_harga'], 0, ',', '.'); ?></td>
                                    <td class="text-end num text-secondary">Rp <?= number_format((float) $t['bayar'], 0, ',', '.'); ?></td>
                                    <td class="text-end num text-secondary">Rp <?= number_format((float) $t['kembalian'], 0, ',', '.'); ?></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn-clean btn-clean-secondary btn-clean-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalDetail"
                                                data-kode="<?= e($t['kode_transaksi']); ?>"
                                                data-pemesan="<?= e($t['nama_pemesan'] ?: '-'); ?>"
                                                data-tanggal="<?= e($t['tanggal']); ?>"
                                                data-total="<?= number_format((float) $t['total_harga'], 0, ',', '.'); ?>"
                                                data-bayar="<?= number_format((float) $t['bayar'], 0, ',', '.'); ?>"
                                                data-kembalian="<?= number_format((float) $t['kembalian'], 0, ',', '.'); ?>">
                                                Rincian
                                            </button>
                                            <a href="cetak_struk.php?kode=<?= urlencode($t['kode_transaksi']); ?>" target="_blank" class="btn-clean btn-clean-primary btn-clean-sm">
                                                Cetak
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="9" class="text-center text-muted py-4">
                                    Tidak ada catatan transaksi pada filter yang dipilih.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal Rincian Transaksi -->
    <div class="modal fade" id="modalDetail" tabindex="-1" aria-labelledby="modalDetailLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header">
                    <h6 class="modal-title fw-bold" id="modalDetailLabel">Rincian Transaksi: <span id="det-kode" class="text-primary"></span></h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body py-3">
                    <div class="row g-3 mb-3 p-3 bg-light rounded border">
                        <div class="col-sm-4">
                            <span class="small text-muted d-block">Nama Pemesan</span>
                            <span class="fw-semibold text-dark" id="det-pemesan">-</span>
                        </div>
                        <div class="col-sm-4">
                            <span class="small text-muted d-block">Waktu Transaksi</span>
                            <span class="fw-semibold text-dark" id="det-tanggal">-</span>
                        </div>
                        <div class="col-sm-4 text-sm-end">
                            <span class="small text-muted d-block">Total Pembayaran</span>
                            <span class="fw-bold text-dark h6 mb-0 num">Rp <span id="det-total">0</span></span>
                        </div>
                    </div>

                    <h6 class="fw-bold text-dark small mb-2">Daftar Item Menu:</h6>
                    <div id="det-tabel-item" class="mb-3">
                        <p class="text-muted small">Memuat rincian item...</p>
                    </div>

                    <div class="d-flex justify-content-end gap-4 pt-2 border-top small">
                        <div>Uang Diterima: <strong>Rp <span id="det-bayar" class="num">0</span></strong></div>
                        <div>Kembalian: <strong>Rp <span id="det-kembalian" class="num">0</span></strong></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-clean btn-clean-secondary btn-clean-sm" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalDetail = document.getElementById('modalDetail');
        if (modalDetail) {
            modalDetail.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                const kode = btn.getAttribute('data-kode');

                document.getElementById('det-kode').textContent = kode;
                document.getElementById('det-pemesan').textContent = btn.getAttribute('data-pemesan');
                document.getElementById('det-tanggal').textContent = btn.getAttribute('data-tanggal');
                document.getElementById('det-total').textContent = btn.getAttribute('data-total');
                document.getElementById('det-bayar').textContent = btn.getAttribute('data-bayar');
                document.getElementById('det-kembalian').textContent = btn.getAttribute('data-kembalian');

                const container = document.getElementById('det-tabel-item');
                container.innerHTML = '<p class="text-muted small py-2">Memuat rincian item...</p>';

                fetch('get_detail_transaksi.php?kode=' + encodeURIComponent(kode))
                    .then(res => {
                        if (!res.ok) throw new Error('Gagal memuat rincian');
                        return res.text();
                    })
                    .then(html => {
                        container.innerHTML = html;
                    })
                    .catch(() => {
                        container.innerHTML = '<div class="alert alert-danger small mb-0">Gagal mengambil data rincian dari server.</div>';
                    });
            });
        }
    </script>
</body>
</html>
