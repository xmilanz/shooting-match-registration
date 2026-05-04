<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/config/data.php';
require_once __DIR__ . '/db/dbconn.php';
require_admin();

$shooterID = intval($_GET['ID']);
$shooterKEY = intval($_GET['KEY']);

$line = getShooterData($conn, $table, $shooterID, $shooterKEY);
$nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

include './components/modal-warning-form.php';
WarningModalForm(
    "danger",
    "Mazání závodníka",
    "index.php",
    [
        "shooterID" => $shooterID,
        "shooterKEY" => $shooterKEY
    ],
    "Opravdu chcete smazat závodníka " . htmlspecialchars($line['Jmeno']) . " " . htmlspecialchars($line['Prijmeni']) . " (" . $nazev_discipliny . ")?",
    "Tato akce je nevratná, rozmyslete si to pořádně!!!",
    "./save.php",
    "delete_shooter",
    "Smazat závodníka"
);