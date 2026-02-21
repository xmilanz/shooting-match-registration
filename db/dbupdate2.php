<?php
/*
  přidání možnosti hromadné registrace do více disciplín
*/

$result = $conn->query("ALTER TABLE match_config ADD `Zavod_registrace_hromadna` varchar(3) CHARACTER SET utf8 COLLATE utf8_general_ci AFTER `Zavod_registrace_pozastaveno`");

/* aktualizace verze databáze */
$result = $conn->query("update $table_nastaveni set parValueI=2 where parName='dbver';");

?>
