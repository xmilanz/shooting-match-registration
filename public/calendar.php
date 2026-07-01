<?php

declare(strict_types=1);

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';

// --- Datum závodu ---
$datumZavod = DateTime::createFromFormat('j.n.Y', $match_data['Zavod_datum'] ?? '', new DateTimeZone('Europe/Prague'));

if (!$datumZavod) {
    http_response_code(404);
    exit('Datum závodu není k dispozici.');
}

// --- Čas hlavního závodu ---
$casMain  = trim($match_data['Zavod_cas_main'] ?? '');
$casStart = '08:00';
$casEnd   = '16:00';

if ($casMain !== '' && strpos($casMain, '-') !== false) {
    [$casStartRaw, $casEndRaw] = array_map('trim', explode('-', $casMain, 2));
    if ($casStartRaw !== '') $casStart = $casStartRaw;
    if ($casEndRaw   !== '') $casEnd   = $casEndRaw;
}

$dtStart = clone $datumZavod;
$dtEnd   = clone $datumZavod;

$sp = array_pad(explode(':', $casStart), 2, '0');
$ep = array_pad(explode(':', $casEnd),   2, '0');
$dtStart->setTime((int)$sp[0], (int)$sp[1]);
$dtEnd->setTime((int)$ep[0],   (int)$ep[1]);

if ($dtEnd <= $dtStart) {
    $dtEnd = (clone $dtStart)->modify('+4 hours');
}

$utc        = new DateTimeZone('UTC');
$dtStartUtc = (clone $dtStart)->setTimezone($utc);
$dtEndUtc   = (clone $dtEnd)->setTimezone($utc);

// Unix timestamp v milisekundách (pro Android intent)
$tsStart = $dtStartUtc->getTimestamp() * 1000;
$tsEnd   = $dtEndUtc->getTimestamp() * 1000;

// UTC formát pro iCal / Google / Outlook
$startUtcIcal    = $dtStartUtc->format('Ymd\THis\Z');
$endUtcIcal      = $dtEndUtc->format('Ymd\THis\Z');
$startUtcOutlook = $dtStartUtc->format('Y-m-d\TH:i:s\Z');
$endUtcOutlook   = $dtEndUtc->format('Y-m-d\TH:i:s\Z');

// --- Texty ---
$nazev     = (string)($match_data['Zavod']           ?? 'Závod');
$misto     = (string)($match_data['Zavod_misto']     ?? '');
$poradatel = (string)($match_data['Zavod_poradatel'] ?? '');
$propozice   = (string)($match_data['Zavod_propozice'] ?? '');
$popisRadky = [];
if ($poradatel !== '') $popisRadky[] = 'Pořadatel: ' . $poradatel;
if ($propozice !== '') $popisRadky[] = 'Propozice: ' . $propozice;
$popisRadky[] = 'Další informace: ' . $reg_url;
$popis = implode('<br>', $popisRadky);

// --- Detekce zařízení ---
$ua      = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isAndroid = stripos($ua, 'android') !== false;
$isIos     = (stripos($ua, 'iphone') !== false || stripos($ua, 'ipad') !== false);

// --- Android: intent:// URI → otevře nativní kalendář ---
if ($isAndroid) {
    $intentUrl = 'intent:#Intent'
        . ';action=android.intent.action.INSERT'
        . ';type=vnd.android.cursor.dir/event'
        . ';S.title='         . rawurlencode($nazev)
        . ';l.beginTime='     . $tsStart
        . ';l.endTime='       . $tsEnd
        . ';S.eventLocation=' . rawurlencode($misto)
        . ';S.description='   . rawurlencode($popis)
        . ';end';

    header('Location: ' . $intentUrl);
    exit;
}

// --- iOS: .ics → Apple Calendar otevře nativně ---
if ($isIos) {
    header('Location: ' . $reg_url . '/ical.php');
    exit;
}

