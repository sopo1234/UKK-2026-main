<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';
include 'includes/wali_kelas.php';

// Halaman ini khusus guru
if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

$user_id = (int)($_SESSION['id_user'] ?? 0);

// Cari data guru dari akun yang sedang login
$stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM t_guru WHERE user_id = ?");
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$guru = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$guru) {
    die("Data guru belum terhubung dengan akun pengguna.");
}

$guru_id = (int)$guru['id'];

// Kelas yang diampu wali kelas aktif (null jika guru belum jadi wali kelas)
$wali = cari_wali_kelas($koneksi, $user_id);
$wali_kelas_id = $wali ? (int)$wali['kelas_id'] : null;

// Ambil siswa yang mempunyai penempatan kelas aktif.
// Wali kelas hanya melihat siswa dari kelas yang diampu.
if ($wali_kelas_id) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT ks.id AS penempatan_id, s.nama AS nama_siswa,
               k.nama AS nama_kelas
        FROM t_kelas_siswa ks
        JOIN t_siswa s ON s.id = ks.siswa_id
        JOIN t_kelas k ON k.id = ks.kelas_id
        WHERE ks.status_aktif = 1 AND s.status_aktif = 1
          AND ks.kelas_id = ?
        ORDER BY s.nama
    ");
    mysqli_stmt_bind_param($stmt, "i", $wali_kelas_id);
    mysqli_stmt_execute($stmt);
    $siswa = mysqli_stmt_get_result($stmt);
} else {
    $siswa = mysqli_query($koneksi, "
        SELECT ks.id AS penempatan_id, s.nama AS nama_siswa,
               k.nama AS nama_kelas
        FROM t_kelas_siswa ks
        JOIN t_siswa s ON s.id = ks.siswa_id
        JOIN t_kelas k ON k.id = ks.kelas_id
        WHERE ks.status_aktif = 1 AND s.status_aktif = 1
        ORDER BY s.nama
    ");
}

// Ambil jenis pelanggaran aktif
$pelanggaran = mysqli_query($koneksi, "
    SELECT id, nama, poin
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama
");

// Ambil catatan pelanggaran.
// Wali kelas hanya melihat catatan dari kelas yang diampu;
// guru biasa melihat catatan miliknya sendiri.
if ($wali_kelas_id) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT *
        FROM t_pelanggaran_siswa
        WHERE kelas_id = ?
        ORDER BY tanggal DESC, id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $wali_kelas_id);
    mysqli_stmt_execute($stmt);
    $catatan = mysqli_stmt_get_result($stmt);
} else {
    $stmt = mysqli_prepare($koneksi, "
        SELECT *
        FROM t_pelanggaran_siswa
        WHERE guru_id = ?
        ORDER BY tanggal DESC, id DESC
    ");
    mysqli_stmt_bind_param($stmt, "i", $guru_id);
    mysqli_stmt_execute($stmt);
    $catatan = mysqli_stmt_get_result($stmt);
}

// Mengamankan teks sebelum ditampilkan ke halaman
function aman_catatan($teks) {
    return htmlspecialchars((string)$teks, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Catatan Pelanggaran</title>
<?php include 'layout/datatables_head.php'; ?>
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php include "layout/sidebar.php"; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <h2>Catatan Pelanggaran Siswa</h2>
    <p>Selamat datang, <b><?= aman_catatan($guru['nama']) ?></b>.

    <?php if ($wali): ?>
        Anda wali kelas <b><?= aman_catatan($wali['nama_kelas'] ?? '-') ?></b>,
        sehingga hanya melihat data dan pelanggaran dari kelas tersebut.
    <?php endif; ?>
    </p>

    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-info">
            <?= aman_catatan($_GET['pesan']) ?>
        </div>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Catatan Pelanggaran</h5>

        <form action="proses_tambah_pelanggaran.php" method="POST">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Siswa dan Kelas</label>
                    <select name="penempatan_id" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>

                        <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                            <option value="<?= (int)$s['penempatan_id'] ?>">
                                <?= aman_catatan($s['nama_siswa']) ?>
                                - <?= aman_catatan($s['nama_kelas']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Jenis Pelanggaran</label>
                    <select name="pelanggaran_id" class="form-control" required>
                        <option value="">-- Pilih Pelanggaran --</option>

                        <?php while ($p = mysqli_fetch_assoc($pelanggaran)): ?>
                            <option value="<?= (int)$p['id'] ?>">
                                <?= aman_catatan($p['nama']) ?>
                                (<?= (int)$p['poin'] ?> poin)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Tanggal</label>
                    <input type="date" name="tanggal" class="form-control"
                           value="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-8">
                    <label>Keterangan</label>
                    <textarea name="keterangan" class="form-control"
                              rows="3" required></textarea>
                </div>

                <div class="form-group col-md-4">
                    <label>&nbsp;</label><br>
                    <button type="submit" class="btn btn-primary">
                        Simpan Catatan
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- DAFTAR CATATAN -->
    <div class="table-card shadow-sm">
        <h5 class="mb-3">Daftar Catatan Pelanggaran</h5>

            <div class="table-responsive">
                <table id="tabelCatatan" class="table table-bordered table-striped table-hover table-sm align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Siswa</th>
                            <th>Kelas</th>
                            <th>Pelanggaran</th>
                            <th>Poin</th>
                            <th>Keterangan</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no = 1; ?>
                    <?php while ($d = mysqli_fetch_assoc($catatan)): ?>
                        <tr>
                                <td><?= $no++ ?></td>
                                <td><?= aman_catatan($d['tanggal']) ?></td>
                                <td><?= aman_catatan($d['nama_siswa']) ?></td>
                                <td><?= aman_catatan($d['nama_kelas']) ?></td>
                                <td><?= aman_catatan($d['nama_pelanggaran']) ?></td>
                                <td><?= (int)$d['poin'] ?></td>
                                <td><?= aman_catatan($d['keterangan']) ?></td>
                                <td><?= aman_catatan($d['status']) ?></td>
                                <td>
                                    <?php if ((int)$d['guru_id'] === $guru_id): ?>
                                        <a href="edit_pelanggaran.php?id=<?= (int)$d['id'] ?>"
                                           class="btn btn-warning btn-sm mb-1">Edit</a>

                                        <form action="hapus_pelanggaran.php" method="POST"
                                              onsubmit="return confirm('Yakin ingin menghapus catatan ini?')">
                                            <input type="hidden" name="id"
                                                   value="<?= (int)$d['id'] ?>">
                                            <button type="submit"
                                                    class="btn btn-danger btn-sm">Hapus</button>
                                        </form>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
    </div>

</main>

<?php $dt_id = 'tabelCatatan'; $dt_empty = 'Belum ada catatan pelanggaran'; include 'layout/datatables_bottom.php'; ?>
</body>
</html>