<?php
/**
 * Export evaluation data to Excel (XLSX) using PhpSpreadsheet 5.5.0
 */
require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/EvaluationManager.php';
require_once __DIR__ . '/config/FacilityManager.php';

// Load Composer autoloader (includes PhpSpreadsheet + its dependencies)
require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

$evaluation_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$evaluation_id) {
    header("Location: dashboard.php");
    exit;
}

try {
    $database = new Database();
    $db = $database->connect();
    $evaluation_manager = new EvaluationManager($db);
    $facility_manager = new FacilityManager($db);

    $evaluation = $evaluation_manager->getEvaluation($evaluation_id);
    if (!$evaluation) {
        header("Location: dashboard.php");
        exit;
    }

    // Check access control
    $user = $_SESSION;
    $current_role = $user['role'] ?? 'viewer';
    
    if (in_array($current_role, ['viewer', 'input'])) {
    if (!$facility_manager->canUserAccessFacility($evaluation['facility_id'], $user['user_id'], $user['facility_names'] ?? ($user['facility_name'] ?? null))) {
            header("Location: unauthorized.php");
            exit;
        }
    }

    $indicators = $evaluation_manager->getIndicatorsForEvaluation($evaluation_id);
    $facility = $facility_manager->getFacility($evaluation['facility_id']);
} catch (Exception $e) {
    error_log("Export Error: " . $e->getMessage());
    header("Location: dashboard.php");
    exit;
}

// organize by building block
$organized_by_bb = [];
foreach ($indicators as $row) {
    $bb = $row['building_block_name'];
    if (!isset($organized_by_bb[$bb])) {
        $organized_by_bb[$bb] = [];
    }
    $organized_by_bb[$bb][] = $row;
}

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Sheet1');

function prepareSheet($sheet, $facility, $evaluation)
{
    // page setup: folio/long bond (legal landscape)
    $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
    $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
    $sheet->getPageMargins()->setLeft(0.25);
    $sheet->getPageMargins()->setRight(0.25);
    $sheet->getPageMargins()->setTop(0.25);
    $sheet->getPageMargins()->setBottom(0.25);

    // fixed column widths scaled for folio page (A and I for logos)
    $sheet->getColumnDimension('A')->setWidth(10);  // left logo
    $sheet->getColumnDimension('B')->setWidth(25);
    // KPI narrower
    $sheet->getColumnDimension('C')->setWidth(20);
    $sheet->getColumnDimension('D')->setWidth(15);
    // Means of Verification narrower
    $sheet->getColumnDimension('E')->setWidth(20);
    // score column
    $sheet->getColumnDimension('F')->setWidth(15);
    // percentage column
    $sheet->getColumnDimension('G')->setWidth(25);
    // remarks wider
    $sheet->getColumnDimension('H')->setWidth(30);
    $sheet->getColumnDimension('I')->setWidth(10);  // right logo

    // add logos and header text
    addLogos($sheet);
    // Tagline area: move Republic header into rows 1-4 for printed letterhead
    $sheet->mergeCells("B1:H4");
    $sheet->setCellValue("B1", "Republic of the Philippines\nProvince of Leyte\nPROVINCIAL HEALTH OFFICE\nCandahug Palo, Leyte");
    $sheet->getStyle("B1")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);

    // Reserve row 5 as a small spacer, place main title on row 6
    $sheet->mergeCells("A6:I6");
    $sheet->setCellValue("A6", "PROVINCE-WIDE LOCAL HEALTH SYSTEM MONITORING TOOL");
    $sheet->getStyle("A6")->getFont()->setBold(true)->setSize(16);
    $sheet->getStyle("A6")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);

    // start further rows after title
    $row = 7;

    // Status on right (column I) - now row 7
    $sheet->setCellValue("I{$row}", ucfirst($evaluation['status']));
    $sheet->getStyle("I{$row}")->getFont()->setBold(true);
    $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $row++;

    // Instructions spanning B:H (now row 8)
    $sheet->mergeCells("B{$row}:H{$row}");
    $sheet->setCellValue("B{$row}", "Single-click = Complied (1) | Double-click = Not Complied (0)");
    $sheet->getStyle("B{$row}")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle("B{$row}")->getFont()->setItalic(true)->setSize(11);
    $row++;

    // Facility info spanning B:H (now row 9)
    $sheet->mergeCells("B{$row}:H{$row}");
    $sheet->setCellValue("B{$row}", "Facility: " . ($facility['name'] ?? 'N/A'));
    $sheet->getStyle("B{$row}")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setWrapText(true);
    $row++;

    // Date (now row 10)
    $sheet->mergeCells("B{$row}:H{$row}");
    $sheet->setCellValue("B{$row}", "Date: " . date('M d, Y', strtotime($evaluation['evaluation_date'])));
    $sheet->getStyle("B{$row}")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setWrapText(true);
    $row++;

    // Period (now row 11)
    $sheet->mergeCells("B{$row}:H{$row}");
    $sheet->setCellValue("B{$row}", "Period: " . $evaluation['evaluation_period']);
    $sheet->getStyle("B{$row}")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setWrapText(true);
    $row++;

    // Approval status row
    $approvalText = 'Approval: ';
    if (isset($evaluation['approved']) && $evaluation['approved'] === 1) {
        $approvalText .= 'Approved';
    } elseif (isset($evaluation['approved']) && $evaluation['approved'] === 2) {
        $approvalText .= 'Rejected';
    } else {
        $approvalText .= 'Pending Review';
    }
    $sheet->mergeCells("B{$row}:H{$row}");
    $sheet->setCellValue("B{$row}", $approvalText);
    $sheet->getStyle("B{$row}")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setWrapText(true);
    $sheet->getStyle("B{$row}")->getFont()->setItalic(true)->setSize(11);
    $row++;

    $row++; // blank row before table (now row 13)
    
    // add table header row
    renderTableHeaders($sheet, $row);
    $row++;
    
    return $row;
}

