<?php
/*
  unikatni klic pro hromadnou platbu
*/

$result = $conn->query("ALTER TABLE $table ADD `bulkId` int(10) AFTER `klic`");

/* aktualizace verze databáze */
$result = $conn->query("update $table_nastaveni set parValueI=2.1 where parName='dbver';");

?>
