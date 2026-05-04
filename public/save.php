<?php
include "./header.php";

// TENOLIX SINGLE REGISTRACE (junior evidence nadstandardnich udaju)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrovat_tenolix'])) {

    // --- kontrola CSRF tokenu ---
    if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
        http_response_code(403);
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Neplatný CSRF token. Registrujte se po kliknutí na registrace',
            'duration' => 2500
        ];
        header("Location: index.php");
        exit;
    }
    // token po použití zneplatníme
    unset($_SESSION['token']);
    // --- honeypot (robots) ---
    if (!empty($_POST['gender'])) {
        exit('Detekován Spam.');
    }

    $jmeno = trim(mb_convert_case($_POST['Jmeno'] ?? '', MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'] ?? '', MB_CASE_TITLE, "UTF-8"));
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

    // Tenolix fields
    $rocnik = (int)($_POST['Rocnik'] ?? '');
    $zodpovednaOsoba = trim(mb_convert_case($_POST['ZodpovednaOsoba'] ?? '', MB_CASE_TITLE, "UTF-8"));
    $trenink = isset($_POST['Trenink']) ? 1 : 0;
    $klub = normalizeText($_POST['Klub'] ?? '');

    // ziskame castku za jednu disciplinu
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    // prepocitame castku $feeValues[0]['Value'] + 100 za trénink
    if ($trenink === 1) {
        $castka = ($feeValues[0]['Value'] + 100);
    } else {
        $castka = $feeValues[0]['Value'];
    }

    $stmt = $conn->prepare("
		INSERT INTO $table 
		(Prijmeni, Jmeno, Rocnik, ZodpovednaOsoba, Trenink, Stav, ObcanskyPrukaz, ZbrojniOpravneni, CisloZbrane, NazevZbrane, VarSym, Mail, Kategorie, DatReg, RegistraceIP, Disciplina, Staff, klic, CastkaZaplatit, Poznamka, Klub, Zavod)
		VALUES (?, ?, NULLIF(?,''), NULLIF(?,''), ?, NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NULLIF(?,''), ?)
	");
    $stmt->bind_param(
        "ssisississississsidsss",
        $prijmeni,
        $jmeno,
        $rocnik,
        $zodpovednaOsoba,
        $trenink,
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
        $castka,
        $poznamka,
        $klub,
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

    // posilame potvrzeni registrace a platebni udaje zavodnihovi vcetne odkazu na spravu ucasti (zruseni)
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
    $CastkaZaplatit = ($isVIP) ? '0'  : number_format($castka, 2, ',', ' ');

    // nice nazev pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
    $nazev_kategorie = getValueFromTable($conn, "ssas_k4m_tenolix_categories", "Name", $line['Kategorie'], "Value");

    $STRELEC_SHOOTER = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC_ZODPOVEDNA_OSOBA = "Zodpovědná osoba: " . htmlspecialchars($zodpovednaOsoba, ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC_ROCNIK = "Ročník: " . htmlspecialchars($rocnik, ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC_KATEGORIE = "Kategorie: $nazev_kategorie" . "\r\n";
    $STRELEC_TRENINK = "Trénink: " . ($trenink ? "ANO" : "NE") . "\r\n";
    $STRELEC_CASTKA = "Částka: $CastkaZaplatit  " . $match_data['Banka_ucet_MENA'] . "";
    $link_cancel = "<a href='" . htmlspecialchars($reg_url, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($line['klic']) . "'><strong>zrušit účast</strong></a>";

    include './components/modal-warning.php';
    WarningModal(
        "Úspěšná registrace",
        "registrace.php",
        "<div class='col-12 fw-bolder text-danger'>Zaregistrovali jsme závodníka s těmito údaji.</div>
		<div class='font-monospace d-inline-block text-start mt-2'>
		    $STRELEC_SHOOTER<br>
            $STRELEC_ZODPOVEDNA_OSOBA<br>
            $STRELEC_ROCNIK<br>
            $STRELEC_KATEGORIE<br>
            $STRELEC_TRENINK<br>
            $STRELEC_CASTKA
		</div>
		",
        "Potvrzení registrace bylo odesláno na adresu $email",
        "<button type='button' class='btn btn-primary' onclick=\"window.location.href = 'registrace.php';\">Nová registrace</button>
		<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zavřít</button>
		"
    );

    // posilame mail zavodnikovi
    $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel] " . "\r\n";
    $STRELEC .= "Zodpovědná osoba: " . htmlspecialchars($zodpovednaOsoba, ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Ročník: " . htmlspecialchars($rocnik, ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Kategorie: $nazev_kategorie" . "\r\n";
    $STRELEC .= "Trénink: " . ($trenink ? "ANO" : "NE") . "\r\n";

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
        $message = $email_registrace_tenolix_platba_text;
    } else {
        $message = $email_registrace_tenolix_bez_platby_predem;
    }

    $message = str_replace("##STRELEC##", $STRELEC, $message);
    $message = str_replace("##VAR_SYMBOL##", $varsymbol, $message);
    $message = str_replace("##CASTKA##", $CastkaZaplatit, $message);
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
}


// SINGLE REGISTRACE 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrovat'])) {

    // --- kontrola CSRF tokenu ---
    if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
        http_response_code(403);
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Neplatný CSRF token. Registrujte se po kliknutí na registrace',
            'duration' => 2500
        ];
        header("Location: index.php");
        exit;
    }
    // token po použití zneplatníme
    unset($_SESSION['token']);
    // --- honeypot (robots) ---
    if (!empty($_POST['gender'])) {
        exit('Detekován Spam.');
    }

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
    $link_cancel = "<a href='" . htmlspecialchars($reg_url, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($line['klic']) . "'><strong>zrušit účast</strong></a>";

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
    $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel] " . "\r\n";
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
}

// REGISTRACE DO SMĚN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['smena_registrovat'])) {

    // --- kontrola CSRF tokenu ---
    if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
        http_response_code(403);
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Neplatný CSRF token. Registrujte se po kliknutí na registrace',
            'duration' => 2500
        ];
        header("Location: index.php");
        exit;
    }
    // token po použití zneplatníme
    unset($_SESSION['token']);
    // --- honeypot (robots) ---
    if (!empty($_POST['gender'])) {
        exit('Detekován Spam.');
    }

    $jmeno = trim(mb_convert_case($_POST['Jmeno'] ?? '', MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'] ?? '', MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
    $stav = $_POST['Stav'] ?? '';
    $email = trim($_POST['Email'] ?? '');
    $ip = $_SERVER["REMOTE_ADDR"];
    $op = normalizePrukaz($_POST['ObcanskyPrukaz'] ?? '');
    $zo = isset($_POST['ZbrojniOpravneni']) ? 1 : 0;
    $cz = normalizePrukaz(trim($_POST['CZ']) ?? '');
    $nz = normalizeText($_POST['NZ'] ?? '');
    $isVIP = in_array($_POST['Staff'], ['RO', 'POM']);

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

    // ziskame pocet registrovanych disciplin zavodnika
    $DiscStmt = $conn->prepare("SELECT count(Prijmeni) as discCount FROM $table where Prijmeni = ? and Jmeno = ? and Mail = ?");
    $DiscStmt->bind_param("sss", $prijmeni, $jmeno, $email);
    $DiscStmt->execute();
    $result = $DiscStmt->get_result();
    $DiscStmt->close();
    $discCount = $result->fetch_object()->discCount;

    if ($isVIP) {
        $CastkaZaplatit = 0;
    } elseif ($discCount == 0) {
        $CastkaZaplatit = $feeValues[0]['Value'];
    } elseif ($discCount == 1) {
        $CastkaZaplatit = $feeValues[1]['Value'];
    } else {
        $CastkaZaplatit = $feeValues[2]['Value'];
    }

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
        $CastkaZaplatit,
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
    $CastkaZaplatit = ($isVIP) ? '0'  : $CastkaZaplatit;

    // nice nazev pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

    $STRELEC_SHOOTER = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC_KATEGORIE = "Kategorie: $kategorie" . "\r\n";
    $STRELEC_DISCIPLINA = "Disciplína: $nazev_discipliny \r\n";
    $STRELEC_RO = "Rozhodčí: $Rozhodci";
    $STRELEC_POM = "Pomocník: $Pomocnik";
    $STRELEC_CASTKA = "Částka: $CastkaZaplatit  " . $match_data['Banka_ucet_MENA'] . "";
    $link_cancel = "<a href='" . htmlspecialchars($reg_url, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($line['klic']) . "'><strong>zrušit účast</strong></a>";

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
    $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel] " . "\r\n";
    $STRELEC .= "Kategorie: $kategorie" . "\r\n";
    $STRELEC .= "Disciplina: $nazev_discipliny" . "\r\n\r\n";
    $STRELEC .= "<i>Rozhodčí: $Rozhodci" . "\r\n";
    $STRELEC .= "Pomocník: $Pomocnik</i>" . "\r\n\r\n";
    $STRELEC .= "Poznámka: $poznamka</i>" . "\r\n";

    $qrParams = [
        'accountNumber' => $match_data['Banka_ucet_cislo'],
        'bankCode'      => $match_data['Banka_ucet_kod'],
        'amount'        => $CastkaZaplatit,
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
    $message = str_replace("##CASTKA##", number_format($CastkaZaplatit, 2, ',', ' '), $message);
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
}

// HROMADNA REGISTRACE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['bulk_registrovat'])) {

    // --- kontrola CSRF tokenu ---
    if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
        http_response_code(403);
        exit('Neplatný CSRF token.');
    }
    // token po použití zneplatníme
    unset($_SESSION['token']);
    // --- honeypot (robots) ---
    if (!empty($_POST['gender'])) {
        exit('Spam detekován.');
    }

    $jmeno = trim(mb_convert_case($_POST['Jmeno'] ?? '', MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'] ?? '', MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
    $email = trim($_POST['Email'] ?? '');
    $staff = $_POST['Staff'] ?? '';
    $op = normalizePrukaz($_POST['ObcanskyPrukaz'] ?? '');
    $zo = isset($_POST['ZbrojniOpravneni']) ? 1 : 0;
    $czArr = $_POST['CZ'] ?? [];
    $czArrNormalized = array_map('normalizePrukaz', $czArr);
    $nzArr = $_POST['NZ'] ?? [];
    $nzArrNormalized = array_map('normalizeText', $nzArr);
    $kategorie = $_POST['Kategorie'] ?? '';
    $discArr = $_POST['Disciplina'] ?? [];
    $poznamkaArr = $_POST['Poznamka'] ?? [];
    $ip = $_SERVER['REMOTE_ADDR'];
    $isVIP = in_array($_POST['Staff'], ['RO', 'POM']);

    if (count($discArr) === 1)
        $bulkId = 0;
    else {
        $bulkId = random_int(10000000, 99999999);
    }

    // ziskame castky za jednotlive discipliny
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    $insertStmt = $conn->prepare("
      INSERT INTO $table
        (Prijmeni, Jmeno, ObcanskyPrukaz, ZbrojniOpravneni, CisloZbrane, NazevZbrane, VarSym, Mail, Kategorie, DatReg, RegistraceIP, Disciplina, Staff, klic, bulkId, CastkaZaplatit, Poznamka, Zavod)
      VALUES (?, ?, NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $cisla_disc_odkazy = [];
    $cisla = [];

    foreach ($discArr as $i => $disc) {
        $cz = isset($czArrNormalized[$i]) ? trim($czArrNormalized[$i]) : null;
        $nz = isset($nzArrNormalized[$i]) ? trim($nzArrNormalized[$i]) : null;
        //        $nz = $nzArr[$i];
        $poznamka = $poznamkaArr[$i];
        $datreg = time();
        $varsymbol = random_int(1000, 9999);
        $klic = random_int(1000, 9999);

        //        if (($staff == "RO") || ($staff == "POM")) {
        if ($isVIP) {
            $CastkaZaplatit = 0;
        } elseif ($i === 0) {
            $CastkaZaplatit = $feeValues[0]['Value'];
        } elseif ($i === 1) {
            $CastkaZaplatit = $feeValues[1]['Value'];
        } else {
            $CastkaZaplatit = $feeValues[2]['Value'];
        }

        $insertStmt->bind_param(
            "sssssssssssssiiiss",
            $prijmeni,
            $jmeno,
            $op,
            $zo,
            $cz,
            $nz,
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
        $link = "<a href='" . htmlspecialchars($reg_url, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($klic) . "'>Zrušit účast</a>";

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
            'message' => 'Závodník byl úspěšně zaregistrován.',
            'duration' => 2000
        ];
        header("Location: registrace.php");
    }

    // posíláme e-mail
    $STRELEC .= "Závodník: " . htmlspecialchars($jmeno, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($prijmeni, ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Kategorie: $kategorie" . "\r\n\r\n";
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
        $STRELEC .= "&nbsp;&nbsp;- " . $r['nazev'] . "  (" . $castka . " " . $match_data['Banka_ucet_MENA'] . ") [" . $r['link'] . "]\r\n";
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

    //    if (($staff == "RO") || ($staff == "POM")) {
    if ($isVIP) {
        $message = $email_registrace_bez_platby_text;
    } elseif (($bulkId === 0) and ($match_data['Payment_before'])) {
        $message = $email_registrace_platba_text;
    } elseif ($match_data['Payment_before']) {
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
}


// VYRAZENI ZAVODNIKA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_shooter'])) {
    $ip = ($_SERVER["REMOTE_ADDR"]);
    $cislo = $_POST['shooterID'];
    $klic = $_POST['shooterKEY'];

    $line = getShooterData($conn, $table, $cislo, $klic);

    // nice nazev pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
    // nice nazev pro mail

    if (!$line) {
        include './components/modal-warning.php';
        WarningModal(
            "Vyřazení závodníka",
            "index.php",
            "<div class='col-12 fw-bolder text-danger'>Nelze dohledat závodníka.</div>",
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
        $dnesText,
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
            "<div class='col-12 fw-bolder text-danger'>Při vkládání do databáze došlo k chybě!</div>",
            "Zkuste to později nebo kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'registrace.php';\">Zpět na registraci</button>"
        );
        exit;
    } else {
        include './components/modal-warning.php';
        WarningModal(
            "Vyřazení závodníka",
            "index.php",
            "<div class='col-12 fw-bolder text-danger'>Závodník " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($nazev_discipliny, ENT_QUOTES, 'UTF-8') . ")<br>byl vyřazen ze závodu $match_data[Zavod].</div>",
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

        // 3) Pro každý řádek aktualizujeme castku u zavodnika
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

        // Pokud zůstala jen jedna disciplína → změna bulkId na 0
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
    $STRELEC .= "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
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
            "<div class='col-12 fw-bolder text-danger'>Při odeslání e-mailu došlo k chybě</div>",
            "Závodník je vyřazený. Kontaktujte <a href='mailto:" . htmlspecialchars($line['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu na [$email]'>pořadatele závodu</a>.",
            "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zpět</button>"
        );
        exit;
    }
}

//VYNUCENA ZMENA HESLA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['forced_password_change'])) {
    $username       = $_SESSION['name'] ?? '';
    $passwordNew    = $_POST['password_new'] ?? '';
    $passwordVerify = $_POST['password_new1'] ?? '';

    if ($username) {
        $stmt = $conn->prepare("SELECT password FROM $table_admins WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        if ($result && $row = $result->fetch_assoc()) {
            $storedHash = $row['password'];

            // kontrola shody puvodniho a noveho hesla
            if (password_verify($passwordNew, $storedHash)) {
                $_SESSION['toast'] = [
                    'type' => 'warning',
                    'message' => 'Nové heslo nesmí být stejné jako původní.',
                    'duration' => 2500
                ];
                header("Location: password_change.php");
                exit();
            }
            // kontrola shody nových hesel
            if ($passwordNew !== $passwordVerify) {
                $_SESSION['toast'] = [
                    'type' => 'warning',
                    'message' => 'Hesla se neshodují.',
                    'duration' => 2500
                ];
                header("Location: password_change.php");
                exit();
            }

            // kontrola síly nového hesla
            $errorMessage = '';
            if (!isValidPassword($passwordNew, $username, $errorMessage)) {
                $_SESSION['toast'] = [
                    'type' => 'warning',
                    'message' => $errorMessage,
                    'duration' => 2500
                ];
                header("Location: /");
                exit();
            }


            // ulož nový hash
            $hash = password_hash($passwordNew, PASSWORD_DEFAULT);
            $updateStmt = $conn->prepare("
                UPDATE $table_admins 
                SET password = ?,
                last_password_change = NOW(),
                force_password_change = 0
                WHERE username = ?");
            $updateStmt->bind_param(
                "ss",
                $hash,
                $username
            );
            $updateStmt->execute();
            $updateStmt->close();

            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Heslo bylo úspěšně změněno.',
                'duration' => 2000
            ];
            header('Location: ' . $admin_url);
            exit();
        }
    }
}
