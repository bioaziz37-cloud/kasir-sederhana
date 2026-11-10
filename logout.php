<?php
session_start();

// Hapus semua variabel sesi
$_SESSION = [];

// Hancurkan sesi
session_unset();
session_destroy();

// Arahkan kembali ke halaman login dengan pesan sukses logout
header("Location: index.php?pesan=logout");
exit;
?>