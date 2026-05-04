<?php

/*
   MilanZ 
   Tenolix CUP
 */
$result = $conn->query("ALTER TABLE $table 
                                   ADD COLUMN `Rocnik` tinyint(4) DEFAULT NULL AFTER `Jmeno`,
                                   ADD COLUMN `ZodpovednaOsoba` varchar(155) DEFAULT NULL AFTER `Rocnik`,
                                   ADD COLUMN `Trenink` tinyint(1) DEFAULT 0 AFTER `ZodpovednaOsoba`,
                                   ADD COLUMN `Klub` varchar(255) DEFAULT NULL AFTER `Region`;
                                   ");

/* aktualizace verze databaze */
$result = $conn->query("update $table_setting set parValueI=3.1 where parName='dbver';");

?>
