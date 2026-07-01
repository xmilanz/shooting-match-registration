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

// Načtení kategorií
$nazvy_kategorii = $zkratky_kategorii = [];
$result = $conn->query("SELECT * FROM $table_categories ORDER BY Id");
while ($row = $result->fetch_assoc()) {
    $id = $row['Id'];
    $nazvy_kategorii[$id] = $row['Value'];
    $zkratky_kategorii[$id] = $row['Name'];
}

// Načteme počty všech disciplin
$counts = [];
$sqlCounts = "SELECT Disciplina, COUNT(*) AS count FROM " . $table . " GROUP BY Disciplina";
$resCounts = $conn->query($sqlCounts);
while ($r = $resCounts->fetch_assoc()) {
    $counts[$r['Disciplina']] = (int)$r['count'];
}
$resCounts->free();

// Získáme kapacitu disciplín z nastavení závodu
$discMax = (int)($match_data['Squad_main_max'] ?? 0);

// Výpis disciplin
foreach ($nazvy_disciplin as $id => $nazev_discipliny) {
    $zkratka = htmlspecialchars($zkratky_disciplin[$id] ?? '', ENT_QUOTES, 'UTF-8');
    $popis = "- " . htmlspecialchars($popisy_disciplin[$id] ?? '', ENT_QUOTES, 'UTF-8') . "";
    $pocet = $counts[$zkratka] ?? 0;

    echo "<div class='row my-3 mx-1 ms-2 border border-primary bg-white clearfix'>";
    echo "<div class='caption col h5 py-2 px-2'><span>$nazev_discipliny <small>$popis</small></span>";

    // Stav registrace, tlacitko (single registrace)
    $disabledMatchBtn = "<button class='btn btn-outline-dark float-end' disabled>Pozastaveno</button>";
    $enabledBtn = "<button class='btn btn-primary float-end' data-bs-toggle='collapse' href='#reg_form_$id'>Vybrat</button>";
    $disabledBtn = "<button class='btn btn-danger float-end' disabled>Obsazeno</button>";

    if ($match_data['Zavod_registrace_pozastaveno'] == 1) {
        echo $disabledMatchBtn;
    } else if ($regAktivni) {
        echo ($pocet < $match_data['Squad_main_max']) ? $enabledBtn : $disabledBtn;
    }

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
        $serieIcon = ""; //doprogramovat podle potřeby, zatím ponecháno prázdné, protože se nevyužívá
        $staffIcon = "";
        if ($line['Staff'] == "RO") {
            $staffIcon = "<i class='far fa-clock' style='font-size:12px'></i>";
        };
        $pomIcon = "";
        if ($line['Staff'] == "POM") {
            $staffIcon = "<i class='far fa-handshake' style='font-size:12px'></i>";
        };
        $vipIcon = "";
        if ($line['Staff'] == "VIP") {
            $staffIcon = "<i class='far fa-crown' style='font-size:12px'></i>";
        };

        echo "<span class='fw-bold text-nowrap'>" . $serieIcon . $staffIcon . "&nbsp;" . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "</span>, ";
    }
    $stmt->close();
    echo "</div>";

    // SINGLE REGISTRACE
    echo "<div class='col bg-light m-3 border rounded border-primary'><div id='reg_form_$id' class='collapse'>";
    echo "<form class='row my-3 needs-validation' method='post' action='./save.php' novalidate>";
    echo "<input type='hidden' name='token' value='" . htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8') . "'>";
    echo "<input type='hidden' name='gender'>";
    echo "<input type='hidden' name='action' value='register_single'>";
    echo "<input type='hidden' name='Disciplina' value='" . htmlspecialchars($zkratka, ENT_QUOTES, 'UTF-8') . "'>";
    echo "<input type='hidden' name='datreg' value=" . $dnes->getTimestamp() . ">";

    $result = $conn->query("SELECT Max(Cislo) FROM " . $table . "");
    $line = $result ? $result->fetch_row() : [0];

    $tyden = (clone $datumZavod)->format('W');
    $varsymbol = "$tyden" . ($line[0] + 1);
