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

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        /* Kartu login utama */
        .login-box {
            width: 100%;
            max-width: 380px;
            background-color: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 40px rgba(22, 163, 74, 0.18);
            overflow: hidden;
        }

        /* Kepala kartu berwarna hijau */
        .login-header {
            background-color: #16a34a;
            padding: 30px 30px 26px;
            text-align: center;
        }

        .logo {
            width: 64px;
            height: 64px;
            margin: 0 auto 12px;
            background-color: #ffffff;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 30px;
            color: #16a34a;
        }

        .login-header h1 {
            margin: 0;
            font-size: 20px;
            color: #ffffff;
        }

        .login-header p {
            margin: 6px 0 0;
            font-size: 12px;
            color: #dcfce7;
        }

        /* Isi form */
        .login-body {
            padding: 26px 30px 34px;
        }

        label {
            display: block;
            font-size: 12px;
            font-weight: bold;
            margin: 0 0 7px;
            color: #14532d;
        }

        input {
            width: 100%;
            height: 42px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            padding: 0 14px;
            margin-bottom: 16px;
            background-color: #ffffff;
            outline: none;
            font-size: 14px;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        input:focus {
            border-color: #16a34a;
            box-shadow: 0 0 0 3px rgba(22, 163, 74, 0.15);
        }

        button {
            width: 100%;
            height: 42px;
            margin-top: 6px;
            border: none;
            border-radius: 8px;
            background-color: #16a34a;
            color: #ffffff;
            font-size: 15px;
            font-weight: bold;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background-color 0.2s;
        }

        button:hover {
            background-color: #14532d;
        }

        /* Pesan error hijau tua pada latar putih */
        .pesan-error {
            color: #7f1d1d;
            background-color: #fee2e2;
            border: 1px solid #fecaca;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 12px;
            margin-bottom: 14px;
            overflow-wrap: anywhere;
        }

        /* Layar sangat kecil */
        @media (max-width: 400px) {
            .login-header,
            .login-body {
                padding-left: 20px;
                padding-right: 20px;
            }
        }
    </style>
</head>

<body>

    <div class="login-box">

        <div class="login-header">
            <div class="logo">⚖</div>
            <h1>Pelanggaran Siswa</h1>
            <p>Sistem Informasi Manajemen Poin Pelanggaran</p>
        </div>

        <div class="login-body">

            <?php
            if (isset($_SESSION['pesan_error'])) {
                echo '<p class="pesan-error">' .
                    htmlspecialchars($_SESSION['pesan_error'], ENT_QUOTES, 'UTF-8') .
                    '</p>';

                unset($_SESSION['pesan_error']);
            }
            ?>

            <form action="proses_login.php" method="POST">

                <label for="email">Username / Email</label>
                <input
                    type="text"
                    name="email"
                    id="email"
                    placeholder="Masukkan email..."
                    required
                    autocomplete="username"
                >

                <label for="password">Password</label>
                <input
                    type="password"
                    name="password"
                    id="password"
                    placeholder="Masukkan password..."
                    required
                    autocomplete="current-password"
                >

                <button type="submit">LOGIN</button>

            </form>

        </div>

    </div>

</body>
</html>
