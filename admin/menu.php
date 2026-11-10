<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: ../index.php?pesan=belum_login");
    exit;
}

include '../koneksi.php';
/** @var mysqli $koneksi Defined by koneksi.php. */

if (isset($_POST['simpan'])) {
    $nama_menu = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $kategori  = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga     = $_POST['harga'];
    $stok      = $_POST['stok'];

    $query = "INSERT INTO menu (nama_menu, kategori, harga, stok) VALUES ('$nama_menu', '$kategori', '$harga', '$stok')";
    if (mysqli_query($koneksi, $query)) {
        header("Location: menu.php?pesan=sukses_tambah");
        exit;
    } else {
        $error = "Gagal menyimpan data menu!";
    }
}

if (isset($_POST['update'])) {
    $id        = $_POST['id'];
    $nama_menu = mysqli_real_escape_string($koneksi, $_POST['nama_menu']);
    $kategori  = mysqli_real_escape_string($koneksi, $_POST['kategori']);
    $harga     = $_POST['harga'];
    $stok      = $_POST['stok'];

    $query = "UPDATE menu SET nama_menu = '$nama_menu', kategori = '$kategori', harga = '$harga', stok = '$stok' WHERE id = '$id'";
    if (mysqli_query($koneksi, $query)) {
        header("Location: menu.php?pesan=sukses_edit");
        exit;
    } else {
        $error = "Gagal memperbarui data menu!";
    }
}

