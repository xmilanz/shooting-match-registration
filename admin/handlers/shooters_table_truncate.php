<?php
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    exit('Nemáte oprávnění');
}

if (
    empty($_POST['csrf_token']) ||
    empty($_SESSION['csrf_token']) ||
    !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
) {
    exit('Neplatný CSRF token.');
}

$conn->query("TRUNCATE TABLE `$table`");
logAction("table truncate");
$_SESSION['toast'] = [
    'type' => 'danger',
    'message' => 'Tabulka závodníků byla úspěšně vyprázdněna. Závodníci se budou přidávat s číslem od 1 ',
    'duration' => 2500
];
header("Location: /");
exit;
