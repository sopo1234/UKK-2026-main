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

// CEK TOMBOL SIMPAN
if (!isset($_POST['simpan'])) {
    header("Location: kelola_guru.php");
    exit;
}

// AMBIL DATA
$nip         = trim($_POST['nip'] ?? '');
$nama        = trim($_POST['nama'] ?? '');
$email       = trim($_POST['email'] ?? '');
$password    = $_POST['password'] ?? '';
$status_aktif = ($_POST['status_aktif'] ?? '1') === '0' ? 0 : 1;

if ($nip === '' || $nama === '' || $email === '' || $password === '') {
    $_SESSION['pesan_error'] = "Semua kolom wajib diisi.";
    header("Location: kelola_guru.php");
    exit;
}

// HASH PASSWORD
$password_hash = password_hash(
    $password,
    PASSWORD_DEFAULT
);

// CEGAH DUPLIKAT: email harus unik di t_users
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_users WHERE email = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$email_terpakai = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
mysqli_stmt_close($stmt);

if ($email_terpakai) {
    $_SESSION['pesan_error'] =
        "Email \"" . $email . "\" sudah digunakan. Gunakan email lain.";
    header("Location: kelola_guru.php");
    exit;
}

// CEGAH DUPLIKAT: NIP harus unik di t_guru
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_guru WHERE nip = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $nip);
mysqli_stmt_execute($stmt);
$nip_terpakai = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
mysqli_stmt_close($stmt);

if ($nip_terpakai) {
    $_SESSION['pesan_error'] =
        "NIP \"" . $nip . "\" sudah terdaftar pada guru lain.";
    header("Location: kelola_guru.php");
    exit;
}

try {
    // ============================
    // SIMPAN KE t_users
    // ============================

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_users (name, email, password, role)
         VALUES (?, ?, ?, 'guru')"
    );
    mysqli_stmt_bind_param($stmt, "sss", $nama, $email, $password_hash);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal menambahkan akun guru.");
    }

    // AMBIL ID USER
    $user_id = mysqli_insert_id($koneksi);
    mysqli_stmt_close($stmt);

    // ============================
    // SIMPAN KE t_guru
    // ============================

    $stmt = mysqli_prepare(
        $koneksi,
        "INSERT INTO t_guru (nip, nama, email, status_aktif, user_id)
         VALUES (?, ?, ?, ?, ?)"
    );
    mysqli_stmt_bind_param($stmt, "sssii", $nip, $nama, $email, $status_aktif, $user_id);

    if (!mysqli_stmt_execute($stmt)) {
        mysqli_stmt_close($stmt);
        throw new Exception("Gagal menambahkan data guru.");
    }

    mysqli_stmt_close($stmt);

    $_SESSION['pesan_sukses'] = "Guru \"" . $nama . "\" berhasil ditambahkan.";
    header("Location: kelola_guru.php");
    exit;

} catch (mysqli_sql_exception $e) {
    if ((int)($e->getCode()) === 1062) {
        $_SESSION['pesan_error'] =
            "Data sudah terdaftar: email atau NIP ini sudah digunakan.";
    } else {
        $_SESSION['pesan_error'] =
            "Gagal menambahkan guru: " . $e->getMessage();
    }
    header("Location: kelola_guru.php");
    exit;

} catch (Throwable $e) {
    $_SESSION['pesan_error'] = "Gagal menambahkan guru: " . $e->getMessage();
    header("Location: kelola_guru.php");
    exit;
}
