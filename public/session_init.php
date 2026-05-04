<?php
declare(strict_types=1);

// Bezpečná inicializace session pro sdílení mezi subdoménami.
// Z HTTP_HOST odvodíme hlavní doménu (poslední 2 části)
$host = $_SERVER['HTTP_HOST'] ?? '';
$parts = explode('.', $host);

// Pokud je doména typu "neco.mydomain.cz"
if (count($parts) >= 2) {
    $mainDomain = '.' . $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
} else {
    // fallback – nemělo by nastat
    $mainDomain = $host;
}

define('COOKIE_DOMAIN', $mainDomain);


// Striktní režim zabraňuje session fixation
ini_set('session.use_strict_mode', '1');

// Vlastní název sessiony (sdílený mezi subdoménami)
session_name('SP_session');

// Nastavení cookie parametrů – MUSÍ být před session_start()
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'domain'   => COOKIE_DOMAIN,  // např. .mydomain.cz
    'secure'   => true,           // vyžaduje HTTPS
    'httponly' => true,
    'samesite' => 'Strict'        // nebo 'Lax' pokud to neomezuje UX
]);

// Start session (pouze jednou)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializace základních hodnot
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
}
if (!isset($_SESSION['last_activity'])) {
    $_SESSION['last_activity'] = time();
}

// Timeout session
$timeout = 30 * 60; // 30 minut

if (
    isset($_SESSION['last_activity']) &&
    (time() - (int)$_SESSION['last_activity']) > $timeout
) {
    session_unset();
    session_destroy();
    // Session se znovu vytvoří při dalším requestu
    return;
}

// Aktualizace last_activity
$_SESSION['last_activity'] = time();
