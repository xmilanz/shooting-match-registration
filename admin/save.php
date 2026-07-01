<link rel="stylesheet" type="text/css" href="/styles/style.css">
<link rel="stylesheet" href="https://pro.fontawesome.com/releases/v5.10.0/css/all.css" integrity="sha384-AYmEC3Yw5cVb3ZcuHtOA93w35dYTsvhLPVnYs9eStHfGJvOvKxVfELGroGkvsg+p" crossorigin="anonymous" />
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';
require_once __DIR__ . '/config/mail_texty.php';
require_admin();

$action = $_POST['action'] ?? '';

$handlers = [
    'match_config' => './handlers/match_config.php',
    'shooter_new' => './handlers/shooter_new.php',
    'shooter_edit' => './handlers/shooter_edit.php',
    'shooter_cancel' => './handlers/shooter_cancel.php',
    'shooter_delete' => './handlers/shooter_delete.php',
    'payment_save_bulk' => './handlers/payment_save_bulk.php',
    'payment_save_single' => './handlers/payment_save_single.php',
    'user_new' => './handlers/user_new.php',
    'user_delete' => './handlers/user_delete.php',
    'user_password_change' => './handlers/user_password_change.php',
    'category_new' => './handlers/category_new.php',
    'category_delete' => './handlers/category_delete.php',
    'discipline_new' => './handlers/discipline_new.php',
    'discipline_delete' => './handlers/discipline_delete.php',
    'fee_new' => './handlers/fee_new.php',
    'fee_delete' => './handlers/fee_delete.php',
    'inline_edit' => './handlers/inline_edit.php',
    'shooters_table_truncate' => './handlers/shooters_table_truncate.php'
];

if (isset($handlers[$action])) {
    require $handlers[$action];
} else {
    http_response_code(400);
    exit('Neznámá akce.');
}

$conn = new mysqli($db_host, $db_login, $db_pass, $db_dtb);
?>

<script type='text/javascript'>
    var myModal = new bootstrap.Modal(document.getElementById('myModal'));
    myModal.show();
</script>