<?php
/*
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
*/
require_once __DIR__ . '/../config/secret/smtp.php';
require_once __DIR__ . '/../config/secret/db.php';

require_once __DIR__ . '/../config/data.php';
require_once __DIR__ . '/../functions.php';

$conn = new mysqli($db_host, $db_login, $db_pass, $db_dtb);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

ensureTable($conn, $table_setting, 'setting', $table . '_setting');
ensureTable($conn, $table, 'main', $table);
ensureTable($conn, $table_matches, 'match_config', $table_matches);
ensureTable($conn, $table_disciplines, 'disciplines', $table . '_disciplines');
ensureTable($conn, $table_fee, 'fee', $table . '_fee');
ensureTable($conn, $table_admins, 'site_admins', $table_admins);

$installed =
    ensureTable($conn, $table_setting,     'setting',     $table . '_setting') |
    ensureTable($conn, $table,             'main',        $table) |
    ensureTable($conn, $table_matches,     'match_config',$table_matches) |
    ensureTable($conn, $table_disciplines, 'disciplines', $table . '_disciplines') |
    ensureTable($conn, $table_fee,         'fee',         $table . '_fee') |
    ensureTable($conn, $table_admins,      'site_admins', $table_admins);

if ($installed) {
    echo "Instalace dokončena. Pokračujte klávesou F5.";
    exit;
}

$result = $conn->query("SELECT * from $table_matches where Zavod_id='$table' limit 1");
if ($result->num_rows > 0) {
    $match_data = $result->fetch_array();
}

// insert záznamu pro $table do $table_matches, pokud ještě neexistuje
$safeTableId = $conn->real_escape_string($table);
$check = $conn->query("SELECT Zavod_id FROM $table_matches WHERE Zavod_id='$safeTableId'");
if ($check && $check->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO $table_matches (Zavod_id) VALUES (?)");
    $stmt->bind_param('s', $table);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("UPDATE $table_matches SET Zavod_datum = DATE_FORMAT(CURDATE(),'%d.%m.%Y') WHERE Zavod_id = ?");
    $stmt->bind_param(
        's',
        $table
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
}

$migrations = [
    '2.1' => 'dbupdate21.php',
    '2.2' => 'dbupdate22.php',
    '2.3' => 'dbupdate23.php',
    '2.4' => 'dbupdate24.php',
    '2.5' => 'dbupdate25.php',
    '2.6' => 'dbupdate26.php',
    '2.7' => 'dbupdate27.php',
    '2.8' => 'dbupdate28.php',
    '2.9' => 'dbupdate29.php',
    '3.0' => 'dbupdate30.php',
    '3.1' => 'dbupdate31.php',
    '3.2' => 'dbupdate32.php',
    '3.3' => 'dbupdate33.php',
];

$res = $conn->query("SELECT parValueI FROM $table_setting WHERE parName='dbver' LIMIT 1");
$row = $res ? $res->fetch_assoc() : null;
$currentVersion = $row['parValueI'] ?? '0';

foreach ($migrations as $version => $script) {
    if (version_compare($currentVersion, $version, '<')) {
//        echo "Migrace z $currentVersion na $version<br>";
        require $script;
    }
}
?>