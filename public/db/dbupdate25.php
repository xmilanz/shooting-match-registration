<?php
/*
  MilanZ 
  evidence obcanskeho prukazu (viz legislativa 2026)
*/

$result = $conn->query("ALTER TABLE $table_matches ADD `Zavod_obcansky_prukaz` tinyint(1) DEFAULT '1' AFTER `Zavod_more_divisions`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.5 where parName='dbver';");

?>
