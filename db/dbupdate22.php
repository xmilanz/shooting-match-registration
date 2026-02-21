<?php
/*
  ulozeni castky k zaplaceni v databazi
*/

$result = $conn->query("ALTER TABLE $table ADD `CastkaZaplatit` int(10) AFTER `DatPay`");

/* aktualizace verze databáze */
$result = $conn->query("update $table_nastaveni set parValueI=2.2 where parName='dbver';");

?>
