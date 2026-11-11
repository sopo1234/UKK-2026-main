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
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

$nama            = trim($_POST['nama'] ?? '');
$tanggal_mulai   = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif    = (int) ($_POST['status_aktif'] ?? 1);

if ($nama === '') {
    $_SESSION['pesan_error'] = "Nama tahun ajaran wajib diisi.";
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

// Validasi format tanggal
$mulai   = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (!$mulai || $mulai->format('Y-m-d') !== $tanggal_mulai ||
    !$selesai || $selesai->format('Y-m-d') !== $tanggal_selesai) {
    $_SESSION['pesan_error'] = "Format tanggal tidak valid.";
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

if ($tanggal_selesai < $tanggal_mulai) {
    $_SESSION['pesan_error'] = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

// CEGAH DUPLIKAT: nama tahun ajaran harus unik
$stmt = mysqli_prepare(
    $koneksi,
    "SELECT id FROM t_tahun_ajaran WHERE nama = ? LIMIT 1"
);
mysqli_stmt_bind_param($stmt, "s", $nama);
mysqli_stmt_execute($stmt);
$sudah_ada = mysqli_num_rows(mysqli_stmt_get_result($stmt)) > 0;
mysqli_stmt_close($stmt);

if ($sudah_ada) {
    $_SESSION['pesan_error'] =
        "Tahun ajaran \"" . $nama . "\" sudah terdaftar.";
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

// SIMPAN (prepared statement)
$stmt = mysqli_prepare(
    $koneksi,
    "INSERT INTO t_tahun_ajaran (nama, tanggal_mulai, tanggal_selesai, status_aktif)
     VALUES (?, ?, ?, ?)"
);
mysqli_stmt_bind_param($stmt, "sssi", $nama, $tanggal_mulai, $tanggal_selesai, $status_aktif);

try {
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['pesan_sukses'] =
            "Tahun ajaran \"" . $nama . "\" berhasil ditambahkan.";
    } else {
        $_SESSION['pesan_error'] =
            "Gagal menambahkan tahun ajaran: " . mysqli_stmt_error($stmt);
    }
} catch (mysqli_sql_exception $e) {
    if ((int)($e->getCode()) === 1062) {
        $_SESSION['pesan_error'] =
            "Tahun ajaran \"" . $nama . "\" sudah terdaftar.";
    } else {
        $_SESSION['pesan_error'] =
            "Gagal menambahkan tahun ajaran: " . $e->getMessage();
    }
}

mysqli_stmt_close($stmt);

header("Location: kelola_tahun_ajaran.php");
exit;
