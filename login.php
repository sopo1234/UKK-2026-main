<?php
session_start();
include 'config/koneksi.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Pelanggaran Siswa</title>

    <!-- Bootstrap 5, sama dengan halaman dashboard -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css"
          rel="stylesheet">

    <!-- Layout bersama + tema Teal on Paper -->
    <link rel="stylesheet" href="layout/layout.css">
    <link rel="stylesheet" href="layout/tema.css">

    <style>
        body {
            background: #f7f9f8;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }

        /* Kartu login menyerupai .form-card di layout.css */
        .login-card {
            width: 100%;
            max-width: 380px;
            padding: 30px;
        }

        .login-title {
            color: #06525c;
            font-weight: bold;
            text-align: center;
            margin-bottom: 20px;
        }

        .login-card label {
            font-size: 13px;
            margin: 12px 0 5px;
            color: #333;
        }
    </style>
</head>

<body>

    <div class="card shadow-sm login-card">
        <div class="card-body">

            <h4 class="login-title">&#9878; Pelanggaran Siswa</h4>

            <?php
            if (isset($_SESSION['pesan_error'])) {
                echo '<div class="alert alert-danger py-2">'
                    . htmlspecialchars($_SESSION['pesan_error'], ENT_QUOTES, 'UTF-8')
                    . '</div>';

                unset($_SESSION['pesan_error']);
            }
            ?>

            <form action="proses_login.php" method="POST">

                <label for="email">Username / Email</label>
                <input
                    type="text"
                    name="email"
                    id="email"
                    class="form-control"
                    placeholder="Masukkan email..."
                    required
                    autocomplete="username"
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    class="form-control"
                    placeholder="Masukkan password..."
                    required
                    autocomplete="current-password"
                >

                <button type="submit" class="btn btn-primary w-100 mt-3">
                    LOGIN
                </button>

            </form>

        </div>
    </div>

</body>
</html>
