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


function avoidspace(event) {
    var k = event ? event.which : window.event.keyCode;
    if (k == 32) return false;
}


function replaceChars(index) {
	var jmenoInput = document.getElementById(`Jmeno${index}`);
	if (jmenoInput) {
		var inputJmeno = jmenoInput.value;
		var outputJmeno = inputJmeno.replace(/[^a-zA-ZáÁčČďĎéÉěĚíÍňŇóÓřŘšŠťŤúÚůŮýÝžŽ]/g, "");
		jmenoInput.value = outputJmeno;
	}

	var prijmeniInput = document.getElementById(`Prijmeni${index}`);
	if (prijmeniInput) {
		var inputPrijmeni = prijmeniInput.value;
		var outputPrijmeni = inputPrijmeni.replace(/[^a-zA-ZáÁčČďĎéÉěĚíÍňŇóÓřŘšŠťŤúÚůŮýÝžŽ0-9]/g, "");
		prijmeniInput.value = outputPrijmeni;
	}

	var emailInput = document.getElementById(`Email${index}`);
	if (emailInput) {
		var inputEmail = emailInput.value;
		var outputEmail = inputEmail.normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-zA-Z0-9@\.]/g, "");
		emailInput.value = outputEmail;
	}
}

// HROMADNA REGISTRACE 
document.getElementById('addDisc').addEventListener('click', () => {
    const container = document.getElementById('discRows');
    const row = container.querySelector('.disc-row');
    const clone = row.cloneNode(true);

    clone.querySelectorAll('select, textarea').forEach(el => {
        el.value = '';
        el.classList.remove('is-invalid', 'is-valid');
    });
    // vyčistit hodnoty v klonech
    clone.querySelectorAll('select, input, textarea').forEach(el => el.value = '');
    container.appendChild(clone);
    updateRemoveButtons();
});

document.getElementById('discRows').addEventListener('click', e => {
    if (e.target.closest('.remove-row')) {
        const rows = document.querySelectorAll('#discRows .disc-row');
        if (rows.length > 1) {
            e.target.closest('.disc-row').remove();
            updateRemoveButtons();
        }
    }
});

function updateRemoveButtons() {
    const rows = document.querySelectorAll('#discRows .disc-row');
    rows.forEach((row, index) => {
        const btn = row.querySelector('.remove-row');
        if (btn) {
            btn.style.display = (index === 0) ? 'none' : 'inline-block';
        }
    });
}

document.addEventListener("DOMContentLoaded", function () {
    const popovers = [];

    document.querySelectorAll('[data-bs-toggle="popover"]').forEach(function (el) {
        const popover = new bootstrap.Popover(el, {
            trigger: 'focus'
        });

        popovers.push(popover);

        el.addEventListener('show.bs.popover', function () {
            popovers.forEach(function (p) {
                if (p !== popover) {
                    p.hide();
                }
            });
        });
    });
});
