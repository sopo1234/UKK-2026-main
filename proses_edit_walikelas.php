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

// File ini hanya menangani POST dari edit_walikelas.php
if (!isset($_POST['edit'])) {
    header("Location: kelola_walikelas.php");
    exit;
}

$id              = (int) $_POST['id'];
$guru_id         = (int) $_POST['guru_id'];
$kelas_id        = (int) $_POST['kelas_id'];
$tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];
$tanggal_mulai   = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
$tanggal_selesai = mysqli_real_escape_string($koneksi, $_POST['tanggal_selesai']);
$status_aktif    = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

$sql = "UPDATE t_wali_kelas
        SET
            guru_id='$guru_id',
            kelas_id='$kelas_id',
            tahun_ajaran_id='$tahun_ajaran_id',
            tanggal_mulai='$tanggal_mulai',
            tanggal_selesai='$tanggal_selesai',
            status_aktif='$status_aktif',
            updated_at=CURRENT_TIMESTAMP
        WHERE id='$id'";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kelola_walikelas.php");
    exit;
} else {
    echo "Gagal mengubah data wali kelas: " . mysqli_error($koneksi);
}

?>
