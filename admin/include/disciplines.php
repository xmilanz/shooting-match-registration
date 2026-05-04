<div class="modal fade" id="manage_disciplines" tabindex="-1" role="dialog" data-bs-backdrop="static"
    aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog <?= ($match_data['Zavod_registrace_smeny']) ? "modal-xl" : "modal-lg"; ?>  modal-notify modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-center">
                <h4 class="modal-title text-white w-100 fw-bold">Správa
                    disciplín<span class="<?= $smenyRegistraceClass ?>"><?= empty($match_data['Pocet_smen']) ? '' : " (počet směn $match_data[Pocet_smen])" ?></span>
                </h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                    aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <div class="<?= $smenyRegistraceClass ?> alert alert-info small mt-0" role="alert">
                        Při registraci do směn slouží pole <strong>Směna (od)</strong> a <strong>Směna (do)</strong> k
                        vymezení směn, ve kterých se budou střílet jednotlivé disciplíny. U ostatních způsobů registrace
                        (hromadná nebo jednotlivá) ponechte pole prázdná.<br><br>
                        PŘÍKLAD 1: vojenské pušky a karabiny spolu střílí ve směnách 1-4, u <strong>každé z
                            nich</strong> se uvede stejné rozmezí <strong>Směna (od) = 1 </strong> a <strong>Směna (do)
                            = 4</strong><br>
                        PŘÍKLAD 2: Bizon se bude střílet v 5 a 6 směně, uvede se <strong>Směna (od) = 5 </strong> a
                        <strong>Směna (do) = 6</strong>.
                    </div>

                    <table class="table table-striped table-bordered bg-white">
                        <thead>
                            <tr>
                                <th style="width:100px; vertical-align:top;">Zkratka</th>
                                <th style="width:150px; vertical-align:top;">Název</th>
                                <th style="vertical-align:top;">Popis</th>
                                <th class="<?= $smenyRegistraceClass ?>" style="width:60px; vertical-align:top; text-align:center;">Směna<br>(od)</th>
                                <th class="<?= $smenyRegistraceClass ?>" style="width:60px; vertical-align:top; text-align:center;">Směna<br>(do)</th>
                                <th style="width:100px; vertical-align:top" colspan="3" class="text-center">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $result = $conn->query("SELECT * FROM $table_disciplines ORDER BY Id");
                            while ($result && ($line = $result->fetch_assoc())) {
                            ?>
                                <tr>
                                    <td class="editable" data-table="<?= $table_disciplines ?>" data-field="Name"
                                        data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Name']) ?></td>
                                    <td style="white-space:nowrap;" class="editable" data-table="<?= $table_disciplines ?>"
                                        data-field="Value" data-id="<?= $line['Id'] ?>">
                                        <?= htmlspecialchars($line['Value']) ?>
                                    </td>
                                    <td class="editable" data-table="<?= $table_disciplines ?>" data-field="Description"
                                        data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Description']) ?></td>
                                    <td class="<?= $smenyRegistraceClass ?> editable text-center" data-table="<?= $table_disciplines ?>"
                                        data-field="Shift_from" data-id="<?= $line['Id'] ?>">
                                        <?= htmlspecialchars($line['Shift_from']) ?>
                                    </td>
                                    <td class="<?= $smenyRegistraceClass ?> editable text-center" data-table="<?= $table_disciplines ?>"
                                        data-field="Shift_to" data-id="<?= $line['Id'] ?>">
                                        <?= htmlspecialchars($line['Shift_to']) ?>
                                    </td>
                                    <td class="save-cell" data-id="<?= $line['Id'] ?>">
                                        <button class="btn btn-sm btn-success me-1" disabled><i
                                                class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-secondary" disabled><i
                                                class="bi bi-x-lg"></i></button>
                                        <form action="./save.php" method="POST" class="d-inline"> <input type="hidden"
                                                name="delete_discipline" value="1"> <input type="hidden" name="name"
                                                value="<?= $line['Name'] ?>"> <button type="submit"
                                                class="btn btn-sm btn-danger ms-2"> <i class="bi bi-trash3 mx-2"></i>
                                            </button> </form>
                                    </td>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                            <form class="needs-validation" method="post" action="./save.php" validate>
                                <tr>
                                    <td><input class="form-control" type="text" name="Name" id="Name" onkeypress="return avoidspace(event)" required></td>
                                    <td><input class="form-control" type="text" name="Value" id="Value" required></td>
                                    <td><input class="form-control" type="text" name="Description" id="Description"></td>
                                    <td class="<?= $smenyRegistraceClass ?>"><input class="form-control" type="text" name="Shift_from" id="Shift_from"></td>
                                    <td class="<?= $smenyRegistraceClass ?>"><input class="form-control" type="text" name="Shift_to" id="Shift_to"></td>
                                    <td class="text-center"><button type="submit" name="new_discipline" class="btn btn-sm btn-primary px-4 py-2"><i class="bi bi-plus-circle me-1"></i>Přidat</button></td>
                                </tr>
                            </form>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal"
                    aria-label="Close">Zavřít</button>
            </div>
        </div>
    </div>
</div>