// --- Desktop: výběrová stránka ---
$googleUrl = 'https://calendar.google.com/calendar/render?action=TEMPLATE'
    . '&text='     . rawurlencode($nazev)
    . '&dates='    . $startUtcIcal . '/' . $endUtcIcal
    . '&location=' . rawurlencode($misto)
    . '&details='  . rawurlencode($popis);

$outlookUrl = 'https://outlook.live.com/calendar/0/action/compose?rru=addevent'
    . '&subject='  . rawurlencode($nazev)
    . '&startdt='  . $startUtcOutlook
    . '&enddt='    . $endUtcOutlook
    . '&location=' . rawurlencode($misto)
    . '&body='     . rawurlencode($popis);

$icsUrl = $reg_url . '/ical.php';

?>
<!DOCTYPE html>
<html lang="cs">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Přidat do kalendáře – <?= htmlspecialchars($nazev, ENT_QUOTES, 'UTF-8') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <style>
        body {
            background: #f5f5f5;
        }

        .calendar-card {
            max-width: 420px;
            margin: 30px auto;
        }

        .calendar-btn {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 20px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            transition: opacity .15s;
        }

        .calendar-btn:hover {
            opacity: .85;
            text-decoration: none;
        }

        .calendar-btn svg {
            flex-shrink: 0;
        }
    </style>
</head>

<body>
    <div class="calendar-card card shadow-sm">
        <div class="bg-secondary bg-opacity-50 text-center p-3">
            <h5 class="mb-1"><?= htmlspecialchars($nazev, ENT_QUOTES, 'UTF-8') ?></h5>
            <small class="text-body-secondary mb-0"><?= htmlspecialchars($poradatel, ENT_QUOTES, 'UTF-8') ?></small>
        </div>
        <div class="bg-secondary bg-opacity-10 border-bottom text-dark px-3 py-2">
            <small class="text-body-secondary">
                datum: <?= htmlspecialchars($dtStart->format('j. n. Y'), ENT_QUOTES, 'UTF-8') ?>
                <br>
                čas: <?= htmlspecialchars($casStart . ' – ' . $casEnd, ENT_QUOTES, 'UTF-8') ?>
                <br>
                místo: <?= htmlspecialchars($misto, ENT_QUOTES, 'UTF-8') ?>
            </small>
        </div>
        <div class="weight-100 small p-3">
            <p>Přidat do kalendáře</p>

            <div class="d-grid gap-3">

                <!-- Android Calendar -->
                <a href="<?= htmlspecialchars($googleUrl, ENT_QUOTES, 'UTF-8') ?>"
                    target="_blank" rel="noopener"
                    class="calendar-btn text-white bg-primary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5A2 2 0 0 0 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z" />
                    </svg>
                    Android telefon, Google kalendář
                </a>
                <!-- Apple Calendar -->
                <a href="<?= htmlspecialchars($icsUrl, ENT_QUOTES, 'UTF-8') ?>"
                    class="calendar-btn bg-info text-white">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5A2 2 0 0 0 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z" />
                    </svg>
                    iPhone, iPad
                </a>

                <!-- Outlook.com -->
                <a href="<?= htmlspecialchars($outlookUrl, ENT_QUOTES, 'UTF-8') ?>"
                    target="_blank" rel="noopener"
                    class="calendar-btn text-white" style="background:#0072C6;">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                        <path d="M19 4h-1V2h-2v2H8V2H6v2H5A2 2 0 0 0 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V9h14v11z" />
                    </svg>
                    Microsoft 365
                </a>

                <!-- stáhnout .ics -->
                <a href="<?= htmlspecialchars($icsUrl, ENT_QUOTES, 'UTF-8') ?>"
                    class="calendar-btn text-white bg-secondary">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="white" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 16l-5-5 1.41-1.41L11 13.17V4h2v9.17l2.59-2.58L17 11l-5 5zm-7 2h14v2H5v-2z" />
                    </svg>
                    stáhnout .ics (Thunderbird, Outlook…)
                </a>
            </div>
        </div>
    </div>
</body>

</html>