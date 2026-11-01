<?php
session_start();
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// shortcut: form tambah sekarang berada di dalam kelola_guru.php
// (buka halaman kelola untuk menambah data guru)
header("Location: kelola_guru.php");
exit;
