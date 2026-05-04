<?php
include "./header.php";
$stmt = $conn->prepare("
		SELECT Prijmeni,Jmeno,Disciplina FROM $table
		 where UPPER(`Disciplina`) != 'VYRAZENO'  ORDER BY Disciplina
	");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>

<!-- dataTable https://datatables.net/download/ -->
<script type="text/javascript" src="js/datatable_conf.js"></script>
<link href="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.2.1/b-3.2.0/b-colvis-3.2.0/b-html5-3.2.0/b-print-3.2.0/cr-2.0.4/fc-5.0.4/fh-4.0.1/r-3.0.3/sp-2.3.3/datatables.min.css" rel="stylesheet">
<script src="https://cdn.datatables.net/v/bs5/jszip-3.10.1/dt-2.2.1/b-3.2.0/b-colvis-3.2.0/b-html5-3.2.0/b-print-3.2.0/cr-2.0.4/fc-5.0.4/fh-4.0.1/r-3.0.3/sp-2.3.3/datatables.min.js"></script>
<!-- dataTable  -->

<h1 class='p-3'>Závodníci</h1>
<div class="col-md-12">
    <table id="zavodnici" class="table table-striped table-bordered bg-white">
        <thead>
            <tr>
                <th>Příjmení</th>
                <th>Jméno</th>
                <th>Disciplína</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($line = $result->fetch_assoc()) {
                $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
                echo "<TR>";
                echo "<TD>";
                echo
                htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "</TD><TD>" . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . "</TD><TD>" . $nazev_discipliny . "</TD>";
                echo "</TR>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php
// --------------------------- //
// tabulka s pocty zavodniku  //
// -------------------------- //

// celkovy pocet závodníků (zaplaceno, VIP, rozhodčí a pomocníci)
if (!$match_data['Payment_before']) {
    $stmt = $conn->prepare("
		SELECT count(Disciplina) as comp FROM $table
		where UPPER(`Disciplina`) != 'VYRAZENO'
	");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    echo "<h3 class='pl-3 pt-3'>Závodníci s potvrzenou účastí</h3>";
    $stmt = $conn->prepare("
        SELECT count(Disciplina) as comp FROM $table    
        where UPPER(`Disciplina`) != 'VYRAZENO' and Zaplaceno = 1
    ");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
}
$zavodniciCelkem = $result->fetch_object()->comp;

// pocet neplaticich zavodniku (VIP, rozhodci a pomocnici)
$stmt = $conn->prepare("
    SELECT count(Disciplina) as notpay FROM $table    
    where UPPER(`Disciplina`) != 'VYRAZENO' and (Staff  = 'RO' or Staff = 'POM' or Staff = 'VIP')
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$zavodniciNeplati = $result->fetch_object()->notpay;

// pocet zavodniku, kteri zaplatili
$stmt = $conn->prepare("
    SELECT count(Disciplina) as paid FROM $table    
    where UPPER(`Disciplina`) != 'VYRAZENO' and Zaplaceno = 1
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$zavodniciZaplaceno = $result->fetch_object()->paid;

// pocet zavodniku, kteri dosud nezaplatili 
$stmt = $conn->prepare("
    SELECT count(Disciplina) as unpaid FROM $table    
    where UPPER(`Disciplina`) != 'VYRAZENO' and Zaplaceno = 0
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$zavodniciNezaplaceno = $result->fetch_object()->unpaid;


// tabulka s pocty zavodniku v jednotlivych divizich
if (!$match_data['Payment_before']) {
    $stmt = $conn->prepare("
        SELECT Disciplina,Count(Prijmeni) as Count FROM $table    
        where UPPER(`Disciplina`) != 'VYRAZENO' GROUP BY Disciplina HAVING count(Prijmeni)>=1 ORDER BY Disciplina
    ");
} else {
    $stmt = $conn->prepare("
        SELECT Disciplina,Count(Prijmeni) as Count FROM $table    
        where UPPER(`Disciplina`) != 'VYRAZENO' and Zaplaceno=1 GROUP BY Disciplina HAVING count(Prijmeni)>=1 ORDER BY Disciplina
    ");
}
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<div class="row pl-3 pt-3">
    <div class="col-md-4">
        <table id="zavodnici" class="table table-striped table-bordered bg-white">
            <thead>
                <tr>
                    <th colspan="2">Počet závodníků: <small><?= $zavodniciCelkem ?> (<?= $zavodniciNeplati ?> rozhodčích a pomocníků)</small></th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($line = $result->fetch_assoc()) {
                    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");
                    echo "<tr><td>" . $nazev_discipliny . "</td><td>" . htmlspecialchars($line['Count'], ENT_QUOTES, 'UTF-8') . "</td></tr>\n";
                }
                ?>
            </tbody>
        </table>
        <div class="my-3 <?= $paymentBeforeClass ?>">
            <h3>Přehled placení</h3>
            <ul>
                <li>zaplaceno: <?= $zavodniciZaplaceno ?></li>
                <li>nezaplaceno: <?= $zavodniciNezaplaceno ?></li>
            </ul>
        </div>
    </div>
</div>
<?php
include "./footer.php";
?>