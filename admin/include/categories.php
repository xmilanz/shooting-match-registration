<div class="modal fade" id="manage_categories" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-center">
                <h4 class="modal-title text-white w-100 fw-bold">Správa kategorií</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered bg-white">
                        <thead>
                            <tr>
                                <th style="width:90px; vertical-align:top;">Zkratka</th>
                                <th>Název</th>
                                <th style="width:150px; vertical-align:top" colspan="3" class="text-center">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $stmt = $conn->prepare("SELECT * from $table_categories ORDER BY Id");
                            $stmt->execute();
                            $result_names = $stmt->get_result();
                            while ($line = $result_names->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td class="editable" data-table="<?= $table_categories ?>" data-field="Name" data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Name']) ?></td>
                                    <td class="editable" data-table="<?= $table_categories ?>" data-field="Value" data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Value']) ?></td>
                                    <td class="save-cell" data-id="<?= $line['Id'] ?>">
                                        <button class="btn btn-sm btn-success me-1" disabled><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-secondary" disabled><i class="bi bi-x-lg"></i></button>
                                        <form action="./save.php" method="POST" class="d-inline">
                                            <input type="hidden" name="action" value="category_delete">
                                            <input type="hidden" name="name" value="<?= $line['Name']; ?>">
                                            <button type="submit" class="btn btn-sm btn-danger ms-2"> <i class="bi bi-trash3 mx-2"></i></button>
                                        </form>
                                    </td>
                                </tr>

                            <?php
                                $i++;
                            }
                            ?>
                            <form class="needs-validation" method="post" action="./save.php" validate>
                                <input type="hidden" name="action" value="category_new">
                                <tr>
                                    <td><input class="form-control" type="text" name="Name" id="Name" placeholder="SEN" onfocus="this.placeholder = ''" onblur="this.placeholder = 'SEN'" onkeypress="return avoidspace(event)" required></td>
                                    <td><input class="form-control" type="text" name="Value" id="Value" placeholder="Senior" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Senior'" required></td>
                                    </td>
                                    <td class="text-center">
                                        <button type="submit" class="btn btn-sm btn-primary px-5 py-2"><i class="bi bi-plus-circle me-1"></i>Přidat</button>
                                    </td>
                                </tr>
                            </form>
                        </tbody>

                    </table>
                </div>
                </form>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href = 'index.php';">Zavřít</button>
            </div>
        </div>
    </div>
</div>