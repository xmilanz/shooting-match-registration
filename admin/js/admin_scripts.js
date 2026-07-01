/* ============================================================
    DATATABLE
============================================================ */
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

/* ============================================================
    REWOPEN MODALS
============================================================ */

// users
$(document).ready(function () {
  // Check if URL contains the reopen_modal parameter
  if (window.location.search.indexOf("users") > -1) {
    $("#manage_users").modal("show");
  }

  $("#manage_users").on("hidden.bs.modal", function (e) {
    if (shouldReopenModal()) {
      $("#manage_users").modal("show");
    }
  });

  function shouldReopenModal() {
    return false; // This condition can remain as false for now
  }
});
$(document).ready(function () {
  if (window.location.search.indexOf("users") > -1) {
    // Remove the parameter from URL
    window.history.replaceState(null, null, window.location.pathname);
  }
});

// categories
$(document).ready(function () {
  // Check if URL contains the reopen_modal parameter
  if (window.location.search.indexOf("categories") > -1) {
    $("#manage_categories").modal("show");
  }

  $("#manage_categories").on("hidden.bs.modal", function (e) {
    if (shouldReopenModal()) {
      $("#manage_categories").modal("show");
    }
  });

  function shouldReopenModal() {
    return false; // This condition can remain as false for now
  }
});
$(document).ready(function () {
  if (window.location.search.indexOf("categories") > -1) {
    // Remove the parameter from URL
    window.history.replaceState(null, null, window.location.pathname);
  }
});

// disciplines
$(document).ready(function () {
  // Check if URL contains the reopen_modal parameter
  if (window.location.search.indexOf("disciplines") > -1) {
    $("#manage_disciplines").modal("show");
  }

  $("#manage_disciplines").on("hidden.bs.modal", function (e) {
    if (shouldReopenModal()) {
      $("#manage_disciplines").modal("show");
    }
  });

  function shouldReopenModal() {
    return false; // This condition can remain as false for now
  }
});
$(document).ready(function () {
  if (window.location.search.indexOf("disciplines") > -1) {
    // Remove the parameter from URL
    window.history.replaceState(null, null, window.location.pathname);
  }
});

// fee
$(document).ready(function () {
  // Check if URL contains the reopen_modal parameter
  if (window.location.search.indexOf("fee") > -1) {
    $("#manage_fee").modal("show");
  }

  $("#manage_fee").on("hidden.bs.modal", function (e) {
    if (shouldReopenModal()) {
      $("#manage_fee").modal("show");
    }
  });

  function shouldReopenModal() {
    return false; // This condition can remain as false for now
  }
});
$(document).ready(function () {
  if (window.location.search.indexOf("fee") > -1) {
    // Remove the parameter from URL
    window.history.replaceState(null, null, window.location.pathname);
  }
});

// open modal with spinner
$(document).ready(function () {
  $("#myModal").modal("show");

  $("form").on("submit", function () {
    $("#spinner").show();
    //      $('.modal-footer button').prop('disabled', true); // deaktivace tlačítek
  });
});

/* ============================================================
    INLINE EDITOVATELNÉ FORMULÁŘE
============================================================ */
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
          body: `action=inline_edit&table=${table}&id=${id}&field=${field}&value=${encodeURIComponent(normalizedValue)}`,
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

/* ============================================================
    AKTIVACE POPOVER PRO INFORMACE O UŽIVATELI
============================================================ */

document.addEventListener("DOMContentLoaded", function () {
  const popoverTrigger = document.getElementById("userInfoBtn");
  const popover = new bootstrap.Popover(popoverTrigger);
});

/* ============================================================
    VYBER ZAVODU V MENU - ODESILANI POSTU S ID ZAVODU
============================================================ */

document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll(".select-race").forEach(function (item) {
    item.addEventListener("click", function (e) {
      e.preventDefault();
      const raceId = this.dataset.raceId;

      // vložit ID závodu do hidden inputu
      document.getElementById("raceInput").value = raceId;

      // odeslat POST
      document.getElementById("raceForm").submit();
    });
  });
});
