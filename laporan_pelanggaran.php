<?php
// Laporan Pelanggaran Siswa (khusus guru, dengan filter & simpan laporan)
include 'includes/cek_session.php';
include 'config/koneksi.php';

if (($_SESSION['role'] ?? '') != 'guru') {
    header("Location: dashboard.php");
    exit;
}

function e($nilai)
{
    return htmlspecialchars((string)($nilai ?? ''), ENT_QUOTES, 'UTF-8');
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
$nama_guru = '';
$stmt = mysqli_prepare($koneksi, "SELECT id, nama FROM t_guru WHERE user_id = ? LIMIT 1");
mysqli_stmt_bind_param($stmt, "i", $_SESSION['id_user']);
mysqli_stmt_execute($stmt);
$g = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);
if ($g) {
    $guru_id  = (int) $g['id'];
    $nama_guru = $g['nama'];
}

// Kelas yang diwali kelasi guru ini (status aktif)
$kelas_wali = [];
$stmt = mysqli_prepare($koneksi, "
    SELECT wk.kelas_id, k.nama
    FROM t_wali_kelas wk
    JOIN t_kelas k ON k.id = wk.kelas_id
    WHERE wk.guru_id = ? AND wk.status_aktif = 1
    ORDER BY k.nama
");
mysqli_stmt_bind_param($stmt, "i", $guru_id);
mysqli_stmt_execute($stmt);
$res_wali = mysqli_stmt_get_result($stmt);
while ($row = mysqli_fetch_assoc($res_wali)) {
    $kelas_wali[(int) $row['kelas_id']] = $row['nama'];
}
mysqli_stmt_close($stmt);

// ---------- FILTER ----------
$cari     = trim($_GET['q'] ?? '');
$kelas_id = (int) ($_GET['kelas_id'] ?? 0);
$dari     = trim($_GET['dari'] ?? '');
$sampai   = trim($_GET['sampai'] ?? '');
$status   = $_GET['status'] ?? '';
$oleh     = $_GET['oleh'] ?? 'semua';

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
if ($kelas_id > 0 && !isset($kelas_wali[$kelas_id])) {
    // Kelas yang dipilih bukan kelas yang diwali kelasi
    $kelas_id = 0;
}

$where = [];
$tipe  = '';
$param = [];

// Batasi isi laporan hanya kelas yang diwali kelasi guru ini
if (!$kelas_wali) {
    $where[] = '1=0';
} else {
    $in     = implode(',', array_fill(0, count($kelas_wali), '?'));
    $where[] = "ps.kelas_id IN ($in)";
    $tipe   .= str_repeat('i', count($kelas_wali));
    array_push($param, ...array_keys($kelas_wali));
}

if ($cari !== '') {
    $where[] = '(ps.nama_siswa LIKE ? OR s.nis LIKE ? OR ps.nama_pelanggaran LIKE ?)';
    $tipe   .= 'sss';
    $like    = '%' . $cari . '%';
    array_push($param, $like, $like, $like);
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
if ($oleh === 'saya' && $guru_id !== null) {
    $where[] = 'ps.guru_id = ?';
    $tipe   .= 'i';
    $param[] = $guru_id;
}

$sql_where = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$dasar = "FROM t_pelanggaran_siswa ps LEFT JOIN t_siswa s ON s.id = ps.siswa_id $sql_where";

// ---------- RINGKASAN ----------
$stmt = mysqli_prepare($koneksi, "
    SELECT COUNT(*) AS total,
           COALESCE(SUM(ps.poin), 0) AS total_poin,
           COUNT(DISTINCT ps.siswa_id) AS total_siswa
    $dasar
");
if ($param) {
    mysqli_stmt_bind_param($stmt, $tipe, ...$param);
}
mysqli_stmt_execute($stmt);
$ringkas = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
mysqli_stmt_close($stmt);

$total        = (int) $ringkas['total'];
$total_poin   = (int) $ringkas['total_poin'];
$total_siswa  = (int) $ringkas['total_siswa'];

// ---------- DAFTAR PELANGGARAN (maks 100 baris) ----------
$stmt = mysqli_prepare($koneksi, "
    SELECT ps.tanggal, ps.nama_siswa, s.nis, ps.nama_kelas, ps.nama_pelanggaran,
           ps.poin, ps.keterangan, ps.tindakan, ps.status, ps.nama_guru
    $dasar
    ORDER BY ps.tanggal DESC, ps.id DESC
    LIMIT 100
");
if ($param) {
    mysqli_stmt_bind_param($stmt, $tipe, ...$param);
}
mysqli_stmt_execute($stmt);
$hasil = mysqli_stmt_get_result($stmt);

// ---------- LAPORAN TERSIMPAN (milik guru ini) ----------
$laporan_tersimpan = [];
if ($guru_id !== null) {
    $stmt = mysqli_prepare($koneksi, "
        SELECT id, judul, periode, total_catatan, total_poin, created_at
        FROM t_laporan
        WHERE guru_id = ?
        ORDER BY id DESC
        LIMIT 50
    ");
    mysqli_stmt_bind_param($stmt, "i", $guru_id);
    mysqli_stmt_execute($stmt);
    $res_laporan = mysqli_stmt_get_result($stmt);
    while ($row = mysqli_fetch_assoc($res_laporan)) {
        $laporan_tersimpan[] = $row;
    }
    mysqli_stmt_close($stmt);
}

// Teks periode untuk laporan tersimpan & tombol simpan
$periode = 'Semua tanggal';
if ($dari !== '' && $sampai !== '') {
    $periode = $dari . ' s/d ' . $sampai;
} elseif ($dari !== '') {
    $periode = 'Sejak ' . $dari;
} elseif ($sampai !== '') {
    $periode = 'Sampai ' . $sampai;
}

$warna = ['tercatat' => 'secondary', 'ditindak' => 'warning', 'selesai' => 'success'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pelanggaran Siswa</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="layout/layout.css">
    <link rel="stylesheet" href="layout/tema.css">
    <style>
        .text-bg-secondary {
            background-color: #2f737d !important;
            color: #f7f9f8 !important;
        }

        @media print {
            .app-sidebar,
            .no-print {
                display: none !important;
            }

            .app-main {
                margin-left: 0 !important;
                padding: 0 !important;
            }

            body {
                background: #ffffff !important;
            }
        }
    </style>
</head>
<body>

<?php include 'layout/sidebar.php'; ?>

<main class="app-main">

    <div class="page-header">
        <h1>Laporan Pelanggaran Siswa</h1>
        <a href="dashboard.php">Kembali ke Dashboard</a>
        <p class="mb-0 text-muted">Laporan atas nama: <b><?= e($nama_guru) ?></b></p>
    </div>

    <?php if (isset($_SESSION['pesan_sukses'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= e($_SESSION['pesan_sukses']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['pesan_sukses']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['pesan_error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= e($_SESSION['pesan_error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['pesan_error']); ?>
    <?php endif; ?>

    <?php if (!$kelas_wali): ?>
        <div class="alert alert-info">
            Anda belum menjadi wali kelas aktif, sehingga tidak ada laporan pelanggaran yang dapat ditampilkan.
        </div>
    <?php endif; ?>

    <div class="table-card shadow-sm mb-4">

        <form method="GET" action="laporan_pelanggaran.php" class="row g-2 mb-3 no-print">
            <div class="col-md-4">
                <input type="text" name="q" value="<?= e($cari) ?>" class="form-control"
                       placeholder="Cari nama, NIS, atau pelanggaran...">
            </div>
            <div class="col-md-3">
                <select name="kelas_id" class="form-select">
                    <option value="0">Semua kelas wali</option>
                    <?php foreach ($kelas_wali as $id_kelas => $nama_kelas): ?>
                        <option value="<?= (int) $id_kelas ?>" <?= $kelas_id === (int) $id_kelas ? 'selected' : '' ?>>
                            <?= e($nama_kelas) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <select name="status" class="form-select">
                    <option value="">Semua status</option>
                    <option value="tercatat" <?= $status === 'tercatat' ? 'selected' : '' ?>>Tercatat</option>
                    <option value="ditindak" <?= $status === 'ditindak' ? 'selected' : '' ?>>Ditindak</option>
                    <option value="selesai" <?= $status === 'selesai' ? 'selected' : '' ?>>Selesai</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="oleh" class="form-select">
                    <option value="semua" <?= $oleh === 'semua' ? 'selected' : '' ?>>Semua guru</option>
                    <option value="saya" <?= $oleh === 'saya' ? 'selected' : '' ?>>Catatan saya saja</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small mb-0">Dari tanggal</label>
                <input type="date" name="dari" value="<?= e($dari) ?>" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="form-label small mb-0">Sampai tanggal</label>
                <input type="date" name="sampai" value="<?= e($sampai) ?>" class="form-control">
            </div>
            <div class="col-md-6 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary">Tampilkan</button>
                <a href="laporan_pelanggaran.php" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>

        <h5 class="mb-2">Ringkasan Laporan</h5>
        <p class="mb-2">
            Periode: <b><?= e($periode) ?></b>
            <?php if ($oleh === 'saya'): ?>
                &middot; hanya catatan saya
            <?php endif; ?>
        </p>
        <ul class="mb-3">
            <li>Total catatan pelanggaran: <b><?= $total ?></b></li>
            <li>Total poin: <b><?= $total_poin ?></b></li>
            <li>Siswa yang terlibat: <b><?= $total_siswa ?></b></li>
        </ul>

        <div class="no-print d-flex gap-2 mb-3 flex-wrap">
            <button type="button" class="btn btn-outline-dark" onclick="window.print()">Cetak Laporan</button>

            <form method="POST" action="proses_simpan_laporan.php" class="d-flex gap-2 flex-wrap align-items-center">
                <input type="hidden" name="q" value="<?= e($cari) ?>">
                <input type="hidden" name="kelas_id" value="<?= (int) $kelas_id ?>">
                <input type="hidden" name="dari" value="<?= e($dari) ?>">
                <input type="hidden" name="sampai" value="<?= e($sampai) ?>">
                <input type="hidden" name="status" value="<?= e($status) ?>">
                <input type="hidden" name="oleh" value="<?= e($oleh) ?>">
                <input type="hidden" name="periode" value="<?= e($periode) ?>">
                <input type="hidden" name="total_catatan" value="<?= (int) $total ?>">
                <input type="hidden" name="total_poin" value="<?= (int) $total_poin ?>">
                <input type="text" name="judul" class="form-control"
                       style="max-width: 260px;"
                       placeholder="Judul laporan (opsional)">
                <button type="submit" name="simpan" class="btn btn-primary">Simpan Laporan</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Tanggal</th>
                        <th>Siswa</th>
                        <th>Kelas</th>
                        <th>Pelanggaran</th>
                        <th>Poin</th>
                        <th>Tindakan</th>
                        <th>Status</th>
                        <th>Dicatat oleh</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($total === 0): ?>
                        <tr><td colspan="9" class="text-center text-muted">Tidak ada data pada filter ini.</td></tr>
                    <?php endif; ?>

                    <?php $no = 1; while ($r = mysqli_fetch_assoc($hasil)): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td class="text-nowrap"><?= e($r['tanggal']) ?></td>
                            <td>
                                <?= e($r['nama_siswa']) ?>
                                <div class="small text-muted"><?= e($r['nis']) ?></div>
                            </td>
                            <td><?= e($r['nama_kelas']) ?></td>
                            <td>
                                <?= e($r['nama_pelanggaran']) ?>
                                <?php if ($r['keterangan'] !== ''): ?>
                                    <div class="small text-muted"><?= e($r['keterangan']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td><?= (int) $r['poin'] ?></td>
                            <td><?= $r['tindakan'] !== '' ? e($r['tindakan']) : '<span class="text-muted">-</span>' ?></td>
                            <td><span class="badge text-bg-<?= $warna[$r['status']] ?? 'secondary' ?>"><?= e(ucfirst($r['status'])) ?></span></td>
                            <td><?= e($r['nama_guru']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total > 100): ?>
            <p class="small text-muted mb-0">
                Menampilkan 100 dari <?= $total ?> catatan. Sisanya dapat dilihat pada menu Riwayat.
            </p>
        <?php endif; ?>
    </div>

    <div class="table-card shadow-sm">
        <h5 class="mb-3">Laporan Tersimpan</h5>

        <div class="table-responsive">
            <table class="table table-bordered table-striped table-hover align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Judul</th>
                        <th>Periode</th>
                        <th>Total Catatan</th>
                        <th>Total Poin</th>
                        <th>Dibuat</th>
                        <th class="no-print">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$laporan_tersimpan): ?>
                        <tr><td colspan="7" class="text-center text-muted">Belum ada laporan tersimpan.</td></tr>
                    <?php endif; ?>

                    <?php $no = 1; foreach ($laporan_tersimpan as $l): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><?= e($l['judul']) ?></td>
                            <td><?= e($l['periode']) ?></td>
                            <td><?= (int) $l['total_catatan'] ?></td>
                            <td><?= (int) $l['total_poin'] ?></td>
                            <td><?= e($l['created_at']) ?></td>
                            <td class="no-print">
                                <form action="hapus_laporan.php" method="POST"
                                      onsubmit="return confirm('Yakin ingin menghapus laporan ini?')">
                                    <input type="hidden" name="id" value="<?= (int) $l['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
