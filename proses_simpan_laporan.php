<?php
// proses_simpan_laporan.php — simpan ringkasan laporan milik guru yang login
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['simpan'])) {
    header("Location: laporan_pelanggaran.php");
    exit;
}

// Pastikan tabel laporan tersedia
mysqli_query($koneksi, "
    CREATE TABLE IF NOT EXISTS t_laporan (
        id INT AUTO_INCREMENT PRIMARY KEY,
        guru_id INT NOT NULL,
        judul VARCHAR(150) NOT NULL,
        periode VARCHAR(100) NOT NULL,
        total_catatan INT NOT NULL DEFAULT 0,
        total_poin INT NOT NULL DEFAULT 0,
        filter_q VARCHAR(150) DEFAULT '',
        filter_kelas_id INT DEFAULT 0,
        filter_dari DATE NULL,
        filter_sampai DATE NULL,
        filter_status VARCHAR(20) DEFAULT '',
        filter_oleh VARCHAR(10) DEFAULT 'semua',
        created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        KEY idx_laporan_guru (guru_id),
        CONSTRAINT fk_laporan_guru FOREIGN KEY (guru_id)
            REFERENCES t_guru(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
");

// Identitas guru yang login
$guru_id = null;
$stmt = mysqli_prepare($koneksi, "SELECT id FROM t_guru WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_user']);
mysqli_stmt_execute($stmt);
$g = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if ($g) {
    $guru_id = (int) $g['id'];
}

if ($guru_id === null) {
    $_SESSION['pesan_error'] =
        "Akun Anda belum terhubung dengan data guru, sehingga laporan tidak dapat disimpan.";
    header("Location: laporan_pelanggaran.php");
    exit;
}

// Kelas yang diwali kelasi guru ini (status aktif)
$kelas_wali = [];
$stmt = mysqli_prepare($koneksi, "
    SELECT wk.kelas_id
    FROM t_wali_kelas wk
    WHERE wk.guru_id = ? AND wk.status_aktif = 1
");
mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$res_wali = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res_wali)) {
    $kelas_wali[] = (int) $row['kelas_id'];
}
mysqli_stmt_close($stmt);

// Guru biasa (bukan wali kelas aktif) tidak boleh menyimpan laporan.
if (!$kelas_wali) {
    $_SESSION['pesan_error'] = 'Laporan hanya dapat disimpan oleh guru yang menjadi wali kelas aktif.';
    header('Location: dashboard.php');
    exit;
}

// ---------- AMBIL + VALIDASI DATA ----------
$judul   = trim($_POST['judul'] ?? '');
$periode = trim($_POST['periode'] ?? '');

$cari     = trim($_POST['q'] ?? '');
$kelas_id = (int) ($_POST['kelas_id'] ?? 0);
$dari     = trim($_POST['dari'] ?? '');
$sampai   = trim($_POST['sampai'] ?? '');
$status   = $_POST['status'] ?? '';
$oleh     = $_POST['oleh'] ?? 'semua';

$tgl_valid = function ($t) {
    $d = DateTime::createFromFormat('Y-m-d', $t);
    return $d && $d->format('Y-m-d') === $t;
};
if ($dari !== '' && !$tgl_valid($dari)) {
    $dari = '';
}
if ($sampai !== '' && !$tgl_valid($sampai)) {
    $sampai = '';
}
if (!in_array($status, ['tercatat', 'ditindak', 'selesai'], true)) {
    $status = '';
}
if ($oleh !== 'saya') {
    $oleh = 'semua';
}
if ($kelas_id > 0 && !in_array($kelas_id, $kelas_wali, true)) {
    // Kelas yang dipilih bukan kelas yang diwali kelasi
    $kelas_id = 0;
}

if ($periode === '') {
    $periode = 'Semua tanggal';
}

if ($judul === '') {
    // Judul otomatis berdasarkan periode
    $judul = 'Laporan Pelanggaran — ' . $periode;
}
if (mb_strlen($judul) > 150) {
    $judul = mb_substr($judul, 0, 150);
}

// ---------- HITUNG ULANG RINGKASAN (tidak percaya data form) ----------
$where = [];
$tipe  = '';
$param = [];

// Batasi isi laporan hanya kelas yang diwali kelasi guru ini
if (!$kelas_wali) {
    $where[] = '1=0';
} else {
    $in      = implode(',', array_fill(0, count($kelas_wali), '?'));
    $where[] = "ps.kelas_id IN ($in)";
    $tipe   .= str_repeat('i', count($kelas_wali));
    array_push($param, ...$kelas_wali);
}

if ($cari !== '') {
    $where[] = '(ps.nama_siswa LIKE ? OR s.nis LIKE ? OR p.nama LIKE ? OR ps.nama_pelanggaran LIKE ?)';
    $tipe   .= 'ssss';
    $like    = '%' . $cari . '%';
    array_push($param, $like, $like, $like, $like);
}
if ($kelas_id > 0) {
    $where[] = 'ps.kelas_id = ?';
    $tipe   .= 'i';
    $param[] = $kelas_id;
}
if ($dari !== '') {
    $where[] = 'ps.tanggal >= ?';
    $tipe   .= 's';
    $param[] = $dari;
}
if ($sampai !== '') {
    $where[] = 'ps.tanggal <= ?';
    $tipe   .= 's';
    $param[] = $sampai;
}
if ($status !== '') {
    $where[] = 'ps.status = ?';
    $tipe   .= 's';
    $param[] = $status;
}
if ($oleh === 'saya') {
    $where[] = 'ps.guru_id = ?';
    $tipe   .= 'i';
    $param[] = $guru_id;
}

$sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$stmt = mysqli_prepare($koneksi, "
    SELECT COUNT(*) AS total, COALESCE(SUM(ps.poin), 0) AS total_poin
    FROM t_pelanggaran_siswa ps
    LEFT JOIN t_siswa s ON s.id = ps.siswa_id
    LEFT JOIN t_pelanggaran p ON p.id = ps.pelanggaran_id
    $sql_where
");
if ($param) {
    mysqli_stmt_bind_param($stmt, $tipe, ...$param);
}
mysqli_stmt_execute($stmt);
$ringkas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$total_catatan = (int) $ringkas['total'];
$total_poin    = (int) $ringkas['total_poin'];

if ($total_catatan === 0) {
    $_SESSION['pesan_error'] =
        "Tidak ada catatan pelanggaran pada filter ini, laporan tidak disimpan.";
    header("Location: laporan_pelanggaran.php");
    exit;
}

// ---------- SIMPAN ----------
$stmt = mysqli_prepare($koneksi, "
    INSERT INTO t_laporan
        (guru_id, judul, periode, total_catatan, total_poin,
         filter_q, filter_kelas_id, filter_dari, filter_sampai,
         filter_status, filter_oleh, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, NULLIF(?, ''), NULLIF(?, ''), ?, ?, NOW())
");

$filter_dari   = $dari;
$filter_sampai = $sampai;

mysqli_stmt_bind_param(
    $stmt,
    "issiisissss",
    $guru_id,
    $judul,
    $periode,
    $total_catatan,
    $total_poin,
    $cari,
    $kelas_id,
    $filter_dari,
    $filter_sampai,
    $status,
    $oleh
);

try {
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['pesan_sukses'] =
            "Laporan \"" . $judul . "\" berhasil disimpan.";
    } else {
        $_SESSION['pesan_error'] =
            "Gagal menyimpan laporan: " . mysqli_stmt_error($stmt);
    }
} catch (mysqli_sql_exception $e) {
    $_SESSION['pesan_error'] =
        "Gagal menyimpan laporan: " . $e->getMessage();
}

mysqli_stmt_close($stmt);

header("Location: laporan_pelanggaran.php");
exit;
