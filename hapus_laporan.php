<?php
// hapus_laporan.php — hapus laporan tersimpan milik guru yang login
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id'];

    // Identitas guru yang login
    $stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ? LIMIT 1");
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['id']);
    mysqli_stmt_execute($stmt);
    $g = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($g && $id > 0) {
        $guru_id = (int) $g['id'];

        // Hanya laporan milik sendiri yang boleh dihapus
        $stmt = mysqli_prepare($koneksi, "
            DELETE FROM t_laporan
            WHERE id = ? AND guru_id = ?
        ");
        mysqli_stmt_bind_param($stmt, "ii", $id, $guru_id);
        mysqli_stmt_execute($stmt);

        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['pesan_sukses'] = "Laporan berhasil dihapus.";
        } else {
            $_SESSION['pesan_error'] = "Laporan tidak ditemukan.";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['pesan_error'] = "Laporan tidak ditemukan.";
    }
}

header("Location: laporan_pelanggaran.php");
exit;
