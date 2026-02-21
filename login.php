<?php
include "header.php";
echo "
   <div class='text-center'>
   	<img src='./images/bkg_ssapt.png'>
   </div>

      <div id='myModal' class='row modal fade' tabindex='-1'>
   	<div class='modal-dialog'>
   	  <div class='modal-content'>
   		<div class='modal-header bg-danger text-center'>
   			<h4 class='modal-title text-white w-100 fw-bold py-2'>Přihlášení do administrace závodu</h4><br>
   			<button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close'></button>
   		</div>
   		<div class='modal-body text-center'>
        <div id='spinner' class='text-center w-100 mb-3' style='display:none;'>
           <div class='spinner-border text-danger' role='status'>
             <span class='visually-hidden'>Načítání...</span>
           </div>
           <p class='text-danger mt-2'>Přihlašování...</p>
        </div>
    	<form class='row needs-validation mb-0' method='post' action='./authenticate.php' >
   			<div class='col-md-2'></div>
   			 <div class='col-md-8'>
   				<div class='input-group mb-3'>
   					<div class='input-group-prepend'>
   						<span class='input-group-text' id='addon-wrapping'>Jméno</span>
   					</div>
   					<input type='text' class='form-control' id='username' name='username' required autofocus>
   				</div>
   				<div class='input-group mb-3'>
   					<span class='input-group-text' id='addon-wrapping'>&nbsp;Heslo&nbsp;</span>
   					<input type='password' class='form-control' id='password' name='password' required>
   				</div>
   			 </div>
   			<div class='modal-footer border-top-0 col-12'>
   				<button type='submit' class='btn btn-danger'>Přihlásit</button>
   				<button type='button' class='btn btn-outline-danger' onclick=\"window.location.href = 'index.php';\">Zrušit</button>
   			</div>
   		</form>
   		</div>
      </div>
    </div>
   </div>

<script type='text/javascript'>
  $(document).ready(function(){
    $('#myModal').modal('show');

    $('form').on('submit', function(){
      $('#spinner').show();
      $('.modal-footer button').prop('disabled', true); // deaktivace tlačítek
    });
  });
</script>
   ";

include "footer.php";