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

// Normalizuje cislo ZP cislo prukazu zbrane
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

        $_SERVER['dbcreateParam'] = $dbcreateParam;
        $_SERVER['dbcreateTable'] = $dbcreateTable;
        include_once __DIR__ . '/db/dbcreate.php';
    }
}

function runQuery($query, $name = '')
{
    global $conn;
    if ($conn->query($query) === TRUE) {
        echo "<pre style='color:white;font-size:14px;'>$name<br/>Pokračujte klávesou F5</pre>";
    } else {
        echo "<pre style='color:#ff0000;font-size:14px;'>$name: Chyba – " . $conn->error . "</pre>";
        exit;
    }
}

function getValueFromTable($conn, $table, $whereColumn, $whereValue, $returnColumn)
{
    $sql = "SELECT $returnColumn FROM $table WHERE $whereColumn = ?";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        die("Chyba při přípravě dotazu: " . $conn->error);
    }

    $type = is_int($whereValue) ? "i" : "s";

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

    $forbidden = include __DIR__ . '/config/forbidden_passwords.php';

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
