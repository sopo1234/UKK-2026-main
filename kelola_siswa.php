<?php
session_start();
require_once __DIR__ . '/config/koneksi.php';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if (($_SESSION['role'] ?? '') !== 'admin') {
    exit("Akses ditolak!");
}

// Menu aktif
$activeMenu = 'siswa';

// Ambil data siswa
$sql = "SELECT * FROM t_siswa ORDER BY id DESC";
$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die(
        "Gagal mengambil data siswa: " .
        htmlspecialchars(mysqli_error($koneksi), ENT_QUOTES, 'UTF-8')
    );
}

// Mengamankan output
function h($nilai) {
    return htmlspecialchars(
        (string)($nilai ?? '-'),
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Siswa</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <!-- DataTables -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="layout/layout.css">

    <style>
        #tabelSiswa {
            width: 100% !important;
            white-space: nowrap;
        }

        #tabelSiswa th,
        #tabelSiswa td {
            vertical-align: middle;
        }

        .table-card {
            width: 100%;
        }

        .table-responsive {
            width: 100%;
            overflow-x: auto;
        }

        .aksi {
            display: flex;
            gap: 5px;
        }
    </style>
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php require_once __DIR__ . '/layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

    <div class="page-header">
        <h1>Kelola Siswa</h1>

        <a href="dashboard.php">Kembali ke Dashboard</a>

        <a href="data_siswa.php" class="btn btn-secondary">
            Lihat Data Siswa
        </a>
    </div>

    <!-- FORM TAMBAH SISWA (dipindah dari tambah_siswa.php) -->
    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Siswa Baru</h5>

        <form action="proses_tambah_siswa.php" method="POST">

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>NIS</label>
                    <input type="text" name="nis" class="form-control"
                           maxlength="15" inputmode="numeric" pattern="[0-9]*">
                </div>

                <div class="form-group col-md-3">
                    <label>NISN</label>
                    <input type="text" name="nisn" class="form-control"
                           maxlength="10" inputmode="numeric" pattern="[0-9]*" required>
                </div>

                <div class="form-group col-md-6">
                    <label>Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="form-control" required>
                        <option value="">-- Pilih --</option>
                        <option value="L">Laki-laki</option>
                        <option value="P">Perempuan</option>
                    </select>
                </div>

                <div class="form-group col-md-3">
                    <label>Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" class="form-control" required>
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
                        Simpan Siswa
                    </button>
                </div>
            </div>

            <div class="form-group">
                <label>Alamat</label>
                <textarea name="alamat" rows="2" class="form-control" required></textarea>
            </div>

        </form>
    </div>

    <div class="table-card shadow-sm">

        <h5 class="mb-3">Daftar Data Siswa</h5>

        <div class="table-responsive">
            <table id="tabelSiswa"
                   class="table table-striped table-bordered table-hover table-sm">

                <thead class="thead-dark">
                    <tr>
                        <th>No</th>
                        <th>NIS</th>
                        <th>NISN</th>
                        <th>Nama</th>
                        <th>Jenis Kelamin</th>
                        <th>Tanggal Lahir</th>
                        <th>Alamat</th>
                        <th>Status</th>
                        <th>Created At</th>
                        <th>Updated At</th>
                        <th>Aksi</th>
                    </tr>
                </thead>

                <tbody>
                <?php
                $no = 1;

                while ($siswa = mysqli_fetch_assoc($hasil)) {
                ?>
                    <tr>
                        <td><?= $no++; ?></td>

                        <td><?= h($siswa['nis'] ?? '-'); ?></td>

                        <td><?= h($siswa['nisn'] ?? '-'); ?></td>

                        <td><?= h($siswa['nama'] ?? '-'); ?></td>

                        <td>
                            <?php
                            $jk = $siswa['jenis_kelamin'] ?? '';

                            if ($jk === 'L') {
                                echo 'Laki-laki';
                            } elseif ($jk === 'P') {
                                echo 'Perempuan';
                            } else {
                                echo '-';
                            }
                            ?>
                        </td>

                        <td><?= h($siswa['tanggal_lahir'] ?? '-'); ?></td>

                        <td><?= h($siswa['alamat'] ?? '-'); ?></td>

                        <td>
                            <?php if ((int)($siswa['status_aktif'] ?? 0) === 1) { ?>
                                <span class="badge badge-success">Aktif</span>
                            <?php } else { ?>
                                <span class="badge badge-secondary">Tidak Aktif</span>
                            <?php } ?>
                        </td>

                        <td><?= h($siswa['created_at'] ?? '-'); ?></td>

                        <!-- Diperbaiki: tidak memunculkan warning jika kolom tidak tersedia -->
                        <td><?= h($siswa['updated_at'] ?? '-'); ?></td>

                        <td>
                            <div class="aksi">
                                <a href="edit_siswa.php?id=<?= (int)$siswa['id']; ?>"
                                   class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <a href="hapus_siswa.php?id=<?= (int)$siswa['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus siswa ini?')">
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php } ?>
                </tbody>

            </table>
        </div>
    </div>

</main>

<!-- JavaScript -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<!-- NIS dan NISN hanya boleh angka -->
<script src="layout/numeric_only.js"></script>

<script>
$(document).ready(function () {
    $('#tabelSiswa').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data siswa",
            zeroRecords: "Data siswa tidak ditemukan",
            emptyTable: "Belum ada data siswa",
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
