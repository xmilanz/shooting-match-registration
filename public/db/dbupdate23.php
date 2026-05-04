<?php
/*
  MilanZ 
  ulozeni castky k zaplaceni v databazi
*/

$result = $conn->query("ALTER TABLE $table ADD `CastkaZaplatit` int(3) AFTER `DatPay`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.3 where parName='dbver';");

?>
