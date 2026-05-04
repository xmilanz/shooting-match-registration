<?php
/*
  MilanZ 
  pri platbe zavodu na miste lze vlozit do reg emailu text s QR pro volitelnou platbu predem
*/

$result = $conn->query("ALTER TABLE $table_matches ADD `Zavod_platba_volitelna` tinyint(1) DEFAULT 0 AFTER `Zavod_zobrazovat_sponzory`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.4 where parName='dbver';");

?>
