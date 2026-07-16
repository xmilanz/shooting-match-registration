<?php

declare(strict_types=1);
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';
require_admin();

$toast = $_SESSION['toast'] ?? null;
unset($_SESSION['toast']);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}


// kontrola přihlášení
$table = $_SESSION['zavod_id'];

$result = $conn->query("SELECT * FROM $table_matches WHERE Zavod_id='$table' LIMIT 1");

if ($result->num_rows > 0) {
    $match_data = $result->fetch_array();
} else {
    echo "<pre class='text-warning text-center h4 m-5'>Závod neobsahuje žádná data.<br>Zkontrolujte záznam '$table' v tabulce '$table_matches'</pre></h2>";
    exit;
}

$stmt = $conn->prepare("SELECT firstname,lastname,email FROM $table_admins WHERE username = ?");
$stmt->bind_param("s", $_SESSION['name']);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$line = mysqli_fetch_assoc($result);

require_once __DIR__ . '/config/mail_texty.php';

// podminene css d-none
$paymentBeforeClass = ($match_data['Payment_before'] == 1) ? '' : 'd-none';
$hromadnaRegistraceClass = ($match_data['Zavod_registrace_hromadna'] == 1) ? '' : 'd-none';
$smenyRegistraceClass = ($match_data['Zavod_registrace_smeny'] == 1) ? "" : "d-none";
$registracePozastavena = ($match_data['Zavod_registrace_pozastaveno'] == 1) ? '' : 'd-none';

$organizer = $_SESSION['organizer'] ?? '';
$availableRaces = getRacesForOrganizer($conn, $organizer, $zavody_prefix);

$dnes = (new DateTime())->format("Y-m-d H:i:s");
?>

<!doctype html>
<html lang="cs">

<HEAD>
    <meta http-equiv="Content-Language" content="cs">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <link rel="shortcut icon" href="./images/favicon.ico" />
    <title>Administrace závodu <?= htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') ?></title>
    <link rel="stylesheet" type="text/css" href="./styles/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <!-- bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
        integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
        crossorigin="anonymous"></script>
    <link rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/css/bootstrap-datepicker.min.css">
    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-datepicker/1.9.0/js/bootstrap-datepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <!-- dataTable https://datatables.net/download/ -->
    <script type="text/javascript" src="./js/datatable_conf.js"></script>
    <link
        href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.2.1/b-3.2.1/b-colvis-3.2.1/b-html5-3.2.0/b-print-3.2.0/cr-2.0.4/date-1.5.5/r-3.0.3/sb-1.8.1/sp-2.3.3/datatables.min.css"
        rel="stylesheet">
    <script
        src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.2.1/b-3.2.1/b-colvis-3.2.1/b-html5-3.2.0/b-print-3.2.0/cr-2.0.4/date-1.5.5/r-3.0.3/sb-1.8.1/sp-2.3.3/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/plug-ins/1.10.24/dataRender/ellipsis.js"></script>
</HEAD>

