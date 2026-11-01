<?php
session_start();
include 'config/koneksi.php';

// handler form "Tambah Kelas Baru" — form ada di kelola_kelas.php

// Cek apakah user sudah login
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: kelola_kelas.php");
    exit;
}

if (isset($_POST['simpan'])) {
    $nama         = mysqli_real_escape_string($koneksi, $_POST['nama']);
    $tingkat      = mysqli_real_escape_string($koneksi, $_POST['tingkat']);
    $jurusan      = mysqli_real_escape_string($koneksi, $_POST['jurusan']);
    $status_aktif = mysqli_real_escape_string($koneksi, $_POST['status_aktif']);

    // Query INSERT ke tabel t_kelas
    $query = "INSERT INTO t_kelas (nama, tingkat, jurusan, status_aktif) VALUES ('$nama', '$tingkat', '$jurusan', '$status_aktif')";

    if (mysqli_query($koneksi, $query)) {
        header("Location: kelola_kelas.php");
        exit;
    }

    echo "Gagal menyimpan data kelas: " . mysqli_error($koneksi);
} else {
    header("Location: kelola_kelas.php");
    exit;
}
