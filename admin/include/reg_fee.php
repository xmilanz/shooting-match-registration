<div class="modal fade" id="manage_fee" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-notify modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-center">
                <h4 class="modal-title text-white w-100 fw-bold">Správa startovného</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered bg-white">
                        <thead>
                            <tr>
                                <th>Počet disciplín</th>
                                <th>Startovné</th>
                                <th style="width:100px;" colspan="3" class="text-center">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $query = "SELECT * from $table_fee ORDER BY Count";
                            $result = mysqli_query($conn, $query);
                            while ($line = mysqli_fetch_array($result)) {
                            ?>
                                <tr>
                                    <td ><?= htmlspecialchars($line['Count']) ?></td>
                                    <td class="editable" data-table="<?= $table_fee ?>" data-field="Value" data-id="<?= $line['Id'] ?>"><?= htmlspecialchars($line['Value']) ?></td>
                                    <td class="save-cell" data-id="<?= $line['Id'] ?>">
                                        <button class="btn btn-sm btn-success me-1" disabled><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-secondary" disabled><i class="bi bi-x-lg"></i></button>
                                        <!--a class="btn btn-sm btn-danger ms-2" href="./save.php?delete_fee&count=<?= htmlspecialchars($line['Count'], ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-trash3 mx-2"></i></a></a-->
                                        <form action="./save.php" method="POST" class="d-inline"> <input type="hidden" name="delete_fee" value="1"> <input type="hidden" name="count" value="<?= $line['Count'] ?>"> <button type="submit" class="btn btn-sm btn-danger ms-2"> <i class="bi bi-trash3 mx-2"></i> </button> </form>
                                    </td>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                            <form class="needs-validation" method="post" action="./save.php" validate>
                                <tr>
                                    <td>
                                        <select name="Count" id="Count" class="form-select" required>
                                            <option value="1">1</option>
                                            <option value="2">2</option>
                                            <option value="3">3</option>
                                        </select>
                                    </td>
                                    <td><input class="form-control" type="text" name="Value" id="Value" placeholder="startovné" onfocus="this.placeholder = ''" onblur="startovné" required></td>
                                    <td class="text-center">
                                        <button type="submit" name="new_fee" class="btn btn-sm btn-primary px-4 py-2"><i class="bi bi-plus-circle me-1"></i>Přidat</button>
                                    </td>
                                </tr>
                            </form>
                        </tbody>
                    </table>
                </div>
                <div id="accordion" class="col-md-12 mt-3">
                    <div class="card">
                        <a class="collapsed card-link" data-bs-toggle="collapse" href="#collapse">
                            <div class="card-header fw-bolder ">Nápověda</div>
                        </a>
                        <div id="collapse" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-11">
                                        <dl class="row  text-start">
                                            <dd class="col-12 ps-3">- startovné za jednu disciplínu - 250 Kč</dd>
                                            <dd class="col-12 ps-3">- startovné za dvě disciplíny - 400 Kč (druhá disciplína je za 150 Kč)</dd>
                                            <dd class="col-12 ps-3">- startovné za každou další disciplínu - 200 Kč</dd>
                                            <dt class="col-12 pt-3 text-center text-danger">!!! POČET MUSÍ VŽDY BÝT 3 !!!</dt>
                                            <dd class="col-12 pt-1 text-center small">Pokud se za dvě a více disciplín platí stejně, pak použijte stejnou částku<br>(1 disciplína 200, dvě disciplíny 100, každá další disciplína 100)</dd>
                                            <dd class="col-12 pt-1 text-center small">Pokud je jen jedna částka, pak použijte pouze tu pro všechny položky<br>(1 disciplína 200, dvě disciplíny 200, každá další disciplína 200)</dd>
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