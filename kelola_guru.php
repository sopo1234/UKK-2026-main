
<?php
session_start();

include 'config/koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// CEK ROLE
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'admin') {
    echo "Akses ditolak!";
    exit;
}

// AMBIL DATA GURU
$sql = "SELECT
            g.id,
            g.nip,
            g.nama,
            g.email,
            g.status_aktif,
            g.created_at,
            g.updated_at
        FROM t_guru g
        JOIN t_users u ON g.user_id = u.id
        WHERE u.role = 'guru'
        ORDER BY g.id DESC";

$hasil = mysqli_query($koneksi, $sql);

if (!$hasil) {
    die("Gagal mengambil data guru: " . mysqli_error($koneksi));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Kelola Guru</title>

    <!-- Bootstrap -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css">

    <!-- CSS DataTables -->
    <link rel="stylesheet"
          href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap4.min.css">

    <link rel="stylesheet" href="layout/layout.css">

    <style>
        #tabelGuru {
            width: 100% !important;
            white-space: nowrap;
        }

        #tabelGuru th,
        #tabelGuru td {
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
        <h1>Kelola Guru</h1>

        <a href="dashboard.php">Kembali ke Dashboard</a>
    </div>

    <!-- FORM TAMBAH GURU (dipindah dari tambah_guru.php) -->
    <div class="form-card form-card-full shadow-sm mb-4">
        <h5 class="mb-3">Tambah Guru Baru</h5>

        <form action="proses_tambah_guru.php" method="POST">

            <div class="form-row">
                <div class="form-group col-md-3">
                    <label>NIP</label>
                    <input type="text" name="nip" class="form-control"
                           maxlength="18" inputmode="numeric" pattern="[0-9]*" required>
                </div>

                <div class="form-group col-md-5">
                    <label>Nama</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>

                <div class="form-group col-md-4">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group col-md-4">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>

                <div class="form-group col-md-4">
                    <label>Status</label>
                    <select name="status_aktif" class="form-control" required>
                        <option value="1">Aktif</option>
                        <option value="0">Tidak Aktif</option>
                    </select>
                </div>

                <div class="form-group col-md-4">
                    <label>&nbsp;</label><br>
                    <button type="submit" name="simpan" class="btn btn-primary">
                        Simpan Guru
                    </button>
                </div>
            </div>

        </form>
    </div>

            <h2>Data Guru</h2>

            <div class="table-card shadow-sm">

                <div class="table-responsive">

                <table id="tabelGuru"
                       class="table table-striped table-bordered table-hover table-sm">

                    <thead class="thead-dark">
                        <tr>
                            <th>No</th>
                            <th>NIP</th>
                            <th>Nama</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>Created At</th>
                            <th>Updated At</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>

                    <?php
                    $no = 1;

                    while ($guru = mysqli_fetch_assoc($hasil)) {
                    ?>

                        <tr>

                            <td><?= $no++; ?></td>

                            <td>
                                <?= htmlspecialchars($guru['nip'] ?? ''); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($guru['nama'] ?? ''); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($guru['email'] ?? ''); ?>
                            </td>

                            <td>
                                <?php if ((int)($guru['status_aktif'] ?? 0) === 1) { ?>
                                    <span class="badge badge-success">Aktif</span>
                                <?php } else { ?>
                                    <span class="badge badge-secondary">Tidak Aktif</span>
                                <?php } ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($guru['created_at'] ?? ''); ?>
                            </td>

                            <td>
                                <?= htmlspecialchars($guru['updated_at'] ?? ''); ?>
                            </td>

                            <td>
                                <a href="edit_guru.php?id=<?= (int)$guru['id']; ?>"
                                   class="btn btn-warning btn-sm">
                                    Edit
                                </a>

                                <a href="hapus_guru.php?id=<?= (int)$guru['id']; ?>"
                                   class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin ingin menghapus guru ini?')">
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

<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>

<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap4.min.js"></script>

<script>
$(document).ready(function () {

    $('#tabelGuru').DataTable({
        pageLength: 10,
        lengthMenu: [5, 10, 25, 50, 100],
        order: [],
        language: {
            search: "Cari:",
            lengthMenu: "Tampilkan _MENU_ data",
            info: "Menampilkan _START_ sampai _END_ dari _TOTAL_ data",
            infoEmpty: "Tidak ada data guru",
            zeroRecords: "Data guru tidak ditemukan",
            emptyTable: "Belum ada data guru",
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