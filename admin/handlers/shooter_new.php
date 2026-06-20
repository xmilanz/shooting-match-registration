<?php
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
        logAction("shooter new");
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Závodník byl úspěšně zaregistrován a registrační e-mail odeslán.',
            'duration' => 2500
        ];
        header("refresh:0;url=index.php");
    }
}
