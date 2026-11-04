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
    die("ID kelas tidak ditemukan!");
}

$id = (int) $_GET['id'];

// Tabel yang menunjuk ke t_kelas lewat foreign key kelas_id
// harus dibersihkan dulu, karena constraint-nya RESTRICT.
$tabel_terkait = ['t_pelanggaran_siswa', 't_wali_kelas', 't_kelas_siswa'];

mysqli_begin_transaction($koneksi);

try {
    foreach ($tabel_terkait as $tabel) {
        $stmt = mysqli_prepare(
            $koneksi,
            "DELETE FROM $tabel WHERE kelas_id = ?"
        );
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Baru hapus kelasnya
    $stmt = mysqli_prepare(
        $koneksi,
        "DELETE FROM t_kelas WHERE id = ?"
    );
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($koneksi);

    header("Location: data_kelas.php");
    exit;

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($koneksi);
    echo "Gagal menghapus kelas: "
         . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

?>
