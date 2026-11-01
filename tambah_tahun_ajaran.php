<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// shortcut: form tambah sekarang berada di dalam kelola_tahun_ajaran.php
// (buka halaman kelola untuk menambah tahun ajaran)
header("Location: kelola_tahun_ajaran.php");
exit;
