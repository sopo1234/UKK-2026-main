<?php
// aritmatika.php
// Menghitung perkalian dan pembagian dua nilai.

$nilai1 = 10;
$nilai2 = 5;

$perkalian = $nilai1 * $nilai2;
$pembagian = ($nilai2 != 0) ? ($nilai1 / $nilai2) : null;

function tampil($nilai)
{
    return htmlspecialchars((string) $nilai, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aritmatika</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px;
            line-height: 1.6;
        }

        h1 {
            margin-bottom: 8px;
        }

        .hasil {
            display: inline-block;
            margin-top: 12px;
            padding: 16px 20px;
            border: 1px solid #cccccc;
            border-radius: 6px;
            background-color: #f7f7f7;
        }
    </style>
</head>
<body>

    <h1>Perhitungan Aritmatika</h1>

    <p>Nilai 1: <strong><?= tampil($nilai1) ?></strong></p>
    <p>Nilai 2: <strong><?= tampil($nilai2) ?></strong></p>

    <div class="hasil">
        <p>Perkalian: <?= tampil($nilai1) ?> &times; <?= tampil($nilai2) ?> = <strong><?= tampil($perkalian) ?></strong></p>
        <p>
            Pembagian:
            <?php if ($pembagian === null): ?>
                <strong>Tidak dapat dibagi karena nilai 2 adalah 0.</strong>
            <?php else: ?>
                <?= tampil($nilai1) ?> &divide; <?= tampil($nilai2) ?> = <strong><?= tampil($pembagian) ?></strong>
            <?php endif; ?>
        </p>
    </div>

</body>
</html>
