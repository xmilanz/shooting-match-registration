<?php
/*
  MilanZ 
  registrace do směn
*/

$result = $conn->query("ALTER TABLE `$table` ADD `Stav` int(4) DEFAULT NULL AFTER `Jmeno`;");
$result = $conn->query("ALTER TABLE $table_matches ADD `Pocet_smen` int(2) NULL AFTER `Zavod_stages`;");
$result = $conn->query("ALTER TABLE $table_matches ADD `Zavod_registrace_smeny` tinyint(1) DEFAULT 0 AFTER `Zavod_registrace_hromadna`;");
$result = $conn->query("ALTER TABLE `$table_disciplines` 
                                        ADD COLUMN `Shift_from` TINYINT UNSIGNED, 
                                        ADD COLUMN `Shift_to` TINYINT UNSIGNED;
                    ");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=3.0 where parName='dbver';");

?>