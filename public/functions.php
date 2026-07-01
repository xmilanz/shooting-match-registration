<?php
if ((include 'libs/PhpMailer/PHPMailerAutoload.php') === false) {
    if ((include '../libs/PhpMailer/PHPMailerAutoload.php') === false) {
    }
}

function email($from_text, $from, $to, $subject = '', $message = '', $headers = '')
{
    global $smtp_username, $smtp_password, $smtp_server;
    $message = nl2br($message);
    //Create a new PHPMailer instance
    $mail = new PHPMailer;
    //Tell PHPMailer to use SMTP
    $mail->isSMTP();
    $mail->setLanguage('cs');
    $mail->CharSet = 'UTF-8';
    //Enable SMTP debugging
    // 0 = off (for production use)
    // 1 = client messages
    // 2 = client and server messages
    $mail->SMTPDebug = 0;
    //Ask for HTML-friendly debug output
    $mail->Debugoutput = 'html';
    //Set the hostname of the mail server
    $mail->Host = $smtp_server;
    //Set the SMTP port number - likely to be 25, 465 or 587
    $mail->Port = 465;
    //Whether to use SMTP authentication
    $mail->SMTPSecure = 'ssl';
    // Enable TLS encryption, `ssl` also accepted
    $mail->SMTPAuth = true;
    //Username to use for SMTP authentication
    $mail->Username = $smtp_username;
    //Password to use for SMTP authentication
    $mail->Password = $smtp_password;
    //Set who the message is to be sent from
    $mail->setFrom($from, $from_text);
    //Set an alternative reply-to address
    //  $mail->addReplyTo("name@domain.tld"); funkční reply-to
    //Set who the message is to be sent to
    $mail->addAddress($to, $to);
    //Set the subject line
    $mail->Subject = $subject;
    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    $mail->msgHTML($message);
    //Replace the plain text body with one created manually
    //$mail->AltBody = $message;
    //Attach an image file
    //$mail->addAttachment('images/phpmailer_mini.png');
    //send the message, check for errors
    if (!$mail->send()) {
        echo "Mailer Error: " . $mail->ErrorInfo . "<hr/>";
        echo "smtp: " . $mail->Host . "<br/>";
        echo "login: " . $mail->Username . "<br/>";
        return false;
    } else {
        return true;
    }
}

// Normalizuje cislo ZP, cislo PZ, cislo zbrane (odstrani mezery, prevede na velka pismena, pridá mezery mezi prefix a číslo)
function normalizePrukaz($input)
{
    $input = preg_replace('/\s+/', '', $input);
    $input = strtoupper($input);
    if (preg_match('/^[A-Z]{2}\d+$/', $input)) {
        $prefix = substr($input, 0, 2);
        $number = substr($input, 2);
        return $prefix . ' ' . $number;
    }
    if (preg_match('/^\d+$/', $input)) {
        return $input;
    }
    return $input;
}

// Normalizuje a validuje textova pole (např. název zbraně, poznámka)
function normalizeText(?string $input, int $maxLength = 255): ?string
{
    $text = trim($input ?? '');
    $text = preg_replace('/[\x00-\x1F\x7F]/u', '', $text);

    if ($text === '') {
        return null;
    }

    if (mb_strlen($text, 'UTF-8') > $maxLength) {
        return null;
    }

    // povoleno: písmena, čísla, mezery, -, _, /
    if (!preg_match('/^[\p{L}\p{N}\s\/_-]+$/u', $text)) {
        return null;
    }

    return $text;
}

