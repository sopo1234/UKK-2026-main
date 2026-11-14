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
    mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_user']);
    mysqli_stmt_execute($stmt);
    $g = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    if ($g) {
        $guru_id = (int) $g['id'];

        // Hanya wali kelas aktif yang boleh mengelola laporan.
        $stmt = mysqli_prepare($koneksi, "
            SELECT 1 FROM t_wali_kelas
            WHERE guru_id = ? AND status_aktif = 1
            LIMIT 1
        ");
        mysqli_stmt_bind_param($stmt, "i", $guru_id);
        mysqli_stmt_execute($stmt);
        $wali_aktif = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
        mysqli_stmt_close($stmt);

        if (!$wali_aktif) {
            $_SESSION['pesan_error'] = 'Laporan hanya dapat diakses oleh guru yang menjadi wali kelas aktif.';
            header("Location: dashboard.php");
            exit;
        }

        if ($id > 0) {
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
    } else {
        $_SESSION['pesan_error'] = "Laporan tidak ditemukan.";
    }
}

header("Location: laporan_pelanggaran.php");
exit;
