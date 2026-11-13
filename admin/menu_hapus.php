<?php
require_once __DIR__ . '/../koneksi.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf()) {
        header("Location: menu.php?pesan=gagal_csrf");
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM menu WHERE id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $deleted = mysqli_stmt_affected_rows($stmt) > 0;
            mysqli_stmt_close($stmt);

            if ($deleted) {
                header("Location: menu.php?pesan=sukses_hapus");
                exit;
            }
        }
    }
}

// Fallback jika masih ada akses GET langsung tapi divalidasi
header("Location: menu.php?pesan=gagal_hapus");
exit;
