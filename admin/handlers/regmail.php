<?php
$line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);

$disciplina = $line['Disciplina'];
$varsymbol = $line['VarSym'];
$isVIP = in_array($line['Staff'], ['VIP', 'RO', 'POM']);

$link_cancel = "<a href='$web_adresa_admin/zrus_ucast.php?id=$line[Cislo]&klic=$line[klic]'><strong>zrušit účast</strong></a>";

$line['Staff'] == "RO" ? $Rozhodci = "ANO" : $Rozhodci = "NE";
$line['Staff'] == "POM" ? $Pomocnik = "ANO" : $Pomocnik = "NE";

// Uprava terminu zaplaceni závodníka, co je zaregistrovan mene nez Zavod_pocet_dni_na_platbu dni pred prematchem
$datumZavod = new DateTime($match_data['Zavod_datum']);
$datumPrematch = (clone $datumZavod)->modify("-1 days");

$datumRegistraceZavodnika = new DateTime();
$datumRegistraceZavodnika->setTimestamp($line['DatReg']);

if ($datumRegistraceZavodnika >= $datumPrematch->modify("-$match_data[Zavod_pocet_dni_na_platbu] days")) {
    $paymentDeadline = $datumZavod->modify("-2 days")->format('d.m.Y');
} else {
    $paymentDeadline = (clone $datumRegistraceZavodnika)->modify("+$match_data[Zavod_pocet_dni_na_platbu] days")->format('d.m.Y');
}

// podmínky pro volbu textu v závislosti na statutu závodníka
if ($isVIP) {
    $message = $email_registrace_bez_platby_text;
} else if ($line['ZaplatiNaMiste'] == "1") {
    $message = $email_registrace_platba_na_miste;
} else if ($match_data['Payment_before'] == "1") {
    $message = $email_registrace_platba_text;
} else {
    $message = $email_registrace_zavod_bez_platby_predem_text;
}

// priprava e-mailu zavodnikovi
// ziskame castku za jednu disciplinu
$FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
$FeeStmt->execute();
$feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$FeeStmt->close();

// nice názvy pro mail
$nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

$STRELEC = "Závodník:" . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel]\r\n";
$STRELEC .= "Kategorie: " . htmlspecialchars($line['Kategorie'], ENT_QUOTES, 'UTF-8') . "\r\n";
$STRELEC .= "Disciplína: $nazev_discipliny" . "\r\n\r\n";
$STRELEC .= "<i>Rozhodčí: $Rozhodci" . "\r\n";
$STRELEC .= "Pomocník: $Pomocnik</i>" . "\r\n\r\n";
$STRELEC .= "Poznámka: " . htmlspecialchars($line['Poznamka'], ENT_QUOTES, 'UTF-8') . "</i>" . "\r\n";

$qrParams = [
    'accountNumber' => $match_data['Banka_ucet_cislo'],
    'bankCode'      => $match_data['Banka_ucet_kod'],
    'amount'        => $feeValues[0]['Value'],
    'currency'      => $match_data['Banka_ucet_MENA'],
    'vs'            => $varsymbol,
    'message'       => $match_data['Zavod'],
    'size'          => 100
];
$qr_link = 'https://api.paylibo.com/paylibo/generator/czech/image?' . http_build_query($qrParams);

$from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
$from = $match_data['Zavod_email_from'];
$to = $line['Mail'];
$subject = "Registrace " . $match_data['Zavod'];

$message = str_replace("##STRELEC##", $STRELEC, $message);
$message = str_replace("##VAR_SYMBOL##", $varsymbol, $message);
$message = str_replace("##CASTKA##", number_format($feeValues[0]['Value'], 2, ',', ' '), $message);
$message = str_replace("##QR_LINK##", $qr_link, $message);
$message = str_replace("##DatPay##", $paymentDeadline, $message);

$send_email = email($from_text, $from, $to, $subject, $message);
if (!$send_email) {
    include './components/modal-warning.php';
    WarningModal(
        "danger",
        "Chyba odeslání e-mailu",
        "index.php",
        "Při odeslání e-mailu závodníkovi došlo k chybě.",
        "Pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
        "Zpět do administrace"
    );
} else {
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Registrační e-mail byl odeslán.',
        'duration' => 2500
    ];

    header("refresh:0;url=index.php");
    // informace o e-mailu zaslaném z administrace se do databaze nezapisuje
}
