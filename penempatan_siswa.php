<?php
include 'includes/cek_session.php';
include 'config/koneksi.php';

// Hanya admin yang boleh akses
if (($_SESSION['role'] ?? '') != 'admin') {
    header("Location: dashboard.php");
    exit;
}

function h($value) {
    return htmlspecialchars((string)($value ?? '-'), ENT_QUOTES, 'UTF-8');
}

// Ambil tahun ajaran aktif (untuk dropdown)
$tahun_result = mysqli_query($koneksi, "
    SELECT id, nama
    FROM t_tahun_ajaran
    WHERE status_aktif = 1
    ORDER BY id DESC
");
$tahun_list = [];
while ($ta = mysqli_fetch_assoc($tahun_result)) {
    $tahun_list[] = $ta;
}

// Ambil SEMUA siswa aktif (validasi duplikat ditangani di backend)
$siswa_result = mysqli_query($koneksi, "
    SELECT id, nis, nisn, nama
    FROM t_siswa
    WHERE status_aktif = 1
    ORDER BY nama ASC
");
$siswa_list = [];
while ($s = mysqli_fetch_assoc($siswa_result)) {
    $siswa_list[] = $s;
}

// Ambil kelas aktif
$kelas_result = mysqli_query($koneksi, "
    SELECT id, nama, tingkat, jurusan
    FROM t_kelas
    WHERE status_aktif = 1
    ORDER BY nama ASC
");
$kelas_list = [];
while ($k = mysqli_fetch_assoc($kelas_result)) {
    $kelas_list[] = $k;
}

// Ambil data penempatan siswa (semua, tidak difilter)
$hasil = mysqli_query($koneksi, "
    SELECT
        ks.id,
        s.nisn,
        s.nama AS nama_siswa,
        ta.nama AS tahun_ajaran,
        k.nama AS nama_kelas,
        k.tingkat,
        k.jurusan,
        ks.tanggal_mulai,
        ks.tanggal_selesai,
        ks.status_aktif
    FROM t_kelas_siswa ks
    LEFT JOIN t_siswa s ON ks.siswa_id = s.id
    LEFT JOIN t_tahun_ajaran ta ON ks.tahun_ajaran_id = ta.id
    LEFT JOIN t_kelas k ON ks.kelas_id = k.id
    ORDER BY ks.id DESC
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Penempatan Siswa</title>
    <?php include 'layout/datatables_head.php'; ?>
</head>
<body>

<!-- SIDEBAR BERSAMA -->
<?php include "layout/sidebar.php"; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Penempatan Siswa</h1>
        <p>Kelola penempatan siswa berdasarkan kelas dan tahun ajaran.</p>
    </div>

    <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div class="alert alert-success">
            <?= h($_SESSION['pesan_sukses']); ?>
        </div>
        <?php unset($_SESSION['pesan_sukses']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['pesan_error'])): ?>
        <div class="alert alert-danger">
            <?= h($_SESSION['pesan_error']); ?>
        </div>
        <?php unset($_SESSION['pesan_error']); ?>
    <?php endif; ?>

    <!-- FORM TAMBAH -->
    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Penempatan Siswa</h5>

        <form action="proses_simpan_penempatan.php" method="POST">

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Siswa</label>
                    <select name="siswa_id" class="form-control" required>
                        <option value="">-- Pilih Siswa --</option>
                        <?php foreach ($siswa_list as $s): ?>
                            <option value="<?= h($s['id']); ?>">
                                <?= h($s['nis'] . ' - ' . $s['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Tahun Ajaran</label>
                    <select name="tahun_ajaran_id" class="form-control" required>
                        <option value="">-- Pilih Tahun Ajaran --</option>
                        <?php foreach ($tahun_list as $ta): ?>
                            <option value="<?= h($ta['id']); ?>">
                                <?= h($ta['nama']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>Kelas</label>
                    <select name="kelas_id" class="form-control" required>
                        <option value="">-- Pilih Kelas --</option>
                        <?php foreach ($kelas_list as $k): ?>
                            <option value="<?= h($k['id']); ?>">
                                <?= h($k['nama'] . ' - Tingkat ' . $k['tingkat'] . ' - ' . $k['jurusan']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" required>
                </div>

                <div class="form-group col-md-4">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" required>
                </div>

                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Simpan Penempatan</button>
        </form>
    </div>

    <!-- DAFTAR PENEMPATAN -->
    <div class="table-card shadow-sm">
        <h5 class="mb-3">Data Penempatan Siswa</h5>

        <div class="table-responsive">
            <table id="tabelPenempatan" class="table table-bordered table-striped table-hover table-sm align-middle">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>NISN</th>
                        <th>Nama Siswa</th>
                        <th>Tahun Ajaran</th>
                        <th>Kelas</th>
                        <th>Tingkat</th>
                        <th>Jurusan</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; ?>
                    <?php while ($row = mysqli_fetch_assoc($hasil)): ?>
                        <tr>
                            <td><?= $no++; ?></td>
                            <td><?= h($row['nisn']); ?></td>
                            <td><?= h($row['nama_siswa']); ?></td>
                            <td><?= h($row['tahun_ajaran']); ?></td>
                            <td><?= h($row['nama_kelas']); ?></td>
                            <td><?= h($row['tingkat']); ?></td>
                            <td><?= h($row['jurusan']); ?></td>
                            <td><?= h($row['tanggal_mulai']); ?></td>
                            <td><?= h($row['tanggal_selesai']); ?></td>
                            <td>
                                <span class="badge badge-<?= (int)$row['status_aktif'] === 1 ? 'success' : 'secondary' ?>">
                                    <?= (int)$row['status_aktif'] === 1 ? 'Aktif' : 'Tidak Aktif' ?>
                                </span>
                            </td>
                            <td>
                                <a href="edit_penempatan.php?id=<?= (int)$row['id'] ?>" class="btn btn-warning btn-sm mb-1">Edit</a>
                                <form action="hapus_penempatan.php" method="POST" onsubmit="return confirm('Yakin ingin menghapus penempatan ini?')">
                                    <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                    <?php if ($no === 1): ?>
                        <tr><td colspan="11" class="text-center text-muted">Belum ada data penempatan.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<?php include 'layout/datatables_bottom.php'; ?>
<script>
    $(document).ready(function () {
        $('#tabelPenempatan').DataTable({
            language: {
                search: "Cari:",
                lengthMenu: "Tampilkan _MENU_ data",
                info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
                infoEmpty: "Tidak ada data",
                zeroRecords: "Data tidak ditemukan",
                emptyTable: "Belum ada data penempatan siswa",
                paginate: {
                    first: "Pertama",
                    last: "Terakhir",
                    next: "Berikutnya",
                    previous: "Sebelumnya"
                }
            },
            pageLength: 10,
            lengthMenu: [5, 10, 25, 50, 100],
            order: []
        });
    });
</script>

</body>
</html>