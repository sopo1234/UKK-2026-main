<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// shortcut: form tambah sekarang berada di dalam kelola_pelanggaran_kategori.php
// (buka halaman kelola untuk menambah kategori)
header("Location: kelola_pelanggaran_kategori.php");
exit;
