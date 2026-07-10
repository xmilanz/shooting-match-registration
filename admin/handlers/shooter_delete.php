<?php
$line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);
$nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
$nazev_kategorie = getValueFromTable($conn, $table_categories, "Name", $line['Kategorie'], "Value");

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
    logAction("shooter delete");
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
exit();
?>

