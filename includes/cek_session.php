<?php
// Sesi bersama + proteksi login dasar.
// Role dicek masing-masing halaman setelah include file ini.

// Cegah halaman tersimpan di cache: tombol Back setelah logout harus memuat ulang
// dan diarahkan kembali ke login oleh pengecekan session di bawah.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
