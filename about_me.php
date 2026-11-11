<?php
// about_me.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>About Me - Raditya Jeti Prasetya</title>

    <link rel="stylesheet" href="layout/layout.css">

    <style>
        :root {
            --ink: #17181c;
            --muted: #6b7080;
            --line: #e5e7eb;
            --bg: #fcfcfd;
            --aksen: #2563eb;
        }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            line-height: 1.65;
        }

        /* Pola titik samar di latar, halus */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image: radial-gradient(var(--line) 1px, transparent 1px);
            background-size: 26px 26px;
            opacity: .35;
            pointer-events: none;
            z-index: -1;
        }

        .profil {
            max-width: 780px;
            margin: 0 auto;
        }

        /* ===== Animasi masuk bertahap ===== */
        @keyframes naik {
            from { opacity: 0; transform: translateY(22px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .tampil-awal {
            animation: naik .7s cubic-bezier(.2, .7, .3, 1) both;
        }

        .tampil-awal:nth-child(2) { animation-delay: .12s; }
        .tampil-awal:nth-child(3) { animation-delay: .24s; }

        /* ===== Kepala ===== */
        .profil-head {
            display: flex;
            align-items: center;
            gap: clamp(1.25rem, 3vw, 2rem);
            flex-wrap: wrap;
            padding: 1rem 0 2.5rem;
        }

        /* Monogram dengan ring berputar halus */
        .monogram {
            flex: none;
            width: clamp(88px, 12vw, 116px);
            aspect-ratio: 1;
            display: grid;
            place-items: center;
            border-radius: 50%;
            background: var(--ink);
            color: #fff;
            font-weight: 700;
            font-size: clamp(1.6rem, 3vw, 2rem);
            letter-spacing: .06em;
            position: relative;
            animation: naik .7s cubic-bezier(.2, .7, .3, 1) both;
        }

        .monogram::before,
        .monogram::after {
            content: "";
            position: absolute;
            inset: -7px;
            border-radius: 50%;
            border: 2px solid transparent;
            border-top-color: var(--aksen);
            animation: putar 2.6s linear infinite;
        }

        .monogram::after {
            inset: -14px;
            border-top-color: rgba(37, 99, 235, .35);
            animation-duration: 5s;
            animation-direction: reverse;
        }

        @keyframes putar {
            to { transform: rotate(360deg); }
        }

        .profil-head .status {
            margin: 0 0 .5rem;
            font-size: .8rem;
            font-weight: 600;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--aksen);
        }

        .profil-head h1 {
            margin: 0 0 .75rem;
            font-size: clamp(2rem, 4.5vw, 2.9rem);
            font-weight: 700;
            letter-spacing: -0.03em;
            line-height: 1.08;
            position: relative;
            display: inline-block;
        }

        /* Garis aksen di bawah nama, digambar saat halaman dibuka */
        .profil-head h1::after {
            content: "";
            position: absolute;
            left: 0;
            bottom: -.35rem;
            height: 4px;
            width: 100%;
            border-radius: 4px;
            background: linear-gradient(90deg, var(--aksen), #7c3aed);
            transform-origin: left;
            animation: tarik .8s .5s cubic-bezier(.2, .7, .3, 1) both;
        }

        @keyframes tarik {
            from { transform: scaleX(0); }
            to   { transform: scaleX(1); }
        }

        .profil-head p.tagline {
            margin: 0;
            max-width: 46ch;
            color: var(--muted);
        }

        /* ===== Judul bagian ===== */
        .judul-bagian {
            margin: 0 0 1.25rem;
            font-size: .8rem;
            font-weight: 700;
            letter-spacing: .14em;
            text-transform: uppercase;
            color: var(--muted);
            display: flex;
            align-items: center;
            gap: .6rem;
        }

        /* Garis kecil di samping judul memanjang saat hover bagian */
        .judul-bagian::before {
            content: "";
            width: 18px;
            height: 2px;
            background: var(--aksen);
            transition: width .3s ease;
        }

        section.blok:hover .judul-bagian::before {
            width: 38px;
        }

        /* ===== Bagian ===== */
        section.blok {
            padding: 2rem 0;
            border-top: 1px solid var(--line);
            transition: border-color .3s ease;
        }

        /* ===== Data diri interaktif ===== */
        .data-diri {
            margin: 0;
        }

        .data-diri .baris {
            display: grid;
            grid-template-columns: 7rem 1fr;
            gap: 1.25rem;
            padding: .8rem .75rem;
            border-radius: 10px;
            transition: background .25s ease, transform .25s ease;
            position: relative;
        }

        .data-diri .baris:hover {
            background: rgba(37, 99, 235, .06);
            transform: translateX(6px);
        }

        .data-diri .baris + .baris {
            margin-top: 2px;
            border-top: 1px solid var(--line);
        }

        .data-diri dt {
            margin: 0;
            color: var(--muted);
            font-size: .9rem;
        }

        .data-diri dd {
            margin: 0;
            font-weight: 500;
        }

        /* ===== Proyek: kartu melayang ===== */
        .kartu-proyek {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            padding: 1.5rem 1.6rem;
            transition: transform .3s ease, box-shadow .3s ease, border-color .3s ease;
        }

        .kartu-proyek:hover {
            transform: translateY(-5px);
            border-color: rgba(37, 99, 235, .45);
            box-shadow: 0 16px 34px -18px rgba(23, 24, 28, .28);
        }

        .kartu-proyek h3 {
            margin: 0 0 .5rem;
            font-size: 1.2rem;
        }

        .kartu-proyek p {
            margin: 0 0 .9rem;
            color: var(--muted);
            max-width: 62ch;
        }

        .tekno {
            margin: 0;
            color: var(--muted);
            font-size: .92rem;
            letter-spacing: .04em;
        }

        /* ===== Scroll-reveal (tolong oleh JS kecil) ===== */
        .siap-reveal {
            opacity: 0;
            transform: translateY(26px);
            transition: opacity .65s cubic-bezier(.2, .7, .3, 1),
                        transform .65s cubic-bezier(.2, .7, .3, 1);
        }

        .siap-reveal.telah-reveal {
            opacity: 1;
            transform: translateY(0);
        }

        @media (max-width: 575.98px) {
            .data-diri .baris {
                grid-template-columns: 5.5rem 1fr;
                gap: 1rem;
            }
        }

        @media (prefers-reduced-motion: reduce) {
            * { animation: none !important; transition: none !important; }
            .siap-reveal { opacity: 1; transform: none; }
        }
    </style>
</head>

<body>

<!-- SIDEBAR BERSAMA -->
<?php include 'layout/sidebar.php'; ?>

<!-- KONTEN UTAMA -->
<main class="app-main">
    <div class="profil">

        <!-- Kepala -->
        <section class="profil-head">
            <div class="monogram" aria-hidden="true">JETI</div>

            <div class="tampil-awal">
                <p class="status">Pelajar SMK</p>
                <h1>Raditya Jeti Prasetya</h1>
                <p class="tagline">
                    Seorang pelajar yang sedang belajar dan mengembangkan
                    kemampuan di bidang teknologi.
                </p>
            </div>
        </section>

        <!-- Data diri -->
        <section class="blok siap-reveal">
            <h2 class="judul-bagian">Data Diri</h2>

            <dl class="data-diri">
                <div class="baris">
                    <dt>Nama</dt>
                    <dd>Raditya Jeti Prasetya</dd>
                </div>
                <div class="baris">
                    <dt>Status</dt>
                    <dd>Pelajar SMK</dd>
                </div>
                <div class="baris">
                    <dt>Sekolah</dt>
                    <dd>SMK Muhammadiyah Kota Tasikmalaya</dd>
                </div>
                <div class="baris">
                    <dt>Umur</dt>
                    <dd>17 tahun</dd>
                </div>
                <div class="baris">
                    <dt>Hobi</dt>
                    <dd>Main musik (gitar, drum, keyboard) dan main game</dd>
                </div>
            </dl>
        </section>

        <!-- Proyek -->
        <section class="blok siap-reveal">
            <h2 class="judul-bagian">Proyek Saat Ini</h2>

            <div class="kartu-proyek">
                <h3>Sistem Pelanggaran Siswa</h3>
                <p>
                    Aplikasi web untuk mencatat pelanggaran siswa. Admin
                    mengelola data siswa, guru, kelas, tahun ajaran, dan wali
                    kelas. Guru mencatat serta memantau pelanggaran siswa.
                    Dibuat sebagai proyek UKK 2026.
                </p>
                <p class="tekno">PHP&nbsp;&middot;&nbsp;MySQL&nbsp;&middot;&nbsp;Bootstrap 5</p>
            </div>
        </section>

    </div>
</main>

<script>
// Scroll-reveal: bagian muncul saat masuk layar, sekali saja tanpa library
document.addEventListener('DOMContentLoaded', function () {
    const target = document.querySelectorAll('.siap-reveal');

    const pengamat = new IntersectionObserver(function (entri) {
        entri.forEach(function (e) {
            if (e.isIntersecting) {
                e.target.classList.add('telah-reveal');
                pengamat.unobserve(e.target);
            }
        });
    }, { threshold: 0.15 });

    target.forEach(function (el) { pengamat.observe(el); });
});
</script>

</body>
</html>
