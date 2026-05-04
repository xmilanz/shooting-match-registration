<link rel="stylesheet" type="text/css" href="/styles/style.css">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';
require_once __DIR__ . '/config/mail_texty.php';
require_admin();

$conn = new mysqli($db_host, $db_login, $db_pass, $db_dtb);


// KONFIGRACE ZAVODU 
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['match_config'])) {
    if ($match_data['Payment_before']) {
        $stmt = $conn->prepare("
	UPDATE $table_matches 
        SET Banka_ucet_cislo = ?,
   	 Banka_ucet_kod = ?,
   	 Banka_nazev = ?,
   	 Banka_adresa = ?,
   	 Klub_web = ?,
   	 Zavod = ?,
   	 Zavod_datum = ?,
   	 Zavod_cas_registrace_zacatek = ?,
   	 Zavod_cas_registrace_konec = ?,
   	 Zavod_zacatek_registrace = ?,
   	 Zavod_konec_registrace = ?,
   	 Zavod_registrace_pozastaveno = ?,
   	 Zavod_registrace_hromadna = ?,
     Zavod_registrace_smeny = ?,
   	 Zavod_more_divisions = ?,
   	 Zavod_zobrazovat_sponzory = ?,
     Zavod_obcansky_prukaz = ?,
   	 Zavod_zbrojni_prukaz = ?,
   	 Zavod_cislo_zbrane = ?,
     Zavod_nazev_zbrane = ?,
     Zavod_platba_volitelna = ?,
   	 Web_zobrazovat_situace = ?,
   	 Web_zobrazovat_aliasy = ?,
   	 Web_zobrazovat_vysledky = ?,
   	 Zavod_cas_prematch = ?,
   	 Zavod_cas_prezence = ?,
   	 Zavod_cas_main = ?,
   	 Zavod_cas_main_dopoledne = ?,
   	 Zavod_cas_main_odpoledne = ?,
   	 Zavod_misto = ?,
   	 Zavod_misto_mapa = ?,
   	 Zavod_poradatel = ?,
   	 Zavod_poradatel_adresa = ?,
   	 Zavod_match_director = ?,
   	 Zavod_email_poradatel = ?,
   	 Zavod_telefon_poradatel = ?,
   	 Zavod_range_master = ?,
   	 Zavod_email_range_master = ?,
   	 Zavod_telefon_range_master = ?,
   	 Zavod_stats = ?,
   	 Zavod_email_stats = ?,
   	 Zavod_telefon_stats = ?,
   	 Zavod_hospodar = ?,
   	 Zavod_email_hospodar = ?,
   	 Zavod_telefon_hospodar = ?,
   	 Zavod_email_from = ?,
   	 Zavod_stages = ?,
     Pocet_smen = ?,
   	 Zavod_min_pocet_ran = ?,
   	 Zavod_pocet_dni_na_platbu = ?,
   	 Zavod_vysledky = ?,
   	 Zavod_propozice = ?,
   	 Squad_main_max = ?,
   	 Squad_prem_max = ?,
   	 Payment_before = ?
    WHERE Zavod_id = ?
    ");
        $stmt->bind_param(
            "sssssssssssissssssssssssssssssssssssssssssssssiiiissiiss",
            $_POST['Banka_ucet_cislo'],
            $_POST['Banka_ucet_kod'],
            $_POST['Banka_nazev'],
            $_POST['Banka_adresa'],
            $_POST['Klub_web'],
            $_POST['Zavod'],
            $_POST['Zavod_datum'],
            $_POST['Zavod_cas_registrace_zacatek'],
            $_POST['Zavod_cas_registrace_konec'],
            $_POST['Zavod_zacatek_registrace'],
            $_POST['Zavod_konec_registrace'],
            $_POST['Zavod_registrace_pozastaveno'],
            $_POST['Zavod_registrace_hromadna'],
            $_POST['Zavod_registrace_smeny'],
            $_POST['Zavod_more_divisions'],
            $_POST['Zavod_zobrazovat_sponzory'],
            $_POST['Zavod_obcansky_prukaz'],
            $_POST['Zavod_zbrojni_prukaz'],
            $_POST['Zavod_cislo_zbrane'],
            $_POST['Zavod_nazev_zbrane'],
            $_POST['Zavod_platba_volitelna'],
            $_POST['Web_zobrazovat_situace'],
            $_POST['Web_zobrazovat_aliasy'],
            $_POST['Web_zobrazovat_vysledky'],
            $_POST['Zavod_cas_prematch'],
            $_POST['Zavod_cas_prezence'],
            $_POST['Zavod_cas_main'],
            $_POST['Zavod_cas_main_dopoledne'],
            $_POST['Zavod_cas_main_odpoledne'],
            $_POST['Zavod_misto'],
            $_POST['Zavod_misto_mapa'],
            $_POST['Zavod_poradatel'],
            $_POST['Zavod_poradatel_adresa'],
            $_POST['Zavod_match_director'],
            $_POST['Zavod_email_poradatel'],
            $_POST['Zavod_telefon_poradatel'],
            $_POST['Zavod_range_master'],
            $_POST['Zavod_email_range_master'],
            $_POST['Zavod_telefon_range_master'],
            $_POST['Zavod_stats'],
            $_POST['Zavod_email_stats'],
            $_POST['Zavod_telefon_stats'],
            $_POST['Zavod_hospodar'],
            $_POST['Zavod_email_hospodar'],
            $_POST['Zavod_telefon_hospodar'],
            $_POST['Zavod_email_from'],
            $_POST['Zavod_stages'],
            $_POST['Pocet_smen'],
            $_POST['Zavod_min_pocet_ran'],
            $_POST['Zavod_pocet_dni_na_platbu'],
            $_POST['Zavod_vysledky'],
            $_POST['Zavod_propozice'],
            $_POST['Squad_main_max'],
            $_POST['Squad_prem_max'],
            $_POST['Payment_before'],
            $table
        );
    } else {
        $stmt = $conn->prepare("
	UPDATE $table_matches 
            SET Banka_ucet_cislo = ?,
   	 Banka_ucet_kod = ?,
     Klub_web = ?,
     Zavod = ?,
     Zavod_datum = ?,
     Zavod_cas_registrace_zacatek = ?,
     Zavod_cas_registrace_konec = ?,
     Zavod_zacatek_registrace = ?,
     Zavod_konec_registrace = ?,
     Zavod_registrace_pozastaveno = ?,
     Zavod_registrace_hromadna = ?,
     Zavod_registrace_smeny = ?,
     Zavod_more_divisions = ?,
     Zavod_zobrazovat_sponzory = ?,
     Zavod_obcansky_prukaz = ?,
     Zavod_zbrojni_prukaz = ?,
     Zavod_cislo_zbrane = ?,
     Zavod_nazev_zbrane = ?,
     Zavod_platba_volitelna = ?,
     Web_zobrazovat_situace = ?,
     Web_zobrazovat_aliasy = ?,
     Web_zobrazovat_vysledky = ?,
     Zavod_cas_prematch = ?,
     Zavod_cas_prezence = ?,
     Zavod_cas_main = ?,
     Zavod_cas_main_dopoledne = ?,
     Zavod_cas_main_odpoledne = ?,
     Zavod_misto = ?,
     Zavod_misto_mapa = ?,
     Zavod_poradatel = ?,
     Zavod_poradatel_adresa = ?,
     Zavod_match_director = ?,
     Zavod_email_poradatel = ?,
     Zavod_telefon_poradatel = ?,
     Zavod_range_master = ?,
     Zavod_email_range_master = ?,
     Zavod_telefon_range_master = ?,
     Zavod_stats = ?,
     Zavod_email_stats = ?,
     Zavod_telefon_stats = ?,
     Zavod_hospodar = ?,
     Zavod_email_hospodar = ?,
     Zavod_telefon_hospodar = ?,
     Zavod_email_from = ?,
     Zavod_stages = ?,
     Pocet_smen = ?,
     Zavod_min_pocet_ran = ?,
     Zavod_pocet_dni_na_platbu = ?,
     Zavod_vysledky = ?,
     Zavod_propozice = ?,
     Squad_main_max = ?,
     Squad_prem_max = ?,
     Payment_before = ?
    WHERE Zavod_id = ?
    ");
        $stmt->bind_param(
            "sssssssssisssssssssssssssssssssssssssssssssssiiissiiss",
            $_POST['Banka_ucet_cislo'],
            $_POST['Banka_ucet_kod'],
            $_POST['Klub_web'],
            $_POST['Zavod'],
            $_POST['Zavod_datum'],
            $_POST['Zavod_cas_registrace_zacatek'],
            $_POST['Zavod_cas_registrace_konec'],
            $_POST['Zavod_zacatek_registrace'],
            $_POST['Zavod_konec_registrace'],
            $_POST['Zavod_registrace_pozastaveno'],
            $_POST['Zavod_registrace_hromadna'],
            $_POST['Zavod_registrace_smeny'],
            $_POST['Zavod_more_divisions'],
            $_POST['Zavod_zobrazovat_sponzory'],
            $_POST['Zavod_obcansky_prukaz'],
            $_POST['Zavod_zbrojni_prukaz'],
            $_POST['Zavod_cislo_zbrane'],
            $_POST['Zavod_nazev_zbrane'],
            $_POST['Zavod_platba_volitelna'],
            $_POST['Web_zobrazovat_situace'],
            $_POST['Web_zobrazovat_aliasy'],
            $_POST['Web_zobrazovat_vysledky'],
            $_POST['Zavod_cas_prematch'],
            $_POST['Zavod_cas_prezence'],
            $_POST['Zavod_cas_main'],
            $_POST['Zavod_cas_main_dopoledne'],
            $_POST['Zavod_cas_main_odpoledne'],
            $_POST['Zavod_misto'],
            $_POST['Zavod_misto_mapa'],
            $_POST['Zavod_poradatel'],
            $_POST['Zavod_poradatel_adresa'],
            $_POST['Zavod_match_director'],
            $_POST['Zavod_email_poradatel'],
            $_POST['Zavod_telefon_poradatel'],
            $_POST['Zavod_range_master'],
            $_POST['Zavod_email_range_master'],
            $_POST['Zavod_telefon_range_master'],
            $_POST['Zavod_stats'],
            $_POST['Zavod_email_stats'],
            $_POST['Zavod_telefon_stats'],
            $_POST['Zavod_hospodar'],
            $_POST['Zavod_email_hospodar'],
            $_POST['Zavod_telefon_hospodar'],
            $_POST['Zavod_email_from'],
            $_POST['Zavod_stages'],
            $_POST['Pocet_smen'],
            $_POST['Zavod_min_pocet_ran'],
            $_POST['Zavod_pocet_dni_na_platbu'],
            $_POST['Zavod_vysledky'],
            $_POST['Zavod_propozice'],
            $_POST['Squad_main_max'],
            $_POST['Squad_prem_max'],
            $_POST['Payment_before'],
            $table
        );
    }
    $stmt->execute();
    session_start();
    if ($stmt->errno !== 0) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba databáze",
            "index.php",
            "Při vkládání do databáze došlo k chybě!",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } elseif ($stmt->affected_rows === 0) {
        $_SESSION['toast'] = [
            'type' => 'primary',
            'message' => 'V nastavení závodu jste neprovedli žádné změny.',
            'duration' => 2000
        ];
    } else {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Změny nastavení závodu byly úspěšně uloženy.',
            'duration' => 2500
        ];
    }
    $stmt->close();
    header("Location: /");
    exit();
}

