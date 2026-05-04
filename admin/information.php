<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/config/data.php';
require_once __DIR__ . '/db/dbconn.php';
require_admin();

$ID = isset($_POST['ID']) ? intval($_POST['ID']) : 0;

$stmt = $conn->prepare("
		SELECT * FROM $table
		WHERE Cislo = ?
	 ");
$stmt->bind_param(
    "i",
    $ID
);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();


if ($result && $result->num_rows > 0) {
    $line = $result->fetch_assoc();

    $Disciplina_old = $line['Disciplina'];

    $staffLabels = [
        "PAY" => "platící závodník",
        "DNS" => "vyřazen",
        "RO" => "rozhodčí - neplatí",
        "POM" => "pomocník - neplatí",
        "VIP" => "VIP - neplatí"
    ];
    $staffLabel = $staffLabels[$line['Staff']] ?? htmlspecialchars($line['Staff'], ENT_QUOTES, 'UTF-8');

    $nazev_discipliny = getValueFromTable($conn, $table_disciplines, "Name", $line['Disciplina'], "Value");

    $paymentBeforeClass = !empty($match_data['Payment_before']) ? '' : 'd-none';
    $zavodObcanskyPrukazClass = !empty($match_data['Zavod_obcansky_prukaz']) ? '' : 'd-none';

    $zavodCisloZbraneClass = !empty($match_data['Zavod_cislo_zbrane']) ? '' : 'd-none';
    $zavodNazevZbraneClass = !empty($match_data['Zavod_nazev_zbrane']) ? '' : 'd-none';

    $zavodStavClass = !empty($match_data['Zavod_registrace_smeny']) ? '' : 'd-none';
    $zavodStavRequired = !empty($match_data['Zavod_registrace_smeny']) ? 'required' : '';

?>
    <!-- ID závodníka -->
    <INPUT type="hidden" id="shooterID" name="shooterID" value="<?= htmlspecialchars($ID, ENT_QUOTES, 'UTF-8') ?>" required>
    <!-- ID závodníka -->
    <!-- stav platby -->
    <INPUT type="hidden" id="Zaplaceno" name="Zaplaceno"
        value="<?= htmlspecialchars($line['Zaplaceno'], ENT_QUOTES, 'UTF-8') ?>">
    <!-- stav platby -->

    <div class='accordion' id='accordionInformation'>
        <div class='accordion-item'>
            <h2 class='accordion-header'>
                <button class='accordion-button' type='button' data-bs-toggle='collapse' data-bs-target='#collapseOne'
                    aria-expanded='true' aria-controls='collapseOne'>
                    Základní informace
                </button>
            </h2>
            <div id='collapseOne' class='accordion-collapse collapse show' data-bs-parent='#accordionInformation'>
                <div class='accordion-body'>
                    <div class='row pb-3'>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>Jméno</label>
                            <input class="form-control" type="text" name="Jmeno" id="Jmeno"
                                onkeypress="return avoidspace(event)" placeholder="Jan" onfocus="this.placeholder = ''"
                                onblur="this.placeholder = 'Jan'"
                                value="<?= htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>Příjmení</label>
                            <input class="form-control" type="text" name="Prijmeni" id="Prijmeni"
                                onkeypress="return avoidspace(event)" placeholder="Novák" onfocus="this.placeholder = ''"
                                onblur="this.placeholder = 'Novák'"
                                value="<?= htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                    </div>
                    <div class='row pb-3'>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>E-mail</label>
                            <input class="form-control" type="email" id="Mail" name="Mail"
                                onkeypress="return avoidspace(event)" onfocus="this.placeholder = ''"
                                onblur="this.placeholder = 'novak@mujemail.cz'" placeholder="novak@mujemail.cz"
                                value="<?= htmlspecialchars($line['Mail'], ENT_QUOTES, 'UTF-8') ?>" required>
                        </div>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>Kategorie</label>
                            <select class="form-select" name=Kategorie>
                                <option value="<?= htmlspecialchars($line['Kategorie'], ENT_QUOTES, 'UTF-8') ?>">
                                    <?= htmlspecialchars($line['Kategorie'], ENT_QUOTES, 'UTF-8') ?></option>
                                <option value="Regular">Regular</option>
                                <option value="Junior">Junior</option>
                            </select>
                        </div>
                    </div>
                    <div class="row pb-3 <?= $zavodObcanskyPrukazClass ?>">
                        <div class="col-md-6 pt-1">
                            <label class='form-label pt-1'>Číslo OP / EZP</label>
                            <input class="form-control" type="text" name="ObcanskyPrukaz" id="ObcanskyPrukaz"
                                value="<?= htmlspecialchars($line['ObcanskyPrukaz'], ENT_QUOTES, 'UTF-8') ?>">
                        </div>
                        <div class="col-md-6 pt-5">
                            <input type="checkbox" class="form-check-input" id="ZbrojniOpravneni" name="ZbrojniOpravneni"
                                <?php echo ($line['ZbrojniOpravneni'] == 1) ? "CHECKED" : ""; ?>>
                            <label class="form-check-label" for="ZbrojniOpravneni">Držitel zbrojního oprávnění</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class='accordion-item'>
            <h2 class='accordion-header'>
                <button class='accordion-button collapsed' type='button' data-bs-toggle='collapse' aria-expanded='false'
                    data-bs-target='#collapseTwo' aria-controls='collapseTwo'>
                    <?= (($line['bulkId'] > 0) ? 'Závod a hromadně zaregistrované disciplíny' : 'Závod') ?>
                </button>
            </h2>
            <div id='collapseTwo' class='accordion-collapse collapse' data-bs-parent='#accordionInformation'>
                <div class='accordion-body'>
                    <fieldset class='<?= (($line['bulkId'] > 0) ? 'border p-3 my-3 rounded' : '') ?>'>
                        <legend class='float-none w-auto px-2 h6 <?= (($line['bulkId'] > 0) ? '' : 'd-none') ?>'>Registrace
                            závodníka</legend>
                        <div class='row pb-3'>
                            <div class='col-md-5 <?= $zavodStavClass ?>'>
                                <label class='form-label'>Stav</label>
                                <input class='bg-light form-control' name='Stav' id='Stav'
                                    value='<?= htmlspecialchars($line['Stav'], ENT_QUOTES, 'UTF-8') ?>'
                                    <?= $zavodStavRequired ?>>
                                <div class="invalid-feedback">Doplňte stav</div>

                            </div>
                            <div class='col-md-5 <?= ($line['Disciplina'] == 'VYRAZENO' ? 'd-none' : '') ?>'>
                                <label class='form-label'>Disciplína</label>
                                <select class="form-select" name=Disciplina required>
                                    <option value="<?= htmlspecialchars($line['Disciplina'], ENT_QUOTES, 'UTF-8'); ?>">
                                        <?= $nazev_discipliny ?></option>
                                    <?php
                                    $discResult = $conn->query("SELECT Name, Value FROM $table_disciplines ORDER BY Value");
                                    if ($discResult) {
                                        while ($discipline = $discResult->fetch_assoc()) {
                                            echo "<option value=\"" . htmlspecialchars($discipline['Name'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($discipline['Value'], ENT_QUOTES, 'UTF-8') . "</option>";
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class='col-md-5 <?= (empty($line['DisciplinaReg']) ? 'd-none' : '') ?>'>
                                <label class='form-label'>Disciplína před vyřazením</label>
                                <input readonly class='bg-light text-muted form-control'
                                    value='<?= htmlspecialchars($line['DisciplinaReg'], ENT_QUOTES, 'UTF-8') ?>'>
                            </div>
                        </div>
                        <div class='row pb-3'>
                            <div class='col-md-5 <?= $zavodCisloZbraneClass ?>'>
                                <label class='form-label'>Číslo zbraně</label>
                                <input readonly class='bg-light text-muted form-control'
                                    value='<?= htmlspecialchars($line['CisloZbrane'], ENT_QUOTES, 'UTF-8') ?>'>
                            </div>
                            <div class='col-md-7 <?= $zavodNazevZbraneClass ?>'>
                                <label class='form-label'>Název zbraně</label>
                                <input readonly class='bg-light text-muted form-control'
                                    value='<?= htmlspecialchars($line['NazevZbrane'], ENT_QUOTES, 'UTF-8') ?>'>
                            </div>
                        </div>
                        <div class='row pb-3'>
                            <div class='col-md-5'>
                                <label class='form-label'>Statut</label>
                                <select class="form-select" name=Staff>
                                    <option value="<?= htmlspecialchars($line['Staff'], ENT_QUOTES, 'UTF-8') ?>">
                                        <?= $staffLabel ?></option>
                                    <option value="VIP">VIP</option>
                                    <option value="PAY">platící závodník</option>
                                    <option value="RO">rozhodčí</option>
                                    <option value="POM">pomocník</option>
                                </select>
                            </div>
                            <div class='col-md-3  <?= hidden($match_data['Zavod_payment_before'] == 1); ?>'>
                                <label class='form-label'>VS</label>
                                <input readonly class='bg-light text-dark form-control'
                                    value='<?= htmlspecialchars($line['VarSym'], ENT_QUOTES, 'UTF-8') ?>'>
                            </div>
                            <div class='col-md-4  <?= hidden($match_data['Zavod_payment_before'] == 1); ?>'>
                                <label class='form-label'>Zaplatit</label>
                                <input readonly class='bg-light text-dark form-control'
                                    value="<?= htmlspecialchars($line['CastkaZaplatit'], ENT_QUOTES, 'UTF-8') ?> <?= $match_data['Banka_ucet_MENA'] ?>">
                            </div>
                            <div
                                class='col-md-4 pt-4 <?php echo (($line['Staff'] == "PAY") && ($match_data['Payment_before'] == 1)) ? '' : 'd-none' ?>'>
                                <input class="form-check-input" type="checkbox" id="ZaplatiNaMiste" name="ZaplatiNaMiste"
                                    <?php echo ($line['ZaplatiNaMiste'] == 1) ? "CHECKED" : ""; ?>>
                                <label class="form-check-label" for="ZaplatiNaMiste">Zaplatí na místě</label>
                            </div>
                        </div>
                        <div class='row'>
                            <div class='col-md-12'>
                                <label class='form-label pt-1'>Poznámka</label>
                                <textarea class="form-control" type="text" name="Poznamka" id="Poznamka"
                                    placeholder="Poznámka" onfocus="this.placeholder = ''"
                                    onblur="this.placeholder = 'Poznámka'"><?= htmlspecialchars($line['Poznamka'], ENT_QUOTES, 'UTF-8') ?></textarea>
                            </div>
                        </div>

                    </fieldset>
                    <?php
                    if ($line['bulkId'] > 0) {
                    ?>
                        <fieldset class="border p-3 my-3 rounded">
                            <legend class="float-none w-auto px-2 h6">Všechny disciplíny závodníka</legend>
                            <div class='row p-2'>
                                <table class='table table-hover table-bordered disciplines'>
                                    <tr>
                                        <th>Disciplína</th>
                                        <th>Zaplatit</th>
                                    </tr>
                                    <tbody>
                                        <?php
                                        $bulkStmt = $conn->prepare("
                                        SELECT 
                                            CastkaZaplatit,
                                            Disciplina,
                                            Castka
                                                FROM $table
                                                WHERE bulkId = ? AND Disciplina != 'VYRAZENO'
                                                ORDER BY Cislo
                                            ");
                                        $bulkId = (int) $line['bulkId'];
                                        $bulkStmt->bind_param("i", $bulkId);
                                        $bulkStmt->execute();
                                        $result = $bulkStmt->get_result();
                                        $bulkStmt->close();

                                        $bulkSumStmt = $conn->prepare("
                                        SELECT 
                                            SUM(CastkaZaplatit) AS sumaZaplatit
                                                FROM $table
                                                WHERE bulkId = ?
                                            ");
                                        $bulkSumStmt->bind_param("i", $bulkId);
                                        $bulkSumStmt->execute();
                                        $SumZaplatit = $bulkSumStmt->get_result();
                                        $bulkSumStmt->close();

                                        $SumaZaplatit = $SumZaplatit->fetch_object()->sumaZaplatit;
                                        while ($bulkLine = $result->fetch_assoc()) {
                                        ?>
                                            <tr>
                                                <td><?= htmlspecialchars($bulkLine['Disciplina'], ENT_QUOTES, 'UTF-8') ?></td>
                                                <td><?= htmlspecialchars($bulkLine['CastkaZaplatit'], ENT_QUOTES, 'UTF-8') ?></td>
                                            </tr>
                                        <?php
                                        }
                                        ?>
                                        <tr style='font-weight:600'>
                                            <td>Celkem zaplatit</td>
                                            <td><?= $SumaZaplatit ?> <?= $match_data['Banka_ucet_MENA'] ?></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </fieldset>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>

        <div class='accordion-item'>
            <h2 class='accordion-header'>
                <button
                    class='accordion-button collapsed <?= (!empty($line['Vyrazeno']) ? 'bg-secondary text-white' : '') ?>'
                    type='button' data-bs-toggle='collapse' data-bs-target='#collapseThree' aria-expanded='false'
                    aria-controls='collapseThree'>
                    Registrace a vyřazení
                </button>
            </h2>
            <div id='collapseThree' class='accordion-collapse collapse' data-bs-parent='#accordionInformation'>
                <div class='accordion-body'>
                    <div class='row'>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>Datum registrace</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= gmdate("d.m.Y H:i", htmlspecialchars($line['DatReg'], ENT_QUOTES, 'UTF-8')) ?>'>
                        </div>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>IP registrace</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= htmlspecialchars($line['RegistraceIP'], ENT_QUOTES, 'UTF-8') ?>'>
                        </div>
                        <div class='col-md-12 py-2'></div>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>Datum a čas vyřazení</label>
                            <input readonly class='bg-light form-control'
                                value='<?= (!empty($line['Vyrazeno']) ? date('d.m.Y H:i', strtotime($line['Vyrazeno'])) : '---') ?>'>
                        </div>
                        <div class='col-md-6'>
                            <label class='form-label pt-1'>IP vyřazení</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= (!empty($line['VyrazenoIP']) ? htmlspecialchars($line['VyrazenoIP'], ENT_QUOTES, 'UTF-8') : '---') ?>'>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class='accordion-item <?= (empty($match_data['Payment_before']) ? 'd-none' : '') ?>'>
            <h2 class='accordion-header'>
                <button
                    class='accordion-button collapsed <?= (!empty($line['Zaplaceno']) ? 'bg-success text-white' : '') ?>'
                    type='button' data-bs-toggle='collapse' data-bs-target='#collapseFour' aria-expanded='false'
                    aria-controls='collapseFour'>
                    Placení
                </button>
            </h2>
            <div id='collapseFour' class='accordion-collapse collapse' data-bs-parent='#accordionInformation'>
                <div class='accordion-body'>
                    <div class='row'>
                        <div class='col-md-3'>
                            <label class='form-label pt-1'>Klíč</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= htmlspecialchars($line['klic'], ENT_QUOTES, 'UTF-8') ?>'>
                        </div>
                        <div class='col-md-3'>
                            <label class='form-label pt-1'>VS</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= htmlspecialchars($line['VarSym'], ENT_QUOTES, 'UTF-8') ?>'>
                        </div>
                        <div class='col-md-4'>
                            <label class='form-label pt-1'>Zaplatit do</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= (!empty($line['ZaplatiNaMiste']) ? 'na místě' : htmlspecialchars($line['DatPay'], ENT_QUOTES, 'UTF-8')) ?>'>
                        </div>
                        <div class='col-md-12 py-2'></div>
                        <div class='col-md-4 <?= (!empty($line['ZaplatiNaMiste']) ? 'd-none' : '') ?>'>
                            <label class='form-label pt-1'>Urgence</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= (!empty($line['Urgence']) ? htmlspecialchars($line['Urgence'], ENT_QUOTES, 'UTF-8') : '---') ?>'>
                        </div>
                        <div class='col-md-4 <?= (!empty($line['ZaplatiNaMiste']) ? 'd-none' : '') ?>'>
                            <label class='form-label pt-1'>Zaplaceno dne</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= (!empty($line['DatumZaplaceni']) ? date('d.m.Y H:i', strtotime($line['DatumZaplaceni'])) : '---') ?>'>

                        </div>
                        <div class='col-md-3 <?= (!empty($line['ZaplatiNaMiste']) ? 'd-none' : '') ?>'>
                            <label class='form-label pt-1'>Částka (Kč)</label>
                            <input readonly class='bg-light text-dark form-control'
                                value='<?= (!empty($line['Castka']) ? htmlspecialchars($line['Castka'], ENT_QUOTES, 'UTF-8') : '---') ?>'>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php
}
