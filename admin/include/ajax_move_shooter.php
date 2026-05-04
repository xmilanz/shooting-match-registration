<?php
require_once __DIR__ . '/../session_init.php';
require_once __DIR__ . '/../config/data.php';
require_once __DIR__ . '/../db/dbconn.php';
require_admin();

header('Content-Type: application/json');

$cislo   = isset($_POST['cislo']) ? intval($_POST['cislo']) : 0;
$stavNew = isset($_POST['stav'])  ? intval($_POST['stav'])  : 0;

if ($cislo <= 0 || $stavNew <= 0) {
    echo json_encode(['success' => false, 'error' => 'Neplatné parametry.']);
    exit;
}

$shift = intdiv($stavNew, 100);
$pos   = $stavNew % 100;

if ($shift < 1 || $pos < 1 || $pos > 10) {
    echo json_encode(['success' => false, 'error' => 'Neplatný stav.']);
    exit;
}

// Ověření, zda cílový stav není obsazen jiným závodníkem
$checkStmt = $conn->prepare("SELECT Cislo FROM $table WHERE Stav = ? AND Cislo != ? LIMIT 1");
$checkStmt->bind_param('ii', $stavNew, $cislo);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();
$checkStmt->close();

if ($checkResult->num_rows > 0) {
    echo json_encode(['success' => false, 'error' => 'Cílový stav je již obsazen.']);
    exit;
}

$stmt = $conn->prepare("UPDATE $table SET Stav = ? WHERE Cislo = ?");
$stmt->bind_param('ii', $stavNew, $cislo);
$ok = $stmt->execute();
$stmt->close();

echo json_encode(['success' => $ok]);
?>
