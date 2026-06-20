<?php
/*
  MilanZ 
  registrace do směn
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'Stav'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `Stav` int(4) DEFAULT NULL 
        AFTER `Jmeno`
    ");

    if (!$result) {
        die("MySQL error: " . $conn->error);
    }
}

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Pocet_smen'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_matches`
        ADD COLUMN `Pocet_smen` int(2) NULL 
        AFTER `Zavod_stages`
    ");
    if (!$result) {
        die("MySQL error 3.0: " . $conn->error);
    }
}

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Zavod_registrace_smeny'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_matches`
        ADD COLUMN `Zavod_registrace_smeny` tinyint(1) DEFAULT 0
        AFTER `Zavod_registrace_hromadna`
    ");
    if (!$result) {
        die("MySQL error 3.0: " . $conn->error);
    }
}

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_disciplines'
    AND COLUMN_NAME = 'Shift_from'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_disciplines`
        ADD COLUMN `Shift_from` TINYINT UNSIGNED
    ");
    if (!$result) {
        die("MySQL error 3.0: " . $conn->error);
    }
}

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_disciplines'
    AND COLUMN_NAME = 'Shift_to'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_disciplines`
        ADD COLUMN `Shift_to` TINYINT UNSIGNED
        AFTER `Shift_from`
    ");
    if (!$result) {
        die("MySQL error 3.0: " . $conn->error);
    }
}


/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValueI='3.0'
    WHERE parName='dbver'
");
if (!$result) {
    die("MySQL error 3.0: " . $conn->error);
}
?>