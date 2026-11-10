<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: ../index.php?pesan=belum_login");
    exit;
}

require_once '../koneksi.php';

if (!isset($koneksi) || $koneksi === null) {
    die('Koneksi database gagal.');
}

if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

function getCartLines(mysqli $connection, array $cart): array
{
    if (empty($cart)) {
        throw new RuntimeException('Keranjang masih kosong.');
    }

    $statement = mysqli_prepare($connection, 'SELECT nama_menu, harga, stok FROM menu WHERE id = ? LIMIT 1');
    if (!$statement) {
        throw new RuntimeException('Data menu tidak dapat diperiksa.');
    }

    mysqli_stmt_bind_result($statement, $menuName, $price, $stock);
    $lines = [];
    $total = 0;

    foreach ($cart as $menuId => $quantity) {
        $menuId = (int) $menuId;
        $quantity = (int) $quantity;
        if ($menuId < 1 || $quantity < 1) {
            mysqli_stmt_close($statement);
            throw new RuntimeException('Isi keranjang tidak valid.');
        }

        mysqli_stmt_bind_param($statement, 'i', $menuId);
        mysqli_stmt_execute($statement);
        mysqli_stmt_store_result($statement);

        if (mysqli_stmt_num_rows($statement) !== 1 || !mysqli_stmt_fetch($statement)) {
            mysqli_stmt_free_result($statement);
            mysqli_stmt_close($statement);
            throw new RuntimeException('Salah satu menu di keranjang sudah tidak tersedia.');
        }

        $availableStock = (int) $stock;
        $unitPrice = (int) $price;
        mysqli_stmt_free_result($statement);

        if ($quantity > $availableStock) {
            mysqli_stmt_close($statement);
            throw new RuntimeException('Stok salah satu menu tidak mencukupi.');
        }

        $subtotal = $unitPrice * $quantity;
        $total += $subtotal;
        $lines[] = [
            'menu_id' => $menuId,
            'nama_menu' => $menuName,
            'harga' => $unitPrice,
            'jumlah' => $quantity,
            'subtotal' => $subtotal,
        ];
    }

    mysqli_stmt_close($statement);

    return ['items' => $lines, 'total' => $total];
}

function saveTransaction(mysqli $connection, array $items, int $total, int $paid, string $paymentMethod, string $orderName): string
{
    mysqli_begin_transaction($connection);

    try {
        $code = 'TRX-' . date('YmdHis') . random_int(100, 999);
        $date = date('Y-m-d H:i:s');
        $change = $paid - $total;

        $transactionStatement = mysqli_prepare(
            $connection,
            'INSERT INTO transaksi (kode_transaksi, nama_pemesan, tanggal, total_harga, bayar, kembalian, metode_pembayaran) VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $detailStatement = mysqli_prepare(
            $connection,
            'INSERT INTO detail_transaksi (kode_transaksi, menu_id, jumlah, subtotal) VALUES (?, ?, ?, ?)'
        );
        $stockStatement = mysqli_prepare(
            $connection,
            'UPDATE menu SET stok = stok - ? WHERE id = ? AND stok >= ?'
        );

        if (!$transactionStatement || !$detailStatement || !$stockStatement) {
            throw new RuntimeException('Pernyataan transaksi tidak dapat disiapkan.');
        }

        mysqli_stmt_bind_param($transactionStatement, 'sssiiis', $code, $orderName, $date, $total, $paid, $change, $paymentMethod);
        mysqli_stmt_execute($transactionStatement);

        foreach ($items as $item) {
            $menuId = (int) $item['menu_id'];
            $quantity = (int) $item['jumlah'];
            $subtotal = (int) $item['subtotal'];

            mysqli_stmt_bind_param($detailStatement, 'siii', $code, $menuId, $quantity, $subtotal);
            mysqli_stmt_execute($detailStatement);

            mysqli_stmt_bind_param($stockStatement, 'iii', $quantity, $menuId, $quantity);
            mysqli_stmt_execute($stockStatement);
            if (mysqli_stmt_affected_rows($stockStatement) !== 1) {
                throw new RuntimeException('Stok menu berubah. Periksa stok, lalu ulangi transaksi.');
            }
        }

        mysqli_stmt_close($transactionStatement);
        mysqli_stmt_close($detailStatement);
        mysqli_stmt_close($stockStatement);
        mysqli_commit($connection);

        return $code;
    } catch (Throwable $exception) {
        mysqli_rollback($connection);
        throw $exception;
    }
}

