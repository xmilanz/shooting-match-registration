<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';
require_once __DIR__ . '/config/mail_texty.php';
require_admin();


$stmt = $conn->prepare("
SELECT * FROM $table_matches
      WHERE Zavod_id = ?
   ");
$stmt->bind_param(
    "s",
    $table
);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
$match_data = mysqli_fetch_array($result);

$action = $_POST['action'] ?? '';

$handlers = [
    'regmail' => './handlers/regmail.php',
    'regmail_bulk' => './handlers/regmail_bulk.php',
    'payment_warn' => './handlers/payment_warn.php',
    'payment_bulk_warn' => './handlers/payment_bulk_warn.php'
];

if (isset($handlers[$action])) {
    require $handlers[$action];
} else {
    http_response_code(400);
    exit('Neznámá akce.');
}
