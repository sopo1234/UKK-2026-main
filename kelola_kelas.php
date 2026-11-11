<?php
session_start();
include 'config/koneksi.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$role = strtolower($_SESSION['role'] ?? '');

if ($role !== 'admin') 
    exit("Akses ditolak!");

// AMBIL DATA KELAS (semua kelas yang pernah dibuat)
$sql = "SELECT
            id,
            nama,
            tingkat,
            jurusan,
            status_aktif,
            created_at
        FROM t_kelas
        ORDER BY id ASC";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Kelas</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="layout/layout.css">

    <style>
        #tabelKelas {
            width: 100% !important;
            white-space: nowrap;
        }

        #tabelKelas th,
        #tabelKelas td {
            vertical-align: middle;
        }
    </style>
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Kelola Kelas</h1>

        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>

    <!-- PESAN SUKSES -->
    <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div class="alert alert-success">
            <?= htmlspecialchars($_SESSION['pesan_sukses']); ?>
        </div>
        <?php unset($_SESSION['pesan_sukses']); ?>
    <?php endif; ?>

    <!-- PESAN ERROR -->
    <?php if (isset($_SESSION['pesan_error'])): ?>
        <div class="alert alert-danger">
            <?= htmlspecialchars($_SESSION['pesan_error']); ?>
        </div>
        <?php unset($_SESSION['pesan_error']); ?>
    <?php endif; ?>

    <!-- FORM 1: TAMBAH KELAS BARU (dipindah dari tambah_kelas.php) -->
    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Kelas Baru</h5>

        <form action="tambah_kelas.php" method="POST">
            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Nama Kelas</label>
                    <input type="text" name="nama" class="form-control"
                           required placeholder="Contoh: X RPL 1">
                </div>

                <div class="form-group col-md-2">
                    <label>Tingkat</label>
                    <input type="text" name="tingkat" class="form-control"
                           required placeholder="X / XI / XII">
                </div>

                <div class="form-group col-md-3">
                    <label>Jurusan</label>
                    <input type="text" name="jurusan" class="form-control"
                           required placeholder="Contoh: RPL">
                </div>

                <div class="form-group col-md-3">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>
            </div>

            <button type="submit" name="simpan" class="btn btn-primary">
                Simpan Kelas
            </button>
        </form>
    </div>

            <div class="table-card shadow-sm">

                <div class="table-responsive">

                    <table id="tabelKelas"
                       class="table table-striped table-bordered table-hover table-sm">

                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Kelas</th>
                            <th>Tingkat</th>
                            <th>Jurusan</th>
                            <th>Status</th>
                            <th>Dibuat</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $no = 1;

                    while ($data = mysqli_fetch_assoc($hasil)) {
                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($data['nama'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['tingkat'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['jurusan'] ?? '-'); ?>
                            </td>

                            <td>
                                <?php if ((int)($data['status_aktif'] ?? 0) === 1) { ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php } else { ?>
                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['created_at'] ?? '-'); ?>
                            </td>

                        </tr>

                    <?php
                    }
                    ?>

                    </tbody>

                </table>

                </div>
            </div>

        </main>

<!-- JAVASCRIPT -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelKelas').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data kelas",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada kelas yang dibuat",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        }
    });

});
</script>

</body>
</html>