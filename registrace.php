<?php
include "./header.php";

session_start();
$_SESSION['token'] = bin2hex(random_bytes(32));

$casRegistraceKonec = $match_data['Zavod_cas_registrace_konec'];
$casRegistraceZacatek = $match_data['Zavod_cas_registrace_zacatek'];

$zavodObcanskyPrukazRequired = !empty($match_data['Zavod_obcansky_prukaz']) ? 'required' : '';
$zavodObcanskyPrukazClass = !empty($match_data['Zavod_obcansky_prukaz']) ? '' : 'd-none';

$zavodCisloZbraneRequired = !empty($match_data['Zavod_cislo_zbrane']) ? 'required' : '';
$zavodCisloZbraneClass = !empty($match_data['Zavod_cislo_zbrane']) ? '' : 'd-none';

$zavodRegistraceHromadnaClass = !empty($match_data['Zavod_registrace_hromadna']) ? '' : 'd-none';
$zavodRegistraceSingleClass = empty($match_data['Zavod_registrace_hromadna']) ? '' : 'd-none';

$dnes = new DateTime();

$datumZavod = new DateTime($match_data['Zavod_datum']);

$datumZacatekRegistrace = (clone $datumZavod)
    ->modify("-{$match_data['Zavod_zacatek_registrace']} days")
    ->setTime(...explode(':', $casRegistraceZacatek));

// vytvořit konec registrace (datum minus Y dní + čas)
$datumKonecRegistrace = (clone $datumZavod)
    ->modify("-{$match_data['Zavod_konec_registrace']} days")
    ->setTime(...explode(':', $casRegistraceKonec));

$reg_started = false;
$reg_text = "";

// Stav registrace
if ($match_data['Zavod_registrace_pozastaveno'] === "on") {
    $reg_text = "<span class='text-danger'>Registrace je pozastavená</span>";
} else if ($dnes > $datumKonecRegistrace) {
    $reg_text = "Registrace skončila " . $datumKonecRegistrace->format('j.n.Y H:i') . " ";
    $match_data['Squad_main_max'] = 0;
    $match_data['Squad_prem_max'] = 0;
} else if ($dnes < $datumZacatekRegistrace) {
    $reg_text = "Registrace bude spuštěna " . $datumZacatekRegistrace->format('j.n.Y H:i') . " ";
    $match_data['Squad_main_max'] = 0;
    $match_data['Squad_prem_max'] = 0;
} else {
    $reg_started = true;
    $reg_text = "Registrace bude ukončena " . $datumKonecRegistrace->format('j.n.Y H:i') . " ";
}

?>
<h2 class='pb-3'><?= $reg_text ?></h2>


