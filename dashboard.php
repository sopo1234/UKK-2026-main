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
if ($role === 'guru') { header("Location: guru.php"); exit; }

$username = $_SESSION['username']
    ?? $_SESSION['nama_user']
    ?? $_SESSION['name']
    ?? 'Pengguna';

$username = htmlspecialchars(
    (string)$username,
    ENT_QUOTES,
    'UTF-8'
);

// Koneksi database
include 'config/koneksi.php';

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
                    <?= htmlspecialchars(
                        ucfirst($role),
                        ENT_QUOTES,
                        'UTF-8'
                    ); ?>
                </strong>
            </p>

            <hr>

            <!-- KARTU DATA -->
            <div class="row">

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

            </div>

            <hr>

            <!-- INFORMASI DASHBOARD -->
            <h4>Dashboard Pelanggaran Siswa</h4>

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

            <?php } ?>

        </main>

</body>
</html>