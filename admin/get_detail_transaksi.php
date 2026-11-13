<?php
require_once __DIR__ . '/../koneksi.php';
require_login();

if (!isset($_GET['kode'])) {
    http_response_code(400);
    echo '<div class="alert alert-warning small mb-0">Kode transaksi tidak disertakan.</div>';
    exit;
}

$kode = trim((string) $_GET['kode']);
if ($kode === '') {
    http_response_code(400);
    echo '<div class="alert alert-warning small mb-0">Kode transaksi tidak valid.</div>';
    exit;
}

$stmt = mysqli_prepare(
    $koneksi,
    'SELECT d.*, m.nama_menu, m.harga 
     FROM detail_transaksi d 
     JOIN menu m ON d.menu_id = m.id 
     WHERE d.kode_transaksi = ?'
);

if (!$stmt) {
    http_response_code(500);
    echo '<div class="alert alert-danger small mb-0">Gagal menyiapkan data detail transaksi.</div>';
    exit;
}

mysqli_stmt_bind_param($stmt, 's', $kode);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

echo '<div class="table-responsive">';
echo '<table class="table-clean mb-0">';
echo '<thead><tr>';
echo '<th style="width: 40px;">No</th>';
echo '<th>Nama Menu</th>';
echo '<th class="text-end">Harga Satuan</th>';
echo '<th class="text-center" style="width: 60px;">Jumlah</th>';
echo '<th class="text-end">Subtotal</th>';
echo '</tr></thead>';
echo '<tbody>';

$no = 1;
$total = 0.0;
$hasRows = false;

while ($row = mysqli_fetch_assoc($result)) {
    $hasRows = true;
    $subtotal = (float) $row['subtotal'];
    $total += $subtotal;
    echo '<tr>';
    echo '<td class="text-secondary">' . $no++ . '</td>';
    echo '<td class="fw-semibold">' . e($row['nama_menu']) . '</td>';
    echo '<td class="text-end num">Rp ' . number_format((float) $row['harga'], 0, ',', '.') . '</td>';
    echo '<td class="text-center num">' . (int) $row['jumlah'] . '</td>';
    echo '<td class="text-end num fw-semibold">Rp ' . number_format($subtotal, 0, ',', '.') . '</td>';
    echo '</tr>';
}

if (!$hasRows) {
    echo '<tr><td colspan="5" class="text-center text-muted py-3">Tidak ada data item untuk transaksi ini.</td></tr>';
}

echo '</tbody>';
echo '</table>';
echo '</div>';

mysqli_stmt_close($stmt);
