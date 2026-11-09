<?php
session_start();
include 'config/koneksi.php';

// Cek apakah user sudah login dan memiliki role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
    header("Location: login.php");
    exit;
}

// Riwayat catatan pelanggaran.
// Wali kelas hanya melihat riwayat dari kelas yang diampu.
include 'includes/wali_kelas.php';
$wali = cari_wali_kelas($koneksi, (int)($_SESSION['id_user'] ?? 0));

if ($wali) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT tanggal, nama_siswa, nama_kelas, nama_pelanggaran,
               poin, nama_guru, status
        FROM t_pelanggaran_siswa
        WHERE kelas_id = ?
        ORDER BY tanggal DESC, id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $wali['kelas_id']);
    mysqli_stmt_execute($stmt);
    $riwayat = mysqli_stmt_get_result($stmt);
} else {
    // Riwayat seluruh catatan pelanggaran (semua guru)
    $riwayat = mysqli_query($koneksi, "
        SELECT tanggal, nama_siswa, nama_kelas, nama_pelanggaran,
               poin, nama_guru, status
        FROM t_pelanggaran_siswa
        ORDER BY tanggal DESC, id DESC
    ");
}

if (!$riwayat) {
    die("Gagal mengambil riwayat: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pelanggaran - UKK 2026</title>

    <?php include 'layout/datatables_head.php'; ?>
</head>
<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Riwayat Pelanggaran Siswa</h1>
        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>

    <div class="table-card shadow-sm">

        <h5 class="mb-3">Daftar Riwayat Pelanggaran</h5>

        <div class="table-responsive">
            <table id="tabelRiwayat"
                   class="table table-striped table-bordered table-hover table-sm">

                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Nama Siswa</th>
                        <th>Kelas</th>
                        <th>Jenis Pelanggaran</th>
                        <th>Poin</th>
                        <th>Guru Pencatat</th>
                        <th>Status</th>
                    </tr>
                </thead>

                <tbody>
                <?php if (mysqli_num_rows($riwayat) > 0): ?>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($riwayat)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= htmlspecialchars($row['tanggal'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['nama_siswa'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($row['nama_pelanggaran'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= (int)($row['poin'] ?? 0); ?></td>
                            <td><?= htmlspecialchars($row['nama_guru'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></td>
                            <td>
                                <span class="badge badge-secondary">
                                    <?= htmlspecialchars($row['status'] ?? '-', ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
                </tbody>

            </table>
        </div>
    </div>

</main>

<?php $dt_id = 'tabelRiwayat'; $dt_empty = 'Belum ada data riwayat pelanggaran'; include 'layout/datatables_bottom.php'; ?>

</body>
</html>
