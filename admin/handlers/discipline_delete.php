<?php
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
    logAction("discipline delete");
    $_SESSION['toast'] = [
        'type' => 'danger',
        'message' => 'Disciplína byla smazána.',
        'duration' => 2500
    ];
    header("Location: index.php?disciplines");
}
