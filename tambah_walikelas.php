<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// shortcut: form tambah sekarang berada di dalam kelola_walikelas.php
// (buka halaman kelola untuk menambah wali kelas)
header("Location: kelola_walikelas.php");
exit;