if (isset($_POST['batalkan_qris'])) {
    unset($_SESSION['pending_qris']);
    header('Location: kasir.php');
    exit;
}

if (!empty($_SESSION['pending_qris'])) {
    if (isset($_POST['konfirmasi_qris'])) {
        $pending = $_SESSION['pending_qris'];
        $token = (string) ($_POST['qris_token'] ?? '');

        if (!hash_equals($pending['token'], $token)) {
            $error = 'Sesi pembayaran tidak valid. Silakan ulangi proses pembayaran.';
        } else {
            try {
                $transactionCode = saveTransaction(
                    $koneksi,
                    $pending['items'],
                    (int) $pending['total'],
                    (int) $pending['total'],
                    'QRIS',
                    (string) ($pending['nama_pemesan'] ?? '')
                );
                unset($_SESSION['pending_qris'], $_SESSION['cart']);
                header('Location: kasir.php?pesan=sukses&trx=' . urlencode($transactionCode));
                exit;
            } catch (Throwable $exception) {
                error_log('Transaksi QRIS gagal: ' . $exception->getMessage());
                $error = $exception->getMessage() === 'Stok menu berubah. Periksa stok, lalu ulangi transaksi.'
                    ? $exception->getMessage()
                    : 'Transaksi belum berhasil disimpan. Periksa koneksi, lalu coba lagi.';
            }
        }
    } elseif (($_GET['pembayaran'] ?? '') !== 'qris') {
        header('Location: kasir.php?pembayaran=qris');
        exit;
    }
}

// 1. Tambah produk ke keranjang
if (isset($_POST['tambah_keranjang'])) {
    $id_menu = (int)($_POST['menu_id'] ?? 0);
    $jumlah_beli = (int)($_POST['jumlah'] ?? 1);

    if ($id_menu <= 0) {
        $error = "Menu tidak valid!";
    } else {
        $cek_stok = mysqli_query($koneksi, "SELECT * FROM menu WHERE id = '$id_menu' LIMIT 1");
        $data_menu = mysqli_fetch_assoc($cek_stok);

        if (!$data_menu) {
            $error = "Menu tidak ditemukan!";
        } else {
            $jumlah_di_keranjang = $_SESSION['cart'][$id_menu] ?? 0;

            if (($jumlah_di_keranjang + $jumlah_beli) > $data_menu['stok']) {
                $error = "Jumlah pembelian melebihi stok yang tersedia!";
            } else {
                $_SESSION['cart'][$id_menu] = $jumlah_di_keranjang + $jumlah_beli;
                header("Location: kasir.php?status=added");
                exit;
            }
        }
    }
}

// 2. Kosongkan keranjang
if (isset($_GET['aksi']) && $_GET['aksi'] == 'reset') {
    unset($_SESSION['cart']);
    header("Location: kasir.php");
    exit;
}

// 3. Hapus 1 item dari keranjang
if (isset($_GET['hapus'])) {
    unset($_SESSION['cart'][$_GET['hapus']]);
    header("Location: kasir.php");
    exit;
}

