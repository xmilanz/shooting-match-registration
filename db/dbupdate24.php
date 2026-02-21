<?php
/*
  evidence obcanskeho prukazu (legislativa 2026)
*/

$result = $conn->query("ALTER TABLE match_config ADD `Zavod_obcansky_prukaz` varchar(3) DEFAULT 'on' AFTER `Zavod_more_divisions`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_nastaveni set parValueI=2.4 where parName='dbver';");

?>
