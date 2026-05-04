<?php
declare(strict_types=1);
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/config/data.php';

// vymazat session
session_unset();
session_destroy();

// smazat cookie explicitně pro doménu
setcookie(session_name(), '', time() - 3600, '/', '.strelniceprachatice.cz', true, true);

header('Location: ' . $reg_redirect_url);
exit;
?>