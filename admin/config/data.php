<?php
// tabulka, do které se ukládají závodníci - získáváme ze zavod_id
$table = isset($_SESSION['zavod_id']) ? preg_replace('/[^a-zA-Z0-9_]/', '', $_SESSION['zavod_id']) : '';

// tabulka, do které se ukládá konfigurace jednotlivých závodů, např. "match_config"
$table_matches = "";

// tabulka, do které se ukládají administrátoři závodu, např. "site_admins"
$table_admins = "";

// URL registrace závodu, např. https://registrace.domena.cz/index.php
$reg_redirect_url = "";

// podmínky pro sdílené tabulky určitého druhu závodu - příklady
if (strpos($table, 'k4m') !== false) {
    $table_disciplines = "ssas_k4m_disciplines";
    $table_categories = "ssas_k4m_categories";
    $table_fee = "ssas_k4m_fee";
}
elseif (strpos($table, 'odstrelovacka') !== false) {
    $table_disciplines = "ssas_odstrelovacka_disciplines";
    $table_fee = "ssas_odstrelovacka_fee";
}
else {
    $table_disciplines=$table."_disciplines";
    $table_fee=$table."_fee";
}

$table_categories = "ssas_categories";

$admin_roles = array(
    "admin" => "přístup ke všem funkcím registračního systému",
    "editor" => "nastavení závodu; správa závodníků; squadů, kategorií, divizí a disciplín; tisk startovních listin; export seznamu",
    "viewer" => "zobrazení informací o závodníkovi; tisk startovních listin; export seznamu"
);

// omezení přístupu admistrátora pro různé závody - příklady
$zavody_prefix = [
    'prachatice' => 'ssas',
    'pelhrimov'  => 'pelhrimov',
    'all'        => '', // zobrazení všech závodů
];

// zpracovani adres zavodu - příklady
$custom_urls = [
    'ssas_odstrelovacka_1_2026' => 'odstrelovacka-1-kolo',
    'ssas_odstrelovacka_2_2026' => 'odstrelovacka-2-kolo',
];


function tableToUrl(string $table, array $custom_urls): string
{
    // výjimky mají přednost
    if (isset($custom_urls[$table])) {
        return $custom_urls[$table];
    }

    // standardní scénář - prefix tabulky závodu podle pořadatele
    return preg_replace(
        ['/^ssas_/', '/_\d{4}$/', '/_/'],
        ['', '', '-'],
        $table
    );
}

$web_adresa = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']);
$web_adresa_admin = str_replace('admin', 'registrace', $web_adresa);

$slug = tableToUrl($table, $custom_urls);

$web_adresa_admin .= $slug;
$web_adresa_admin .= "/";

$vyvojar = "webdesign@milanz.org";
?>