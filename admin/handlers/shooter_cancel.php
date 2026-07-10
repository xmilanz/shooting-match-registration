<?php
$line = getShooterData($conn, $table, $_POST['shooterID'], $_POST['shooterKEY']);

$dnes = date_format(new DateTime(), "d.m.Y H:i");
$ip = ($_SERVER["REMOTE_ADDR"] . " - admin");

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
    $dnes,
    $ip,
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
    exit;
} else {
    header("refresh:0;url=index.php");

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
                'message' => 'Nebyla nalezena žádná hromadná platba.',
                'duration' => 2500
            ];
            header("Location: /");
            exit;
        }

        $discCount = count($rows);
        $FeeStmt = $conn->prepare("SELECT * FROM $table_fee ORDER BY Count");
        $FeeStmt->execute();
        $feeValues = $FeeStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $FeeStmt->close();

        // 3) Pro každý řádek aktualizujeme castku u PAY zavodnika 
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
                'duration' => 2500
            ];
        }
    }

    // příprava mailu zavodnikovi
    // nice názvy pro mail
    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
    $nazev_kategorie = getValueFromTable($conn, $table_categories, "Name", $line['Kategorie'], "Value");

    $STRELEC = "Závodník: " . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "\r\n";
    $STRELEC .= "Kategorie: $nazev_kategorie" . "\r\n";
    $STRELEC .= "Disciplína: $nazev_discipliny" . "\r\n";

    $from_text = htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8');
    $from = htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8');
    $to = $line['Mail'];
    $subject = "Zrušení registrace závodníka " . $match_data['Zavod'];
    $message = $email_text_vyrazeni;
    $message = str_replace("##STRELEC##", $STRELEC, $message);

    $send_email = email($from_text, $from, $to, $subject, $message);
    if (!$send_email) {
        include './components/modal-warning.php';
        WarningModal(
            "danger",
            "Chyba odeslání e-mailu",
            "index.php",
            "Při odeslání e-mailu závodníkovi došlo k chybě.",
            "Pro odstranění problému s odesíláním kontaktujte <a href='mailto:" . htmlspecialchars($vyvojar, ENT_QUOTES, 'UTF-8') . "?subject=" . htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') . " - chyba odeslani e-mailu'>vývojáře</a> registračního systému.",
            "Zpět do administrace"
        );
    } else {
        logAction("shooter cancel");
        $_SESSION['toast'] = [
            'type' => 'danger',
            'message' => 'Závodník byl vyřazen a e-mail s informací odeslán.',
            'duration' => 3000
        ];
    }
    exit();
}
