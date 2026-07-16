<?php
/*
  MilanZ 
  v registraci je v menu možné skrýt přehledy a závodníky
*/
$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Web_zobrazovat_prehledy'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_matches`
        ADD COLUMN `Web_zobrazovat_prehledy` TINYINT(1) DEFAULT 1
        AFTER `Web_zobrazovat_discipliny`
    ");
    if (!$result) {
        die(" 3.4: " . $conn->error);
    }
}

$check = $conn->query("
    SELECT 1
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE()
    AND TABLE_NAME = '$table_matches'
    AND COLUMN_NAME = 'Web_zobrazovat_zavodniky'
");
if ($check->num_rows == 0) {
    $result = $conn->query("
        ALTER TABLE `$table_matches`
        ADD COLUMN `Web_zobrazovat_zavodniky` TINYINT(1) DEFAULT 1
        AFTER `Web_zobrazovat_prehledy`
    ");
    if (!$result) {
        die(" 3.4: " . $conn->error);
    }
}

/* aktualizace verze databaze */
$result = $conn->query("
    UPDATE $table_setting
    SET parValue='3.4'
    WHERE parName='dbver'
");

if (!$result) {
    die("MySQL error 3.4: " . $conn->error);
}
?>