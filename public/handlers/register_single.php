<?php 
    $jmeno = trim(mb_convert_case($_POST['Jmeno'] ?? '', MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'] ?? '', MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
    $stav = $_POST['Stav'] ?? '';
    $email = trim($_POST['Email'] ?? '');
    $ip = $_SERVER["REMOTE_ADDR"];
    $op = normalizePrukaz($_POST['ObcanskyPrukaz'] ?? '');
    $zo = isset($_POST['ZbrojniOpravneni']) ? 1 : 0;
    $cz = normalizePrukaz(trim($_POST['CZ']) ?? '');
    $nz = normalizeText($_POST['NZ'] ?? '');

    $poznamka = trim($_POST['Poznamka'] ?? '');
    $staff = $_POST['Staff'] ?? '';
    $kategorie = $_POST['Kategorie'] ?? '';
    $varsymbol = random_int(1000, 9999);
    $klic = random_int(1000, 9999);
    $datreg = time();

    // ziskame castku za jednu disciplinu
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    $stmt = $conn->prepare("
		INSERT INTO $table 
		(Prijmeni, Jmeno, Stav, ObcanskyPrukaz, ZbrojniOpravneni, CisloZbrane, NazevZbrane, VarSym, Mail, Kategorie, DatReg, RegistraceIP, Disciplina, Staff, klic, CastkaZaplatit, Poznamka, Zavod)
		VALUES (?, ?, NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
	");
    $stmt->bind_param(
        "ssisssssssssssiiss",
        $prijmeni,
        $jmeno,
        $stav,
        $op,
        $zo,
        $cz,
        $nz,
        $varsymbol,
        $email,
        $kategorie,
        $datreg,
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
    $isVIP = in_array($line['Staff'], ['RO', 'POM']);

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
    $CastkaZaplatit = ($isVIP) ? '0'  : number_format($feeValues[0]['Value'], 2, ',', ' ');

    // nice nazev pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

    $STRELEC_SHOOTER = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC_KATEGORIE = "Kategorie: $kategorie" . "\r\n";
    $STRELEC_DISCIPLINA = "Disciplína: $nazev_discipliny \r\n";
    $STRELEC_RO = "Rozhodčí: $Rozhodci";
    $STRELEC_POM = "Pomocník: $Pomocnik";
    $STRELEC_CASTKA = "Částka: $CastkaZaplatit  " . $match_data['Banka_ucet_MENA'] . "";
    
    $link_cancel = buildCancelLinks($reg_url, $cislo, $klic);
    $link_ical = buildCalendarLinks($reg_url, $match_data);

    include './components/modal-warning.php';
    WarningModal(
        "Úspěšná registrace",
        "registrace.php",
        "<div class='col-12 fw-bolder text-danger'>Zaregistrovali jsme závodníka s těmito údaji.</div>
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
    $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel] [$link_ical] [$link_ical] " . "\r\n";
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

    if ($isVIP) {
        $message = $email_registrace_bez_platby_text;
        $stmt = $conn->prepare("
		UPDATE $table 
		SET Zaplaceno = 1,
        Castka = 0,
        CastkaZaplatit = 0,
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
    } elseif ($match_data['Payment_before']) {
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
            "Závodník je zaregistrovaný, ale e-mail se nepodařilo odeslat. Kontaktujte <a href='mailto:" . htmlspecialchars($line['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu na [$email]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zpět</button>"
        );
        exit;
    } else {
        //zapiseme do DB, ze registracni mail byl odeslan
        $stmt = $conn->prepare("
            	UPDATE $table 
		        SET OdeslanRegMail = 1
		        WHERE Mail = ? AND OdeslanRegMail = 0
	            ");
        $stmt->bind_param(
            "s",
            $email
        );
        $stmt->execute();
        $stmt->close();
    }
    include "./footer.php";
?>