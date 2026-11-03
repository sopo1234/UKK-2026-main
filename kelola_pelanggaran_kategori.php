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

$query = mysqli_query(
    $koneksi,
    "SELECT * FROM t_pelanggaran_kategori ORDER BY id DESC"
);

if (!$query) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori Pelanggaran</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <!-- CSS DataTables -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="layout/layout.css">
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

            <h2>Data Kategori Pelanggaran</h2>

            <a href="dashboard.php">Kembali ke Dashboard</a>

            <hr>

            <!-- FORM TAMBAH KATEGORI (dipindah dari tambah_pelanggaran_kategori.php) -->
            <div class="form-card form-card-full shadow-sm mb-4">
                <h5 class="mb-3">Tambah Kategori Pelanggaran Baru</h5>

                <!-- Aksi ke file proses asli (nama file memakai underscore di akhir) -->
                <form action="proses_tambah_pelanggaran_kategori_.php" method="POST">

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label>Nama Kategori</label>
                            <input type="text" name="nama" class="form-control"
                                   maxlength="100" required>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Status</label>
                            <select name="status_aktif" class="form-control" required>
                                <option value="1">Aktif</option>
                                <option value="0">Tidak Aktif</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group col-md-8">
                            <label>Deskripsi</label>
                            <textarea name="deskripsi" rows="3" class="form-control" required></textarea>
                        </div>

                        <div class="form-group col-md-4">
                            <label>&nbsp;</label><br>
                            <button type="submit" name="simpan" class="btn btn-primary">
                                Simpan Kategori
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- TABEL DATATABLES -->
            <div class="table-card shadow-sm">

            <div class="table-responsive">
                <table id="tabelKategori"
                       class="table table-striped table-bordered table-hover table-sm">

                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php
                        $no = 1;

                        while ($data = mysqli_fetch_assoc($query)) {
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>

                                <td>
                                    <?= htmlspecialchars(
                                        $data['nama'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?= htmlspecialchars(
                                        $data['deskripsi'] ?? '',
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ); ?>
                                </td>

                                <td>
                                    <?= $data['status_aktif'] == 1
                                        ? 'Aktif'
                                        : 'Tidak Aktif'; ?>
                                </td>

                                <td>
                                    <a class="btn btn-warning btn-sm"
                                       href="edit_pelanggaran_kategori.php?id=<?= (int)$data['id']; ?>">
                                        Edit
                                    </a>

                                    <a class="btn btn-danger btn-sm"
                                       href="hapus_pelanggaran_kategori.php?id=<?= (int)$data['id']; ?>"
                                       onclick="return confirm('Yakin ingin menghapus kategori ini?')">
                                        Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>

                </table>
                </div>
            </div>
            <!-- /table-card -->

        </main>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelKategori').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data kategori",
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
```