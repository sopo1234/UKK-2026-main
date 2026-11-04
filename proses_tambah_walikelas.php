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

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    header("Location: tambah_walikelas.php");
    exit;
}

$tahun_ajaran_id = (int) $_POST['tahun_ajaran_id'];
$kelas_id = (int) $_POST['kelas_id'];
$guru_id = (int) $_POST['guru_id'];
$tanggal_mulai = $_POST['tanggal_mulai'];
$tanggal_selesai = $_POST['tanggal_selesai'];
$status_aktif = (int) $_POST['status_aktif'];

if ($tanggal_selesai < $tanggal_mulai) {
    die("Tanggal selesai tidak boleh sebelum tanggal mulai.");
}

// Tahun ajaran harus aktif, kelas dan guru belum menjadi wali pada tahun tersebut.
$cek_ta = mysqli_query(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran
     WHERE id = $tahun_ajaran_id AND status_aktif = 1"
);

if (mysqli_num_rows($cek_ta) === 0) {
    die("Tahun ajaran tidak ditemukan atau tidak aktif.");
}

$cek_kelas = mysqli_query(
    $koneksi,
    "SELECT id FROM t_wali_kelas
     WHERE kelas_id = $kelas_id AND tahun_ajaran_id = $tahun_ajaran_id
     LIMIT 1"
);

if (mysqli_num_rows($cek_kelas) > 0) {
    die("Kelas ini sudah memiliki wali kelas pada tahun ajaran tersebut.");
}

$cek_guru = mysqli_query(
    $koneksi,
    "SELECT id FROM t_wali_kelas
     WHERE guru_id = $guru_id AND tahun_ajaran_id = $tahun_ajaran_id
     LIMIT 1"
);

if (mysqli_num_rows($cek_guru) > 0) {
    die("Guru ini sudah menjadi wali kelas pada tahun ajaran tersebut.");
}

$query = "INSERT INTO t_wali_kelas
          (tahun_ajaran_id, kelas_id, guru_id,
           tanggal_mulai, tanggal_selesai, status_aktif)
          VALUES (?, ?, ?, ?, ?, ?)";

$stmt = mysqli_prepare($koneksi, $query);

mysqli_stmt_bind_param(
    $stmt,
    "iiissi",
    $tahun_ajaran_id,
    $kelas_id,
    $guru_id,
    $tanggal_mulai,
    $tanggal_selesai,
    $status_aktif
);

if (mysqli_stmt_execute($stmt)) {
    header("Location: kelola_walikelas.php");
    exit;
}

echo "Gagal menyimpan data: " . mysqli_error($koneksi);
?>