<?php
session_start();
include 'config/koneksi.php';

// Cek apakah user sudah login dan memiliki role guru
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'guru') {
    header("Location: login.php");
    exit;
}

// Proses form saat disubmit
if (isset($_POST['simpan'])) {
    $pelanggaran_kategori_id = mysqli_real_escape_string($koneksi, $_POST['pelanggaran_kategori_id']);
    $kode                    = mysqli_real_escape_string($koneksi, $_POST['kode']);
    $nama                    = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $poin                    = mysqli_real_escape_string($koneksi, $_POST['poin']);
    $deskripsi               = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);
    $status_aktif            = mysqli_real_escape_string($koneksi, $_POST['status_aktif']);

    // Query INSERT ke tabel t_pelanggaran
    $query = "INSERT INTO t_pelanggaran (pelanggaran_kategori_id, kode, nama, poin, deskripsi, status_aktif) 
              VALUES ('$pelanggaran_kategori_id', '$kode', '$nama', '$poin', '$deskripsi', '$status_aktif')";
    
    if (mysqli_query($koneksi, $query)) {
        header("Location: riwayat.php");
        exit;
    } else {
        $error = "Gagal menyimpan data: " . mysqli_error($koneksi);
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Pelanggaran - UKK 2026</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #14532d;
        }
        .container {
            max-width: 600px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(22, 163, 74, 0.15);
        }
        h2 {
            margin-top: 0;
            color: #14532d;
        }
        hr {
            border: 0;
            border-top: 1px solid #dcfce7;
            margin-bottom: 20px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        input[type="text"],
        input[type="number"],
        textarea,
        select {
            width: 100%;
            padding: 8px 10px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            box-sizing: border-box;
            outline: none;
        }
        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea:focus,
        select:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        }
        textarea {
            resize: vertical;
            height: 80px;
        }
        button {
            padding: 10px 20px;
            background-color: #16a34a;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        button:hover {
            background-color: #14532d;
        }
        a {
            color: #166534;
            text-decoration: none;
            margin-left: 10px;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Tambah Data Pelanggaran Baru</h2>
        <hr>

        <?php if (isset($error)) : ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="" method="POST">
            <div class="form-group">
                <label>ID Kategori Pelanggaran (pelanggaran_kategori_id):</label>
                <input type="number" name="pelanggaran_kategori_id" required placeholder="Contoh: 1">
            </div>

            <div class="form-group">
                <label>Kode Pelanggaran:</label>
                <input type="text" name="kode" required placeholder="Contoh: P001">
            </div>

            <div class="form-group">
                <label>Nama Pelanggaran:</label>
                <input type="text" name="nama" required placeholder="Contoh: Terlambat datang ke sekolah">
            </div>

            <div class="form-group">
                <label>Jumlah Poin:</label>
                <input type="number" name="poin" required placeholder="Contoh: 10">
            </div>

            <div class="form-group">
                <label>Deskripsi:</label>
                <textarea name="deskripsi" placeholder="Keterangan tambahan pelanggaran..."></textarea>
            </div>

            <div class="form-group">
                <label>Status Aktif:</label>
                <select name="status_aktif" required>
                    <option value="1">Aktif</option>
                    <option value="0">Tidak Aktif</option>
                </select>
            </div>

            <button type="submit" name="simpan">Simpan Pelanggaran</button>
            <a href="riwayat.php">Kembali</a>
        </form>
    </div>

</body>
</html>