// NOVY ZAVODNIK
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_shooter'])) {
    $jmeno = trim(mb_convert_case($_POST['Jmeno'], MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'], MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
    $stav =  $_POST['Stav'];
    $ip = ($_SERVER["REMOTE_ADDR"] . " - admin");
    $op = trim($_POST['ObcanskyPrukaz'] ?? '');
    $zo = isset($_POST['ZbrojniOpravneni']) ? 1 : 0;
    $cz = normalizePrukaz(trim($_POST['CZ']) ?? '');
    $nz = normalizetext(trim($_POST['NZ']) ?? '');
    $kategorie = $_POST['Kategorie'] ?? '';
    $email = trim($_POST['Mail']);
    $namiste = isset($_POST['ZaplatiNaMiste']) ? 1 : 0;
    $varsymbol = rand(1000, 9999);
    $klic = rand(1000, 9999);

    // kontrola, zda stav již není obsazený - TO-DO
    $ShiftStmt = $conn->prepare("
    SELECT 1 FROM $table 
    WHERE Stav = ?
    LIMIT 1
");
    $ShiftStmt->bind_param("i", $stav);
    $ShiftStmt->execute();
    $result = $ShiftStmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['toast'] = [
            'type'    => 'danger',
            'message' => 'Stav je již obsazen.',
            'duration' => 3000
        ];
        header("Location: /");
        exit;
    }

    // ziskame castku za jednu disciplinu
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    $stmt = $conn->prepare("
		INSERT INTO $table 
        (Prijmeni,Jmeno,Stav,ObcanskyPrukaz,ZbrojniOpravneni,CisloZbrane,NazevZbrane,VarSym,Kategorie,Mail,Disciplina,DatReg,RegistraceIP,Staff,klic,ZaplatiNaMiste,CastkaZaplatit,Poznamka,Zavod)
        VALUES (?, ?, NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), NULLIF(?,''), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
    $stmt->bind_param(
        "ssissssissssssisiss",
        $prijmeni,
        $jmeno,
        $stav,
        $op,
        $zo,
        $cz,
        $nz,
        $varsymbol,
        $kategorie,
        $email,
        $_POST['Disciplina'],
        $_POST['datreg'],
        $ip,
        $_POST['Staff'],
        $klic,
        $namiste,
        $feeValues[0]['Value'],
        $_POST['Poznamka'],
        $table
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();
    $cislo = $conn->insert_id;

    if ($affected === 0) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba databáze",
            "index.php",
            "Při vkládání do databáze došlo k chybě!",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
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
    $stmt->close();

    $line = mysqli_fetch_array($result);

    $isVIP = in_array($line['Staff'], ['VIP', 'RO', 'POM']);
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
    $dnes = date_format(new DateTime(), "d.m.Y H:i");
    $mena = $match_data['Banka_ucet_MENA'];
    $link_cancel = "<a href='$web_adresa_admin/zrus_ucast.php?id=$line[Cislo]&klic=$line[klic]'><strong>zrušit účast</strong></a>";

    // podmínky pro volbu textu v závislosti na statutu závodníka
    if ($isVIP) {
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
            $line['Cislo'],
            $line['klic']
        );
        $stmt->execute();
        $stmt->close();
        $message = $email_registrace_bez_platby_text_novy_zavodnik;
    } elseif ($line['ZaplatiNaMiste'] == 1) {
        $message = $email_registrace_platba_na_miste_novy_zavodnik;
    } elseif ($match_data['Payment_before'] == 1) {
        $message = $email_registrace_platba_text_novy_zavodnik;
    } else {
        $message = $email_registrace_zavod_bez_platby_predem;
    }


    // priprava podkladu pro e-mail zavodnikovi
    // nice názvy pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

    $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " [$link_cancel]\r\n";
    $STRELEC .= "Kategorie: $kategorie" . "\r\n";
    $STRELEC .= "Discpilína: $nazev_discipliny" . "\r\n\r\n";
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
    $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
    $to = $email;
    $subject = "Registrace " . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8');
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
            "Závodník byl zaregistrován, pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        // zapiseme do DB, ze registracni mail byl odeslan
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
        $result = $stmt->get_result();
        $stmt->close();
        if ($result->num_rows === 0) {
            include './components/modal-warning.php';
            WarningModal(
                "danger",
                "Chyba databáze",
                "index.php",
                "Při vkládání do databáze došlo k chybě!",
                "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
                "Zpět do administrace"
            );
        } else {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Závodník byl úspěšně zaregistrován a registrační e-mail odeslán.',
                'duration' => 2500
            ];
            header("refresh:0;url=index.php");
        }
    }
}


