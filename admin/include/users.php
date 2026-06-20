<div class="modal fade" id="manage_users" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-notify modal-warning" role="document">
        <div class="modal-content">
            <div class="modal-header bg-secondary text-center">
                <h4 class="modal-title text-white w-100 fw-bold">Správa uživatelů</h4>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="col-md-12">
                    <table class="table table-striped table-bordered bg-white">
                        <thead>
                            <tr>
                                <th style="width:120px;">Uživatel</th>
                                <th colspan="2" style="width:140px;" class="text-center">Heslo (vynucená změna)</th>
                                <th>E-mail</th>
                                <th style="width:120px;">Jméno</th>
                                <th style="width:120px;">Příjmení</th>
                                <th style="width:90px;" class="text-center">Role</th>
                                <th style="width:120px;" class="text-center">Pořadatel</th>
                                <th colspan="4" class="text-center">Akce</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $stmt = $conn->prepare("SELECT id, username, email, lastname, firstname, role, organizer,force_password_change FROM $table_admins ORDER BY id");
                            $stmt->execute();
                            $result_names = $stmt->get_result();
                            while ($line = $result_names->fetch_assoc()) {
                            ?>
                                <tr>
                                    <td>
                                        <?= htmlspecialchars($line['username']) ?>
                                    </td>
                                    <td style="width:140px;"
                                        class="editable"
                                        data-table="<?= $table_admins ?>"
                                        data-field="password" data-id="<?= $line['id'] ?>">
                                    </td>
                                    <td
                                        class="editable-toggle"
                                        data-table="<?= $table_admins ?>"
                                        data-field="force_password_change"
                                        data-id="<?= $line['id'] ?>">
                                        <div class="form-check form-switch m-0 d-flex justify-content-center">
                                            <input
                                                class="form-check-input force-password-change-switch"
                                                type="checkbox"
                                                role="switch"
                                                <?= (int)$line['force_password_change'] === 1 ? 'checked' : '' ?>>
                                        </div>
                                    </td>
                                    <td class="editable"
                                        data-table="<?= $table_admins ?>"
                                        data-field="email" data-id="<?= $line['id'] ?>"><?= htmlspecialchars($line['email']) ?>
                                    </td>
                                    <td class="editable"
                                        data-table="<?= $table_admins ?>"
                                        data-field="firstname" data-id="<?= $line['id'] ?>"><?= htmlspecialchars($line['firstname']) ?>
                                    </td>
                                    <td class="editable"
                                        data-table="<?= $table_admins ?>"
                                        data-field="lastname" data-id="<?= $line['id'] ?>"><?= htmlspecialchars($line['lastname']) ?>
                                    </td>
                                    <td class="editable text-center"
                                        data-table="<?= $table_admins ?>"
                                        data-field="role"
                                        data-id="<?= $line['id'] ?>"><?= htmlspecialchars($line['role']) ?>
                                    </td>
                                    <td class="editable text-center"
                                        data-table="<?= $table_admins ?>"
                                        data-field="organizer"
                                        data-id="<?= $line['id'] ?>"><?= htmlspecialchars($line['organizer']) ?>
                                    </td>
                                    <td class="save-cell"
                                        data-id="<?= $line['id'] ?>">
                                        <button class="btn btn-sm btn-success me-1" disabled><i class="bi bi-check-lg"></i></button>
                                        <button class="btn btn-sm btn-secondary" disabled><i class="bi bi-x-lg"></i></button>
                                        <form action="./save.php" method="POST" class="d-inline">
                                            <input type='hidden' name='action' value='user_delete'>
                                            <input type="hidden" name="username" value="<?= $line['username'] ?>">
                                            <button type="submit" class="btn btn-sm btn-danger ms-2"> <i class="bi bi-trash3 mx-2"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php
                                $i++;
                            }
                            ?>
                            <form class="needs-validation" method="post" action="./save.php" onsubmit="return validatePassword()" validate>
                                <input type='hidden' name='action' value='user_new'>
                                <tr>
                                    <td><input class="form-control" type="text" name="Username" id="Username" placeholder="jan.novak" onfocus="this.placeholder = ''" onblur="this.placeholder = 'jan.novak';replaceChars()" required></td>
                                    <td colspan="2"><input class="form-control" type="password" name="Heslo" id="Heslo" class="form-control" required></td>
                                    <td><input class="form-control" type="email" id="Mail" name="Mail" onfocus="this.placeholder = ''" onblur="this.placeholder='novak@mujemail.cz';replaceChars()" placeholder="novak@mujemail.cz" value="" required></td>
                                    <td><input class="form-control" type="text" name="Jmeno" id="Jmeno" placeholder="Jan" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Jan';replaceChars()" required></td>
                                    <td><input class="form-control" type="text" name="Prijmeni" id="Prijmeni" placeholder="Novák" onfocus="this.placeholder = ''" onblur="this.placeholder = 'Novák';replaceChars()" required></td>
                                    <td>
                                        <select name="Role" id="Role" class="form-select" required>
                                            <option value="admin">admin</option>
                                            <option value="editor">editor</option>
                                            <option value="viewer">viewer</option>
                                        </select>
                                    </td>
                                    <td>
                                        <select name="Organizer" id="Organizer" class="form-select" required>
                                            <option value="all">all</option>
                                            <option value="prachatice">prachatice</option>
                                        </select>
                                    </td>
                                    <td colspan="3" class="text-center">
                                        <button type="submit" name="new_user" class="btn btn-sm btn-primary px-4 py-2"><i class="bi bi-plus-circle me-1"></i>Přidat</button>
                                    </td>
                                </tr>
                            </form>
                        </tbody>
                    </table>
                </div>
                <div id="accordion" class="col-md-12 mt-3">
                    <div class="card">
                        <a class="collapsed card-link" data-bs-toggle="collapse" href="#collapse">
                            <div class="card-header fw-bolder ">Seznam rolí (oprávnění)</div>
                        </a>
                        <div id="collapse" class="collapse" data-parent="#accordion">
                            <div class="card-body">
                                <div class="col-md-12">
                                    <dl class="row  text-start">
                                        <dt class="col-1 text-end pe-0">admin</dt>
                                        <dd class="col-11 ps-3"><?= $admin_roles['admin'] ?></dd>
                                        <dt class="col-1 text-end pe-0">editor</dt>
                                        <dd class="col-11 ps-3"><?= $admin_roles['editor'] ?></dd>
                                        <dt class="col-1 text-end pe-0">viewer</dt>
                                        <dd class="col-11 ps-3"><?= $admin_roles['viewer'] ?></dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0">
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href = 'index.php';">Zavřít</button>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll(".editable-toggle input[type='checkbox']").forEach((checkbox) => {
        checkbox.addEventListener("change", function() {
            const cell = this.closest(".editable-toggle");
            const table = cell.dataset.table;
            const field = cell.dataset.field;
            const id = cell.dataset.id;
            const value = this.checked ? 1 : 0;

            fetch("./save.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `action=inline_edit&table=${encodeURIComponent(table)}&id=${encodeURIComponent(id)}&field=${encodeURIComponent(field)}&value=${encodeURIComponent(value)}`
            }).then(() => {
                const row = cell.closest("tr");
                row?.classList.add("table-warning");

                setTimeout(() => {
                    row?.classList.remove("table-warning");
                }, 800);
            });
        });
    });
</script>

<script>
    document.querySelectorAll(".editable-toggle input").forEach((el) => {
        el.addEventListener("change", function() {
            const td = this.closest("td");
            const value = this.checked ? 1 : 0;

            fetch("./save.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/x-www-form-urlencoded"
                },
                body: `action=inline_edit&table=${td.dataset.table}&id=${td.dataset.id}&field=${td.dataset.field}&value=${value}`
            });
        });
    });
</script>