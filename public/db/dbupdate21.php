<?php
/*
  MilanZ 
  přidání možnosti hromadné registrace do více disciplín
*/

$result = $conn->query("ALTER TABLE $table_matches ADD `Zavod_registrace_hromadna` tinyint(1) DEFAULT 0 AFTER `Zavod_registrace_pozastaveno`");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.1 where parName='dbver';");

?>
