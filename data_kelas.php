<?php
session_start();
include 'config/koneksi.php';

// Cek apakah user sudah login
if (!isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Kelas - UKK 2026</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            color: #14532d;
        }
        .container {
            max-width: 800px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 6px 24px rgba(22, 163, 74, 0.15);
            margin: auto;
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
        .btn-tambah {
            display: inline-block;
            padding: 8px 15px;
            background-color: #16a34a;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        .btn-tambah:hover {
            background-color: #14532d;
            color: white;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 5px;
            overflow: hidden;
        }
        table th, table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #dcfce7;
        }
        table th {
            background-color: #14532d;
            color: white;
        }
        table tr:hover {
            background-color: #f0fdf4;
        }
        .btn-action {
            padding: 5px 10px;
            text-decoration: none;
            border-radius: 6px;
            color: white;
            font-size: 14px;
            margin-right: 5px;
        }
        .btn-edit {
            background-color: #ffffff;
            color: #16a34a;
            border: 1px solid #16a34a;
        }
        .btn-edit:hover {
            background-color: #dcfce7;
            color: #14532d;
        }
        .btn-hapus {
            background-color: #dc3545;
        }
        .btn-hapus:hover {
            background-color: #c82333;
        }
        .back-link {
            display: inline-block;
            margin-top: 15px;
            color: #166534;
            text-decoration: none;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>Daftar Data Kelas</h2>
        <hr>

        <a href="tambah_kelas.php" class="btn-tambah">+ Tambah Kelas Baru</a>

        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Nama Kelas</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Ambil data dari tabel t_kelas (menggunakan deteksi otomatis kolom nama/nama_kelas)
                $query = "SELECT * FROM t_kelas ORDER BY id DESC";
                $result = mysqli_query($koneksi, $query);

                if ($result && mysqli_num_rows($result) > 0) {
                    $no = 1;
                    while ($row = mysqli_fetch_assoc($result)) {
                        // Menentukan kolom mana yang berisi nama kelas
                        $nama_kelas_val = isset($row['nama_kelas']) ? $row['nama_kelas'] : (isset($row['nama']) ? $row['nama'] : '-');
                        
                        echo "<tr>";
                        echo "<td>" . $no++ . "</td>";
                        echo "<td>" . htmlspecialchars($nama_kelas_val) . "</td>";
                        echo "<td>";
                        echo "<a href='edit_data_kelas.php?id=" . $row['id'] . "' class='btn-action btn-edit'>Edit</a>";
                        echo "<a href='hapus_kelas.php?id=" . $row['id'] . "' class='btn-action btn-hapus' onclick='return confirm(\"Yakin ingin menghapus kelas ini?\")'>Hapus</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3' style='text-align: center;'>Belum ada data kelas.</td></tr>";
                }
                ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="back-link">&larr; Kembali ke Dashboard</a>
    </div>

</body>
</html>