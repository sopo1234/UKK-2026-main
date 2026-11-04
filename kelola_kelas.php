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

// AMBIL DATA KELAS SISWA
$sql = "SELECT
            ks.*,
            s.nama AS nama_siswa,
            k.nama AS nama_kelas,
            k.tingkat,
            k.jurusan,
            ta.nama AS tahun_ajaran
        FROM t_kelas_siswa ks
        LEFT JOIN t_siswa s ON ks.siswa_id = s.id
        LEFT JOIN t_kelas k ON ks.kelas_id = k.id
        LEFT JOIN t_tahun_ajaran ta
            ON ks.tahun_ajaran_id = ta.id
        ORDER BY ks.id DESC";

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

    <title>Kelola Kelas Siswa</title>

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
        <h1>Kelola Kelas Siswa</h1>

        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>

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
                    <select name="jurusan" class="form-control" required>
                        <option value="">-- Pilih Jurusan --</option>
                        <option value="RPL">RPL</option>
                        <option value="TKJ">TKJ</option>
                        <option value="BD">BD</option>
                        <option value="TKR">TKR</option>
                        <option value="TSM">TSM</option>
                    </select>
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

    <!-- FORM 1: TAMBAH KELAS BARU (dipindah dari tambah_kelas.php) -->

                <div class="table-responsive">

                    <table id="tabelKelas"
                       class="table table-striped table-bordered table-hover table-sm">

                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Nama Siswa</th>
                            <th>Kelas</th>
                            <th>Tahun Ajaran</th>
                            <th>Tanggal Mulai</th>
                            <th>Tanggal Selesai</th>
                            <th>Status</th>
                            <th>Aksi</th>
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
                                <?= htmlspecialchars($data['nama_siswa'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    ($data['nama_kelas'] ?? '-') . ' - ' .
                                    ($data['tingkat'] ?? '-') . ' ' .
                                    ($data['jurusan'] ?? '')
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['tahun_ajaran'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['tanggal_mulai'] ?? '-'); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($data['tanggal_selesai'] ?? '-'); ?>
                            </td>

                            <td>
                                <?php if ((int)($data['status_aktif'] ?? 0) === 1) { ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php } else { ?>
                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                <?php } ?>
                            </td>

                            <td>
                                <a href="edit_kelas.php?id=<?= (int)$data['id']; ?>"
                                   class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <a href="hapus_kelas_siswa.php?id=<?= (int)$data['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus penempatan siswa ini?')">
                                    Hapus
                                </a>
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
            infoEmpty: "Tidak ada data kelas siswa",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data kelas siswa",
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