<!-- HROMADNA REGISTRACE -->
<div class="<?= $zavodRegistraceHromadnaClass ?>">
    <?php
    $disabledBtn = "<button class='btn btn-lg btn-primary mb-3 ms-2 'disabled>Registrace jedné nebo více disciplín</button>";
    $enabledBtn = "<button class='btn btn-lg btn-primary mb-3 ms-2 ' data-bs-toggle='modal' data-bs-target='#bulkRegModal'>Registrace do jedné nebo více disciplín</button>";

    if ($match_data['Zavod_registrace_pozastaveno'] == "on") {
        echo $disabledBtn;
    } else if ($reg_started && $dnes < $datumKonecRegistrace) {
        echo $enabledBtn;
    } else {
        echo "";
    }
    ?>
    <div class="modal fade" id="bulkRegModal" tabindex="-1">
        <div class="modal-dialog">
            <form id="bulkRegForm" class="modal-content needs-validation" method="post" action="./save.php" novalidate>
                <input type="hidden" name="token" value="<?php echo $_SESSION['token']; ?>">
                <input type="hidden" name="gender">
                <input type="hidden" name="datreg" value="<?php echo $dnes->getTimestamp(); ?>">
                <div class="modal-header bg-primary text-center">
                    <h4 class="modal-title text-white w-100 fw-bold py-2">Registrace do jedné nebo více disciplín</h4>
                    <br>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href = 'index.php';"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <label class="form-label mt-3">Jméno</label>
                            <input class="form-control" type="text" name="Jmeno" onkeypress="return avoidspace(event)" placeholder="Jan" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Jan';replaceChars()" required>
                            <div class="invalid-feedback">Vyplňte jméno</div>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label mt-3">Příjmení</label>
                            <input class="form-control" type="text" name="Prijmeni" id="Prijmeni" placeholder="Novák" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Novák';replaceChars()" required>
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
                    <div class="row mb-4">
                        <div class="col-md-5">
                            <label class="form-label mt-3">E-mail</label>
                            <input class="form-control" type="email" id="Email" name="Email" onfocus="this.placeholder = ''" onblur="this.placeholder='novak@mujemail.cz';replaceChars()" placeholder="novak@mujemail.cz" value="" required>
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
                    <div class="row mb-4">
                        <div class="col-md-5 <?= $zavodObcanskyPrukazClass ?>">
                            <label class="form-label mt-3">Číslo OP / EZP
                                <a
                                    href="#"
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
                            <input class="form-control" type="text" name="ObcanskyPrukaz" placeholder="0123456789 / 0000000000" onfocus="this.placeholder = ''" onblur="this.placeholder = '0123456789 / 0000000000'" <?= $zavodObcanskyPrukazRequired ?>>
                            <div class="invalid-feedback">Nevyplnili jste číslo OP / EZP<br>(u juniora bez OP napište 0000000000)</div>
                        </div>
                        <div class="col-md-6 mt-5 <?= $zavodObcanskyPrukazClass ?>">
                            <label class="form-check-label" for="ZbrojniOpravneni">
                                <input class="me-1" type="checkbox" class="form-check-input" id="ZbrojniOpravneni" name="ZbrojniOpravneni">Držitel zbrojního oprávnění
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
                                    $query = mysqli_query($conn, "SELECT * from $table_disciplines ORDER BY Id");
                                    while ($discipline = mysqli_fetch_array($query)) {
                                        echo "<option value=" . htmlspecialchars($discipline['Name'], ENT_QUOTES, 'UTF-8') . ">" . htmlspecialchars($discipline['Value'], ENT_QUOTES, 'UTF-8') . "</option>";
                                    }
                                    ?>
                                </select>
                                <div class="invalid-feedback">Vyberte disciplínu</div>
                            </div>
                            <div class="col-md-6 <?= $zavodCisloZbraneClass ?>">
                                <label class="form-label mt-3">Číslo zbraně</label>
                                <input class="form-control" type="text" name="CZ[]" placeholder="" onfocus="this.placeholder = ''" onblur="this.placeholder = ''" <?= $zavodCisloZbraneRequired ?>>
                                <div class="invalid-feedback">Vyplňte číslo zbraně</div>
                            </div>
                            <div class="col-md-11 d-flex flex-column">
                                <label class="form-label mt-3">Poznámka</label>
                                <textarea class="form-control" type="text" name="Poznamka[]" placeholder="Požadavek na směnu,..." onfocus="this.placeholder = ''" onblur="this.placeholder = 'Požadavek na směnu,...' "></textarea>
                            </div>
                            <div class="col-md-1 d-flex ">
                                <div class="mt-auto ms-auto">
                                    <button type="button"
                                        class="btn btn-sm btn-outline-danger remove-row">
                                        &minus;
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <button type="button" id="addDisc" class="btn btn-sm btn-success mt-1 ms-2">
                        + Další disciplína
                    </button>
                    <div class="row">
                        <div class="col-12 mt-4 text-start">
                            Provedením registrace vyjadřuji souhlas s
                            <a data-bs-toggle="collapse" href="#collapseRules" role="button" aria-expanded="false" aria-controls="collapseRules">pravidly registrace</a> a&nbsp;zpracováním osobních údajů.
                            <div class="collapse" id="collapseRules">
                                <div class="card card-body mt-2 mb-3 me-4">
                                    <ul>
                                        <li>Registrace se uzavírá <?= ($match_data['Zavod_konec_registrace'] == 0) ? 'o půlnoci před registrací' : "$match_data[Zavod_konec_registrace] den/dny před konáním závodu" ?>.</li>
                                        <li>Pořadatelé si vyhrazují právo zařadit závodníků do jednotlivých směn za účelem zajištění hladkého průběhu závodu.</li>
                                        <li>Nezadá-li závodník při registraci platný e-mail, vystavuje se riziku, že nebude informován o případných změnách závodu.</li>
                                        <li class="<?= $paymentBeforeClass ?> ">Startovné se hradí tak, aby platba proběhla do <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dnů od registrace.<br>- u závodníků zaregistrovaných méně jak <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dní před závodem je třeba startovné zaplatit nejpozději dva dny před závodem</li>
                                        <li class="<?= $paymentBeforeClass ?>">Startovné je nevratné, lze jej přenést na jiného závodníka.</li>
                                        <li class="<?= $paymentBeforeClass ?>">V případě neuhrazení startovného v řádném termínu je registrace zrušena.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12 text-center">
                            <div class="bd-callout-info m-lg-2">
                                <strong><i class="fas fa-info-circle"></i></strong>Při registraci více disciplín je vybírejte podle toho, jak je budete chtít střílet.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="bulk_registrovat" class="btn btn-primary">Registrovat</button>
                    <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href = 'registrace.php';">Zavřít</button>
                </div>
            </form>
        </div>
    </div>
