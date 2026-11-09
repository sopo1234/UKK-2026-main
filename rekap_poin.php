<?php
session_start();
include 'config/koneksi.php';

// Cek apakah user sudah login dan memiliki role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
    header("Location: login.php");
    exit;
}

// Rekap total poin pelanggaran per siswa dikelas aktif.
// Wali kelas hanya melihat rekap dari kelas yang diampu.
include 'includes/wali_kelas.php';
$wali = cari_wali_kelas($koneksi, (int)($_SESSION['id_user'] ?? 0));

if ($wali) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT s.id, s.nama,
               COALESCE(k.nama, '-') AS nama_kelas,
               COALESCE(SUM(ps.poin), 0) AS total_poin
        FROM t_siswa s
        INNER JOIN t_kelas_siswa ks ON ks.siswa_id = s.id AND ks.status_aktif = 1
        INNER JOIN t_kelas k ON k.id = ks.kelas_id
        LEFT JOIN t_pelanggaran_siswa ps ON ps.siswa_id = s.id
        WHERE s.status_aktif = 1
          AND ks.kelas_id = ?
        GROUP BY s.id, s.nama, k.nama
        ORDER BY total_poin DESC, s.nama
    ");
    mysqli_stmt_bind_param($stmt, "i", $wali['kelas_id']);
    mysqli_stmt_execute($stmt);
    $rekap = mysqli_stmt_get_result($stmt);
} else {
    $rekap = mysqli_query($koneksi, "
        SELECT s.id, s.nama,
               COALESCE(k.nama, '-') AS nama_kelas,
               COALESCE(SUM(ps.poin), 0) AS total_poin
        FROM t_siswa s
        LEFT JOIN t_kelas_siswa ks ON ks.siswa_id = s.id AND ks.status_aktif = 1
        LEFT JOIN t_kelas k ON k.id = ks.kelas_id
        LEFT JOIN t_pelanggaran_siswa ps ON ps.siswa_id = s.id
        WHERE s.status_aktif = 1
        GROUP BY s.id, s.nama, k.nama
        ORDER BY total_poin DESC, s.nama
    ");
}

if (!$rekap) {
    die("Gagal mengambil rekap poin: " . mysqli_error($koneksi));
}

// Tindakan mengikuti akumulasi poin siswa
function tindakan_poin(int $poin): string
{
    if ($poin > 100) return 'Panggilan Orang Tua / Dikembalikan';
    if ($poin >= 50) return 'Skorsing & Pembinaan BK';
    if ($poin >= 25) return 'Peringatan Tertulis';
    if ($poin >= 10) return 'Peringatan Lisan';
    return 'Aman';
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekap Poin Siswa - UKK 2026</title>

    <?php include 'layout/datatables_head.php'; ?>
</head>
<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Rekap Poin Pelanggaran Siswa</h1>
        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>

    <div class="table-card shadow-sm">

        <h5 class="mb-3">Rekap Akumulasi Poin</h5>

        <div class="table-responsive">
            <table id="tabelRekap"
                   class="table table-striped table-bordered table-hover table-sm">

                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Total Poin</th>
                        <th>Status / Tindakan</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (mysqli_num_rows($rekap) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($rekap)): ?>
                        <?php $total = (int)$row['total_poin']; ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['nama'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas'], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <?= $total > 0
                                    ? '<span class="badge badge-danger">' . $total . '</span>'
                                    : 0; ?>
                            </td>
                            <td><?= tindakan_poin($total); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

</main>

<?php $dt_id = 'tabelRekap'; $dt_empty = 'Belum ada data rekap poin'; include 'layout/datatables_bottom.php'; ?>

</body>
</html>
