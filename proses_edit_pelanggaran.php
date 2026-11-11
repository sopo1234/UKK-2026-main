<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

$user_id = (int)($_SESSION['id_user'] ?? 0);

// Cari guru yang sedang login
$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru) {
    $_SESSION['pesan_error'] = "Data guru tidak ditemukan.";
    header("Location: catatan_pelanggaran.php");
    exit;
}

$guru_id    = (int)$guru['id'];
$id         = (int)($_POST['id'] ?? 0);
$tanggal    = $_POST['tanggal'] ?? '';
$keterangan = trim($_POST['keterangan'] ?? '');
$tindakan   = trim($_POST['tindakan'] ?? '');
$status     = $_POST['status'] ?? '';

$status_valid = ['tercatat', 'ditindak', 'selesai'];

if (
    $id <= 0 ||
    $tanggal === '' ||
    $keterangan === '' ||
    !in_array($status, $status_valid, true)
) {
    $_SESSION['pesan_error'] = "Data tidak lengkap atau tidak valid.";
    header("Location: edit_pelanggaran.php?id=" . $id);
    exit;
}

// Validasi tanggal
$tanggal_obj = DateTime::createFromFormat('!Y-m-d', $tanggal);
if (!$tanggal_obj || $tanggal_obj->format('Y-m-d') !== $tanggal) {
    $_SESSION['pesan_error'] = "Tanggal tidak valid.";
    header("Location: edit_pelanggaran.php?id=" . $id);
    exit;
}

// Update hanya catatan milik guru yang login
$stmt = mysqli_prepare($koneksi, "
    UPDATE t_pelanggaran_siswa
    SET tanggal = ?, keterangan = ?, tindakan = ?,
        status = ?, updated_at = NOW()
    WHERE id = ? AND guru_id = ?
");

mysqli_stmt_bind_param(
    $stmt,
    "ssssii",
    $tanggal,
    $keterangan,
    $tindakan,
    $status,
    $id,
    $guru_id
);

try {
    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) >= 0) {
            $_SESSION['pesan_sukses'] = "Catatan berhasil diperbarui.";
        } else {
            $_SESSION['pesan_error'] = "Catatan tidak ditemukan atau bukan milik Anda.";
        }
    } else {
        $_SESSION['pesan_error'] = "Gagal memperbarui catatan: " . mysqli_stmt_error($stmt);
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['pesan_error'] = "Gagal memperbarui catatan: " . $e->getMessage();
}

mysqli_stmt_close($stmt);

header("Location: catatan_pelanggaran.php");
exit;
