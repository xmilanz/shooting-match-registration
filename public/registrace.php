<?php
include "./header.php";
$_SESSION['token'] = bin2hex(random_bytes(32));

$casRegistraceKonec = $match_data['Zavod_cas_registrace_konec'];
$casRegistraceZacatek = $match_data['Zavod_cas_registrace_zacatek'];

$dnes = new DateTime();

$datumZavod = new DateTime($match_data['Zavod_datum']);

$datumZacatekRegistrace = (clone $datumZavod)
    ->modify("-{$match_data['Zavod_zacatek_registrace']} days")
    ->setTime(...explode(':', $casRegistraceZacatek));

$datumKonecRegistrace = (clone $datumZavod)
    ->modify("-{$match_data['Zavod_konec_registrace']} days")
    ->setTime(...explode(':', $casRegistraceKonec));

$reg_started = false;
$reg_text = "";

if ($match_data['Zavod_registrace_pozastaveno'] == 1) {
    $reg_text = "<span class='text-danger'>Registrace je pozastavená</span>";
} else if ($dnes > $datumKonecRegistrace) {
    $reg_text = "Registrace skončila " . $datumKonecRegistrace->format('j.n.Y H:i') . " ";
} else if ($dnes < $datumZacatekRegistrace) {
    $reg_text = "Registrace bude spuštěna " . $datumZacatekRegistrace->format('j.n.Y H:i') . " ";
} else {
    $reg_started = true;
    $reg_text = "Registrace bude ukončena " . $datumKonecRegistrace->format('j.n.Y H:i') . " ";
}

// Příznak: je registrace aktivní?
$regAktivni = $reg_started
    && $dnes < $datumKonecRegistrace
    && $match_data['Zavod_registrace_pozastaveno'] == 0;
?>
<h2>
    <?= $reg_text ?>
</h2>

<?php

if (stripos($match_data['Zavod'], 'tenolix') !== false) { 
    include_once __DIR__ . '/include/registrace_tenolix.php';
} else if ((stripos($match_data['Zavod'], 'mistrovství') !== false) || (stripos($match_data['Zavod'], 'mčr') !== false ) || (stripos($match_data['Zavod'], 'celostátní') !== false)) { 
    include_once __DIR__ . '/include/registrace_mcr.php';
} else if ($match_data['Zavod_registrace_hromadna']) {
    include_once __DIR__ . '/include/registrace_bulk.php';
} else if ($match_data['Zavod_registrace_smeny']) {
    include_once __DIR__ . '/include/registrace_shifts.php';
} else {
    include_once __DIR__ . '/include/registrace_single.php';
}?>

<script type="text/javascript" src="./js/reg_form.js"></script>

<?php
include "./footer.php";
?>