// function to render table headers
function renderTableHeaders($sheet, $row)
{
    // order changed: score first among last three
    $headers = ['Strategic Objectives', 'Key Performance Indicators', 'Target', 'Means of Verification', 'Score', '% (Percentage)', 'Remarks'];
    foreach ($headers as $col => $header) {
        $cellRef = chr(66 + $col) . $row;  // B=66, C=67, ... H=72
        $sheet->setCellValue($cellRef, $header);
        $sheet->getStyle($cellRef)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($cellRef)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF667EEA');
        $sheet->getStyle($cellRef)->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER)
            ->setWrapText(true);
    }
    // automatic row height for header
}

// function to add logos to sheet
function addLogos($sheet)
{
    $left = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $left->setName('PoL Logo');
    $left->setPath(__DIR__ . '/images/province_of_leyte_logo.png');
    $left->setHeight(60);
    $left->setCoordinates('A1');
    $left->setOffsetX(5);
    $left->setOffsetY(5);
    $left->setWorksheet($sheet);

    $right = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
    $right->setName('PHO Logo');
    $right->setPath(__DIR__ . '/images/province_health_office_logo.jpg');
    $right->setHeight(60);
    $right->setCoordinates('I1');
    $right->setOffsetX(5);
    $right->setOffsetY(5);
    $right->setWorksheet($sheet);
}

$row = prepareSheet($sheet, $facility, $evaluation);

