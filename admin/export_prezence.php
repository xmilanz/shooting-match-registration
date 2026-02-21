<?php
session_start();
if (!isset($_SESSION['loggedin'])) {
    header('Location: ../index.php');
    exit;
}

require_once __DIR__ . '/../db/dbconn.php';
require_once '../libs/PhpSpreadsheet/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

// ================= DATA =================

$stmt = $conn->prepare("
    SELECT 
        Cislo,
        CONCAT(Prijmeni, ' ', Jmeno) AS PrijmeniJmeno,
        TRIM(CONCAT(ObcanskyPrukaz,' ',IF(ZbrojniOpravneni = 'on', '(zo)', ''))) AS `Občanský průkaz`,
        CisloZbrane,
        Disciplina,
        CastkaZaplatit,
        Poznamka
    FROM $table
    WHERE Vyrazeno IS NULL
    ORDER BY PrijmeniJmeno, `Občanský průkaz`, Cislo
");
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$result_match = $conn->query("SELECT Zavod FROM match_config WHERE Zavod_id='$table' LIMIT 1");
$match_data = $result_match->fetch_array();

// ================= EXCEL =================

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// ---------- PAGE SETUP (TISK) ----------

$sheet->getPageSetup()
    ->setPaperSize(PageSetup::PAPERSIZE_A4)
    ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
    ->setFitToWidth(null)
    ->setFitToHeight(null);
    
//for ($r = 19; $r <= $highestRow; $r += 16) {
//    $sheet->setBreak("A$r", \PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::BREAK_ROW);
//}

// opakování hlavičky (řádky 1–3)
$sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, 3);

// okraje
$sheet->getPageMargins()
    ->setTop(0.75)
    ->setBottom(0.75)
    ->setLeft(0.5)
    ->setRight(0.5);

// ================= HLAVIČKA =================

// řádek 1
$sheet->setCellValue('A1', $match_data['Zavod'] . ' - ' . date('d.m.Y') . ' - Podpisový arch účastníků');
$sheet->mergeCells('A1:H1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
$sheet->getRowDimension(1)->setRowHeight(30);
$sheet->getStyle('A1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_TOP);

// řádek 2
$sheet->setCellValue('A2', 'Svým podpisem stvrzuji, že jsem se seznámil(a) s Provozním řádem střelnice Prachatice');
$sheet->mergeCells('A2:H2');
$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(14);
$sheet->getRowDimension(2)->setRowHeight(30);
$sheet->getStyle('A2')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_TOP);

// řádek 3 – hlavička tabulky
$sheet->fromArray(
    ['', 'Příjmení, Jméno', 'OP / EZP', 'CisloZbrane','Disciplína', 'Směna', 'Startovné', 'Podpis', 'Poznámka'],
    null,
    'A3'
);

// ================= DATA =================

$row = 4;
$counter = 1;
foreach ($data as $line) {
    $sheet->fromArray(
        [
            $counter,
            $line['PrijmeniJmeno'],
            $line['Občanský průkaz'],
            $line['CisloZbrane'],
            $line['Disciplina'],
            '',
            $line['CastkaZaplatit'],
            '',
            $line['Poznamka']
        ],
        null,
        "A$row"
    );
    $row++;
    $counter++;
}

$highestRow = $sheet->getHighestRow();

// ================= STYLY =================

$headerStyle = [
    'font' => ['bold' => true, 'size' => 14],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical'   => Alignment::VERTICAL_CENTER
    ],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'DDDDDD']
    ],
];

$bodyStyle = [
    'font' => ['size' => 13],
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
];

// hlavička tabulky
$sheet->getStyle("B3:H3")->applyFromArray($headerStyle);
$sheet->getRowDimension(3)->setRowHeight(30);

// tělo tabulky
$sheet->getStyle("A4:H$highestRow")->applyFromArray($bodyStyle);
// tučné jméno
$sheet->getStyle("B4:B$highestRow")->getFont()->setBold(true)->setSize(14);

// centrování
$sheet->getStyle("C4:C$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("E4:H$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// výšky řádků
for ($i = 4; $i <= $highestRow; $i++) {
    $sheet->getRowDimension($i)->setRowHeight(27);
}

// ================= ŠÍŘKY SLOUPCŮ =================

$sheet->getColumnDimension('A')->setWidth(5);
$sheet->getColumnDimension('B')->setWidth(30);
$sheet->getColumnDimension('C')->setWidth(13);
$sheet->getColumnDimension('D')->setWidth(15);
$sheet->getColumnDimension('E')->setWidth(15);
$sheet->getColumnDimension('F')->setWidth(12);
$sheet->getColumnDimension('G')->setWidth(12);
$sheet->getColumnDimension('H')->setWidth(35);

// ================= VÝSTUP =================

$filename = 'Podpisovy_arch_' . $table . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
ob_clean();
$writer->save('php://output');
exit;
