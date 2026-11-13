<?php
require_once __DIR__ . '/../koneksi.php';
require_login();

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

$error = null;

// Mengambil baris item di keranjang dengan query aman
function getCartLines(mysqli $connection, array $cart): array
{
    if (empty($cart)) {
        throw new RuntimeException('Keranjang belanja masih kosong.');
    }

    $statement = mysqli_prepare($connection, 'SELECT nama_menu, harga, stok FROM menu WHERE id = ? LIMIT 1');
    if (!$statement) {
        throw new RuntimeException('Gagal menyiapkan verifikasi menu.');
    }

    $lines = [];
    $total = 0;

    foreach ($cart as $menuId => $quantity) {
        $menuId = (int) $menuId;
        $quantity = (int) $quantity;
        if ($menuId < 1 || $quantity < 1) {
            mysqli_stmt_close($statement);
            throw new RuntimeException('Data keranjang tidak valid.');
        }

        mysqli_stmt_bind_param($statement, 'i', $menuId);
        mysqli_stmt_execute($statement);
        $result = mysqli_stmt_get_result($statement);
        $menu = mysqli_fetch_assoc($result);

        if (!$menu) {
            mysqli_stmt_close($statement);
            throw new RuntimeException('Salah satu menu di keranjang sudah tidak tersedia.');
        }

        $availableStock = (int) $menu['stok'];
        $unitPrice = (float) $menu['harga'];

        if ($quantity > $availableStock) {
            mysqli_stmt_close($statement);
            throw new RuntimeException("Stok untuk menu '{$menu['nama_menu']}' tidak mencukupi (sisa: {$availableStock}).");
        }

        $subtotal = $unitPrice * $quantity;
        $total += $subtotal;
        $lines[] = [
            'menu_id' => $menuId,
            'nama_menu' => $menu['nama_menu'],
            'harga' => $unitPrice,
            'jumlah' => $quantity,
            'subtotal' => $subtotal,
        ];
    }

    mysqli_stmt_close($statement);
    return ['items' => $lines, 'total' => $total];
}

// Simpan transaksi dengan transaksi database aman
function saveTransaction(mysqli $connection, array $items, float $total, float $paid, string $paymentMethod, string $orderName): string
{
    mysqli_begin_transaction($connection);

    try {
        $code = 'TRX-' . date('YmdHis') . random_int(100, 999);
        $date = date('Y-m-d H:i:s');
        $change = max(0.0, $paid - $total);

        $trxStmt = mysqli_prepare(
            $connection,
            'INSERT INTO transaksi (kode_transaksi, nama_pemesan, tanggal, total_harga, bayar, kembalian, metode_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $detailStmt = mysqli_prepare(
            $connection,
            'INSERT INTO detail_transaksi (kode_transaksi, menu_id, jumlah, subtotal) VALUES (?, ?, ?, ?)'
        );
        $stockStmt = mysqli_prepare(
            $connection,
            'UPDATE menu SET stok = stok - ? WHERE id = ? AND stok >= ?'
        );

        if (!$trxStmt || !$detailStmt || !$stockStmt) {
            throw new RuntimeException('Gagal menyiapkan query transaksi.');
        }

        mysqli_stmt_bind_param($trxStmt, 'sssddds', $code, $orderName, $date, $total, $paid, $change, $paymentMethod);
        mysqli_stmt_execute($trxStmt);

        foreach ($items as $item) {
            $menuId = (int) $item['menu_id'];
            $quantity = (int) $item['jumlah'];
            $subtotal = (float) $item['subtotal'];

            mysqli_stmt_bind_param($detailStmt, 'siid', $code, $menuId, $quantity, $subtotal);
            mysqli_stmt_execute($detailStmt);

            mysqli_stmt_bind_param($stockStmt, 'iii', $quantity, $menuId, $quantity);
            mysqli_stmt_execute($stockStmt);
            if (mysqli_stmt_affected_rows($stockStmt) !== 1) {
                throw new RuntimeException('Stok produk telah berubah oleh transaksi lain. Silakan periksa kembali.');
            }
        }

        mysqli_stmt_close($trxStmt);
        mysqli_stmt_close($detailStmt);
        mysqli_stmt_close($stockStmt);
        mysqli_commit($connection);

        return $code;
    } catch (Throwable $e) {
        mysqli_rollback($connection);
        throw $e;
    }
}

// Batalkan QRIS
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['batalkan_qris'])) {
    if (verify_csrf()) {
        unset($_SESSION['pending_qris']);
        header('Location: kasir.php');
        exit;
    }
}