// EDITACE ZAVODNIKA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_shooter'])) {
    $alias = trim(mb_convert_case($_POST['Alias'], MB_CASE_UPPER, "UTF-8"));
    $jmeno = trim(mb_convert_case($_POST['Jmeno'], MB_CASE_TITLE, "UTF-8"));
    $prijmeni = trim(mb_convert_case($_POST['Prijmeni'], MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
    $stav = (int) $_POST['Stav'];
    $op = normalizePrukaz($_POST['ObcanskyPrukaz'] ?? '');
    $zo = isset($_POST['ZbrojniOpravneni']) ? 1 : 0;
    $email = trim($_POST['Mail']);
    $mena = $match_data['Banka_ucet_MENA'];
    $dnes = date_format(new DateTime(), "d.m.Y H:i");

    $stmt = $conn->prepare("
		SELECT * FROM $table
		WHERE Cislo = ?
	    ");
    $stmt->bind_param(
        "i",
        $_POST['shooterID']
    );
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $line = mysqli_fetch_array($result);
    $disciplina = $line['DisciplinaReg'];

    //puvodni hodnoty
    $oldStaff = $line['Staff'];
    $oldStav = (int) $line['Stav'];
    $wasVIP = in_array($oldStaff, ['VIP', 'RO', 'POM']);
    $isVIP = in_array($_POST['Staff'], ['VIP', 'RO', 'POM']);

    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();
    $castka = $feeValues[0]['Value'];

    // kontrola, zda stav již není obsazený - TO-DO

    if ($oldStav != $stav) {

        $ShiftStmt = $conn->prepare("
        SELECT Stav FROM $table 
        WHERE Stav = ?
    ");
        $ShiftStmt->bind_param("i", $stav);
        $ShiftStmt->execute();
        $rows = $ShiftStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $ShiftStmt->close();

        if (count($rows) > 0) {
            $_SESSION['toast'] = [
                'type'    => 'danger',
                'message' => 'Stav je již obsazen.',
                'duration' => 2500
            ];
            header("Location: /");
            exit;
        }
    }


    // editace vyrazeneho zavodnika (bez zmeny statutu)
    if (
        $_POST['Disciplina'] == "VYRAZENO" &&
        $_POST['Staff'] == "DNS"
    ) {
        $stmt = $conn->prepare("
        UPDATE $table 
		SET Jmeno = ?,
        Prijmeni = ?,
        Stav = NULLIF(?,''),
        ObcanskyPrukaz = ?,
        ZbrojniOpravneni = ?,
        Mail = ?,
        Kategorie = ?,
        Disciplina = ?,
        Staff = ?,
        Zaplaceno = NULLIF(?,''),
        ZaplatiNaMiste = NULLIF(?,''),
        Poznamka = ?
        WHERE Cislo= ?
        ");
        $stmt->bind_param(
            "ssisssssssssi",
            $jmeno,
            $prijmeni,
            $_POST["Stav"],
            $op,
            $zo,
            $email,
            $_POST['Kategorie'],
            $_POST['Disciplina'],
            $_POST['Staff'],
            $_POST['Zaplaceno'],
            $_POST['ZaplatiNaMiste'],
            $_POST['Poznamka'],
            $_POST['shooterID']
        );

        // editace vyrazeneho zavodnika (zmena statutu na Platici PAY) --> obnovime disciplinu z DisciplineReg
    } else if (
        $_POST['Disciplina'] == "VYRAZENO" &&
        $_POST['Staff'] == "PAY"
    ) {
        $stmt = $conn->prepare("
        UPDATE $table 
		SET Jmeno = ?,
        Prijmeni = ?,
        Stav = NULLIF(?,''),
        ObcanskyPrukaz = ?,
        ZbrojniOpravneni = ?,
        Mail = ?,
        Kategorie = ?,
        Disciplina = ?,
        Staff = ?,
        CastkaZaplatit = ?,
        Zaplaceno = NULLIF(?,''),
        ZaplatiNaMiste = NULLIF(?,''),
        Poznamka = ?
        WHERE Cislo= ?
        ");
        $stmt->bind_param(
            "ssissssssssssi",
            $jmeno,
            $prijmeni,
            $_POST["Stav"],
            $op,
            $zo,
            $email,
            $_POST['Kategorie'],
            $line['DisciplinaReg'],
            $_POST['Staff'],
            $castka,
            $_POST['Zaplaceno'],
            $_POST['ZaplatiNaMiste'],
            $_POST['Poznamka'],
            $_POST['shooterID']
        );

        // editace vyrazeneho zavodnika (zmena statutu na VIP, POM, RO) --> obnovime disciplinu z DisciplineReg a Zaplatit = 0
    } else if (
        $_POST['Disciplina'] == "VYRAZENO" &&
        $isVIP
    ) {
        $stmt = $conn->prepare("
        UPDATE $table 
		SET Jmeno = ?,
        Prijmeni = ?,
        Stav = NULLIF(?,''),
        ObcanskyPrukaz = ?,
        ZbrojniOpravneni = ?,
        Mail = ?,
        Kategorie = ?,
        Disciplina = ?,
        Staff = ?,
        CastkaZaplatit = 0,
        Zaplaceno = NULLIF(?,''),
        ZaplatiNaMiste = NULLIF(?,''),
        Poznamka = ?
        WHERE Cislo= ?
        ");
        $stmt->bind_param(
            "ssisssssssssi",
            $jmeno,
            $prijmeni,
            $_POST["Stav"],
            $op,
            $zo,
            $email,
            $_POST['Kategorie'],
            $line['DisciplinaReg'],
            $_POST['Staff'],
            $_POST['Zaplaceno'],
            $_POST['ZaplatiNaMiste'],
            $_POST['Poznamka'],
            $_POST['shooterID']
        );

        // editace aktivniho neplaticiho zavodnika - zmena statutu PAY --> nastavime CastkaZaplatit a zrusime Zaplaceno 0 a Castka 0
    } else if (
        ($_POST['Disciplina'] != "VYRAZENO") &&
        $wasVIP &&
        ($_POST['Staff'] == "PAY")
    ) {
        $stmt = $conn->prepare("
        UPDATE $table
        SET Jmeno = ?,
            Prijmeni = ?,
            Stav = NULLIF(?,''),
            ObcanskyPrukaz = ?,
            ZbrojniOpravneni = ?,
            Mail = ?,
            Kategorie = ?,
            Disciplina = ?,
            Staff = ?,
            CastkaZaplatit = ?,
            Zaplaceno = 0,
            Castka = NULL,
            ZaplatiNaMiste = NULLIF(?,''),
            Poznamka = ?
        WHERE Cislo = ?
    ");
        $stmt->bind_param(
            "ssissssssissi",
            $jmeno,
            $prijmeni,
            $_POST["Stav"],
            $op,
            $zo,
            $email,
            $_POST['Kategorie'],
            $_POST['Disciplina'],
            $_POST['Staff'],
            $castka,
            $_POST['ZaplatiNaMiste'],
            $_POST['Poznamka'],
            $_POST['shooterID']
        );
        // editace aktivniho zavodnika (zmena statutu na VIP, POM, RO) --> zmenime castku na CastkaZaplatit = 0
    } else if (
        ($_POST['Disciplina'] != "VYRAZENO") &&
        $isVIP
    ) {
        $stmt = $conn->prepare("
        UPDATE $table 
		SET Jmeno = ?,
        Prijmeni = ?,
        Stav = NULLIF(?,''),
        ObcanskyPrukaz = ?,
        ZbrojniOpravneni = ?,
        Mail = ?,
        Kategorie = ?,
        Disciplina = ?,
        Staff = ?,
        CastkaZaplatit = 0,
        Zaplaceno = NULLIF(?,''),
        ZaplatiNaMiste = NULLIF(?,''),
        Poznamka = ?
        WHERE Cislo= ?
        ");
        $stmt->bind_param(
            "ssisssssssssi",
            $jmeno,
            $prijmeni,
            $_POST["Stav"],
            $op,
            $zo,
            $email,
            $_POST['Kategorie'],
            $line['Disciplina'],
            $_POST['Staff'],
            $_POST['Zaplaceno'],
            $_POST['ZaplatiNaMiste'],
            $_POST['Poznamka'],
            $_POST['shooterID']
        );
    } else {
        $stmt = $conn->prepare("
        UPDATE $table 
		SET Jmeno = ?,
        Prijmeni = ?,
        Stav = NULLIF(?,''),
        ObcanskyPrukaz = ?,
        ZbrojniOpravneni = ?,
        Mail = ?,
        Kategorie = ?,
        Disciplina = ?,
        Staff = ?,
        CastkaZaplatit = ?,
        Zaplaceno = NULLIF(?,''),
        ZaplatiNaMiste = NULLIF(?,''),
        Poznamka = ?
        WHERE Cislo= ?
        ");
        $stmt->bind_param(
            "ssissssssisssi",
            $jmeno,
            $prijmeni,
            $_POST["Stav"],
            $op,
            $zo,
            $email,
            $_POST['Kategorie'],
            $_POST['Disciplina'],
            $_POST['Staff'],
            $castka,
            $_POST['Zaplaceno'],
            $_POST['ZaplatiNaMiste'],
            $_POST['Poznamka'],
            $_POST['shooterID']
        );
    }
    $stmt->execute();
    session_start();
    if ($stmt->errno !== 0) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba databáze",
            "index.php",
            "Při vkládání do databáze došlo k chybě!",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } elseif ($stmt->affected_rows === 0) {
        $_SESSION['toast'] = [
            'type' => 'primary',
            'message' => 'V nastavení závodníka jste neprovedli žádné změny.',
            'duration' => 2000
        ];
    } else {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Změny nastavení závodníka byly úspěšně uloženy.',
            'duration' => 2500
        ];
    }
    $stmt->close();
    header("Location: /");
}

// VYRAZENI ZAVODNIKA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_shooter'])) {
    $line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);

    $dnes = date_format(new DateTime(), "d.m.Y H:i");
    $ip = ($_SERVER["REMOTE_ADDR"] . " - admin");

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
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
        exit;
    } else {
        header("refresh:0;url=index.php");

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
                    'message' => 'Nebyla nalezena žádná hromadná platba.',
                    'duration' => 2500
                ];
                header("Location: /");
                exit;
            }

            $discCount = count($rows);
            $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
            $FeeStmt->execute();
            $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $FeeStmt->close();

            // 3) Pro každý řádek aktualizujeme castku u PAY zavodnika 
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
                    'duration' => 2500
                ];
            }
        }

        // příprava mailu zavodnikovi
        // nice názvy pro mail
        $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

        $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
        $STRELEC .= "Kategorie: " . htmlspecialchars($line['Kategorie'], ENT_QUOTES, 'UTF-8') . "\r\n";
        $STRELEC .= "Disciplína: $nazev_discipliny" . "\r\n";

        $from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
        $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
        $to = $line['Mail'];
        $subject = "Zrušení registrace závodníka " . $match_data['Zavod'];
        $message = $email_text_vyrazeni;
        $message = str_replace("##STRELEC##", $STRELEC, $message);

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
                'type' => 'danger',
                'message' => 'Závodník byl vyřazen a e-mail s informací odeslán.',
                'duration' => 3000
            ];
        }
    }
}