// Normalizuje retezec
function normalize(string $str): string
{
    static $trans = [
        'á' => 'a',
        'č' => 'c',
        'ď' => 'd',
        'é' => 'e',
        'ě' => 'e',
        'í' => 'i',
        'ň' => 'n',
        'ó' => 'o',
        'ř' => 'r',
        'š' => 's',
        'ť' => 't',
        'ú' => 'u',
        'ů' => 'u',
        'ý' => 'y',
        'ž' => 'z',
        'Á' => 'a',
        'Č' => 'c',
        'Ď' => 'd',
        'É' => 'e',
        'Ě' => 'e',
        'Í' => 'i',
        'Ň' => 'n',
        'Ó' => 'o',
        'Ř' => 'r',
        'Š' => 's',
        'Ť' => 't',
        'Ú' => 'u',
        'Ů' => 'u',
        'Ý' => 'y',
        'Ž' => 'z',
    ];

    $str = strtr($str, $trans);
    return strtolower(trim($str));
}

function getShooterData(mysqli $conn, string $table, int $shooterID, int $shooterKEY): ?array
{
    $stmt = $conn->prepare("SELECT * FROM $table WHERE Cislo = ? AND klic = ?");
    $stmt->bind_param("ii", $shooterID, $shooterKEY);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return $data ?: null;
}

function ensureTable(mysqli $conn, string $name, string $paramKey, string $paramTable = ''): void
{
    global $table; // přístup k $table z data.php
    $res = $conn->query("SHOW TABLES LIKE '" . $conn->real_escape_string($name) . "'");
    if (! $res || $res->num_rows === 0) {
        $dbcreateParam = $paramKey;
        $dbcreateTable = $paramTable ?: $name;

        // Předání proměnných do dbcreate skriptu
        $_SERVER['dbcreateParam'] = $dbcreateParam;
        $_SERVER['dbcreateTable'] = $dbcreateTable;
        include __DIR__ . '/db/dbcreate.php';
    }
}

function runQuery($query, $name = '')
{
    global $conn;
    if ($conn->query($query) === TRUE) {
        // echo "<pre style='color:white;font-size:14px;'>$name<br/>Pokračujte klávesou F5</pre>";
        echo "";
    } else {
        echo "<pre style='color:#ff0000;font-size:14px;'>$name: Chyba – " . $conn->error . "</pre>";
        exit;
    }
}

function getValueFromTable($conn, $table, $whereColumn, $whereValue, $returnColumn)
{
    // připravíme dotaz s placeholderem
    $sql = "SELECT $returnColumn FROM $table WHERE $whereColumn = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Chyba při přípravě dotazu: " . $conn->error);
    }

    // určení typu (integer nebo string)
    $type = is_int($whereValue) ? "i" : "s";

    // napojení parametru
    $stmt->bind_param($type, $whereValue);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row[$returnColumn];
    } else {
        return null; // pokud nic nenajde
    }
}