// Konfirmasi QRIS
if (!empty($_SESSION['pending_qris'])) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi_qris'])) {
        if (!verify_csrf()) {
            $error = 'Sesi tidak valid. Silakan ulangi proses.';
        } else {
            $pending = $_SESSION['pending_qris'];
            $token = (string) ($_POST['qris_token'] ?? '');

            if (!hash_equals($pending['token'], $token)) {
                $error = 'Token sesi QRIS tidak cocok. Silakan ulangi proses.';
            } else {
                try {
                    $trxCode = saveTransaction(
                        $koneksi,
                        $pending['items'],
                        (float) $pending['total'],
                        (float) $pending['total'],
                        'QRIS',
                        (string) ($pending['nama_pemesan'] ?? '')
                    );
                    unset($_SESSION['pending_qris'], $_SESSION['cart']);
                    header('Location: kasir.php?pesan=sukses&trx=' . urlencode($trxCode));
                    exit;
                } catch (Throwable $e) {
                    $error = $e->getMessage();
                }
            }
        }
    } elseif (($_GET['pembayaran'] ?? '') !== 'qris') {
        header('Location: kasir.php?pembayaran=qris');
        exit;
    }
}

// 1. Tambah produk ke keranjang
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tambah_keranjang'])) {
    if (!verify_csrf()) {
        $error = 'Sesi telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $menuId = (int) ($_POST['menu_id'] ?? 0);
        $qty = max(1, (int) ($_POST['jumlah'] ?? 1));

        if ($menuId <= 0) {
            $error = 'Menu tidak valid.';
        } else {
            $stmtCek = mysqli_prepare($koneksi, 'SELECT id, nama_menu, stok FROM menu WHERE id = ? LIMIT 1');
            if ($stmtCek) {
                mysqli_stmt_bind_param($stmtCek, 'i', $menuId);
                mysqli_stmt_execute($stmtCek);
                $resCek = mysqli_stmt_get_result($stmtCek);
                $menu = mysqli_fetch_assoc($resCek);
                mysqli_stmt_close($stmtCek);

                if (!$menu) {
                    $error = 'Menu tidak ditemukan di database.';
                } else {
                    $currentQty = $_SESSION['cart'][$menuId] ?? 0;
                    if (($currentQty + $qty) > (int) $menu['stok']) {
                        $error = "Jumlah pembelian melebihi stok tersedia (sisa stok: {$menu['stok']}).";
                    } else {
                        $_SESSION['cart'][$menuId] = $currentQty + $qty;
                        header('Location: kasir.php');
                        exit;
                    }
                }
            }
        }
    }
}

// 2. Kosongkan keranjang (via POST untuk keamanan)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reset_keranjang'])) {
    if (verify_csrf()) {
        unset($_SESSION['cart']);
        header('Location: kasir.php');
        exit;
    }
}

// 3. Hapus 1 item dari keranjang (via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['hapus_item'])) {
    if (verify_csrf()) {
        $idHapus = (int) ($_POST['item_id'] ?? 0);
        unset($_SESSION['cart'][$idHapus]);
        header('Location: kasir.php');
        exit;
    }
}

// 4. Proses Pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bayar_transaksi'])) {
    if (!verify_csrf()) {
        $error = 'Sesi pembayaran telah kedaluwarsa. Silakan muat ulang halaman.';
    } else {
        $metode = trim((string) ($_POST['metode_pembayaran'] ?? 'Tunai'));
        $bayar = max(0.0, (float) ($_POST['bayar'] ?? 0));
        $namaPemesan = trim((string) ($_POST['nama_pemesan'] ?? ''));

        if ($namaPemesan === '' || strlen($namaPemesan) > 100) {
            $error = 'Nama pemesan wajib diisi (maksimal 100 karakter).';
        } elseif (!in_array($metode, ['Tunai', 'QRIS'], true)) {
            $error = 'Metode pembayaran tidak valid.';
        } else {
            try {
                $cartSummary = getCartLines($koneksi, $_SESSION['cart']);
                $totalHarga = (float) $cartSummary['total'];

                if ($metode === 'Tunai' && $bayar < $totalHarga) {
                    throw new RuntimeException('Nominal pembayaran tunai kurang dari total belanja.');
                }

                if ($metode === 'QRIS') {
                    $_SESSION['pending_qris'] = [
                        'items' => $cartSummary['items'],
                        'total' => $totalHarga,
                        'nama_pemesan' => $namaPemesan,
                        'token' => bin2hex(random_bytes(32)),
                    ];
                    header('Location: kasir.php?pembayaran=qris');
                    exit;
                }

                $trxCode = saveTransaction($koneksi, $cartSummary['items'], $totalHarga, $bayar, $metode, $namaPemesan);
                unset($_SESSION['cart']);
                header('Location: kasir.php?pesan=sukses&trx=' . urlencode($trxCode));
                exit;
            } catch (Throwable $e) {
                $error = $e->getMessage();
            }
        }
    }
}

