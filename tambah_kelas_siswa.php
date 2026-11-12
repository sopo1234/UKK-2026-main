<?php
session_start();
include 'config/koneksi.php';

// CEK LOGIN & ROLE
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

// Dropdown siswa aktif, kelas aktif, tahun ajaran
$siswa = mysqli_query($koneksi, "SELECT id, nama FROM t_siswa WHERE status_aktif = 1 ORDER BY nama");
$kelas = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas WHERE status_aktif = 1 ORDER BY nama");
$tahun = mysqli_query($koneksi, "SELECT id, nama FROM t_tahun_ajaran WHERE status_aktif = 1 ORDER BY nama DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Penempatan Siswa</title>

    <?php include 'layout/datatables_head.php'; ?>
</head>
<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Tambah Penempatan Siswa ke Kelas</h1>
        <a href="kelola_kelas.php">Kembali ke Kelola Kelas</a>
    </div>

    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Penempatan Baru</h5>

        <form action="proses_tambah_kelas.php" method="POST">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Siswa</label>
                    <select name="siswa_id" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php while ($s = mysqli_fetch_assoc($siswa)) { ?>
                            <option value="<?= (int)$s['id']; ?>">
                                <?= htmlspecialchars($s['nama']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Kelas</label>
                    <select name="kelas_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php while ($k = mysqli_fetch_assoc($kelas)) { ?>
                            <option value="<?= (int)$k['id']; ?>">
                                <?= htmlspecialchars($k['nama'] . ' - ' . $k['tingkat'] . ' ' . $k['jurusan']); ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-control" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php $tahun_terpilih = true; ?>
                        <?php while ($t = mysqli_fetch_assoc($tahun)) { ?>
                            <option value="<?= (int)$t['id']; ?>" <?= $tahun_terpilih ? 'selected' : ''; ?>>
                                <?= htmlspecialchars($t['nama']); ?>
                            </option>
                            <?php $tahun_terpilih = false; ?>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" required>
                </div>

                <div class="form-group col-md-3">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" required>
                </div>

                <div class="form-group col-md-3">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>&nbsp;</label><br>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        Simpan Penempatan
                    </button>
                </div>
            </div>
        </form>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