</div>

<h2 class="pb-1 pt-2 <?= $zavodRegistraceHromadnaClass ?>">Seznam závodníků v disciplínách</h2>
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

// Výpis disciplin
foreach ($nazvy_disciplin as $id => $nazev_discipliny) {
    $zkratka = htmlspecialchars($zkratky_disciplin[$id] ?? '', ENT_QUOTES, 'UTF-8');
    $popis = htmlspecialchars($popisy_disciplin[$id] ?? '', ENT_QUOTES, 'UTF-8');

    // Počet závodníků
    $stmt = $conn->prepare("SELECT COUNT(*) FROM " . $table . " WHERE Disciplina = ?");
    $stmt->bind_param("s", $zkratka);
    $stmt->execute();
    $stmt->bind_result($pocet);
    $stmt->fetch();
    $stmt->close();

    echo "<div class='row my-3 mx-1 ms-2 border border-primary bg-white clearfix'>";
    echo "<div class='caption col h5 py-2 px-2'><span>$nazev_discipliny <small>- $popis</small></span>";

    // Stav registrace, tlacitko (single registrace)
    $disabledMatchBtn = "<button class='<?= $zavodRegistraceSingleClass ?> btn btn-outline-dark float-end' disabled>Pozastaveno</button>";
    $enabledBtn = "<button class='<?= $zavodRegistraceSingleClass ?> btn btn-primary float-end' data-bs-toggle='collapse' href='#reg_form_$id'>Vybrat</button>";
    $disabledBtn = "<button class='<?= $zavodRegistraceSingleClass ?> btn btn-danger float-end' disabled>Obsazeno</button>";

    if ($match_data['Zavod_registrace_pozastaveno'] == "on") {
        echo $disabledMatchBtn;
    } else if ($reg_started && $dnes < $datumKonecRegistrace) {
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

        if (empty($match_config['Payment_before'])) {
            echo "<span class=text-dark>";
        } elseif ($line['Zaplaceno'] == "on") {
            echo "<span class=text-success>";
        } elseif (($dnes >= $datumPaymentWarn)  and $line['Disciplina'] != "VYRAZENO" and $line['Zaplaceno'] != "on" and $line['ZaplatiNaMiste'] != "on") {
            echo "<span class= text-danger>";
        }

        // definice ikon 
        $serieIcon = "";
        $line['Staff'] == "RO" ? $roIcon = "<i class='far fa-clock' style='font-size:12px'></i>" : $roIcon = "";
        $line['Staff'] == "POM" ? $roIcon = "<i class='far fa-handshake' style='font-size:12px'></i>" : $roIcon = "";
        $line['Staff'] == "VIP" ? $roIcon = "<i class='far fa-crown' style='font-size:12px'></i>" : $roIcon = "";

        echo "<span class='fw-bold text-nowrap'>" . $serieIcon . $roIcon . $pomIcon . $vipIcon . "&nbsp;" . htmlspecialchars($line['Jmeno'], ENT_QUOTES, 'UTF-8') . " " . htmlspecialchars($line['Prijmeni'], ENT_QUOTES, 'UTF-8') . "</span>, ";
    }
    $stmt->close();
    echo "</div>";

    // SINGLE REGISTRACE
    echo "<div class='col bg-light m-3 border rounded border-primary'><div id='reg_form_$id' class='collapse'>";
    echo "<form class='row my-3 needs-validation' method='post' action='./save.php' novalidate>";
    echo "<input type='hidden' name='token' value=" . $_SESSION['token'] . ">";
    echo "<input type='hidden' name='gender'>";
    echo "<input type='hidden' name='Disciplina' value='" . htmlspecialchars($zkratka, ENT_QUOTES, 'UTF-8') . "'>";
    echo "<input type='hidden' name='datreg' value=" . $dnes->getTimestamp() . ">";

    $result = $conn->query("SELECT Max(Cislo) FROM " . $table . "");
    $line = mysqli_fetch_row($result);

    $tyden = (clone $datumZavod)->format('W');
    $varsymbol = "$tyden" . ($line[0] + 1);
?>
    <div class="row">
        <div class="col-md-3">
            <label for="Jmeno" class="form-label mt-3">Jméno</label>
            <input class="form-control" type="text" name="Jmeno" id="Jmeno" placeholder="Jan" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Jan';replaceChars()" required>
            <div class="invalid-feedback">Nevyplnili jste jméno</div>
        </div>
        <div class="col-md-3">
            <label class="form-label mt-3">Příjmení</label>
            <input class="form-control" type="text" name="Prijmeni" id="Prijmeni<?= $zkratka ?>" onkeypress="return avoidspace(event)" onfocus="this.placeholder = ''" placeholder="Novák" onblur="this.placeholder = 'Novák';replaceChars('<?= $zkratka ?>')" required>
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
            <input class="form-control" type="email" id="Email<?= $zkratka ?>" name="Email" onfocus="this.placeholder = ''" onkeypress="return avoidspace(event)" placeholder="novak@mujemail.cz" onblur="replaceChars('<?= $zkratka ?>')" required>
            <div class="invalid-feedback">Nevyplnili jste e-mail</div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-3 <?= $zavodObcanskyPrukazClass ?>">
            <label for="ObcanskyPrukaz" class="form-label mt-3">Číslo OP / EZP
                <a
                    href="#"
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
            <input class="form-control" type="text" name="ObcanskyPrukaz" id="ObcanskyPrukaz<?= $zkratka ?>" placeholder="0123456789 / 0000000000" onfocus="this.placeholder = ''" onblur="this.placeholder = '0123456789 / 0000000000'" <?= $zavodObcanskyPrukazRequired ?>>
            <div class="invalid-feedback">Nevyplnili jste číslo OP / EZP<br>(u juniora bez OP napište 0000000000)</div>
        </div>
        <div class="col-md-2 mt-5 <?= $zavodObcanskyPrukazClass ?>">
            <label class="form-check-label" for="ZbrojniOpravneni<?= $zkratka ?>">
                <input class="me-1" type="checkbox" class="form-check-input" id="ZbrojniOpravneni<?= $zkratka ?>" name="ZbrojniOpravneni"> Držitel zbrojního oprávnění
            </label>
        </div>
        <div class="col-md-3 <?= $zavodCisloZbraneClass ?>">
            <label for="CZ" class="form-label mt-3">Číslo zbraně</label>
            <input class="form-control" type="text" name="CZ" id="CZ<?= $zkratka ?>" placeholder="" onfocus="this.placeholder = ''" onblur="this.placeholder = ''" <?= $zavodCisloZbraneRequired ?>>
            <div class="invalid-feedback">Nevyplnili jste číslo zbraně</div>
        </div>

        <div class="col-md-2">
            <label for="Kategorie" class="form-label mt-3">Kategorie</label>
            <select class="form-select" name="Kategorie" id="Kategorie<?= $zkratka ?>">
                <option value="Regular" selected>Regular</option>
                <option value="Junior">Junior</option>
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
    <div class="col-md-12">
        <label for="Poznamka" class="form-label mt-3">Poznámka</label>
        <textarea class="form-control" type="text" name="Poznamka" id="Poznamka" placeholder="Poznámka" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Poznámka'" rows="3"></textarea>
    </div>

    <div class="col-12 mt-4 text-start">
        Provedením registrace vyjadřuji souhlas s
        <a data-bs-toggle="collapse" href="#collapseRules" role="button" aria-expanded="false" aria-controls="collapseRules">pravidly registrace</a> a&nbsp;zpracováním osobních údajů.
        <div class="collapse" id="collapseRules">
            <div class="card card-body mt-2 mb-3 me-4">
                <ul>
                    <li>Registrace se uzavírá <?= ($match_data['Zavod_konec_registrace'] == 0) ? 'o půlnoci před registrací' : "$match_data[Zavod_konec_registrace] den/dny před konáním závodu" ?>.</li>
                    <li>Pořadatelé si vyhrazují právo zařadit závodníků do jednotlivých směn za účelem zajištění hladkého průběhu závodu.</li>
                    <li>Nezadá-li závodník při registraci platný email, vystavuje se riziku, že nebude informován o případných změnách závodu.</li>
                    <li class="<?= $paymentBeforeClass ?> ">Startovné se hradí tak, aby platba proběhla do <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dnů od registrace.<br>- u závodníků zaregistrovaných méně jak <?php echo $match_data['Zavod_pocet_dni_na_platbu']; ?> dní před závodem je třeba startovné zaplatit nejpozději dva dny před závodem</li>
                    <li class="<?= $paymentBeforeClass ?>">Startovné je nevratné, lze jej přenést na jiného závodníka.</li>
                    <li class="<?= $paymentBeforeClass ?>">V případě neuhrazení startovného v řádném termínu je registrace zrušena.</li>
                </ul>
            </div>
        </div>
    </div>
    <div class="row py-2">
        <div class="bd-callout-info m-lg-2">
            <strong><i class="fas fa-info-circle"></i></strong>Pokud sdílíte zbraň s jiným závodníkem, napište do poznámky jeho jméno a příjemní
        </div>
    </div>

    <div class="col-12 text-center">
        <button type="submit" name="registrovat" class="btn btn-primary mt-2">Registrovat</button>
    </div>
<?php
    echo "</form></div></div></div>";
}
?>

<script type="text/javascript" src="./js/reg_form.js"></script>
<?php include "./footer.php"; ?>