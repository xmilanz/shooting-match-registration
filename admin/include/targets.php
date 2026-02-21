<div class="modal fade" id="upload_targets" tabindex="-1" role="dialog" data-bs-backdrop="static" aria-labelledby="myModalLabel"  aria-hidden="true">
   <div class="modal-dialog modal-notify modal-warning" role="document">
      <div class="modal-content">
         <div class="modal-header bg-success text-center">
            <h4 class="modal-title text-white w-100 fw-bold">Nahrání obrázků situací</h4>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
         </div>
         <div class="modal-body">
            <div class="row">
               <div class="col-lg-12">
                  <div class="card">
                     <div class="card-body card-block">
                        <form action="#" method="post" enctype="multipart/form-data" class="form-horizontal">
                           <div class="row mb-3">
                              <div class="col-12 col-md-12">
                                 <div class="control-group" id="files">
                                    <label class="control-label" for="files">
                                       <span class='text-primary'><i class='far fa-info-circle pe-2' style='font-size:16px'></i>Soubor musí splňovat tyto parametry:
                                       <ul>
                                          <li>název: target.png, target2.png,...</li>
                                          <li>velikost < 300 KB</li>
                                          <li>přípona: png</li>
                                       </ul>
                                    </label>
                                    <div class="controls">
                                       <div class="entry input-group upload-input-group">  
                                          <input class="form-control" name="files[]" type="file">
                                          <button class="btn btn-upload btn-success btn-add" type="button">  
                                          <i class="fa fa-plus"> </i>  
                                          </button>  
                                       </div>
                                    </div>
                                    <button class="btn btn-primary mt-3 float-end" type="submit" name="SubmitButton">Nahrát</button>  
                                 </div>
                              </div>
                           </div>
                        </form>
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
<?php
   if (isset($_POST['SubmitButton'])) {
       $targetDir = "../targets/";
       $allowedTypes = ['png'];
       $maxFileSize = 300 * 1024;

       if (!is_dir($targetDir)) {
           mkdir($targetDir, 0755, true);
       }

       $statusMessages = [];

       if (!empty($_FILES['files']['name'][0])) {
           $uploadedFiles = $_FILES['files'];

           foreach ($uploadedFiles['tmp_name'] as $key => $tmpName) {
               $fileName = basename(strtolower($uploadedFiles['name'][$key]));
               $fileSize = $uploadedFiles['size'][$key];
               $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
               $targetFilePath = $targetDir . $fileName;

               if (!in_array($fileExt, $allowedTypes)) {
                   $statusMessages[] = "<span class='text-danger'>Soubor \"$fileName\" má nepovolený typ ($fileExt). Použijte formát PNG.</span>";
                   continue;
               }

               if ($fileSize > $maxFileSize) {
                   $statusMessages[] = "<span class='text-danger'>Soubor \"$fileName\" překračuje maximální velikost (300 KB).</span>";
                   continue;
               }

   			if (!preg_match('/^target([1-9]|[1-9][0-9])\.png$/', $fileName)) {
   				$statusMessages[] = "<span class='text-danger'>Soubor \"$fileName\" nemá správný název. Použijte názvy target1.png, target2.png,...</span>";
   				continue;
   			}

               if (move_uploaded_file($tmpName, $targetFilePath)) {
                   $statusMessages[] = "<span class='text-success'>Soubor \"$fileName\" byl úspěšně nahrán.</span>";

               } else {
                   $statusMessages[] = "<span class='text-danger'>Při nahrávání souboru \"$fileName\" došlo k chybě. Zkontrolujte, zda vyhovuje požadavkům nebo kontaktujte <a href='mailto:$vyvojar?subject=IPSC registrace - chyba uploadu'>správce aplikace</a>.</span>";
               }
           }
       } else {
           $statusMessages[] = "<span class='text-danger'>Nevybrali jste žádný soubor.</span>";
       }
   }

   if (!empty($statusMessages)) {
       echo "
       <div class='modal fade' id='uploadStatusModal' tabindex='-1' role='dialog' aria-labelledby='uploadStatusModalLabel' aria-hidden='true'>
         <div class='modal-dialog' role='document'>
           <div class='modal-content'>
             <div class='modal-header bg-info text-white'>
               <h5 class='modal-title' id='uploadStatusModalLabel'>Stav nahrávání</h5>
               <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal' aria-label='Zavřít'></button>
             </div>
             <div class='modal-body'>
            ";
       foreach ($statusMessages as $message) {
           echo "$message<br>";
       }
       echo "
             </div>
             <div class='modal-footer'>
               <button type=\"button\" class=\"btn btn-outline-dark\" onclick=\"window.location.href = 'index.php';\">Zavřít</button>
             </div>
           </div>
         </div>
       </div>";
   }
   ?>
<script>
   $(document).ready(function() {
       $('#uploadStatusModal').modal('show'); // Otevře modal po nahrání
   });
</script>