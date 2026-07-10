<?php
$bulkId = $_POST['shooterBULK'];

$stmt = $conn->prepare("
        SELECT Cislo, Jmeno, Prijmeni, Staff, Mail, Disciplina, Kategorie, klic, VarSym, DatReg, DatPay, Poznamka
        FROM $table
        WHERE bulkId = ? AND Disciplina !='VYRAZENO'
        ORDER BY Cislo
   ");
$stmt->bind_param("i", $bulkId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// ziskame castky za jednotlive discipliny
$FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
$FeeStmt->execute();
$feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$FeeStmt->close();


if (empty($rows)) {
    $_SESSION['toast'] = [
        'type'    => 'danger',
        'message' => 'Nebyla nalezena žádná hromadná platba.',
        'duration' => 2500
    ];
    header("Location: /");
    exit;
}
$cisla_disc_odkazy = [];
// Pro každý záznam získáme potřebné údaje
foreach ($rows as $i => $row) {
    $cislo = $row['Cislo'];
    $klic  = $row['klic'];
    $disc  = $row['Disciplina'];
    $poznamka  = $row['Poznamka'];
    $nazev = getValueFromTable($conn, $table_disciplines, "Name", $disc, "Value");

    $link = "<a href='" . htmlspecialchars($web_adresa_admin, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($klic) . "'>zrušit účast</a>";

    $cisla_disc_odkazy[] = [
        'cislo' => $cislo,
        'nazev' => $nazev,
        'link'  => $link,
        'poznamka'  => $poznamka
    ];
}
// příprava mailu zavodnikovi
$varsymbol = $rows[0]['VarSym'];
$paymentDeadline = $rows[0]['DatPay'];
$datumRegistraceZavodnika = new DateTime();
$datumRegistraceZavodnika->setTimestamp($rows[0]['DatReg'])->format('d.m.Y');
$isVIP = in_array($rows[0]['Staff'], ['VIP', 'RO', 'POM']);

// nice názvy pro mail
$nazev_kategorie = getValueFromTable($conn, $table_categories, "Name", $row['Kategorie'], "Value");
$link_ical = buildCalendarLinks($web_adresa_admin, $match_data);

$STRELEC = "Závodník: " . htmlspecialchars($rows[0]['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($rows[0]['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_ical] " . "\r\n";
$STRELEC .= "Kategorie: $nazev_kategorie \r\n\r\n";
$STRELEC .= "Disciplíny:\r\n";
foreach ($cisla_disc_odkazy as $i => $r) {
    if ($isVIP) {
        $castka = 0;
    } elseif ($i === 0) {
        $castka = $feeValues[0]['Value'];
    } elseif ($i === 1) {
        $castka = $feeValues[1]['Value'];
    } else {
        $castka = $feeValues[2]['Value'];
    }
    $STRELEC .= "&nbsp;&nbsp;- " . $r['nazev'] . "  (" . number_format($castka, 2, ',', ' ') . " " . $match_data['Banka_ucet_MENA'] . ") [" . $r['link'] . "]\r\n";
    $STRELEC .= "&nbsp;&nbsp;- Poznámka: " . $r['poznamka'] . "</i>" . "\r\n\r\n";
}

//vypocet celkove castky
$discCount = count($rows);
if ($discCount === 1) {
    $celkovaCastka = $feeValues[0]['Value'];
} elseif ($discCount === 2) {
    $celkovaCastka = ($feeValues[0]['Value'] + $feeValues[1]['Value']);
} else {
    $celkovaCastka = ($feeValues[0]['Value'] + $feeValues[1]['Value']) + (($discCount - 2) * $feeValues[2]['Value']);
}

$qrParams = [
    'accountNumber' => $match_data['Banka_ucet_cislo'],
    'bankCode'      => $match_data['Banka_ucet_kod'],
    'amount'        => $celkovaCastka,
    'currency'      => $match_data['Banka_ucet_MENA'],
    'vs'            => $varsymbol,
    'message'       => $match_data['Zavod'],
    'size'          => 100
];
$qr_link = 'https://api.paylibo.com/paylibo/generator/czech/image?' . http_build_query($qrParams);

$from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
$from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
$to = $rows[0]['Mail'];
$subject = "Registrace " . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8');

// podmínky pro volbu textu v závislosti na statutu závodníka
if ($line['ZaplatiNaMiste'] == "1") {
    $message = $email_registrace_platba_na_miste;
} elseif ($match_data['Payment_before'] == 1) {
    $message = $email_hromadna_registrace_platba_text;
} else {
    $message = $email_hromadna_registrace_zavod_bez_platby_predem_text;
}

$message = str_replace("##STRELEC##", $STRELEC, $message);
$message = str_replace("##VAR_SYMBOL##", $varsymbol, $message);
$message = str_replace("##CASTKA##", number_format($celkovaCastka, 2, ',', ' '), $message);
$message = str_replace("##QR_LINK##", $qr_link, $message);
$message = str_replace("##DatReg##", $datumRegistraceZavodnika->format('d.m.Y'), $message);
$message = str_replace("##DatPay##", $paymentDeadline, $message);
$send_email = email($from_text, $from, $to, $subject, $message);

if (!$send_email) {
    include './components/modal-warning.php';
    WarningModal(
        "danger",
        "Chyba odeslání e-mailu",
        "index.php",
        "Při odeslání e-mailu závodníkovi došlo k chybě.",
        "Závodník byl zaregistrován, pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
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
