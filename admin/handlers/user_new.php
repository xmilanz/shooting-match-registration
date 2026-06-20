<?php
$username = $_POST['Username'] ?? '';
$password = $_POST['Heslo'] ?? '';
$jmeno = $_POST['Jmeno'] ?? '';
$prijmeni = $_POST['Prijmeni'] ?? '';
$email = $_POST['Mail'] ?? '';
$role = $_POST['Role'] ?? 'viewer';
$poradatel = $_POST['Organizer'] ?? '';

if ($username && $password && isValidPassword($password)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO $table_admins (username, email, password, firstname, lastname, role, organizer,force_password_change) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
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
    $UZIVATEL = "<strong>Jméno pro přihlášení:</strong> " . $username  . "\r\n";
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
        logAction("user new");
        $_SESSION['toast'] = [
            'type' => 'success',
            'message' => 'Uživatel byl přidán a e-mail s informací odeslán.',
            'duration' => 2000
        ];
    }
}
