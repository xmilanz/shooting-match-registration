<?php
/*
  MilanZ 
  evidence názvu zbraně
*/

$result = $conn->query("ALTER TABLE $table_matches ADD `Zavod_nazev_zbrane` tinyint(1) DEFAULT 0 AFTER `Zavod_cislo_zbrane`;");
$result = $conn->query("ALTER TABLE $table ADD `NazevZbrane` varchar(255) DEFAULT NULL AFTER `CisloZbrane`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.9 where parName='dbver';");

?>
