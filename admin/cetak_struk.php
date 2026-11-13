<?php
require_once __DIR__ . '/../koneksi.php';
require_login();

$kode = trim((string) ($_GET['kode'] ?? ''));
if ($kode === '') {
    die("Kode transaksi tidak ditentukan.");
}

$stmtTrx = mysqli_prepare($koneksi, "SELECT * FROM transaksi WHERE kode_transaksi = ? LIMIT 1");
if (!$stmtTrx) {
    die("Gagal menyiapkan data transaksi.");
}

mysqli_stmt_bind_param($stmtTrx, "s", $kode);
mysqli_stmt_execute($stmtTrx);
$resTrx = mysqli_stmt_get_result($stmtTrx);
$trx = mysqli_fetch_assoc($resTrx);
mysqli_stmt_close($stmtTrx);

if (!$trx) {
    die("Transaksi dengan kode tersebut tidak ditemukan.");
}

$stmtDetail = mysqli_prepare(
    $koneksi,
    "SELECT d.*, m.nama_menu, m.harga 
     FROM detail_transaksi d 
     JOIN menu m ON d.menu_id = m.id 
     WHERE d.kode_transaksi = ?"
);
mysqli_stmt_bind_param($stmtDetail, "s", $kode);
mysqli_stmt_execute($stmtDetail);
$query_detail = mysqli_stmt_get_result($stmtDetail);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Struk <?= e($trx['kode_transaksi']); ?> - RPL Coffee &amp; Eatery</title>
    <link rel="icon" type="image/png" href="../logo/LOGO.png">
    <style>
        @page {
            size: 58mm auto;
            margin: 0;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Courier New', Courier, monospace;
            font-size: 11px;
            width: 58mm;
            margin: 0 auto;
            padding: 4mm 3mm;
            color: #000;
            background: #fff;
            line-height: 1.35;
        }

        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .fw-bold { font-weight: bold; }

        .receipt-header {
            text-align: center;
            margin-bottom: 8px;
        }

        .receipt-logo {
            width: 32px;
            height: 32px;
            object-fit: contain;
            margin-bottom: 4px;
        }

        .store-name {
            font-size: 13px;
            font-weight: bold;
            margin: 0;
            letter-spacing: 0.5px;
        }

        .store-sub {
            font-size: 10px;
            margin: 2px 0 0 0;
            color: #333;
        }

        .divider {
            border-bottom: 1px dashed #000;
            margin: 6px 0;
        }

        .meta-table, .items-table, .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }

        .meta-table td {
            padding: 1px 0;
            vertical-align: top;
        }

        .items-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .summary-table td {
            padding: 1.5px 0;
        }

        .footer-note {
            text-align: center;
            margin-top: 10px;
            font-size: 9.5px;
            color: #444;
        }

        .action-bar {
            margin-top: 16px;
            text-align: center;
        }

        .btn-print {
            background: #000;
            color: #fff;
            border: none;
            padding: 6px 12px;
            font-size: 11px;
            border-radius: 4px;
            cursor: pointer;
            font-family: sans-serif;
        }

        .link-back {
            display: block;
            margin-top: 8px;
            font-size: 11px;
            color: #444;
            text-decoration: none;
            font-family: sans-serif;
        }

        @media print {
            body {
                width: 58mm;
                padding: 2mm;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body onload="window.print()">

    <div class="receipt-header">
        <img class="receipt-logo" src="../logo/LOGO.png" alt="Logo">
        <h1 class="store-name">RPL COFFEE &amp; EATERY</h1>
        <p class="store-sub">Layanan Penjualan Kasir</p>
    </div>

    <div class="divider"></div>

    <table class="meta-table">
        <tr>
            <td style="width: 50px;">No.Trx</td>
            <td style="width: 6px;">:</td>
            <td><?= e($trx['kode_transaksi']); ?></td>
        </tr>
        <tr>
            <td>Waktu</td>
            <td>:</td>
            <td><?= e($trx['tanggal']); ?></td>
        </tr>
        <tr>
            <td>Pemesan</td>
            <td>:</td>
            <td><?= e($trx['nama_pemesan'] ?: '-'); ?></td>
        </tr>
        <tr>
            <td>Kasir</td>
            <td>:</td>
            <td><?= e($_SESSION['nama_lengkap'] ?? 'Staf'); ?></td>
        </tr>
        <tr>
            <td>Metode</td>
            <td>:</td>
            <td><?= e($trx['metode_pembayaran'] ?? 'Tunai'); ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <table class="items-table">
        <?php while ($item = mysqli_fetch_assoc($query_detail)) : ?>
            <tr>
                <td colspan="2" class="fw-bold"><?= e($item['nama_menu']); ?></td>
            </tr>
            <tr>
                <td><?= (int) $item['jumlah']; ?> x <?= number_format((float) $item['harga'], 0, ',', '.'); ?></td>
                <td class="text-end">Rp <?= number_format((float) $item['subtotal'], 0, ',', '.'); ?></td>
            </tr>
        <?php endwhile; ?>
    </table>

    <div class="divider"></div>

    <table class="summary-table">
        <tr>
            <td class="fw-bold">Total</td>
            <td class="text-end fw-bold">Rp <?= number_format((float) $trx['total_harga'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Bayar</td>
            <td class="text-end">Rp <?= number_format((float) $trx['bayar'], 0, ',', '.'); ?></td>
        </tr>
        <tr>
            <td>Kembalian</td>
            <td class="text-end">Rp <?= number_format((float) $trx['kembalian'], 0, ',', '.'); ?></td>
        </tr>
    </table>

    <div class="divider"></div>

    <div class="footer-note">
        <p style="margin: 0 0 2px 0;">Terima kasih atas kunjungan Anda.</p>
        <p style="margin: 0;">Barang yang sudah dibeli tidak dapat ditukar.</p>
    </div>

    <div class="action-bar no-print">
        <button class="btn-print" onclick="window.print()">Cetak Ulang</button>
        <a class="link-back" href="kasir.php">&larr; Kembali ke Kasir</a>
    </div>

</body>
</html>
