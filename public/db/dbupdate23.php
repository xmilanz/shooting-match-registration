<?php
/*
  MilanZ 
  ulozeni castky k zaplaceni v databazi
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'CastkaZaplatit'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE $table 
        ADD `CastkaZaplatit` int(3) 
        AFTER `DatPay`
    ");
    if (!$result) {
        die("MySQL error 2.3: " . $conn->error);
    }
}
/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValue='2.3'
    WHERE parName='dbver'
");
if (!$result) {
    die("MySQL error 2.3: " . $conn->error);
}
?>