<?php
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
exit();
?>
