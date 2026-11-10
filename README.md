# 🧾 Kasir Sederhana

Aplikasi Point of Sale (POS) / Kasir sederhana berbasis web menggunakan PHP dan MySQL. Cocok untuk usaha kecil menengah seperti warung, toko, dan kafe.

## ✨ Fitur

- 🔐 **Login & Autentikasi** — Sistem login dengan session
- 📊 **Dashboard** — Ringkasan data transaksi
- 🍔 **Manajemen Menu** — Tambah, edit, hapus menu/produk
- 💰 **Kasir / POS** — Antarmuka kasir untuk transaksi penjualan
- 🧾 **Cetak Struk** — Cetak struk transaksi
- 📜 **Riwayat Transaksi** — Lihat riwayat transaksi dengan detail
- 📱 **QRIS** — Dukungan pembayaran QRIS

## 🛠️ Teknologi

- **Backend:** PHP (Native)
- **Database:** MySQL
- **Frontend:** HTML, CSS, JavaScript
- **Server:** Laragon / XAMPP / WAMP

## 📋 Prasyarat

- PHP >= 7.4
- MySQL >= 5.7
- Web server (Apache/Nginx)

## 🚀 Instalasi

1. **Clone repository**
   ```bash
   git clone https://github.com/USERNAME/kasir-sederhana.git
   ```

2. **Pindahkan ke folder web server**
   ```bash
   # Contoh untuk Laragon
   cp -r kasir-sederhana C:/laragon/www/
   ```

3. **Import database**
   - Buat database baru dengan nama `db_kasir`
   - Import file SQL yang sudah disediakan:
   ```bash
   mysql -u root db_kasir < db_kasir.sql
   ```
   Atau import melalui phpMyAdmin: pilih database `db_kasir` → tab **Import** → pilih file `db_kasir.sql` → klik **Go**

4. **Konfigurasi koneksi database**
   
   Edit file `koneksi.php` sesuai pengaturan database Anda:
   ```php
   $host = "localhost";
   $user = "root";
   $pass = "";
   $db   = "db_kasir";
   ```

5. **Akses aplikasi**
   
   Buka browser dan akses: `http://localhost/kasir-sederhana`

## 📁 Struktur Proyek

```
kasir-sederhana/
├── admin/
│   ├── dashboard.php       # Halaman dashboard
│   ├── kasir.php           # Halaman kasir/POS
│   ├── menu.php            # Manajemen menu
│   ├── menu_hapus.php      # Hapus menu
│   ├── riwayat.php         # Riwayat transaksi
│   ├── cetak_struk.php     # Cetak struk
│   ├── get_detail_transaksi.php  # API detail transaksi
│   ├── sidebar.php         # Komponen sidebar
│   └── theme.css           # Stylesheet
├── logo/
│   └── LOGO.png            # Logo aplikasi
├── qris/
│   └── QRIS BIO.jpeg       # Gambar QRIS
├── db_kasir.sql            # File database SQL
├── index.php               # Halaman login
├── koneksi.php             # Konfigurasi database
├── logout.php              # Proses logout
└── README.md
```

## 🔑 Login Default

| Username | Password |
|----------|----------|
| `admin`  | `12345`  |

## 📄 Lisensi

Proyek ini bersifat open-source dan tersedia di bawah lisensi [MIT](LICENSE).
