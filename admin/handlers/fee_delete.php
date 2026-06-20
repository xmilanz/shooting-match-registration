<?php
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
    logAction("fee delete");
    $_SESSION['toast'] = [
        'type' => 'danger',
        'message' => 'Položka startovného byla smazána.',
        'duration' => 2500
    ];
    header("Location: index.php?fee");
}
