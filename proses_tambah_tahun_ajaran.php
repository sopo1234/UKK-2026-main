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

if (!isset($_POST['simpan'])) {
    header("Location: tambah_tahun_ajaran.php");
    exit;
}

$nama = mysqli_real_escape_string($koneksi, $_POST['nama']);
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

// Hanya boleh ada satu tahun ajaran aktif.
if ($status_aktif === 1) {
    $cek = mysqli_query(
        $koneksi,
        "SELECT id FROM t_tahun_ajaran WHERE status_aktif = 1 LIMIT 1"
    );

    if (mysqli_num_rows($cek) > 0) {
        die("Hanya boleh ada satu tahun ajaran aktif. Nonaktifkan tahun ajaran lain terlebih dahulu.");
    }
}

$sql = "INSERT INTO t_tahun_ajaran
        (nama, tanggal_mulai, tanggal_selesai, status_aktif)
        VALUES
        ('$nama', '$tanggal_mulai', '$tanggal_selesai', '$status_aktif')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
} else {
    echo "Gagal menambahkan tahun ajaran: " . mysqli_error($koneksi);
}

?>