?>
    <div class="row">
        <div class="col-md-3">
            <label for="Jmeno" class="form-label mt-3">Jméno</label>
            <input class="form-control" type="text" name="Jmeno" id="Jmeno" placeholder="Jan"
                onfocus="this.placeholder = ''" onblur="this.placeholder = 'Jan';replaceChars()" required>
            <div class="invalid-feedback">Nevyplnili jste jméno</div>
        </div>
        <div class="col-md-3">
            <label class="form-label mt-3">Příjmení</label>
            <input class="form-control" type="text" name="Prijmeni" id="Prijmeni<?= $zkratka ?>"
                onkeypress="return avoidspace(event)" onfocus="this.placeholder = ''" placeholder="Novák"
                onblur="this.placeholder = 'Novák';replaceChars('<?= $zkratka ?>')" required>
            <div class="invalid-feedback">Nevyplnili jste příjmení</div>
        </div>
        <div class="col-md-2">
            <label class="form-label mt-3">Doplnění jména</label>
            <select class="form-select" name="Prijmeni_stav" id="Prijmeni_stav<?= $zkratka ?>">
                <option value="" selected>-</option>
                <option value=" ml.">ml.</option>
                <option value=" st.">st.</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="Email" class="form-label mt-3">E-mail</label>
            <input class="form-control" type="email" id="Email<?= $zkratka ?>" name="Email" onfocus="this.placeholder = ''"
                onkeypress="return avoidspace(event)" placeholder="novak@mujemail.cz"
                onblur="replaceChars('<?= $zkratka ?>')" required>
            <div class="invalid-feedback">Nevyplnili jste e-mail</div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-3 <?= hidden($match_data['Zavod_obcansky_prukaz'] == 0); ?>">
            <label for="ObcanskyPrukaz" class="form-label mt-3">Číslo OP / EZP
                <a
                    role="button"
                    tabindex="0"
                    id="userInfoBtn"
                    data-bs-toggle="popover"
                    data-bs-placement="top"
                    data-bs-html="true"
                    data-bs-title="Občanský průkaz a Evrovský zbrojní pas"
                    data-bs-content="
                        Nemá-li závodník dosud vydaný občanský průkaz<br>(nejčastěji kategorie Junior), napište <strong>0000000000</strong>.<br><br>U cizích státních příslušníků vyplňte číslo identifikačního <br>průkazu i v případě, že obsahuje mezery nebo písmena.
                        ">
                    <sup><i class="fas fa-question-circle text-primary ms-1" style="font-size: 12px;"></i></sup>
                </a>
            </label>
            <input
                class="form-control"
                type="text"
                name="ObcanskyPrukaz"
                id="ObcanskyPrukaz<?= $zkratka ?>"
                placeholder="0123456789 / 0000000000" onfocus="this.placeholder = ''"
                onblur="this.placeholder = '0123456789 / 0000000000'"
                <?= required($match_data['Zavod_obcansky_prukaz'] == 1); ?>>
            <div class="invalid-feedback">Nevyplnili jste číslo OP / EZP<br>(u juniora bez OP napište 0000000000)</div>
        </div>

        <div class="col-md-3 mt-5 <?= hidden($match_data['Zavod_obcansky_prukaz'] == 0); ?>">
            <label class="form-check-label" for="ZbrojniOpravneni<?= $zkratka ?>">
                <input class="me-1" type="checkbox" class="form-check-input" id="ZbrojniOpravneni<?= $zkratka ?>"
                    name="ZbrojniOpravneni"> Držitel zbrojního oprávnění
            </label>
        </div>
        <div class="col-md-2">
            <label for="Kategorie" class="form-label mt-3">Kategorie</label>
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
        <div class="col-md-2">
            <label for="Staff" class="form-label mt-3">Staff</label>
            <select class="form-select" name=Staff>
                <option value="PAY" selected>Platící závodník</option>
                <option value="RO">Rozhodčí</option>
                <option value="POM">Pomocník</option>
            </select>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-3 <?= hidden($match_data['Zavod_cislo_zbrane'] == 0); ?>">
            <label for="CZ" class="form-label mt-3">Číslo zbraně</label>
            <input class="form-control" type="text" name="CZ" id="CZ<?= $zkratka ?>" placeholder=""
                onfocus="this.placeholder = ''" onblur="this.placeholder = ''" <?= required($match_data['Zavod_cislo_zbrane'] == 1); ?>>
            <div class="invalid-feedback">Nevyplnili jste číslo zbraně</div>
        </div>

        <div class="col-md-7 <?= hidden($match_data['Zavod_nazev_zbrane'] == 0); ?>">
            <label for="NZ" class="form-label mt-3">Název zbraně</label>
            <input class="form-control" type="text" name="NZ" id="NZ<?= $zkratka ?>" maxlength="255" placeholder=""
                onfocus="this.placeholder = ''" onblur="this.placeholder = ''" <?= required($match_data['Zavod_nazev_zbrane'] == 1); ?>>
            <div class="invalid-feedback">Nevyplnili jste název zbraně</div>
        </div>

    </div>

    <div class="col-md-12">
        <label for="Poznamka" class="form-label mt-3">Poznámka</label>
        <textarea class="form-control" type="text" name="Poznamka" id="Poznamka" placeholder="Poznámka"
            onfocus="this.placeholder = ''" onblur="this.placeholder = 'Poznámka'" rows="3"></textarea>
    </div>

    <div class="row px-4 mt-3">
        <div class="alert alert-danger m-lg-2 <?= hidden($match_data['Zavod_cislo_zbrane'] == 0); ?>" role="alert">
            Protože se eviduje VÝROBNÍ ČÍSLO ZBRANĚ, přineste si s sebou na prezenci výpis ze zbrojního listu pro jeho kontrolu.
        </div>
        <div class="alert alert-info m-lg-2" role="alert">
            Pokud sdílíte zbraň s jiným závodníkem, napište do poznámky jeho jméno a příjmení.
        </div>
    </div>

    <div class="row px-4 mt-3">
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
    <div class="col-12 text-center mt-3">
        <button type="submit" class="btn btn-primary mb-2">Registrovat</button>
    </div>
<?php
    echo "</form></div></div></div>";
}

?>