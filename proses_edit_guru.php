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
    echo "Akses ditolak!";
    exit;
}

// CEK TOMBOL EDIT
if (!isset($_POST['edit'])) {
    header("Location: kelola_guru.php");
    exit;
}

// AMBIL DATA
$id = $_POST['id'];
$nip = trim($_POST['nip']);
$nama = $_POST['nama'];
$email = $_POST['email'];
$status_aktif = $_POST['status_aktif'];
$password = $_POST['password'] ?? '';
$konfirmasi_password = $_POST['konfirmasi_password'] ?? '';

if (!ctype_digit($nip) || strlen($nip) > 18) {
    die("NIP maksimal 18 digit dan hanya angka.");
}

if ($password !== '' && $password !== $konfirmasi_password) {
    die("Password dan konfirmasi password tidak sama.");
}


// ============================
// CARI USER ID
// ============================

$sql_cari = "SELECT user_id
             FROM t_guru
             WHERE id='$id'";

$hasil = mysqli_query($koneksi, $sql_cari);

if (!$hasil) {

    echo "Gagal mencari guru: "
         . mysqli_error($koneksi);

    exit;
}

$guru = mysqli_fetch_assoc($hasil);

if (!$guru) {

    echo "Data guru tidak ditemukan!";

    exit;
}

$user_id = $guru['user_id'];


// ============================
// UPDATE t_guru
// ============================

$sql_guru = "UPDATE t_guru
             SET
                nip='$nip',
                nama='$nama',
                email='$email',
                status_aktif='$status_aktif',
                updated_at=CURRENT_TIMESTAMP
             WHERE id='$id'";

if (!mysqli_query($koneksi, $sql_guru)) {

    echo "Gagal mengubah data guru: "
         . mysqli_error($koneksi);

    exit;
}


// ============================
// UPDATE t_users
// ============================

$sql_user = "UPDATE t_users
             SET
                name='$nama',
                email='$email',
                updated_at=CURRENT_TIMESTAMP";

if ($password !== '') {
    $password_hash = mysqli_real_escape_string(
        $koneksi,
        password_hash($password, PASSWORD_DEFAULT)
    );
    $sql_user .= ", password='$password_hash'";
}

$sql_user .= "
             WHERE id='$user_id'
             AND role='guru'";

if (!mysqli_query($koneksi, $sql_user)) {

    echo "Data guru berhasil diubah, "
         . "tetapi akun user gagal diubah: "
         . mysqli_error($koneksi);

    exit;
}


// KEMBALI
header("Location: kelola_guru.php");
exit;

?>