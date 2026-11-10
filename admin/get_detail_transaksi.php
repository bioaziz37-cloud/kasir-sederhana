<?php
include '../koneksi.php';

if (isset($_GET['kode'])) {
    if (!isset($koneksi) || !($koneksi instanceof mysqli)) {
        http_response_code(500);
        echo 'Database connection not available.';
        exit;
    }

    $kode = $_GET['kode'];
    $query = mysqli_query($koneksi, "SELECT detail_transaksi.*, menu.nama_menu, menu.harga FROM detail_transaksi JOIN menu ON detail_transaksi.menu_id = menu.id WHERE detail_transaksi.kode_transaksi = '" . mysqli_real_escape_string($koneksi, $kode) . "'");

    echo '<table class="table table-sm table-bordered align-middle mb-0">';
    echo '<thead class="table-light"><tr><th>No</th><th>Nama Menu</th><th>Harga Satuan</th><th>Jumlah</th><th>Subtotal</th></tr></thead>';
    echo '<tbody>';

    $no = 1;
    while ($row = mysqli_fetch_assoc($query)) {
        echo '<tr>';
        echo '<td>' . $no++ . '</td>';
        echo '<td>' . htmlspecialchars($row['nama_menu']) . '</td>';
        echo '<td>Rp ' . number_format($row['harga'], 0, ',', '.') . '</td>';
        echo '<td>' . $row['jumlah'] . '</td>';
        echo '<td>Rp ' . number_format($row['subtotal'], 0, ',', '.') . '</td>';
        echo '</tr>';
    }

    echo '</tbody>';
    echo '</table>';
}
