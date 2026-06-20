<!-- ===============================================================
    REGISTRCE TENOLIX K4M - junior evidence nadstandardnich udaju
    - rok narozeni
    - klub
    - trenink
    - zodpovedna osoba
=============================================================== -->

<?php
// Tenolix special: registrace je řízená pouze kategoriemi.
// Disciplínu řešíme jen interně kvůli uložení do DB (vezmeme 1. z `$table_disciplines`).
$onlyDisciplineCodeRaw = '';
$onlyDisciplineCode = '';
$resDisc = $conn->query("SELECT Name FROM $table_disciplines ORDER BY Id LIMIT 1");
if ($resDisc && ($rowDisc = $resDisc->fetch_assoc())) {
    $onlyDisciplineCodeRaw = (string)($rowDisc['Name'] ?? '');
    $onlyDisciplineCode = htmlspecialchars($onlyDisciplineCodeRaw, ENT_QUOTES, 'UTF-8');
}

// Načtení kategorií
$kategorie = [];
$resultKat = $conn->query("SELECT * FROM ssas_k4m_tenolix_categories ORDER BY Id");
if ($resultKat) {
    while ($row = $resultKat->fetch_assoc()) {
        $katId = (int)$row['Id'];
        $kategorie[$katId] = [
            'Name' => (string)$row['Name'],
            'Value' => (string)$row['Value'],
        ];
    }
}

