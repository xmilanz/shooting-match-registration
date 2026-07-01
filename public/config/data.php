<?php
$table = "dev_ssas_match";
$table_setting = $table . "_setting";

$table_setting = $table . "_setting";
$table_admins = "";

$reg_url = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']); // pro zobrazení v emailu (link na odhlášení ze závodu) musí být absolutní URL

// URL administrace závodu, např. https://admin.domena.cz/index.php
$admin_url = "";

// podmínky pro sdílené tabulky určitého druhu závodu - příklady
if (strpos($table, 'k4m') !== false) {
    $table_disciplines = "ssas_k4m_disciplines";
    $table_categories = "ssas_k4m_categories";
    $table_fee = "ssas_k4m_fee";
    $druh_souteze = "Malorážkový závod na myslivecké terče (liška, srnec, kamzík, prase), tzv. velký standard";
}
elseif (strpos($table, 'manevry') !== false) {
    $table_disciplines = "ssas_manevry_disciplines";
    $table_fee = "ssas_manevry_fee";
    $druh_souteze = "Soutěž jednotlivců ve střelbě z velkorážné pušky, karabiny na pistolové náboje, malorážky a velké i malorážné pistole nebo revolveru";
}
else {
    $table_disciplines=$table."_disciplines";
    $table_fee=$table."_fee";
    $druh_souteze ="stručný popis soutěže";
}

$table_categories = "ssas_categories";


$admin_roles = array(
    "admin" => "přístup ke všem funkcím registračního systému",
    "editor" => "nastavení závodu; správa závodníků; squadů, kategorií, divizí a disciplín; tisk startovních listin; export seznamu",
    "viewer" => "zobrazení informací o závodníkovi; tisk startovních listin; export seznamu"
);

$vyvojar = "webdesign@milanz.org";
?>