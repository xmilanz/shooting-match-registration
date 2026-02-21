<?php
/*
  evidence drzitel zbrojniho opravneni
*/

$result = $conn->query("ALTER TABLE $table ADD `ZbrojniOpravneni` varchar(3) DEFAULT NULL AFTER `ObcanskyPrukaz`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_nastaveni set parValueI=2.5 where parName='dbver';");
?>
