<?php

declare(strict_types=1);

/**
 * Vykreslí sekci se seznamem závodníků (tabulka + vyúčtování + legenda).
 * Používá se jak při prvním načtení stránky, tak i při AJAX refreshi bez F5.
 */
function renderCompetitorsSection(
    mysqli $conn,
    string $table,
    array $match_data,
    string $dnes,
    string $paymentBeforeClass,
    string $hromadnaRegistraceClass
): string {
    // ochrana proti SQL injection (název tabulky musí být čistý identifikátor)
    $table = preg_replace('/[^a-zA-Z0-9_]/', '', $table);

    // lokální stav pro bulk zvýraznění / akce
    $zobrazenoBulkId = [];
    $zobrazenoBulkInfo = [];
    $zobrazenoBulkEmail = [];
    $zobrazenoBulkPlatba = [];

    ob_start();
?>
    <?php
    // Dotaz pro získání závodníků
    if ($match_data['Payment_before']) {
        $stmt = $conn->prepare("
            SELECT 
        Cislo,
        Stav,
        CASE WHEN Prijmeni LIKE '% %' THEN CONCAT(SUBSTRING_INDEX(Prijmeni, ' ', 1), ' ', Jmeno, ' ', SUBSTRING_INDEX(Prijmeni, ' ', -1)) ELSE CONCAT(Prijmeni, ' ', Jmeno) END AS PrijmeniJmeno,
        TRIM(CONCAT(ObcanskyPrukaz,' ',IF(ZbrojniOpravneni = 1, '(zo)', ''))) AS `Občanský průkaz`,
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
        CastkaZaplatit  AS 'Startovné',
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
        Stav,
        CONCAT (Prijmeni, ' ', Jmeno) AS 'Příjmení Jméno',
        TRIM(CONCAT(ObcanskyPrukaz,' ',IF(ZbrojniOpravneni = 1, '(zo)', ''))) AS `Občanský průkaz`,
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
        CastkaZaplatit  AS 'Startovné',
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
            if ($match_data['Payment_before']) {
                $fields[] = 'Platba';
            }
            $fields[] = 'Funkce';
        }
        $fields[] = $col->name;
    }

    // Vykreslení hlavičky
    echo '<table id="zavodnici" class="table table-striped table-bordered bg-white my-2 align-middle">';
    echo '<thead><tr>';

    foreach ($fields as $header) {
        echo '<th>' . htmlspecialchars((string)$header, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</th>';
    }
    echo '</tr></thead>';
    echo '<tbody>';
    // Pokud potřebujeme znovu projít výsledek od začátku
    $result->data_seek(0);

    // Pro součet zaplacených
    $sumaZaplaceno = [];

    while ($line = $result->fetch_assoc()) {
        // --- Logika pro třídu řádku, aktuální datum, součet plateb atp. ---
        if (!empty($line['Zaplaceno'])) {
            $mena = $line['Mena'] ?? 'CZK';
            $sumaZaplaceno[$mena] = ($sumaZaplaceno[$mena] ?? 0) + (float)($line['Castka'] ?? 0);
        }

        $lineClass = '';
        // odliseni prvniho zavodnika v hromadne registraci
        $bulkId = (int)($line['bulkId'] ?? 0);
        $nezaplaceno = ((int)($line['Zaplaceno'] ?? 0) === 0 && (int)($line['NaMiste'] ?? 0) === 0 && ($line['Disciplina'] ?? '') != "VYRAZENO");
        $plati = (($line['Staff'] ?? '') == "PAY");
        $jeBulk = ($bulkId > 0);
        if (!in_array($bulkId, $zobrazenoBulkId, true) && $nezaplaceno && $jeBulk) {
            $zobrazenoBulkId[] = $bulkId;
            $lineClass = "zavodnik-bulk-1-st";
        } elseif (in_array($bulkId, $zobrazenoBulkId, true) && $nezaplaceno && $jeBulk) {
            $lineClass = "zavodnik-bulk-others";
        }
        if (($line['Disciplina'] ?? '') == "VYRAZENO" && $jeBulk) {
            $lineClass = "zavodnik-bulk-vyrazeno";
        }


        // badge ikony stavu a statutu závodníka
        // stav
        $stavText = "čeká na platbu";
        $stavClass = "bg-secondary";

        if (($line['Disciplina'] ?? '') == "VYRAZENO") {
            $stavText = "";
            $stavClass = "";
            $lineClass = "zavodnik-vyrazeno";
        }

        if (!empty($line['NaMiste'])) {
            $stavText = "platba na místě";
            $stavClass = "bg-info";
            $lineClass = "zavodnik-namiste";
        }
        if (($line['Staff'] ?? '') !== "PAY") {
            $stavText = "neplatí";
            $stavClass = "bg-success";
        }
        if (!empty($line['Zaplaceno']) && (($line['Staff'] ?? '') == "PAY" && (($line['Disciplina'] ?? '') != 'VYRAZENO'))) {
            $stavText = "zaplaceno";
            $stavClass = "bg-success";
            $lineClass = "zavodnik-zaplatil";
        }
        if (!empty($line['Urgence']) && (int)($line['Zaplaceno'] ?? 0) === 0) {
            $stavText = "urgence platby";
            $stavClass = "bg-danger";
            //$lineClass = "zavodnik-urgence";
        }
        if (
            ($match_data['Payment_before'])
            && (($line['Disciplina'] ?? '') != "VYRAZENO")
            && (($line['Staff'] ?? '') == "PAY")
            && ((int)($line['Zaplaceno'] ?? 0) === 0)
            && !empty($line['Zaplatit'])
            && ($dnes >= date('Y-m-d', strtotime((string)$line['Zaplatit'] . ' - 5 days')))
        ) {
            $stavText = "Nezaplaceno v limitu";
            $stavClass = "bg-danger";
            $lineClass = "zavodnik-nezaplaceno";
        }

        // statut
        $staffText = 'platící závodník';
        $staffClass = "bg-secondary";

        if (($line['Disciplina'] ?? '') == "VYRAZENO") {
            $staffText = "vyřazeno";
            $staffClass = "bg-dark";
        } elseif (($line['Staff'] ?? '') == "RO") {
            $staffText = "rozhodčí";
            $staffClass = "bg-warning";
        } elseif (($line['Staff'] ?? '') == "POM") {
            $staffText = "pomocník";
            $staffClass = "bg-warning";
        } elseif (($line['Staff'] ?? '') == "VIP") {
            $staffText = "VIP";
            $staffClass = "bg-warning";
        }

        echo "<tr class='" . htmlspecialchars($lineClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "'>";

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

                case 'Platba':
                    echo "<td class='Platba'><span class='badge p-2 $stavClass'>$stavText</span></td>";
                    break;

                case 'Funkce':
                    echo "<td class='functions'>";
    ?>
                    <div class="btn-group" role="group">
                            <?php if (!$jeBulk): ?>
                                <!-- SINGLE registrace -->
                                <button data-id="<?= (int)$line['Cislo'] ?>" href="#info_shooter"
                                    class="modal_info_shooter btn text-secondary"
                                    data-bs-toggle="modal" title="Informace o závodníkovi">
                                    <i class="fas fa-edit"></i> Info
                                </button>
                            <?php elseif (!in_array($bulkId, $zobrazenoBulkInfo, true)): ?>
                                <!-- První závodník v BULK -->
                                <?php $zobrazenoBulkInfo[] = $bulkId; ?>
                                <button data-id="<?= (int)$line['Cislo'] ?>" href="#info_shooter"
                                    class="modal_info_shooter btn text-secondary"
                                    data-bs-toggle="modal" title="Informace o hromadné registraci">
                                    <i class="fas fa-edit"></i> Info
                                    <span class="bulk-label">Bulk</span>
                                </button>
                            <?php else: ?>
                                <!-- Ostatní závodníci v BULK -->
                                <button data-id="<?= (int)$line['Cislo'] ?>" href="#info_shooter"
                                    class="modal_info_shooter btn text-secondary"
                                    data-bs-toggle="modal" title="Informace o hromadné registraci">
                                    <i class="fas fa-edit"></i> Info
                                    <span class="bulk-label">Bulk</span>
                                </button>
                            <?php endif; ?>

                            <?php if (($_SESSION['role'] === 'admin' ||  $_SESSION['role'] === 'editor') && !$jeBulk && ($line['Disciplina'] ?? '') != "VYRAZENO"): ?>
                                <!-- SINGLE registrace -->
                                <button data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>" href="#send_regmail"
                                    class="modal_regmail btn text-secondary"
                                    data-bs-toggle="modal" data-bs-backdrop="static" data-bs-keyboard="false"
                                    title="Poslat registrační e-mail">
                                    <i class="fas fa-envelope"></i> E-mail
                                    <span class="bulk-label"></span>
                                </button>
                            <?php elseif (($_SESSION['role'] === 'admin' ||  $_SESSION['role'] === 'editor') && !$jeBulk && ($line['Disciplina'] ?? '') == "VYRAZENO"): ?>
                                <button data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>" href="#send_regmail"
                                    class="modal_regmail btn text-secondary disabled"
                                    data-bs-toggle="modal" data-bs-backdrop="static" data-bs-keyboard="false"
                                    title="Poslat registrační e-mail">
                                    <i class="fas fa-envelope"></i> E-mail
                                </button>
                            <?php elseif (($_SESSION['role'] === 'admin' ||  $_SESSION['role'] === 'editor') && !in_array($bulkId, $zobrazenoBulkEmail, true)): ?>
                                <!-- První závodník v BULK -->
                                <?php $zobrazenoBulkEmail[] = $bulkId; ?>
                                <button data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>" bulk-key="<?= (int)$bulkId ?>" href="#send_bulk_regmail"
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

                        <?php if (($_SESSION['role'] === 'admin') || (($line['Disciplina'] ?? '') != "VYRAZENO" && $_SESSION['role'] === 'editor')): ?>
                            <div class="btn-group" role="group">
                                <button type="button" class="btn text-secondary" data-bs-toggle="dropdown" aria-expanded="false" title="Další akce">
                                    <i class="fas fa-ellipsis-v"></i>
                                </button>
                                <ul class="dropdown-menu">
                                    <?php if ($plati): ?>
                                        <?php if ($nezaplaceno): ?>
                                            <?php if (!$jeBulk): ?>
                                                <!-- SINGLE registrace -->
                                                <li>
                                                    <button class="dropdown-item modal_payment_warn <?= $paymentBeforeClass ?>"
                                                        data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>"
                                                        href="#payment_warn" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                        Upozornění na nezaplacení
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item modal_payment_save <?= $paymentBeforeClass ?>"
                                                        data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>"
                                                        href="#payment_save" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Označit jako zaplaceno
                                                    </button>
                                                </li>
                                            <?php elseif (!in_array($bulkId, $zobrazenoBulkPlatba, true)): ?>
                                                <!-- První závodník v BULK -->
                                                <?php $zobrazenoBulkPlatba[] = $bulkId; ?>
                                                <li>
                                                    <button class="dropdown-item modal_bulk_payment_warn <?= $paymentBeforeClass ?>"
                                                        data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>" bulk-key="<?= (int)$bulkId ?>"
                                                        href="#bulk_payment_warn" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                                        Upozornění na nezaplacení
                                                    </button>
                                                </li>
                                                <li>
                                                    <button class="dropdown-item modal_bulk_payment_save <?= $paymentBeforeClass ?>"
                                                        data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>" bulk-key="<?= (int)$bulkId ?>"
                                                        href="#bulk_payment_save" data-bs-toggle="modal"
                                                        data-bs-backdrop="static" data-bs-keyboard="false">
                                                        <i class="fas fa-check-circle text-success me-2"></i>
                                                        Hromadná platba
                                                    </button>
                                                </li>
                                            <?php endif; ?>
                                        <?php endif; ?>
                                    <?php endif; ?>

                                    <?php if (($line['Disciplina'] ?? '') != "VYRAZENO"): ?>
                                        <li>
                                            <button class="dropdown-item modal_cancel_shooter"
                                                data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>"
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
                                                data-id="<?= (int)$line['Cislo'] ?>" data-key="<?= (int)$line['Klic'] ?>"
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
                    $colClass = preg_replace('/[^a-zA-Z0-9_\-]/', '', (string)$col);
                    echo "<td class='$colClass'>" . htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "</td>";
            }
        }
        echo "</tr>";
    }
    echo '</tbody></table>';
    ?>

    <div class="ms-3 mb-3 <?= htmlspecialchars($paymentBeforeClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
        <h5>Vyúčtování</h5>
        <?php foreach ($sumaZaplaceno as $mena => $castka) {
            echo "&nbsp;- zaplaceno: " . htmlspecialchars((string)$castka, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . " " . htmlspecialchars((string)($match_data['Banka_ucet_MENA'] ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . "<br>";
        } ?>
    </div>
    <div class="ms-3 mb-3">
        <h5>Legenda</h5>
        - registrováno
        &nbsp;<span class='zavodnik-bulk-others <?= htmlspecialchars($hromadnaRegistraceClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>'><br>- hromadná registrace do více disciplín</span><br>
        <span class="<?= htmlspecialchars($paymentBeforeClass, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
            - rozhodčí, pomocníci a VIP neplatí (automaticky se potvrdí účast a neposílá se urgence ani se automaticky nevyřadí)<br>
            <span class='zavodnik-zaplatil'>- zaplaceno<br></span>
            <span class='zavodnik-namiste'>- zaplatí na místě<br></span>
            <span style='color: #ff0000; '>- ruční urgence platby<br></span>
        </span>
        - <span class='zavodnik-vyrazeno'>vyřazeno</span>
    </div>
<?php

    return (string)ob_get_clean();
}

// AJAX endpoint pro refresh seznamu závodníků (bez reloadu stránky / F5)
if (isset($_GET['ajax']) && $_GET['ajax'] === 'competitors') {
    require_once __DIR__ . '/session_init.php';
    require_once __DIR__ . '/db/dbconn.php';
    require_once __DIR__ . '/functions.php';

    require_admin();

    // stejná inicializace dat závodu jako v header.php, ale bez výpisu HTML
    $table = (string)($_SESSION['zavod_id'] ?? '');
    if ($table === '') {
        http_response_code(400);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'missing_match'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $result = $conn->query("SELECT * FROM $table_matches WHERE Zavod_id='" . $conn->real_escape_string($table) . "' LIMIT 1");
    if (!$result || $result->num_rows === 0) {
        http_response_code(404);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['error' => 'match_not_found'], JSON_UNESCAPED_UNICODE);
        exit;
    }
    $match_data = (array)$result->fetch_array();

    $paymentBeforeClass = (($match_data['Payment_before'] ?? 0) == 1) ? '' : 'd-none';
    $hromadnaRegistraceClass = (($match_data['Zavod_registrace_hromadna'] ?? 0) == 1) ? '' : 'd-none';
    $dnes = (new DateTime())->format("Y-m-d H:i:s");

    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'html' => renderCompetitorsSection($conn, (string)$table, $match_data, $dnes, $paymentBeforeClass, $hromadnaRegistraceClass),
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

include __DIR__ . '/header.php';
?>
<div id="main">
    <div class="content">
        <button class="btn btn-outline-primary btn-rounded my-2 ms-2" onclick="ToggleFilter()">
            <i class="fas fa-solid fa-filter  me-1"></i>Zobrazit / skrýt filtr</button>
        <button id="refresh-competitors" class="btn btn-outline-primary btn-rounded my-2 ms-2" type="button">
            <i class="fas fa-sync-alt me-1"></i> Obnovit závodníky
        </button>
        <?php
        $ip = $_SERVER['REMOTE_ADDR'];
        ?>

        <div id="competitorsContainer">
            <?= renderCompetitorsSection($conn, (string)$table, (array)$match_data, (string)$dnes, (string)$paymentBeforeClass, (string)$hromadnaRegistraceClass) ?>
        </div>
    </div>
    <div class="footer">SSAŠ střelnice Prachatice &copy; Milan Žídek <?= date("Y") ?><span style="float:right">Shooting match registration system 3.8</span></div>
</div>
<?php
include_once("./include/match_config.php");
include_once("./include/new.php");
include_once("./include/admin_smeny.php");
include_once("./include/disciplines.php");
include_once("./include/users.php");
include_once("./include/fee.php");
include_once("./include/user_password_change.php");
include_once("./include/pass_values.php");
include_once("./include/truncate_table.php");
?>

<script type="text/javascript" src="./js/admin_scripts.js"></script>
<script type="text/javascript" src="./js/admin_reg_form.js"></script>

<script>
    var paymentBefore = <?= json_encode($match_data['Payment_before'] == 1) ?>;
    var registraceSmeny = <?= json_encode($match_data['Zavod_registrace_smeny'] == 1) ?>;

    function bindCompetitorActionButtons() {
        var $root = $('#competitorsContainer');

        // odstraníme původní direct-handlery (jsou navázané při prvním načtení),
        // aby po refreshi nedocházelo k dvojitému spouštění
        $root.find('.modal_info_shooter').off('click').on('click', function() {
            var ID = $(this).data('id');
            $('#modalID').val(ID);
            $.post("information.php", {
                ID: ID
            }, function(result) {
                $("#modal-info-included").html(result);
            });
        });

        $root.find('.modal_regmail').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            $.ajax({
                url: 'regmail.php?ID=' + ID + '&KEY=' + KEY,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });

        $root.find('.modal_bulk_regmail').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            var BULK = $(this).attr('bulk-key');
            $.ajax({
                url: 'regmail_bulk.php?ID=' + ID + '&KEY=' + KEY + '&BULK=' + BULK,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });

        $root.find('.modal_delete_shooter').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            $.ajax({
                url: 'delete.php?ID=' + ID + '&KEY=' + KEY,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });

        $root.find('.modal_cancel_shooter').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            $.ajax({
                url: 'cancel.php?ID=' + ID + '&KEY=' + KEY,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });

        $root.find('.modal_payment_warn').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            $.ajax({
                url: 'payment_warn.php?ID=' + ID + '&KEY=' + KEY,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });

        $root.find('.modal_payment_save').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            $.ajax({
                url: 'payment_save.php?ID=' + ID + '&KEY=' + KEY,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });

        $root.find('.modal_bulk_payment_warn').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            var BULK = $(this).attr('bulk-key');
            $.ajax({
                url: 'payment_warn_bulk.php?ID=' + ID + '&KEY=' + KEY + '&BULK=' + BULK,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });

        $root.find('.modal_bulk_payment_save').off('click').on('click', function() {
            var ID = $(this).attr('data-id');
            var KEY = $(this).attr('data-key');
            var BULK = $(this).attr('bulk-key');
            $.ajax({
                url: 'payment_save_bulk.php?ID=' + ID + '&KEY=' + KEY + '&BULK=' + BULK,
                cache: false,
                success: function(result) {
                    $(".modal-content").html(result);
                }
            });
        });
    }

    async function refreshCompetitors() {
        var $btn = $('#refresh-competitors');
        if ($btn.prop('disabled')) return;

        var originalHtml = $btn.html();
        $btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Načítám...');

        try {
            const res = await fetch('index.php?ajax=competitors', {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                },
                cache: 'no-store',
                credentials: 'same-origin'
            });

            if (res.status === 401) {
                // session vypršela – bezpečně přesměrujeme na plný reload
                window.location.href = 'index.php';
                return;
            }

            const data = await res.json();
            if (!data || !data.html) {
                throw new Error('Invalid response');
            }

            if (window.SSASAdmin && typeof window.SSASAdmin.destroyZavodniciDataTableIfExists === 'function') {
                window.SSASAdmin.destroyZavodniciDataTableIfExists();
            }
            document.getElementById('competitorsContainer').innerHTML = data.html;
            if (window.SSASAdmin && typeof window.SSASAdmin.initZavodniciDataTable === 'function') {
                window.SSASAdmin.initZavodniciDataTable({
                    paymentBefore: paymentBefore,
                    registraceSmeny: registraceSmeny
                });
            }
            bindCompetitorActionButtons();
        } catch (e) {
            console.error(e);
            alert('Nepodařilo se obnovit seznam závodníků. Zkuste to prosím znovu.');
        } finally {
            $btn.prop('disabled', false).html(originalHtml);
        }
    }

    $(document).ready(function() {
        // DataTables init řeší datatable_conf.js, tady řešíme jen akční tlačítka a refresh
        bindCompetitorActionButtons();
        $('#refresh-competitors').on('click', refreshCompetitors);
    });
</script>



</BODY>

</HTML>