$kategori_filter = $_GET['kategori'] ?? '';
if ($kategori_filter == 'Makanan' || $kategori_filter == 'Minuman') {
    $result_menu = mysqli_query($koneksi, "SELECT * FROM menu WHERE kategori = '$kategori_filter' ORDER BY nama_menu ASC");
} else {
    $result_menu = mysqli_query($koneksi, "SELECT * FROM menu ORDER BY kategori ASC, nama_menu ASC");
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Menu | RPL Coffee &amp; Eatery</title>
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
                <h4 class="fw-bold text-dark mb-1">Kelola Menu Produk</h4>
                <p class="text-muted small mb-0">Tambah, ubah, atau hapus daftar menu makanan dan minuman.</p>
            </div>
            <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#modalTambah">
                Tambah Menu Baru
            </button>
        </div>

        <div class="container-fluid p-0">
            <?php if (isset($_GET['pesan'])) : ?>
                <?php if ($_GET['pesan'] == 'sukses_tambah') : ?>
                    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                        Menu baru berhasil ditambahkan!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php elseif ($_GET['pesan'] == 'sukses_edit') : ?>
                    <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                        Data menu berhasil diperbarui!
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Tabel Data Menu dengan Filter -->
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark fs-6">Data Menu Produk</h5>
                    <div class="btn-group btn-group-sm menu-filter">
                        <a href="menu.php" class="btn <?= empty($kategori_filter) ? 'active' : 'btn-light'; ?>">Semua</a>
                        <a href="menu.php?kategori=Makanan" class="btn <?= $kategori_filter == 'Makanan' ? 'active' : 'btn-light'; ?>">Makanan</a>
                        <a href="menu.php?kategori=Minuman" class="btn <?= $kategori_filter == 'Minuman' ? 'active' : 'btn-light'; ?>">Minuman</a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th width="5%">No</th>
                                    <th>Nama Menu</th>
                                    <th>Kategori</th>
                                    <th>Harga</th>
                                    <th>Stok</th>
                                    <th width="20%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($result_menu) > 0) : ?>
                                    <?php $no = 1; while ($row = mysqli_fetch_assoc($result_menu)) : ?>
                                        <tr>
                                            <td><?= $no++; ?></td>
                                            <td><?= htmlspecialchars($row['nama_menu']); ?></td>
                                            <td>
                                                <span class="badge <?= $row['kategori'] == 'Makanan' ? 'bg-success' : 'bg-primary'; ?>">
                                                    <?= $row['kategori']; ?>
                                                </span>
                                            </td>
                                            <td>Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                            <td><?= $row['stok']; ?></td>
                                            <td>
                                                <button type="button" class="btn btn-outline-primary btn-sm" 
                                                    data-bs-toggle="modal" 
                                                    data-bs-target="#modalEdit" 
                                                    data-id="<?= $row['id']; ?>"
                                                    data-nama="<?= htmlspecialchars($row['nama_menu']); ?>"
                                                    data-kategori="<?= $row['kategori']; ?>"
                                                    data-harga="<?= $row['harga']; ?>"
                                                    data-stok="<?= $row['stok']; ?>">
                                                    Edit
                                                </button>
                                                <a href="menu_hapus.php?id=<?= $row['id']; ?>" class="btn btn-outline-danger btn-sm" onclick="return confirm('Yakin ingin menghapus menu ini?')">Hapus</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else : ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Belum ada data menu dalam kategori ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL TAMBAH -->
    <div class="modal fade" id="modalTambah" tabindex="-1" aria-labelledby="modalTambahLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <form action="" method="POST">
                    <div class="modal-header bg-dark text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title fw-bold fs-6" id="modalTambahLabel">Tambah Menu Baru</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <div class="mb-3">
                            <label for="nama_menu" class="form-label small fw-bold">Nama Menu</label>
                            <input type="text" class="form-control form-control-sm" id="nama_menu" name="nama_menu" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="kategori" class="form-label small fw-bold">Kategori</label>
                            <select class="form-select form-select-sm" id="kategori" name="kategori" required>
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="harga" class="form-label small fw-bold">Harga (Rp)</label>
                            <input type="number" class="form-control form-control-sm" id="harga" name="harga" required>
                        </div>
                        <div class="mb-3">
                            <label for="stok" class="form-label small fw-bold">Stok Awal</label>
                            <input type="number" class="form-control form-control-sm" id="stok" name="stok" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="simpan" class="btn btn-primary btn-sm px-3">Simpan Menu</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT -->
    <div class="modal fade" id="modalEdit" tabindex="-1" aria-labelledby="modalEditLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <form action="" method="POST">
                    <div class="modal-header bg-dark text-white" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                        <h5 class="modal-title fw-bold fs-6" id="modalEditLabel">Edit Menu Produk</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <input type="hidden" id="edit-id" name="id">
                        <div class="mb-3">
                            <label for="edit-nama" class="form-label small fw-bold">Nama Menu</label>
                            <input type="text" class="form-control form-control-sm" id="edit-nama" name="nama_menu" required autocomplete="off">
                        </div>
                        <div class="mb-3">
                            <label for="edit-kategori" class="form-label small fw-bold">Kategori</label>
                            <select class="form-select form-select-sm" id="edit-kategori" name="kategori" required>
                                <option value="Makanan">Makanan</option>
                                <option value="Minuman">Minuman</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="edit-harga" class="form-label small fw-bold">Harga (Rp)</label>
                            <input type="number" class="form-control form-control-sm" id="edit-harga" name="harga" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit-stok" class="form-label small fw-bold">Stok</label>
                            <input type="number" class="form-control form-control-sm" id="edit-stok" name="stok" required>
                        </div>
                    </div>
                    <div class="modal-footer bg-light" style="border-bottom-left-radius: 12px; border-bottom-right-radius: 12px;">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" name="update" class="btn btn-primary btn-sm px-3">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modalEdit = document.getElementById('modalEdit');
        modalEdit.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            modalEdit.querySelector('#edit-id').value = button.getAttribute('data-id');
            modalEdit.querySelector('#edit-nama').value = button.getAttribute('data-nama');
            modalEdit.querySelector('#edit-kategori').value = button.getAttribute('data-kategori');
            modalEdit.querySelector('#edit-harga').value = button.getAttribute('data-harga');
            modalEdit.querySelector('#edit-stok').value = button.getAttribute('data-stok');
        });
    </script>
</body>
</html>