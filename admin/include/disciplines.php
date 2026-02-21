<div class="modal fade" id="manage_disciplines" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-notify modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-center">
                <h4 class="modal-title text-white w-100 fw-bold">Správa disciplín</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered bg-white">
                        <thead>
                            <tr>
                                <th style="width:90px;">Zkratka</th>
                                <th>Název</th>
                                <th>Popis</th>
                                <th style="width:100px;" colspan="3" class="text-center">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $query = "SELECT * from $table_disciplines ORDER BY Id";
                            $result = mysqli_query($conn, $query);
                            while ($line = mysqli_fetch_array($result)) {
                            ?>
                                <tr>
                                    <td class="editable" data-table="<?= $table_disciplines ?>" data-field="Name" data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Name']) ?></td>
                                    <td style="white-space:nowrap;" class="editable" data-table="<?= $table_disciplines ?>" data-field="Value" data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Value']) ?></td>
                                    <td class="editable" data-table="<?= $table_disciplines ?>" data-field="Description" data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Description']) ?></td>
                                    <td class="save-cell" data-id="<?= $line['Id'] ?>">
                                        <button class="btn btn-sm btn-success me-1" disabled><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-secondary" disabled><i class="bi bi-x-lg"></i></button>
                                        <form action="./save.php" method="POST" class="d-inline"> <input type="hidden" name="delete_discipline" value="1"> <input type="hidden" name="name" value="<?= $line['Name'] ?>"> <button type="submit" class="btn btn-sm btn-danger ms-2"> <i class="bi bi-trash3 mx-2"></i> </button> </form>
                                    </td>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                            <form class="needs-validation" method="post" action="./save.php" validate>
                                <tr>
                                    <td><input class="form-control" type="text" name="Name" id="Name" placeholder="MaO-OPAK,..." onfocus="this.placeholder = ''" onblur="MaO-OPAK..." onkeypress="return avoidspace(event)" required></td>
                                    <td><input class="form-control" type="text" name="Value" id="Value" placeholder="MaO opakovací,..." onfocus="this.placeholder = ''" onblur="MaO opakovací,..." required></td>
                                    <td><input class="form-control" type="text" name="Description" id="Description" placeholder="stručný popis disciplíny na 1 řádek (nepovinné)" onfocus="this.placeholder = ''" onblur="stručný popis disciplíny na 1 řádek (nepovinné)"></td>
                                    <td class="text-center">
                                        <button type="submit" name="new_discipline" class="btn btn-sm btn-primary px-4 py-2"><i class="bi bi-plus-circle me-1"></i>Přidat</button>
                                    </td>
                                </tr>
                            </form>
                        </tbody>
                    </table>
                </div>
                <div id="accordion" class="col-md-12 mt-3">
                    <div class="card">
                        <a class="collapsed card-link" data-bs-toggle="collapse" href="#collapse">
                            <div class="card-header fw-bolder ">Seznam všech disciplín</div>
                        </a>
                        <div id="collapse" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-8">
                                        <dl class="row  text-start">
                                            <dt class="col-5 text-end pe-0">MaO-OPAK</dt>
                                            <dd class="col-7 ps-2">MaO opakovací</dd>
                                            <dt class="col-5 text-end pe-0">MAO-OPN</dt>
                                            <dd class="col-7 ps-2">MaO OPEN</dd>
                                            <dt class="col-5 text-end pe-0">MaO-STD</dt>
                                            <dd class="col-7 ps-2">MaO STANDARD</dd>
                                            <dt class="col-5 text-end pe-0">MaO-VZD</dt>
                                            <dd class="col-7 ps-2">Vzduchovka</dd>
                                            <dt class="col-5 text-end pe-0">VeO</dt>
                                            <dd class="col-7 ps-2">Puška odstřelovací (VeO)</dd>

                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close">Zavřít</button>
            </div>
        </div>
    </div>
</div>