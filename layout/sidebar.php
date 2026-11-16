<?php
// Pastikan session aktif
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Halaman yang sedang dibuka
$current_page = basename($_SERVER['PHP_SELF']);

// Role pengguna
$role = strtolower((string)($_SESSION['role'] ?? ''));

// Inisialisasi menu agar tidak terjadi Undefined variable
$menu = [];

// Menu Admin
$menuAdmin = [
    'dashboard.php' => 'Dashboard',
    'kelola_siswa.php' => 'Data Siswa',
    'kelola_guru.php' => 'Data Guru',
    'kelola_kelas.php' => 'Data Kelas',
    'kelola_tahun_ajaran.php' => 'Data Tahun Ajaran',
    'kelola_walikelas.php' => 'Data Wali Kelas',
    'penempatan_siswa.php' => 'Penempatan Siswa',
    'kelola_pelanggaran_kategori.php' => 'Kategori Pelanggaran',
    'kelola_pelanggaran.php' => 'Jenis Pelanggaran',
    'about_me.php' => 'About Me'
];

// Menu Guru
$menuGuru = [
    'dashboard.php' => 'Dashboard',
    'catatan_pelanggaran.php' => 'Catat Pelanggaran',
    'tindakan.php' => 'Tindakan',
    'riwayat.php' => 'Riwayat',
    'rekap_poin.php' => 'Rekap Poin',
    'laporan_pelanggaran.php' => 'Laporan',
    'about_me.php' => 'About Me'
];

// Menu Laporan hanya untuk guru yang aktif menjadi wali kelas.
if ($role === 'guru') {
    require_once __DIR__ . '/../config/koneksi.php';
    require_once __DIR__ . '/../includes/wali_kelas.php';

    $wali_aktif = cari_wali_kelas($koneksi, (int) ($_SESSION['id_user'] ?? 0));
    if (!$wali_aktif) {
        unset($menuGuru['laporan_pelanggaran.php']);
    }
}

// Tentukan menu berdasarkan role
if ($role === 'admin') {
    $menu = $menuAdmin;
} elseif ($role === 'guru') {
    $menu = $menuGuru;
}
?>

<link rel="stylesheet" href="layout/layout.css">
<link rel="stylesheet" href="layout/tema.css">

<?php include __DIR__ . '/searchable_select.php'; ?>

<aside class="app-sidebar">
    <h4>Pelanggaran Siswa</h4>

    <nav>
        <?php foreach ($menu as $file => $label): ?>
            <a
                href="<?= htmlspecialchars($file, ENT_QUOTES, 'UTF-8') ?>"
                class="<?= ($current_page === $file) ? 'active' : '' ?>"
                <?= ($current_page === $file)
                    ? 'aria-current="page"'
                    : '' ?>
            ><?= htmlspecialchars($label, ENT_QUOTES, 'UTF-8') ?></a>
        <?php endforeach; ?>

        <hr>

        <a href="logout.php" class="logout">Logout</a>
    </nav>
</aside>

<script>
(function () {
    // Saat halaman ditinggalkan (misalnya menekan Logout), sembunyikan isinya.
    // Dengan begitu bfcache Chrome tidak sempat menampilkan dashboard lagi,
    // walaupun hanya sekilas, ketika tombol Back ditekan setelah logout.
    window.addEventListener('pagehide', function () {
        document.documentElement.style.visibility = 'hidden';
    });

    // Halaman yang dipulihkan dari cache/bfcache harus dicek ulang ke server.
    // Jika session sudah dihapus (logout), server akan mengarahkan ke login.php.
    window.addEventListener('pageshow', function (event) {
        var nav = window.performance && performance.getEntriesByType
            ? performance.getEntriesByType('navigation')[0]
            : null;

        if (event.persisted || (nav && nav.type === 'back_forward')) {
            window.location.reload();
            return;
        }

        document.documentElement.style.visibility = '';
    });
})();
</script>
