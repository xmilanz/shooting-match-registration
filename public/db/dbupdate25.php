<?php
/*
  MilanZ 
  evidence obcanskeho prukazu (viz legislativa 2026)
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Zavod_obcansky_prukaz'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_matches`
        ADD COLUMN `Zavod_obcansky_prukaz` TINYINT(1) DEFAULT 1
        AFTER `Zavod_more_divisions`
    ");
    if (!$result) {
        die("MySQL error 2.5: " . $conn->error);
    }
}
/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValue='2.5'
    WHERE parName='dbver'
");
if (!$result) {
    die("MySQL error 2.5: " . $conn->error);
}
?>