// 4. Proses pembayaran
if (isset($_POST['bayar_transaksi'])) {
    $metode_pembayaran = $_POST['metode_pembayaran'] ?? 'Tunai';
    $bayar = max(0, (int) ($_POST['bayar'] ?? 0));
    $nama_pemesan = trim((string) ($_POST['nama_pemesan'] ?? ''));

    if ($nama_pemesan === '' || !preg_match('/^.{1,100}$/us', $nama_pemesan)) {
        $error = 'Nama pemesan wajib diisi dan maksimal 100 karakter.';
    } elseif (!in_array($metode_pembayaran, ['Tunai', 'QRIS'], true)) {
        $error = 'Metode pembayaran tidak valid.';
    } else {
        try {
            $cartSummary = getCartLines($koneksi, $_SESSION['cart']);
            $total_harga = $cartSummary['total'];

            if ($metode_pembayaran === 'Tunai' && $bayar < $total_harga) {
                throw new RuntimeException('Uang pembayaran tunai kurang dari total belanja.');
            }

            if ($metode_pembayaran === 'QRIS') {
                $_SESSION['pending_qris'] = [
                    'items' => $cartSummary['items'],
                    'total' => $total_harga,
                    'nama_pemesan' => $nama_pemesan,
                    'token' => bin2hex(random_bytes(32)),
                ];
                header('Location: kasir.php?pembayaran=qris');
                exit;
            }

            if ($metode_pembayaran !== 'Tunai') {
                $bayar = $total_harga;
            }

            $transactionCode = saveTransaction($koneksi, $cartSummary['items'], $total_harga, $bayar, $metode_pembayaran, $nama_pemesan);
            unset($_SESSION['cart']);
            header('Location: kasir.php?pesan=sukses&trx=' . urlencode($transactionCode));
            exit;
        } catch (Throwable $exception) {
            error_log('Transaksi gagal: ' . $exception->getMessage());
            $error = $exception->getMessage() === 'Uang pembayaran tunai kurang dari total belanja.'
                || $exception->getMessage() === 'Keranjang masih kosong.'
                || $exception->getMessage() === 'Isi keranjang tidak valid.'
                || $exception->getMessage() === 'Salah satu menu di keranjang sudah tidak tersedia.'
                || $exception->getMessage() === 'Stok salah satu menu tidak mencukupi.'
                ? $exception->getMessage()
                : 'Transaksi belum berhasil disimpan. Periksa koneksi, lalu coba lagi.';
        }
    }
}

