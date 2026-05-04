<?php

declare(strict_types=1);
include("./header.php");

$usernameInput = trim((string)($_POST['username'] ?? ''));
$passwordInput = (string)($_POST['password'] ?? '');

if ($usernameInput === '' || $passwordInput === '') {
    exit('Zadejte jméno a heslo');
}

// pri pouzivani dedikovane administrace to neni potreba
if ($stmt = $conn->prepare("SELECT id, password, role, organizer,force_password_change FROM $table_admins WHERE username = ? LIMIT 1")) {
    $stmt->bind_param('s', $usernameInput);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $hashFromDb, $role, $organizer, $force_password_change);
        $stmt->fetch();

        // ověření hesla
        if (password_verify($passwordInput, $hashFromDb)) {

            // Zabrání session fixation
            session_regenerate_id(true);

            // Bezpečné uložení údajů do session
            $_SESSION['admin_id']      = $id;
            $_SESSION['name']          = $usernameInput;
            $_SESSION['role']          = $role;
            $_SESSION['organizer']     = $organizer;
            $_SESSION['loggedin']      = true;

            // Bezpečnostní metadata
            $_SESSION['user_agent']    = $_SERVER['HTTP_USER_AGENT'] ?? '';
            $_SESSION['ip_fragment']   = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 7);
            $_SESSION['last_activity'] = time();

            // Sanitizace závodu
            $_SESSION['zavod_id'] = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

            if ($force_password_change == 1) {
                // Redirect na zmenu hesla
                header('Location: password_change.php');
                exit;
            }

            // Redirect do administrace
            header('Location: ' . $admin_url);
            $stmt->close();
            exit;
        } else {
            include __DIR__ . '/components/modal-warning.php';
            WarningModal(
                "Přihlášení do administrace závodu",
                "login.php",
                "<div class='col-12 fw-bolder text-danger'>Chyba autentizace.</div>",
                "Zadejte správné heslo a zkuste to znovu.",
                "<button type='button' class='btn btn-outline-danger' onclick=\"window.location.href = 'login.php';\">Zpět na přihlášení</button>"
            );
        }
    } else {
        include __DIR__ . '/components/modal-warning.php';
        WarningModal(
            "Přihlášení do administrace závodu",
            "login.php",
            "<div class='col-12 fw-bolder text-danger'>Chyba autentizace.</div>",
            "Zadejte správné uživatelské jméno a heslo a zkuste to znovu.",
            "<button type='button' class='btn btn-outline-danger' onclick=\"window.location.href = 'login.php';\">Zpět na přihlášení</button>"
        );
    }

    $stmt->close();
} else {
    include './components/modal-warning.php';
    WarningModal(
        "Chyba databáze",
        "registrace.php",
        "<div class='col-12 fw-bolder text-danger'>Při dotazu do do databáze došlo k chybě!</div>",
        "Zkuste to později nebo kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba databáze při přihlášení uživatele [$table]'>pořadatele závodu</a>.",
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'login.php';\">Zpět na přihlášení</button>"
    );
    exit;
}
include __DIR__ . '/footer.php';
