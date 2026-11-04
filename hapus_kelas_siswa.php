<?php

session_start();
include 'config/koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// CEK ROLE
if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

// CEK ID
if (!isset($_GET['id'])) {
    die("ID penempatan tidak ditemukan!");
}

$id = (int) $_GET['id'];

// HAPUS PENEMPATAN SISWA KE KELAS
// (tidak ada tabel lain yang menunjuk ke t_kelas_siswa,
//  jadi cukup hapus satu baris ini)
try {
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_kelas_siswa WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    header("Location: kelola_kelas.php");
    exit;

} catch (mysqli_sql_exception $e) {
    echo "Gagal menghapus penempatan: "
         . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

?>
