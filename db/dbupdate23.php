<?php
/*
  moznost nastavit cas zacatku a konce registrace
*/

$result = $conn->query("
    ALTER TABLE match_config
    ADD `Zavod_cas_registrace_zacatek` VARCHAR(255) DEFAULT '12:00' AFTER `Zavod_cas_registrace`,
    ADD `Zavod_cas_registrace_konec` VARCHAR(255) DEFAULT '17:00' AFTER `Zavod_cas_registrace_zacatek`
");

/* aktualizace verze databáze */
$result = $conn->query("update $table_nastaveni set parValueI=2.3 where parName='dbver';");

?>
