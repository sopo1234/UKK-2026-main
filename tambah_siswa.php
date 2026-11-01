<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// shortcut: form tambah sekarang berada di dalam kelola_siswa.php
// (buka halaman kelola untuk menambah data siswa)
header("Location: kelola_siswa.php");
exit;
