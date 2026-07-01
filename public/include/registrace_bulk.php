<?php
$disabledBtn = "<button class='btn btn-lg btn-primary mb-3 ms-2 'disabled>Registrace jedné nebo více disciplín</button>";
$enabledBtn = "<button class='btn btn-lg btn-primary mb-3 ms-2 ' data-bs-toggle='modal' data-bs-target='#bulkRegModal'>Registrace do jedné nebo více disciplín</button>";

if ($match_data['Zavod_registrace_pozastaveno'] == 1) {
    echo $disabledBtn;
} elseif ($regAktivni) {
    echo $enabledBtn;
}
?>
<div class="modal fade" id="bulkRegModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="bulkRegForm" class="modal-content needs-validation" method="post" action="./save.php" novalidate>
            <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
            <input type="hidden" name="gender">
            <input type='hidden' name='action' value='register_bulk'>
            <input type="hidden" name="datreg" value="<?php echo $dnes->getTimestamp(); ?>">
            <div class="modal-header bg-primary text-center">
                <h4 class="modal-title text-white w-100 fw-bold py-2">Registrace do jedné nebo více disciplín</h4>
                <br>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="window.location.href = 'index.php';"></button>
            </div>
            <div class="modal-body">
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
                        <select class="form-select" name="Prijmeni_stav" id="Prijmeni_stav_bulk">
                            <option value="" selected>-</option>
                            <option value=" ml.">ml.</option>
                            <option value=" st.">st.</option>
                        </select>
                    </div>
                </div>
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
                        <select class="form-select" name=Kategorie required>
                            <option value="" selected>--- vyberte ---</option>
                            <?php
                            $stmt = $conn->prepare("SELECT * from $table_categories ORDER BY Id");
                            $stmt->execute();
                            $result_names = $stmt->get_result();
                            while ($line = $result_names->fetch_array()) {
                                echo "<option value=" . $line['Name'] . ">" . $line['Value'] . "</option>";
                            }
                            $stmt->close();
                            ?>
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

                <!-- Dynamické řádky disciplín -->
                <div id="discRows">
                    <div class="row disc-row pb-2">
                        <div class="col-md-5">
                            <label class="form-label mt-3">Disciplína</label>
                            <select name="Disciplina[]" class="form-select" required>
                                <option value="" selected>vyberte disciplínu</option>
                                <?php
                                $discResult = $conn->query("SELECT Name, Value FROM $table_disciplines ORDER BY Id");
                                if ($discResult) {
                                    while ($discipline = $discResult->fetch_assoc()) {
                                        echo "<option value=\"" . htmlspecialchars($discipline['Name'], ENT_QUOTES, 'UTF-8') . "\">" . htmlspecialchars($discipline['Value'], ENT_QUOTES, 'UTF-8') . "</option>";
                                    }
                                }
                                ?>
                            </select>
                            <div class="invalid-feedback">Vyberte disciplínu</div>
                        </div>
                        <div class="col-md-6 <?= hidden($match_data['Zavod_cislo_zbrane'] == 0); ?>">
                            <label class="form-label mt-3">Číslo zbraně</label>
                            <input class="form-control" type="text" name="CZ[]" placeholder=""
                                onfocus="this.placeholder = ''" onblur="this.placeholder = ''"
                                <?= required($match_data['Zavod_cislo_zbrane'] == 1); ?>>
                            <div class="invalid-feedback">Vyplňte číslo zbraně</div>
                        </div>
                        <div class="col-md-11 <?= hidden($match_data['Zavod_nazev_zbrane'] == 0); ?>">
                            <label class="form-label mt-3">Název zbraně</label>
                            <input class="form-control" type="text" name="NZ[]" placeholder=""
                                onfocus="this.placeholder = ''" onblur="this.placeholder = ''"
                                <?= required($match_data['Zavod_nazev_zbrane'] == 1); ?>>
                            <div class="invalid-feedback">Vyplňte název zbraně</div>
                        </div>

                        <div class="col-md-11 d-flex flex-column">
                            <label class="form-label mt-3">Poznámka</label>
                            <textarea class="form-control" type="text" name="Poznamka[]"
                                placeholder="Požadavek na směnu,..." onfocus="this.placeholder = ''"
                                onblur="this.placeholder = 'Požadavek na směnu,...' "></textarea>
                        </div>
                        <div class="col-md-1 d-flex ">
                            <div class="mt-auto ms-auto">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-row">
                                    &minus;
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <button type="button" id="addDisc" class="btn btn-sm btn-success mt-1 ms-2">
                    + Další disciplína
                </button>


                <div class="row pe-4 mt-3">
                    <div class="alert alert-danger m-lg-2 <?= hidden($match_data['Zavod_cislo_zbrane'] == 0); ?>" role="alert">
                        Při prezenci se eviduje také VÝROBNÍ ČÍSLO ZBRANĚ. Můžete ho vyplnit zde ve formuláři nebo si jej přineste s sebou <small>(napsané na papíru, vytisknutý výpis ze zbrojního listu nebo online v Portálu občana).</small>
                    </div>
                    <div class="alert alert-info m-lg-2" role="alert">
                        Pokud sdílíte zbraň s jiným závodníkem, napište do poznámky jeho jméno a příjmení.
                    </div>
                </div>

                <div class="row pe-4 mt-3">
                    <div class="col-12 text-center">
                        Provedením registrace vyjadřuji souhlas s
                        <a data-bs-toggle="collapse" href="#collapseRules" role="button" aria-expanded="false"
                            aria-controls="collapseRules">pravidly registrace</a> a&nbsp;zpracováním osobních údajů.
                        <div class="collapse text-start" id="collapseRules">
                            <div class="card card-body mt-2 mb-3 me-4">
                                <ul>
                                    <li>Registrace se uzavírá
                                        <?= ($match_data['Zavod_konec_registrace'] == 0) ? 'o půlnoci před registrací' : "$match_data[Zavod_konec_registrace] den/dny před konáním závodu" ?>.
                                    </li>
                                    <li>Pořadatelé si vyhrazují právo zařadit závodníků do jednotlivých směn za účelem zajištění hladkého
                                        průběhu závodu.</li>
                                    <li>Nezadá-li závodník při registraci platný email, vystavuje se riziku, že nebude informován o
                                        případných změnách závodu.</li>
                                    <li class="<?= hidden($match_data['Payment_before'] == 0); ?> ">Startovné se hradí tak, aby platba proběhla do
                                        <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dnů od registrace.<br>- u závodníků
                                        zaregistrovaných méně jak <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dní před závodem
                                        je třeba startovné zaplatit nejpozději dva dny před závodem
                                    </li>
                                    <li class="<?= hidden($match_data['Payment_before'] == 0); ?>">Startovné je nevratné, lze jej přenést na jiného závodníka.</li>
                                    <li class="<?= hidden($match_data['Payment_before'] == 0); ?>">V případě neuhrazení startovného v řádném termínu je registrace
                                        zrušena.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Registrovat</button>
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close"
                    onclick="window.location.href = 'registrace.php';">Zavřít</button>
            </div>
        </form>
    </div>
