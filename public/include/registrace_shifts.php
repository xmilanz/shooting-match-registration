<?php
$stmt = $conn->prepare("
    SELECT Prijmeni,Jmeno,Stav,Disciplina FROM $table
    WHERE UPPER(`Disciplina`) != 'VYRAZENO' ORDER BY Disciplina
");
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();

$matrix = [];

while ($row = $result->fetch_assoc()) {
    $stav = (int) $row['Stav'];
    $shift = intdiv($stav, 100);
    $pos = $stav % 100;

    if ($shift < 1 || $pos < 1 || $pos > 10)
        continue;

    $matrix[$shift][$pos] = [
        'stav' => $stav,
        'name' => trim($row['Jmeno'] . ' ' . $row['Prijmeni']),
        'disc' => $row['Disciplina']
    ];
}

// Celkový počet směn z DB, fallback = 10
$pocetSmen = isset($match_data['Pocet_smen']) && (int) $match_data['Pocet_smen'] > 0
    ? (int) $match_data['Pocet_smen']
    : 10;

$allShiftKeys = range(1, $pocetSmen);

$disciplinesResult = $conn->query("SELECT Id,Value, Shift_from, Shift_to FROM $table_disciplines ORDER BY Id");
$disciplines = [];
while ($d = $disciplinesResult->fetch_assoc()) {
    $disciplines[] = $d;
}
?>

<!-- ============================================================
     MODAL – registrace na konkrétní stav (single disciplína)
============================================================ -->
<div class="modal fade" id="singleRegModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="singleRegForm" class="modal-content needs-validation" method="post" action="./save.php" novalidate>
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <input type="hidden" name="gender">
            <input type="hidden" name="action" value="register_shifts">
            <input type="hidden" name="datreg" value="<?php echo $dnes->getTimestamp(); ?>">
            <input type="hidden" name="Stav" id="modalStav" value="">
            <div class="modal-header bg-primary text-center">
                <h4 class="modal-title text-white w-100 fw-bold py-2">
                    Registrace - stav
                    <span id="modalStavLabel"></span>
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="window.location.href = 'index.php';"></button>
            </div>
            <div class="modal-body">

                <!-- Jméno / Příjmení -->
                <div class="row mb-4">
                    <div class="col-md-4">
                        <label class="form-label mt-3">Jméno</label>
                        <input class="form-control" type="text" name="Jmeno" onkeypress="return avoidspace(event)"
                            placeholder="Jan" onfocus="this.placeholder = ''"
                            onblur="this.placeholder = 'Jan';replaceChars()" required>
                        <div class="invalid-feedback">Vyplňte jméno</div>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label mt-3">Příjmení</label>
                        <input class="form-control" type="text" name="Prijmeni" id="Prijmeni" placeholder="Novák"
                            onfocus="this.placeholder = ''" onblur="this.placeholder = 'Novák';replaceChars()" required>
                        <div class="invalid-feedback">Vyplňte příjmení</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mt-3">Doplnění jména</label>
                        <select class="form-select" name="Prijmeni_stav" id="Prijmeni_stav<?= $zkratka ?>">
                            <option value="" selected>-</option>
                            <option value=" ml.">ml.</option>
                            <option value=" st.">st.</option>
                        </select>
                    </div>
                </div>

                <!-- E-mail / Kategorie / Staff -->
                <div class="row mb-4">
                    <div class="col-md-5">
                        <label class="form-label mt-3">E-mail</label>
                        <input class="form-control" type="email" id="Email" name="Email" onfocus="this.placeholder = ''"
                            onblur="this.placeholder='novak@mujemail.cz';replaceChars()" placeholder="novak@mujemail.cz"
                            value="" required>
                        <div class="invalid-feedback">Neplatný e-mail</div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label mt-3">Kategorie</label>
                        <select name="Kategorie" class="form-select">
                            <option value="Regular" selected>Regular</option>
                            <option value="Junior">Junior</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mt-3">Staff</label>
                        <select name="Staff" class="form-select" required>
                            <option value="PAY">Platící závodník</option>
                            <option value="RO">Rozhodčí</option>
                            <option value="POM">Pomocník</option>
                        </select>
                    </div>
                </div>

                <!-- OP / EZP -->
                <div class="row mb-4">
                    <div class="col-md-5 <?= hidden($match_data['Zavod_obcansky_prukaz'] == 0); ?>">
                        <label class="form-label mt-3">Číslo OP / EZP
                            <a href="#" role="button" tabindex="0" id="userInfoBtn" data-bs-toggle="popover"
                                data-bs-placement="top" data-bs-html="true"
                                data-bs-title="Občanský průkaz a Evrovský zbrojní pas"
                                data-bs-content="Nemá-li závodník dosud vydaný občanský průkaz<br>(nejčastěji kategorie Junior), napište <strong>0000000000</strong>.<br><br>U cizích státních příslušníků vyplňte číslo identifikačního <br>průkazu i v případě, že obsahuje mezery nebo písmena.">
                                <sup><i class="fas fa-question-circle text-primary ms-1"
                                        style="font-size: 12px;"></i></sup>
                            </a>
                        </label>
                        <input class="form-control" type="text" name="ObcanskyPrukaz"
                            placeholder="0123456789 / 0000000000" onfocus="this.placeholder = ''"
                            onblur="this.placeholder = '0123456789 / 0000000000'" <?= required($match_data['Zavod_obcansky_prukaz'] == 1); ?>>
                        <div class="invalid-feedback">Nevyplnili jste číslo OP / EZP<br>(u juniora bez OP napište
                            0000000000)</div>
                    </div>
                    <div class="col-md-6 mt-5 <?= hidden($match_data['Zavod_obcansky_prukaz'] == 0); ?>">
                        <label class="form-check-label" for="ZbrojniOpravneni">
                            <input class="me-1" type="checkbox" class="form-check-input" id="ZbrojniOpravneni"
                                name="ZbrojniOpravneni">Držitel zbrojního oprávnění
                        </label>
                    </div>
                </div>

                <!-- Disciplína -->
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label class="form-label mt-3">Disciplína</label>
                        <select name="Disciplina" class="form-select" required>
                            <option value="" selected>vyberte disciplínu</option>
                            <?php
                            $discResult = $conn->query("SELECT Name, Value, Shift_from, Shift_to FROM $table_disciplines ORDER BY Id");
                            if ($discResult) {
                                while ($disciplina = $discResult->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($disciplina['Name'], ENT_QUOTES, 'UTF-8') . '"'
                                        . ' data-from="' . (int) $disciplina['Shift_from'] . '"'
                                        . ' data-to="' . (int) $disciplina['Shift_to'] . '">'
                                        . htmlspecialchars($disciplina['Value'], ENT_QUOTES, 'UTF-8')
                                        . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <div class="invalid-feedback">Vyberte disciplínu</div>
                    </div>
                    <div class="col-md-6 <?= hidden($match_data['Zavod_cislo_zbrane'] == 0); ?>">
                        <label class="form-label mt-3">Číslo zbraně</label>
                        <input class="form-control" type="text" name="CZ" placeholder="" onfocus="this.placeholder = ''"
                            onblur="this.placeholder = ''" <?= required($match_data['Zavod_cislo_zbrane'] == 1); ?>>
                        <div class="invalid-feedback">Vyplňte číslo zbraně</div>
                    </div>
                    <div class="col-md-11 <?= hidden($match_data['Zavod_nazev_zbrane'] == 0); ?>">
                        <label class="form-label mt-3">Název zbraně</label>
                        <input class="form-control" type="text" name="NZ" placeholder="" onfocus="this.placeholder = ''"
                            onblur="this.placeholder = ''" <?= required($match_data['Zavod_nazev_zbrane'] == 1); ?>>
                        <div class="invalid-feedback">Vyplňte název zbraně</div>
                    </div>
                    <div class="col-md-12">
                        <label class="form-label mt-3">Poznámka</label>
                        <textarea class="form-control" name="Poznamka" placeholder="Poznámka..."
                            onfocus="this.placeholder = ''" onblur="this.placeholder = 'Poznámka...'"></textarea>
                    </div>
                </div>

                <!-- Souhlas s pravidly -->
                <div class="row my-3">
                    <div class="col-12 text-start">
                        Provedením registrace vyjadřuji souhlas s
                        <a data-bs-toggle="collapse" href="#collapseRules" role="button" aria-expanded="false"
                            aria-controls="collapseRules">pravidly registrace</a> a&nbsp;zpracováním osobních údajů.
                        <div class="collapse" id="collapseRules">
                            <div class="card card-body mt-2 mb-3 me-4">
                                <ul>
                                    <li>Registrace se uzavírá
                                        <?= ($match_data['Zavod_konec_registrace'] == 0) ? 'o půlnoci před registrací' : "$match_data[Zavod_konec_registrace] den/dny před konáním závodu" ?>.
                                    </li>
                                    <li>Pořadatelé si vyhrazují právo zařadit závodníků do jednotlivých směn za účelem
                                        zajištění hladkého průběhu závodu.</li>
                                    <li>Nezadá-li závodník při registraci platný e-mail, vystavuje se riziku, že nebude
                                        informován o případných změnách závodu.</li>
                                    <li class="<?= hidden($match_data['Payment_before'] == 0); ?>">Startovné se hradí tak, aby platba proběhla
                                        do
                                        <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dnů od
                                        registrace.<br>
                                        - u závodníků zaregistrovaných méně jak
                                        <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dní před závodem je
                                        třeba startovné zaplatit nejpozději dva dny před závodem
                                    </li>
                                    <li class="<?= hidden($match_data['Payment_before'] == 0); ?>">Startovné je nevratné, lze jej přenést na
                                        jiného závodníka.</li>
                                    <li class="<?= hidden($match_data['Payment_before'] == 0); ?>">V případě neuhrazení startovného v řádném
                                        termínu je registrace zrušena.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 text-center">
                        <div class="alert alert-danger <?= $zavodCisloZbraneClass ?>" role="alert">
                            Pro ověření čísla zbraně při prezenci je nutné, abyste měli výpis zbrojního listu
                            <small>(vytisknutý nebo online z Portálu občana).</small>
                        </div>
                        <div class="alert alert-info" role="alert">
                            Pokud sdílíte zbraň s jiným závodníkem, napište do poznámky jeho jméno a příjmení.
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="smena_registrovat" class="btn btn-primary">Registrovat</button>
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close"
                    onclick="window.location.href = 'registrace.php';">Zavřít</button>
            </div>
        </form>
    </div>
</div>

<!-- =================
     TABULKA SMĚN 
====================== -->

<div class="alert alert-info small mt-0" role="alert">
    Do konkrétní směny (stavu) se zaregistrujete kliknutím na neobsazené místo. Pro registraci další disciplíny tento postup opakujte.<br>
    V jednotlivých směnách se střílí pouze určité disciplíny, dostupné disciplíny pro směnu se zobrazí při umístění myši nad příslušnou buňku.<br>
    <strong><span class="text-danger">Registrujte se tak, aby se směny a stavy rovnoměrně obsazovaly - při nedoddržení bude pořadatel nucen změní stav bez ohledu na preferenci závodníka..</span></strong>
</div>


<div class="table-responsive-shifts pt-3">
    <table class="table table-bordered shift-table text-center align-middle">
        <thead>
            <tr style="height:40px;">
                <th></th>
                <?php for ($c = 1; $c <= 10; $c++): ?>
                    <th class="text-center align-middle text-white">Stav&nbsp;
                        <?= $c ?>
                    </th>
                <?php endfor; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($allShiftKeys as $shift): ?>
                <tr>

                    <th class="text-nowrap column1st">Směna
                        <?= $shift ?>
                    </th>

                    <?php for ($c = 1; $c <= 10; $c++):
                        $stavCislo = $shift * 100 + $c;
                        $isFree = !isset($matrix[$shift][$c]);
                    ?>

                        <td class="shift-cell <?= ($isFree && $regAktivni) ? 'open-reg-modal' : '' ?>" data-col="<?= $c ?>"
                            <?php if ($isFree && $regAktivni):
                                // Disciplíny povolené pro tuto směnu
                                $allowed = array_filter($disciplines, fn($d) => $shift >= $d['Shift_from'] && $shift <= $d['Shift_to']);
                                $popoverContent = implode('<br>', array_map(
                                    fn($d) => '• ' . htmlspecialchars($d['Value'], ENT_QUOTES, 'UTF-8'),
                                    $allowed
                                ));
                            ?>
                            data-stav="<?= $stavCislo ?>"
                            data-shift="<?= $shift ?>"
                            data-bs-toggle="modal"
                            data-bs-target="#singleRegModal"
                            data-bs-trigger="hover"
                            data-bs-placement="top"
                            data-bs-html="true"
                            data-bs-title="Disciplíny ve směně <?= $shift ?>"
                            data-bs-content="<?= $popoverContent ?>"
                            <?php endif; ?>>

                            <?php if (!$isFree):
                                $comp = $matrix[$shift][$c]; ?>

                                <div class="border rounded p-2 bg-white shadow-sm" style="font-size:.78rem;min-height:50px;">


                                    <strong>
                                        <?= htmlspecialchars($comp['name']) ?>
                                    </strong><br>
                                    <span class="text-muted">
                                        <?= htmlspecialchars($comp['disc']) ?>
                                    </span>
                                </div>

                            <?php else: ?>

                                <?php if ($regAktivni): ?>
                                    <span class="d-none badge bg-secondary">
                                        <?= $stavCislo ?>
                                    </span>
                                <?php else: ?>
                                    <!--span class="text-muted small">— volné —</span-->
                                    <span class="text-muted small"></span>
                                <?php endif; ?>

                            <?php endif; ?>

                        </td>

                    <?php endfor; ?>

                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <?php
    $stmt = $conn->prepare("
		SELECT Value,Shift_from,Shift_to FROM $table_disciplines ORDER BY Id
	");
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    ?>
    <div class="ms-3 mt-4">
        <h5>Registrace disciplín ve směnách</h5>
        <ul>
            <?php
            while ($line = $result->fetch_assoc()) {
                echo "<li>";
                echo htmlspecialchars($line['Value'], ENT_QUOTES, 'UTF-8') . ": směna " . $line['Shift_from'] . " - " . htmlspecialchars($line['Shift_to'], ENT_QUOTES, 'UTF-8') . "</li> ";
            }
            ?>
        </ul>
    </div>

</div>

<script>
    document.querySelectorAll('.open-reg-modal').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var stav = this.dataset.stav;
            var shift = parseInt(this.dataset.shift);

            document.getElementById('modalStav').value = stav;
            document.getElementById('modalStavLabel').textContent = stav;

            // Filtrování disciplín podle směny
            var select = document.querySelector('#singleRegModal select[name="Disciplina"]');
            Array.from(select.options).forEach(function(opt) {
                if (opt.value === '') return; // placeholder ponecháme
                var from = parseInt(opt.dataset.from);
                var to_ = parseInt(opt.dataset.to);
                opt.hidden = (shift < from || shift > to_);
                opt.disabled = (shift < from || shift > to_);
            });
            // Reset výběru
            select.value = '';
        });
    });
</script>

<script>
    document.querySelectorAll('.open-reg-modal').forEach(function(el) {
        new bootstrap.Popover(el, {
            trigger: 'hover',
            html: true,
            placement: el.dataset.bsPlacement || 'top',
            title: el.dataset.bsTitle,
            content: el.dataset.bsContent
        });
    });
</script>