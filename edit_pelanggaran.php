<?php
// edit_pelanggaran.php — form edit catatan pelanggaran milik guru yang login
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

// Pesan dari proses_edit_pelanggaran.php
if (isset($_SESSION['pesan_error'])) {
    $pesan_error = $_SESSION['pesan_error'];
    unset($_SESSION['pesan_error']);
}

function aman($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}

$user_id = (int)($_SESSION['id_user'] ?? 0);

// Cari guru yang sedang login
$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$guru) {
    die("Data guru tidak ditemukan.");
}

$guru_id = (int)$guru['id'];
$id = (int)($_GET['id'] ?? 0);

// Ambil catatan milik guru yang login
$stmt = mysqli_prepare($koneksi, "
    SELECT id, tanggal, nama_siswa, nama_kelas, nama_pelanggaran,
           poin, keterangan, tindakan, status
    FROM t_pelanggaran_siswa
    WHERE id = ? AND guru_id = ?
    LIMIT 1
");
mysqli_stmt_bind_param($stmt, "ii", $id, $guru_id);
mysqli_stmt_execute($stmt);
$catatan = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

if (!$catatan) {
    $_SESSION['pesan_error'] = "Catatan tidak ditemukan atau bukan milik Anda.";
    header("Location: catatan_pelanggaran.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Edit Catatan Pelanggaran</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <link rel="stylesheet" href="layout/layout.css">
    <link rel="stylesheet" href="layout/tema.css">
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Edit Catatan Pelanggaran</h1>

        <a href="catatan_pelanggaran.php" class="btn btn-secondary">
            Kembali ke Catatan Pelanggaran
        </a>
    </div>

    <?php if (isset($pesan_error)): ?>
        <div class="alert alert-danger"><?= aman($pesan_error) ?></div>
    <?php endif; ?>

    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Edit Catatan</h5>

        <p class="text-muted mb-3">
            <b><?= aman($catatan['nama_siswa']) ?></b> (<?= aman($catatan['nama_kelas']) ?>) &mdash;
            <?= aman($catatan['nama_pelanggaran']) ?> (<?= (int)$catatan['poin'] ?> poin)
        </p>

        <form action="proses_edit_pelanggaran.php" method="POST">

            <input type="hidden" name="id" value="<?= (int)$catatan['id']; ?>">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" required
                           value="<?= aman($catatan['tanggal']); ?>">
                </div>

                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="status" class="form-control" required>
                        <option value="tercatat" <?= $catatan['status'] === 'tercatat' ? 'selected' : ''; ?>>
                            Tercatat (belum ditindak)
                        </option>
                        <option value="ditindak" <?= $catatan['status'] === 'ditindak' ? 'selected' : ''; ?>>
                            Sedang ditindak
                        </option>
                        <option value="selesai" <?= $catatan['status'] === 'selesai' ? 'selected' : ''; ?>>
                            Selesai
                        </option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Tindakan <span class="text-muted">(opsional)</span></label>
                    <textarea name="tindakan" class="form-control" rows="3"
                              placeholder="Kosongkan jika belum ada tindakan"><?= aman($catatan['tindakan']); ?></textarea>
                </div>
            </div>

            <div class="form-group">
                <label>Keterangan</label>
                <textarea name="keterangan" class="form-control" rows="3"
                          required><?= aman($catatan['keterangan']); ?></textarea>
            </div>

            <button type="submit" name="edit" class="btn btn-primary">
                Simpan Perubahan
            </button>
        </form>
    </div>

</main>

</body>
</html>
