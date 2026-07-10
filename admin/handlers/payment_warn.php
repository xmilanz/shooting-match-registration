<?php
$line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);

$squad = $line['Squad'];
$varsymbol = $line['VarSym'];

$link_cancel = buildCancelLinks($web_adresa_admin, $_POST['shooterID'], $_POST['shooterKEY']);
$link_ical = buildCalendarLinks($web_adresa_admin, $match_data);

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

// priprava podkladu pro e-mail zavodnikovi
// ziskame castku za jednu disciplinu
$FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
$FeeStmt->execute();
$feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
$FeeStmt->close();

// nice názvy pro mail
$nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
// nice názvy pro mail

$STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel] [$link_ical]\r\n";
$STRELEC .= "Kategorie: " . htmlspecialchars($line['Kategorie'], ENT_QUOTES, 'UTF-8') . "\r\n";
$STRELEC .= "Disciplína: $nazev_discipliny" . "\r\n\r\n";
$STRELEC .= "<i>Rozhodčí: $Rozhodci" . "\r\n";
$STRELEC .= "Pomocník: $Pomocnik</i>" . "\r\n";

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
$subject = "Chybějící platba " . $match_data['Zavod'];

$message = $email_urgence_platba_text;
$message = str_replace("##STRELEC##", $STRELEC, $message);
$message = str_replace("##VAR_SYMBOL##", $varsymbol, $message);
$message = str_replace("##CASTKA##", number_format($feeValues[0]['Value'], 2, ',', ' '), $message);
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
    $dnes = date_format(new DateTime(), "d.m.Y H:i");
    //zapiseme do DB, kdy byla urgence odeslana
    $stmt = $conn->prepare("
            	UPDATE $table 
		        SET Urgence = ?
		        WHERE Cislo = ? and klic = ?
	            ");
    $stmt->bind_param(
        "sii",
        $dnes,
        $_POST['shooterID'],
        $_POST['shooterKEY']
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    if ($affected == 0) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba databáze",
            "index.php",
            "Při vkládání do databáze došlo k chybě!",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systmu.",
            "Zpět do administrace"
        );
    } else {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'message' => 'Urgence platby byla odeslána.',
            'duration' => 2500
        ];
        header("refresh:0;url=index.php");
    }
}
