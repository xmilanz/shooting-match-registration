<?php
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
    //$link = "<a href='" . htmlspecialchars($reg_url, ENT_QUOTES, 'UTF-8') . "/zrus_ucast.php?id=" . rawurlencode($cislo) . "&klic=" . rawurlencode($klic) . "'>Zrušit účast</a>";
    $link = buildCancelLinks($reg_url, $cislo, $klic);
    $link_ical = buildCalendarLinks($reg_url, $match_data);

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
$STRELEC = "Závodník: " . htmlspecialchars($jmeno, ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($prijmeni, ENT_QUOTES, 'UTF-8') . " [$link_ical] " . "\r\n";
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
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zpět</button>",
        "$poradatel"
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
