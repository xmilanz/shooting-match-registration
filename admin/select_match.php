<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';
require_once __DIR__ . '/config/data.php';
require_admin(); 

// CSRF check
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
    http_response_code(403);
    exit('neplatný CSRF token');
}

$zavodId = $_POST['zavod_id'] ?? '';

$organizer = $_SESSION['organizer'] ?? ''; 
$availableRaces = getRacesForOrganizer($conn, $organizer, $zavody_prefix);

if (!array_key_exists($zavodId, $availableRaces)) {
    $_SESSION['toast'] = [
        'type' => 'danger',
        'message' => 'Vybraný závod není dostupný.'
    ];
    header('Location: /');
    exit;
}

$_SESSION['zavod_id'] = $zavodId;
$_SESSION['zavod_name'] = $availableRaces[$zavodId];

header('Location: /');
exit;