<BODY>
    <?php require_once __DIR__ . '/components/toast.php'; ?>
    <div class="container">
        <div class="header">
            <div class="header-logo">
                <div class="logo-left"></div>
                <div class="text-center">
                    <a class="logo-text" href="<?= $web_adresa_admin ?>" target="_blank">
                        <?= htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') ?> - administrace</a><br>
                    <?php if ($match_data['Zavod_registrace_pozastaveno'] == 1) {
                        echo "<span class='text-danger fs-5 tooltip '><strong>[registrace je pozastavená]</strong><span class='tooltiptext  lh-base'><strong>Spuštění registrace</strong> se provede v <span class='bg-success text-white' >Konfiguraci</span> - sekce <strong>Základní informace</strong></span></span>";
                    } elseif ($match_data['Payment_before'] == 1) {
                        echo "<span class='text-danger tooltip '>[platba startovného $match_data[Zavod_pocet_dni_na_platbu] dnů od registrace]<span class='tooltiptext'>Startovné se platí před závodem, nejpozději do $match_data[Zavod_pocet_dni_na_platbu] dnů od provedení registrace.<br><br>Nezaplatí-li závodník do té doby, pošle se ráno upozornění na chybějící platbu.<br><br>Jestliže nezaplatí ani po tomto upozornění, je druhý den večer automaticky vyřazen.</span></span>";
                    } else {
                        echo "<span class='text-danger tooltip '>[platba startovného na místě]<span class='tooltiptext'>Závodník platí v den závodu při prezenci <strong>nejpozději 30 minut před závodem</strong></span></span>";
                    }
                    ?>
                </div>
                <div class="logo-right"></div>
            </div>
        </div>
        <nav class="navbar navbar-expand-lg navbar-fixed-top bg-dark">
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#collapsibleNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="collapsibleNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item dropdown">
                        <button class="btn btn-danger dropdown-toggle me-4" id="raceDropdown" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <?= isset($_SESSION['zavod_name']) ? htmlspecialchars($_SESSION['zavod_name']) : '<i class="fas fa-bullseye"></i>&nbsp;&nbsp;Vyberte závod' ?>
                        </button>

                        <ul class="dropdown-menu" aria-labelledby="raceDropdown">
                            <?php foreach ($availableRaces as $id => $name): ?>
                                <li>
                                    <a href="#" class="dropdown-item select-race"
                                        data-race-id="<?= htmlspecialchars($id, ENT_QUOTES, 'UTF-8') ?>">
                                        <?= htmlspecialchars($name, ENT_QUOTES, 'UTF-8') ?>
                                    </a>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </li>
                    <?php if ($_SESSION['role'] === 'admin' or $_SESSION['role'] === 'editor'): ?>
                        <li class="nav-item">
                            <button href="" class="btn btn-success me-3" data-bs-toggle="modal"
                                data-bs-target="#match_configuration"><i class="fas fa-sliders-h"></i>&nbsp;&nbsp;Konfigurace</button>
                        </li>
                        <li class="nav-item">
                            <button href="" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#new_shooter"><i
                                    class="fas fa-running"></i>&nbsp;&nbsp;Nový závodník</a>
                        </li>
                        <li class="nav-item <?= !$match_data['Zavod_registrace_smeny'] ? "d-none" : ""; ?>">
                            <button href="" class="btn btn-primary ms-3" data-bs-toggle="modal"
                                data-bs-target="#admin_smeny"><i class="fas fa-table me-1"></i>&nbsp;&nbsp;Zařazení do směn</a>
                        </li>
                        <li class="nav-item dropdown">
                            <button class="btn btn-dark dropdown-toggle ms-3" id="dropdownButton" data-bs-toggle="dropdown"
                                aria-expanded="false">Nastavení závodu</button>
                            <ul class="dropdown-menu">
                                <?php if ($_SESSION['role'] === 'admin'): ?>
                                    <li>
                                        <a class="dropdown-item" href="" data-bs-toggle="modal"
                                            data-bs-target="#manage_users">Uživatelé</a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item text-danger" href="#" data-bs-toggle="modal"
                                            data-bs-target="#truncateModal">Vyprázdnit tabulku závodníků</a>
                                    </li>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                <?php endif; ?>
                            <?php endif; ?>
                            <li><a class="dropdown-item" href="" data-bs-toggle="modal" data-bs-target="#manage_disciplines">Disciplíny</a></li>
                            <li><a class="dropdown-item" href="" data-bs-toggle="modal" data-bs-target="#manage_categories">Kategorie</a></li>
                            <li>
                                <a class="dropdown-item" href="" data-bs-toggle="modal"
                                    data-bs-target="#manage_fee">Startovné</a>
                            </li>
                            </ul>
                        </li>
                        <li class="nav-item dropdown">
                            <button class="btn btn-dark dropdown-toggle mx-3" id="dropdownButton1" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                Export
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="export_prezence<?= (stripos(normalize($match_data['Zavod']), 'tenolix') !== false) ? "_tenolix" : ""; ?>.php">Seznam pro prezenci</a></li>
                            </ul>
                        </li>
                </ul>
                <div class="userArea dropdown me-2">
                    <button class="btn btn-dark custom" type="button" id="userDropdown" data-bs-toggle="dropdown"
                        aria-expanded="false">
                        <i class="fa fa-user pe-2" style="font-size:15px"></i>
                        <span class="text-dashed"><?= $line['firstname'] . " " . $line['lastname'] ?></span>
                    </button>

                    <div class="userArea dropdown-menu dropdown-menu-end p-3 shadow" aria-labelledby="userDropdown"
                        style="min-width: 260px;">

                        <div class="d-flex align-items-center justify-content-between">
                            <dl class='row text-start'>
                                <dt class='col-4 text-end text-start pe-0'><strong>login:</strong></dt>
                                <dd class='col-8 ps-2'><?= $_SESSION['name'] ?>
                                <dt class='col-4 text-end text-start pe-0'><strong>jméno:</strong></dt>
                                <dd class='col-8 ps-2'><?= $line['firstname'] . " " . $line['lastname'] ?></dd>
                                <dt class='col-4 text-end text-start pe-0'><strong>e-mail:</strong></dt>
                                <dd class='col-8 ps-2'><?= $line['email'] ?></dd>
                                <dt class='col-4 text-end text-start pe-0'><strong>role:</strong></dt>
                                <dd class='col-8 ps-2'><?= $_SESSION['role'] ?></dd>
                                <dt class='col-4 text-end text-start pe-0'><strong>soutěže:</strong></dt>
                                <dd class='col-8 ps-2'><?= $_SESSION['organizer'] ?></dd>
                                <dt class='col-4 text-end text-start pe-0'><strong>oprávnění:</strong></dt>
                                <dd class='col-8 ps-2'><?= $admin_roles[$_SESSION['role']] ?></dd>
                                <dt class='col-4 text-end text-start pe-0'><strong>IP adresa:</strong></dt>
                                <dd class='col-8 ps-2'><?= $_SERVER['REMOTE_ADDR']; ?></dd>
                            </dl>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <a href="#" class="btn btn-secondary btn-sm" data-bs-toggle="modal"
                                    data-bs-target="#password_change">
                                    Změna hesla
                                </a>
                            </div>
                            <div class="col-6 text-end">
                                <a href="logout.php" class="btn btn-danger btn-sm">
                                    Odhlásit
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
        </nav>
        <form id="raceForm" method="POST" action="select_match.php" style="display:none;">
            <input type="hidden" name="zavod_id" id="raceInput">
            <input type="hidden" name="csrf_token"
                value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
        </form>