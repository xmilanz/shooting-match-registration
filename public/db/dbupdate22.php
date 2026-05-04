<?php
/*
  MilanZ 
  unikatni klic pro hromadnou platbu
*/

$result = $conn->query("ALTER TABLE $table ADD `bulkId` int(10) DEFAULT NULL AFTER `klic`;");

/* aktualizace verze databáze */
$result = $conn->query("update $table_setting set parValueI=2.2 where parName='dbver';");

?>
