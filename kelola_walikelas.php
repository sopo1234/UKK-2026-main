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

$query = "SELECT wk.*,
                 g.nama AS nama_guru,
                 k.nama AS nama_kelas,
                 k.tingkat,
                 k.jurusan,
                 ta.nama AS tahun_ajaran
          FROM t_wali_kelas wk
          LEFT JOIN t_guru g ON wk.guru_id = g.id
          LEFT JOIN t_kelas k ON wk.kelas_id = k.id
          LEFT JOIN t_tahun_ajaran ta
              ON wk.tahun_ajaran_id = ta.id
          ORDER BY wk.id DESC";

$data = mysqli_query($koneksi, $query);

if (!$data) {
    die("Gagal mengambil data: " . mysqli_error($koneksi));
}

// Data dropdown untuk form tambah (dipindah dari tambah_walikelas.php)
$q_guru  = mysqli_query($koneksi, "SELECT id, nama FROM t_guru ORDER BY nama");
$q_kelas  = mysqli_query($koneksi, "SELECT id, nama, tingkat, jurusan FROM t_kelas ORDER BY nama");
$q_ta  = mysqli_query($koneksi, "SELECT id, nama, status_aktif FROM t_tahun_ajaran ORDER BY status_aktif DESC, nama DESC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Wali Kelas</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <!-- DataTables -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="layout/layout.css">

    <style>
        .table-responsive {
            margin-top: 20px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

            <h2>Data Wali Kelas</h2>

            <a href="dashboard.php">Kembali ke Dashboard</a>

            <hr>

            <!-- FORM TAMBAH WALI KELAS (dipindah dari tambah_walikelas.php) -->
            <div class="form-card form-card-full shadow-sm mb-4">
                <h5 class="mb-3">Tambah Wali Kelas Baru</h5>

                <form action="proses_tambah_walikelas.php" method="POST">
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label>Guru</label>
                            <select name="guru_id" class="form-control" required>
                                <option value="">-- Pilih Guru --</option>
                                <?php while ($g = mysqli_fetch_assoc($q_guru)) { ?>
                                    <option value="<?= (int)$g['id']; ?>">
                                        <?= htmlspecialchars($g['nama']); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="form-group col-md-4">
                            <label>Kelas</label>
                            <select name="kelas_id" class="form-control" required>
                                <option value="">-- Pilih Kelas --</option>
                                <?php while ($k = mysqli_fetch_assoc($q_kelas)) { ?>
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
                                <?php while ($ta = mysqli_fetch_assoc($q_ta)) { ?>
                                    <option value="<?= (int)$ta['id']; ?>"
                                        <?= (int)$ta['status_aktif'] === 1 ? 'selected' : ''; ?>>
                                        <?= htmlspecialchars($ta['nama']); ?>
                                    </option>
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
                                Simpan Wali Kelas
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="table-card shadow-sm">

            <div class="table-responsive">
                <table id="tabelWaliKelas"
                       class="table table-striped table-bordered table-hover table-sm"
                       style="width:100%">

                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>Guru</th>
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

                    while ($row = mysqli_fetch_assoc($data)) {
                    ?>
                        <tr>
                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nama_guru'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['nama_kelas'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                                (<?= htmlspecialchars(
                                    $row['tingkat'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                                <?= htmlspecialchars(
                                    $row['jurusan'] ?? '',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>)
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['tahun_ajaran'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['tanggal_mulai'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars(
                                    $row['tanggal_selesai'] ?? '-',
                                    ENT_QUOTES,
                                    'UTF-8'
                                ); ?>
                            </td>

                            <td>
                                <?= ($row['status_aktif'] ?? 0) == 1
                                    ? 'Aktif'
                                    : 'Tidak Aktif'; ?>
                            </td>

                            <td>
                                <a class="btn btn-warning btn-sm"
                                   href="edit_walikelas.php?id=<?= (int)$row['id']; ?>">
                                    Edit
                                </a>

                                <a class="btn btn-danger btn-sm"
                                   href="hapus_walikelas.php?id=<?= (int)$row['id']; ?>"
                                   onclick="return confirm('Yakin ingin menghapus data ini?')">
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

<!-- JavaScript DataTables -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {
    $('#tabelWaliKelas').DataTable({
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data",
            zeroRecords: "Data tidak ditemukan",
            emptyTable: "Belum ada data wali kelas",
            paginate: {
                first: "Pertama",
                last: "Terakhir",
                next: "Selanjutnya",
                previous: "Sebelumnya"
            }
        },
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        ordering: true,
        order: []
    });
});
</script>

</body>
</html>