<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh akses
if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    $_SESSION['pesan_error'] = "ID tidak valid.";
    header("Location: penempatan_siswa.php");
    exit;
}

$stmt = mysqli_prepare($koneksi, "DELETE FROM t_kelas_siswa WHERE id = ?");
mysqli_stmt_bind_param($stmt, "i", $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = "Penempatan siswa berhasil dihapus.";
} else {
    $_SESSION['pesan_error'] = "Gagal menghapus: " . mysqli_stmt_error($stmt);
}
mysqli_stmt_close($stmt);

header("Location: penempatan_siswa.php");
exit;