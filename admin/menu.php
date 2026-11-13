<?php
require_once __DIR__ . '/../koneksi.php';
require_login();

$error = null;

// Tambah Menu Baru
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['simpan'])) {
    if (!verify_csrf()) {
        $error = "Permintaan tidak valid atau sesi telah kedaluwarsa.";
    } else {
        $nama_menu = trim((string) ($_POST['nama_menu'] ?? ''));
        $kategori  = trim((string) ($_POST['kategori'] ?? 'Makanan'));
        $harga     = filter_var($_POST['harga'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stok      = filter_var($_POST['stok'] ?? 0, FILTER_VALIDATE_INT);

        if ($nama_menu === '' || strlen($nama_menu) > 100) {
            $error = "Nama menu wajib diisi (maksimal 100 karakter).";
        } elseif (!in_array($kategori, ['Makanan', 'Minuman'], true)) {
            $error = "Kategori tidak valid.";
        } elseif ($harga === false || $harga <= 0) {
            $error = "Harga harus berupa angka lebih besar dari 0.";
        } elseif ($stok === false || $stok < 0) {
            $error = "Stok awal tidak boleh bernilai negatif.";
        } else {
            $stmt = mysqli_prepare($koneksi, "INSERT INTO menu (nama_menu, kategori, harga, stok) VALUES (?, ?, ?, ?)");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssdi", $nama_menu, $kategori, $harga, $stok);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: menu.php?pesan=sukses_tambah");
                    exit;
                }
                mysqli_stmt_close($stmt);
            }
            $error = "Gagal menyimpan menu ke database.";
        }
    }
}

// Update Menu
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update'])) {
    if (!verify_csrf()) {
        $error = "Permintaan tidak valid atau sesi telah kedaluwarsa.";
    } else {
        $id        = (int) ($_POST['id'] ?? 0);
        $nama_menu = trim((string) ($_POST['nama_menu'] ?? ''));
        $kategori  = trim((string) ($_POST['kategori'] ?? 'Makanan'));
        $harga     = filter_var($_POST['harga'] ?? 0, FILTER_VALIDATE_FLOAT);
        $stok      = filter_var($_POST['stok'] ?? 0, FILTER_VALIDATE_INT);

        if ($id <= 0) {
            $error = "ID menu tidak valid.";
        } elseif ($nama_menu === '' || strlen($nama_menu) > 100) {
            $error = "Nama menu wajib diisi (maksimal 100 karakter).";
        } elseif (!in_array($kategori, ['Makanan', 'Minuman'], true)) {
            $error = "Kategori tidak valid.";
        } elseif ($harga === false || $harga <= 0) {
            $error = "Harga harus berupa angka lebih besar dari 0.";
        } elseif ($stok === false || $stok < 0) {
            $error = "Stok tidak boleh bernilai negatif.";
        } else {
            $stmt = mysqli_prepare($koneksi, "UPDATE menu SET nama_menu = ?, kategori = ?, harga = ?, stok = ? WHERE id = ?");
            if ($stmt) {
                mysqli_stmt_bind_param($stmt, "ssdii", $nama_menu, $kategori, $harga, $stok, $id);
                if (mysqli_stmt_execute($stmt)) {
                    mysqli_stmt_close($stmt);
                    header("Location: menu.php?pesan=sukses_edit");
                    exit;
                }
                mysqli_stmt_close($stmt);
            }
            $error = "Gagal memperbarui data menu.";
        }
    }
}

