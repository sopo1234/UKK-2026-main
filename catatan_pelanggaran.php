<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

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

// Ambil siswa yang mempunyai penempatan kelas aktif
$siswa = mysqli_query($koneksi, "
    SELECT ks.id AS penempatan_id, s.nama AS nama_siswa,
           k.nama AS nama_kelas
    FROM t_kelas_siswa ks
    JOIN t_siswa s ON s.id = ks.siswa_id
    JOIN t_kelas k ON k.id = ks.kelas_id
    WHERE ks.status_aktif = 1 AND s.status_aktif = 1
    ORDER BY s.nama
");

// Ambil daftar kelas yang memiliki siswa aktif (untuk pilihan kelas)
$kelas_list = mysqli_query($koneksi, "
    SELECT DISTINCT k.id, k.nama
    FROM t_kelas k
    JOIN t_kelas_siswa ks ON ks.kelas_id = k.id
    WHERE ks.status_aktif = 1
    ORDER BY k.nama
");

// Ambil jenis pelanggaran aktif
$pelanggaran = mysqli_query($koneksi, "
    SELECT id, nama, poin
    FROM t_pelanggaran
    WHERE status_aktif = 1
    ORDER BY nama
");

// Ambil seluruh catatan dari semua guru (semua user hanya dapat melihat)
$stmt = mysqli_prepare($koneksi, "
    SELECT ps.id, ps.tanggal, ps.nama_siswa, ps.nama_kelas,
           COALESCE(NULLIF(p.nama, ''), NULLIF(ps.nama_pelanggaran, '0'), '') AS nama_pelanggaran,
           ps.poin, ps.keterangan, ps.status, ps.nama_guru, s.nis
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON s.id = ps.siswa_id
    LEFT JOIN t_pelanggaran p ON p.id = ps.pelanggaran_id
    ORDER BY ps.tanggal DESC, ps.id DESC
");
mysqli_stmt_execute($stmt);
$catatan = mysqli_stmt_get_result($stmt);

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
    <p>Selamat datang, <b><?= aman_catatan($guru['nama']) ?></b>. Tabel di bawah menampilkan seluruh catatan dari semua guru (hanya lihat).</p>

    <?php if (isset($_GET['pesan'])): ?>
        <div class="alert alert-info">
            <?= aman_catatan($_GET['pesan']) ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div class="alert alert-success">
            <?= aman_catatan($_SESSION['pesan_sukses']) ?>
        </div>
        <?php unset($_SESSION['pesan_sukses']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['pesan_error'])): ?>
        <div class="alert alert-danger">
            <?= aman_catatan($_SESSION['pesan_error']) ?>
        </div>
        <?php unset($_SESSION['pesan_error']); ?>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Catatan Pelanggaran</h5>

        <form action="proses_tambah_pelanggaran.php" method="POST">

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Kelas</label>
                    <select id="pilihKelas" class="form-control">
                        <option value="">-- Semua Kelas --</option>

                        <?php while ($k = mysqli_fetch_assoc($kelas_list)): ?>
                            <option value="<?= aman_catatan($k['nama']) ?>">
                                <?= aman_catatan($k['nama']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group col-md-2">
                    <label>Siswa</label>
                    <select name="penempatan_id" id="pilihSiswa" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>

                        <?php while ($s = mysqli_fetch_assoc($siswa)): ?>
                            <option value="<?= (int)$s['penempatan_id'] ?>"
                                    data-kelas="<?= aman_catatan($s['nama_kelas']) ?>">
                                <?= aman_catatan($s['nama_siswa']) ?>
                                - <?= aman_catatan($s['nama_kelas']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group col-md-3">
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
                            <th>Dicatat oleh</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no = 1; ?>
                    <?php while ($d = mysqli_fetch_assoc($catatan)): ?>
                        <tr>
                                <td><?= $no++ ?></td>
                                <td><?= aman_catatan($d['tanggal']) ?></td>
                                <td>
                                    <?= aman_catatan($d['nama_siswa']) ?>
                                    <div class="small text-muted"><?= aman_catatan($d['nis']) ?></div>
                                </td>
                                <td><?= aman_catatan($d['nama_kelas']) ?></td>
                                <td><?= aman_catatan($d['nama_pelanggaran']) ?></td>
                                <td><?= (int)$d['poin'] ?></td>
                                <td><?= aman_catatan($d['keterangan']) ?></td>
                                <td><?= ucfirst(aman_catatan($d['status'])) ?></td>
                                <td><?= aman_catatan($d['nama_guru']) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
    </div>

</main>

<?php $dt_id = 'tabelCatatan'; $dt_empty = 'Belum ada catatan pelanggaran'; include 'layout/datatables_bottom.php'; ?>

<script>
// Filter daftar siswa sesuai kelas yang dipilih
document.getElementById('pilihKelas').addEventListener('change', function () {
    var kelasDipilih = this.value;
    var selectSiswa = document.getElementById('pilihSiswa');

    // Reset pilihan siswa setiap kali kelas diganti
    selectSiswa.value = '';

    selectSiswa.querySelectorAll('option[data-kelas]').forEach(function (opt) {
        var cocok = (kelasDipilih === '' || opt.dataset.kelas === kelasDipilih);
        opt.style.display = cocok ? '' : 'none';
    });
});
</script>
</body>
</html>