// MAZANI ZAVODNIKA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_shooter'])) {
    $line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

    $stmt = $conn->prepare("
    DELETE FROM $table 
    WHERE Cislo = ? AND klic = ?
	");
    $stmt->bind_param(
        "ii",
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
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Závodník byl smazán a e-mail s informací o smazání odeslán.',
            'duration' => 3000
        ];
        header("refresh:0;url=index.php");

        //pri smazani zavodnika odešleme statistikovi mail
        $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
        $to = htmlspecialchars($match_data['Zavod_email_stats'], ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - smazání závodníka #" . $_POST['shooterID'];
        $message = "V administraci závodu <strong>" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> smazal admin " . $_SESSION['name'] . " závodníka #" . $line['Cislo'] . " " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " (" . $nazev_discipliny . ")." . "\r\n";
        $send_email = email($from_text, $from, $to, $subject, $message);
    }
}


// EVIDENCE UHRADY PLATBY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_paid'])) {
    $dnes = date_format(new DateTime(), "d.m.Y H:i");

    $line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);

    // ziskame castku za jednu disciplinu
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    $stmt = $conn->prepare("
    UPDATE $table 
    SET Zaplaceno = 1,
    Castka = ?,
    Mena = ?,
    DatumZaplaceni = ?
    WHERE Cislo = ? AND klic = ?
	");
    $stmt->bind_param(
        "sssii",
        $feeValues[0]['Value'],
        $match_data['Banka_ucet_MENA'],
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
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        header("refresh:0;url=index.php");

        // příprava mailu zavodnikovi
        // nice názvy pro mail
        $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

        $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " \r\n";
        $STRELEC .= "Kategorie: " . htmlspecialchars($line['Kategorie'], ENT_QUOTES, 'UTF-8') . "\r\n";
        $STRELEC .= "Discpilína: $nazev_discipliny" . "\r\n";

        $from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
        $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
        $to = $line['Mail'];
        $subject = "Evidence platby " . $match_data['Zavod'];
        $message = $email_text_platba;
        $message = str_replace("##STRELEC##", $STRELEC, $message);

        $send_email = email($from_text, $from, $to, $subject, $message);
        if (!$send_email) {
            include './components/modal-warning.php';
            WarningModal(
                "danger",
                "Chyba odeslání e-mailu",
                "index.php",
                "Při odeslání e-mailu závodníkovi došlo k chybě.",
                "Platba byla zaevidována, pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
                "Zpět do administrace"
            );
        } else {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Platba byla zaevidována a e-mail závodníkovi odeslán.',
                'duration' => 2500
            ];
        }
    }
}


