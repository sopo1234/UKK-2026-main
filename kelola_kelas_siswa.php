<?php

session_start();

include 'config/koneksi.php';

// CEK LOGIN
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// CEK ROLE
if ($_SESSION['role'] != 'guru') {
    echo "Akses ditolak!";
    exit;
}

// Halaman ini hanya untuk wali kelas aktif,
// dan hanya menampilkan penempatan siswa di kelas yang diampu.
include 'includes/wali_kelas.php';
$wali = cari_wali_kelas($koneksi, (int)($_SESSION['id_user'] ?? 0));

if (!$wali) {
    echo "Akses ditolak! Anda belum ditunjuk sebagai wali kelas.";
    exit;
}

$wali_kelas_id = (int)$wali['kelas_id'];

// AMBIL DATA (hanya kelas yang diampu)
$stmt = mysqli_prepare($koneksi, "
    SELECT *
    FROM t_kelas_siswa
    WHERE kelas_id = ?
    ORDER BY id DESC
");
mysqli_stmt_bind_param($stmt, "i", $wali_kelas_id);
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

if (!$hasil) {
    die("Gagal mengambil data kelas: " . mysqli_error($koneksi));
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <meta charset="UTF-8">
    <title>Kelola Kelas</title>
    <link rel="stylesheet" href="layout/layout.css">
</head>

<body>
<script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.1.3/dist/js/bootstrap.min.js" integrity="sha384-ChfqqxuZUCnJSK3+MXmPNIyE6ZbWh2IMqE241rYiqJxyMiZ6OW/JmZQ5stwEULTy" crossorigin="anonymous"></script>
<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">

<h1>Data Siswa Kelas <?= htmlspecialchars($wali['nama_kelas'] ?? '-', ENT_QUOTES, 'UTF-8'); ?></h1>

<a href="dashboard.php">
    Kembali ke Dashboard
</a>

<br><br>

<hr>

<h2>Daftar Penempatan Siswa</h2>

<table class="table table-sm table-dark">

    <tr>
        <th>No</th>
        <th>Siswa ID</th>
        <th>Tahun Ajaran ID</th>
        <th>Kelas ID</th>
        <th>Tanggal Mulai</th>
        <th>Tanggal Selesai</th>
        <th>Status</th>
        <th>Created At</th>
        <th>Updated At</th>
        <th>Aksi</th>
    </tr>

    <?php

    $no = 1;

    while ($kelas = mysqli_fetch_assoc($hasil)) {

    ?>

    <tr>

        <td>
            <?php echo $no++; ?>
        </td>

        <td>
            <?php echo $kelas['siswa_id']; ?>
        </td>

        <td>
            <?php echo $kelas['tahun_ajaran_id']; ?>
        </td>

        <td>
            <?php echo $kelas['kelas_id']; ?>
        </td>

        <td>
            <?php echo $kelas['tanggal_mulai']; ?>
        </td>

        <td>
            <?php echo $kelas['tanggal_selesai']; ?>
        </td>

        <td>

            <?php

            if ($kelas['status_aktif'] == 1) {
                echo "Aktif";
            } else {
                echo "Tidak Aktif";
            }

            ?>

        </td>

        <td>
            <?php echo $kelas['created_at']; ?>
        </td>

        <td>
            <?php echo $kelas['updated_at']; ?>
        </td>

        <td>

            <a href="edit_kelas.php?id=<?php echo $kelas['id']; ?>">
                <button type="button" class="btn btn-warning">Edit</button>
            </a>

            |

            <a
                href="hapus_kelas_siswa.php?id=<?php echo (int)$kelas['id']; ?>"
                onclick="return confirm('Yakin ingin menghapus penempatan siswa ini?')"
            >
                <button type="button" class="btn btn-danger">Hapus</button>
            </a>

        </td>

    </tr>

    <?php

    }

    ?>

</table>

</main>

</body>

</html>