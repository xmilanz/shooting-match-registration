<?php
include "header.php";
session_start();

if (empty($_POST['username']) || empty($_POST['password'])) {
    exit('Zadejte jméno a heslo');
}
if ($stmt = $conn->prepare('SELECT id, password, role, organizer FROM site_admins WHERE username = ?')) {
    $stmt->bind_param('s', $_POST['username']);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id, $password, $role, $organizer);
        $stmt->fetch();

        if (($organizer != $poradatel) && ($organizer != all)) {
            include './components/modal-warning.php';
            WarningModal(
                "Přihlášení do administrace závodu",
                "login.php",
                "<div class='col-12 fw-bolder text-danger'>Nemáte oprávnění pro přihlášení k administraci tohoto zábvodu.",
                "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - blokovany pristup pro [$_POST[username]] - pořadatel [$organizer]'>vývojáře</a> nebo ajťáka klubu.",
                "<button type='button' class='btn btn-outline-danger' onclick=\"window.location.href = 'login.php';\">Zpět na přihlášení</button>"
            );
        }
        if (password_verify($_POST['password'], $password)) {
            session_regenerate_id();
            $_SESSION['loggedin'] = TRUE;
            $_SESSION['name'] = $_POST['username'];
            $_SESSION['id'] = $id;
            $_SESSION['role'] = $role;
            header('Location: ./admin/index.php');
        } else {
            include './components/modal-warning.php';
            WarningModal(
                "Přihlášení do administrace závodu",
                "login.php",
                "<div class='col-12 fw-bolder text-danger'>Chyba autentizace.",
                "Zadejte správné heslo a zkuste to znovu.",
                "<button type='button' class='btn btn-outline-danger' onclick=\"window.location.href = 'login.php';\">Zpět na přihlášení</button>"
            );
        }
    } else {
        include './components/modal-warning.php';
        WarningModal(
            "Přihlášení do administrace závodu",
            "login.php",
            "<div class='col-12 fw-bolder text-danger'>Chyba autentizace - uživatel '" . htmlspecialchars($_POST['username']) . "' neexistuje.",
            "Zadejte správné uživatelské jméno a heslo a zkuste to znovu.",
            "<button type='button' class='btn btn-outline-danger' onclick=\"window.location.href = 'login.php';\">Zpět na přihlášení</button>"
        );
    }
    $stmt->close();
}
include "footer.php";