// EVIDENCE HROMADNE PLATBY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_bulk_payment'])) {
    $bulkId = $_POST['shooterBULK'];

    $stmt = $conn->prepare("
        SELECT Cislo, Jmeno, Prijmeni, Mail, Disciplina, Kategorie, klic
        FROM $table
        WHERE bulkId = ? AND Zaplaceno = 0 AND Disciplina != 'VYRAZENO' ORDER BY Cislo
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

    //vypocet celkove castky
    $discCount = count($rows);
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee  ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    if ($discCount === 1) {
        $celkovaCastka = $feeValues[0]['Value'];
    } elseif ($discCount === 2) {
        $celkovaCastka = ($feeValues[0]['Value'] + $feeValues[1]['Value']);
    } else {
        $celkovaCastka = ($feeValues[0]['Value'] + $feeValues[1]['Value']) + (($discCount - 2) * $feeValues[2]['Value']);
    }

    $datumPlatby = date('Y-m-d H:i:s');

    // 3) Pro každý řádek aktualizuj platbu
    $updateStmt = $conn->prepare("
        UPDATE $table
        SET Castka = ?,
            DatPay = ?,
            Zaplaceno = 1,
            Mena = ?
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

        $updateStmt->bind_param("issi", $castka, $datumPlatby, $match_data['Banka_ucet_MENA'], $r['Cislo']);
        $updateStmt->execute();
    }
    $updateStmt->close();
    header("refresh:0;url=index.php");

    // příprava mailu zavodnikovi
    $STRELEC = "Závodník: " . htmlspecialchars($rows[0]['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($rows[0]['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Kategorie: " . htmlspecialchars($rows[0]['Kategorie'], ENT_QUOTES, 'UTF-8') . "\r\n\r\n";
    $STRELEC .= "Disciplíny:\r\n";

    foreach ($rows as $i => $r) {
        if ($i === 0) {
            $castka = $feeValues[0]['Value'];
        } elseif ($i === 1) {
            $castka = $feeValues[1]['Value'];
        } else {
            $castka = $feeValues[2]['Value'];
        }
        $nazev = getValueFromTable($conn, $table_disciplines, "Name", $r['Disciplina'], "Value");
        $STRELEC .= "- " . htmlspecialchars($nazev, ENT_QUOTES, 'UTF-8') . " (" . number_format($castka, 2, ',', ' ') . " " . $match_data['Banka_ucet_MENA'] . ") \r\n";
    }

    $from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
    $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
    $to = $rows[0]['Mail'];
    $subject = "Evidence hromadné platby " . $match_data['Zavod'];
    $message = $email_text_hromadna_platba;
    $message = str_replace("##STRELEC##", $STRELEC, $message);
    $message = str_replace("##CASTKA##", number_format($celkovaCastka, 2, ',', ' '), $message);

    $send_email = email($from_text, $from, $to, $subject, $message);
    if (!$send_email) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba odeslání e-mailu",
            "index.php",
            "Při odeslání e-mailu závodníkovi došlo k chybě.",
            "Hromadná platba byla zaevidována, pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Hromadná platba byla zaevidována a e-mail závodníkovi odeslán.',
            'duration' => 2000
        ];
    }
}

