# Kasir Sederhana

Aplikasi kasir berbasis web yang saya buat pakai PHP native dan MySQL. Dibuat untuk kebutuhan usaha kecil kayak warung, toko, atau kafe — yang penting bisa catat transaksi dan cetak struk.

## Fitur

- Login admin dengan session
- Dashboard ringkasan transaksi
- Kelola menu (tambah, edit, hapus)
- Halaman kasir buat input transaksi
- Cetak struk
- Riwayat transaksi lengkap dengan detailnya
- Pembayaran via QRIS

## Dibuat Pakai

- PHP (native, tanpa framework)
- MySQL
- HTML, CSS, JavaScript
- Laragon buat local server

## Cara Install

Pastikan sudah punya PHP (minimal 7.4), MySQL (minimal 5.7), dan web server (Apache/Nginx). Kalau pakai Laragon, XAMPP, atau WAMP tinggal jalan.

1. Clone repo ini

   ```bash
   git clone https://github.com/bioaziz37-cloud/kasir-sederhana.git
   ```

2. Taruh di folder web server

   Kalau pakai Laragon, copy aja ke `C:/laragon/www/`.

3. Buat database dan import SQL-nya

   Buat database baru namanya `db_kasir`, lalu import file SQL-nya:

   ```bash
   mysql -u root db_kasir < db_kasir.sql
   ```

   Atau kalau lebih nyaman lewat phpMyAdmin: pilih database `db_kasir`, klik tab Import, pilih file `db_kasir.sql`, terus klik Go.

4. Sesuaikan koneksi database (kalau perlu)

   Cek file `koneksi.php`, pastikan settingan-nya sesuai:

   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "db_kasir";
   ```

   Kalau MySQL kamu pakai password, isi di bagian `$pass`.

5. Buka di browser

   Akses `http://localhost/kasir-sederhana` dan login pakai akun default.

## Login Default

| Username | Password |
|----------|----------|
| `admin`  | `12345`  |

## Struktur File

```
kasir-sederhana/
├── admin/
│   ├── dashboard.php
│   ├── kasir.php
│   ├── menu.php
│   ├── menu_hapus.php
│   ├── riwayat.php
│   ├── cetak_struk.php
│   ├── get_detail_transaksi.php
│   ├── sidebar.php
│   └── theme.css
├── logo/
│   └── LOGO.png
├── qris/
│   └── QRIS BIO.jpeg
├── db_kasir.sql
├── index.php
├── koneksi.php
├── logout.php
└── README.md
```

## Lisensi

Open-source, lisensi [MIT](LICENSE).
