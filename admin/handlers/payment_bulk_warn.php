<?php
$bulkId = $_POST['shooterBULK'];
$dnes = date_format(new DateTime(), "d.m.Y H:i");

$stmt = $conn->prepare("
        SELECT Cislo, Jmeno, Prijmeni, Mail, Disciplina, Kategorie, klic, VarSym, DatReg
        FROM $table
        WHERE bulkId = ? AND Zaplaceno = 0 AND Disciplina !='VYRAZENO'
        ORDER BY Cislo
   ");
$stmt->bind_param("i", $bulkId);
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

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
$cisla = [];
$nazev_kategorie = getValueFromTable($conn, $table_categories, "Name", $rows[0]['Kategorie'], "Value");

// Pro každý řádek aktualizuj urgenci
$updStmt = $conn->prepare("
        UPDATE $table
        SET Urgence = ?
        WHERE Cislo = ?
    ");
foreach ($rows as $i => $row) {
    $updStmt->bind_param("ss", $dnes, $row['Cislo']);
    $updStmt->execute();

    $cislo = $row['Cislo'];
    $klic  = $row['klic'];
    $disc  = $row['Disciplina'];
    $nazev = getValueFromTable($conn, $table_disciplines, "Name", $disc, "Value");

    $link = "<a href='" . htmlspecialchars($web_adresa, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($klic) . "'>Zrušit účast</a>";

    if ($row && isset($row['Cislo'])) {
        $cisla[] = $row['Cislo'];
    }

    $cisla_disc_odkazy[] = [
        'cislo' => $cislo,
        'nazev' => $nazev,
        'link'  => $link
    ];
}
$updStmt->close();
header("refresh:0;url=index.php");

// příprava mailu zavodnikovi
$varsymbol = $rows[0]['VarSym'];

$datumZavod = new DateTime($match_data['Zavod_datum']);
$datumPrematch = (clone $datumZavod)->modify("-1 days");
$datumRegistraceZavodnika = new DateTime();
$datumRegistraceZavodnika->setTimestamp($rows[0]['DatReg']);

if ($datumRegistraceZavodnika >= $datumPrematch->modify("-$match_data[Zavod_pocet_dni_na_platbu] days")) {
    $paymentDeadline = $datumZavod->modify("-2 days")->format('d.m.Y');
} else {
    $paymentDeadline = (clone $datumRegistraceZavodnika)->modify("+$match_data[Zavod_pocet_dni_na_platbu] days")->format('d.m.Y');
}

// ziskame castky za jednotlive discipliny
$FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
$FeeStmt->execute();
$feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$FeeStmt->close();

$STRELEC = "Závodník " . htmlspecialchars($rows[0]['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($rows[0]['Prijmeni'], ENT_QUOTES, 'UTF-8') . "[$link_ical]\r\n";
$STRELEC .= "Kategorie: $nazev_kategorie \r\n\r\n";
$STRELEC .= "Disciplíny:\r\n";
foreach ($cisla_disc_odkazy as $i => $r) {
    if ($i === 0) {
        $castka = $feeValues[0]['Value'];
    } elseif ($i === 1) {
        $castka = $feeValues[1]['Value'];
    } else {
        $castka = $feeValues[2]['Value'];
    }
    $STRELEC .= "- " . $r['nazev'] . " (" . number_format($castka, 2, ',', ' ') . " " . $match_data['Banka_ucet_MENA'] . ") [" . $r['link'] . "]\r\n";
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
$subject = "Chybějící platba " . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8');

$message = $email_urgence_hromadna_registrace_platba_text;
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
        'type' => 'warning',
        'message' => 'Urgence hromadné platby byla odeslána.',
        'duration' => 2500
    ];
    header("refresh:0;url=index.php");
}
