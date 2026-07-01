<?php
/*
  MilanZ 
  pri platbe zovodu na miste lze vlozit do reg emailu text s QR pro volitelnou platbu predem
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Zavod_platba_volitelna'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE $table_matches 
        ADD COLUMN `Zavod_platba_volitelna` tinyint(1) DEFAULT 0 
        AFTER `Zavod_zobrazovat_sponzory`
    ");
    if (!$result) {
        die("MySQL error 2.8: " . $conn->error);
    }
}
/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValue='2.8'
    WHERE parName='dbver'
");
if (!$result) {
    die("MySQL error 2.8: " . $conn->error);
}
?>