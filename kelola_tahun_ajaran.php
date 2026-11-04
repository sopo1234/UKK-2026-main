<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['role'] != 'admin') {
    die("Akses ditolak!");
}

$sql = "SELECT * FROM t_tahun_ajaran ORDER BY id DESC";
$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Tahun Ajaran</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">

    <link rel="stylesheet" href="layout/layout.css">
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Kelola Tahun Ajaran</h1>

            <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>
    <!-- /page-header -->

    <!-- FORM TAMBAH TAHUN AJARAN (dipindah dari tambah_tahun_ajaran.php) -->
    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Tahun Ajaran Baru</h5>

        <form action="proses_tambah_tahun_ajaran.php" method="POST">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Nama Tahun Ajaran</label>
                    <input type="text" name="nama" class="form-control"
                           required placeholder="Contoh: 2026/2027">
                </div>

                <div class="form-group col-md-3">
                    <label>Tanggal Mulai</label>
                    <input type="date" name="tanggal_mulai" class="form-control" required>
                </div>

                <div class="form-group col-md-3">
                    <label>Tanggal Selesai</label>
                    <input type="date" name="tanggal_selesai" class="form-control" required>
                </div>

                <div class="form-group col-md-2">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="simpan" class="btn btn-primary">
                Simpan Tahun Ajaran
            </button>
        </form>
    </div>

            <div class="table-card shadow-sm">

            <table id="tabelTahunAjaran" class="table table-striped table-bordered table-hover table-sm">
                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Tahun Ajaran</th>
                        <th>Tanggal Mulai</th>
                        <th>Tanggal Selesai</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                    <?php
                    $no = 1;
                    while ($data = mysqli_fetch_assoc($hasil)) {
                    ?>
                    <tr>
                        <td><?php echo $no++; ?></td>
                        <td><?php echo htmlspecialchars($data['nama'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($data['tanggal_mulai'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($data['tanggal_selesai'] ?? '-'); ?></td>
                        <td>
                            <?php if ((int)($data['status_aktif'] ?? 0) === 1) { ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php } else { ?>
                                <span class="badge badge-secondary">Tidak Aktif</span>
                            <?php } ?>
                        </td>
                        <td><?php echo htmlspecialchars($data['created_at'] ?? '-'); ?></td>
                        <td><?php echo htmlspecialchars($data['updated_at'] ?? '-'); ?></td>
                        <td>
                            <a href="edit_tahun_ajaran.php?id=<?php echo (int)$data['id']; ?>"
                               class="btn btn-warning btn-sm">Edit</a>

                            <a href="hapus_tahun_ajaran.php?id=<?php echo (int)$data['id']; ?>"
                               class="btn btn-danger btn-sm"
                               onclick="return confirm('Yakin ingin menghapus tahun ajaran ini?')">
                                Hapus
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
            </div>
            <!-- /table-card -->

        </main>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelTahunAjaran').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data tahun ajaran",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
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