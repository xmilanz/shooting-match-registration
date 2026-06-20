<?php
/*
   MilanZ 
   Tenolix CUP
 */
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table'
    AND COLUMN_NAME = 'Rocnik'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `Rocnik` int(4) DEFAULT NULL 
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
    AND COLUMN_NAME = 'ZodpovednaOsoba'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `ZodpovednaOsoba` varchar(155) DEFAULT NULL 
        AFTER `Rocnik`
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
    AND COLUMN_NAME = 'Trenink'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `Trenink` tinyint(1) DEFAULT 0 
        AFTER `ZodpovednaOsoba`
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
    AND COLUMN_NAME = 'Klub'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table`
        ADD COLUMN `Klub` varchar(255) DEFAULT NULL 
        AFTER `Region`
    ");
    if (!$result) {
        die("MySQL error: " . $conn->error);
    }
} 

/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValueI='3.1'
    WHERE parName='dbver'
");

if (!$result) {
    die("MySQL error 3.1: " . $conn->error);
}
?>