// Logika Filter Kategori
$kategori_filter = $_GET['kategori'] ?? '';
if ($kategori_filter == 'Makanan' || $kategori_filter == 'Minuman') {
    $result_menu = mysqli_query($koneksi, "SELECT * FROM menu WHERE kategori = '$kategori_filter' AND stok > 0 ORDER BY nama_menu ASC");
} else {
    $result_menu = mysqli_query($koneksi, "SELECT * FROM menu WHERE stok > 0 ORDER BY kategori ASC, nama_menu ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Kasir | RPL Coffee &amp; Eatery</title>
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
                <h4 class="fw-bold text-dark mb-1">Halaman Kasir / Transaksi</h4>
                <p class="text-muted small mb-0">Pilih menu produk dan lakukan proses transaksi penjualan.</p>
            </div>
            <span class="text-muted small fw-semibold">Panel Admin</span>
        </div>

        <div class="container-fluid p-0">
            <?php if (isset($error)) : ?>
                <div class="alert alert-danger py-2"><?= $error; ?></div>
            <?php endif; ?>

            <?php if (isset($_GET['pesan']) && $_GET['pesan'] == 'sukses') : ?>
                <div class="alert alert-success py-2 d-flex justify-content-between align-items-center">
                    <div>
                        Transaksi berhasil disimpan! Kode Transaksi: <strong><?= $_GET['trx']; ?></strong>
                    </div>
                    <a href="cetak_struk.php?kode=<?= $_GET['trx']; ?>" target="_blank" class="btn btn-dark btn-sm fw-bold">Cetak Struk</a>
                </div>
            <?php endif; ?>

            <?php if (!empty($_SESSION['pending_qris'])) : ?>
                <?php
                $pendingQris = $_SESSION['pending_qris'];
                $qrisImageExists = is_file(__DIR__ . '/../qris/QRIS BIO.jpeg');
                ?>
                <div class="row justify-content-center">
                    <div class="col-xl-8 col-lg-10">
                        <div class="card qris-payment-card">
                            <div class="card-body p-4 p-md-5">
                                <div class="row align-items-center g-4">
                                    <div class="col-md-6 text-center">
                                        <?php if ($qrisImageExists) : ?>
                                            <a href="../qris/QRIS%20BIO.jpeg" target="_blank" rel="noopener">
                                                <img class="qris-payment-image" src="../qris/QRIS%20BIO.jpeg" alt="Kode pembayaran QRIS Bazoy">
                                            </a>
                                            <a class="qris-image-link" href="../qris/QRIS%20BIO.jpeg" target="_blank" rel="noopener">Buka gambar ukuran penuh</a>
                                        <?php else : ?>
                                            <div class="alert alert-danger mb-0">Gambar QRIS tidak ditemukan. Periksa file di folder qris.</div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="qris-kicker">Pembayaran QRIS</span>
                                        <h5 class="fw-bold mt-2 mb-2">Pindai untuk membayar</h5>
                                        <p class="text-muted small mb-4">Buka aplikasi mobile banking atau e-wallet, pilih menu scan QRIS, lalu pindai kode di samping.</p>

                                        <div class="qris-total-box">
                                            <span class="small text-muted">Atas nama</span>
                                            <strong><?= htmlspecialchars($pendingQris['nama_pemesan'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></strong>
                                            <span class="small text-muted">Total yang perlu dibayar</span>
                                            <strong>Rp <?= number_format((int) $pendingQris['total'], 0, ',', '.'); ?></strong>
                                        </div>

                                        <p class="qris-reminder">Masukkan nominal sesuai total di atas. Pastikan pembayaran sudah masuk sebelum dikonfirmasi.</p>

                                        <?php if ($qrisImageExists) : ?>
                                            <form action="kasir.php" method="POST" class="d-grid gap-2 mt-4">
                                                <input type="hidden" name="qris_token" value="<?= htmlspecialchars($pendingQris['token'], ENT_QUOTES, 'UTF-8'); ?>">
                                                <button type="submit" name="konfirmasi_qris" class="btn btn-primary py-2">Pembayaran sudah diterima</button>
                                            </form>
                                        <?php endif; ?>
                                        <form action="kasir.php" method="POST" class="d-grid mt-2">
                                            <button type="submit" name="batalkan_qris" class="btn btn-light py-2">Kembali ke keranjang</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php else : ?>
            <div class="row">
                <!-- Kolom Kiri: Daftar Produk Bentuk Grid Kartu -->
                <div class="col-lg-7 mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-dark mb-0 fs-6">Daftar Menu Produk</h6>
                        <div class="btn-group btn-group-sm menu-filter">
                            <a href="kasir.php" class="btn <?= empty($kategori_filter) ? 'active' : 'btn-light'; ?>">Semua</a>
                            <a href="kasir.php?kategori=Makanan" class="btn <?= $kategori_filter == 'Makanan' ? 'active' : 'btn-light'; ?>">Makanan</a>
                            <a href="kasir.php?kategori=Minuman" class="btn <?= $kategori_filter == 'Minuman' ? 'active' : 'btn-light'; ?>">Minuman</a>
                        </div>
                    </div>

                    <div class="row row-cols-1 row-cols-md-2 g-3">
                        <?php if (mysqli_num_rows($result_menu) > 0) : ?>
                            <?php while ($m = mysqli_fetch_assoc($result_menu)) : ?>
                                <div class="col">
                                    <div class="card product-card h-100">
                                        <div class="card-body d-flex flex-column justify-content-between p-3">
                                            <div>
                                                <div class="d-flex justify-content-between align-items-start mb-2">
                                                    <h6 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($m['nama_menu']); ?></h6>
                                                    <span class="badge <?= $m['kategori'] == 'Makanan' ? 'bg-success' : 'bg-primary'; ?> small">
                                                        <?= $m['kategori']; ?>
                                                    </span>
                                                </div>
                                                <p class="text-primary fw-bold mb-1">Rp <?= number_format($m['harga'], 0, ',', '.'); ?></p>
                                                <p class="text-muted small mb-3">Stok tersedia: <?= $m['stok']; ?></p>
                                            </div>

                                            <form action="" method="POST" class="d-flex gap-2">
                                                <input type="hidden" name="menu_id" value="<?= $m['id']; ?>">
                                                <input type="number" name="jumlah" value="1" min="1" max="<?= $m['stok']; ?>" class="form-control form-control-sm text-center" style="width: 70px;" required>
                                                <button type="submit" name="tambah_keranjang" class="btn btn-primary btn-sm w-100 fw-bold">+ Keranjang</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <div class="col-12">
                                <div class="card shadow-sm text-center py-4">
                                    <p class="text-muted mb-0">Tidak ada menu tersedia dalam kategori ini.</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Kolom Kanan: Keranjang & Pembayaran (Sticky dengan Animasi Card) -->
                <div class="col-lg-5 mb-4">
                    <div class="card shadow-sm sticky-top" style="top: 20px;">
                        <div class="card-header pos-cart-header text-white fw-bold d-flex justify-content-between align-items-center py-3">
                            <span>Keranjang Belanja</span>
                            <a href="kasir.php?aksi=reset" class="btn btn-outline-light btn-sm py-0">Reset</a>
                        </div>
                        <div class="card-body p-3">
                            <div class="table-responsive mb-3" style="max-height: 220px; overflow-y: auto;">
                                <table class="table table-sm align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Menu</th>
                                            <th>Qty</th>
                                            <th>Subtotal</th>
                                            <th>#</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php 
                                        $total_belanja = 0;
                                        if (!empty($_SESSION['cart'])) :
                                            foreach ($_SESSION['cart'] as $id_m => $qty) :
                                                $q_item = mysqli_query($koneksi, "SELECT * FROM menu WHERE id = '$id_m'");
                                                $d_item = mysqli_fetch_assoc($q_item);
                                                $subtotal = $d_item['harga'] * $qty;
                                                $total_belanja += $subtotal;
                                        ?>
                                            <tr class="cart-item-row">
                                                <td><?= htmlspecialchars($d_item['nama_menu']); ?></td>
                                                <td><?= $qty; ?></td>
                                                <td>Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                                <td>
                                                    <a href="kasir.php?hapus=<?= $id_m; ?>" class="text-danger text-decoration-none fw-bold">&times;</a>
                                                </td>
                                            </tr>
                                        <?php 
                                            endforeach;
                                        else : 
                                        ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted py-3">Keranjang masih kosong</td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <form action="" method="POST">
                                <input type="hidden" name="total_harga" value="<?= $total_belanja; ?>">

                                <div class="mb-3">
                                    <label for="nama_pemesan" class="form-label small fw-bold">Nama Pemesan</label>
                                    <input type="text" class="form-control form-control-sm" id="nama_pemesan" name="nama_pemesan"
                                        maxlength="100" autocomplete="name" required>
                                </div>
                                
                                <div class="mb-2 d-flex justify-content-between">
                                    <span class="fw-bold text-dark">Total:</span>
                                    <span class="fw-bold fs-5 text-primary">Rp <?= number_format($total_belanja, 0, ',', '.'); ?></span>
                                </div>

                                <div class="mb-3">
                                    <label for="metode_pembayaran" class="form-label small fw-bold">Metode Pembayaran</label>
                                    <select class="form-select form-select-sm" id="metode_pembayaran" name="metode_pembayaran" onchange="toggleBayar()" required>
                                        <option value="Tunai">Tunai (Cash)</option>
                                        <option value="QRIS">QRIS</option>
                                    </select>
                                </div>

                                <div class="mb-3" id="input-bayar-container">
                                    <label for="bayar" class="form-label small fw-bold">Nominal Bayar Tunai (Rp)</label>
                                    <input type="number" class="form-control form-control-sm" id="bayar" name="bayar">
                                </div>

                                <div class="d-grid">
                                    <button type="submit" name="bayar_transaksi" class="btn btn-success fw-bold py-2" <?= empty($_SESSION['cart']) ? 'disabled' : ''; ?>>Proses Pembayaran</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleBayar() {
            const metode = document.getElementById('metode_pembayaran').value;
            const containerBayar = document.getElementById('input-bayar-container');
            const inputBayar = document.getElementById('bayar');

            if (metode !== 'Tunai') {
                containerBayar.style.display = 'none';
                inputBayar.removeAttribute('required');
            } else {
                containerBayar.style.display = 'block';
                inputBayar.setAttribute('required', 'true');
            }
        }
    </script>
</body>
</html>