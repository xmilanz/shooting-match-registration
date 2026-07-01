<?php
/*
  MilanZ 
  přidání možnosti hromadné registrace do více disciplín
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Zavod_registrace_hromadna'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_matches`
        ADD COLUMN `Zavod_registrace_hromadna` TINYINT(1) DEFAULT 0
        AFTER `Zavod_registrace_pozastaveno`
    ");
    if (!$result) {
        die("MySQL error 2.1: " . $conn->error);
    }
}
/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValue='2.1'
    WHERE parName='dbver'
");
if (!$result) {
    die("MySQL error 2.1: " . $conn->error);
}
?>