<?php
$ip = ($_SERVER["REMOTE_ADDR"]);
$cislo = $_POST['shooterID'];
$klic = $_POST['shooterKEY'];

$line = getShooterData($conn, $table, $cislo, $klic);

// nice nazev pro mail
$nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
$nazev_kategorie = getValueFromTable($conn, $table_categories, "Name", $line['Kategorie'], "Value");

if (!$line) {
    include './components/modal-warning.php';
    WarningModal(
        "Vyřazení závodníka",
        "index.php",
        "<div class='col-12 fw-bolder text-danger'>Nelze dohledat závodníka.</div>",
        "Kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba vyrazeni zavodnika'>pořadatele závodu</a>.",
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'registrace.php';\">Zpět</button>",
        "$poradatel"
    );
    exit;
}
$stmt = $conn->prepare("
        UPDATE $table 
        SET DisciplinaReg = ?,
            Disciplina = 'VYRAZENO', 
            CastkaZaplatit = NULL,
            Staff = 'DNS',
            Vyrazeno = ?, 
            VyrazenoIP = ?,
            bulkId = 0
        WHERE Cislo = ? AND klic = ?
    ");
$stmt->bind_param(
    "sssii",
    $line['Disciplina'],
    $dnesText,
    $ip,
    $cislo,
    $klic
);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected === 0) {
    include './components/modal-warning.php';
    WarningModal(
        "Chyba databáze",
        "registrace.php",
        "<div class='col-12 fw-bolder text-danger'>Při vkládání do databáze došlo k chybě!</div>",
        "Zkuste to později nebo kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba aktualizace databáze [$table]'>pořadatele závodu</a>.",
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'registrace.php';\">Zpět na registraci</button>",
        "$poradatel"
    );
    exit;
} else {
    include './components/modal-warning.php';
    WarningModal(
        "Vyřazení závodníka",
        "index.php",
        "<div class='col-12 fw-bolder text-danger'>Závodník " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($nazev_discipliny, ENT_QUOTES, 'UTF-8') . ")<br>byl vyřazen ze závodu $match_data[Zavod].</div>",
        "E-mail s informací byl odeslán na adresu " . htmlspecialchars($line['Mail'], ENT_QUOTES, 'UTF-8') . " zadanou při registraci.",
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zavřít</button>",
        "$poradatel"
    );
}

// prepocitame castku k zaplaceni u hromadnych registraci
$bulkId = $line['bulkId'];

if ($bulkId > 0) {
    // Získání zbývajících závodníků v BULK
    $stmt = $conn->prepare("
            SELECT Cislo, Disciplina 
            FROM $table 
            WHERE bulkId = ? AND Disciplina != 'VYRAZENO' ORDER BY Cislo
        ");
    $stmt->bind_param("i", $bulkId);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    if (empty($rows)) {
        $_SESSION['toast'] = [
            'type'    => 'danger',
            'message' => 'Nenalezena žádná hromadná platba.',
            'duration' => 2500
        ];
        header("Location: index.php");
        exit;
    }

    $discCount = count($rows);
    $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
    $FeeStmt->execute();
    $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $FeeStmt->close();

    // 3) Pro každý řádek aktualizujeme castku u zavodnika
    if ($line['Staff'] == PAY) {
        $updateStmt = $conn->prepare("
        UPDATE $table
        SET CastkaZaplatit = ?
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

            $updateStmt->bind_param("ii", $castka, $r['Cislo']);
            $updateStmt->execute();
        }
        $updateStmt->close();
    }

    // Pokud zůstala jen jedna disciplína → změna bulkId na 0
    if ($discCount === 1) {
        $soloCislo = $rows[0]['Cislo'];
        $bulkResetStmt = $conn->prepare("UPDATE $table SET bulkId = 0 WHERE Cislo = ?");
        $bulkResetStmt->bind_param("i", $soloCislo);
        $bulkResetStmt->execute();
        $bulkResetStmt->close();

        $_SESSION['toast'] = [
            'type'    => 'info',
            'message' => 'Z hromadné registrace zůstala jen 1 disciplína.',
            'duration' => 2000
        ];
    }
}

// posilame mail zavodnikovi
$STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
$STRELEC .= "Disciplina: $nazev_discipliny" . "\r\n";
$STRELEC .= "Kategorie: $nazev_kategorie" . "\r\n";

$from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
$from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
$to = htmlspecialchars($line['Mail'], ENT_QUOTES, 'UTF-8');
$subject = "Zrušení registrace závodníka " . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8');
$message = $email_text_vyrazeni_vlastni;
$message = str_replace("##STRELEC##", $STRELEC, $message);

$send_email = email($from_text, $from, $to, $subject, $message);
if (!$send_email) {
    include './components/modal-warning.php';
    WarningModal(
        "Chyba odeslání e-mailu",
        "index.php",
        "<div class='col-12 fw-bolder text-danger'>Při odeslání e-mailu došlo k chybě</div>",
        "Závodník je vyřazený. Kontaktujte <a href='mailto:" . htmlspecialchars($line['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu na [$email]'>pořadatele závodu</a>.",
        "<button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = 'index.php';\">Zpět</button>",
        "$poradatel"
    );
    exit;
}
?>