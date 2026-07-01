<?php

declare(strict_types=1);

require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/db/dbconn.php';

// --- Datum závodu (formát d.m.Y, např. "1.6.2026") ---
$datumZavod = DateTime::createFromFormat('j.n.Y', $match_data['Zavod_datum'] ?? '', new DateTimeZone('Europe/Prague'));

if (!$datumZavod) {
    http_response_code(404);
    exit('Datum závodu není k dispozici.');
}

// --- Čas hlavního závodu (formát "H:i - H:i", např. "9:00 - 15:00") ---
$casMain  = trim($match_data['Zavod_cas_main'] ?? '');
$casStart = '08:00';
$casEnd   = '16:00';

if ($casMain !== '' && strpos($casMain, '-') !== false) {
    [$casStartRaw, $casEndRaw] = array_map('trim', explode('-', $casMain, 2));
    if ($casStartRaw !== '') $casStart = $casStartRaw;
    if ($casEndRaw   !== '') $casEnd   = $casEndRaw;
}

// --- Sestavení DTSTART / DTEND v Europe/Prague, převod na UTC ---
$dtStart = clone $datumZavod;
$dtEnd   = clone $datumZavod;

$startParts = array_pad(explode(':', $casStart), 2, '0');
$endParts   = array_pad(explode(':', $casEnd),   2, '0');

$dtStart->setTime((int)$startParts[0], (int)$startParts[1]);
$dtEnd->setTime((int)$endParts[0],   (int)$endParts[1]);

// Pokud je konec dříve nebo stejně jako začátek, nastavíme +4 hodiny
if ($dtEnd <= $dtStart) {
    $dtEnd = (clone $dtStart)->modify('+4 hours');
}

// Převod na UTC pro iCal
$utc = new DateTimeZone('UTC');
$dtStartUtc = (clone $dtStart)->setTimezone($utc);
$dtEndUtc   = (clone $dtEnd)->setTimezone($utc);
$dtStamp    = (new DateTime('now', $utc))->format('Ymd\THis\Z');

// --- Texty ---
$nazevZavodu = (string)($match_data['Zavod']          ?? 'Závod');
$mistoZavodu = (string)($match_data['Zavod_misto']    ?? '');
$poradatel   = (string)($match_data['Zavod_poradatel'] ?? '');
$propozice   = (string)($match_data['Zavod_propozice'] ?? '');

$popisRadky = [];
if ($poradatel !== '') $popisRadky[] = 'Pořadatel: ' . $poradatel;
if ($propozice !== '') $popisRadky[] = 'Propozice: ' . $propozice;
$popisRadky[] = 'Další informace: ' . $reg_url;
$popis = implode('\n', $popisRadky);

// --- Helpers ---
function icalEscape(string $text): string
{
    return str_replace(['\\', ';', ',', "\n", "\r"], ['\\\\', '\\;', '\\,', '\\n', ''], $text);
}

function icalLine(string $content): string
{
    $maxLen  = 74;
    $lines   = [];
    $current = '';
    foreach (preg_split('//u', $content, -1, PREG_SPLIT_NO_EMPTY) as $char) {
        if (strlen($current . $char) > $maxLen) {
            $lines[]  = $current;
            $current  = ' ' . $char;
        } else {
            $current .= $char;
        }
    }
    $lines[] = $current;
    return implode("\r\n", $lines);
}

// --- Sestavení .ics ---
$uid = 'zavod-' . $table . '-' . $dtStartUtc->format('Ymd') . '@' . ($_SERVER['HTTP_HOST'] ?? 'registrace');

$ics   = [];
$ics[] = 'BEGIN:VCALENDAR';
$ics[] = 'VERSION:2.0';
$ics[] = 'PRODID:-//' . icalEscape($poradatel ?: 'Registrace') . '//Registrace zavodu//CS';
$ics[] = 'CALSCALE:GREGORIAN';
$ics[] = 'METHOD:PUBLISH';
$ics[] = 'BEGIN:VEVENT';
$ics[] = icalLine('UID:'      . $uid);
$ics[] = icalLine('DTSTAMP:'  . $dtStamp);
$ics[] = icalLine('DTSTART:'  . $dtStartUtc->format('Ymd\THis\Z'));
$ics[] = icalLine('DTEND:'    . $dtEndUtc->format('Ymd\THis\Z'));
$ics[] = icalLine('SUMMARY:'  . icalEscape($nazevZavodu));
$ics[] = icalLine('LOCATION:' . icalEscape($mistoZavodu));
$ics[] = icalLine('DESCRIPTION:' . $popis);
$ics[] = icalLine('URL:'      . $reg_url);
$ics[] = 'END:VEVENT';
$ics[] = 'END:VCALENDAR';

$icsContent = implode("\r\n", $ics) . "\r\n";

// --- Název souboru ---
$fileName = trim(preg_replace('/[^A-Za-z0-9_-]+/', '_', $nazevZavodu), '_') ?: 'zavod';
$fileName .= '.ics';

// --- Odeslání ---
header('Content-Type: text/calendar; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $fileName . '"');
header('Content-Length: ' . strlen($icsContent));
header('Cache-Control: no-cache, no-store, must-revalidate');

echo $icsContent;
exit;
