<?php
include("./header.php");
$shooterID = intval($_GET['id']);
$shooterKEY = intval($_GET['klic']);

$line = getShooterData($conn, $table, $shooterID, $shooterKEY);

// NELZE DOHLEDAT ZAVODNIKA
if (!$line) {
    include './components/modal-warning.php';
    WarningModal(
        "Vyřazení závodníka",
        "index.php",
        "<div class='col-12 fw-bolder text-danger'>Nelze dohledat závodníka v databázi",
        "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba dohledání zavodnika'>pořadatele závodu</a>.",
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zavřít</button>"
    );
    exit;
}

// ZAVODNIK UZ JE VYRAZENY (disciplina je VYRAZENO)
if ($line['Disciplina'] == 'VYRAZENO') {
    include './components/modal-warning.php';
    WarningModal(
        "Vyřazení závodníka",
        "index.php",
        "Závodník " . $line['Cislo'] . " " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " je už vyřazený.",
        "Pokud jste tuto akci neprovedli, neprodleně nás kontaktujte.",
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zavřít</button>"
    );
    exit;
}

// KONEČNĚ VYŘAZUJEME
else {
    include './components/modal-warning-form.php';
    WarningModalForm(
        "Vyřazení závodníka",
        "index.php",
        [
            "shooterID" => $shooterID,
            "shooterKEY" => $shooterKEY
        ],
        "Závodník #" . $line['Cislo'] . " " . htmlspecialchars($line['Jmeno']) . " " . htmlspecialchars($line['Prijmeni']) . " (" . htmlspecialchars($line['Disciplina'], ENT_QUOTES, 'UTF-8') . ") bude vyřazen.",
        "Pokud jste provedli platbu registračního poplatku,<br>můžete místo vyřazení přenést startovné na jiného závodníka.",
        "./save.php",
        "cancel_shooter",
        "Vyřadit závodníka"
    );
}
