<?php
$alias = trim(mb_convert_case($_POST['Alias'], MB_CASE_UPPER, "UTF-8"));
$jmeno = trim(mb_convert_case($_POST['Jmeno'], MB_CASE_TITLE, "UTF-8"));
$prijmeni = trim(mb_convert_case($_POST['Prijmeni'], MB_CASE_TITLE, "UTF-8")) . $_POST['Prijmeni_stav'] . '';
$stav = (int) $_POST['Stav'];
$op = normalizePrukaz($_POST['ObcanskyPrukaz'] ?? '');
$zo = isset($_POST['ZbrojniOpravneni']) ? 1 : 0;
$cz = normalizePrukaz(trim($_POST['CisloZbrane'] ?? ''));
$nz = normalizeText(trim($_POST['NazevZbrane'] ?? ''));
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

//ziskani puvodnich hodnot pro kontrolu zmeny statu a VIP statusu
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
        exit();
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
        CisloZbrane = ?,
        NazevZbrane = ?,
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
        "ssisisssssssssi",
        $jmeno,
        $prijmeni,
        $_POST["Stav"],
        $op,
        $zo,
        $cz,
        $nz,
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
        CisloZbrane = ?,
        NazevZbrane = ?,
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
        "ssisisssssssi",
        $jmeno,
        $prijmeni,
        $_POST["Stav"],
        $op,
        $zo,
        $cz,
        $nz,
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
        CisloZbrane = ?,
        NazevZbrane = ?,
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
        "ssisissssssssi",
        $jmeno,
        $prijmeni,
        $_POST["Stav"],
        $op,
        $zo,
        $cz,
        $nz,
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
            CisloZbrane = ?,
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
        "ssisisssssissi",
        $jmeno,
        $prijmeni,
        $_POST["Stav"],
        $op,
        $zo,
        $cz,
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
        CisloZbrane = ?,
        NazevZbrane = ?,
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
        "ssisisssssssssi",
        $jmeno,
        $prijmeni,
        $_POST["Stav"],
        $op,
        $zo,
        $cz,
        $nz,
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
        CisloZbrane = ?,
        NazevZbrane = ?,
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
        "ssisissssssisssi",
        $jmeno,
        $prijmeni,
        $_POST["Stav"],
        $op,
        $zo,
        $cz,
        $nz,
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
    logAction("shooter edit");
    $_SESSION['toast'] = [
        'type' => 'success',
        'message' => 'Změny nastavení závodníka byly úspěšně uloženy.',
        'duration' => 2500
    ];
}
$stmt->close();
header("Location: /");
exit();
?>