foreach ($kategorie as $katId => $kat) {
    $katCodeRaw = (string)($kat['Name'] ?? '');
    $katLabel = htmlspecialchars((string)($kat['Value'] ?? ''), ENT_QUOTES, 'UTF-8');
    $katCode = htmlspecialchars($katCodeRaw, ENT_QUOTES, 'UTF-8');

    // Počet závodníků v kategorii (Disciplina držíme fixně jen kvůli DB)
    if ($onlyDisciplineCodeRaw !== '') {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE Disciplina = ? AND Kategorie = ?');
        $stmt->bind_param('ss', $onlyDisciplineCodeRaw, $katCodeRaw);
    } else {
        $stmt = $conn->prepare('SELECT COUNT(*) FROM ' . $table . ' WHERE Kategorie = ?');
        $stmt->bind_param('s', $katCodeRaw);
    }
    $stmt->execute();
    $stmt->bind_result($pocet);
    $stmt->fetch();
    $stmt->close();

    echo "<div class='row my-3 mx-1 ms-2 border border-primary bg-white clearfix'>";
    echo "<div class='caption col h5 py-2 px-2'><span>kategorie " . $katLabel . " </span>";

    // Stav registrace, tlacitko (single registrace)
    $disabledMatchBtn = "<button class='btn btn-outline-dark float-end' disabled>Pozastaveno</button>";
    $enabledBtn = "<button class='btn btn-primary float-end' data-bs-toggle='collapse' href='#reg_form_kat_$katId'>Vybrat</button>";
    $disabledBtn = "<button class='btn btn-danger float-end' disabled>Obsazeno</button>";

    if ($match_data['Zavod_registrace_pozastaveno'] == 1) {
        echo $disabledMatchBtn;
    } else if ($regAktivni) {
        echo ($pocet < $match_data['Squad_main_max']) ? $enabledBtn : $disabledBtn;
    }

    echo '</div>';
    echo "<div class='col-12 d-block pb-3 text-start'>";
    // Výpis závodníků v kategorii
    if ($onlyDisciplineCodeRaw !== '') {
        $stmt = $conn->prepare('SELECT Alias,Prijmeni,Jmeno,Zaplaceno,DatumZaplaceni,ZaplatiNaMiste,DatPay,Staff,Disciplina,Urgence FROM ' . $table . ' WHERE Disciplina = ? AND Kategorie = ? ORDER BY DatReg DESC, Prijmeni');
        $stmt->bind_param('ss', $onlyDisciplineCodeRaw, $katCodeRaw);
    } else {
        $stmt = $conn->prepare('SELECT Alias,Prijmeni,Jmeno,Zaplaceno,DatumZaplaceni,ZaplatiNaMiste,DatPay,Staff,Disciplina,Urgence FROM ' . $table . ' WHERE Kategorie = ? ORDER BY DatReg DESC, Prijmeni');
        $stmt->bind_param('s', $katCodeRaw);
    }
    $stmt->execute();
    $result_names = $stmt->get_result();
    while ($line = $result_names->fetch_assoc()) {
        $datumZaplatit = new DateTime($line['DatPay']);
        $datumPaymentWarn = (clone $datumZaplatit)->modify('-5 days');

        if ($match_data['Payment_before'] == 0) {
            echo '<span class=text-dark>';
        } elseif ($line['Zaplaceno'] == 1) {
            echo '<span class=text-success>';
        } elseif (($dnes >= $datumPaymentWarn) and $line['Disciplina'] != 'VYRAZENO' and $line['Zaplaceno'] == 0 and $line['ZaplatiNaMiste'] == 0) {
            echo '<span class= text-danger>';
        }

        echo "<span class='fw-bold text-nowrap'>" . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . ' ' . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . '</span>, ';
    }
    $stmt->close();
    echo '</div>';

    // SINGLE REGISTRACE (kategorie)
    echo "<div class='col bg-light m-3 border rounded border-primary'><div id='reg_form_kat_$katId' class='collapse'>";
    echo "<form class='row my-3 needs-validation' method='post' action='./save.php' novalidate>";
    echo "<input type='hidden' name='action' value='register_tenolix'>";
    echo "<input type='hidden' name='token' value='" . htmlspecialchars($_SESSION['token'] ?? '', ENT_QUOTES, 'UTF-8') . "'>";
    echo "<input type='hidden' name='gender'>";
    echo "<input type='hidden' name='Disciplina' value='" . $onlyDisciplineCode . "'>";
    echo "<input type='hidden' name='Kategorie' value='" . $katCode . "'>";
    echo "<input type='hidden' name='Staff' value='PAY'>";
    echo "<input type='hidden' name='datreg' value=" . $dnes->getTimestamp() . '>';

    $result = $conn->query('SELECT Max(Cislo) FROM ' . $table . '');
    $line = $result ? $result->fetch_row() : [0];

    $tyden = (clone $datumZavod)->format('W');
    $varsymbol = "$tyden" . ($line[0] + 1);
?>
    <div class="row">
        <div class="col-md">
            <fieldset class="border p-3 mx-3 rounded">

                <legend class="float-none w-auto px-2 h6">Osobní údaje</legend>
                <label for="Jmeno" class="form-label">Jméno</label>

                <div class="row">
                    <div class="col-md-12">
                        <input class="form-control" type="text" name="Jmeno" id="Jmeno" placeholder="Jan"
                            onfocus="this.placeholder = ''" onblur="this.placeholder = 'Jan';replaceChars()" required>
                        <div class="invalid-feedback">Nevyplnili jste jméno</div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-9">
                        <label class="form-label mt-3">Příjmení</label>
                        <input class="form-control" type="text" name="Prijmeni" id="Prijmeni<?= $katCode ?>"
                            onkeypress="return avoidspace(event)" onfocus="this.placeholder = ''" placeholder="Novák"
                            onblur="this.placeholder = 'Novák';replaceChars('<?= $katCode ?>')" required>
                        <div class="invalid-feedback">Nevyplnili jste příjmení</div>
                    </div>
                    <div class="col-md-3">
                        <label for="Rocnik" class="form-label mt-3">Ročník</label>
                        <input class="form-control" type="year" pattern="[0-9]{4}" maxlength="4" name="Rocnik" id="Rocnik<?= $katCode ?>"
                            onkeypress="return avoidspace(event)" onfocus="this.placeholder = ''" placeholder="2000"
                            onblur="this.placeholder = '2000';replaceChars('<?= $katCode ?>')" required>
                        <div class="invalid-feedback">Nevyplnili jste rok narození</div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <label class="form-label mt-3">Klub</label>
                        <input class="form-control" type="text" name="Klub" id="Klub<?= $katCode ?>"
                            onfocus="this.placeholder = ''" placeholder="SSAS Prachatice"
                            onblur="this.placeholder = 'SSAS Prachatice';replaceChars('<?= $katCode ?>')" required>
                        <div class="invalid-feedback">Nevyplnili jste klub</div>
                    </div>
                </div>
        </div>
        </fieldset>

        <div class="col-md">
            <fieldset class="border p-3 mx-3 rounded">
                <legend class="float-none w-auto px-2 h6">Doprovod</legend>
                <label class="form-label">Zodpovědná osoba</label>
                <input class="form-control" type="text" name="ZodpovednaOsoba" id="ZodpovednaOsoba<?= $katCode ?>"
                    onfocus="this.placeholder = ''" placeholder="Příjmení Jméno"
                    onblur="this.placeholder = 'Příjmení Jméno';replaceChars('<?= $katCode ?>')">
                <div class="invalid-feedback">Nevyplnili jste zodpovědnou osobu</div>

                <label for="Email" class="form-label mt-3">E-mail</label>
                <input class="form-control" type="email" id="Email<?= $katCode ?>" name="Email" onfocus="this.placeholder = ''"
                    onkeypress="return avoidspace(event)" placeholder="novak@mujemail.cz"
                    onblur="replaceChars('<?= $katCode ?>')" required>
                <div class="invalid-feedback">Nevyplnili jste e-mail</div>

            <label for="ObcanskyPrukaz" class="form-label mt-3">Číslo OP / EZP
                <a
                    role="button"
                    tabindex="0"
                    id="userInfoBtn"
                    data-bs-toggle="popover"
                    data-bs-placement="top"
                    data-bs-html="true"
                    data-bs-title="Občanský průkaz a Evrovský zbrojní pas"
                    data-bs-content="Nemá-li závodník dosud vydaný občanský průkaz<br>(nejčastěji kategorie Junior), napište <strong>0000000000</strong>.<br><br>U cizích státních příslušníků vyplňte číslo identifikačního <br>průkazu i v případě, že obsahuje mezery nebo písmena.">
                    <sup><i class="fas fa-question-circle text-primary ms-1"></i></sup>
                </a>
            </label>
                <input class="form-control" type="text" name="ObcanskyPrukaz" id="ObcanskyPrukaz<?= $katCode ?>"
                    placeholder="0123456789 / 0000000000" onfocus="this.placeholder = ''"
                    onblur="this.placeholder = '0123456789 / 0000000000'" <?= required($match_data['Zavod_obcansky_prukaz'] == 1); ?>>
                <div class="invalid-feedback">Nevyplnili jste číslo OP / EZP</div>
            </fieldset>
        </div>
    </div>

    <div class="row ps-4 pe-5 mt-3">
        <fieldset class="border p-3 mx-3 rounded">
            <legend class="float-none w-auto px-2 h6">Závod</legend>
            <div class="row">
                <div class="col-md-4">
                    <label for="CZ" class="form-label">Číslo zbraně</label>
                    <input class="form-control" type="text" name="CZ" id="CZ<?= $katCode ?>" placeholder=""
                        onfocus="this.placeholder = ''" onblur="this.placeholder = ''" <?= required($match_data['Zavod_cislo_zbrane'] == 1); ?>>
                    <div class="invalid-feedback">Nevyplnili jste číslo zbraně</div>
                </div>
                <div class="col-md-2">
                    <label class="form-check-label mt-4" for="Trenink<?= $katCode ?>">
                        <input class="me-1" type="checkbox" class="form-check-input" id="Trenink<?= $katCode ?>"
                            name="Trenink"> Trénink
                    </label>
                </div>
                <div class="col-md-6">
                    <label for="Poznamka" class="form-label">Poznámka</label>
                    <textarea class="form-control" type="text" name="Poznamka" id="Poznamka" placeholder="Poznámka"
                        onfocus="this.placeholder = ''" onblur="this.placeholder = 'Poznámka'" rows="3"></textarea>
                </div>
            </div>
        </fieldset>
    </div>

    <div class="row px-4 mt-3">
        <div class="alert alert-danger m-lg-2 <?= hidden($match_data['Zavod_cislo_zbrane'] == 0); ?>" role="alert">
            Při prezenci se eviduje také VÝROBNÍ ČÍSLO ZBRANĚ. Můžete ho vyplnit zde ve formuláři nebo si jej přineste s sebou <small>(napsané na papíru, vytisknutý výpis ze zbrojního listu nebo online v Portálu občana).</small>
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