// NOVY UZIVATEL
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_user'])) {
    $username = $_POST['Username'] ?? '';
    $password = $_POST['Heslo'] ?? '';
    $jmeno = $_POST['Jmeno'] ?? '';
    $prijmeni = $_POST['Prijmeni'] ?? '';
    $email = $_POST['Mail'] ?? '';
    $role = $_POST['Role'] ?? 'viewer';
    $poradatel = $_POST['Organizer'] ?? '';

    //deaktivovana kontrola hesla
    //    if ($username && $password ) { 

    if ($username && $password && isValidPassword($password)) {
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("INSERT INTO $table_admins (username, email, password, firstname, lastname, role, organizer) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $email,  $hash, $jmeno, $prijmeni, $role, $poradatel);
        $stmt->execute();
        $stmt->close();
    } else {
        $_SESSION['toast'] = [
            'type' => 'warning',
            'message' => 'Heslo musí mít 8–255 znaků, obsahovat číslo a speciální znak.',
            'duration' => 2500
        ];
        header("Location: index.php?users");
        exit();
    }
    if ($affected === 0) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba databáze",
            "index.php",
            "Při vkládání do databáze došlo k chybě!",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        header("Location: index.php?users");

        // odešleme uživateli mail
        $UZIVATEL .= "<strong>Jméno pro přihlášení:</strong> " . $username  . "\r\n";
        $UZIVATEL .= "<strong>Heslo:</strong> pošle administrátor jinou cestou \r\n";
        $UZIVATEL .= "<strong>Role:</strong> " . $role . " " . $admin_roles[$role] . "\r\n";

        $from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
        $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
        $to = $email;
        $subject = "SSAŠ střelnice Prachatice - přístupové údaje do administrace registračního systému soutěží";
        $message = "$email_novy_uzivatel";
        $message = str_replace("##UZIVATEL##", $UZIVATEL, $message);
        $send_email = email($from_text, $from, $to, $subject, $message);

        if (!$send_email) {
            include './components/modal-warning.php';
            WarningModal(
                "danger",
                "Chyba odeslání e-mailu",
                "index.php",
                "Při odeslání e-mailu závodníkovi došlo k chybě.",
                "Uživatel byl přidán, pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
                "Zpět do administrace"
            );
        } else {
            $_SESSION['toast'] = [
                'type' => 'success',
                'message' => 'Uživatel byl přidán a e-mail s informací odeslán.',
                'duration' => 2000
            ];
        }
    }
}

