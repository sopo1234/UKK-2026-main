<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh akses
if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: penempatan_siswa.php");
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$siswa_id = (int)($_POST['siswa_id'] ?? 0);
$tahun_ajaran_id = (int)($_POST['tahun_ajaran_id'] ?? 0);
$kelas_id = (int)($_POST['kelas_id'] ?? 0);
$tanggal_mulai = $_POST['tanggal_mulai'] ?? '';
$tanggal_selesai = $_POST['tanggal_selesai'] ?? '';
$status_aktif = $_POST['status_aktif'] ?? '';

if ($id <= 0 || !$siswa_id || !$tahun_ajaran_id || !$kelas_id ||
    empty($tanggal_mulai) || empty($tanggal_selesai) ||
    !in_array((string)$status_aktif, ['0', '1'], true)) {
    $_SESSION['pesan_error'] = "Semua data wajib diisi.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Validasi format tanggal
$mulai = DateTime::createFromFormat('!Y-m-d', $tanggal_mulai);
$selesai = DateTime::createFromFormat('!Y-m-d', $tanggal_selesai);

if (!$mulai || !$selesai ||
    $mulai->format('Y-m-d') !== $tanggal_mulai ||
    $selesai->format('Y-m-d') !== $tanggal_selesai) {
    $_SESSION['pesan_error'] = "Format tanggal tidak valid.";
    header("Location: penempatan_siswa.php");
    exit;
}

if ($tanggal_selesai < $tanggal_mulai) {
    $_SESSION['pesan_error'] = "Tanggal selesai tidak boleh sebelum tanggal mulai.";
    header("Location: penempatan_siswa.php");
    exit;
}

// Cek duplikat (siswa sudah ditempatkan di tahun ajaran yang sama, kecuali record ini sendiri)
$stmt = mysqli_prepare($koneksi, "
    SELECT id FROM t_kelas_siswa
    WHERE siswa_id = ? AND tahun_ajaran_id = ? AND id != ?
");
mysqli_stmt_bind_param($stmt, "iii", $siswa_id, $tahun_ajaran_id, $id);
mysqli_stmt_execute($stmt);
if (mysqli_stmt_get_result($stmt)->num_rows > 0) {
    $_SESSION['pesan_error'] = "Siswa sudah ditempatkan pada tahun ajaran tersebut.";
    header("Location: penempatan_siswa.php");
    exit;
}
mysqli_stmt_close($stmt);

// Update data
$stmt = mysqli_prepare($koneksi, "
    UPDATE t_kelas_siswa
    SET siswa_id = ?, tahun_ajaran_id = ?, kelas_id = ?,
        tanggal_mulai = ?, tanggal_selesai = ?, status_aktif = ?
    WHERE id = ?
");
mysqli_stmt_bind_param($stmt, "iiisssi", $siswa_id, $tahun_ajaran_id, $kelas_id, $tanggal_mulai, $tanggal_selesai, $status_aktif, $id);

if (mysqli_stmt_execute($stmt)) {
    $_SESSION['pesan_sukses'] = "Penempatan siswa berhasil diperbarui.";
} else {
    $_SESSION['pesan_error'] = "Gagal memperbarui: " . mysqli_stmt_error($stmt);
}
mysqli_stmt_close($stmt);

header("Location: penempatan_siswa.php");
exit;