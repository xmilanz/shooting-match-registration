<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';
require_once __DIR__ . '/config/mail_texty.php';
require_admin();


$stmt = $conn->prepare("
SELECT * FROM $table_matches
      WHERE Zavod_id = ?
   ");
$stmt->bind_param(
    "s",
    $table
);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$match_data = mysqli_fetch_array($result);

// REGISTRACNI MAIL ODESLANY Z ADMINISTRACE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['regmail'])) {
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
}


// HROMADNY REGISTRACNI MAIL ODESLANY Z ADMINISTRACE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_regmail'])) {
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

        $link = "<a href='" . htmlspecialchars($web_adresa_admin, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($klic) . "'>Zrušit účast</a>";

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


    $STRELEC = "Závodník: " . htmlspecialchars($rows[0]['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($rows[0]['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Kategorie: " . htmlspecialchars($row['Kategorie'], ENT_QUOTES, 'UTF-8') . "\r\n\r\n";
    $STRELEC .= "Disciplíny:\r\n";
    foreach ($cisla_disc_odkazy as $i => $r) {
        //        if (($staff == "RO") or ($staff == "POM")) { 
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
}


// URGENCE PLATBY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['payment_warn'])) {
    $line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);

    $squad = $line['Squad'];
    $varsymbol = $line['VarSym'];

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

    // priprava podkladu pro e-mail zavodnikovi
    // ziskame castku za jednu disciplinu
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    // nice názvy pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
    // nice názvy pro mail

    $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel]\r\n";
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
}


// URGENCE HROMADNE PLATBY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_payment_warn'])) {
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

    $STRELEC = "Závodník " . htmlspecialchars($rows[0]['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($rows[0]['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Kategorie: " . htmlspecialchars($rows[0]['Kategorie'], ENT_QUOTES, 'UTF-8') . "\r\n\r\n";
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
}
