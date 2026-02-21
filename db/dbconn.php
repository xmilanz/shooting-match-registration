<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once __DIR__ . '/../config/data.php';
require_once __DIR__ . '/../functions.php';

$conn = new mysqli($db_host, $db_login, $db_pass, $db_dtb);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$result = $conn->query("SELECT * from match_config where Zavod_id='$table' limit 1");
if ($result->num_rows > 0) {
    $match_data = $result->fetch_array();
}

ensureTable($conn, $table_nastaveni, 'nastaveni', $table . '_nastaveni');
ensureTable($conn, $table, 'hlavni', $table);
ensureTable($conn, 'match_config', 'match_config');
ensureTable($conn, $table_disciplines, 'disciplines', $table . '_disciplines');
ensureTable($conn, $table_fee, 'fee', $table . '_fee');

// insert záznamu pro $table do match_config, pokud uz neexistuje
$safeTableId = $conn->real_escape_string($table);
$check = $conn->query("SELECT Zavod_id FROM match_config WHERE Zavod_id='$safeTableId'");
if ($check && $check->num_rows === 0) {
    $stmt = $conn->prepare("INSERT INTO match_config (Zavod_id) VALUES (?)");
    $stmt->bind_param("s", $table);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("
		UPDATE match_config
		SET Zavod_datum = DATE_FORMAT(CURDATE(),'%d.%m.%Y')
		WHERE Zavod_id = ?
	");
    $stmt->bind_param(
        "s",
        $table
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
}

$migrations = [
    2 => 'dbupdate2.php',
    3 => 'dbupdate21.php',
    4 => 'dbupdate22.php',
    5 => 'dbupdate23.php',
    6 => 'dbupdate24.php',
    7 => 'dbupdate25.php',
];

$res = $conn->query("SELECT parValueI FROM $table_nastaveni WHERE parName='dbver' LIMIT 1");
$row = $res ? $res->fetch_assoc() : null;
$currentVersion = $row['parValueI'] ?? 0;

foreach ($migrations as $version => $script) {
    if ($currentVersion < $version) {
        require $script;
    }
}
