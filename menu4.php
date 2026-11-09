<?php
session_start();
if (!isset($_SESSION['role'])) { header("Location: login.php"); exit; }
if ($_SESSION['role'] != "admin" && $_SESSION['role'] != "guru") {
    echo "<h1>Akses Ditolak!</h1>"; exit;
}
?>
<!DOCTYPE html><html lang="id"><head><title>Menu 4</title>
<style>
    body { font-family: Arial, sans-serif; background: linear-gradient(135deg,#f0fdf4 0%,#dcfce7 100%); color:#14532d; padding:30px; }
    a { color:#166534; }
    a:hover { color:#14532d; }
</style>
</head><body>
<h1>Menu 4</h1><p>Menu 4 dapat diakses oleh Admin dan Guru.</p>
<a href="dashboard.php">Kembali ke Dashboard</a>
</body></html>