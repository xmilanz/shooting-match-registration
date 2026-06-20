<?php
/*
   MilanZ 
   MCR K4M
 */

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'Ulice'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `Ulice` varchar(255) DEFAULT NULL 
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
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'Mesto'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `Mesto` varchar(155) DEFAULT NULL 
        AFTER `Ulice`
    ");

    if (!$result) {
        die("MySQL error: " . $conn->error);
    }
}


$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'PSC'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `PSC` int(5) DEFAULT 0 
        AFTER `Mesto`
    ");

    if (!$result) {
        die("MySQL error: " . $conn->error);
    }
}

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'Mesto'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `Mesto` varchar(155) DEFAULT NULL 
        AFTER `Ulice`
    ");

    if (!$result) {
        die("MySQL error: " . $conn->error);
    }
}

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'CMMS'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `CMMS` tinyint(1) DEFAULT NULL 
        AFTER `Trenink`
    ");
    if (!$result) {
        die("MySQL error: " . $conn->error);
    }
}

/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValueI='3.2'
    WHERE parName='dbver'
");

if (!$result) {
    die("MySQL error 3.2: " . $conn->error);
}
?>
