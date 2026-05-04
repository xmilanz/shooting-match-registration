<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/config/data.php';
require_once __DIR__ . '/db/dbconn.php';
require_admin();

$shooterID = intval($_GET['ID']);
$shooterKEY = intval($_GET['KEY']);
$shooterBULK = intval($_GET['BULK']);

$line = getShooterData($conn, $table, $shooterID, $shooterKEY);

include './components/modal-warning-form.php';
WarningModalForm(
    "success",
    "Registrační e-mail",
    "index.php",
    [
        "shooterID" => $shooterID,
        "shooterKEY" => $shooterKEY,
        "shooterBULK" => $shooterBULK
    ],
    "Opravdu chcete poslat závodníkovi<br> " . htmlspecialchars($line['Jmeno']) . " " . htmlspecialchars($line['Prijmeni']) . " registrační mail?",
    "Znovu pošleme registrační e-mail (informace o závodu,<br>QR kód pro zaplacení,...).",
    "./send.php",
    "bulk_regmail",
    "Odeslat registrační mail"
);