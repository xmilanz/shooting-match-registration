<?php
include "./header.php";

session_start();

// NOVA SINGLE REGISTRACE 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrovat'])) {

    if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
        http_response_code(403);
        exit('Neplatný CSRF token.');
    }
    unset($_SESSION['token']);

    if (!empty($_POST['gender'])) {
        exit('Spam detekován.');
    }
    $jmeno = trim(mb_convert_case($_POST['Jmeno'] ?? '', MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'] ?? '', MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
    $email = trim($_POST['Email'] ?? '');
    $ip = $_SERVER["REMOTE_ADDR"];
    $op = normalizePrukaz($_POST['ObcanskyPrukaz'] ?? '');
    $zo = isset($_POST['ZbrojniOpravneni']) ? 'on' : '';
    $cz = normalizePrukaz(trim($_POST['CZ']) ?? '');
    $poznamka = trim($_POST['Poznamka'] ?? '');
    $staff = $_POST['Staff'] ?? '';
    $kategorie = $_POST['Kategorie'] ?? '';
    $varsymbol = rand(1000, 9999);
    $klic = rand(1000, 9999);

    // ziskame castku za jednu disciplinu
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    $stmt = $conn->prepare("
		INSERT INTO $table 
		(Prijmeni, Jmeno, ObcanskyPrukaz, ZbrojniOpravneni, CisloZbrane, VarSym, Mail, Kategorie, DatReg, RegistraceIP, Disciplina, Staff, klic, CastkaZaplatit, Poznamka, Zavod)
		VALUES (?, ?, NULLIF(?,''), ?, NULLIF(?,''), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	");
    $stmt->bind_param(
        "ssssssssssssiiss",
        $prijmeni,
        $jmeno,
        $op,
        $zo,
        $cz,
        $varsymbol,
        $email,
        $kategorie,
        $_POST['datreg'],
        $ip,
        $_POST['Disciplina'],
        $staff,
        $klic,
        $feeValues[0]['Value'],
        $poznamka,
        $table
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $cislo = $conn->insert_id;

    if ($affected === 0) {
        include './components/modal-warning.php';
        WarningModal(
            "Chyba databáze",
            "registrace.php",
            "Při vkládání do databáze došlo k chybě!",
            "Zkuste to později nebo kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'registrace.php';\">Zpět na registraci</button>"
        );
        exit;
    }

    // posilame potvrzeni registrace a platebni udaje zavodnihovi vcetne  odkazu na spravu ucasti (zruseni)
    $stmt = $conn->prepare("
		SELECT * FROM $table
		WHERE Prijmeni = ? and Jmeno = ? and VarSym = ? and  Mail = ?
	");
    $stmt->bind_param(
        "ssis",
        $prijmeni,
        $jmeno,
        $varsymbol,
        $email
    );
    $stmt->execute();
    $result = $stmt->get_result();
    $line = $result->fetch_assoc();
    $stmt->close();

    $staff == "RO" ? $Rozhodci = "ANO" : $Rozhodci = "NE";
    $staff == "POM" ? $Pomocnik = "ANO" : $Pomocnik = "NE";

    // Uprava terminu zaplaceni závodníka, co se zaregistruje mene nez Zavod_pocet_dni_na_platbu dni pred prematchem
    $datumZavod = new DateTime($match_data['Zavod_datum']);
    $datumPrematch = (clone $datumZavod)->modify("-1 days");
    $datumRegistraceZavodnika = new DateTime();
    $datumRegistraceZavodnika->setTimestamp($line['DatReg']);

    if ($datumRegistraceZavodnika >= $datumPrematch->modify("-$match_data[Zavod_pocet_dni_na_platbu] days")) {
        $paymentDeadline = $datumZavod->modify("-2 days")->format('j.m.Y');
    } else {
        $paymentDeadline = (clone $datumRegistraceZavodnika)->modify("+$match_data[Zavod_pocet_dni_na_platbu] days")->format('j.m.Y');
    }

    $tyden = intval(date("W", strtotime($match_data['Zavod_datum'])));
    $varsymbol_new = sprintf("%02d%04d", $tyden, $cislo);

    $stmt = $conn->prepare("
		UPDATE $table 
		SET VarSym = ?,
		DatPay = ?
		WHERE VarSym = ?
	");
    $stmt->bind_param(
        "isi",
        $varsymbol_new,
        $paymentDeadline,
        $varsymbol
    );
    $stmt->execute();
    $stmt->close();

    $varsymbol = $varsymbol_new;
    $CastkaZaplatit = (($line['Staff'] == "RO") or ($line['Staff'] == "POM")) ? '0'  : number_format($feeValues[0]['Value'], 2, ',', ' ');

    // nice nazev pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

    $STRELEC_SHOOTER = "Střelec: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC_KATEGORIE = "Kategorie: $kategorie". "\r\n";
    $STRELEC_DISCIPLINA = "Disciplína: $nazev_discipliny \r\n";
    $STRELEC_RO = "Rozhodčí: $Rozhodci";
    $STRELEC_POM = "Pomocník: $Pomocnik";
    $STRELEC_CASTKA = "Částka: $CastkaZaplatit  " . $match_data['Banka_ucet_MENA'] . "";
    $link_cancel = "<a href='" . htmlspecialchars($web_adresa, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($line['klic']) . "'><strong>zrušit účast</strong></a>";

    include './components/modal-warning.php';
    WarningModal(
        "Úspěšná registrace",
        "registrace.php",
        "<div class='col-12 fw-bolder text-danger'>Zaregistrovali jsme závodníka s těmito údaji<br>
		<div class='font-monospace d-inline-block text-start mt-2'>
		    $STRELEC_SHOOTER<br>
            $STRELEC_KATEGORIE<br>
		    $STRELEC_DISCIPLINA<br>
            $STRELEC_CASTKA<br>
            $STRELEC_RO<br>
            $STRELEC_POM
		</div>
		",
        "Potvrzení registrace bylo odesláno na adresu $email",
        "<button type='button' class='btn btn-primary' onclick=\"window.location.href = 'registrace.php';\">Nová registrace</button>
		<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zavřít</button>
		"
    );

    // posilame mail zavodnikovi
    $STRELEC .= "Střelec: #" . $cislo . " " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel] " . "\r\n";
    $STRELEC .= "Kategorie: $kategorie" . "\r\n";
    $STRELEC .= "Disciplina: $nazev_discipliny" . "\r\n\r\n";
    $STRELEC .= "<i>Rozhodčí: $Rozhodci" . "\r\n";
    $STRELEC .= "Pomocník: $Pomocnik</i>" . "\r\n\r\n";
    $STRELEC .= "Poznámka: $poznamka</i>" . "\r\n";

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
    $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
    $to = $email;
    $subject = "Registrace " . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8');
    $dnes = date_format(new DateTime(), "j.n.Y H:i");
    $mena = $match_data['Banka_ucet_MENA'];

    if (($staff == "RO") or ($staff == "POM")) {
        $message = $email_registrace_bez_platby_text;
        $stmt = $conn->prepare("
		UPDATE $table 
		SET Zaplaceno = 'on',
        Castka = '0',
        CastkaZaplatit = '0',
        Mena = ?, 
        DatumZaplaceni = ?
        WHERE Cislo = ? and klic = ?
	    ");
        $stmt->bind_param(
            "ssii",
            $mena,
            $dnes,
            $cislo,
            $klic
        );
        $stmt->execute();
        $stmt->close();
    } elseif ($match_data['Payment_before'] == 'on') {
        $message = $email_registrace_platba_text;
    } else {
        $message = $email_registrace_zavod_bez_platby_predem;
    }

    $message = str_replace("##STRELEC##", $STRELEC, $message);
    $message = str_replace("##VAR_SYMBOL##", $varsymbol, $message);
    $message = str_replace("##CASTKA##", number_format($feeValues[0]['Value'], 2, ',', ' '), $message);
    $message = str_replace("##QR_LINK##", $qr_link, $message);
    $message = str_replace("##DatPay##", $paymentDeadline, $message);

    $send_email = email($from_text, $from, $to, $subject, $message);
    if (!$send_email) {
        include './components/modal-warning.php';
        WarningModal(
            "Chyba odeslání e-mailu",
            "index.php",
            "<div class='col-12 fw-bolder text-danger'>Při odeslání e-mailu došlo k chybě!",
            "Závodník je úspěšně zaregistrovaný. Kontaktujte <a href='mailto:" . htmlspecialchars($line['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu na [$email]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zpět</button>"
        );
        exit;
    } else {
        //zapiseme do DB, ze registracni mail byl odeslan
        $stmt = $conn->prepare("
            	UPDATE $table 
		        SET OdeslanRegMail = '1'
		        WHERE Mail = ? AND OdeslanRegMail IS NULL
	            ");
        $stmt->bind_param(
            "s",
            $email
        );
        $stmt->execute();
        $stmt->close();
    }
}


// NOVA HROMADNA REGISTRACE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_registrovat'])) {

    if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
        http_response_code(403);
        exit('Neplatný CSRF token.');
    }
    unset($_SESSION['token']);

    if (!empty($_POST['gender'])) {
        exit('Spam detekován.');
    }

    $jmeno = trim(mb_convert_case($_POST['Jmeno'] ?? '', MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'] ?? '', MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
    $email = trim($_POST['Email'] ?? '');
    $staff = $_POST['Staff'] ?? '';
    $op = normalizePrukaz($_POST['ObcanskyPrukaz'] ?? '');
    $zo = isset($_POST['ZbrojniOpravneni']) ? 'on' : '';
    $czArr = $_POST['CZ'] ?? [];
    $czArrNormalized = array_map('normalizePrukaz', $czArr);
    $kategorie = $_POST['Kategorie'] ?? '';
    $discArr = $_POST['Disciplina'] ?? [];
    $poznamkaArr = $_POST['Poznamka'] ?? [];
    $ip = $_SERVER['REMOTE_ADDR'];

    if (count($discArr) === 1)
        $bulkId = 0;
    else {
        $bulkId = rand(10000000, 99999999);
    }

    // ziskame castky za jednotlive discipliny
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    $insertStmt = $conn->prepare("
      INSERT INTO $table
        (Prijmeni, Jmeno, ObcanskyPrukaz, ZbrojniOpravneni, CisloZbrane, VarSym, Mail, Kategorie, DatReg, RegistraceIP, Disciplina, Staff, klic, bulkId, CastkaZaplatit, Poznamka, Zavod)
      VALUES (?, ?, NULLIF(?,''), ?, NULLIF(?,''), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $cisla_disc_odkazy = [];
    $cisla = [];

    foreach ($discArr as $i => $disc) {
        $cz = isset($czArrNormalized[$i]) ? trim($czArrNormalized[$i]) : null;
        $poznamka = $poznamkaArr[$i];
        $datreg = time();
        $varsymbol = rand(1000, 9999);
        $klic = rand(1000, 9999);

        if (($staff == "RO") or ($staff == "POM")) {
            $CastkaZaplatit = 0;
        } elseif ($i === 0) {
            $CastkaZaplatit = $feeValues[0]['Value'];
        } elseif ($i === 1) {
            $CastkaZaplatit = $feeValues[1]['Value'];
        } else {
            $CastkaZaplatit = $feeValues[2]['Value'];
        }

        $insertStmt->bind_param(
            "ssssssssssssiiiss",
            $prijmeni,
            $jmeno,
            $op,
            $zo,
            $cz,
            $varsymbol,
            $email,
            $kategorie,
            $datreg,
            $ip,
            $disc,
            $staff,
            $klic,
            $bulkId,
            $CastkaZaplatit,
            $poznamka,
            $table
        );
        $insertStmt->execute();
        $cislo = $conn->insert_id;
        $affected = $insertStmt->affected_rows;

        $nazev = getValueFromTable($conn, $table_disciplines, "Name", $disc, "Value");
        $link = "<a href='" . htmlspecialchars($web_adresa, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($klic) . "'>Zrušit účast</a>";

        if ($cislo > 0) {
            $cisla[] = $cislo;
        }

        $cisla_disc_odkazy[] = [
            'cislo' => $cislo,
            'nazev' => $nazev,
            'link'  => $link,
            'poznamka'  => $poznamka

        ];

        // Uprava terminu zaplaceni závodníka, co se zaregistruje mene nez Zavod_pocet_dni_na_platbu dni pred prematchem
        $datumZavod = new DateTime($match_data['Zavod_datum']);
        $datumPrematch = (clone $datumZavod)->modify("-1 days");
        $datumRegistraceZavodnika = new DateTime();
        $datumRegistraceZavodnika->setTimestamp($datreg);

        if ($datumRegistraceZavodnika >= $datumPrematch->modify("-$match_data[Zavod_pocet_dni_na_platbu] days")) {
            $paymentDeadline = $datumZavod->modify("-2 days")->format('j.m.Y');
        } else {
            $paymentDeadline = (clone $datumRegistraceZavodnika)->modify("+$match_data[Zavod_pocet_dni_na_platbu] days")->format('j.m.Y');
        }

        $updateStmt = $conn->prepare("UPDATE $table SET DatPay = ? WHERE VarSym = ?");
        $updateStmt->bind_param("si", $paymentDeadline, $varsymbol);
        $updateStmt->execute();
        $updateStmt->close();
    }
    $insertStmt->close();

    // pro hromadna platba za vsechny discipliny - jeden variabilni symbol
    $tyden = intval(date("W", strtotime($match_data['Zavod_datum'])));
    $cislo_hromadne = min($cisla);
    $varsymbol = "9" . sprintf("%02d%04d", $tyden, $cislo_hromadne);

    foreach ($cisla as $cislo) {
        $stmt = $conn->prepare("UPDATE $table SET VarSym = ? WHERE Cislo = ?");
        $stmt->bind_param("ii", $varsymbol, $cislo);
        $stmt->execute();
        $stmt->close();
    }

    if ($affected === 0) {
        $_SESSION['toast'] = [
            'type'    => 'danger',
            'message' => 'Při vkládání do databáze došlo k chybě.',
            'duration' => 2500
        ];
        header("Location: registrace.php");
        exit;
    } else {
        $_SESSION['toast'] = [
            'type'    => 'success',
            'message' => 'Registrace proběhla úspěšně.',
            'duration' => 2000
        ];
        header("Location: registrace.php");
    }

    // posíláme e-mail
    $STRELEC .= "Střelec: " . htmlspecialchars($jmeno, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($prijmeni, ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Kategorie: $kategorie" . "\r\n\r\n";
    $STRELEC .= "Disciplíny:\r\n";

    foreach ($cisla_disc_odkazy as $i => $r) {
        if (($staff == "RO") or ($staff == "POM")) {
            $castka = 0;
        } elseif ($i === 0) {
            $castka = $feeValues[0]['Value'];
        } elseif ($i === 1) {
            $castka = $feeValues[1]['Value'];
        } else {
            $castka = $feeValues[2]['Value'];
        }
        $STRELEC .= "&nbsp;&nbsp;- #" . $r['cislo'] . " " . $r['nazev'] . "  (" . $castka . " " . $match_data['Banka_ucet_MENA'] . ") [" . $r['link'] . "]\r\n";
        $STRELEC .= "&nbsp;&nbsp;- Poznámka: " . $r['poznamka'] . "</i>" . "\r\n\r\n";
    }

    //vypocet celkove castky za vsechny discipliny
    $discCount = count($discArr);
    if ($discCount === 1) {
        $castka = $feeValues[0]['Value'];
    } elseif ($discCount === 2) {
        $castka = $feeValues[0]['Value'] + $feeValues[1]['Value'];
    } else {
        $castka = $feeValues[0]['Value'] + $feeValues[1]['Value'] + (($discCount - 2) * $feeValues[2]['Value']);
    }

    $qrParams = [
        'accountNumber' => $match_data['Banka_ucet_cislo'],
        'bankCode'      => $match_data['Banka_ucet_kod'],
        'amount'        => $castka,
        'currency'      => $match_data['Banka_ucet_MENA'],
        'vs'            => $varsymbol,
        'message'       => $match_data['Zavod'],
        'size'          => 100
    ];
    $qr_link = 'https://api.paylibo.com/paylibo/generator/czech/image?' . http_build_query($qrParams);

    $from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
    $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
    $to = $email;
    $subject = "Registrace " . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8');
    $dnes = date_format(new DateTime(), "j.n.Y H:i");
    $mena = $match_data['Banka_ucet_MENA'];

    if (($staff == "RO") or ($staff == "POM")) {
        $message = $email_registrace_bez_platby_text;
    } elseif (($bulkId === 0) and ($match_data['Payment_before'] == 'on')) {
        $message = $email_registrace_platba_text;
    } elseif ($match_data['Payment_before'] == 'on') {
        $message = $email_hromadna_registrace_platba_text;
    } elseif ($bulkId === 0) {
        $message = $email_registrace_zavod_bez_platby_predem;
    } else {
        $message = $email_hromadna_registrace_zavod_bez_platby_predem;
    }

    $message = str_replace("##STRELEC##", $STRELEC, $message);
    $message = str_replace("##VAR_SYMBOL##", $varsymbol, $message);
    $message = str_replace("##QR_LINK##", $qr_link, $message);
    $message = str_replace("##CASTKA##", $castka, $message);
    $message = str_replace("##QR_LINK##", $qr_link, $message);
    $message = str_replace("##DatPay##", $paymentDeadline, $message);

    $send_email = email($from_text, $from, $to, $subject, $message);
    if (!$send_email) {
        include './components/modal-warning.php';
        WarningModal(
            "Chyba odeslání e-mailu",
            "index.php",
            "<div class='col-12 fw-bolder text-danger'>Při odeslání e-mailu došlo k chybě!",
            "Závodník je úspěšně zaregistrovaný. Kontaktujte <a href='mailto:" . htmlspecialchars($line['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu na [$email]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zpět</button>"
        );
        exit;
    } else {
        //zapiseme do DB, ze registracni mail byl odeslan
        $stmt = $conn->prepare("
            	UPDATE $table 
		        SET OdeslanRegMail = '1'
		        WHERE Mail = ? AND OdeslanRegMail IS NULL
	            ");
        $stmt->bind_param(
            "s",
            $email
        );
        $stmt->execute();
        $stmt->close();
    }
}


// VYRAZENI ZAVODNIKA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_shooter'])) {
    $ip = ($_SERVER["REMOTE_ADDR"]);
    $cislo = $_POST['shooterID'];
    $klic = $_POST['shooterKEY'];

    $line = getShooterData($conn, $table, $cislo, $klic);

    // nice nazev pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

    if (!$line) {
        include './components/modal-warning.php';
        WarningModal(
            "Vyřazení závodníka",
            "index.php",
            "<div class='col-12 fw-bolder text-danger'>Nelze dohledat závodníka.",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba vyrazeni zavodnika'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'registrace.php';\">Zpět</button>"
        );
        exit;
    }
    $stmt = $conn->prepare("
        UPDATE $table 
        SET DisciplinaReg = ?,
            Disciplina = 'VYRAZENO', 
            CastkaZaplatit = NULL,
            Staff = 'DNS',
            Vyrazeno = ?, 
            VyrazenoIP = ?,
            bulkId = 0
        WHERE Cislo = ? AND klic = ?
    ");
    $stmt->bind_param(
        "sssii",
        $line['Disciplina'],
        $dnes,
        $ip,
        $cislo,
        $klic
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        include './components/modal-warning.php';
        WarningModal(
            "Chyba databáze",
            "registrace.php",
            "<div class='col-12 fw-bolder text-danger'>Při vkládání do databáze došlo k chybě!",
            "Zkuste to později nebo kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'registrace.php';\">Zpět na registraci</button>"
        );
        exit;
    } else {
        include './components/modal-warning.php';
        WarningModal(
            "Vyřazení závodníka",
            "index.php",
            "<div class='col-12 fw-bolder text-danger'>Závodník #" . $cislo . " " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($nazev_discipliny, ENT_QUOTES, 'UTF-8') . ")<br>byl vyřazen ze závodu $match_data[Zavod].",
            "E-mail s informací byl odeslán na adresu " . htmlspecialchars($line['Mail'], ENT_QUOTES, 'UTF-8') . " zadanou při registraci.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zavřít</button>"
        );
    }

    // prepocitame castku k zaplaceni u hromadnych registraci
    $bulkId = $line['bulkId'];

    if ($bulkId > 0) {
        // Získání zbývajících závodníků v BULK
        $stmt = $conn->prepare("
            SELECT Cislo, Disciplina 
            FROM $table 
            WHERE bulkId = ? AND Disciplina != 'VYRAZENO' ORDER BY Cislo
        ");
        $stmt->bind_param("i", $bulkId);
        $stmt->execute();
        $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (empty($rows)) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Nenalezena žádná hromadná platba.',
                'duration' => 2500
            ];
            header("Location: index.php");
            exit;
        }

        $discCount = count($rows);
        $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
        $FeeStmt->execute();
        $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $FeeStmt->close();

        // Pro každý řádek aktualizujeme castku u zavodnika
        if ($line['Staff'] == PAY) {
            $updateStmt = $conn->prepare("
        UPDATE $table
        SET CastkaZaplatit = ?
        WHERE Cislo = ?
    ");
            foreach ($rows as $i => $r) {
                if ($i === 0) {
                    $castka = $feeValues[0]['Value'];
                } elseif ($i === 1) {
                    $castka = $feeValues[1]['Value'];
                } else {
                    $castka = $feeValues[2]['Value'];
                }

                $updateStmt->bind_param("ii", $castka, $r['Cislo']);
                $updateStmt->execute();
            }
            $updateStmt->close();
        }

        // Pokud zůstala jen jedna disciplína změníme bulkId na 0
        if ($discCount === 1) {
            $soloCislo = $rows[0]['Cislo'];
            $bulkResetStmt = $conn->prepare("UPDATE $table SET bulkId = 0 WHERE Cislo = ?");
            $bulkResetStmt->bind_param("i", $soloCislo);
            $bulkResetStmt->execute();
            $bulkResetStmt->close();

            $_SESSION['toast'] = [
                'type'    => 'info',
                'message' => 'Z hromadné registrace zůstala jen 1 disciplína.',
                'duration' => 2000
            ];
        }
    }

    // posilame mail zavodnikovi
    $STRELEC .= "Střelec: #" . $cislo . " " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Disciplina: $nazev_discipliny" . "\r\n";

    $from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
    $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
    $to = htmlspecialchars($line['Mail'], ENT_QUOTES, 'UTF-8');
    $subject = "Zrušení registrace závodníka " . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8');
    $message = $email_text_vyrazeni_vlastni;
    $message = str_replace("##STRELEC##", $STRELEC, $message);

    $send_email = email($from_text, $from, $to, $subject, $message);
    if (!$send_email) {
        include './components/modal-warning.php';
        WarningModal(
            "Chyba odeslání e-mailu",
            "index.php",
            "<div class='col-12 fw-bolder text-danger'>Při odeslání e-mailu došlo k chybě!",
            "Závodník je vyřazený. Kontaktujte <a href='mailto:" . htmlspecialchars($line['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu na [$email]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zpět</button>"
        );
        exit;
    }
}
