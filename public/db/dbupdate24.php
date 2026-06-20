<?php
/*
  MilanZ 
  pri platbe zavodu na miste lze vlozit do reg emailu text s QR pro volitelnou platbu predem
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
        AFTER `Zavod_zobrazovat_sponzory`
    ");
    if (!$result) {
        die("MySQL error 2.4: " . $conn->error);
    }
}
/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValueI='2.4'
    WHERE parName='dbver'
");
if (!$result) {
    die("MySQL error 2.4: " . $conn->error);
}
?>