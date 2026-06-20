<div class="modal fade" id="password_change" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog d-flex justify-content-center">

        <div class="modal-content w-75">
            <div class="modal-header bg-secondary text-center">
                <h4 class="modal-title text-white w-100 fw-bold py-2">Změna hesla</h4>
                <br>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href = 'index.php';"></button>
            </div>
            <div class="modal-body">
                <form class="row" method="post" action="./save.php" novalidate>
                    <input type='hidden' name='action' value='user_password_change'>

                    <div class="form-outline mb-3">
                        <label class="form-label" for="password">Původní heslo</label>
                        <input type="password" id="password" name="password" class="form-control" />
                        <i class="bi bi-eye-slash toggle-password" data-target="password"></i>
                    </div>

                    <div class="form-outline">
                        <label class="form-label" for="password_new">Nové heslo</label>
                        <input type="password" id="password_new" name="password_new" class="form-control" />
                        <i class="bi bi-eye-slash toggle-password" data-target="password_new"></i>
                    </div>

                    <div class="form-outline mb-1">
                        <label class="form-label" for="password_new1">Zopakovat nové heslo</label>
                        <input type="password" id="password_new1" name="password_new1" class="form-control" />
                        <i class="bi bi-eye-slash toggle-password" data-target="password_new1"></i>
                    </div>

                    <div id="accordion" class="col-md-12 mt-1">
                        <div class="card">
                            <a class="collapsed card-link" data-bs-toggle="collapse" href="#collapse">
                                <div class="card-header">Požadavky na heslo</div>
                            </a>
                            <div id="collapse" class="collapse" data-parent="#accordion">
                                <div class="card-body">
                                    <div class="col-md-12">
                                        <ul>
                                            <li>délka 8 - 255 znaků</li>
                                            <li>alespoň jedno velké písmeno</li>
                                            <li>alespoň jeden speciální znak</li>
                                            <li>login nesmí být součástí hesla</li>
                                        </ui>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            </div>

            <div class="modal-footer border-top-0">
                <button type="submit" class="btn btn-secondary">Změnit heslo</button>
                <button type="button" class="btn btn-outline-dark" data-bs-dismiss="modal" aria-label="Close" onclick="window.location.href = 'index.php';">Zrušit</button>
            </div>
            </form>
        </div>
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


<script>
    document.querySelectorAll(".toggle-password").forEach(icon => {
        icon.addEventListener("click", function() {
            const input = document.getElementById(this.dataset.target);
            const type = input.getAttribute("type") === "password" ? "text" : "password";
            input.setAttribute("type", type);
            this.classList.toggle("bi-eye");
        });
    });
</script>