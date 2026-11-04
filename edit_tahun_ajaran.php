<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    exit("Akses ditolak!");
}

// Menu aktif
$activeMenu = 'tahun_ajaran';

// Ambil data tahun ajaran berdasarkan ID
if (!isset($_GET['id'])) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_tahun_ajaran WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (!$hasil || mysqli_num_rows($hasil) === 0) {
    header("Location: kelola_tahun_ajaran.php");
    exit;
}

$tahun_ajaran = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Tahun Ajaran</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="layout/layout.css">
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php require_once __DIR__ . '/layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Edit Tahun Ajaran</h1>

        <a href="kelola_tahun_ajaran.php" class="btn btn-secondary">
            Kembali ke Kelola Tahun Ajaran
        </a>
    </div>

    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Edit Tahun Ajaran</h5>

        <form action="proses_edit_tahun_ajaran.php" method="POST">

            <input type="hidden" name="id"
                   value="<?= (int)$tahun_ajaran['id']; ?>">

            <div class="form-row">
                <div class="form-group col-md-6">
                    <label>Nama Tahun Ajaran</label>
                    <input type="text" name="nama" class="form-control" required
                           placeholder="Contoh: 2025/2026"
                           value="<?= htmlspecialchars((string)($tahun_ajaran['nama'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1" <?= (int)($tahun_ajaran['status_aktif'] ?? 0) === 1 ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= (int)($tahun_ajaran['status_aktif'] ?? 0) === 0 ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" required
                           value="<?= htmlspecialchars((string)($tahun_ajaran['tanggal_mulai'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" required
                           value="<?= htmlspecialchars((string)($tahun_ajaran['tanggal_selesai'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>
            </div>

            <button type="submit" name="edit" class="btn btn-primary">
                Perbarui Data
            </button>

        </form>
    </div>

</main>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
