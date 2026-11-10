<?php
session_start();

if (!isset($_SESSION['status_login']) || $_SESSION['status_login'] != true) {
    header("Location: ../index.php?pesan=belum_login");
    exit;
}

require '../koneksi.php';

if (!isset($koneksi) && isset($conn)) {
    $koneksi = $conn;
}

if (!isset($koneksi)) {
    $koneksi = mysqli_connect('localhost', 'root', '', 'kasir_sederhana');
}

if (isset($_GET['id'])) {
    $id = (int) $_GET['id'];
    mysqli_query($koneksi, "DELETE FROM menu WHERE id = '$id'");
}

header("Location: menu.php?pesan=sukses_hapus");
exit;