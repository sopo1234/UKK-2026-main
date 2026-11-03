<?php
session_start();
include 'config/koneksi.php';

// Cek apakah user sudah login dan memiliki role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
    header("Location: login.php");
    exit;
}

// Acuan tindakan / sanksi berdasarkan akumulasi poin
$tindakan = [
    ['0 - 9',   'Tidak ada tindakan',                 'Monitoring rutin oleh guru'],
    ['10 - 24', 'Peringatan Tertulis',                'Ditangani oleh wali kelas'],
    ['25 - 49', 'Panggilan Orang Tua - Pembinaan BK', 'Ditangani oleh Guru BK'],
    ['50 - 99', 'Skorsing',                           'Sanksi disiplin sekolah'],
    ['> 99',    'Dikembalikan ke Orang Tua',          'Keputusan kepala sekolah'],
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tindakan / Sanksi - UKK 2026</title>

    <?php include 'layout/datatables_head.php'; ?>
</head>
<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Data Tindakan / Sanksi Pelanggaran</h1>
        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>

    <div class="table-card shadow-sm">

        <h5 class="mb-3">Acuan Tindakan Berdasarkan Akumulasi Poin</h5>

        <div class="table-responsive">
            <table class="table table-striped table-bordered table-hover table-sm">

                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Batas Poin</th>
                        <th>Jenis Tindakan / Sanksi</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($tindakan as $i => $t): ?>
                        <tr>
                            <td><?= $i + 1; ?></td>
                            <td><?= htmlspecialchars($t[0], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($t[1], ENT_QUOTES, 'UTF-8'); ?></td>
                            <td><?= htmlspecialchars($t[2], ENT_QUOTES, 'UTF-8'); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