</div>


<?php
// Načtení disciplín
$nazvy_disciplin = $zkratky_disciplin = $popisy_disciplin = [];
$result = $conn->query("SELECT * FROM $table_disciplines ORDER BY Id");
while ($row = $result->fetch_assoc()) {
    $id = $row['Id'];
    $nazvy_disciplin[$id] = $row['Value'];
    $zkratky_disciplin[$id] = $row['Name'];
    $popisy_disciplin[$id] = $row['Description'];
}

// Počet závodníků
$stmt = $conn->prepare("SELECT COUNT(*) FROM " . $table . " WHERE Disciplina = ?");
$stmt->bind_param("s", $zkratka);
$stmt->execute();
$stmt->bind_result($pocet);
$stmt->fetch();
$stmt->close();

// Výpis disciplin
foreach ($nazvy_disciplin as $id => $nazev_discipliny) {
    $zkratka = htmlspecialchars($zkratky_disciplin[$id] ?? '', ENT_QUOTES, 'UTF-8');
    $popis = htmlspecialchars($popisy_disciplin[$id] ?? '', ENT_QUOTES, 'UTF-8');


    echo "<div class='row my-3 mx-1 ms-2 border border-primary bg-white clearfix'>";
    echo "<div class='caption col h5 py-2 px-2'><span>$nazev_discipliny <small>- $popis</small></span>";

    echo "</div>";
    echo "<div class='col-12 d-block pb-3 text-start'>";

    // Výpis závodníků
    $stmt = $conn->prepare("SELECT Alias,Prijmeni,Jmeno,Zaplaceno,DatumZaplaceni,ZaplatiNaMiste,DatPay,Staff,Disciplina,Urgence FROM " . $table . " WHERE Disciplina = ? ORDER BY DatReg DESC, Prijmeni");
    $stmt->bind_param("s", $zkratka);
    $stmt->execute();
    $result_names = $stmt->get_result();
    while ($line = $result_names->fetch_assoc()) {
        $datumZaplatit = new DateTime($line['DatPay']);
        $datumPaymentWarn = (clone $datumZaplatit)->modify("-5 days");

        if ($match_data['Payment_before'] == 0) {
            echo "<span class=text-dark>";
        } elseif ($line['Zaplaceno'] == 1) {
            echo "<span class=text-success>";
        } elseif (($dnes >= $datumPaymentWarn) and $line['Disciplina'] != "VYRAZENO" and $line['Zaplaceno'] == 0 and $line['ZaplatiNaMiste'] == 0) {
            echo "<span class= text-danger>";
        }

        // definice ikon
        $serieIcon = "";
        $roIcon = $pomIcon = $vipIcon = "";
        if ($line['Staff'] === "RO") {
            $roIcon = "<i class='far fa-clock' style='font-size:12px'></i>";
        } elseif ($line['Staff'] === "POM") {
            $pomIcon = "<i class='far fa-handshake' style='font-size:12px'></i>";
        } elseif ($line['Staff'] === "VIP") {
            $vipIcon = "<i class='far fa-crown' style='font-size:12px'></i>";
        }

        // SEZNAM ZÁVODNÍKŮ
        echo "<span class='fw-bold text-nowrap'>" . $serieIcon . $roIcon . $pomIcon . $vipIcon . "&nbsp;" . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "</span>, ";
    }
    $stmt->close();

    echo "</div>";

    echo "<div class='col bg-light m-3 border rounded border-primary'>";
    echo "</div></div>";
}
?>