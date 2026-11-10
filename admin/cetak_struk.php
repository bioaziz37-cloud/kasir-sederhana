<?php
session_start();
if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: ../index.php");
    exit;
}

$koneksi = null;
include '../koneksi.php';

if (!$koneksi instanceof mysqli) {
    echo "Koneksi database tidak tersedia!";
    exit;
}

if (!isset($_GET['kode'])) {
    echo "Kode transaksi tidak ditemukan!";
    exit;
}

$kode = mysqli_real_escape_string($koneksi, $_GET['kode']);
$query_trx = mysqli_query($koneksi, "SELECT * FROM transaksi WHERE kode_transaksi = '$kode'");
$trx = mysqli_fetch_assoc($query_trx);

if (!$trx) {
    echo "Data transaksi tidak ditemukan!";
    exit;
}

$query_detail = mysqli_query($koneksi, "SELECT detail_transaksi.*, menu.nama_menu, menu.harga FROM detail_transaksi JOIN menu ON detail_transaksi.menu_id = menu.id WHERE detail_transaksi.kode_transaksi = '$kode'");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Struk <?= htmlspecialchars($trx['kode_transaksi']); ?> | RPL Coffee &amp; Eatery</title>
    <link rel="icon" type="image/png" href="../logo/LOGO.png">
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        body {
            box-sizing: border-box;
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 58mm;
            margin: 0 auto;
            padding: 3mm;
            color: #211b17;
            background: #fff;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }
        .receipt-logo { width: 18mm; height: 18mm; object-fit: contain; margin-bottom: 5px; }
        .text-center > h4 { font-size: 14px; letter-spacing: 0.04em; }
        .text-center > p { line-height: 1.45; }
        .line { border-bottom: 1px dashed #8d8175; margin: 8px 0; }
        table { width: 100%; font-size: 11px; border-collapse: collapse; }
        th, td { padding: 3px 0; vertical-align: top; }
        table + .line { margin-top: 9px; }
        .no-print button {
            border: 1px solid #8a4f31;
            border-radius: 4px;
            background: #8a4f31;
            color: #fff;
        }
        .no-print a { color: #754024 !important; }
        @media print {
            body { width: 58mm; margin: 0; padding: 2mm; }
            .no-print { display: none; }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="text-center">
        <img class="receipt-logo" src="../logo/LOGO.png" alt="Logo RPL Coffee &amp; Eatery">
        <h4 class="fw-bold" style="margin: 0;">RPL COFFEE N EATERY</h4>
        <p style="margin: 2px 0;">Jl. Raya Nepal No. Labkom 2</p>
        <p style="margin: 2px 0;">Telp: 081234567890</p>
    </div>

    <div class="line"></div>

    <div>
        <span>No. Trx : <?= $trx['kode_transaksi']; ?></span><br>
        <span>Pemesan : <?= htmlspecialchars($trx['nama_pemesan'] ?: '-', ENT_QUOTES, 'UTF-8'); ?></span><br>
        <span>Tanggal : <?= $trx['tanggal']; ?></span><br>
        <span>Kasir   : <?= htmlspecialchars($_SESSION['nama_lengkap']); ?></span><br>
        <span>Metode  : <strong><?= $trx['metode_pembayaran'] ?? 'Tunai'; ?></strong></span>
    </div>

    <div class="line"></div>

    <table>
        <?php while ($item = mysqli_fetch_assoc($query_detail)) : ?>
            <tr>
                <td colspan="3" class="fw-bold"><?= htmlspecialchars($item['nama_menu']); ?></td>
            </tr>
            <tr>
                <td><?= $item['jumlah']; ?> x <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                <td></td>
                <td class="text-end">Rp <?= number_format($item['subtotal'], 0, ',', '.'); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="line"></div>

    <table>
        <tr>
            <td class="fw-bold">Total Belanja:</td>
            <td class="text-end fw-bold">Rp <?= number_format($trx['total_harga'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Bayar:</td>
            <td class="text-end">Rp <?= number_format($trx['bayar'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Kembalian:</td>
            <td class="text-end">Rp <?= number_format($trx['kembalian'], 0, ',', '.'); ?></td>
        </tr>
    </table>

    <div class="line"></div>

    <div class="text-center" style="margin-top: 10px;">
        <p style="margin: 0;">Terima Kasih Atas Kunjungan Anda</p>
        <p style="margin: 2px 0;">Barang yang sudah dibeli tidak dapat ditukar</p>
        <p style="margin: 0px 0;"><hr>LAB-2</p>
        <p style="margin: 0px;">Pass : smkn1pml</p>
    </div>

    <div class="text-center no-print" style="margin-top: 15px;">
        <button onclick="window.print()" style="padding: 5px 10px; font-size: 11px; cursor: pointer;">Cetak Ulang</button>
        <a href="kasir.php" style="display: block; margin-top: 5px; font-size: 11px; text-decoration: none; color: blue;">Kembali ke Kasir</a>
    </div>

</body>
</html>