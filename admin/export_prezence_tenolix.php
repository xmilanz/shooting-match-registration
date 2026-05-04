<?php
require_once __DIR__ . '/session_init.php';
require_once __DIR__ . '/config/data.php';
require_once __DIR__ . '/db/dbconn.php';
require_admin();

require_once __DIR__ . '/libs/PhpSpreadsheet/vendor/autoload.php';

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
        Kategorie,
        CASE WHEN Prijmeni LIKE '% %' THEN CONCAT(SUBSTRING_INDEX(Prijmeni, ' ', 1), ' ', Jmeno, ' ', SUBSTRING_INDEX(Prijmeni, ' ', -1)) ELSE CONCAT(Prijmeni, ' ', Jmeno) END AS PrijmeniJmeno,
        Rocnik,
        Klub,
        Poznamka,
        Trenink,
        Zaplaceno,
        ZodpovednaOsoba,
        ObcanskyPrukaz,
        CisloZbrane,
        CastkaZaplatit
    FROM $table
    WHERE Vyrazeno IS NULL
    ORDER BY Cislo
");
$stmt->execute();
$result = $stmt->get_result();
$data = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$result_match = $conn->query("SELECT Zavod FROM $table_matches WHERE Zavod_id='$table' LIMIT 1");
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
$sheet->setCellValue('A1', $match_data['Zavod'] . ' - ' . date('d.m.Y') . ' - seznam účastníků');
$sheet->mergeCells('A1:K1');
$sheet->getStyle('A1')->getFont()->setBold(true)->setSize(20);
$sheet->getRowDimension(1)->setRowHeight(30);
$sheet->getStyle('A1')->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
    ->setVertical(Alignment::VERTICAL_TOP);

// řádek 2
//$sheet->setCellValue('A2', 'Svým podpisem stvrzuji, že jsem se seznámil(a) s Provozním řádem střelnice Prachatice');
//$sheet->mergeCells('A2:K2');
//$sheet->getStyle('A2')->getFont()->setItalic(true)->setSize(14);
//$sheet->getRowDimension(2)->setRowHeight(30);
//$sheet->getStyle('A2')->getAlignment()
//    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
//    ->setVertical(Alignment::VERTICAL_TOP);

// řádek 3 – hlavička tabulky
$sheet->fromArray(
    ['Číslo', 'Kategorie', 'Příjmení Jméno', 'Ročník', 'Klub', 'Poznámka', 'Trénink', 'Zaplatit', 'Zodpovědná osoba', 'Občanský průkaz', 'Číslo zbraně'],
    null,
    'A3'
);

// ================= DATA =================

$row = 4;
foreach ($data as $line) {
    $sheet->fromArray(
        [
            $line['Cislo'],
            $line['Kategorie'],
            $line['PrijmeniJmeno'],
            $line['Rocnik'],
            $line['Klub'],
            $line['Poznamka'],
            ((int)($line['Trenink'] ?? 0) === 1) ? 'ANO' : 'NE',
//            ((int)($line['Zaplaceno'] ?? 0) === 1) ? 'ANO' : 'NE',
            $line['CastkaZaplatit'],
            $line['ZodpovednaOsoba'],
            $line['ObcanskyPrukaz'],
            $line['CisloZbrane'],
        ],
        null,
        "A$row"
    );
    $row++;
}

$highestRow = $sheet->getHighestRow();

// ================= STYLY =================

$headerStyle = [
    'font' => ['bold' => true, 'size' => 11],
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
    'font' => ['size' => 11],
    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
    'borders' => [
        'allBorders' => ['borderStyle' => Border::BORDER_THIN]
    ],
];

// hlavička tabulky
$sheet->getStyle("A3:K3")->applyFromArray($headerStyle);
$sheet->getRowDimension(3)->setRowHeight(25);

// tělo tabulky
$sheet->getStyle("A4:K$highestRow")->applyFromArray($bodyStyle);
// tučné jméno
$sheet->getStyle("C4:C$highestRow")->getFont()->setBold(true)->setSize(11);

// centrování
$sheet->getStyle("A4:B$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("D4:D$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("G4:H$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("K4:K$highestRow")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// výšky řádků
for ($i = 4; $i <= $highestRow; $i++) {
    $sheet->getRowDimension($i)->setRowHeight(20);
}

// ================= ŠÍŘKY SLOUPCŮ =================

$sheet->getColumnDimension('A')->setWidth(5);   // Cislo
$sheet->getColumnDimension('B')->setWidth(10);  // Kategorie
$sheet->getColumnDimension('C')->setWidth(20);  // PrijmeniJmeno
$sheet->getColumnDimension('D')->setWidth(7);  // Rocnik
$sheet->getColumnDimension('E')->setWidth(24);  // Klub
$sheet->getColumnDimension('F')->setWidth(30);  // Poznamka
$sheet->getColumnDimension('G')->setWidth(8);  // Trenink
$sheet->getColumnDimension('H')->setWidth(9);  // Zaplatit
$sheet->getColumnDimension('I')->setWidth(20);  // ZodpovednaOsoba
$sheet->getColumnDimension('J')->setWidth(18);  // ObcanskyPrukaz
$sheet->getColumnDimension('K')->setWidth(15);  // CisloZbrane

// ================= VÝSTUP =================

$filename = 'Podpisovy_arch_' . $table . '.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
ob_clean();
$writer->save('php://output');
exit;
