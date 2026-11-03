<?php
session_start();

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower((string)($_SESSION['role'] ?? ''));

if (!in_array($role, ['admin', 'guru'], true)) {
    exit("Akses ditolak!");
}

include 'config/koneksi.php';

// Guru yang ditunjuk sebagai wali kelas aktif tetap ber-role 'guru',
// tetapi mendapat panel khusus di dashboard yang sama.
$wali_kelas = null;
if ($role === 'guru') {
    $q_wali = mysqli_query($koneksi, "
        SELECT wk.kelas_id, k.nama AS nama_kelas
        FROM t_wali_kelas wk
        JOIN t_guru g ON g.id = wk.guru_id
        LEFT JOIN t_kelas k ON k.id = wk.kelas_id
        WHERE g.user_id = " . (int)($_SESSION['id_user'] ?? 0) . "
          AND wk.status_aktif = 1
        LIMIT 1
    ");
    if ($q_wali && mysqli_num_rows($q_wali) === 1) {
        $wali_kelas = mysqli_fetch_assoc($q_wali);
    }
}

$username = $_SESSION['username']
    ?? $_SESSION['nama_user']
    ?? $_SESSION['name']
    ?? 'Pengguna';

$username = htmlspecialchars(
    (string)$username,
    ENT_QUOTES,
    'UTF-8'
);

/**
 * Helper: hitung jumlah baris sebuah tabel secara realtime.
 */
function hitung_tabel(string $tabel): int
{
    global $koneksi;

    $query = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM `$tabel`");

    if (!$query) {
        return 0;
    }

    $row = mysqli_fetch_assoc($query);

    return (int)($row['total'] ?? 0);
}

// Angka realtime dari database
$total_siswa = hitung_tabel('t_siswa');
$total_guru  = hitung_tabel('t_guru');
$total_kelas = hitung_tabel('t_kelas');

// Angka untuk panel wali kelas
$total_siswa_wk      = 0;
$total_pelanggaran_wk = 0;
if ($wali_kelas) {
    $kelas_id_wk = (int)$wali_kelas['kelas_id'];

    $q = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_kelas_siswa WHERE kelas_id = $kelas_id_wk AND status_aktif = 1");
    $total_siswa_wk = $q ? (int)mysqli_fetch_assoc($q)['total'] : 0;

    $q = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM t_pelanggaran_siswa WHERE kelas_id = $kelas_id_wk");
    $total_pelanggaran_wk = $q ? (int)mysqli_fetch_assoc($q)['total'] : 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Pelanggaran Siswa</title>

    <!-- Bootstrap 5 agar min-vh-100 berfungsi -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">
</head>

<body class="bg-white">

<!-- SIDEBAR BERSAMA -->
<?php require_once __DIR__ . '/layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main bg-white">

    <h2>Dashboard</h2>

            <p>
                Selamat datang,
                <strong><?= $username; ?></strong>.
            </p>

            <p>
                Role:
                <strong>
                    <?= $wali_kelas
                        ? 'Guru (Wali Kelas ' . htmlspecialchars($wali_kelas['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8') . ')'
                        : htmlspecialchars(ucfirst($role), ENT_QUOTES, 'UTF-8'); ?>
                </strong>
            </p>

            <hr>

            <!-- KARTU DATA -->
            <div class="row">

            <?php if ($role === 'admin') { ?>

                <!-- KARTU SISWA -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                Data Siswa
                            </h5>

                            <h2><?= $total_siswa; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- KARTU GURU -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                Data Guru
                            </h5>

                            <h2><?= $total_guru; ?></h2>
                        </div>
                    </div>
                </div>

                <!-- KARTU KELAS -->
                <div class="col-md-4 mb-3">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title">
                                Kelas
                            </h5>

                            <h2><?= $total_kelas; ?></h2>
                        </div>
                    </div>
                </div>

            <?php } else { ?>

                <!-- PANEL WALI KELAS -->
                <?php if ($wali_kelas) { ?>
                    <div class="col-md-6 mb-3">
                        <div class="card shadow-sm border-primary">
                            <div class="card-body">
                                <h5 class="card-title">Kelas Yang Diampu</h5>
                                <h2><?= htmlspecialchars($wali_kelas['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Siswa Kelas</h5>
                                <h2><?= $total_siswa_wk; ?></h2>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-3 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Pelanggaran Kelas</h5>
                                <h2><?= $total_pelanggaran_wk; ?></h2>
                            </div>
                        </div>
                    </div>
                <?php } else { ?>

                    <!-- GURU BIASA -->
                    <div class="col-md-4 mb-3">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h5 class="card-title">Catatan Pelanggaran</h5>
                                <p class="card-text">Silakan catat pelanggaran siswa melalui menu di samping.</p>
                            </div>
                        </div>
                    </div>
                <?php } ?>

            <?php } ?>

            </div>

            <hr>

            <!-- INFORMASI DASHBOARD -->
            <h4><?= $wali_kelas ? 'Dashboard Wali Kelas' : 'Dashboard ' . ucfirst($role); ?></h4>

            <p>
                Selamat datang di sistem informasi pelanggaran siswa.
                Silakan pilih menu di sebelah kiri untuk melanjutkan.
            </p>

            <?php if ($role === 'admin') { ?>

                <a href="kelola_siswa.php"
                   class="btn btn-primary">
                    Kelola Data Siswa
                </a>

                <a href="kelola_pelanggaran.php"
                   class="btn btn-secondary">
                    Data Pelanggaran
                </a>

            <?php } else { ?>

                <a href="catatan_pelanggaran.php"
                   class="btn btn-primary">
                    Catat Pelanggaran
                </a>

                <a href="rekap_poin.php"
                   class="btn btn-secondary">
                    Rekap Poin
                </a>

                <?php if ($wali_kelas) { ?>
                    <a href="kelola_kelas_siswa.php"
                       class="btn btn-info">
                        Data Siswa Kelas <?= htmlspecialchars($wali_kelas['nama_kelas'] ?? '', ENT_QUOTES, 'UTF-8'); ?>
                    </a>
                <?php } ?>

            <?php } ?>

        </main>

</body>
</html>