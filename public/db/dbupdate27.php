<?php
/*
  MilanZ 
  v registraci je v menu možné skrýt výsledky
*/

$result = $conn->query("ALTER TABLE $table_matches ADD `Web_zobrazovat_vysledky` tinyint(1) DEFAULT 0 AFTER `Web_zobrazovat_situace`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.7 where parName='dbver';");

?>
