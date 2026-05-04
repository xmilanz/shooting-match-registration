<?php
$result = $conn->query("SELECT * from $table_matches where Zavod_id='$table'");
$match_data = mysqli_fetch_array($result);

$paymentBeforeClass = ($match_data['Payment_before'] == 1) ? '' : 'd-none';
$paymentOnPlaceClass = ($match_data['Payment_before'] == 0) ? '' : 'd-none';

$paymentConfigRequired = ($match_data['Payment_before'] == 1) ? 'required' : '';

$admin_feature = ($_SESSION['role'] === 'admin') ? '' : 'd-none';
?>

<div class="modal fade" id="match_configuration" tabindex="-1" role="dialog" data-bs-backdrop="static"
    aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-center">
                <h4 class="modal-title text-white w-100 fw-bold py-2">Konfigurace závodu</h4>
                <br>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"
                    onclick="window.location.href = 'index.php';"></button>
            </div>
            <div class="modal-body">
                <div id='spinner' class='text-center w-100 mb-3' style='display:none;'>
                    <div class='spinner-border text-success' role='status'>
                        <span class='visually-hidden'>Načítání...</span>
                    </div>
                    <p class='text-success mt-2'>Ukládání...</p>
                </div>
                <form class="row needs-validation" method="post" action="./save.php" novalidate>
                    <!--Accordion-->
                    <div class="accordion" id="accordionMatchConfig">

                        <!-- accordion 1 Základní informace -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Základní informace
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show"
                                data-bs-parent="#accordionMatchConfig">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-8">
                                            <label for="Zavod" class="form-label pt-1">Název závodu<hh class="text-danger">&nbsp;*</hh></label>
                                            <input class="form-control form-control-sm" type="text" name="Zavod" id="Zavod"
                                                placeholder="název závodu"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'název závodu'"
                                                value="<?= htmlspecialchars($match_data['Zavod'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            <div class="invalid-feedback">Nevyplnili jste název závodu</div>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="Zavod_datum" class="form-label pt-1">Datum závodu<hh class="text-danger">&nbsp;*</hh></label>
                                            <input class="form-control form-control-sm" type="text" name="Zavod_datum"
                                                id="Zavod_datum" onkeypress="return avoidspace(event)"
                                                placeholder="datum závodu"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = '1.1.1970'"
                                                value="<?= htmlspecialchars($match_data['Zavod_datum'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            <div class="invalid-feedback">Nevyplnili jste datum závodu</div>
                                        </div>

                                        <div class="col-md-12 mt-2 ">
                                            <?php
                                            echo renderSwitch(
                                                'Zavod_registrace_pozastaveno',
                                                "<span class='fw-bold text-danger'>Pozastavit registraci</span>",
                                                (bool)$match_data['Zavod_registrace_pozastaveno'],
                                                $_SESSION['role'] === 'admin',
                                                'mt-2'
                                            );
                                            ?>
                                        </div>

                                        <div class="col-md-12 py-2"></div>
                                        <div class="col-md-12">
                                            <label for="Zavod_poradatel" class="form-label pt-1">Pořadatel<hh class="text-danger">&nbsp;*</hh></label>
                                            <input class="form-control form-control-sm" type="text"
                                                name="Zavod_poradatel" id="Zavod_poradatel"
                                                placeholder="Klub praktické střelby EGGENBERG"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'Klub praktické střelby EGGENBERG'"
                                                value="<?= htmlspecialchars($match_data['Zavod_poradatel'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            <div class="invalid-feedback">Nevyplnili jste pořadatele</div>
                                        </div>
                                        <div class="col-md-12 py-2"></div>
                                        <div class="col-md-5">
                                            <label for="Zavod_misto" class="form-label pt-1">Místo<hh class="text-danger">&nbsp;*</hh></label>
                                            <input class="form-control form-control-sm" type="text" name="Zavod_misto" id="Zavod_misto"
                                                placeholder="místo konání závodu"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'místo konání závodu'"
                                                value="<?= htmlspecialchars($match_data['Zavod_misto'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            <div class="invalid-feedback">Nevyplnili jste místo konání závodu</div>
                                        </div>
                                        <div class="col-md-7">
                                            <label for="Zavod_misto_mapa" class="form-label pt-1">Odkaz na mapy</label>
                                            <input class="form-control form-control-sm" type="text"
                                                name="Zavod_misto_mapa" id="Zavod_misto_mapa"
                                                placeholder="odkaz na Google mapy nebo mapy.cz"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'odkaz na Google mapy nebo mapy.cz'"
                                                value="<?= htmlspecialchars($match_data['Zavod_misto_mapa'], ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- accordion 2 Nastavení webových stránek -->
                        <div class="accordion-item <?= $admin_feature ?>">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Nastavení webových stránek
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse"
                                data-bs-parent="#accordionMatchConfig">
                                <div class="accordion-body">
                                    <div class="row mt-2">
                                        <div class="col-md-6">
                                            <?php
                                            echo renderSwitch(
                                                'Zavod_zobrazovat_sponzory',
                                                "Zobrazovat sponzory",
                                                (bool)$match_data['Zavod_zobrazovat_sponzory']
                                            );
                                            ?>
                                        </div>
                                        <div class="col-md-6">
                                            <?php
                                            echo renderSwitch(
                                                'Web_zobrazovat_vysledky',
                                                "Zobrazovat výsledky",
                                                (bool)$match_data['Web_zobrazovat_vysledky']
                                            );
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <div class="col-md-6">
                                            <label for="Klub_web" class="form-label pt-1">
                                                Webové stránky klubu<hh class="text-danger">&nbsp;*</hh>
                                            </label>
                                            <input class="form-control form-control-sm" type="text" id="Klub_web" name="Klub_web"
                                                placeholder="https://www.domena.cz"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'https://www.domena.cz'"
                                                value="<?= htmlspecialchars($match_data['Klub_web'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            <div class="invalid-feedback">Nevyplnili jste web klubu</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Zavod_vysledky" class="form-label pt-1">
                                                Stránka výsledků
                                            </label>
                                            <input class="form-control form-control-sm" type="text" id="Zavod_vysledky" name="Zavod_vysledky"
                                                placeholder="odkaz pro zobrazení výsledků"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'odkaz pro zobrazení výsledků'"
                                                value="<?= htmlspecialchars($match_data['Zavod_vysledky'], ENT_QUOTES, 'UTF-8') ?>">
                                        </div>
                                    </div>

                                    <div class="row my-3">
                                        <div class="col-md-6">
                                            <label for="Zavod_email_from" class="form-label pt-1">
                                                Odesílatel registračních emailů<hh class="text-danger">&nbsp;*</hh>
                                            </label>
                                            <input class="form-control form-control-sm" type="email"
                                                id="Zavod_email_from" name="Zavod_email_from"
                                                onkeypress="return avoidspace(event)"
                                                placeholder="registrace@kps-eggenberg.cz"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'registrace@kps-eggenberg.cz'"
                                                value="<?= htmlspecialchars($match_data['Zavod_email_from'], ENT_QUOTES, 'UTF-8') ?>" required>
                                            <div class="invalid-feedback">Nevyplnili jste email</div>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="Zavod_propozice" class="form-label pt-1">Propozice</label>
                                            <input class="form-control form-control-sm" type="text"
                                                name="Zavod_propozice" id="Zavod_propozice"
                                                placeholder="odkaz ke stažení propozic"
                                                onfocus="this.placeholder = ''"
                                                onblur="this.placeholder = 'odkaz ke stažení propozic'"
                                                value="<?= htmlspecialchars($match_data['Zavod_propozice'], ENT_QUOTES, 'UTF-8') ?>">

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- accordion 3 Nastavení závodu -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Nastavení závodu
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse"
                                data-bs-parent="#accordionMatchConfig">
                                <div class="accordion-body">
                                    <div class="row mt-3">
                                        <div class="col-md-5">
                                            <label class="form-check-label" for="Zavod_registrace_hromadna">
                                                <?php
                                                echo renderSwitch(
                                                    'Zavod_registrace_hromadna',
                                                    "Hromadná registrace",
                                                    (bool)$match_data['Zavod_registrace_hromadna']
                                                );
                                                ?>
                                        </div>
                                        <div class="col-md-5">
                                            <?php
                                            echo renderSwitch(
                                                'Zavod_registrace_smeny',
                                                "Registrace do směn",
                                                (bool)$match_data['Zavod_registrace_smeny']
                                            );
                                            ?>
                                        </div>
                                    </div>
                                    <div class="row mt-4">
                                        <div class="col-md-4">
                                            <?php
                                            echo renderSwitch(
                                                'Zavod_obcansky_prukaz',
                                                "Číslo OP",
                                                (bool)$match_data['Zavod_obcansky_prukaz']
                                            );
                                            ?>
                                        </div>
                                        <div class="col-md-4">
                                            <?php
                                            echo renderSwitch(
                                                'Zavod_cislo_zbrane',
                                                "Číslo zbraně",
                                                (bool)$match_data['Zavod_cislo_zbrane']
                                            );
                                            ?>
                                        </div>
                                        <div class="col-md-4">
                                            <?php
                                            echo renderSwitch(
                                                'Zavod_nazev_zbrane',
                                                "Název zbraně",
                                                (bool)$match_data['Zavod_nazev_zbrane']
                                            );
                                            ?>

                                        </div>
                                    </div>
                                    <div class="row mt-4 <?= $paymentOnPlaceClass ?>">
                                        <div class="col-md-12 ">
                                            <?php
                                            echo renderSwitch(
                                                'Zavod_platba_volitelna',
                                                'Volitelná platba
                                                      <a href="#" role="button" tabindex="0"
                                                          data-bs-toggle="popover"
                                                          data-bs-placement="top"
                                                          data-bs-html="true"
                                                          data-bs-title="Volitelná platba při placení soutěže na místě"
                                                          data-bs-content="Při zaškrtnutí volby se do registračního emailu vloží podklady pro platbu předem s informací o volitelnosti zaplacení. <span class=\'text-danger\'><br>
                                                          Ujistěte se, že je zadané správné číslo účtu a kód banky v sekci <strong>Placení závodu</strong></span><br>
                                                          <hr>
                                                          <i>Pro případ, že se rozhodnete startovné zaplatit předem, posíláme platební údaje:</i>
                                                          <ul>
                                                              <li>číslo účtu</li>
                                                              <li>variabilní symbol</li>
                                                              <li>částka</li>
                                                              <li>QR kód pro rychlou platbu</li>
                                                          </ul>">
                                                          <sup><i class="fas fa-question-circle text-primary ms-1" style="font-size: 12px;"></i></sup>
                                                      </a>',
                                                (bool)$match_data['Zavod_platba_volitelna']
                                            );                                            ?>
                                        </div>
                                    </div>
                                    <div class="row mt-3">
                                        <fieldset class="border p-3 my-3 rounded">
                                            <legend class="float-none w-auto px-2 h6">Registrace</legend>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="Zavod_zacatek_registrace" class="form-label pt-1">
                                                        Začátek (dní)
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_zacatek_registrace"
                                                        id="Zavod_zacatek_registrace"
                                                        onkeypress="return avoidspace(event)"
                                                        placeholder="před závodem"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = 'před závodem'"
                                                        value="<?= $match_data['Zavod_zacatek_registrace'] ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="Zavod_cas_registrace_zacatek" class="form-label pt-1">
                                                        Čas spuštění
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_cas_registrace_zacatek"
                                                        id="Zavod_cas_registrace_zacatek"
                                                        onkeypress="return avoidspace(event)"
                                                        placeholder="17:00"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = '17:00'"
                                                        value="<?= htmlspecialchars($match_data['Zavod_cas_registrace_zacatek'], ENT_QUOTES, 'UTF-8') ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="Zavod_konec_registrace" class="form-label pt-1">
                                                        Konec (dní)
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_konec_registrace"
                                                        id="Zavod_konec_registrace"
                                                        onkeypress="return avoidspace(event)"
                                                        placeholder="před zavodem"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = 'před zavodem'"
                                                        value="<?= $match_data['Zavod_konec_registrace'] ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="Zavod_cas_registrace_konec" class="form-label pt-1">
                                                        Čas ukončení
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_cas_registrace_konec"
                                                        id="Zavod_cas_registrace_konec"
                                                        onkeypress="return avoidspace(event)"
                                                        placeholder="18:00"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = '18:00'"
                                                        value="<?= htmlspecialchars($match_data['Zavod_cas_registrace_konec'], ENT_QUOTES, 'UTF-8') ?>">
                                                </div>

                                            </div>
                                        </fieldset>
                                        <fieldset class="border p-3 my-3 rounded">
                                            <legend class="float-none w-auto px-2 h6">Časový rozvrh</legend>
                                            <div class="row">
                                                <div
                                                    class="col-md-4 <?= (($match_data['Squad_prem_max'] == 0) ? 'd-none' : '') ?>">
                                                    <label for="Zavod_cas_prematch"
                                                        class="form-label pt-1">Prematch</label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_cas_prematch"
                                                        id="Zavod_cas_prematch"
                                                        placeholder="12:00 - 16:00"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = '12:00 - 16:00'"
                                                        value="<?= $match_data['Zavod_cas_prematch'] ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="Zavod_cas_prezentace" class="form-label pt-1">
                                                        Prezentace
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_cas_prezence"
                                                        id="Zavod_cas_prezence"
                                                        placeholder="8:00 - 9:00"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = '8:00 - 9:00'"
                                                        value="<?= $match_data['Zavod_cas_prezence'] ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="Zavod_cas_main" class="form-label pt-1">
                                                        Hlavní závod
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_cas_main"
                                                        id="Zavod_cas_main"
                                                        placeholder="13:00 - 17:00"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = '13:00 - 17:00'"
                                                        value="<?= $match_data['Zavod_cas_main'] ?>">
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <label for="Zavod_cas_main_dopoledne" class="form-label pt-1">
                                                        Dopolední směna
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_cas_main_dopoledne"
                                                        id="Zavod_cas_prezence"
                                                        placeholder="9:00 - 12:00"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = '9:00 - 12:00'"
                                                        value="<?= $match_data['Zavod_cas_main_dopoledne'] ?>">
                                                </div>
                                                <div class="col-md-4">
                                                    <label for="Zavod_cas_main_odpoledne" class="form-label pt-1">
                                                        Odpolední směna
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Zavod_cas_main_odpoledne"
                                                        id="Zavod_cas_main_odpoledne"
                                                        placeholder="13:00 - 16:00"
                                                        onfocus="this.placeholder = ''"
                                                        onblur="this.placeholder = '13:00 - 16:00'"
                                                        value="<?= $match_data['Zavod_cas_main_odpoledne'] ?>">
                                                </div>
                                            </div>
                                        </fieldset>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="Zavod_min_pocet_ran" class="form-label pt-1">
                                                    Počet ran<hh class="text-danger">&nbsp;*</hh>
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_min_pocet_ran" id="Zavod_min_pocet_ran"
                                                    value="<?= $match_data['Zavod_min_pocet_ran'] ?>" required>
                                                <div class="invalid-feedback">Nevyplnili jste počet ran</div>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="Zavod_stages" class="form-label pt-1">
                                                    Počet terčů / situací<hh class="text-danger">&nbsp;*</hh>
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_stages" id="Zavod_stages"
                                                    onkeypress="return avoidspace(event)"
                                                    value="<?= $match_data['Zavod_stages'] ?>" required>
                                                <div class="invalid-feedback">Nevyplnili jste počet terčů / situací</div>
                                            </div>
                                            <div class="col-md-4">
                                                <label for="Pocet_smen" class="form-label pt-1">
                                                    Počet směn
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Pocet_smen"
                                                    id="Pocet_smen"
                                                    onkeypress="return avoidspace(event)"
                                                    placeholder="hromadná registrace"
                                                    onfocus="this.placeholder = ''"
                                                    onblur="this.placeholder = 'hromadná registrace'"
                                                    onkeypress="return avoidspace(event)"
                                                    value="<?= $match_data['Pocet_smen'] ?>">
                                            </div>
                                        </div>
                                        <fieldset class="border p-3 my-3 rounded">
                                            <legend class="float-none w-auto px-2 h6">
                                                Počty závodníků
                                            </legend>
                                            <div class="row">
                                                <div class="col-md-3 ">
                                                    <label for="Squad_prem_max" class="form-label">
                                                        Prematch
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Squad_prem_max"
                                                        id="Squad_prem_max"
                                                        onkeypress="return avoidspace(event)"
                                                        value="<?= $match_data['Squad_prem_max'] ?>">
                                                </div>
                                                <div class="col-md-5">
                                                    <label for="Squad_main_max" class="form-label">
                                                        Disciplína / squad<hh class="text-danger">&nbsp;*</hh>
                                                    </label>
                                                    <input class="form-control form-control-sm" type="text"
                                                        name="Squad_main_max"
                                                        id="Squad_main_max"
                                                        onkeypress="return avoidspace(event)"
                                                        value="<?= $match_data['Squad_main_max'] ?>" required>
                                                    <div class="invalid-feedback">Nevyplnili jste počet závodníků</div>
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- accordion 4 Vedení závodu -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    Vedení závodu
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse"
                                data-bs-parent="#accordionMatchConfig">
                                <div class="accordion-body">

                                    <fieldset class="border p-3 my-3 rounded">
                                        <legend class="float-none w-auto px-2 h6">Ředitel</legend>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="Zavod_match_director" class="form-label small">
                                                    Jméno<hh class="text-danger">&nbsp;*</hh>
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_match_director"
                                                    id="Zavod_match_director"
                                                    value="<?= htmlspecialchars($match_data['Zavod_match_director'], ENT_QUOTES, 'UTF-8') ?>" required>
                                                <div class="invalid-feedback">Nevyplnili jste ředitele soutěže</div>
                                            </div>
                                            <div class="col-md-5">
                                                <label for="Zavod_email_poradatel" class="form-label small">
                                                    E-mail
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_email_poradatel"
                                                    id="Zavod_email_poradatel"
                                                    onkeypress="return avoidspace(event)"
                                                    value="<?= htmlspecialchars($match_data['Zavod_email_poradatel'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="Zavod_telefon_poradatel" class="form-label small">
                                                    Telefon
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_telefon_poradatel" id="Zavod_telefon_poradatel"
                                                    value="<?= htmlspecialchars($match_data['Zavod_telefon_poradatel'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset class="border p-3 my-3 rounded">
                                        <legend class="float-none w-auto px-2 h6">
                                            Hlavní rozhodčí
                                        </legend>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="Zavod_range_master" class="form-label small">
                                                    Jméno
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_range_master"
                                                    id="Zavod_range_master"
                                                    value="<?= htmlspecialchars($match_data['Zavod_range_master'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-5">
                                                <label for="Zavod_email_range_master" class="form-label small">
                                                    E-mail
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_email_range_master"
                                                    id="Zavod_email_range_master"
                                                    value="<?= htmlspecialchars($match_data['Zavod_email_range_master'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="Zavod_telefon_range_master" class="form-label small">
                                                    Telefon
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_telefon_range_master"
                                                    id="Zavod_telefon_range_master"
                                                    value="<?= htmlspecialchars($match_data['Zavod_telefon_range_master'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset class="border p-3 my-3 rounded">
                                        <legend class="float-none w-auto px-2 h6">
                                            Statistik
                                        </legend>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="Zavod_stats" class="form-label small">
                                                    Jméno
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_stats"
                                                    id="Zavod_stats"
                                                    value="<?= htmlspecialchars($match_data['Zavod_stats'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-5">
                                                <label for="Zavod_email_stats" class="form-label small">
                                                    E-mail
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_email_stats"
                                                    id="Zavod_email_stats"
                                                    value="<?= htmlspecialchars($match_data['Zavod_email_stats'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="Zavod_telefon_stats" class="form-label small">
                                                    telefon
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_telefon_stats" id="Zavod_telefon_stats"
                                                    value="<?= htmlspecialchars($match_data['Zavod_telefon_stats'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset class="border p-3 my-3 rounded">
                                        <legend class="float-none w-auto px-2 h6">
                                            Hospodář
                                        </legend>
                                        <div class="row">
                                            <div class="col-md-3">
                                                <label for="Zavod_hospodar" class="form-label small">
                                                    Jméno
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_hospodar"
                                                    id="Zavod_hospodar"
                                                    value="<?= htmlspecialchars($match_data['Zavod_hospodar'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-5">
                                                <label for="Zavod_email_hospodar" class="form-label small">
                                                    E-mail
                                                </label>
                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_email_hospodar"
                                                    id="Zavod_email_hospodar"
                                                    value="<?= htmlspecialchars($match_data['Zavod_email_hospodar'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                            <div class="col-md-4">
                                                <label for="Zavod_telefon_hospodar" class="form-label">
                                                    Telefon</label>

                                                <input class="form-control form-control-sm" type="text"
                                                    name="Zavod_telefon_hospodar"
                                                    id="Zavod_telefon_hospodar"
                                                    value="<?= htmlspecialchars($match_data['Zavod_telefon_hospodar'], ENT_QUOTES, 'UTF-8') ?>">
                                            </div>
                                        </div>
                                    </fieldset>
                                </div>
                            </div>
                        </div>

                        <!-- accordion 5 Placení závodu -->
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                    data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                    Placení závodu&nbsp;<span class="text-secondary small"><?php if ($match_data['Payment_before'] == 1) {
                                                                                                echo "(startovné se platí do " . $match_data['Zavod_pocet_dni_na_platbu'] . " dnů od registrace)";
                                                                                            } else {
                                                                                                echo "(startovné se platí na místě)";
                                                                                            } ?> </span>
                                </button>
                            </h2>
                            <div id="collapseFive" class="accordion-collapse collapse"
                                data-bs-parent="#accordionMatchConfig">
                                <div class="accordion-body">
                                    <div class="row">
                                        <div class="col-md-12 mb-3 ">
                                            <?php
                                            echo renderSwitch(
                                                'Payment_before',
                                                "<span class='fw-bold text-danger'>Placení startovného
                                                   " . htmlspecialchars($match_data['Zavod_pocet_dni_na_platbu'], ENT_QUOTES, 'UTF-8') . "
                                                       dnů od registrace
                                                </span>",
                                                (bool)$match_data['Payment_before']
                                            );
                                            ?>
                                        </div>
                                        <div class="col-md-7 ">
                                            <label for="Banka_ucet_cislo" class="form-label pt-1">
                                                Číslo účtu
                                            </label>
                                            <input class="form-control form-control-sm" type="text"
                                                id="Banka_ucet_cislo"
                                                name="Banka_ucet_cislo"
                                                onkeypress="return avoidspace(event)"
                                                value="<?= htmlspecialchars($match_data['Banka_ucet_cislo'], ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $paymentConfigRequired ?>>
                                            <div class="invalid-feedback">Nevyplnili jste číslo účtu</div>
                                        </div>
                                        <div class="col-md-4 ">
                                            <label for="Banka_ucet_kod" class="form-label pt-1">Kód banky</label>
                                            <input class="form-control form-control-sm" type="text"
                                                id="Banka_ucet_kod"
                                                name="Banka_ucet_kod"
                                                onkeypress="return avoidspace(event)"
                                                value="<?= htmlspecialchars($match_data['Banka_ucet_kod'], ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $paymentConfigRequired ?>>
                                            <div class="invalid-feedback">Nevyplnili jste kód banky</div>
                                        </div>
                                        <div class="col-md-11 pt-2 <?= "$paymentBeforeClass" ?>">
                                            <label for="Banka_nazev" class="form-label pt-1">
                                                Banka
                                            </label>
                                            <input class="form-control form-control-sm" type="text"
                                                id="Banka_nazev"
                                                name="Banka_nazev"
                                                value="<?= htmlspecialchars($match_data['Banka_nazev'], ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $paymentConfigRequired ?>>
                                            <div class="invalid-feedback">Nevyplnili jste název banky</div>
                                        </div>
                                        <div class="col-md-11 <?= "$paymentBeforeClass" ?>">
                                            <label for="Banka_adresa" class="form-label pt-1">
                                                Adresa banky
                                            </label>
                                            <input class="form-control form-control-sm" type="text"
                                                id="Banka_adresa"
                                                name="Banka_adresa"
                                                value="<?= htmlspecialchars($match_data['Banka_adresa'], ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $paymentConfigRequired ?>>
                                            <div class="invalid-feedback">Nevyplnili jste adresu banky</div>
                                        </div>
                                        <div class="col-md-11 pt-1 <?= "$paymentBeforeClass" ?>">
                                            <label for="Zavod_poradatel_adresa" class="form-label pt-1">
                                                Adresa pořadatele
                                            </label>
                                            <input class="form-control form-control-sm" type="text"
                                                name="Zavod_poradatel_adresa"
                                                id="Zavod_poradatel_adresa"
                                                value="<?= htmlspecialchars($match_data['Zavod_poradatel_adresa'], ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $paymentConfigRequired ?>>
                                            <div class="invalid-feedback">Nevyplnili jste adresu banky</div>
                                        </div>
                                        <div class="col-md-5 pt-2 <?= "$paymentBeforeClass" ?>">
                                            <label for="Zavod_pocet_dni_na_platbu" class="form-label pt-1">Počet dní na
                                                platbu</label>
                                            <input class="form-control form-control-sm" type="text"
                                                name="Zavod_pocet_dni_na_platbu"
                                                id="Zavod_pocet_dni_na_platbu"
                                                onkeypress="return avoidspace(event)"
                                                value="<?= htmlspecialchars($match_data['Zavod_pocet_dni_na_platbu'], ENT_QUOTES, 'UTF-8') ?>"
                                                <?= $paymentConfigRequired ?>>
                                            <div class="invalid-feedback">Nevyplnili jste počet dní na platbu</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="submit" name="match_config" class="btn btn-success">Uložit konfiguraci závodu</button>
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close"
                    onclick="window.location.href = 'index.php';">Zavřít bez uložení</button>
            </div>
            </form>
        </div>
    </div>
</div>

<script>
    // Disable form submissions if there are invalid fields
    (function() {
        'use strict';
        window.addEventListener('load', function() {
            // Get the forms we want to add validation styles to
            var forms = document.getElementsByClassName('needs-validation');
            // Loop over them and prevent submission
            var validation = Array.prototype.filter.call(forms, function(form) {
                form.addEventListener('submit', function(event) {
                    if (form.checkValidity() === false) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }, false);
    })();
</script>

<script src="./js/bootstrap-datepicker.cs.js" charset="UTF-8"></script>
<script>
    $('#Zavod_datum').datepicker({
        autoclose: true,
        todayHighlight: true,
        language: 'cs'
    });
</script>

<script>
    const hromadna = document.getElementById('Zavod_registrace_hromadna');
    const smeny = document.getElementById('Zavod_registrace_smeny');

    [hromadna, smeny].forEach(cb => {
        cb.addEventListener('change', () => {
            if (cb.checked) {
                // odškrtne druhý checkbox
                [hromadna, smeny].filter(c => c !== cb).forEach(c => c.checked = false);
            }
        });
    });
</script>