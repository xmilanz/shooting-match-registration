<?php
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
        logAction("user delete");
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Uživatel byl smazán a e-mail statistikovi odeslán.',
            'duration' => 2500
        ];
    }
}
