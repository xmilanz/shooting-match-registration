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
    "Evidence hromadné úhrady startovného",
    "index.php",
    [
        "shooterID" => $shooterID,
        "shooterKEY" => $shooterKEY,
        "shooterBULK" => $shooterBULK
    ],
    "Závodník " . htmlspecialchars($line['Jmeno']) . " " . htmlspecialchars($line['Prijmeni']) . " zaplatil startovné všech disciplín.",
    "Zaevidujeme platbu a pošleme závodníkovi potvrzení.",
    "./save.php",
    "mark_bulk_payment",
    "Zaevidovat hromadnou platbu"
);