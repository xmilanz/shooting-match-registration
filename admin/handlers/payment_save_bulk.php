<?php
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

$nazev_kategorie = getValueFromTable($conn, $table_categories, "Name", $rows[0]['Kategorie'], "Value");

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
$STRELEC .= "Kategorie: $nazev_kategorie \r\n\r\n";
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
exit();
?>
