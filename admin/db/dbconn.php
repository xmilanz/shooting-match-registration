<?php
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);

require_once __DIR__ . '/../config/secret/smtp.php';
require_once __DIR__ . '/../config/secret/db.php';

require_once __DIR__ . '/../config/data.php';
require_once __DIR__ . '/../functions.php';

$conn = new mysqli($db_host, $db_login, $db_pass, $db_dtb);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
$conn->set_charset('utf8mb4');

$result = $conn->query("SELECT * from $table_matches where Zavod_id='$table' limit 1");
if ($result->num_rows > 0) {
    $match_data = $result->fetch_array();
}
