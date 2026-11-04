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

// Pastikan data dikirim melalui form POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: kelola_pelanggaran_kategori.php");
    exit;
}

// Ambil dan bersihkan data dari form
$id           = (int) ($_POST['id'] ?? 0);
$nama         = trim($_POST['nama'] ?? '');
$deskripsi    = trim($_POST['deskripsi'] ?? '');
$status_aktif = (int) ($_POST['status_aktif'] ?? 0);

// Cek kelengkapan data
if ($id <= 0 || $nama == '' || $deskripsi == '') {
    die("Nama dan deskripsi wajib diisi.");
}

// Update kategori (prepared statement agar aman)
$stmt = mysqli_prepare($koneksi, "
    UPDATE t_pelanggaran_kategori
    SET nama = ?, deskripsi = ?, status_aktif = ?, updated_at = NOW()
    WHERE id = ?
");

mysqli_stmt_bind_param($stmt, "ssii", $nama, $deskripsi, $status_aktif, $id);

if (mysqli_stmt_execute($stmt)) {
    // Kembali ke daftar kategori dengan pesan sukses
    header("Location: kelola_pelanggaran_kategori.php?pesan=" .
           urlencode("Kategori berhasil diperbarui.") . "&status=sukses");
    exit;
}

// Tampilkan pesan jika gagal
die("Gagal memperbarui kategori: " . mysqli_stmt_error($stmt));
?>
