<?php
function WarningModal(
    string $Header = '',
    string $CloseHref = '',
    string $Message = '',
    string $Info = '',
    string $FooterButtons = ''
): void {
    $WarnHeader = htmlspecialchars($Header, ENT_QUOTES, 'UTF-8');
    $WarnCloseHref = htmlspecialchars($CloseHref, ENT_QUOTES, 'UTF-8');
    $WarnMessage = $Message;
    $WarnInfo = $Info;
    $WarnFooterButtons = $FooterButtons;

    echo " 
<div class='text-center'>
	<img src='./images/bkg_ssapt.png'>
</div>
<div id='myModal' class='row modal fade' tabindex='-1'>
	<div class='modal-dialog'>
		<div class='modal-content'>
			<div class='modal-header bg-danger text-center'>
				<h4 class='modal-title text-white w-100 fw-bold py-2'>$WarnHeader</h4> <br>
				<button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close' onclick=\"window.location.href = '$WarnCloseHref';\"></button>
			</div>
			<div class='modal-body text-center'>
				<div class='fw-bolder text-danger'>
					$WarnMessage
				</div>
				<div class='alert alert-info fw-normal text-center m-3' role='alert'>
					$WarnInfo
				</div>
			</div>
			<div class='modal-footer border-top-0'>
				$WarnFooterButtons
			</div>
		</div>
    </div>
</div>
 ";
}
?>
<script type='text/javascript'>
    $(document).ready(function() {
        $('#myModal').modal('show');
    });
</script>