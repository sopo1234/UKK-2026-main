<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh akses
if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: dashboard.php");
    exit;
}

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    header("Location: penempatan_siswa.php");
    exit;
}

// Ambil data penempatan
$stmt = mysqli_prepare($koneksi, "
    SELECT ks.*, s.nama AS nama_siswa, s.nis, s.nisn,
           ta.nama AS tahun_ajaran_nama,
           k.nama AS kelas_nama, k.tingkat, k.jurusan
    FROM t_kelas_siswa ks
    LEFT JOIN t_siswa s ON ks.siswa_id = s.id
    LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    LEFT JOIN t_kelas k ON ks.kelas_id = k.id
    WHERE ks.id = ?
");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$data) {
    die("Data penempatan tidak ditemukan.");
}

// Ambil dropdown data
$tahun_list = [];
$tahun_result = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran WHERE status_aktif = 1 ORDER BY id DESC");
while ($ta = mysqli_fetch_assoc($tahun_result)) {
    $tahun_list[] = $ta;
}

$siswa_list = [];
$siswa_result = mysqli_query($koneksi, "
    SELECT id, nis, nisn, nama
    FROM t_siswa
    WHERE status_aktif = 1
    ORDER BY nama ASC
");
while ($s = mysqli_fetch_assoc($siswa_result)) {
    $siswa_list[] = $s;
}

$kelas_list = [];
$kelas_result = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas WHERE status_aktif = 1 ORDER BY nama ASC");
while ($k = mysqli_fetch_assoc($kelas_result)) {
    $kelas_list[] = $k;
}

function h($value) {
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Edit Penempatan Siswa</title>
    <?php include 'layout/datatables_head.php'; ?>
</head>
<body>

<!-- SIDEBAR BERSAMA -->
<?php include "layout/sidebar.php"; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Edit Penempatan Siswa</h1>
        <a href="penempatan_siswa.php" class="btn btn-secondary btn-sm">Kembali</a>
    </div>

    <div class="form-card shadow-sm">
        <h5 class="mb-3">Edit Data Penempatan</h5>

        <form action="proses_edit_penempatan.php" method="POST">
            <input type="hidden" name="id" value="<?= h($data['id']) ?>">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Siswa</label>
                    <select name="siswa_id" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswa_list as $s): ?>
                            <option value="<?= h($s['id']) ?>" <?= $s['id'] == $data['siswa_id'] ? 'selected' : '' ?>>
                                <?= h($s['nis'] . ' - ' . $s['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-control" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php foreach ($tahun_list as $ta): ?>
                            <option value="<?= h($ta['id']) ?>" <?= $ta['id'] == $data['tahun_ajaran_id'] ? 'selected' : '' ?>>
                                <?= h($ta['nama']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Kelas</label>
                    <select name="kelas_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= h($k['id']) ?>" <?= $k['id'] == $data['kelas_id'] ? 'selected' : '' ?>>
                                <?= h($k['nama'] . ' - Tingkat ' . $k['tingkat'] . ' - ' . $k['jurusan']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" value="<?= h($data['tanggal_mulai']) ?>" required>
                </div>

                <div class="form-group col-md-4">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" value="<?= h($data['tanggal_selesai']) ?>" required>
                </div>

                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1" <?= (int)$data['status_aktif'] === 1 ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= (int)$data['status_aktif'] === 0 ? 'selected' : '' ?>>Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="penempatan_siswa.php" class="btn btn-secondary">Batal</a>
        </form>
    </div>

</main>

<?php include 'layout/datatables_bottom.php'; ?>

</body>
</html>