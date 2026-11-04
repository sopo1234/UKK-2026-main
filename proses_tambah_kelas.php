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
    header("Location: tambah_kelas.php");
    exit;
}

$siswa_id = (int) $_POST['siswa_id'];
$kelas_id = (int) $_POST['kelas_id'];
$tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

// Tahun ajaran harus aktif dan siswa belum ditempatkan pada tahun ajaran tersebut.
$cek_ta = mysqli_query(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran
     WHERE id = $tahun_ajaran_id AND status_aktif = 1"
);

if (mysqli_num_rows($cek_ta) === 0) {
    die("Tahun ajaran tidak ditemukan atau tidak aktif.");
}

$cek_siswa = mysqli_query(
    $koneksi,
    "SELECT id FROM t_kelas_siswa
     WHERE siswa_id = $siswa_id AND tahun_ajaran_id = $tahun_ajaran_id
     LIMIT 1"
);

if (mysqli_num_rows($cek_siswa) > 0) {
    die("Siswa sudah ditempatkan pada tahun ajaran tersebut.");
}

$sql = "INSERT INTO t_kelas_siswa
        (siswa_id, tahun_ajaran_id, kelas_id,
         tanggal_mulai, tanggal_selesai, status_aktif)
        VALUES
        ('$siswa_id', '$tahun_ajaran_id', '$kelas_id',
         '$tanggal_mulai', '$tanggal_selesai', '$status_aktif')";

if (mysqli_query($koneksi, $sql)) {
    header("Location: kelola_kelas.php");
    exit;
} else {
    echo "Gagal menambahkan data: " . mysqli_error($koneksi);
}
?>