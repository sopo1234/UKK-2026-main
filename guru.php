<?php
// Dashboard hanya ada satu: dashboard.php.
// Halaman ini diarahkan ke sana agar link lama tidak mati.
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

header("Location: dashboard.php");
exit;
?>
