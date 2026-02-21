// DATA TABLE
$(document).ready(function () {
    $('[data-bs-toggle="popover"]').popover();
});

$(document).ready(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();
});

function ToggleFilter() {
    var elements = document.getElementsByClassName("dtsb-searchBuilder");

    for (var i = 0; i < elements.length; i++) {
        var x = elements[i];
        if (x.style.display === "block") {
            x.style.display = "none";
        } else {
            x.style.display = "block";
        }
    }
}

// Uploader
$(function () {
    $(document).on('click', '.btn-add', function (e) {
        e.preventDefault();
        var controlForm = $('.controls:first'),
            currentEntry = $(this).parents('.entry:first'),
            newEntry = $(currentEntry.clone()).appendTo(controlForm);
        newEntry.find('input').val('');
        controlForm.find('.entry:not(:last) .btn-add')
            .removeClass('btn-add').addClass('btn-remove')
            .removeClass('btn-success').addClass('btn-danger')
            .html('<span class="fa fa-trash"> </span>');
    }).on('click', '.btn-remove', function (e) {
        $(this).parents('.entry:first').remove();
        e.preventDefault();
        return false;
    });
});

// znovuotevření modalu po editaci
// uživatelé
$(document).ready(function () {
    if (window.location.search.indexOf("users") > -1) {
        $("#manage_users").modal("show");
    }

    $("#manage_users").on("hidden.bs.modal", function (e) {
        if (shouldReopenModal()) {
            $("#manage_users").modal("show");
        }
    });

    function shouldReopenModal() {
        return false;
    }
});
$(document).ready(function () {
    if (window.location.search.indexOf("users") > -1) {

        window.history.replaceState(null, null, window.location.pathname);
    }
});


// disciplíny
$(document).ready(function () {
    if (window.location.search.indexOf('disciplines') > -1) {
        $('#manage_disciplines').modal('show');
    }

    $('#manage_disciplines').on('hidden.bs.modal', function (e) {
        if (shouldReopenModal()) {
            $('#manage_disciplines').modal('show');
        }
    });

    function shouldReopenModal() {
        return false;
    }
});
$(document).ready(function () {
    if (window.location.search.indexOf('disciplines') > -1) {

        window.history.replaceState(null, null, window.location.pathname);
    }
});

// startovné
$(document).ready(function () {
ow.location.search.indexOf('fee') > -1) {
        $('#manage_fee').modal('show');
    }

    $('#manage_fee').on('hidden.bs.modal', function (e) {
        if (shouldReopenModal()) {
            $('#manage_fee').modal('show');
        }
    });

    function shouldReopenModal() {
        return false;
    }
});
$(document).ready(function () {
    if (window.location.search.indexOf('fee') > -1) {
        window.history.replaceState(null, null, window.location.pathname);
    }
});

// modal a spinner
$(document).ready(function () {
    $('#myModal').modal('show');

    $('form').on('submit', function () {
        $('#spinner').show();
        //      $('.modal-footer button').prop('disabled', true); // deaktivace tlačítek
    });
});


// inline editovatelné formulare
document.querySelectorAll(".editable").forEach((cell) => {
    cell.addEventListener("click", function () {
        const currentValue = this.textContent.trim();
        const field = this.dataset.field;
        const id = this.dataset.id;
        const table = this.dataset.table;

        if (cell.querySelector("input") || cell.querySelector("select")) return;

        let editor;

        if (field === "role") {
            editor = document.createElement("select");
            editor.className = "form-select form-select-sm";

            ["admin", "editor", "viewer"].forEach((role) => {
                const option = document.createElement("option");
                option.value = role;
                option.textContent = role;
                if (role === currentValue) option.selected = true;
                editor.appendChild(option);
            });
        } else {
            editor = document.createElement("input");
            editor.type = "text";
            editor.value = currentValue;
            editor.className = "form-control form-control-sm";
        }

        cell.textContent = "";
        cell.appendChild(editor);
        editor.focus();

        const row = cell.closest("tr");
        row.classList.add("table-warning");

        const checkBtn = row.querySelector(".bi-check-lg")?.parentElement;
        const cancelBtn = row.querySelector(".bi-x-lg")?.parentElement;

        if (checkBtn && cancelBtn) {
            checkBtn.disabled = false;
            cancelBtn.disabled = false;

            checkBtn.onclick = () => {
                const rawValue = editor.value;
                const normalizedValue = normalizeInput(rawValue, field);

                fetch("./save.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/x-www-form-urlencoded" },
                    body: `update=1&table=${table}&id=${id}&field=${field}&value=${encodeURIComponent(normalizedValue)}`
                }).then(() => {
                    cell.textContent = normalizedValue;
                    row.classList.remove("table-warning");
                    checkBtn.disabled = true;
                    cancelBtn.disabled = true;
                });
            };

            cancelBtn.onclick = () => {
                cell.textContent = currentValue;
                row.classList.remove("table-warning");
                checkBtn.disabled = true;
                cancelBtn.disabled = true;
            };
        }
    });
});

// aktivace bootstrap popover s HTML
document.addEventListener("DOMContentLoaded", function () {
    const popoverTrigger = document.getElementById("userInfoBtn");
    const popover = new bootstrap.Popover(popoverTrigger);
});

