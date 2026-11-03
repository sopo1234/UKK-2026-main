<?php
// Sesi bersama + proteksi login dasar.
// Role dicek masing-masing halaman setelah include file ini.
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
