<?php
include "./header.php";
$stmt = $conn->prepare("
		SELECT Prijmeni,Jmeno,Disciplina,Kategorie FROM $table 
        where Disciplina != 'VYRAZENO' ORDER BY Prijmeni
	");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

// pokud je pouze jedna disciplína
$stmtDiscCount = $conn->prepare("
        SELECT count(Name) as discCount FROM $table_disciplines
        ");
$stmtDiscCount->execute();
$result = $stmtDiscCount->get_result();
$stmtDiscCount->close();

$discCount = $result->fetch_object()->discCount;

// celkovy pocet závodníků (zaplaceno, VIP, rozhodčí a pomocníci)
echo "<h3 class='pl-3 pt-3'>Obsazenost <span class='" . ($discCount == 1 ? 'd-none' : '') . "'>disciplín  a </span>kategorií</h3>";
if ($match_data['Payment_before'] == 0) {
    $stmt = $conn->prepare("
		SELECT count(Prijmeni) as comp FROM $table
		where Disciplina != 'VYRAZENO'
	");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $stmt = $conn->prepare("
        SELECT count(Prijmeni) as comp FROM $table    
        where Disciplina != 'VYRAZENO' and Zaplaceno = 1   
        ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
$zavodniciCelkem = $result->fetch_object()->comp;

// pocet neplaticich zavodniku (VIP, rozhodci a pomocnici)
$stmt = $conn->prepare("
    SELECT count(Prijmeni) as notpay FROM $table    
    where Disciplina != 'VYRAZENO' and (Staff  = 'RO' or Staff = 'POM' or Staff = 'VIP')
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$zavodniciNeplati = $result->fetch_object()->notpay;

// pocet zavodniku, kteri zaplatili
$stmt = $conn->prepare("
    SELECT count(Prijmeni) as paid FROM $table    
    where Disciplina != 'VYRAZENO' and Zaplaceno = 1
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$zavodniciZaplaceno = $result->fetch_object()->paid;

// pocet zavodniku, kteri dosud nezaplatili 
$stmt = $conn->prepare("
    SELECT count(Prijmeni) as unpaid FROM $table    
    where Disciplina != 'VYRAZENO' and Zaplaceno IS NULL
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$zavodniciNezaplaceno = $result->fetch_object()->unpaid;

// tabulky

$paidOnly = $match_data['Payment_before'] == 1;

$sql = "
    SELECT Disciplina, count(Prijmeni) AS Count
    FROM $table
    WHERE Disciplina != 'VYRAZENO'
";

if ($paidOnly) {
    $sql .= " AND Zaplaceno = 1";
}

$sql .= " GROUP BY Disciplina ORDER BY Disciplina";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!-- tabulky přehledů -->
<div class="row pl-3 pt-3">
    <div class="<?= $discCount == 1 ? 'd-none' : '' ?> col-md-4">
        <table id="zavodnici" class="table table-bordered bg-white">
            <?php
            echo "<thead><tr><th colspan='2'>Počet závodníků: <small>$zavodniciCelkem ($zavodniciNeplati rozhodčích a pomocníků)</small></th></tr></thead>";
            echo "<tbody><tr><td><dl>";

            while ($line = $result->fetch_assoc()) {
                $nazevDisciplina = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
                $disciplina = $line['Disciplina'];

                echo "<dt>$nazevDisciplina</dt>";

                // ---------- kategorie ----------
                $stmt = "
                    SELECT Kategorie, COUNT(Prijmeni) AS Count
                    FROM $table
                    WHERE Disciplina != 'VYRAZENO' AND Disciplina = ?
                ";

                if ($paidOnly) {
                    $stmt .= " AND Zaplaceno = 1";
                }

                $stmt .= " GROUP BY Kategorie ORDER BY Kategorie";
                $stmt = $conn->prepare($stmt);
                $stmt->bind_param("s", $disciplina);
                $stmt->execute();
                $cats = $stmt->get_result();

                while ($cat = $cats->fetch_assoc()) {
                    $nazevKategorie = getValueFromTable($conn, $table_categories, "Name", $cat['Kategorie'], "Value");
                    echo "<dd>&nbsp;&nbsp;<small>- {$nazevKategorie}: {$cat['Count']}</small></dd>";
                }

                $stmt->close();
            }

            echo "</dl></td></tr></tbody>";

            ?>
        </table>
    </div>
    <?php
    // tabulka s pocty zavodniku v jednotlivych disciplínách

    if ($match_data['Payment_before'] == 1) {
        $stmt = $conn->prepare("
        SELECT Disciplina,count(Prijmeni) as Count FROM $table
        where Disciplina != 'VYRAZENO' and Zaplaceno=1 GROUP BY Disciplina HAVING count(Prijmeni)>=1 ORDER BY Disciplina
        
    ");
    } else {
        $stmt = $conn->prepare("
        SELECT Disciplina,count(Prijmeni) as Count FROM $table
        where Disciplina != 'VYRAZENO' GROUP BY Disciplina HAVING count(Prijmeni)>=1 ORDER BY Disciplina
    ");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    ?>
    <div class="<?= $discCount == 1 ? 'd-none' : '' ?> col-md-4">
        <table id="zavodnici" class="table table-bordered bg-white">
            <thead>
                <tr>
                    <th>Disciplina</th>
                    <th>Počet závodníků</th>
                </tr>
            </thead>
            <?php
            while ($line = $result->fetch_assoc()) {
                $nazev_Disciplina = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
                echo "<tbody><tr><td>" . $nazev_Disciplina . "</td><td>" . htmlspecialchars($line['Count'], ENT_QUOTES, 'UTF-8') . "</td></tr><tbody>";
            }
            ?>

        </table>
    </div>
    <?php

    // tabulka s pocty zavodniku v jednotlivych kategoriích
    if ($match_data['Payment_before'] == 1) {
        $stmt = $conn->prepare("
        SELECT Kategorie,count(Prijmeni) as Count FROM $table    
        where Disciplina != 'VYRAZENO' and Zaplaceno=1 GROUP BY Kategorie HAVING count(Prijmeni)>=1 ORDER BY Kategorie
    ");
    } else {
        $stmt = $conn->prepare("
        SELECT Kategorie,count(Prijmeni) as Count FROM $table    
        where Disciplina != 'VYRAZENO' GROUP BY Kategorie HAVING count(Prijmeni)>=1 ORDER BY Kategorie
    ");
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    ?>
    <div class="col-md-4">
        <table id="zavodnici" class="table table-bordered bg-white">
            <thead>
                <tr>
                    <th>Kategorie</th>
                    <th>Počet závodníků</th>
                </tr>
            </thead>
            <?php
            while ($line = $result->fetch_assoc()) {
                $nazev_kategorie = getValueFromTable($conn, $table_categories, "Name", $line['Kategorie'], "Value");
                echo "<tbody><tr><td>" . $nazev_kategorie . "</td><td>" . htmlspecialchars($line['Count'], ENT_QUOTES, 'UTF-8') . "</td></tr><tbody>";
            }
            ?>
        </table>
    </div>
</div>
<div class="row pl-3 pt-3">
    <div class="my-3 <?= hidden($match_data['Payment_before'] == 0); ?>">
        <h3>Přehled placení</h3>
        <ul>
            <li>zaplaceno: <?= $zavodniciZaplaceno ?></li>
            <li>nezaplaceno: <?= $zavodniciNezaplaceno ?></li>
        </ul>
    </div>
</div>
<?php
include "./footer.php";
?>