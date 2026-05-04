<?php
$paymentBeforeClass = !empty($match_data['Payment_before']) ? '' : 'd-none';

?>

<div class="modal fade" id="new_shooter" tabindex="-1" role="dialog" data-bs-backdrop="static" data-bs-keyboard="false"
    aria-hidden="true">
    <div class="modal-dialog modal-notify modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-center">
                <h4 class="modal-title text-white w-100 fw-bold">Nový závodník</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form class="row needs-validation" method="post" action="./save.php" novalidate>
                    <?php
                    list($usec, $sec) = explode(" ", microtime());
                    echo "<INPUT TYPE=HIDDEN NAME=datreg VALUE=" . $sec . ">";
                    ?>
                    <div class="row m-1">
                        <fieldset class="border p-2 rounded">
                            <legend class="float-none w-auto px-2 h6">Osobní informace</legend>
                            <div class="row p-1">
                                <div class="col-md-3">
                                    <label for="Jmeno" class="form-label mt-2">Jméno</label>
                                    <input class="form-control" type="text" name="Jmeno" id="Jmeno" placeholder="Jan"
                                        onfocus="this.placeholder = ''" onblur="this.placeholder = 'Jan';replaceChars()"
                                        required>
                                    <div class="invalid-feedback">Nevyplnili jste jméno</div>
                                </div>
                                <div class="col-md-5">
                                    <label for="Prijmeni" class="form-label mt-2">Příjmení</label>
                                    <input class="form-control" type="text" name="Prijmeni" id="Prijmeni"
                                        placeholder="Novák" onfocus="this.placeholder = ''"
                                        onblur="this.placeholder = 'Novák';replaceChars()" required>
                                    <div class="invalid-feedback">Nevyplnili jste příjmení</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="Prijmeni_stav" class="form-label mt-2">Doplnění jména</label>
                                    <select class="form-select" name=Prijmeni_stav>
                                        <option value="" selected>--- vyberte ---</option>
                                        <option value=" ml.">ml.</option>
                                        <option value=" st.">st.</option>
                                    </select>
                                </div>
                            </div>
                            <div class="row p-1">
                                <div class="col-md-8">
                                    <label for="Mail" class="form-label mt-3">Email</label>
                                    <input class="form-control" type="email" id="Mail" name="Mail"
                                        onfocus="this.placeholder = ''"
                                        onblur="this.placeholder='novak@mujemail.cz';replaceChars()"
                                        placeholder="novak@mujemail.cz" value="" required>
                                    <div class="invalid-feedback">Nevyplnili jste e-mail</div>
                                </div>
                                <div class="col-md-4">
                                    <label for="Kategorie" class="form-label mt-3">Kategorie</label>
                                    <select name="Kategorie" id="Kategorie" class="form-select">
                                        <option value="Regular">Regular</option>
                                        <option value="Junior">Junior</option>
                                    </select>
                                </div>
                            </div>
                        </fieldset>
                    </div>

                    <div class="row m-1">
                        <fieldset class="border p-4 my-3 rounded">
                            <legend class="float-none w-auto px-2 h6">Soutěž</legend>
                            <div class="row">
                                <div class="col-md-6 <?= hidden($match_data['Zavod_obcansky_prukaz'] == 0); ?>">
                                    <label for="ObcanskyPrukaz" class="form-label mt-2">Číslo OP / EZP
                                        <a href="#" role="button" tabindex="0" id="userInfoBtn" data-bs-toggle="popover"
                                            data-bs-placement="top" data-bs-html="true"
                                            data-bs-title="Občanský průkaz a Evrovský zbrojní pas"
                                            data-bs-content="Nemá-li závodník dosud vydaný občanský průkaz<br>(nejčastěji kategorie Junior), napište <strong>0000000000</strong>.<br><br>U cizích státních příslušníků vyplňte číslo identifikačního <br>průkazu i v případě, že obsahuje mezery nebo písmena.">
                                            <sup><i class="fas fa-question-circle text-primary ms-1"
                                                    style="font-size: 12px;"></i></sup>
                                        </a>
                                    </label>
                                    <input class="form-control" type="text" name="ObcanskyPrukaz" id="ObcanskyPrukaz"
                                        placeholder="0123456789 / 0000000000" onfocus="this.placeholder = ''"
                                        onblur="this.placeholder = '0123456789 / 0000000000'">
                                </div>
                                <div class="col-md-6 mt-5 <?= hidden($match_data['Zavod_obcansky_prukaz'] == 0); ?>">
                                    <label class="form-check-label" for="ZbrojniOpravneni">
                                        <input type="checkbox" class="form-check-input" id="ZbrojniOpravneni"
                                            name="ZbrojniOpravneni"> Držitel zbrojního oprávnění
                                    </label>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 <?= hidden($match_data['Zavod_registrace_smeny'] == 0); ?>">
                                    <label class="form-label mt-3">Stav</label>
                                    <input class="form-control" type="text" name="Stav" placeholder=""
                                        onfocus="this.placeholder = ''" onblur="this.placeholder = ''"
                                        <?= required($match_data['Zavod_registrace_smeny'] == 1); ?>>
                                    <div class="invalid-feedback">Vyplňte stav</div>
                                </div>

                                <div class="col-md-8">
                                    <label for="Disciplina" class="form-label mt-3">Disciplína</label>
                                    <select class="form-select" name=Disciplina required>
                                        <option value="" selected>--- vyberte ---</option>
                                        <?php
                                        $stmt = $conn->prepare("SELECT * from $table_disciplines ORDER BY Id");
                                        $stmt->execute();
                                        $result_names = $stmt->get_result();
                                        while ($line = $result_names->fetch_array()) {
                                            echo "<option value=" . $line['Name'] . ">" . $line['Value'] . "</option>";
                                        }
                                        $stmt->close();
                                        ?>
                                    </select>
                                    <div class="invalid-feedback">Nevyplnili jste disciplínu</div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 <?= hidden($match_data['Zavod_cislo_zbrane'] == 0); ?>">
                                    <label class="form-label mt-3">Číslo zbraně</label>
                                    <input class="form-control" type="text" name="CZ" placeholder=""
                                        onfocus="this.placeholder = ''" onblur="this.placeholder = ''">
                                </div>

                                <div class="col-md-8 <?= hidden($match_data['Zavod_nazev_zbrane'] == 0); ?>">
                                    <label class="form-label mt-3">Název zbraně</label>
                                    <input class="form-control" type="text" name="NZ" placeholder=""
                                        onfocus="this.placeholder = ''" onblur="this.placeholder = ''">
                                </div>

                            </div>
                        </fieldset>
                    </div>

                    <div class="row m-1">
                        <fieldset class="border p-4 my-3 rounded">
                            <legend class="float-none w-auto px-2 h6">Ostatní</legend>
                            <div class="row">
                                <div class="row ">
                                    <div class="col-md-12 my-2">Statut závodníka</div>
                                    <div class="col-md-6">
                                        <select class="form-select" name=Staff>
                                            <option value="PAY">Platící závodník</option>
                                            <option value="VIP">VIP</option>
                                            <option value="RO">rozhodčí</option>
                                            <option value="POM">pomocník</option>
                                        </select>
                                    </div>
                                    <div class="col-md-6 mt-6 <?= hidden($match_data['Payment_before'] == 0); ?>">
                                        <label class="form-check-label" for="ZaplatiNaMiste">
                                            <input class="form-check-input" type="checkbox" id="ZaplatiNaMiste" name="ZaplatiNaMiste">
                                            Zaplatí na místě
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <label for="Poznamka" class="form-label mt-3">Poznámka</label>
                                    <textarea class="form-control" type="text" name="Poznamka" id="Poznamka"
                                        placeholder="Poznámka" onfocus="this.placeholder = ''"
                                        onblur="this.placeholder = 'Poznámka'" value=""></textarea>
                                </div>
                            </div>
                        </fieldset>
                    </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="submit" name="new_shooter" class="btn btn-primary">Přidat závodníka</button>
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close"
                    onclick="window.location.href = 'index.php';">Zavřít bez uložení</button>
            </div>
            </form>
        </div>
    </div>
</div>