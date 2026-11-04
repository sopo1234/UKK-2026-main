<?php

session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

if (!isset($_POST['simpan'])) {
    header("Location: tambah_siswa.php");
    exit;
}

$nis = trim($_POST['nis']);
$nisn = trim($_POST['nisn']);

// NIS boleh kosong (maks 15 digit), NISN wajib (maks 10 digit); keduanya hanya angka.
if (
    ($nis !== '' && (!ctype_digit($nis) || strlen($nis) > 15)) ||
    !ctype_digit($nisn) ||
    strlen($nisn) > 10
) {
    die("NIS maksimal 15 digit dan NISN maksimal 10 digit, hanya angka.");
}
$nama = $_POST['nama'];
$jenis_kelamin = $_POST['jenis_kelamin'];
$tanggal_lahir = $_POST['tanggal_lahir'];
$alamat = $_POST['alamat'];
$status_aktif = $_POST['status_aktif'];

$sql = "INSERT INTO t_siswa
        (
            nis,
            nisn,
            nama,
            jenis_kelamin,
            tanggal_lahir,
            alamat,
            status_aktif
        )
        VALUES
        (
            '$nis',
            '$nisn',
            '$nama',
            '$jenis_kelamin',
            '$tanggal_lahir',
            '$alamat',
            '$status_aktif'
        )";

if (mysqli_query($koneksi, $sql)) {

    header("Location: kelola_siswa.php");
    exit;

} else {

    echo "Gagal menambahkan siswa: "
         . mysqli_error($koneksi);
}

?>