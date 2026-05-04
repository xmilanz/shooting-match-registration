<?php
function WarningModal(
    string $Color = '',
    string $Header = '',
    string $CloseHref = '',
    string $Message = '',
    string $Info = '',
    $CancelLabel = 'Zrušit'
): void {
    $WarnColor = htmlspecialchars($Color, ENT_QUOTES, 'UTF-8');
    $WarnHeader = htmlspecialchars($Header, ENT_QUOTES, 'UTF-8');
    $WarnCloseHref = htmlspecialchars($CloseHref, ENT_QUOTES, 'UTF-8');
    $WarnMessage = $Message;
    $WarnInfo = $Info;

    echo "
   	<div id='myModal' class='row modal fade' tabindex='-1'>
   	    <div class='modal-dialog'>
   		    <div class='modal-content text-center'>
                <div class='modal-header bg-$WarnColor'>
                    <h4 class='modal-title text-white w-100 fw-bold py-2'>$WarnHeader</h4><br>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Close' onclick=\"window.location.href = '$WarnCloseHref';\"></button>
                </div>
                <div class='modal-body text-center pb-0'>
                    <div class='fw-bolder text-$WarnColor'>
                        $WarnMessage
                    </div>
                    <div class='alert alert-info fw-normal text-center my-3 mx-2' role='alert'>
                        $WarnInfo
                    </div>
                </div>
  		    <div class='modal-footer border-top-0'>
   			    <button type='button' class='btn btn-outline-dark' onclick=\"window.location.href = '$WarnCloseHref';\">$CancelLabel</button>
   		    </div>
   		</div>
   	</div>
";
}
?>

<script type='text/javascript'>
    var myModal = new bootstrap.Modal(document.getElementById('myModal'));
    myModal.show();
</script>