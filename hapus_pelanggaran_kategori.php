<?php
session_start();
include 'config/koneksi.php';

// Cek login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// Cek hak akses admin
if (($_SESSION['role'] ?? '') != 'admin') {
    die("Akses ditolak!");
}

// Pastikan parameter ID ada di URL
if (!isset($_GET['id'])) {
    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}

$id = (int) $_GET['id'];

// 1. Cek apakah kategori masih dipakai di tabel pelanggaran
$stmt_cek = mysqli_prepare($koneksi, "
    SELECT COUNT(*) AS jumlah
    FROM t_pelanggaran
    WHERE pelanggaran_kategori_id = ?
");
mysqli_stmt_bind_param($stmt_cek, 'i', $id);
mysqli_stmt_execute($stmt_cek);
$cek = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_cek));

if ((int)$cek['jumlah'] > 0) {
    // Kategori tidak boleh dihapus karena masih dipakai
    header("Location: kelola_pelanggaran_kategori.php?pesan=" .
           urlencode("Kategori tidak bisa dihapus karena masih dipakai oleh " .
                     (int)$cek['jumlah'] . " data pelanggaran.") .
           "&status=gagal");
    exit;
}

// 2. Jika tidak dipakai, hapus kategori
$stmt_hapus = mysqli_prepare($koneksi, "DELETE FROM t_pelanggaran_kategori WHERE id = ?");
mysqli_stmt_bind_param($stmt_hapus, 'i', $id);

if (mysqli_stmt_execute($stmt_hapus)) {
    header("Location: kelola_pelanggaran_kategori.php?pesan=" .
           urlencode("Kategori berhasil dihapus.") . "&status=sukses");
    exit;
}

// Tampilkan pesan jika gagal
die("Gagal menghapus kategori: " . mysqli_stmt_error($stmt_hapus));
?>