function hasRole(string $role): bool
{
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

function hasAnyRole(array $roles): bool
{
    return isset($_SESSION['role']) && in_array($_SESSION['role'], $roles);
}

function isValidPassword($password, $username = '', &$errorMessage = '')
{
    $length     = strlen($password);
    $hasNumber  = preg_match('/\d/', $password);
    $hasSpecial = preg_match('/[\W_]/', $password);
    $hasUpper   = preg_match('/[A-Z]/', $password);
    $hasLower   = preg_match('/[a-z]/', $password);

    // načti zakázané řetězce z configu
    $forbidden = include __DIR__ . '/config/forbidden_passwords.php';

    // automaticky přidej i username
    if ($username) {
        $forbidden[] = $username;
    }

    foreach ($forbidden as $bad) {
        if ($bad && stripos($password, $bad) !== false) {
            $errorMessage = "Heslo obsahuje zakázané slovo: \"$bad\".";
            return false;
        }
    }

    if ($length < 8 || $length > 255) {
        $errorMessage = "Heslo musí mít 8–255 znaků.";
        return false;
    }
    if (!$hasNumber) {
        $errorMessage = "Heslo musí obsahovat alespoň jedno číslo.";
        return false;
    }
    if (!$hasSpecial) {
        $errorMessage = "Heslo musí obsahovat alespoň jeden speciální znak.";
        return false;
    }
    if (!$hasUpper) {
        $errorMessage = "Heslo musí obsahovat alespoň jedno velké písmeno.";
        return false;
    }
    if (!$hasLower) {
        $errorMessage = "Heslo musí obsahovat alespoň jedno malé písmeno.";
        return false;
    }

    return true;
}

// helper pro odkaz "přidat do kalendáře" v registračním e-mailu
function buildCalendarLinks(string $reg_url, array $match_data): string
{
    $url = htmlspecialchars($reg_url, ENT_QUOTES, 'UTF-8') . '/calendar.php';
    return "<a href='$url'>přidat do kalendáře</a>";
}

// helper pro odkaz "přidat do kalendáře" v registračním e-mailu
function buildCancelLinks(string $reg_url, string $cislo, string $klic): string
{
    $url = htmlspecialchars($reg_url, ENT_QUOTES, 'UTF-8') . '/zrus_ucast.php?id=' . rawurlencode($cislo) . '&klic=' . rawurlencode($klic) . '';
    return "<a href='$url'>zrušit účast</a>";
}


// helper pro CSS d-none a required
function hidden($condition)
{
    return $condition ? 'd-none' : '';
}

function required($condition)
{
    return $condition ? 'required' : '';
}

// Funkce pro kontrolu přihlášení a session validity pro admin sekci
function require_admin(): void
{
    $isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH'])
        && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

    // Pomocná funkce pro logování
    $logFail = function (string $reason) {
        $logLine = sprintf(
            "[%s] Invalid session: %s | IP=%s | UA=%s\n",
            date('Y-m-d H:i:s'),
            $reason,
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
        );
        error_log($logLine, 3, __DIR__ . '/session_fail.log');
    };

    if (empty($_SESSION['admin_id']) || empty($_SESSION['loggedin'])) {
        $logFail('not_logged_in');

        if ($isAjax) {
            http_response_code(401);
            echo json_encode(['error' => 'not_logged_in']);
            exit;
        }
        header('Location: ' . ($GLOBALS['reg_redirect_url'] ?? '/'));
        exit;
    }

    $currentUA = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (!empty($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $currentUA) {
        $logFail('user_agent_mismatch');

        session_unset();
        session_destroy();

        if ($isAjax) {
            http_response_code(401);
            echo json_encode(['error' => 'session_invalid']);
            exit;
        }

        header('Location: ' . ($GLOBALS['reg_redirect_url'] ?? '/'));
        exit;
    }

    // 3) Kontrola IP fragmentu
    if (!empty($_SESSION['ip_fragment'])) {
        $currentFrag = substr($_SERVER['REMOTE_ADDR'] ?? '', 0, 7);
        if ($_SESSION['ip_fragment'] !== $currentFrag) {
            $logFail('ip_fragment_mismatch');

            session_unset();
            session_destroy();

            if ($isAjax) {
                http_response_code(401);
                echo json_encode(['error' => 'session_invalid']);
                exit;
            }

            header('Location: ' . ($GLOBALS['reg_redirect_url'] ?? '/'));
            exit;
        }
    }

    if (empty($_SESSION['zavod_id'])) {
        $logFail('missing_zavod_id');

        session_unset();
        session_destroy();

        if ($isAjax) {
            http_response_code(401);
            echo json_encode(['error' => 'missing_zavod']);
            exit;
        }

        header('Location: ' . ($GLOBALS['reg_redirect_url'] ?? '/'));
        exit;
    }

    $_SESSION['last_activity'] = time();
}

// logování přihlášení
function saveLogin(string $reason, string $usernameInput)
{
    $logLine = sprintf(
        "[%s] %s - %s - %s\n",
        date('Y-m-d H:i:s'),
        $reason,
        $usernameInput,
        $_SERVER['REMOTE_ADDR']
    );
    //$timestamp = date('Y-m-d H:i:s'); 
    //$logLine = "[$timestamp] $reason - $usernameInput - $_SERVER['REMOTE_ADDR']\n";
    error_log($logLine, 3, __DIR__ . '/../devadmin/log/admin_login.log');
};
