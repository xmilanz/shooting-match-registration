<?php
/*
  MilanZ 
  unikatni klic pro hromadnou platbu
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'bulkId'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE $table 
        ADD `bulkId` int(10) DEFAULT NULL 
        AFTER `klic`
    ");
}
if (!$result) {
    die("MySQL error 2.2: " . $conn->error);
}
/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValue='2.2'
    WHERE parName='dbver'
");
if (!$result) {
    die("MySQL error 2.2: " . $conn->error);
}
