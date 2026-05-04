<?php
/*
  MilanZ 
  evidence obcanskeho prukazu - drzitel zbrojniho opravneni
*/

$result = $conn->query("ALTER TABLE $table ADD `ZbrojniOpravneni` tinyint(1) DEFAULT NULL AFTER `ObcanskyPrukaz`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.6 where parName='dbver';");

?>
