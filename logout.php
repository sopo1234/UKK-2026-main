<?php
session_start();

// Jangan simpan respons logout (dan halaman yang dituju) di cache browser.
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');
header('Expires: 0');

// Kosongkan seluruh data session.
$_SESSION = [];

// Hapus cookie session di browser supaya ID session lama tidak dipakai lagi.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    $samesite = $params['samesite'] ?? 'Lax';

    if ($samesite === '') {
        $samesite = 'Lax';
    }

    setcookie(
        session_name(),
        '',
        [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $samesite,
        ]
    );
}

// Hancurkan session di server.
session_destroy();

// Redirect 303 agar browser melakukan GET ke halaman login.
header('Location: login.php', true, 303);
exit;