// MAZANI UZIVATELE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_user'])) {

    // Získání role uživatele, který má být smazán
    $stmt = $conn->prepare("SELECT role FROM $table_admins WHERE username = ?");
    $stmt->bind_param("s", $_POST['username']);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    $userData = mysqli_fetch_assoc($result);

    // Pokud uzivatel admin, zkontrolujeme, kolik jich zbývá
    if ($userData['role'] === 'admin') {
        $stmt = $conn->prepare("SELECT COUNT(*) as pocet FROM $table_admins WHERE role = 'admin'");
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();

        $adminCount = mysqli_fetch_assoc($result)['pocet'];
        if ($adminCount <= 1) {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Nelze smazat posledního administrátora!',
                'duration' => 3500
            ];
            header("Location: index.php?users");
            exit;
        }
    }

    $stmt = $conn->prepare("
		SELECT username, firstname, lastname, email FROM $table_admins
		WHERE username = ?
	 ");
    $stmt->bind_param(
        "s",
        $_POST['username']
    );
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    $line = mysqli_fetch_assoc($result);


    $stmt = $conn->prepare("
        DELETE FROM $table_admins
        WHERE username = ?
	");
    $stmt->bind_param(
        "s",
        $_POST['username']
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
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        header("Location: index.php?users");

        //pri smazani uživatele odešleme statistikovi mail
        $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
        $to = htmlspecialchars($match_data['Zavod_email_stats'], ENT_QUOTES, 'UTF-8');
        $subject = htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - smazání uživatele " . $_POST['username'];
        $message = "V administraci závodu <strong>" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . "</strong> smazal admin " . $_SESSION['name'] . "  uživatele: " . htmlspecialchars($line['firstname'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['lastname'], ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($line['email'], ENT_QUOTES, 'UTF-8') . "\r\n";
        $send_email = email($from_text, $from, $to, $subject, $message);

        if (!$send_email) {
            include './components/modal-warning.php';
            WarningModal(
                "danger",
                "Chyba odeslání e-mailu",
                "index.php",
                "Při odeslání e-mailu statistikovi došlo k chybě.",
                "Uživatel byl smazán, pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
                "Zpět do administrace"
            );
        } else {
            $_SESSION['toast'] = [
                'type' => 'danger',
                'message' => 'Uživatel byl smazán a e-mail statistikovi odeslán.',
                'duration' => 2500
            ];
        }
    }
}

//ZMENA HESLA UZIVATELEM
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password_change'])) {
    $username       = $_SESSION['name'] ?? '';
    $passwordOld    = $_POST['password'] ?? '';
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

            // kontrola starého hesla
            if (!password_verify($passwordOld, $storedHash)) {
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'message' => 'Původní heslo není správné.',
                    'duration' => 2500
                ];
                header("Location: /");
                exit();
            }

            // kontrola shody nových hesel
            if ($passwordNew !== $passwordVerify) {
                $_SESSION['toast'] = [
                    'type' => 'warning',
                    'message' => 'Hesla se neshodují.',
                    'duration' => 2500
                ];
                header("Location: /");
                exit();
            }

            // kontrola shody puvodniho a noveho hesla
            if ($passwordOld == $passwordNew) {
                $_SESSION['toast'] = [
                    'type' => 'warning',
                    'message' => 'Nové heslo je stejné jako původní.',
                    'duration' => 2500
                ];
                header("Location: /");
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
                last_password_change = NOW()
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
            header("Location: /");
            exit();
        }
    }
}