// Ambil daftar menu sesuai filter kategori
$kategoriFilter = trim((string) ($_GET['kategori'] ?? ''));
if ($kategoriFilter === 'Makanan' || $kategoriFilter === 'Minuman') {
    $stmtMenu = mysqli_prepare($koneksi, 'SELECT * FROM menu WHERE kategori = ? AND stok > 0 ORDER BY nama_menu ASC');
    mysqli_stmt_bind_param($stmtMenu, 's', $kategoriFilter);
    mysqli_stmt_execute($stmtMenu);
    $resultMenu = mysqli_stmt_get_result($stmtMenu);
} else {
    $resultMenu = mysqli_query($koneksi, 'SELECT * FROM menu WHERE stok > 0 ORDER BY kategori ASC, nama_menu ASC');
}

// Ambil data detail item di keranjang saat ini
$cartItems = [];
$totalBelanja = 0.0;
if (!empty($_SESSION['cart'])) {
    $stmtItem = mysqli_prepare($koneksi, 'SELECT id, nama_menu, harga, stok FROM menu WHERE id = ? LIMIT 1');
    if ($stmtItem) {
        foreach ($_SESSION['cart'] as $mId => $qty) {
            $mId = (int) $mId;
            $qty = (int) $qty;
            mysqli_stmt_bind_param($stmtItem, 'i', $mId);
            mysqli_stmt_execute($stmtItem);
            $res = mysqli_stmt_get_result($stmtItem);
            if ($row = mysqli_fetch_assoc($res)) {
                $sub = (float) $row['harga'] * $qty;
                $totalBelanja += $sub;
                $cartItems[] = [
                    'id' => $mId,
                    'nama_menu' => $row['nama_menu'],
                    'harga' => (float) $row['harga'],
                    'qty' => $qty,
                    'subtotal' => $sub,
                ];
            }
        }
        mysqli_stmt_close($stmtItem);
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir - RPL Coffee &amp; Eatery</title>
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
                <h1 class="page-title">Halaman Kasir</h1>
                <p class="page-subtitle">Pilih menu pesanan dan proses transaksi penjualan pelanggan.</p>
            </div>
        </header>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger py-2 mb-3" role="alert"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['pesan']) && $_GET['pesan'] === 'sukses' && !empty($_GET['trx'])) : ?>
            <div class="alert alert-success py-2 mb-3 d-flex justify-content-between align-items-center" role="status">
                <div>
                    Transaksi berhasil disimpan. Kode: <strong><?= e($_GET['trx']); ?></strong>
                </div>
                <a href="cetak_struk.php?kode=<?= urlencode($_GET['trx']); ?>" target="_blank" class="btn-clean btn-clean-primary btn-clean-sm">
                    Cetak Struk
                </a>
            </div>
        <?php endif; ?>

        <?php if (!empty($_SESSION['pending_qris'])) : ?>
            <?php
            $pendingQris = $_SESSION['pending_qris'];
            $qrisFile = __DIR__ . '/../qris/QRIS BIO.jpeg';
            $qrisExists = is_file($qrisFile);
            ?>
            <section class="qris-card">
                <div class="row align-items-center g-4">
                    <div class="col-md-5 text-center">
                        <?php if ($qrisExists) : ?>
                            <img src="../qris/QRIS%20BIO.jpeg" alt="QRIS Pembayaran" class="qris-image mb-2">
                            <div>
                                <a href="../qris/QRIS%20BIO.jpeg" target="_blank" class="small text-muted text-decoration-none">Perbesar Gambar</a>
                            </div>
                        <?php else : ?>
                            <div class="alert alert-warning small mb-0">File gambar QRIS belum tersedia di folder qris.</div>
                        <?php endif; ?>
                    </div>
                    <div class="col-md-7">
                        <span class="badge-clean drink mb-2">Metode QRIS</span>
                        <h2 class="h5 fw-bold mb-2">Pindai Kode QRIS</h2>
                        <p class="text-secondary small mb-3">Minta pelanggan memindai kode di samping melalui aplikasi perbankan atau e-wallet.</p>

                        <div class="p-3 bg-light rounded border mb-3">
                            <div class="small text-muted mb-1">Nama Pemesan</div>
                            <div class="fw-semibold text-dark mb-2"><?= e($pendingQris['nama_pemesan'] ?? '-'); ?></div>
                            <div class="small text-muted mb-1">Total Tagihan</div>
                            <div class="h5 fw-bold text-dark mb-0 num">Rp <?= number_format((float) $pendingQris['total'], 0, ',', '.'); ?></div>
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <form action="kasir.php" method="POST">
                                <?= csrf_field(); ?>
                                <input type="hidden" name="qris_token" value="<?= e($pendingQris['token']); ?>">
                                <button type="submit" name="konfirmasi_qris" class="btn-clean btn-clean-primary w-100">
                                    Konfirmasi Pembayaran Diterima
                                </button>
                            </form>
                            <form action="kasir.php" method="POST">
                                <?= csrf_field(); ?>
                                <button type="submit" name="batalkan_qris" class="btn-clean btn-clean-secondary w-100">
                                    Kembali ke Keranjang
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </section>
        <?php else : ?>
            <div class="row g-4">
                <!-- Kolom Kiri: Katalog Produk -->
                <div class="col-lg-7">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="segmented-control">
                            <a href="kasir.php" class="<?= empty($kategoriFilter) ? 'active' : ''; ?>">Semua</a>
                            <a href="kasir.php?kategori=Makanan" class="<?= $kategoriFilter === 'Makanan' ? 'active' : ''; ?>">Makanan</a>
                            <a href="kasir.php?kategori=Minuman" class="<?= $kategoriFilter === 'Minuman' ? 'active' : ''; ?>">Minuman</a>
                        </div>
                        <input type="text" id="quick-filter" class="form-control form-control-sm" style="max-width: 220px;" placeholder="Cari nama menu...">
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 g-3" id="product-grid">
                        <?php if ($resultMenu && mysqli_num_rows($resultMenu) > 0) : ?>
                            <?php while ($m = mysqli_fetch_assoc($resultMenu)) : ?>
                                <div class="col product-item" data-nama="<?= strtolower(e($m['nama_menu'])); ?>">
                                    <div class="pos-product-card">
                                        <div>
                                            <div class="d-flex justify-content-between align-items-start mb-1">
                                                <h3 class="pos-product-title"><?= e($m['nama_menu']); ?></h3>
                                                <span class="badge-clean <?= $m['kategori'] === 'Makanan' ? 'food' : 'drink'; ?>">
                                                    <?= e($m['kategori']); ?>
                                                </span>
                                            </div>
                                            <div class="pos-product-price num">Rp <?= number_format((float) $m['harga'], 0, ',', '.'); ?></div>
                                            <div class="pos-product-stock">Sisa stok: <?= (int) $m['stok']; ?></div>
                                        </div>

                                        <form action="" method="POST" class="d-flex gap-2 mt-3">
                                            <?= csrf_field(); ?>
                                            <input type="hidden" name="menu_id" value="<?= (int) $m['id']; ?>">
                                            <input type="number" name="jumlah" value="1" min="1" max="<?= (int) $m['stok']; ?>" class="form-control form-control-sm text-center" style="width: 64px;" required>
                                            <button type="submit" name="tambah_keranjang" class="btn-clean btn-clean-secondary btn-clean-sm w-100">
                                                + Tambah
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <div class="col-12">
                                <div class="card-clean p-4 text-center text-muted">
                                    Tidak ada produk yang tersedia saat ini.
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Kolom Kanan: Keranjang Pesanan -->
                <div class="col-lg-5">
                    <div class="card-clean sticky-top" style="top: 24px;">
                        <div class="card-clean-header">
                            <h2 class="card-clean-title">Pesanan Saat Ini</h2>
                            <?php if (!empty($cartItems)) : ?>
                                <form action="kasir.php" method="POST" onsubmit="return confirm('Kosongkan keranjang pesanan?');" style="margin: 0;">
                                    <?= csrf_field(); ?>
                                    <button type="submit" name="reset_keranjang" class="btn-clean btn-clean-secondary btn-clean-sm">
                                        Kosongkan
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>

                        <div class="p-3">
                            <div class="table-responsive mb-3" style="max-height: 240px; overflow-y: auto;">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr class="text-secondary small">
                                            <th>Item</th>
                                            <th class="text-center" style="width: 50px;">Qty</th>
                                            <th class="text-end">Subtotal</th>
                                            <th style="width: 30px;"></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (!empty($cartItems)) : ?>
                                            <?php foreach ($cartItems as $c) : ?>
                                                <tr>
                                                    <td>
                                                        <div class="fw-semibold text-dark small"><?= e($c['nama_menu']); ?></div>
                                                        <div class="text-muted" style="font-size: 0.74rem;">@ Rp <?= number_format($c['harga'], 0, ',', '.'); ?></div>
                                                    </td>
                                                    <td class="text-center num small"><?= $c['qty']; ?></td>
                                                    <td class="text-end num small fw-semibold">Rp <?= number_format($c['subtotal'], 0, ',', '.'); ?></td>
                                                    <td class="text-end">
                                                        <form action="kasir.php" method="POST" style="display: inline;">
                                                            <?= csrf_field(); ?>
                                                            <input type="hidden" name="item_id" value="<?= $c['id']; ?>">
                                                            <button type="submit" name="hapus_item" class="border-0 bg-transparent text-danger p-0 fw-bold" style="cursor: pointer;" title="Hapus item">&times;</button>
                                                        </form>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        <?php else : ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-4 small">
                                                    Belum ada menu yang dipilih.
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <form action="" method="POST" id="form-pembayaran">
                                <?= csrf_field(); ?>

                                <div class="mb-3">
                                    <label for="nama_pemesan" class="form-label">Nama Pemesan</label>
                                    <input type="text" class="form-control" id="nama_pemesan" name="nama_pemesan"
                                        maxlength="100" required placeholder="Contoh: Raditya">
                                </div>

                                <div class="d-flex justify-content-between align-items-center py-2 border-top border-bottom mb-3">
                                    <span class="fw-semibold text-secondary">Total Tagihan</span>
                                    <span class="h5 fw-bold text-dark mb-0 num">Rp <?= number_format($totalBelanja, 0, ',', '.'); ?></span>
                                </div>

                                <div class="mb-3">
                                    <label for="metode_pembayaran" class="form-label">Metode Pembayaran</label>
                                    <select class="form-select" id="metode_pembayaran" name="metode_pembayaran" onchange="handlePaymentMethodChange()">
                                        <option value="Tunai">Tunai (Cash)</option>
                                        <option value="QRIS">QRIS</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="field-tunai">
                                    <label for="bayar" class="form-label">Uang Diterima (Rp)</label>
                                    <input type="number" class="form-control" id="bayar" name="bayar" min="<?= (int) $totalBelanja; ?>" step="100" placeholder="Masukkan jumlah uang tunai">
                                    <div class="d-flex justify-content-between small text-secondary mt-1">
                                        <span>Kembalian:</span>
                                        <span id="display-kembalian" class="fw-semibold num">Rp 0</span>
                                    </div>
                                </div>

                                <button type="submit" name="bayar_transaksi" id="btn-submit-bayar" class="btn-clean btn-clean-primary w-100 py-2" <?= empty($cartItems) ? 'disabled' : ''; ?>>
                                    Proses Pembayaran
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const totalBelanja = <?= (float) $totalBelanja; ?>;
        const inputBayar = document.getElementById('bayar');
        const displayKembalian = document.getElementById('display-kembalian');
        const selectMetode = document.getElementById('metode_pembayaran');
        const fieldTunai = document.getElementById('field-tunai');
        const formPembayaran = document.getElementById('form-pembayaran');
        const btnSubmitBayar = document.getElementById('btn-submit-bayar');

        function formatRupiah(amount) {
            return 'Rp ' + Math.max(0, Math.round(amount)).toLocaleString('id-ID');
        }

        function handlePaymentMethodChange() {
            if (!selectMetode || !fieldTunai) return;
            const isCash = selectMetode.value === 'Tunai';
            fieldTunai.style.display = isCash ? 'block' : 'none';
            if (inputBayar) {
                if (isCash) {
                    inputBayar.setAttribute('required', 'required');
                } else {
                    inputBayar.removeAttribute('required');
                }
            }
        }

        if (inputBayar && displayKembalian) {
            inputBayar.addEventListener('input', function () {
                const bayar = parseFloat(inputBayar.value) || 0;
                const kembalian = bayar - totalBelanja;
                displayKembalian.textContent = formatRupiah(kembalian > 0 ? kembalian : 0);
            });
        }

        if (formPembayaran && btnSubmitBayar) {
            formPembayaran.addEventListener('submit', function () {
                btnSubmitBayar.disabled = true;
                btnSubmitBayar.textContent = 'Memproses...';
            });
        }

        // Quick search filter pada daftar menu
        const searchInput = document.getElementById('quick-filter');
        if (searchInput) {
            searchInput.addEventListener('input', function () {
                const query = searchInput.value.toLowerCase().trim();
                const items = document.querySelectorAll('.product-item');
                items.forEach(item => {
                    const nama = item.getAttribute('data-nama') || '';
                    item.style.display = nama.includes(query) ? '' : 'none';
                });
            });
        }
    </script>
</body>
</html>
