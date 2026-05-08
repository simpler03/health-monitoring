<?php
/**
 * Export evaluation as Word-compatible HTML (.doc)
 * Produces a legal-sized document and includes logos/header (print-only style)
 */
require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/EvaluationManager.php';
require_once __DIR__ . '/config/FacilityManager.php';

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
        if (!$facility_manager->canUserAccessFacility($evaluation['facility_id'], $user['user_id'], $user['facility_name'] ?? null)) {
            header("Location: unauthorized.php");
            exit;
        }
    }

    $indicators = $evaluation_manager->getIndicatorsForEvaluation($evaluation_id);
    $facility = $facility_manager->getFacility($evaluation['facility_id']);
} catch (Exception $e) {
    error_log("Export Word Error: " . $e->getMessage());
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

// send headers for Word (.doc) - Word will accept HTML files served as application/msword
header("Content-Type: application/msword; charset=UTF-8");
header("Content-Disposition: attachment; filename=\"evaluation-{$evaluation_id}.doc\"");

// collect base64 images if available
$polPath = __DIR__ . '/images/province_of_leyte_logo.png';
$phoPath = __DIR__ . '/images/province_health_office_logo.png';
$polData = '';
$phoData = '';
if (file_exists($polPath)) {
    $polData = 'data:image/png;base64,' . base64_encode(file_get_contents($polPath));
}
if (file_exists($phoPath)) {
    $phoData = 'data:image/png;base64,' . base64_encode(file_get_contents($phoPath));
}

// allow adjustable page margin via ?margin=0.5 (in inches)
$pageMargin = isset($_GET['margin']) ? floatval($_GET['margin']) : 0.5;

echo '<!DOCTYPE html>' . "\n";
echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:w="urn:schemas-microsoft-com:office:word">' . "\n";
echo '<head><meta charset="utf-8"><meta name="ProgId" content="Word.Document"><title>Evaluation</title>' . "\n";
// styles: ensure print/header appear and page is legal landscape for Word
// include mso-page-orientation for Word compatibility

echo '<style>' . "\n";
echo '@page Section1 { size: 14in 8.5in; margin: ' . $pageMargin . 'in; mso-page-orientation: landscape; }' . "\n";
echo 'div.Section1 { page: Section1; margin: 0; }' . "\n";
echo 'body { margin: 0; padding: 0; }' . "\n";
echo 'table { border-collapse: collapse; width: 100%; }' . "\n";
echo 'th, td { border: 1px solid #444; padding: 6px; vertical-align: top; }' . "\n";
echo 'th { background: #e0e0e0; font-weight: bold; }' . "\n";
echo '.header-table { border: none; margin-bottom: 12px; }' . "\n";
echo '.header-table td { border: none; padding: 0; vertical-align: middle; }' . "\n";
echo '.logo-cell { text-align: center; width: 60px; }' . "\n";
echo '.tagline-cell { text-align: center; padding: 0 20px; }' . "\n";
echo '</style>' . "\n";
echo '</head><body>' . "\n";
echo '<div class="Section1">' . "\n";

// header - logos and tagline arranged in three rows
// first row: PoL logo at left
// second row: centered text
// third row: PHO logo at left
echo '<table class="header-table">' . "\n";
echo '<tr>' . "\n";
echo '<td class="logo-cell">';
if ($polData) echo '<img src="' . $polData . '" style="height:48px; max-width:120px; width:auto;">';
echo '</td>' . "\n";
echo '<td class="tagline-cell">';
echo 'Republic of the Philippines<br>Province of Leyte<br><strong>PROVINCIAL HEALTH OFFICE</strong><br>Candahug Palo, Leyte<br><br>';
echo '<h2 style="margin:6px 0; font-size:16px;">Health System Monitoring Evaluation</h2>';
echo '<div style="font-size:11px; color:#444;">Single-click = Complied (1) | Double-click = Not Complied (0)</div>';
echo '</td>' . "\n";
echo '<td class="logo-cell" style="text-align:right;">';
if ($phoData) echo '<img src="' . $phoData . '" style="height:48px; max-width:120px; width:auto;">';
echo '</td>' . "\n";
echo '</tr>' . "\n";
echo '</table>' . "\n";

// facility info
echo '<div style="margin-bottom:12px;">';
echo '<strong>Facility:</strong> ' . htmlspecialchars($facility['name'] ?? 'N/A') . ' &nbsp; | &nbsp; ';
echo '<strong>Date:</strong> ' . date('M d, Y', strtotime($evaluation['evaluation_date'])) . ' &nbsp; | &nbsp; ';
echo '<strong>Period:</strong> ' . htmlspecialchars($evaluation['evaluation_period']) . "</div>\n";

// table header
echo '<table>' . "\n";
echo '<thead><tr>';
echo '<th>Strategic Objectives</th>';
echo '<th>Key Performance Indicators</th>';
echo '<th>Target</th>';
echo '<th>Means of Verification</th>';
echo '<th>Score</th>';
echo '<th>% (Percentage)</th>';
echo '<th>Remarks</th>';
echo '</tr></thead>' . "\n";
echo '<tbody>' . "\n";

foreach ($organized_by_bb as $bb_name => $bb_scores) {
    // compute counts
    $totalKPIs = 0; $blockTotal = 0;
    foreach ($bb_scores as $s) {
        if (strpos($bb_name, 'Health Service Delivery') !== false &&
            $s['indicator_name'] === 'Percent of facilities meeting service standards') continue;
        $totalKPIs++;
        $sv = $s['score_value']; if ($sv !== null && (float)$sv >= 0.5) $blockTotal++;
    }
    // block header row
    echo '<tr><td colspan="7" style="background:#f0f0f0; font-weight:600;">' . htmlspecialchars($bb_name) . ' (' . $bb_scores[0]['weight_percentage'] . '%) - Score: ' . $blockTotal . ' / ' . $totalKPIs . '</td></tr>' . "\n";

    foreach ($bb_scores as $score) {
        if (strpos($bb_name, 'Health Service Delivery') !== false &&
            $score['indicator_name'] === 'Percent of facilities meeting service standards') continue;
        $sv = $score['score_value'];
        $display = ($sv === null) ? '' : (((float)$sv >= 0.5) ? '1' : '0');
        echo '<tr>';
        echo '<td>' . htmlspecialchars($score['objective_name']) . '</td>';
        echo '<td>' . htmlspecialchars($score['indicator_name']) . '</td>';
        echo '<td>' . htmlspecialchars($score['target_value'] ?? '-') . '</td>';
        echo '<td>' . nl2br(htmlspecialchars($score['means_of_verification'] ?? '')) . '</td>';
        // score first
        echo '<td style="text-align:center;">' . $display . '</td>';
        echo '<td>' . htmlspecialchars($score['percentage_value'] ?? '') . '</td>';
        echo '<td>' . htmlspecialchars($score['remarks'] ?? '') . '</td>';
        echo '</tr>' . "\n";
    }
}

echo '</tbody></table>' . "\n";

// signatures with combined name and e-signature
echo '<div style="margin-top:24px;">';

// Assessed by
echo '<div style="width:30%; display:inline-block; vertical-align:top;">';
echo '<div style="height:60px; border-top:1px solid #333; margin-top:40px; position:relative;">';
if (!empty($evaluation['assessed_esignature'])) {
    echo '<img src="' . $evaluation['assessed_esignature'] . '" style="position:absolute; top:5px; left:5px; height:45px; width:auto; background:transparent;">';
}
echo '</div>';
echo '<div style="text-align:center; margin-top:5px;">Assessed by: ' . htmlspecialchars($evaluation['assessed_by'] ?? '') . '</div>';
echo '</div>';

// Verified by
echo '<div style="width:30%; display:inline-block; margin-left:3%; vertical-align:top;">';
echo '<div style="height:60px; border-top:1px solid #333; margin-top:40px; position:relative;">';
if (!empty($evaluation['verified_esignature'])) {
    echo '<img src="' . $evaluation['verified_esignature'] . '" style="position:absolute; top:5px; left:5px; height:45px; width:auto; background:transparent;">';
}
echo '</div>';
echo '<div style="text-align:center; margin-top:5px;">Verified by: ' . htmlspecialchars($evaluation['verified_by'] ?? '') . '</div>';
echo '</div>';

// Approved by
echo '<div style="width:30%; display:inline-block; margin-left:3%; vertical-align:top;">';
echo '<div style="height:60px; border-top:1px solid #333; margin-top:40px; position:relative;">';
if (!empty($evaluation['approved_esignature'])) {
    echo '<img src="' . $evaluation['approved_esignature'] . '" style="position:absolute; top:5px; left:5px; height:45px; width:auto; background:transparent;">';
}
echo '</div>';
echo '<div style="text-align:center; margin-top:5px;">Approved by: ' . htmlspecialchars($evaluation['approved_by'] ?? '') . '</div>';
echo '</div>';

echo '</div>';

// Second row: Noted by and Conformed by
echo '<div style="margin-top:24px;">';

// Noted by
echo '<div style="width:45%; display:inline-block; vertical-align:top;">';
echo '<div style="height:60px; border-top:1px solid #333; margin-top:40px; position:relative;">';
if (!empty($evaluation['noted_esignature'])) {
    echo '<img src="' . $evaluation['noted_esignature'] . '" style="position:absolute; top:5px; left:5px; height:45px; width:auto; background:transparent;">';
}
echo '</div>';
echo '<div style="text-align:center; margin-top:5px;">Noted by: ' . htmlspecialchars($evaluation['noted_by'] ?? '') . '</div>';
echo '</div>';

// Conformed by
echo '<div style="width:45%; display:inline-block; margin-left:5%; vertical-align:top;">';
echo '<div style="height:60px; border-top:1px solid #333; margin-top:40px; position:relative;">';
if (!empty($evaluation['conformed_esignature'])) {
    echo '<img src="' . $evaluation['conformed_esignature'] . '" style="position:absolute; top:5px; left:5px; height:45px; width:auto; background:transparent;">';
}
echo '</div>';
echo '<div style="text-align:center; margin-top:5px;">Conformed by: ' . htmlspecialchars($evaluation['conformed_by'] ?? '') . '</div>';
echo '</div>';

echo '</div>' . "\n";

echo '</div>' . "\n";
echo '</body></html>' . "\n";

exit;