// Data rows with sheet overflow handling (aim for ~15 sheets)
$maxRows = 65; // rows per sheet (adjusted for ~15 sheets total)
foreach ($organized_by_bb as $bb_name => $bb_scores) {
    // start a new sheet if remaining rows insufficient for this block
    $estimatedRows = count($bb_scores) + 5; // block header + scores + margin
    if ($row + $estimatedRows > $maxRows) {
        $sheet = $spreadsheet->createSheet();
        $sheet->setTitle('Sheet' . $spreadsheet->getSheetCount());
        $row = prepareSheet($sheet, $facility, $evaluation);
    }
    // compute counts up front
    $totalKPIs = 0;
    $blockTotal = 0;
    foreach ($bb_scores as $s) {
        if (strpos($bb_name, 'Health Service Delivery') !== false &&
            $s['indicator_name'] === 'Percent of facilities meeting service standards') {
            continue;
        }
        $totalKPIs++;
        $sv = $s['score_value'];
        if ($sv !== null && (float)$sv >= 0.5) {
            $blockTotal++;
        }
    }

    // Building block header row
    $sheet->mergeCells("B{$row}:H{$row}");
    $bbText = $bb_name . "\n(" . $bb_scores[0]['weight_percentage'] . "%)/\nScore: {$blockTotal} / {$totalKPIs}";
    $sheet->setCellValue("B{$row}", $bbText);
    $sheet->getStyle("B{$row}")->getFont()->setBold(true)->setSize(12);
    $sheet->getStyle("B{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF5F5F5');
    $sheet->getStyle("B{$row}")->getAlignment()
        ->setHorizontal(Alignment::HORIZONTAL_CENTER)
        ->setVertical(Alignment::VERTICAL_CENTER)
        ->setWrapText(true);
    // row height auto (remove fixed 45)
    $row++;

    // Data rows for this building block
    foreach ($bb_scores as $score) {
        // skip demonstration indicator
        if (strpos($bb_name, 'Health Service Delivery') !== false &&
            $score['indicator_name'] === 'Percent of facilities meeting service standards') {
            continue;
        }

        $sv = $score['score_value'];
        $is1 = ($sv !== null && (float)$sv >= 0.5);
        $display = ($sv === null) ? '' : ($is1 ? '1' : '0');

        $sheet->setCellValue("B{$row}", $score['objective_name']);
        $sheet->setCellValue("C{$row}", $score['indicator_name']);
        $sheet->setCellValue("D{$row}", $score['target_value'] ?? '-');
        $sheet->setCellValue("E{$row}", $score['means_of_verification'] ?? '');
        // score first
        $sheet->setCellValue("F{$row}", $display);
        $sheet->setCellValue("G{$row}", $score['percentage_value'] ?? '');
        $sheet->setCellValue("H{$row}", $score['remarks'] ?? '');

        // Apply styling with wrap text everywhere
        for ($col = 2; $col <= 8; $col++) {
            $cellRef = chr(64 + $col) . $row;  // B=66, C=67, ... H=72
            $sheet->getStyle($cellRef)->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle($cellRef)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // Score cell color coding (column F)
        if ($display === '1') {
            $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFC8E6C9');
            $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FF2E7D32');
            $sheet->getStyle("F{$row}")->getFont()->setBold(true);
        } elseif ($display === '0') {
            $sheet->getStyle("F{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFCDD2');
            $sheet->getStyle("F{$row}")->getFont()->getColor()->setARGB('FFC62828');
            $sheet->getStyle("F{$row}")->getFont()->setBold(true);
        }

        // automatic height
        $row++;
    }
}

// --- Signatures sheet (insert before export) ---
// Create a new sheet and populate with signature/approval information from the evaluation record.

$baseSigName = 'Signatures';
// ensure unique sheet name (avoid duplicate name exception)
$name = $baseSigName;
$i = 1;
while ($spreadsheet->getSheetByName($name) !== null) {
    $name = $baseSigName . '_' . $i;
    $i++;
}
// style and layout for signatures sheet (folio/landscape friendly)
$sigSheet = $spreadsheet->createSheet();
$sigSheet->setTitle($name);
$sigSheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
$sigSheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_LEGAL);
$sigSheet->getPageMargins()->setLeft(0.25);
$sigSheet->getPageMargins()->setRight(0.25);
$sigSheet->getPageMargins()->setTop(0.25);
$sigSheet->getPageMargins()->setBottom(0.25);

// column widths for professional horizontal signature layout
$sigSheet->getColumnDimension('A')->setWidth(2);
$sigSheet->getColumnDimension('B')->setWidth(15); // label
$sigSheet->getColumnDimension('C')->setWidth(25); // signature space
$sigSheet->getColumnDimension('D')->setWidth(15); // label
$sigSheet->getColumnDimension('E')->setWidth(25); // signature space
$sigSheet->getColumnDimension('F')->setWidth(15); // label
$sigSheet->getColumnDimension('G')->setWidth(25); // signature space
$sigSheet->getColumnDimension('H')->setWidth(2);

$r = 2;
// === FORMAL TITLE ===
$sigSheet->mergeCells("B{$r}:G{$r}");
$sigSheet->setCellValue("B{$r}", 'APPROVAL & SIGNATURES');
$sigSheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(14)->setColor(new Color('FF333333'));
$sigSheet->getStyle("B{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sigSheet->getRowDimension($r)->setRowHeight(20);
$r += 1;

// === DECORATIVE LINE ===
$sigSheet->getStyle("B{$r}:G{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF667EEA'));
$r += 2; // one empty row

// cache evaluation data
$ev = $evaluation;

// Helper function to render a single approval signature block
function renderApprovalBlock($sheet, &$r, $roleLabel, $name, $signature, $date) {
    // Column references for this block (will be passed as B/C for first, D/E for second, etc)
    // But we need to handle this in the loop
    // Let's keep it simpler and handle the grid manually
}

// first block: three columns (Assessed/Verified/Approved)
$roles = [
    'B' => ['Assessed by', $ev['assessed_by'] ?? '', $ev['assessed_signature'] ?? '', $ev['assessed_date'] ?? ''],
    'D' => ['Verified by', $ev['verified_by'] ?? '', $ev['verified_signature'] ?? '', $ev['verified_date'] ?? ''],
    'F' => ['Approved by', $ev['approved_by'] ?? '', $ev['approved_signature'] ?? '', $ev['approved_date'] ?? ''],
];

// Row for signature blank space with top border
foreach (['C', 'E', 'G'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getRowDimension($r)->setRowHeight(45);
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
}
$r += 1;

// Row for signature bottom border line
foreach (['C', 'E', 'G'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
    $sigSheet->getRowDimension($r)->setRowHeight(2);
}
$r += 1;

// Row: label + name & signature text
$sigSheet->setCellValue("B{$r}", 'Assessed by:');
$sigSheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$assessedName = ($ev['assessed_by'] ?? '');
if (!empty($ev['assessed_signature'])) {
    $assessedName = $ev['assessed_signature'];
}
$sigSheet->setCellValue("C{$r}", $assessedName);
$sigSheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

$sigSheet->setCellValue("D{$r}", 'Verified by:');
$sigSheet->getStyle("D{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$verifiedName = ($ev['verified_by'] ?? '');
if (!empty($ev['verified_signature'])) {
    $verifiedName = $ev['verified_signature'];
}
$sigSheet->setCellValue("E{$r}", $verifiedName);
$sigSheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

$sigSheet->setCellValue("F{$r}", 'Approved by:');
$sigSheet->getStyle("F{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$approvedName = ($ev['approved_by'] ?? '');
if (!empty($ev['approved_signature'])) {
    $approvedName = $ev['approved_signature'];
}
$sigSheet->setCellValue("G{$r}", $approvedName);
$sigSheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
$r += 1;

// Row for date blank space with top border
foreach (['C', 'E', 'G'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getRowDimension($r)->setRowHeight(30);
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
}
$r += 1;

// Row for date bottom border line
foreach (['C', 'E', 'G'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
    $sigSheet->getRowDimension($r)->setRowHeight(2);
}
$r += 1;

// Row: label + date
$sigSheet->setCellValue("B{$r}", 'Date:');
$sigSheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$sigSheet->setCellValue("C{$r}", $ev['assessed_date'] ? date('M d, Y', strtotime($ev['assessed_date'])) : '');
$sigSheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sigSheet->setCellValue("D{$r}", 'Date:');
$sigSheet->getStyle("D{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$sigSheet->setCellValue("E{$r}", $ev['verified_date'] ? date('M d, Y', strtotime($ev['verified_date'])) : '');
$sigSheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sigSheet->setCellValue("F{$r}", 'Date:');
$sigSheet->getStyle("F{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$sigSheet->setCellValue("G{$r}", $ev['approved_date'] ? date('M d, Y', strtotime($ev['approved_date'])) : '');
$sigSheet->getStyle("G{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$r += 2;

// second block: two roles (Noted/Conformed) - same pattern
// Row for signature blank space with top border
foreach (['C', 'E'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getRowDimension($r)->setRowHeight(45);
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
}
$r += 1;

// Row for signature bottom border line
foreach (['C', 'E'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
    $sigSheet->getRowDimension($r)->setRowHeight(2);
}
$r += 1;

// Row: label + name & signature
$sigSheet->setCellValue("B{$r}", 'Noted by:');
$sigSheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$notedName = ($ev['noted_by'] ?? '');
if (!empty($ev['noted_signature'])) {
    $notedName = $ev['noted_signature'];
}
$sigSheet->setCellValue("C{$r}", $notedName);
$sigSheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);

$sigSheet->setCellValue("D{$r}", 'Conformed by:');
$sigSheet->getStyle("D{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$conformedName = ($ev['conformed_by'] ?? '');
if (!empty($ev['conformed_signature'])) {
    $conformedName = $ev['conformed_signature'];
}
$sigSheet->setCellValue("E{$r}", $conformedName);
$sigSheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setWrapText(true);
$r += 1;

// Row for date blank space with top border
foreach (['C', 'E'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getRowDimension($r)->setRowHeight(30);
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
}
$r += 1;

// Row for date bottom border line
foreach (['C', 'E'] as $col) {
    $sigSheet->setCellValue("{$col}{$r}", '');
    $sigSheet->getStyle("{$col}{$r}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM)->setColor(new Color('FF333333'));
    $sigSheet->getRowDimension($r)->setRowHeight(2);
}
$r += 1;

// Row: label + date
$sigSheet->setCellValue("B{$r}", 'Date:');
$sigSheet->getStyle("B{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$sigSheet->setCellValue("C{$r}", $ev['noted_date'] ? date('M d, Y', strtotime($ev['noted_date'])) : '');
$sigSheet->getStyle("C{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

$sigSheet->setCellValue("D{$r}", 'Date:');
$sigSheet->getStyle("D{$r}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('FF333333'));
$sigSheet->setCellValue("E{$r}", $ev['conformed_date'] ? date('M d, Y', strtotime($ev['conformed_date'])) : '');
$sigSheet->getStyle("E{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);



// Output file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="evaluation-' . $evaluation_id . '.xlsx"');
header('Cache-Control: max-age=0');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