// Filter data menu
$kategori_filter = trim((string) ($_GET['kategori'] ?? ''));
if ($kategori_filter === 'Makanan' || $kategori_filter === 'Minuman') {
    $stmt_menu = mysqli_prepare($koneksi, "SELECT * FROM menu WHERE kategori = ? ORDER BY nama_menu ASC");
    mysqli_stmt_bind_param($stmt_menu, "s", $kategori_filter);
    mysqli_stmt_execute($stmt_menu);
    $result_menu = mysqli_stmt_get_result($stmt_menu);
} else {
    $result_menu = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY kategori ASC, nama_menu ASC");
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu - RPL Coffee &amp; Eatery</title>
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
                <h1 class="page-title">Kelola Menu</h1>
                <p class="page-subtitle">Daftar produk makanan dan minuman yang tersedia untuk transaksi kasir.</p>
            </div>
            <button type="button" class="btn-clean btn-clean-primary" data-bs-toggle="modal" data-bs-target="#modalTambah">
                Tambah Menu
            </button>
        </header>

        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger py-2 mb-3" role="alert"><?= e($error); ?></div>
        <?php endif; ?>

        <?php if (isset($_GET['pesan'])) : ?>
            <?php if ($_GET['pesan'] === 'sukses_tambah') : ?>
                <div class="alert alert-success py-2 mb-3" role="status">Menu baru berhasil ditambahkan.</div>
            <?php elseif ($_GET['pesan'] === 'sukses_edit') : ?>
                <div class="alert alert-success py-2 mb-3" role="status">Perubahan data menu berhasil disimpan.</div>
            <?php elseif ($_GET['pesan'] === 'sukses_hapus') : ?>
                <div class="alert alert-success py-2 mb-3" role="status">Menu berhasil dihapus dari sistem.</div>
            <?php elseif ($_GET['pesan'] === 'gagal_hapus') : ?>
                <div class="alert alert-danger py-2 mb-3" role="alert">Menu gagal dihapus atau tidak ditemukan.</div>
            <?php elseif ($_GET['pesan'] === 'gagal_csrf') : ?>
                <div class="alert alert-danger py-2 mb-3" role="alert">Sesi tidak valid saat menghapus data.</div>
            <?php endif; ?>
        <?php endif; ?>

        <section class="card-clean">
            <div class="card-clean-header">
                <h2 class="card-clean-title">Daftar Menu Produk</h2>
                <div class="segmented-control">
                    <a href="menu.php" class="<?= empty($kategori_filter) ? 'active' : ''; ?>">Semua</a>
                    <a href="menu.php?kategori=Makanan" class="<?= $kategori_filter === 'Makanan' ? 'active' : ''; ?>">Makanan</a>
                    <a href="menu.php?kategori=Minuman" class="<?= $kategori_filter === 'Minuman' ? 'active' : ''; ?>">Minuman</a>
                </div>
            </div>
            <div class="table-responsive">
                <table class="table-clean">
                    <thead>
                        <tr>
                            <th style="width: 50px;">No</th>
                            <th>Nama Menu</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Stok</th>
                            <th style="width: 140px;" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result_menu && mysqli_num_rows($result_menu) > 0) : ?>
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($result_menu)) : ?>
                                <tr>
                                    <td class="text-secondary"><?= $no++; ?></td>
                                    <td class="fw-semibold"><?= e($row['nama_menu']); ?></td>
                                    <td>
                                        <span class="badge-clean <?= $row['kategori'] === 'Makanan' ? 'food' : 'drink'; ?>">
                                            <?= e($row['kategori']); ?>
                                        </span>
                                    </td>
                                    <td class="num">Rp <?= number_format((float) $row['harga'], 0, ',', '.'); ?></td>
                                    <td class="num"><?= (int) $row['stok']; ?></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-2">
                                            <button type="button" class="btn-clean btn-clean-secondary btn-clean-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalEdit"
                                                data-id="<?= (int) $row['id']; ?>"
                                                data-nama="<?= e($row['nama_menu']); ?>"
                                                data-kategori="<?= e($row['kategori']); ?>"
                                                data-harga="<?= (float) $row['harga']; ?>"
                                                data-stok="<?= (int) $row['stok']; ?>">
                                                Ubah
                                            </button>
                                            <button type="button" class="btn-clean btn-clean-danger btn-clean-sm"
                                                data-bs-toggle="modal"
                                                data-bs-target="#modalHapus"
                                                data-id="<?= (int) $row['id']; ?>"
                                                data-nama="<?= e($row['nama_menu']); ?>">
                                                Hapus
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">Tidak ada data menu pada kategori ini.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <!-- Modal Tambah -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="" method="POST">
                    <?= csrf_field(); ?>
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold" id="modalTambahLabel">Tambah Menu Baru</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="tambah_nama" class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" id="tambah_nama" name="nama_menu" maxlength="100" required placeholder="Contoh: Kopi Susu Aren">
                        </div>
                        <div class="mb-3">
                            <label for="tambah_kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="tambah_kategori" name="kategori" required>
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="tambah_harga" class="form-label">Harga Satuan (Rp)</label>
                            <input type="number" class="form-control" id="tambah_harga" name="harga" min="100" step="100" required placeholder="Contoh: 15000">
                        </div>
                        <div class="mb-2">
                            <label for="tambah_stok" class="form-label">Stok Awal</label>
                            <input type="number" class="form-control" id="tambah_stok" name="stok" min="0" step="1" required placeholder="Contoh: 20">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-clean btn-clean-secondary btn-clean-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="simpan" class="btn-clean btn-clean-primary btn-clean-sm">Simpan Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Edit -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="" method="POST">
                    <?= csrf_field(); ?>
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-header">
                        <h6 class="modal-title fw-bold" id="modalEditLabel">Ubah Data Menu</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body py-3">
                        <div class="mb-3">
                            <label for="edit_nama" class="form-label">Nama Menu</label>
                            <input type="text" class="form-control" id="edit_nama" name="nama_menu" maxlength="100" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_kategori" class="form-label">Kategori</label>
                            <select class="form-select" id="edit_kategori" name="kategori" required>
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit_harga" class="form-label">Harga Satuan (Rp)</label>
                            <input type="number" class="form-control" id="edit_harga" name="harga" min="100" step="100" required>
                        </div>
                        <div class="mb-2">
                            <label for="edit_stok" class="form-label">Stok</label>
                            <input type="number" class="form-control" id="edit_stok" name="stok" min="0" step="1" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-clean btn-clean-secondary btn-clean-sm" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update" class="btn-clean btn-clean-primary btn-clean-sm">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Konfirmasi Hapus via POST -->
    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow">
                <form action="menu_hapus.php" method="POST">
                    <?= csrf_field(); ?>
                    <input type="hidden" id="hapus_id" name="id">
                    <div class="modal-header border-0 pb-0">
                        <h6 class="modal-title fw-bold text-dark" id="modalHapusLabel">Hapus Menu</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                    </div>
                    <div class="modal-body py-3">
                        <p class="small text-secondary mb-0">Hapus menu <strong id="hapus_nama_display" class="text-dark"></strong>? Tindakan ini tidak dapat dibatalkan.</p>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEdit = document.getElementById('modalEdit');
        if (modalEdit) {
            modalEdit.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                modalEdit.querySelector('#edit_id').value = btn.getAttribute('data-id');
                modalEdit.querySelector('#edit_nama').value = btn.getAttribute('data-nama');
                modalEdit.querySelector('#edit_kategori').value = btn.getAttribute('data-kategori');
                modalEdit.querySelector('#edit_harga').value = btn.getAttribute('data-harga');
                modalEdit.querySelector('#edit_stok').value = btn.getAttribute('data-stok');
            });
        }

        const modalHapus = document.getElementById('modalHapus');
        if (modalHapus) {
            modalHapus.addEventListener('show.bs.modal', function (event) {
                const btn = event.relatedTarget;
                modalHapus.querySelector('#hapus_id').value = btn.getAttribute('data-id');
                modalHapus.querySelector('#hapus_nama_display').textContent = btn.getAttribute('data-nama');
            });
        }
    </script>
</body>
</html>
