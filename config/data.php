<?php
$table = "nazev_tabulky";

$table_nastaveni=$table."_nastaveni";
$table_config=$table."_config";
$table_disciplines=$table."_disciplines";
$table_fee=$table."_fee";

$druh_souteze ="stručný popis soutěže";

$admin_roles = array(
    "admin" => "přístup ke všem funkcím registračního systému",
    "editor" => "nastavení závodu; správa závodníků; disciplin, disciplín; tisk startovních listin; export prezencni listiny",
    "viewer" => "zobrazení informací o závodníkovi; tisk startovních seznamu; export prezencni listiny"
);

$db_host="host";
$db_login="login";
$db_pass="password";
$db_dtb="table";

// smtp autorizace
$smtp_username="username";
$smtp_password="password";
$smtp_server="server";
// smtp autorizace

$web_adresa = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$web_adresa_admin = str_replace('admin', '', $web_adresa);

$vyvojar = "webdesign@milanz.org";
?>