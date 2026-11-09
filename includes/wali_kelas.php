<?php
// Helper hak akses wali kelas.
// Kepakai: include 'config/koneksi.php' sebelum file ini.
// Mengembalikan null jika guru yang sedang login bukan wali kelas aktif.

function cari_wali_kelas(mysqli $koneksi, int $user_id): ?array
{
    $stmt = mysqli_prepare($koneksi, "
        SELECT wk.kelas_id, k.nama AS nama_kelas
        FROM t_wali_kelas wk
        JOIN t_guru g ON g.id = wk.guru_id
        LEFT JOIN t_kelas k ON k.id = wk.kelas_id
        WHERE g.user_id = ?
          AND wk.status_aktif = 1
        LIMIT 1
    ");

    if (!$stmt) {
        return null;
    }

    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $wali = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
    mysqli_stmt_close($stmt);

    return $wali ?: null;
}
