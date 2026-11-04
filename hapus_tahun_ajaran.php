<?php

session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

if (!isset($_GET['id'])) {
    die("ID tahun ajaran tidak ditemukan!");
}

$id = (int) $_GET['id'];

mysqli_begin_transaction($koneksi);

try {
    // Tabel yang menunjuk ke t_tahun_ajaran lewat foreign key
    // harus dibersihkan dulu, karena constraint-nya RESTRICT.
    $tabel_terkait = ['t_wali_kelas', 't_kelas_siswa', 't_pelanggaran_siswa'];

    foreach ($tabel_terkait as $tabel) {
        $stmt = mysqli_prepare($koneksi, "DELETE FROM $tabel WHERE tahun_ajaran_id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_close($stmt);
    }

    // Baru hapus tahun ajarannya
    $stmt = mysqli_prepare($koneksi, "DELETE FROM t_tahun_ajaran WHERE id = ?");
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    mysqli_commit($koneksi);

    header("Location: kelola_tahun_ajaran.php");
    exit;

} catch (mysqli_sql_exception $e) {
    mysqli_rollback($koneksi);
    echo "Gagal menghapus tahun ajaran: "
         . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
}

?>
