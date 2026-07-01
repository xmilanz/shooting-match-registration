<?php
/*
error_log('SESSION token: ' . ($_SESSION['token'] ?? 'NONE'));
error_log('POST token: '    . ($_POST['token']    ?? 'NONE'));
error_log('SESSION ID: '    . session_id());
*/
// --- kontrola CSRF tokenu ---

if (!isset($_POST['token'], $_SESSION['token']) || $_POST['token'] !== $_SESSION['token']) {
    http_response_code(403);
    include './components/modal-warning-extended.php';
    WarningModalExtended(
        "Neúspěšná registrace",
        "registrace.php",
        "<div class='col-12 fw-bolder text-danger'>Odeslání formuláře se nepodařilo dokončit.",
        "Může to být způsobeno obnovením stránky nebo příliš dlouhým čekáním.",
        "Vyplňte formulář znovu a odešlete jej jedním kliknutím na tlačítko Registrovat.",
        "<button type='button' class='btn btn-outline-danger' onclick=\"window.location.href = 'registrace.php';\">Zpět na registraci</button>",
        "$poradatel"
    );
    exit;
}
// token po použití zneplatníme
unset($_SESSION['token']);
// --- honeypot (robots) ---
if (!empty($_POST['gender'])) {
    exit('Detekován Spam.');
}
