<?php

declare(strict_types=1);

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';

$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

// overeni promenne $table
if (!isset($table) || !is_string($table)) {
    die('Chyba: Proměnná $table není nastavena.');
}

$result = $conn->query("SELECT * from $table_matches where Zavod_id='$table' limit 1");
if ($result->num_rows > 0) {
    $match_data = $result->fetch_array();
} else {
    echo "<pre class='text-warning text-center h4 m-5'>Závod neobsahuje žádná data.<br>Zkontrolujte záznam '$table' v tabulce '$table_matches'</pre></h2>";
    exit;
}

require_once __DIR__ . '/config/mail_texty.php';

$dnesText = (new DateTime())->format("Y-m-d H:i:s");
$datumZavod = new DateTime($match_data['Zavod_datum']);
$datumPrematch = (clone $datumZavod)->modify("-1 days");

$dny = [
    'Monday' => 'pondělí',
    'Tuesday' => 'úterý',
    'Wednesday' => 'středa',
    'Thursday' => 'čtvrtek',
    'Friday' => 'pátek',
    'Saturday' => 'sobota',
    'Sunday' => 'neděle'
];

$denZavodEn = (clone $datumZavod)->format('l');
$denZavod = $dny[$denZavodEn] ?? '';

$denPrematchEn = ($datumPrematch)->format('l');
$denPrematch = $dny[$denPrematchEn] ?? '';

$isRegistracePozastavena = ($match_data['Zavod_registrace_pozastaveno']);

// Určení pořadatele
$poradatel = "";
$sponzor = "";

if (!empty($match_data['Zavod_poradatel'])) {
    $normalized = normalize($match_data['Zavod_poradatel']);

    if (strpos($normalized, 'prachatice') !== false) {
        $poradatel = "prachatice";
        $sponzor = "<a href='https://prostordesign.cz/' target='_blank'><img src='./images/logo-prostor-design.png' class='img-thumbnail mb-3 mx-auto d-block' alt='Prostor Design'></a>";
    }
}
?>
<!doctype html>
<html lang="cs">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($match_data['Zavod'] ?? 'Závod', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="shortcut icon" href="./images/favicon.ico" />
    <link rel="apple-touch-icon" href="./images/apple-touch-icon.png" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Roboto+Condensed:400,700|Arimo:400,700" />
    <link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" crossorigin="anonymous" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/magnific-popup.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/magnific-popup.js/1.1.0/jquery.magnific-popup.js"></script>
</head>

<body>
    <?php require_once __DIR__ . '/components/toast.php'; ?>
    <div class="container">
        <div class="header">
            <div class="header-logo">
                <div class="logo-left"></div>
                <div class="text-center">
                    <a class="logo-text" href="<?= htmlspecialchars($match_data['Klub_web'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>" target="_blank">
                        <?= htmlspecialchars($match_data['Zavod'] ?? '', ENT_QUOTES, 'UTF-8') ?>
                    </a>
                </div>
                <div class="logo-right"></div>
            </div>
        </div>

        <nav class="navbar navbar-expand-md sticky-top navbar-dark">
            <a href="index.php"><span class="fas fa-home navbar-toggler my-3" style="font-size:2rem"></span></a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                <ul class="navbar-nav fw-bold">
                    <li class="nav-item"><a class="nav-link" href="./">Propozice</a></li>
                    <li class="nav-item"><a class="nav-link" href="./registrace.php">Registrace</a></li>
                    <li class="nav-item <?= hidden($match_data['Web_zobrazovat_zavodniky'] == 0) ?>"><a class="nav-link" href="./zavodnici.php">Závodníci</a></li>
                    <li class="nav-item <?= hidden($match_data['Web_zobrazovat_prehledy'] == 0) ?>"><a class="nav-link" href="./prehledy.php">Přehledy</a></li>
                    <li class="nav-item <?= hidden($match_data['Web_zobrazovat_discipliny'] == 0) ?>"><a class="nav-link" href="./discipliny.php">Disciplíny</a></li>
                    <li class="nav-item <?= hidden($match_data['Web_zobrazovat_vysledky'] == 0) ?>"><a class="nav-link" href="<?= htmlspecialchars($match_data['Zavod_vysledky'] ?? '#', ENT_QUOTES, 'UTF-8'); ?>">Výsledky</a></li>
                    <li class="nav-item"><a class="nav-link" href="./login.php">&nbsp;<i class="fas fa-user-lock" style="font-size:16px"></i>&nbsp;</a></li>
                </ul>
            </div>
        </nav>

        <div id="main">
            <div id="content">