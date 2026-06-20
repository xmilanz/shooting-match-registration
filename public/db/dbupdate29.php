<?php
/*
  MilanZ 
  evidence názvu a čísla zbraně
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Zavod_nazev_zbrane'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE $table_matches 
        ADD COLUMN `Zavod_nazev_zbrane` tinyint(1) DEFAULT 0 
        AFTER `Zavod_cislo_zbrane`
    ");
    if (!$result) {
        die("MySQL error 2.9: " . $conn->error);
    }
}
$result = $conn->query("
    ALTER TABLE $table 
    ADD COLUMN `NazevZbrane` varchar(255) DEFAULT NULL 
    AFTER `CisloZbrane`
");
if (!$result) {
    die("MySQL error 2.9: " . $conn->error);
}
/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValueI='2.9'
    WHERE parName='dbver'
");

if (!$result) {
    die("MySQL error 2.9: " . $conn->error);
}
?>