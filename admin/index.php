<?php

include "header.php";
$zobrazenoBulkId = [];
$zobrazenoBulkEmail = [];
$zobrazenoBulkPlatba = [];
?>
<div id="main">
    <div class="content">
        <button class="btn btn-secondary btn-rounded my-2" onclick="ToggleFilter()">Zobrazit / skrýt filtr</button>
        <?php
        $ip = $_SERVER['REMOTE_ADDR'];

        // Dotaz pro získání závodníků
        $table   = preg_replace('/[^a-zA-Z0-9_]/', '', $table);
        if ($match_data['Payment_before'] == "on") {
            $stmt = $conn->prepare("
            SELECT 
        Cislo,
        CONCAT (Prijmeni, ' ', Jmeno) AS 'Příjmení Jméno',
        TRIM(CONCAT(ObcanskyPrukaz,' ',IF(ZbrojniOpravneni = 'on', '(zo)', ''))) AS `Občanský průkaz`,
        CisloZbrane,
        Kategorie,
        DatReg,
        Disciplina,
        DisciplinaReg,
        Staff,
        Klic,
        FROM_UNIXTIME(DatReg,'%d.%m.%Y %H:%i') AS Registrace,
        RegistraceIP    AS 'IP registrace',
        Mail,
        DatPay          AS 'Zaplatit',
        VarSym          AS 'VS',
        ZaplatiNaMiste  AS 'NaMiste',
        Zaplaceno,
        Castka,
        DatumZaplaceni  AS 'Datum zaplaceni',
        Urgence,
        Vyrazeno,
        VyrazenoIP      AS 'IP vyrazení',
        bulkId,
        Poznamka
            FROM $table
            ORDER BY Cislo
        ");
        } else {
            $stmt = $conn->prepare("
            SELECT 
        Cislo,
        CONCAT (Prijmeni, ' ', Jmeno) AS 'Příjmení Jméno',
        TRIM(CONCAT(ObcanskyPrukaz,' ',IF(ZbrojniOpravneni = 'on', '(zo)', ''))) AS `Občanský průkaz`,
        CisloZbrane,
        Kategorie,
        DatReg,
        Disciplina,
        DisciplinaReg,
        Staff,
        Klic,
        FROM_UNIXTIME(DatReg,'%d.%m.%Y %H:%i') AS Registrace,
        RegistraceIP    AS 'IP registrace',
        Mail,
        CastkaZaplatit  AS 'Zaplatit',
        VarSym          AS 'VS',
        Vyrazeno,
        VyrazenoIP      AS 'IP vyrazení',
        bulkId,
        Poznamka
            FROM $table
            ORDER BY Cislo
        ");
        }

        $stmt->execute();
        $result = $stmt->get_result();

        // Načteme metadata sloupců jednou
        $fields   = [];
        $result->field_seek(0);
        while ($col = $result->fetch_field()) {
            if ($col->name === 'DatReg') {
                continue;
            }
            if ($col->name === 'VS') {
                $fields[] = 'Statut';
                if ($match_data['Payment_before'] == "on") {
                    $fields[] = 'Stav';
                }
                $fields[] = 'Funkce';
            }
            $fields[] = $col->name;
        }

        // Vykreslení hlavičky
        echo '<table id="zavodnici" class="table table-striped table-bordered bg-white my-2 align-middle">';
        echo '<thead><tr>';

        foreach ($fields as $header) {
            echo '<th>' . htmlspecialchars($header, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</th>';
        }
        echo '</tr></thead>';
        echo '<tbody>';
        // Pokud potřebujeme znovu projít výsledek od začátku
        $result->data_seek(0);

        // Součet zaplacených
        $sumaZaplaceno = [];

        while ($line = $result->fetch_assoc()) {
            if ($line['Zaplaceno'] === 'on') {
                $mena = $line['Mena'] ?? 'CZK';
                $sumaZaplaceno[$mena] = ($sumaZaplaceno[$mena] ?? 0) + (float)$line['Castka'];
            }

            $lineClass = '';
            // odliseni prvniho zavodnika v hromadne registraci
            $bulkId = $line['bulkId'];
            $nezaplaceno = ($line['Zaplaceno'] != "on" && $line['NaMiste'] != "on" && $line['Disciplina'] != "VYRAZENO");
            $jeBulk = ($line['bulkId'] > 0);
            if (!in_array($bulkId, $zobrazenoBulkId) && $nezaplaceno && $jeBulk) {
                $zobrazenoBulkId[] = $bulkId;
                $lineClass = "zavodnik-bulk-1-st";
            } elseif (in_array($bulkId, $zobrazenoBulkId) && $nezaplaceno && $jeBulk) {
                $lineClass = "zavodnik-bulk-others";
            }
            if ($line['Disciplina'] == "VYRAZENO" && $jeBulk) {
                $lineClass = "zavodnik-bulk-vyrazeno";
            }

            // badge ikony stavu a statutu závodníka
            // stav
            $stavText = "čeká na platbu";
            $stavClass = "bg-secondary";

            if ($line['Disciplina'] == "VYRAZENO") {
                $stavText = "";
                $stavClass = "";
                $lineClass = "zavodnik-vyrazeno";
            }

            if ($line['NaMiste'] == "on") {
                $stavText = "platba na místě";
                $stavClass = "bg-info";
                $lineClass = "zavodnik-namiste";
            }
            if ($line['Staff'] !== "PAY") {
                $stavText = "neplatí";
                $stavClass = "bg-success";
            }
            if ($line['Zaplaceno'] == "on" and ($line['Staff'] == "PAY" and ($line['Disciplina'] != 'VYRAZENO'))) {
                $stavText = "zaplaceno";
                $stavClass = "bg-success";
                $lineClass = "zavodnik-zaplatil";
            }
            if (!empty($line['Urgence']) and $line['Zaplaceno'] !== "on") {
                $stavText = "urgence platby";
                $stavClass = "bg-danger";
            }
            if (($dnes >= date('Y-m-d', strtotime($line['Zaplatit'] . ' - 5 days')))
                && ($line['Disciplina'] != "VYRAZENO")
                && ($line['Staff'] == "PAY")
                && ($line['Zaplaceno'] !== "on")
                && ($match_data['Payment_before'] == "on")
            ) {
                $stavText = "Nezaplaceno v limitu";
                $stavClass = "bg-danger";
                $lineClass = "zavodnik-nezaplaceno";
            }

            // statut
            $staffText = 'platící závodník';
            $staffClass = "bg-secondary";

            if ($line['Disciplina'] == "VYRAZENO") {
                $staffText = "vyřazeno";
                $staffClass = "bg-dark";
            } elseif ($line['Staff'] == "RO") {
                $staffText = "rozhodčí";
                $staffClass = "bg-warning";
            } elseif ($line['Staff'] == "POM") {
                $staffText = "pomocník";
                $staffClass = "bg-warning";
            } elseif ($line['Staff'] == "VIP") {
                $staffText = "VIP";
                $staffClass = "bg-warning";
            }

            // bulk platby
            echo "<tr class='$lineClass'>";

            foreach ($fields as $col) {
                switch ($col) {
                    case 'Zaplaceno':
                        echo "<td class='Zaplaceno'>";
                        if (!empty($line['Zaplaceno'])) {
                            echo "<center><i class='fas fa-coins' style='font-size:18px;color:#FF9900;'></i></center>";
                        }
                        echo "</td>";
                        break;

                    case 'Statut':
                        echo "<td class='Statut'><span class='badge p-2 $staffClass'>$staffText</span></td>";
                        break;

                    case 'Stav':
                        echo "<td class='Stav'><span class='badge p-2 $stavClass'>$stavText</span></td>";
                        break;

                    case 'Funkce':
                        echo "<td class='functions'>";
        ?>
                        <div class="btn-group" role="group">
                            <?php if ($_SESSION['role'] === 'admin' or  $_SESSION['role'] === 'editor'): ?>
                            <?php if (!$jeBulk): ?>
                                <!-- SINGLE registrace -->
                                <button data-id="<?= $line['Cislo'] ?>" href="#info_shooter"
                                    class="modal_info_shooter btn text-secondary"
                                    data-bs-toggle="modal" title="Informace o závodníkovi">
                                    <i class="fas fa-edit"></i> Info
                                </button>
                            <?php elseif (!in_array($bulkId, $zobrazenoBulkInfo)): ?>
                                <!-- První závodník v BULK -->
                                <?php $zobrazenoBulkInfo[] = $bulkId; ?>
                                <button data-id="<?= $line['Cislo'] ?>" href="#info_shooter"
                                    class="modal_info_shooter btn text-secondary"
                                    data-bs-toggle="modal" title="Informace o hromadné registraci">
                                    <i class="fas fa-edit"></i> Info
                                    <span class="bulk-label">Bulk</span>
                                </button>
                            <?php else: ?>
                                <!-- Ostatní závodníci v BULK -->
                                <button data-id="<?= $line['Cislo'] ?>" href="#info_shooter"
                                    class="modal_info_shooter btn text-secondary"
                                    data-bs-toggle="modal" title="Informace o hromadné registraci">
                                    <i class="fas fa-edit"></i> Info
                                    <span class="bulk-label">Bulk</span>
                                </button>
                            <?php endif; ?>

                                <?php if (!$jeBulk && $line['Disciplina'] != "VYRAZENO"): ?>
                                    <!-- SINGLE registrace -->
                                    <button data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>" href="#send_regmail"
                                        class="modal_regmail btn text-secondary"
                                        data-bs-toggle="modal" data-bs-backdrop="static" data-bs-keyboard="false"
                                        title="Poslat registrační e-mail">
                                        <i class="fas fa-envelope"></i> E-mail
                                        <span class="bulk-label"></span>
                                    </button>
                                <?php elseif (!$jeBulk && $line['Disciplina'] == "VYRAZENO"): ?>
                                    <button data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>" href="#send_regmail"
                                        class="modal_regmail btn text-secondary disabled"
                                        data-bs-toggle="modal" data-bs-backdrop="static" data-bs-keyboard="false"
                                        title="Poslat registrační e-mail">
                                        <i class="fas fa-envelope"></i> E-mail
                                    </button>
                                <?php elseif (!in_array($bulkId, $zobrazenoBulkEmail)): ?>
                                    <!-- První závodník v BULK -->
                                    <?php $zobrazenoBulkEmail[] = $bulkId; ?>
                                    <button data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>" bulk-key="<?= $bulkId ?>" href="#send_bulk_regmail"
                                        class="modal_bulk_regmail btn text-secondary"
                                        data-bs-toggle="modal" data-bs-backdrop="static" data-bs-keyboard="false"
                                        title="Poslat hromadný registrační e-mail">
                                        <i class="fas fa-envelope"></i> E-mail
                                        <span class="bulk-label">Bulk</span>
                                    </button>
                                    <!-- Ostatni zavodnici v BULK -->
                                <?php else: ?>
                                    <button class="modal_bulk_regmail btn text-secondary disabled">
                                        <i class="fas fa-envelope"></i> E-mail
                                    </button>
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if (($_SESSION['role'] === 'admin') || ($line['Disciplina'] != "VYRAZENO" && $_SESSION['role'] === 'editor')): ?>
                                <div class="btn-group" role="group">
                                    <button type="button" class="btn text-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Další akce">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <ul class="dropdown-menu">
                                        <?php if ($nezaplaceno): ?>
                                            <?php if (!$jeBulk): ?>
                                                <!-- SINGLE registrace -->
                                                <li>
                                                    <button class="dropdown-item modal_payment_warn <?= $paymentBeforeClass ?>"
                                                        data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>"
                                                        href="#payment_warn" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                        Upozornění na nezaplacení
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item modal_payment_save <?= $paymentBeforeClass ?>"
                                                        data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>"
                                                        href="#payment_save" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Označit jako zaplaceno
                                                    </button>
                                                </li>
                                            <?php elseif (!in_array($bulkId, $zobrazenoBulkPlatba)): ?>
                                                <!-- První závodník v BULK -->
                                                <?php $zobrazenoBulkPlatba[] = $bulkId; ?>
                                                <li>
                                                    <button class="dropdown-item modal_bulk_payment_warn <?= $paymentBeforeClass ?>"
                                                        data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>" bulk-key="<?= $bulkId ?>"
                                                        href="#bulk_payment_warn" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                        Upozornění na nezaplacení
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item modal_bulk_payment_save <?= $paymentBeforeClass ?>"
                                                        data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>" bulk-key="<?= $bulkId ?>"
                                                        href="#bulk_payment_save" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Hromadná platba
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>

                                        <?php if ($line['Disciplina'] != "VYRAZENO"): ?>
                                            <li>
                                                <button class="dropdown-item modal_cancel_shooter"
                                                    data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>"
                                                    href="#cancel_shooter" data-bs-toggle="modal"
                                                    data-bs-backdrop="static" data-bs-keyboard="false">
                                                    <i class="fas fa-minus-circle text-danger me-2"></i>
                                                    Vyřadit závodníka
                                                </button>
                                            </li>
                                        <?php endif; ?>

                                        <?php if ($_SESSION['role'] === 'admin'): ?>
                                            <li>
                                                <button class="dropdown-item modal_delete_shooter"
                                                    data-id="<?= $line['Cislo'] ?>" data-key="<?= $line['Klic'] ?>"
                                                    href="#delete_shooter" data-bs-toggle="modal"
                                                    data-bs-backdrop="static" data-bs-keyboard="false">
                                                    <i class="fas fa-trash-alt text-danger me-2"></i>
                                                    Smazat závodníka
                                                </button>
                                            </li>
                                        <?php endif; ?>

                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>
                        </td>
        <?php
                        break;

                    default:
                        // standardní buňka
                        $value = $line[$col] ?? '';
                        echo "<td class='$col'>" . htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</td>";
                }
            }
            echo "</tr>";
        }
        echo '</tbody></table>';
        ?>

        <div class="ms-3 mb-3 <?= $paymentBeforeClass ?>">
            <h5>Vyúčtování</h5>
            <?php foreach ($sumaZaplaceno as $mena => $castka) {
                echo "&nbsp;- zaplaceno: $castka " . $match_data['Banka_ucet_MENA'] . "<br>";
            } ?>
        </div>
        <div class="ms-3 mb-3">
            <h5>Legenda</h5>
            - registrováno
            &nbsp;<span class='zavodnik-bulk-others <?= $hromadnaRegistraceClass ?>'><br>- hromadná registrace do více disciplín</span><br>
            <span class="<?= $paymentBeforeClass ?>">
                - rozhodčí, pomocníci a VIP neplatí (automaticky se potvrdí účast a neposílá se urgence ani se automaticky nevyřadí)<br>
                <span class='zavodnik-zaplatil'>- zaplaceno<br></span>
                <span class='zavodnik-namiste'>- zaplatí na místě<br></span>
                <span style='color: #ff0000; '>- ruční urgence platby<br></span>
            </span>
            - <span class='zavodnik-vyrazeno'>vyřazeno</span>
        </div>
    </div>
    <div class="footer">SSAŠ střelnice Prachatice &copy; Milan Žídek <?= date("Y") ?><span style="float:right">Shooting match registration system 2.0</span></div>
</div>
<?php
include_once("./include/match_config.php");
include_once("./include/new.php");
include_once("./include/disciplines.php");
include_once("./include/users.php");
include_once("./include/password_change.php");
include_once("./include/reg_fee.php");
include_once("./include/targets.php");
include_once("./include/pass_values.php");
?>

<script type="text/javascript" src="./js/admin_scripts.js"></script>
<script type="text/javascript" src="./js/admin_reg_form.js"></script>

<script>
    var paymentBefore = <?= json_encode($match_data['Payment_before'] === 'on') ?>;
</script>


</BODY>

</HTML>