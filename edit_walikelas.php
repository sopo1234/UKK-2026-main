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
$activeMenu = 'walikelas';

// Ambil data wali kelas berdasarkan ID
if (!isset($_GET['id'])) {
    header("Location: kelola_walikelas.php");
    exit;
}

$id = (int) $_GET['id'];

$stmt = mysqli_prepare($koneksi, "SELECT * FROM t_wali_kelas WHERE id = ?");
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (!$hasil || mysqli_num_rows($hasil) === 0) {
    header("Location: kelola_walikelas.php");
    exit;
}

$wali = mysqli_fetch_assoc($hasil);
mysqli_stmt_close($stmt);

// Data untuk dropdown
$guru  = mysqli_query($koneksi, "SELECT id, nama FROM t_guru ORDER BY nama");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY nama");
$tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran ORDER BY nama DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Wali Kelas</title>

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
        <h1>Edit Wali Kelas</h1>

        <a href="kelola_walikelas.php" class="btn btn-secondary">
            Kembali ke Kelola Wali Kelas
        </a>
    </div>

    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Edit Data Wali Kelas</h5>

        <form action="proses_edit_walikelas.php" method="POST">

            <input type="hidden" name="id"
                   value="<?= (int)$wali['id']; ?>">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Guru</label>
                    <select name="guru_id" class="form-control" required>
                        <?php while ($g = mysqli_fetch_assoc($guru)) { ?>
                            <option value="<?= (int)$g['id']; ?>"
                                <?= (int)$g['id'] === (int)($wali['guru_id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars((string)$g['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Kelas</label>
                    <select name="kelas_id" class="form-control" required>
                        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                            <option value="<?= (int)$k['id']; ?>"
                                <?= (int)$k['id'] === (int)($wali['kelas_id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($k['nama'] . ' - ' . $k['tingkat'] . ' ' . $k['jurusan'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-control" required>
                        <?php while ($ta = mysqli_fetch_assoc($tahun)) { ?>
                            <option value="<?= (int)$ta['id']; ?>"
                                <?= (int)$ta['id'] === (int)($wali['tahun_ajaran_id'] ?? 0) ? 'selected' : ''; ?>>
                                <?= htmlspecialchars((string)$ta['nama'], ENT_QUOTES, 'UTF-8'); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" required
                           value="<?= htmlspecialchars((string)($wali['tanggal_mulai'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" required
                           value="<?= htmlspecialchars((string)($wali['tanggal_selesai'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>">
                </div>

                <div class="form-group col-md-3">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1" <?= (int)($wali['status_aktif'] ?? 0) === 1 ? 'selected' : ''; ?>>Aktif</option>
                        <option value="0" <?= (int)($wali['status_aktif'] ?? 0) === 0 ? 'selected' : ''; ?>>Tidak Aktif</option>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>&nbsp;</label><br>
                    <button type="submit" name="edit" class="btn btn-primary">
                        Perbarui Data
                    </button>
                </div>
            </div>

        </form>
    </div>

</main>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