// NOVA DISCIPLINA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_discipline'])) {
    $stmt = $conn->prepare("
        INSERT INTO $table_disciplines 
        (Name,Value,Description)
	    VALUES (?, ?, ?)
	");
    $stmt->bind_param(
        "sss",
        $_POST['Name'],
        $_POST['Value'],
        $_POST['Description']
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba databáze",
            "index.php",
            "Při vkládání do databáze došlo k chybě!",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Disciplína byla úspěšně přidána.',
            'duration' => 2000
        ];
        header("Location: index.php?disciplines");
    }
}

// MAZANI DISCIPLÍNY
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_discipline'])) {
    $stmt = $conn->prepare("
        DELETE FROM $table_disciplines 
        WHERE Name = ?
	");
    $stmt->bind_param(
        "s",
        $_POST['name']
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
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Disciplína byla smazána.',
            'duration' => 2500
        ];
        header("Location: index.php?disciplines");
    }
}

// NOVE STARTOVNE
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_fee'])) {
    $stmt = $conn->prepare("
        INSERT INTO $table_fee 
        (Count,Value)
	    VALUES (?, ?)
	");
    $stmt->bind_param(
        "ii",
        $_POST['Count'],
        $_POST['Value']
    );
    $stmt->execute();
    $affected = $stmt->affected_rows;
    $stmt->close();

    if ($affected === 0) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba databáze",
            "index.php",
            "Při vkládání do databáze došlo k chybě!",
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Startovné bylo úspěšně přidáno.',
            'duration' => 2000
        ];
        header("Location: index.php?fee");
    }
}


// MAZANI STARTOVNÉHO
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_fee'])) {
    $stmt = $conn->prepare("
        DELETE FROM $table_fee
        WHERE Count = ?
	");
    $stmt->bind_param(
        "s",
        $_POST['count']
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
            "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Položka startovného byla smazána.',
            'duration' => 2500
        ];
        header("Location: index.php?fee");
    }
}


// INLINE EDITACE PRO UZIVATELE, DISCIPLINY, STARTOVNE (JEDNO POLE - JEDEN ZAZNAM)
if (isset($_POST['update'])) {
    $table = $_POST['table'];
    $field = $_POST['field'];
    $id = intval($_POST['id']);
    $value = $_POST['value'];

    $allowedTables = [
        '$table_admins' => ['username', 'email', 'role', 'firstname', 'lastname', 'organizer', 'password', 'force_password_change'],
        $table_disciplines => ['Name', 'Value', 'Description', 'Shift_from', 'Shift_to'],
        $table_fee => ['Value']
    ];

    if (array_key_exists($table, $allowedTables) && in_array($field, $allowedTables[$table])) {

        // zmena hesla
        if ($table === '$table_admins' && $field === 'password') {
            // prazdny input
            if (trim($value) === '') {
                $_SESSION['toast'] = [
                    'type' => 'danger',
                    'message' => 'Není možné nastavit prázdné heslo. Opakujte změnu hesla a zadejte heslo vyhovující požadavkům na sílu hesla.',
                    'duration' => 3000
                ];
            exit;
            }
            $hash = password_hash($value, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                UPDATE $table_admins 
                SET password = ?, last_password_change = NOW() , force_password_change = 1
                WHERE id = ?
            ");
            $stmt->bind_param("si", $hash, $id);
        } else {
            // bezny update
            $stmt = $conn->prepare("UPDATE `$table` SET `$field` = ? WHERE id = ?");
            $stmt->bind_param("si", $value, $id);
        }

        $stmt->execute();
        $stmt->close();
    }

    exit;
}

// vyprazdneni tabulky zavodniku
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['truncate_table'])) {

    if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        exit('Nemáte oprávnění');
    }

    if (
        empty($_POST['csrf_token']) ||
        empty($_SESSION['csrf_token']) ||
        !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
    ) {
        exit('Neplatný CSRF token.');
    }

    $conn->query("TRUNCATE TABLE `$table`");

    $_SESSION['toast'] = [
        'type' => 'danger',
        'message' => 'Tabulka závodníků byla úspěšně vyprázdněna. Závodníci se budou přidávat s číslem od 1 ',
        'duration' => 2500
    ];
    header("Location: /");
    exit;
}

?>

<script type='text/javascript'>
    var myModal = new bootstrap.Modal(document.getElementById('myModal'));
    myModal.show();
</script>