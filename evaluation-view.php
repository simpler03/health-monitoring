<?php
/**
 * Evaluation View & Edit Page
 * Spreadsheet-style data entry interface
 * Health Performance Monitoring System
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/EvaluationManager.php';
require_once __DIR__ . '/config/FacilityManager.php';

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';

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

    // Get evaluation
    $evaluation = $evaluation_manager->getEvaluation($evaluation_id);
    if (!$evaluation) {
        header("Location: dashboard.php");
        exit;
    }

    // Convert BLOB esignature data to base64 for display
    $esignature_fields = ['assessed_esignature', 'verified_esignature', 'approved_esignature', 'noted_esignature', 'conformed_esignature'];
    foreach ($esignature_fields as $field) {
        if (!empty($evaluation[$field])) {
            $evaluation[$field] = 'data:image/png;base64,' . base64_encode($evaluation[$field]);
        }
    }

    // Get facility info early (needed for access control and edit permission checks)
    $facility = $facility_manager->getFacility($evaluation['facility_id']);

    // Facility access control: check if user has access to this evaluation's facility
    // Admin can access all, input/viewer can only access facilities they created, edited, or are assigned to
    if ($current_role !== 'admin' && in_array($current_role, ['viewer', 'input'])) {
        if (!$facility_manager->canUserAccessFacility($evaluation['facility_id'], $user['user_id'], $user['facility_name'] ?? null)) {
            header("Location: unauthorized.php");
            exit;
        }
    }

    // If evaluation is completed, strip edit parameter from URL
    if ($evaluation['status'] === 'completed' && !empty($_GET['edit'])) {
        header("Location: evaluation-view.php?id=" . (int)$evaluation_id);
        exit;
    }

    // Check if user is facility owner, editor, or holder
    $is_facility_owner = ($facility && ($facility['creator'] == $user['user_id'] || $facility['editor'] == $user['user_id'] || $facility['name'] === $user['facility_name']));

    // View-only by default; editing only when:
    // 1. User is not a viewer, AND
    // 2. Edit link is opened (?edit=1), AND
    // 3. Evaluation status is 'draft' (not completed), AND
    // 4. User is admin OR user is the facility owner, editor, or holder
    $can_edit = (($current_role !== 'viewer') && !empty($_GET['edit']) && $evaluation['status'] === 'draft' && ($current_role === 'admin' || $is_facility_owner));

    // Get all KPIs with optional saved scores (so view shows what was entered in edit/create)
    $indicators = $evaluation_manager->getIndicatorsForEvaluation($evaluation_id);
    $totals = $evaluation_manager->calculateTotals($evaluation_id);

} catch (Exception $e) {
    error_log("Evaluation View Error: " . $e->getMessage());
    header("Location: dashboard.php");
    exit;
}

// Organize indicators by building block (for table display)
$organized_by_bb = [];
foreach ($indicators as $row) {
    $bb = $row['building_block_name'];
    if (!isset($organized_by_bb[$bb])) {
        $organized_by_bb[$bb] = [];
    }
    $organized_by_bb[$bb][] = $row;
}

// Pagination: one building block per page
$bb_names = array_keys($organized_by_bb);
$total_blocks = count($bb_names);
$current_block_index = isset($_GET['block']) ? max(0, min((int)$_GET['block'], $total_blocks - 1)) : 0;
$current_block_name = $total_blocks > 0 ? $bb_names[$current_block_index] : null;
$base_url = 'evaluation-view.php?id=' . (int)$evaluation_id . ($can_edit ? '&edit=1' : '');

// optional print_all mode: render every building block sequentially for a single multi-page print
$printAll = isset($_GET['print_all']) && $_GET['print_all'] === '1';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evaluation - Web-Based Health Performance Scoring and Monitoring System</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
            padding: 20px;
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
        }

        .header-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        /* hide elements marked print-only on screen, show in print
           These elements (logos / Republic header) will appear only
           in print preview and when exporting to Word/print. */
        .print-only { display: none !important; }
        @media print {
            .print-only { display: block !important; }
            /* set legal page size for print preview - landscape */
            @page { size: 14in 8.5in; margin: 1in; }
        }
        .logos-row {
            justify-content: space-between;
        }
        .logos-row .logo-tagline {
            flex: 1;
            text-align: center;
        }
        .title-row {
            justify-content: space-between;
        }
        .logo-container,
        .logo-and-status,
        .header-title {
            display: flex;
            align-items: center;
        }
        .header-title { flex: 1; justify-content: center; flex-direction: column; text-align: center; }
        .header-row .logo {
            height: 60px;
            max-height: 80px;
        }
        .logo-and-status .status-badge {
            margin-left: 12px;
        }

        .header-row h1 {
            color: #333;
            font-size: 28px;
        }

        .back-link {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
            cursor: pointer;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        .facility-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            padding-top: 15px;
            border-top: 1px solid #eee;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-size: 12px;
            color: #999;
            text-transform: uppercase;
            font-weight: 600;
            margin-bottom: 4px;
        }

        .info-value {
            font-size: 15px;
            color: #333;
            font-weight: 500;
        }

        /* Spreadsheet Table */
        .spreadsheet-wrapper {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .spreadsheet-container {
            overflow-x: auto;
        }

        .spreadsheet {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .spreadsheet thead {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            position: sticky;
            top: 0;
            z-index: 10;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .spreadsheet th {
            padding: 6px 8px;
            /* center all headings as requested */
            text-align: center;
            font-weight: 600;
            border: 1px solid #ddd;
            white-space: nowrap;
            color: black;
        }
        /* center all cell values */
        .spreadsheet td {
            text-align: center;
        }

        .spreadsheet td {
            padding: 6px 8px;
            border: 1px solid #eee;
            background: white;
        }
        /* adjust column widths: reduce Strategic Objectives (1) and Target (3) so Means (4) can expand; Score & % tightened for paper savings */
        .spreadsheet th:nth-child(1), .spreadsheet td:nth-child(1) { width: 120px; }
        .spreadsheet th:nth-child(2), .spreadsheet td:nth-child(2) { width: 160px; }
        .spreadsheet th:nth-child(3), .spreadsheet td:nth-child(3) { width: 120px; }
        .spreadsheet th:nth-child(4), .spreadsheet td:nth-child(4) { width: 240px; }
        .spreadsheet th:nth-child(5), .spreadsheet td:nth-child(5) { width: 60px; }
        .spreadsheet th:nth-child(6), .spreadsheet td:nth-child(6) { width: 75px; }
        .spreadsheet th:nth-child(7), .spreadsheet td:nth-child(7) { width: 300px; }
        /* remarks textarea and hidden input: no width/height caps, vertical resize only */
        .remarks-input,
        .remarks-textarea {
            resize: vertical;
            height: auto;
        }

        .spreadsheet tbody tr {
            page-break-inside: avoid;
        }

        .spreadsheet tbody tr:nth-child(even) {
            background: #f9f9f9;
        }

        .spreadsheet tbody tr:hover {
            background: #f0f0f0;
        }

        /* Leadership and Governance criteria rows */
        .spreadsheet .lg-ref td {
            vertical-align: top;
            font-size: 14px;
            text-align: center;
        }
        .spreadsheet .lg-ref .target-cell,
        .spreadsheet .lg-ref td:nth-child(5) { white-space: normal; }
        /* A Functional Management Committee block: match reference layout (green border, grey background) */
        .spreadsheet .lg-mancom td { background: white; }
        .spreadsheet .lg-mancom .lg-mancom-title {
            background: white;
            font-weight: 600;
            vertical-align: middle;
            text-align: center;
        }
        /* Center KPI and Target cells in Management Committee section */
        .spreadsheet .lg-mancom td {
            text-align: center;
            vertical-align: middle;
        }
        .spreadsheet .lg-mancom .kpi-name,
        .spreadsheet .lg-mancom .target-cell {
            text-align: center;
            vertical-align: middle;
        }
        /* Grievance Committee block: outline like reference image */
        .spreadsheet .lg-grievance td {
            border: 2px solid #c62828;
            background: #fff8f8;
        }
        .spreadsheet .lg-grievance .kpi-name,
        .spreadsheet .lg-grievance .target-cell {
            border-left: 2px solid #c62828;
        }

        /* Row highlighting for building blocks */
        .bb-header-row {
            background: #f5f5f5 !important;
            font-weight: 700;
            color: #333;
        }

        .bb-header-row td {
            background: #f5f5f5;
            border-top: 2px solid #667eea;
        }

        .so-header-cell {
            background: #fafafa;
            font-weight: 600;
            color: #555;
            padding: 10px 12px !important;
        }

        .kpi-name {
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .target-cell {
            background: #f5f5f5;
            color: #555;
            font-weight: 500;
        }

        .spreadsheet td.rowspan-cell {
            vertical-align: top;
            background: #fafbfc;
        }
        .spreadsheet td.bb-cell {
            font-weight: 600;
            background: #e8eaf6;
        }
        .spreadsheet td.so-cell {
            background: #f5f5f5;
            font-weight: 500;
        }
        .spreadsheet td.kpi-cell {
            background: #fafafa;
        }

        /* Building block pagination */
        .block-pagination {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
            padding: 12px 16px;
            margin-bottom: 12px;
            background: #f5f7fa;
            border-radius: 8px;
            border: 1px solid #e0e4e8;
        }
        .block-pagination .block-nav {
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .block-pagination .block-nav a,
        .block-pagination .block-nav span {
            padding: 8px 14px;
            border-radius: 6px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            color: #667eea;
            background: white;
            border: 1px solid #ddd;
        }
        .block-pagination .block-nav a:hover {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .block-pagination .block-nav span.disabled {
            color: #999;
            cursor: not-allowed;
            border-color: #eee;
        }
        .block-pagination .block-pages {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            align-items: center;
        }
        .block-pagination .block-pages a {
            padding: 6px 12px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 13px;
            color: #555;
            background: white;
            border: 1px solid #ddd;
        }
        .block-pagination .block-pages a:hover {
            background: #e8eaee;
        }
        .block-pagination .block-pages a.current {
            background: #667eea;
            color: white;
            border-color: #667eea;
        }
        .block-pagination .block-label {
            font-size: 14px;
            color: #555;
            font-weight: 500;
        }

        /* Click-based scoring: Complied (1) = green, Not Complied (0) = red */
        .score-cell {
            cursor: pointer;
            min-width: 70px;
            text-align: center;
            font-weight: 600;
            transition: background 0.2s, color 0.2s;
            user-select: none;
        }
        .score-cell:hover {
            filter: brightness(1.05);
        }
        .score-cell.score-complied {
            background: #c8e6c9 !important;
            color: #2e7d32;
            border-color: #81c784;
        }
        .score-cell.score-not-complied {
            background: #ffcdd2 !important;
            color: #c62828;
            border-color: #e57373;
        }
        .score-cell.score-empty {
            background: #f5f5f5 !important;
            color: #9e9e9e;
        }
        .score-cell .score-label { font-size: 11px; text-transform: uppercase; margin-top: 2px; }


        /* Editable cells (legacy) */
        .editable-cell {
            cursor: pointer;
            position: relative;
            transition: background 0.2s;
        }
        .editable-cell:hover {
            background: #fff9e6 !important;
        }
        .editable-cell.empty {
            color: #ccc;
        }

        .cell-input {
            width: 100%;
            padding: 8px;
            border: 2px solid #667eea;
            border-radius: 4px;
            font-size: 13px;
            font-family: 'Segoe UI', sans-serif;
        }

        .cell-input:focus {
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        /* Summary row - ensure TOTAL is clearly visible */
        .total-row {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%) !important;
            color: #fff !important;
            font-weight: 700;
        }

        .total-row td {
            background: transparent !important;
            border-color: rgba(255,255,255,0.3);
            color: #fff !important;
            padding: 14px 12px;
            font-size: 15px;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        .total-row td:first-child {
            font-weight: 800;
            font-size: 16px;
        }
        @media print {
            body.print-all .total-row {
                display: none !important;
            }
        }

        /* Grand Total - outside table */
        .grand-total-box {
            background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            padding: 16px 24px;
            margin: 16px 0 0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
            font-size: 18px;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
        }
        .grand-total-box .label { font-weight: 800; }
        .grand-total-box .value { font-size: 22px; }

        /* Signature section */
        .signature-section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .signature-section h3 {
            color: #333;
            margin-bottom: 20px;
            font-size: 16px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }

        .signature-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            gap: 0;
        }

        .signature-label {
            font-weight: 600;
            color: #333;
            font-size: 13px;
            margin-bottom: 10px;
        }

        .signature-name-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding-bottom: 15px;
            border-bottom: 1px solid #ddd;
            margin-bottom: 15px;
        }

        .signature-name-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .signature-date-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .signature-date-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .signature-input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
        }

        .signature-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .signature-field {
            border-top: 1px solid #333;
            height: 60px;
            margin-top: 15px;
            display: flex;
            align-items: flex-end;
            padding-bottom: 5px;
            font-size: 11px;
            color: #666;
            text-align: center;
        }

        /* Controls */
        .controls {
            background: white;
            padding: 20px 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
        }

        .btn {
            padding: 0 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 36px;
            min-width: 80px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
        }

        .btn-secondary {
            background: #e0e0e0;
            color: #333;
        }

        .btn-secondary:hover {
            background: #d0d0d0;
        }

        .btn-danger {
            background: #f44336;
            color: white;
        }

        .btn-danger:hover {
            background: #d32f2f;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-draft {
            background: #fff3e0;
            color: #e65100;
        }
        .status-in_progress {
            background: #fff8e1;
            color: #f57c00;
        }
        .status-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .status-approved {
            background: #e3f2fd;
            color: #1565c0;
        }

        .loading-spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid #f3f3f3;
            border-top: 2px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .toast {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background: white;
            padding: 15px 20px;
            border-radius: 4px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            animation: slideIn 0.3s ease-out;
        }

        .toast.success {
            border-left: 4px solid #4caf50;
        }

        .toast.error {
            border-left: 4px solid #f44336;
        }

        .toast.info {
            border-left: 4px solid #2196f3;
        }

        @keyframes slideIn {
            from { transform: translateX(400px); }
            to { transform: translateX(0); }
        }

        .signature-print-table {
            width: 100%;
            border-collapse: collapse;
            display: none;
            margin-top: 20px;
        }
        .signature-print-table td {
            border: none;
            padding: 6px 8px;
            vertical-align: top;
            font-size: 13px;
        }
        .signature-print-table .role-label {
            font-weight: bold;
            font-size: 14px;
        }
        .signature-print-table .filled-blank {
            display: inline-block;
            width: 100%;
            border-bottom: 1px solid #000;
            padding-bottom: 2px;
        }

        @media print {
            .controls {
                display: none;
            }
            .toast {
                display: none !important;
            }
            body {
                background: white;
                padding: 0;
            }
            /* hide pagination controls and print-all link when printing */
            .block-pagination,
            .block-label > div,
            .back-link {
                display: none !important;
            }
            /* ensure an HSD standalone wrapper can push signatures to the next page */
            .hsd-standalone .signature-section {
                page-break-before: always;
                break-before: page;
            }
            /* when printing all blocks, don’t repeat the header row on every page */
            body.print-all .spreadsheet thead {
                display: table-row-group;
            }
            /* hide the normal signature grid and print divs */
            .signature-section .signature-grid { display: none !important; }
            .signature-print { display: none !important; }
            /* display table layout */
            .signature-print-table { display: table !important; }
            /* ensure signature section always starts on new page and is not split */
            .signature-section { page-break-before: always; page-break-inside: avoid; }
            /* hide completion notice */
            .completion-lock { display: none !important; }
            /* ensure signature image is on top and name on bottom for print */
            .signature-text-layer {
                top: auto !important;
                bottom: 0 !important;
                height: 50% !important;
            }
            .signature-image-layer {
                z-index: 3 !important;
                height: 50% !important;
                opacity: 1 !important;
            }
        }

        /* E-signature styles */
        .signature-esignature-section {
            display: flex;
            flex-direction: column;
            gap: 8px;
            padding: 15px;
            border-top: 1px solid #ddd;
            margin-top: 15px;
        }

        .signature-esignature-label {
            font-size: 12px;
            color: #666;
            font-weight: 500;
        }

        .signature-preview {
            width: 100%;
            height: 60px;
            object-fit: contain;
            border: 1px solid #ddd;
            background: white;
        }

        .btn-sm {
            padding: 0 12px;
            font-size: 12px;
            margin-right: 5px;
            height: 32px;
            min-width: 70px;
        }

        /* Modal styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.7);
        }

        .modal-content {
            background-color: #fefefe;
            margin: 5% auto;
            padding: 0;
            border: 1px solid #888;
            width: 90%;
            max-width: 600px;
            border-radius: 8px;
        }

        .modal-header {
            padding: 15px 20px;
            background: #667eea;
            color: white;
            border-radius: 8px 8px 0 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .modal-header h4 {
            margin: 0;
        }

        .modal-header .close {
            color: white;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
        }

        .modal-body {
            padding: 20px;
            text-align: center;
        }

        .modal-footer {
            padding: 15px 20px;
            background: #f5f5f5;
            border-radius: 0 0 8px 8px;
            text-align: right;
        }

        /* Layered signature display */
        .signature-layered-container {
            position: relative;
            width: 100%;
            height: 60px;
            margin-top: 15px;
            border-bottom: 1px solid #333;
        }

        .signature-text-layer {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 600;
            color: #333;
            z-index: 2;
            pointer-events: none;
            text-decoration: underline;
        }

        .signature-image-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 50%;
            z-index: 3;
            object-fit: contain;
        }

        /* Approval Modal Styles */
        .approval-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .approval-modal-content {
            background-color: #fefefe;
            margin: 10% auto;
            padding: 30px;
            border-radius: 8px;
            max-width: 500px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.3);
            text-align: center;
        }

        .approval-modal h2 {
            color: #333;
            margin-bottom: 20px;
            font-size: 24px;
        }

        .approval-modal p {
            color: #666;
            margin-bottom: 30px;
            font-size: 16px;
        }

        .approval-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .approval-btn {
            padding: 12px 28px;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            min-width: 140px;
            justify-content: center;
        }

        .approval-btn-approved {
            background: #4caf50;
            color: white;
        }

        .approval-btn-approved:hover {
            background: #45a049;
            transform: scale(1.05);
        }

        .approval-btn-rejected {
            background: #f44336;
            color: white;
        }

        .approval-btn-rejected:hover {
            background: #da190b;
            transform: scale(1.05);
        }

        .approval-btn-cancel {
            background: #9e9e9e;
            color: white;
        }

        .approval-btn-cancel:hover {
            background: #757575;
        }

        .approval-btn-icon {
            font-size: 20px;
        }

        /* Approval Status Label Styles */
        .approval-status-label {
            display: inline-block;
            padding: 8px 12px;
            border-radius: 4px;
            font-size: 13px;
            font-weight: 600;
        }

        .approval-status-pending {
            background: #fff3cd;
            color: #856404;
            border-left: 3px solid #ffc107;
        }

        .approval-status-approved {
            background: #d4edda;
            color: #155724;
            border-left: 3px solid #28a745;
        }

        .approval-status-rejected {
            background: #f8d7da;
            color: #721c24;
            border-left: 3px solid #dc3545;
        }
    </style>
</head>
<body class="<?php echo $printAll ? 'print-all' : ''; ?>">
    <div class="container">
        <!-- Header -->
        <div class="header-section">
            <!-- first header row: logos left and right with tagline centred -->
        <div class="header-row logos-row" style="justify-content: center; gap: 20px;">
                <div class="logo-container" style="flex-shrink: 0;">
                    <img src="images/province_of_leyte_logo.png" alt="PoL Logo" class="logo pol-logo">
                </div>
                <div class="logo-tagline" style="text-align:center; font-size:12px; color:#666; flex: 0 1 auto;">
                    Republic of the Philippines<br>
                    Province of Leyte<br>
                    PROVINCIAL HEALTH OFFICE<br>
                    Candahug Palo, Leyte
                </div>
                <div class="logo-container" style="flex-shrink: 0;">
                    <img src="images/province_health_office_logo.jpg" alt="PHO Logo" class="logo pho-logo">
                </div>
        </div>

        <!-- second header row: evaluation title/instructions and status badge -->
        <div class="header-row title-row">
                <div class="header-title">
                    <h1>Province-wide Local Health System Monitoring Tool</h1>
                    <p style="font-size: 13px; color: #666; margin-top: 6px;"><strong style="color: #2e7d32;">Single-click</strong> = Complied (1) &nbsp;|&nbsp; <strong style="color: #c62828;">Double-click</strong> = Not Complied (0). Totals update automatically.</p>
                </div>
                
        </div>

            <div class="facility-info">
                <div class="info-item">
                    <span class="info-label">Facility Name</span>
                    <span class="info-value"><?php echo htmlspecialchars($facility['name'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Evaluation Date</span>
                    <span class="info-value"><?php echo date('M d, Y', strtotime($evaluation['evaluation_date'])); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Period</span>
                    <span class="info-value"><?php echo htmlspecialchars($evaluation['evaluation_period']); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Province</span>
                    <span class="info-value"><?php echo htmlspecialchars($facility['province'] ?? 'N/A'); ?></span>
                </div>
                <div class="info-item">
                    <span class="info-label">Status</span>
                    <span class="status-badge status-<?php echo $evaluation['status']; ?>">
                        <?php echo ucfirst($evaluation['status']); ?>
                    </span>
                </div>
                
                <div class="info-item">
                    <span class="info-label">Approval</span>
                    <?php if ($evaluation['status'] === 'completed'): ?>
                        <?php 
                            $approved = $evaluation['approved'] ?? 0;
                        ?>
                        <?php if ($current_role === 'viewer'): ?>
                            <?php if ($approved == 1): ?>
                                <span class="approval-status-label approval-status-approved">✓ Approved</span>
                            <?php elseif ($approved == 2): ?>
                                <span class="approval-status-label approval-status-rejected">✗ Rejected</span>
                            <?php else: ?>
                                <button id="approve-btn" class="btn btn-primary" onclick="openApprovalModal()" style="padding: 8px 16px; font-size: 13px;">Review & Approve</button>
                            <?php endif; ?>
                        <?php else: ?>
                            <?php if ($approved == 1): ?>
                                <span class="approval-status-label approval-status-approved">✓ Approved</span>
                            <?php elseif ($approved == 2): ?>
                                <span class="approval-status-label approval-status-rejected">✗ Rejected</span>
                            <?php else: ?>
                                <span class="approval-status-label approval-status-pending">Pending Review</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>

            </div>
        </div>


        <!-- Completion Lock Notice -->
        <?php if ($evaluation['status'] === 'completed'): ?>
            <div class="completion-lock" style="margin-bottom: 16px; padding: 12px 16px; background-color: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">
                <strong style="color: #856404;">⚠️ This evaluation has been submitted and is now locked.</strong> 
                <p style="margin: 6px 0 0 0; color: #856404; font-size: 13px;">Editing and auto-save are disabled. Contact an administrator if you need to make changes.</p>
            </div>
        <?php endif; ?>

        <!-- Spreadsheet -->
        <div class="spreadsheet-wrapper <?php echo (!$printAll && ($current_block_name === 'Health Service Delivery' || $current_block_name === 'V. Health Service Delivery')) ? 'hsd-standalone' : ''; ?>">
            <?php if ($total_blocks > 1): ?>
            <div class="block-pagination">
                <div class="block-nav">
                    <?php if ($current_block_index > 0): ?>
                        <a href="<?php echo htmlspecialchars($base_url . '&block=' . ($current_block_index - 1)); ?>">← Previous</a>
                    <?php else: ?>
                        <span class="disabled">← Previous</span>
                    <?php endif; ?>
                    <?php if ($current_block_index < $total_blocks - 1): ?>
                        <a href="<?php echo htmlspecialchars($base_url . '&block=' . ($current_block_index + 1)); ?>">Next →</a>
                    <?php else: ?>
                        <span class="disabled">Next →</span>
                    <?php endif; ?>
                </div>
                <div class="block-label">
                    <?php if ($printAll): ?>
                        Building blocks: All
                    <?php else: ?>
                        Building block <?php echo $current_block_index + 1; ?> of <?php echo $total_blocks; ?>: <?php echo htmlspecialchars($current_block_name); ?>
                    <?php endif; ?>
                </div>
                <div class="block-pages">
                    <?php for ($i = 0; $i < $total_blocks; $i++): ?>
                        <a href="<?php echo htmlspecialchars($base_url . '&block=' . $i); ?>" class="<?php echo (!$printAll && $i === $current_block_index) ? 'current' : ''; ?>"><?php echo $i + 1; ?></a>
                    <?php endfor; ?>
                    <a href="<?php echo htmlspecialchars($base_url . '&print_all=1'); ?>" class="<?php echo $printAll ? 'current' : ''; ?>">All</a>
                </div>
            </div>
            <?php endif; ?>
            <div class="spreadsheet-container" data-block-index="<?php echo $current_block_index; ?>">
                <table class="spreadsheet">
                    <thead>
                        <tr>
                            <th>Strategic Objectives</th>
                            <th>Key Performance Indicators</th>
                            <th>Target</th>
                            <th>Means of Verification</th>
                            <th>Score</th>
                            <th>% (Percentage)</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($organized_by_bb)): ?>
                            <tr><td colspan="7" style="padding: 24px; text-align: center; color: #666;">No indicators defined in the database. Add building blocks and KPIs to start scoring.</td></tr>
                        <?php else: ?>
                        <?php $firstBlock = true; ?>
                        <?php foreach ($organized_by_bb as $bb_name => $bb_scores): ?>
                            <?php if (!$printAll && $bb_name !== $current_block_name) continue; ?>
                            <?php if ($printAll && !$firstBlock): ?>
                                <!-- no page break between blocks any more -->
                            <?php endif; ?>
                            <?php $firstBlock = false; ?>

                            <?php
                                // calculate totals for the block ahead of rendering
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
                            ?>

                            <!-- block header row spanning all 7 columns -->
                            <tr class="bb-header-row" data-block="<?php echo htmlspecialchars($bb_name); ?>" data-total-kpis="<?php echo $totalKPIs; ?>">
                                <td colspan="7">
                                    <?php echo htmlspecialchars($bb_name); ?><br>
                                    <small style="font-weight: normal;">(<?php echo htmlspecialchars($bb_scores[0]['weight_percentage'] ?? ''); ?>%)</small>
                                </td>
                            </tr>

                            <?php
                            // Preprocess for rowspan calculation
                            // Hierarchical merging logic
                            $rowCount = count($bb_scores);
                            $i = 0;
                            while ($i < $rowCount) {
                                $score = $bb_scores[$i];
                                if (strpos($bb_name, 'Health Service Delivery') !== false && $score['indicator_name'] === 'Percent of facilities meeting service standards') {
                                    $i++;
                                    continue;
                                }
                                // Find how many rows share the same Strategic Objective
                                $so = $score['objective_name'];
                                $so_span = 1;
                                while (($i + $so_span) < $rowCount && $bb_scores[$i + $so_span]['objective_name'] === $so) {
                                    $so_span++;
                                }
                                $j = $i;
                                while ($j < $i + $so_span) {
                                    $score_j = $bb_scores[$j];
                                    // Find how many rows share the same KPI within this SO
                                    $kpi = $score_j['indicator_name'];
                                    $kpi_span = 1;
                                    while (($j + $kpi_span) < $i + $so_span && $bb_scores[$j + $kpi_span]['indicator_name'] === $kpi) {
                                        $kpi_span++;
                                    }
                                    $k = $j;
                                    while ($k < $j + $kpi_span) {
                                        $score_k = $bb_scores[$k];
                                        // Find how many rows share the same Target within this KPI
                                        $target = $score_k['target_value'];
                                        $target_span = 1;
                                        while (($k + $target_span) < $j + $kpi_span && $bb_scores[$k + $target_span]['target_value'] === $target) {
                                            $target_span++;
                                        }
                                        $m = $k;
                                        while ($m < $k + $target_span) {
                                            $score_m = $bb_scores[$m];
                                            // Find how many rows share the same Means of Verification within this Target
                                            $mov = $score_m['means_of_verification'];
                                            $mov_span = 1;
                                            while (($m + $mov_span) < $k + $target_span && $bb_scores[$m + $mov_span]['means_of_verification'] === $mov) {
                                                $mov_span++;
                                            }
                                            echo '<tr';
                                            if ($score_m['objective_name'] === 'Health Information Systems') echo ' data-objective="Health Information Systems"';
                                            echo '>';
                                            // Strategic Objectives
                                            if ($m == $i) {
                                                echo '<td class="so-header-cell" rowspan="' . $so_span . '">' . htmlspecialchars($so) . '</td>';
                                            }
                                            // Key Performance Indicators
                                            if ($m == $j) {
                                                echo '<td class="kpi-name" rowspan="' . $kpi_span . '">' . htmlspecialchars($kpi) . '</td>';
                                            }
                                            // Target
                                            if ($m == $k) {
                                                echo '<td class="target-cell" rowspan="' . $target_span . '">' . htmlspecialchars($target ?? '-') . '</td>';
                                            }
                                            // Means of Verification
                                            if ($mov_span > 0 && $m == $m) {
                                                echo '<td rowspan="' . $mov_span . '"><small>' . nl2br(htmlspecialchars($mov ?? '', ENT_QUOTES, 'UTF-8')) . '</small></td>';
                                            }
                                            // Score
                                            $sv = $score_m['score_value'];
                                            $is1 = ($sv !== null && (float)$sv >= 0.5);
                                            $cls = $sv === null ? 'score-empty' : ($is1 ? 'score-complied' : 'score-not-complied');
                                            $disp = $sv === null ? '—' : ($is1 ? '1' : '0');
                                            echo '<td class="score-cell ' . $cls . '" data-block="' . htmlspecialchars($bb_name) . '" data-kpi-id="' . (int)$score_m['key_performance_indicator_id'] . '" data-score="' . ($sv === null ? '' : (int)$is1) . '" title="Single-click = Complied (1), Double-click = Not Complied (0)">' . $disp . '<br><span class="score-label">' . ($sv === null ? 'Click to toggle' : ($is1 ? 'Complied' : 'Not Complied')) . '</span></td>';
                                            // Percentage
                                            $pct = $score_m['percentage_value'];
                                            $pctDisplay = ($pct !== null && $pct !== '') ? (float)$pct : null;
                                            echo '<td class="percentage-cell"><span class="percentage-label" data-value="' . htmlspecialchars($pctDisplay !== null ? $pctDisplay : '') . '">' . ($pctDisplay !== null ? (round($pctDisplay,2) . '%') : '') . '</span></td>';
                                            // Remarks
                                            echo '<td class="remarks-cell">';
                                            if ($can_edit) {
                                                echo '<textarea class="cell-input remarks-textarea" rows="1">' . htmlspecialchars($score_m['remarks'] ?? '') . '</textarea>';
                                                echo '<input type="hidden" class="remarks-input" value="' . htmlspecialchars($score_m['remarks'] ?? '') . '">';
                                            } else {
                                                echo nl2br(htmlspecialchars($score_m['remarks'] ?? ''));
                                            }
                                            echo '</td>';
                                            echo '</tr>';
                                            $m += $mov_span;
                                        }
                                        $k += $target_span;
                                    }
                                    $j += $kpi_span;
                                }
                                $i += $so_span;
                            }
                            ?>

                            <!-- block footer: moved block score & percentage here -->
                            <tr class="bb-footer-row" data-block="<?php echo htmlspecialchars($bb_name); ?>">
                                <td colspan="7" style="text-align: right; padding: 8px 12px; background: #fafafa;">
                                    <span style="margin-right:14px;">Block Score: <strong class="block-score" data-block="<?php echo htmlspecialchars($bb_name); ?>"><?php echo $blockTotal; ?></strong> / <?php echo $totalKPIs; ?></span>
                                    <span>Percentage: <label class="block-percentage" data-block="<?php echo htmlspecialchars($bb_name); ?>"><?php $blockPct = $totalKPIs ? round(($blockTotal / $totalKPIs) * 100, 2) : 0; echo $blockPct . '%'; ?></label></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>

                        <!-- Summary Row (real-time updated) -->
                        <?php if (!$printAll): ?>
                        <tr class="total-row">
                            <td>TOTAL</td>
                            <td colspan="3"></td>
                            <td id="total-score"><?php echo $totals['total_score'] ?? '0'; ?></td>
                            <td colspan="2"></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="grand-total-box">
                <span class="label">Grand Total</span>
                <span class="value" id="grand-total-score"><?php echo $totals['total_score'] ?? '0'; ?></span>
            </div>
            <?php if ($total_blocks > 1): ?>
            <div class="block-pagination" style="margin-top: 12px; margin-bottom: 0;">
                <div class="block-nav">
                    <?php if ($current_block_index > 0): ?>
                        <a href="<?php echo htmlspecialchars($base_url . '&block=' . ($current_block_index - 1)); ?>">← Previous</a>
                    <?php else: ?>
                        <span class="disabled">← Previous</span>
                    <?php endif; ?>
                    <?php if ($current_block_index < $total_blocks - 1): ?>
                        <a href="<?php echo htmlspecialchars($base_url . '&block=' . ($current_block_index + 1)); ?>">Next →</a>
                    <?php else: ?>
                        <span class="disabled">Next →</span>
                    <?php endif; ?>
                </div>
                <div class="block-label">
                    <?php if ($printAll): ?>
                        Building blocks: All
                    <?php else: ?>
                        Building block <?php echo $current_block_index + 1; ?> of <?php echo $total_blocks; ?>: <?php echo htmlspecialchars($current_block_name); ?>
                    <?php endif; ?>
                </div>
                <div class="block-pages">
                    <?php for ($i = 0; $i < $total_blocks; $i++): ?>
                        <a href="<?php echo htmlspecialchars($base_url . '&block=' . $i); ?>" class="<?php echo (!$printAll && $i === $current_block_index) ? 'current' : ''; ?>"><?php echo $i + 1; ?></a>
                    <?php endfor; ?>
                    <a href="<?php echo htmlspecialchars($base_url . '&print_all=1'); ?>" class="<?php echo $printAll ? 'current' : ''; ?>">All</a>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <h3>Approval & Signatures</h3>
            
            <div class="signature-grid">
                <div class="signature-box">
                    <label class="signature-label">
                        <input type="checkbox" id="assessed-check" <?php echo !empty($evaluation['assessed_by']) ? 'checked' : ''; ?>>
                        Assessed by:
                    </label>
                    <div class="signature-name-section">
                        <div class="signature-name-label">Name/Signature:</div>
                        <input type="text" class="signature-input" id="assessed-by" 
                               value="<?php echo htmlspecialchars($evaluation['assessed_by'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-date-section">
                        <div class="signature-date-label">Date:</div>
                        <input type="date" class="signature-input" id="assessed-date"
                               value="<?php echo htmlspecialchars($evaluation['assessed_date'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-esignature-section">
                        <div class="signature-esignature-label">Electronic Signature:</div>
                        <input type="file" class="signature-file-input" id="assessed-esignature-file" accept="image/*" style="display:none;">
                        <div>
                            <button class="btn btn-secondary btn-sm" onclick="chooseFile('assessed')" <?php echo $can_edit ? '' : 'disabled'; ?>>Open Image</button>
                            <button class="btn btn-secondary btn-sm" onclick="cropSignature('assessed')" id="assessed-crop-btn" style="display:none;" <?php echo $can_edit ? '' : 'disabled'; ?>>Crop</button>
                            <button class="btn btn-danger btn-sm" onclick="removeSignature('assessed')" id="assessed-remove-btn" style="display:<?php echo (!empty($evaluation['assessed_esignature']) ? 'inline-block' : 'none'); ?>;" <?php echo $can_edit ? '' : 'disabled'; ?>>Remove</button>
                        </div>
                        <img id="assessed-esignature-preview" class="signature-preview" style="display:<?php echo (!empty($evaluation['assessed_esignature']) ? 'block' : 'none'); ?>;" src="<?php echo htmlspecialchars($evaluation['assessed_esignature'] ?? ''); ?>">
                    </div>
                    <!-- Layered signature display -->
                    <div class="signature-layered-container">
                        <div class="signature-text-layer" id="assessed-text-layer">
                            <?php echo htmlspecialchars($evaluation['assessed_by'] ?? ''); ?>
                        </div>
                        <img class="signature-image-layer" id="assessed-image-layer" 
                             style="display:<?php echo (!empty($evaluation['assessed_esignature']) ? 'block' : 'none'); ?>;" 
                             src="<?php echo htmlspecialchars($evaluation['assessed_esignature'] ?? ''); ?>">
                    </div>
                    <div class="signature-print" id="assessed-print" style="display:none; font-weight:600;">
                        <?php if (!empty($evaluation['assessed_by'])): ?>
                            <?php echo htmlspecialchars($evaluation['assessed_by']); ?><br><?php if (!empty($evaluation['assessed_date'])): ?><?php echo date('M d, Y', strtotime($evaluation['assessed_date'])); ?><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="signature-field"></div>
                </div>

                <div class="signature-box">
                    <label class="signature-label">
                        <input type="checkbox" id="verified-check" <?php echo !empty($evaluation['verified_by']) ? 'checked' : ''; ?>>
                        Verified by:
                    </label>
                    <div class="signature-name-section">
                        <div class="signature-name-label">Name/Signature:</div>
                        <input type="text" class="signature-input" id="verified-by"
                               value="<?php echo htmlspecialchars($evaluation['verified_by'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-date-section">
                        <div class="signature-date-label">Date:</div>
                        <input type="date" class="signature-input" id="verified-date"
                               value="<?php echo htmlspecialchars($evaluation['verified_date'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-esignature-section">
                        <div class="signature-esignature-label">Electronic Signature:</div>
                        <input type="file" class="signature-file-input" id="verified-esignature-file" accept="image/*" style="display:none;">
                        <div>
                            <button class="btn btn-secondary btn-sm" onclick="chooseFile('verified')" <?php echo $can_edit ? '' : 'disabled'; ?>>Open Image</button>
                            <button class="btn btn-secondary btn-sm" onclick="cropSignature('verified')" id="verified-crop-btn" style="display:none;" <?php echo $can_edit ? '' : 'disabled'; ?>>Crop</button>
                            <button class="btn btn-danger btn-sm" onclick="removeSignature('verified')" id="verified-remove-btn" style="display:<?php echo (!empty($evaluation['verified_esignature']) ? 'inline-block' : 'none'); ?>;" <?php echo $can_edit ? '' : 'disabled'; ?>>Remove</button>
                        </div>
                        <img id="verified-esignature-preview" class="signature-preview" style="display:<?php echo (!empty($evaluation['verified_esignature']) ? 'block' : 'none'); ?>;" src="<?php echo htmlspecialchars($evaluation['verified_esignature'] ?? ''); ?>">
                    </div>
                    <!-- Layered signature display -->
                    <div class="signature-layered-container">
                        <div class="signature-text-layer" id="verified-text-layer">
                            <?php echo htmlspecialchars($evaluation['verified_by'] ?? ''); ?>
                        </div>
                        <img class="signature-image-layer" id="verified-image-layer" 
                             style="display:<?php echo (!empty($evaluation['verified_esignature']) ? 'block' : 'none'); ?>;" 
                             src="<?php echo htmlspecialchars($evaluation['verified_esignature'] ?? ''); ?>">
                    </div>
                    <div class="signature-print" id="verified-print" style="display:none; font-weight:600;">
                        <?php if (!empty($evaluation['verified_by'])): ?>
                            <?php echo htmlspecialchars($evaluation['verified_by']); ?><br><?php if (!empty($evaluation['verified_date'])): ?><?php echo date('M d, Y', strtotime($evaluation['verified_date'])); ?><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="signature-field"></div>
                </div>

                <div class="signature-box">
                    <label class="signature-label">
                        <input type="checkbox" id="approved-check" <?php echo !empty($evaluation['approved_by']) ? 'checked' : ''; ?>>
                        Approved by:
                    </label>
                    <div class="signature-name-section">
                        <div class="signature-name-label">Name/Signature:</div>
                        <input type="text" class="signature-input" id="approved-by"
                               value="<?php echo htmlspecialchars($evaluation['approved_by'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-date-section">
                        <div class="signature-date-label">Date:</div>
                        <input type="date" class="signature-input" id="approved-date"
                               value="<?php echo htmlspecialchars($evaluation['approved_date'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-esignature-section">
                        <div class="signature-esignature-label">Electronic Signature:</div>
                        <input type="file" class="signature-file-input" id="approved-esignature-file" accept="image/*" style="display:none;">
                        <div>
                            <button class="btn btn-secondary btn-sm" onclick="chooseFile('approved')" <?php echo $can_edit ? '' : 'disabled'; ?>>Open Image</button>
                            <button class="btn btn-secondary btn-sm" onclick="cropSignature('approved')" id="approved-crop-btn" style="display:none;" <?php echo $can_edit ? '' : 'disabled'; ?>>Crop</button>
                            <button class="btn btn-danger btn-sm" onclick="removeSignature('approved')" id="approved-remove-btn" style="display:<?php echo (!empty($evaluation['approved_esignature']) ? 'inline-block' : 'none'); ?>;" <?php echo $can_edit ? '' : 'disabled'; ?>>Remove</button>
                        </div>
                        <img id="approved-esignature-preview" class="signature-preview" style="display:<?php echo (!empty($evaluation['approved_esignature']) ? 'block' : 'none'); ?>;" src="<?php echo htmlspecialchars($evaluation['approved_esignature'] ?? ''); ?>">
                    </div>
                    <!-- Layered signature display -->
                    <div class="signature-layered-container">
                        <div class="signature-text-layer" id="approved-text-layer">
                            <?php echo htmlspecialchars($evaluation['approved_by'] ?? ''); ?>
                        </div>
                        <img class="signature-image-layer" id="approved-image-layer" 
                             style="display:<?php echo (!empty($evaluation['approved_esignature']) ? 'block' : 'none'); ?>;" 
                             src="<?php echo htmlspecialchars($evaluation['approved_esignature'] ?? ''); ?>">
                    </div>
                    <div class="signature-print" id="approved-print" style="display:none; font-weight:600;">
                        <?php if (!empty($evaluation['approved_by'])): ?>
                            <?php echo htmlspecialchars($evaluation['approved_by']); ?><br><?php if (!empty($evaluation['approved_date'])): ?><?php echo date('M d, Y', strtotime($evaluation['approved_date'])); ?><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="signature-field"></div>
                </div>

                <div class="signature-box">
                    <label class="signature-label">
                        <input type="checkbox" id="noted-check" <?php echo !empty($evaluation['noted_by']) ? 'checked' : ''; ?>>
                        Noted by:
                    </label>
                    <div class="signature-name-section">
                        <div class="signature-name-label">Name/Signature:</div>
                        <input type="text" class="signature-input" id="noted-by"
                               value="<?php echo htmlspecialchars($evaluation['noted_by'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-date-section">
                        <div class="signature-date-label">Date:</div>
                        <input type="date" class="signature-input" id="noted-date"
                               value="<?php echo htmlspecialchars($evaluation['noted_date'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-esignature-section">
                        <div class="signature-esignature-label">Electronic Signature:</div>
                        <input type="file" class="signature-file-input" id="noted-esignature-file" accept="image/*" style="display:none;">
                        <div>
                            <button class="btn btn-secondary btn-sm" onclick="chooseFile('noted')" <?php echo $can_edit ? '' : 'disabled'; ?>>Open Image</button>
                            <button class="btn btn-secondary btn-sm" onclick="cropSignature('noted')" id="noted-crop-btn" style="display:none;" <?php echo $can_edit ? '' : 'disabled'; ?>>Crop</button>
                            <button class="btn btn-danger btn-sm" onclick="removeSignature('noted')" id="noted-remove-btn" style="display:<?php echo (!empty($evaluation['noted_esignature']) ? 'inline-block' : 'none'); ?>;" <?php echo $can_edit ? '' : 'disabled'; ?>>Remove</button>
                        </div>
                        <img id="noted-esignature-preview" class="signature-preview" style="display:<?php echo (!empty($evaluation['noted_esignature']) ? 'block' : 'none'); ?>;" src="<?php echo htmlspecialchars($evaluation['noted_esignature'] ?? ''); ?>">
                    </div>
                    <!-- Layered signature display -->
                    <div class="signature-layered-container">
                        <div class="signature-text-layer" id="noted-text-layer">
                            <?php echo htmlspecialchars($evaluation['noted_by'] ?? ''); ?>
                        </div>
                        <img class="signature-image-layer" id="noted-image-layer" 
                             style="display:<?php echo (!empty($evaluation['noted_esignature']) ? 'block' : 'none'); ?>;" 
                             src="<?php echo htmlspecialchars($evaluation['noted_esignature'] ?? ''); ?>">
                    </div>
                    <div class="signature-print" id="noted-print" style="display:none; font-weight:600;">
                        <?php if (!empty($evaluation['noted_by'])): ?>
                            <?php echo htmlspecialchars($evaluation['noted_by']); ?><br><?php if (!empty($evaluation['noted_date'])): ?><?php echo date('M d, Y', strtotime($evaluation['noted_date'])); ?><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="signature-field"></div>
                </div>

                <div class="signature-box">
                    <label class="signature-label">
                        <input type="checkbox" id="conformed-check" <?php echo !empty($evaluation['conformed_by']) ? 'checked' : ''; ?>>
                        Confirmed by:
                    </label>
                    <div class="signature-name-section">
                        <div class="signature-name-label">Name/Signature:</div>
                        <input type="text" class="signature-input" id="conformed-by"
                               value="<?php echo htmlspecialchars($evaluation['conformed_by'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-date-section">
                        <div class="signature-date-label">Date:</div>
                        <input type="date" class="signature-input" id="conformed-date"
                               value="<?php echo htmlspecialchars($evaluation['conformed_date'] ?? ''); ?>"
                               <?php echo $can_edit ? '' : 'disabled'; ?>>
                    </div>
                    <div class="signature-esignature-section">
                        <div class="signature-esignature-label">Electronic Signature:</div>
                        <input type="file" class="signature-file-input" id="conformed-esignature-file" accept="image/*" style="display:none;">
                        <div>
                            <button class="btn btn-secondary btn-sm" onclick="chooseFile('conformed')" <?php echo $can_edit ? '' : 'disabled'; ?>>Open Image</button>
                            <button class="btn btn-secondary btn-sm" onclick="cropSignature('conformed')" id="conformed-crop-btn" style="display:none;" <?php echo $can_edit ? '' : 'disabled'; ?>>Crop</button>
                            <button class="btn btn-danger btn-sm" onclick="removeSignature('conformed')" id="conformed-remove-btn" style="display:<?php echo (!empty($evaluation['conformed_esignature']) ? 'inline-block' : 'none'); ?>;" <?php echo $can_edit ? '' : 'disabled'; ?>>Remove</button>
                        </div>
                        <img id="conformed-esignature-preview" class="signature-preview" style="display:<?php echo (!empty($evaluation['conformed_esignature']) ? 'block' : 'none'); ?>;" src="<?php echo htmlspecialchars($evaluation['conformed_esignature'] ?? ''); ?>">
                    </div>
                    <!-- Layered signature display -->
                    <div class="signature-layered-container">
                        <div class="signature-text-layer" id="conformed-text-layer">
                            <?php echo htmlspecialchars($evaluation['conformed_by'] ?? ''); ?>
                        </div>
                        <img class="signature-image-layer" id="conformed-image-layer" 
                             style="display:<?php echo (!empty($evaluation['conformed_esignature']) ? 'block' : 'none'); ?>;" 
                             src="<?php echo htmlspecialchars($evaluation['conformed_esignature'] ?? ''); ?>">
                    </div>
                    <div class="signature-print" id="conformed-print" style="display:none; font-weight:600;">
                        <?php if (!empty($evaluation['conformed_by'])): ?>
                            <?php echo htmlspecialchars($evaluation['conformed_by']); ?><br><?php if (!empty($evaluation['conformed_date'])): ?><?php echo date('M d, Y', strtotime($evaluation['conformed_date'])); ?><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="signature-field"></div>
                </div>
            </div>

            <!-- print-only table for signatures -->
            <?php
            $signatureRoles = [
                'assessed' => 'Assessed by',
                'verified' => 'Verified by',
                'approved' => 'Approved by',
                'noted' => 'Noted by',
                'conformed' => 'Conformed by',
            ];
            $chunks = array_chunk(array_keys($signatureRoles), 3);
            ?>
            <table class="signature-print-table">
            <?php foreach ($chunks as $chunk): ?>
                <?php for ($row = 0; $row < 5; $row++): ?>
                    <tr>
                    <?php foreach ($chunk as $role): ?>
                        <td>
                        <?php
                            switch ($row) {
                                case 0:
                                    // bold/formal role label
                                    echo '<span class="role-label">' . $signatureRoles[$role] . '</span>';
                                    break;
                                case 1:
                                    echo 'Name/Signature';
                                    break;
                                case 2:
                                    // data with fill-in-the-blank underline and e-signature
                                    $val = htmlspecialchars($evaluation[$role . '_by'] ?? '');
                                    $esignature = $evaluation[$role . '_esignature'] ?? '';
                                    echo '<div style="position: relative; height: 60px; border-bottom: 1px solid #000;">';
                                    // Signature on top half
                                    if ($esignature) {
                                        echo '<img src="' . htmlspecialchars($esignature) . '" style="position: absolute; top: 0; left: 0; width: 100%; height: 50%; object-fit: contain; z-index: 3;">';
                                    }
                                    // Name on bottom half
                                    echo '<div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 50%; display: flex; align-items: center; justify-content: center; font-size: 14px; font-weight: 600; color: #333; text-decoration: underline; z-index: 2;">';
                                    echo $val ?: '&nbsp;';
                                    echo '</div>';
                                    echo '</div>';
                                    break;
                                case 3:
                                    echo 'Date:';
                                    break;
                                case 4:
                                    $dateVal = !empty($evaluation[$role . '_date']) ? date('M d, Y', strtotime($evaluation[$role . '_date'])) : '';
                                    if ($dateVal === '') { $dateVal = '&nbsp;'; }
                                    echo '<span class="filled-blank">' . $dateVal . '</span>';
                                    break;
                            }
                        ?>
                        </td>
                    <?php endforeach; ?>
                    <?php if (count($chunk) < 3): ?>
                        <?php for ($i = count($chunk); $i < 3; $i++): ?>
                            <td></td>
                        <?php endfor; ?>
                    <?php endif; ?>
                    </tr>
                <?php endfor; ?>
            <?php endforeach; ?>
            </table>
        </div>

        <!-- Controls -->
        <div class="controls">
            <div>
                <a href="evaluations.php"><button class="btn btn-primary">← Back</button></a>
                <a href="facility-summary.php?id=<?php echo (int)$evaluation_id; ?>"><button class="btn btn-primary">📊 View Summary</button></a>
                <?php if ($can_edit): ?>
                    <button class="btn btn-secondary" onclick="saveDraft()">Save Draft</button>
                    <button class="btn btn-primary" onclick="submitEvaluation()">Submit Evaluation</button>
                    <button class="btn btn-secondary" onclick="window.print()">Print</button>
                    <a href="evaluation-export-word.php?id=<?php echo (int)$evaluation_id; ?>" class="btn btn-secondary" target="_blank">Export to Word</a>
                <?php else: ?>
                    <button class="btn btn-secondary" onclick="window.print()">Print</button>
                    <a href="evaluation-export-word.php?id=<?php echo (int)$evaluation_id; ?>" class="btn btn-secondary" target="_blank">Export to Word</a>
                <?php endif; ?>
            </div>
            <div id="sync-status"></div>
        </div>
    </div>

    <script>
        const EVALUATION_ID = <?php echo $evaluation_id; ?>;
        const CAN_EDIT = <?php echo $can_edit ? 'true' : 'false'; ?>;
        const TOTAL_BLOCKS = <?php echo $total_blocks; ?>;
        const EVALUATION_STATUS = '<?php echo $evaluation['status']; ?>';
        const EVALUATION_APPROVED = <?php echo (isset($evaluation['approved']) ? (int)$evaluation['approved'] : 0); ?>;

        /**
         * Set score: 1 = Complied (green), 0 = Not Complied (red).
         * Single-click = 1, double-click = 0.
         * @param {boolean} fromRestore - if true, skip grand total update and persist (avoid double-count)
         */
        function setScore(cell, value, fromRestore) {
            if (!CAN_EDIT || !cell.classList.contains('score-cell')) return;
            const next = value;
            const oldScore = cell.getAttribute('data-score');
            const oldVal = (oldScore === '1' || oldScore === '0') ? parseInt(oldScore, 10) : 0;
            cell.setAttribute('data-score', next);
            cell.classList.remove('score-empty', 'score-complied', 'score-not-complied');
            if (next === 1) {
                cell.classList.add('score-complied');
                cell.innerHTML = '1<br><span class="score-label">Complied</span>';
            } else {
                cell.classList.add('score-not-complied');
                cell.innerHTML = '0<br><span class="score-label">Not Complied</span>';
            }
            // update row percentage label (100% for 1, 0% for 0)
            const row = cell.closest('tr');
            if (row) {
                const pctLbl = row.querySelector('.percentage-label');
                if (pctLbl) {
                    const pctValue = (next === 1) ? 100 : 0;
                    pctLbl.setAttribute('data-value', String(pctValue));
                    pctLbl.textContent = pctValue + '%';
                }
            }
            updateTotals();
            if (!fromRestore) {
                updateGrandTotal(oldVal, next);
                saveScoreToStorage(cell, next);
                persistScore(cell, next);
            }
        }

        function updateGrandTotal(previousCellValue, newCellValue) {
            const grandEl = document.getElementById('grand-total-score');
            if (!grandEl) return;
            const current = parseInt(grandEl.textContent, 10) || 0;
            grandEl.textContent = current - previousCellValue + newCellValue;
        }

        function getStorageKey(cell) {
            const container = document.querySelector('.spreadsheet-container');
            const blockIdx = container ? (container.getAttribute('data-block-index') || '0') : '0';
            const cells = document.querySelectorAll('.spreadsheet .score-cell');
            const idx = Array.from(cells).indexOf(cell);
            return 'eval_' + EVALUATION_ID + '_b' + blockIdx + '_c' + idx;
        }

        function saveScoreToStorage(cell, value) {
            try {
                const key = getStorageKey(cell);
                sessionStorage.setItem(key, String(value));
                // also save percentage (label) and remarks if present in same row
                const row = cell.closest('tr');
                if (row) {
                    const pctLabel = row.querySelector('.percentage-label');
                    const rm = row.querySelector('.remarks-input');
                    if (pctLabel) {
                        const v = pctLabel.getAttribute('data-value');
                        if (v !== null && v !== '') sessionStorage.setItem(key + '_pct', String(v));
                    }
                    if (rm) sessionStorage.setItem(key + '_rm', rm.value);
                }
            } catch (e) {}
        }

        function getStoredGrandTotalFromStaticCells() {
            let sum = 0;
            try {
                for (let b = 0; b < (TOTAL_BLOCKS || 6); b++) {
                    for (let c = 0; c < 200; c++) {
                        const key = 'eval_' + EVALUATION_ID + '_b' + b + '_c' + c;
                        const v = sessionStorage.getItem(key);
                        if (v === '1') sum++;
                    }
                }
            } catch (e) {}
            return sum;
        }

        function applyScoreDisplay(cell, value) {
            if (!cell || !cell.classList.contains('score-cell')) return;
            const next = value;
            cell.setAttribute('data-score', next);
            cell.classList.remove('score-empty', 'score-complied', 'score-not-complied');
            if (next === 1) {
                cell.classList.add('score-complied');
                cell.innerHTML = '1<br><span class="score-label">Complied</span>';
            } else {
                cell.classList.add('score-not-complied');
                cell.innerHTML = '0<br><span class="score-label">Not Complied</span>';
            }
            // update row percentage label for non-editable/static displays
            const row = cell.closest('tr');
            if (row) {
                const pctLbl = row.querySelector('.percentage-label');
                if (pctLbl) {
                    const pctValue = (next === 1) ? 100 : 0;
                    pctLbl.setAttribute('data-value', String(pctValue));
                    pctLbl.textContent = pctValue + '%';
                }
            }
            updateTotals();
        }

        function annotateBlocks() {
            document.querySelectorAll('.score-cell').forEach(cell => {
                if (!cell.hasAttribute('data-block')) {
                    let row = cell.closest('tr');
                    let blk = '';
                    while (row) {
                        if (row.classList.contains('bb-header-row')) {
                            blk = row.cells[0]?.textContent.trim();
                            break;
                        }
                        // new: check for a rowspanned block cell
                        const bbcell = row.querySelector('td.bb-cell');
                        if (bbcell) {
                            blk = bbcell.textContent.trim();
                            break;
                        }
                        row = row.previousElementSibling;
                    }
                    if (blk) cell.setAttribute('data-block', blk);
                }
            });
        }

        function restoreScoresFromStorage() {
            annotateBlocks();
            const container = document.querySelector('.spreadsheet-container');
            if (!container) return;
            const blockIdx = container.getAttribute('data-block-index') || '0';
            const cells = document.querySelectorAll('.spreadsheet .score-cell');
            const grandEl = document.getElementById('grand-total-score');
            const serverTotal = parseInt(grandEl?.textContent || '0', 10);
            const staticTotal = getStoredGrandTotalFromStaticCells();
            if (grandEl) grandEl.textContent = serverTotal + staticTotal;
            cells.forEach((cell, idx) => {
                const hasKpiId = cell.getAttribute('data-kpi-id');
                if (hasKpiId) return;
                try {
                    const key = 'eval_' + EVALUATION_ID + '_b' + blockIdx + '_c' + idx;
                    const saved = sessionStorage.getItem(key);
                    if (saved === '1' || saved === '0') {
                        if (CAN_EDIT) {
                            setScore(cell, parseInt(saved, 10), true);
                        } else {
                            applyScoreDisplay(cell, parseInt(saved, 10));
                        }
                    }
                    // restore percentage and remarks
                    const row = cell.closest('tr');
                    if (row) {
                        const pctLabel = row.querySelector('.percentage-label');
                        const rm = row.querySelector('.remarks-input');
                        const textarea = row.querySelector('.remarks-textarea');
                        const savedPct = sessionStorage.getItem(key + '_pct');
                        const savedRm = sessionStorage.getItem(key + '_rm');
                        if (pctLabel && savedPct !== null) {
                            pctLabel.setAttribute('data-value', savedPct);
                            pctLabel.textContent = (parseFloat(savedPct) || 0) + '%';
                        }
                        if (rm && savedRm !== null) {
                            rm.value = savedRm;
                            if (textarea) textarea.value = savedRm;
                        }
                    }
                } catch (e) {}
            });
        }

        function setupScoreCell(cell) {
            if (!CAN_EDIT) return;
            cell.ondblclick = null;
            cell.onclick = null;
            let clickTimeout;
            cell.addEventListener('click', function(e) {
                clearTimeout(clickTimeout);
                clickTimeout = setTimeout(function() { setScore(cell, 1); }, 250);
            });
            cell.addEventListener('dblclick', function(e) {
                clearTimeout(clickTimeout);
                clickTimeout = null;
                setScore(cell, 0);
                e.preventDefault();
            });
        }

        function updateTotals() {
            const scoreCells = document.querySelectorAll('.score-cell[data-score="1"], .score-cell[data-score="0"]');
            let sum = 0;
            const blockTotals = {};
            scoreCells.forEach(cell => {
                const s = cell.getAttribute('data-score');
                if (s === '1' || s === '0') {
                    const val = parseInt(s, 10);
                    sum += val;
                    // determine block name, either from attribute or by walking rows
                    let blk = cell.getAttribute('data-block');
                    if (!blk) {
                        let row = cell.closest('tr');
                        while (row) {
                            if (row.classList.contains('bb-header-row')) {
                                blk = row.cells[0]?.textContent.trim();
                                break;
                            }
                            row = row.previousElementSibling;
                        }
                    }
                    if (blk) {
                        blockTotals[blk] = (blockTotals[blk] || 0) + val;
                        // also update the row percentage label for this KPI
                        const row = cell.closest('tr');
                        if (row) {
                            const pctLabel = row.querySelector('.percentage-label');
                            if (pctLabel) {
                                const pctVal = (val === 1) ? 100 : 0;
                                pctLabel.setAttribute('data-value', String(pctVal));
                                pctLabel.textContent = pctVal + '%';
                            }
                        }
                    }
                }
            });
            const totalScoreEl = document.getElementById('total-score');
            if (totalScoreEl) totalScoreEl.textContent = sum;
            // update header block score elements
            document.querySelectorAll('.block-score').forEach(el => {
                const blk = el.getAttribute('data-block');
                const val = (blk && blockTotals.hasOwnProperty(blk)) ? blockTotals[blk] : 0;
                el.textContent = val;
                // compute block percentage using header's data-total-kpis if available
                const pctEls = document.querySelectorAll('.block-percentage[data-block="' + (blk || '') + '"]');
                let totalKPIs = 0;
                const header = document.querySelector('.bb-header-row[data-block="' + (blk || '') + '"]');
                if (header) totalKPIs = parseInt(header.getAttribute('data-total-kpis') || '0', 10) || 0;
                const blockPct = totalKPIs ? Math.round((val / totalKPIs) * 100) : 0;
                pctEls.forEach(pel => pel.textContent = blockPct + '%');
            });
        }

        async function persistScore(cell, scoreValue) {
            // gather row values (percentage label and remarks) if available
            const kpiId = cell ? cell.getAttribute('data-kpi-id') : null;
            if (!kpiId) return;
            let percentage = null;
            let remarks = '';
            const row = cell.closest('tr');
            if (row) {
                const pctLabel = row.querySelector('.percentage-label');
                const rmInput = row.querySelector('.remarks-input');
                if (pctLabel) {
                    const v = parseFloat(pctLabel.getAttribute('data-value'));
                    percentage = isNaN(v) ? ((scoreValue === 1) ? 100 : (scoreValue === 0 ? 0 : null)) : v;
                } else {
                    percentage = (scoreValue === 1) ? 100 : (scoreValue === 0 ? 0 : null);
                }
                if (rmInput) remarks = rmInput.value;
            }
            try {
                const res = await fetch('api/save_score.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        evaluation_id: EVALUATION_ID,
                        kpi_id: kpiId,
                        score_value: scoreValue,
                        percentage_value: percentage,
                        remarks: remarks
                    })
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Score saved', 'success');
                } else {
                    // Check if it's an "already completed" error
                    if (result.message && result.message.includes('already completed')) {
                        showToast('⚠️ Evaluation is locked - cannot edit', 'error');
                    } else {
                        showToast(result.message || 'Score not saved', 'error');
                    }
                }
            } catch (e) {
                showToast('Error saving score', 'error');
            }
        }

        function showToast(message, type) {
            // remove any existing toast first (one at a time)
            const existing = document.querySelector('.toast');
            if (existing) existing.remove();
            
            const toast = document.createElement('div');
            toast.className = 'toast ' + type;
            toast.textContent = message;
            document.body.appendChild(toast);
            setTimeout(() => toast.remove(), 3000);
        }

        // debounce helper for persist calls (wait 500ms after last change before saving)
        const persistDebounce = {};
        function debouncedPersistScore(cell, scoreValue) {
            const key = cell ? cell.getAttribute('data-kpi-id') : null;
            if (!key) return;
            
            // cancel previous timeout for this cell
            if (persistDebounce[key]) clearTimeout(persistDebounce[key]);
            
            // schedule new persist after 500ms of inactivity
            persistDebounce[key] = setTimeout(() => {
                persistScore(cell, scoreValue);
                delete persistDebounce[key];
            }, 500);
        }

        async function saveDraft() {
            try {
                const res = await fetch('api/update_evaluation_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ evaluation_id: EVALUATION_ID, status: 'draft' })
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Draft saved.', 'success');
                    document.getElementById('sync-status').textContent = 'Saved as draft';
                } else showToast(result.message || 'Failed', 'error');
            } catch (e) {
                showToast('Error saving draft', 'error');
            }
        }

        async function submitEvaluation() {
            if (!confirm('Submit this evaluation? You can no longer edit after submission.')) return;
            try {
                const res = await fetch('api/update_evaluation_status.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ evaluation_id: EVALUATION_ID, status: 'completed' })
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Evaluation submitted.', 'success');
                    document.querySelector('.status-badge').textContent = 'Completed';
                    document.querySelector('.status-badge').className = 'status-badge status-completed';
                    document.getElementById('sync-status').textContent = 'Submitted';
                } else showToast(result.message || 'Failed', 'error');
            } catch (e) {
                showToast('Error submitting', 'error');
            }
        }

        document.querySelectorAll('.score-cell').forEach(cell => {
            if (!CAN_EDIT) {
                cell.style.cursor = 'default';
                cell.ondblclick = null;
                cell.onclick = null;
            } else {
                setupScoreCell(cell);
            }
        });

        // listen for remarks edits and save (debounced)
        if (CAN_EDIT) {
            document.querySelectorAll('.remarks-input').forEach(input => {
                input.addEventListener('change', function() {
                    const row = input.closest('tr');
                    const scoreCell = row ? row.querySelector('.score-cell') : null;
                    if (scoreCell) updateTotals();
                    debouncedPersistScore(scoreCell, scoreCell ? parseInt(scoreCell.getAttribute('data-score') || 0, 10) : null);
                });
            });
            // sync textarea with hidden input
            document.querySelectorAll('.remarks-textarea').forEach(ta => {
                // auto-grow kept here
                ta.style.height = 'auto';
                ta.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = this.scrollHeight + 'px';
                    const hidden = this.parentElement.querySelector('.remarks-input');
                    if (hidden) {
                        hidden.value = this.value;
                        hidden.dispatchEvent(new Event('change'));
                    }
                });
            });
        }

        restoreScoresFromStorage();
        // ensure totals and block percentages reflect server-provided scores on load
        updateTotals();

        // Notify user if evaluation is already completed and locked
        if (EVALUATION_STATUS === 'completed' && !CAN_EDIT) {
            showToast('ℹ️ This evaluation is completed and locked. View-only mode.', 'info');
        }

        // sync signature print labels from inputs before printing
        function syncSignaturePrints() {
            const assessedBy = document.getElementById('assessed-by');
            const assessedDate = document.getElementById('assessed-date');
            const assessedPrint = document.getElementById('assessed-print');
            const assessedTextLayer = document.getElementById('assessed-text-layer');
            if (assessedPrint) {
                const name = assessedBy ? assessedBy.value : '';
                const date = assessedDate ? assessedDate.value : '';
                assessedPrint.textContent = name ? (name + (date ? ' — ' + new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '')) : '';
            }
            if (assessedTextLayer) {
                assessedTextLayer.textContent = assessedBy ? assessedBy.value : '';
            }

            const verifiedBy = document.getElementById('verified-by');
            const verifiedDate = document.getElementById('verified-date');
            const verifiedPrint = document.getElementById('verified-print');
            const verifiedTextLayer = document.getElementById('verified-text-layer');
            if (verifiedPrint) {
                const name = verifiedBy ? verifiedBy.value : '';
                const date = verifiedDate ? verifiedDate.value : '';
                verifiedPrint.textContent = name ? (name + (date ? ' — ' + new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '')) : '';
            }
            if (verifiedTextLayer) {
                verifiedTextLayer.textContent = verifiedBy ? verifiedBy.value : '';
            }

            const approvedBy = document.getElementById('approved-by');
            const approvedDate = document.getElementById('approved-date');
            const approvedPrint = document.getElementById('approved-print');
            const approvedTextLayer = document.getElementById('approved-text-layer');
            if (approvedPrint) {
                const name = approvedBy ? approvedBy.value : '';
                const date = approvedDate ? approvedDate.value : '';
                approvedPrint.textContent = name ? (name + (date ? ' — ' + new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '')) : '';
            }
            if (approvedTextLayer) {
                approvedTextLayer.textContent = approvedBy ? approvedBy.value : '';
            }
            
            // noted
            const notedBy = document.getElementById('noted-by');
            const notedDate = document.getElementById('noted-date');
            const notedPrint = document.getElementById('noted-print');
            const notedTextLayer = document.getElementById('noted-text-layer');
            if (notedPrint) {
                const name = notedBy ? notedBy.value : '';
                const date = notedDate ? notedDate.value : '';
                notedPrint.textContent = name ? (name + (date ? ' — ' + new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '')) : '';
            }
            if (notedTextLayer) {
                notedTextLayer.textContent = notedBy ? notedBy.value : '';
            }

            // conformed
            const conformedBy = document.getElementById('conformed-by');
            const conformedDate = document.getElementById('conformed-date');
            const conformedPrint = document.getElementById('conformed-print');
            const conformedTextLayer = document.getElementById('conformed-text-layer');
            if (conformedPrint) {
                const name = conformedBy ? conformedBy.value : '';
                const date = conformedDate ? conformedDate.value : '';
                conformedPrint.textContent = name ? (name + (date ? ' — ' + new Date(date).toLocaleDateString('en-US', { month: 'short', day: '2-digit', year: 'numeric' }) : '')) : '';
            }
            if (conformedTextLayer) {
                conformedTextLayer.textContent = conformedBy ? conformedBy.value : '';
            }
        }

        window.addEventListener('beforeprint', function() {
            syncSignaturePrints();
        });
        // also sync when user focuses print button
        document.querySelectorAll('.signature-input').forEach(inp => inp.addEventListener('change', syncSignaturePrints));

        // Initialize layered text display on page load
        syncSignaturePrints();

        // Auto-save signatures (debounced)
        let sigSaveTimer = null;
        function gatherSignaturePayload() {
            const payload = { evaluation_id: EVALUATION_ID };

            const assessedBy = document.getElementById('assessed-by');
            const assessedDate = document.getElementById('assessed-date');
            const assessedCheck = document.getElementById('assessed-check');
            if (assessedBy) payload.assessed_by = assessedBy.value || null;
            if (assessedDate) payload.assessed_date = assessedDate.value || null;
            if (assessedCheck) payload.assessed_signature = assessedCheck.checked ? 'checked' : '';

            const verifiedBy = document.getElementById('verified-by');
            const verifiedDate = document.getElementById('verified-date');
            const verifiedCheck = document.getElementById('verified-check');
            if (verifiedBy) payload.verified_by = verifiedBy.value || null;
            if (verifiedDate) payload.verified_date = verifiedDate.value || null;
            if (verifiedCheck) payload.verified_signature = verifiedCheck.checked ? 'checked' : '';

            const approvedBy = document.getElementById('approved-by');
            const approvedDate = document.getElementById('approved-date');
            const approvedCheck = document.getElementById('approved-check');
            if (approvedBy) payload.approved_by = approvedBy.value || null;
            if (approvedDate) payload.approved_date = approvedDate.value || null;
            if (approvedCheck) payload.approved_signature = approvedCheck.checked ? 'checked' : '';

            // noted and conformed if present on page
            const notedBy = document.getElementById('noted-by');
            const notedDate = document.getElementById('noted-date');
            const notedCheck = document.getElementById('noted-check');
            if (notedBy) payload.noted_by = notedBy.value || null;
            if (notedDate) payload.noted_date = notedDate.value || null;
            if (notedCheck) payload.noted_signature = notedCheck.checked ? 'checked' : '';

            const conformedBy = document.getElementById('conformed-by');
            const conformedDate = document.getElementById('conformed-date');
            const conformedCheck = document.getElementById('conformed-check');
            if (conformedBy) payload.conformed_by = conformedBy.value || null;
            if (conformedDate) payload.conformed_date = conformedDate.value || null;
            if (conformedCheck) payload.conformed_signature = conformedCheck.checked ? 'checked' : '';

            return payload;
        }

        function debouncedSaveSignatures() {
            if (sigSaveTimer) clearTimeout(sigSaveTimer);
            sigSaveTimer = setTimeout(saveSignatures, 600);
        }

        async function saveSignatures() {
            const payload = gatherSignaturePayload();
            try {
                const res = await fetch('api/save_signatures.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const result = await res.json();
                if (result.success) {
                    showToast('Signatures saved', 'success');
                    syncSignaturePrints();
                } else {
                    // Check if it's an "already completed" error
                    if (result.message && result.message.includes('already completed')) {
                        showToast('⚠️ Evaluation is locked - cannot edit', 'error');
                    } else {
                        showToast(result.message || 'Failed to save signatures', 'error');
                    }
                }
            } catch (e) {
                showToast('Error saving signatures', 'error');
            }
        }

        // attach listeners to signature inputs and checkboxes (only if editing is enabled)
        if (CAN_EDIT) {
            document.querySelectorAll('#assessed-by, #assessed-date, #assessed-check, #verified-by, #verified-date, #verified-check, #approved-by, #approved-date, #approved-check, #noted-by, #noted-date, #noted-check, #conformed-by, #conformed-date, #conformed-check').forEach(el => {
                if (!el) return;
                el.addEventListener('change', debouncedSaveSignatures);
                el.addEventListener('input', debouncedSaveSignatures);
            });
        }

        // E-signature functionality
        let currentCropper = null;
        let currentSignatureType = '';

        function chooseFile(type) {
            document.getElementById(type + '-esignature-file').click();
        }

        function cropSignature(type) {
            const fileInput = document.getElementById(type + '-esignature-file');
            if (!fileInput.files[0]) return;

            currentSignatureType = type;
            const modal = document.getElementById('crop-modal');
            const cropImage = document.getElementById('crop-image');

            const reader = new FileReader();
            reader.onload = function(e) {
                cropImage.src = e.target.result;
                modal.style.display = 'block';

                if (currentCropper) {
                    currentCropper.destroy();
                }

                currentCropper = new Cropper(cropImage, {
                    aspectRatio: 4 / 1, // horizontal rectangle
                    viewMode: 1,
                    responsive: true,
                    restore: false,
                    checkCrossOrigin: false,
                    checkOrientation: false,
                    modal: true,
                    guides: true,
                    center: true,
                    highlight: false,
                    background: false,
                    autoCropArea: 0.8,
                    movable: true,
                    rotatable: false,
                    scalable: true,
                    zoomable: true,
                    zoomOnTouch: true,
                    zoomOnWheel: true,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                });
            };
            reader.readAsDataURL(fileInput.files[0]);
        }

        function closeModal() {
            document.getElementById('crop-modal').style.display = 'none';
            if (currentCropper) {
                currentCropper.destroy();
                currentCropper = null;
            }
        }

        function saveCroppedSignature() {
            if (!currentCropper) return;

            const canvas = currentCropper.getCroppedCanvas({
                width: 400, // fixed width
                height: 100, // fixed height for horizontal rectangle
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            // Convert to black signature on transparent background
            const ctx = canvas.getContext('2d');
            const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
            const data = imageData.data;

            // Convert to grayscale and make background transparent
            for (let i = 0; i < data.length; i += 4) {
                const r = data[i];
                const g = data[i + 1];
                const b = data[i + 2];
                const gray = (r + g + b) / 3;

                if (gray > 200) { // light areas become transparent
                    data[i + 3] = 0; // alpha
                } else { // dark areas become black
                    data[i] = 0;
                    data[i + 1] = 0;
                    data[i + 2] = 0;
                    data[i + 3] = 255;
                }
            }

            ctx.putImageData(imageData, 0, 0);

            // Convert to PNG with transparency
            const croppedImageDataURL = canvas.toDataURL('image/png');

            // Update preview
            const preview = document.getElementById(currentSignatureType + '-esignature-preview');
            preview.src = croppedImageDataURL;
            preview.style.display = 'block';

            // Update layered display
            const imageLayer = document.getElementById(currentSignatureType + '-image-layer');
            imageLayer.src = croppedImageDataURL;
            imageLayer.style.display = 'block';

            // Show remove button, hide crop button
            document.getElementById(currentSignatureType + '-crop-btn').style.display = 'none';
            document.getElementById(currentSignatureType + '-remove-btn').style.display = 'inline-block';

            closeModal();

            // Auto-save
            debouncedSaveSignatures();
        }

        function removeSignature(type) {
            if (confirm('Remove this electronic signature?')) {
                const preview = document.getElementById(type + '-esignature-preview');
                preview.src = '';
                preview.style.display = 'none';

                // Clear layered display
                const imageLayer = document.getElementById(type + '-image-layer');
                imageLayer.src = '';
                imageLayer.style.display = 'none';

                // Reset file input
                const fileInput = document.getElementById(type + '-esignature-file');
                fileInput.value = '';

                // Hide crop and remove buttons
                document.getElementById(type + '-crop-btn').style.display = 'none';
                document.getElementById(type + '-remove-btn').style.display = 'none';

                // Auto-save
                debouncedSaveSignatures();
            }
        }

        // Update gatherSignaturePayload to include esignatures
        function gatherSignaturePayload() {
            const payload = {
                evaluation_id: <?php echo (int)$evaluation_id; ?>,
            };

            const assessedBy = document.getElementById('assessed-by');
            const assessedDate = document.getElementById('assessed-date');
            const assessedCheck = document.getElementById('assessed-check');
            if (assessedBy) payload.assessed_by = assessedBy.value || null;
            if (assessedDate) payload.assessed_date = assessedDate.value || null;
            if (assessedCheck) payload.assessed_signature = assessedCheck.checked ? 'checked' : '';

            const assessedPreview = document.getElementById('assessed-esignature-preview');
            if (assessedPreview && assessedPreview.style.display !== 'none' && assessedPreview.src) {
                payload.assessed_esignature = assessedPreview.src; // base64 data URL
            } else {
                payload.assessed_esignature = null;
            }

            const verifiedBy = document.getElementById('verified-by');
            const verifiedDate = document.getElementById('verified-date');
            const verifiedCheck = document.getElementById('verified-check');
            if (verifiedBy) payload.verified_by = verifiedBy.value || null;
            if (verifiedDate) payload.verified_date = verifiedDate.value || null;
            if (verifiedCheck) payload.verified_signature = verifiedCheck.checked ? 'checked' : '';

            const verifiedPreview = document.getElementById('verified-esignature-preview');
            if (verifiedPreview && verifiedPreview.style.display !== 'none' && verifiedPreview.src) {
                payload.verified_esignature = verifiedPreview.src;
            } else {
                payload.verified_esignature = null;
            }

            const approvedBy = document.getElementById('approved-by');
            const approvedDate = document.getElementById('approved-date');
            const approvedCheck = document.getElementById('approved-check');
            if (approvedBy) payload.approved_by = approvedBy.value || null;
            if (approvedDate) payload.approved_date = approvedDate.value || null;
            if (approvedCheck) payload.approved_signature = approvedCheck.checked ? 'checked' : '';

            const approvedPreview = document.getElementById('approved-esignature-preview');
            if (approvedPreview && approvedPreview.style.display !== 'none' && approvedPreview.src) {
                payload.approved_esignature = approvedPreview.src;
            } else {
                payload.approved_esignature = null;
            }

            const notedBy = document.getElementById('noted-by');
            const notedDate = document.getElementById('noted-date');
            const notedCheck = document.getElementById('noted-check');
            if (notedBy) payload.noted_by = notedBy.value || null;
            if (notedDate) payload.noted_date = notedDate.value || null;
            if (notedCheck) payload.noted_signature = notedCheck.checked ? 'checked' : '';

            const notedPreview = document.getElementById('noted-esignature-preview');
            if (notedPreview && notedPreview.style.display !== 'none' && notedPreview.src) {
                payload.noted_esignature = notedPreview.src;
            } else {
                payload.noted_esignature = null;
            }

            const conformedBy = document.getElementById('conformed-by');
            const conformedDate = document.getElementById('conformed-date');
            const conformedCheck = document.getElementById('conformed-check');
            if (conformedBy) payload.conformed_by = conformedBy.value || null;
            if (conformedDate) payload.conformed_date = conformedDate.value || null;
            if (conformedCheck) payload.conformed_signature = conformedCheck.checked ? 'checked' : '';

            const conformedPreview = document.getElementById('conformed-esignature-preview');
            if (conformedPreview && conformedPreview.style.display !== 'none' && conformedPreview.src) {
                payload.conformed_esignature = conformedPreview.src;
            } else {
                payload.conformed_esignature = null;
            }

            return payload;
        }

        // File input change handler
        document.querySelectorAll('.signature-file-input').forEach(input => {
            input.addEventListener('change', function() {
                const type = this.id.replace('-esignature-file', '');
                if (this.files[0]) {
                    document.getElementById(type + '-crop-btn').style.display = 'inline-block';
                }
            });
        });
        // Approval Modal Functions
        function openApprovalModal() {
            // Prevent opening if already approved or rejected
            if (EVALUATION_APPROVED === 1 || EVALUATION_APPROVED === 2) {
                showToast('This evaluation has already been reviewed. Cannot change approval status.', 'info');
                return;
            }
            document.getElementById('approval-modal').style.display = 'block';
        }

        function closeApprovalModal() {
            document.getElementById('approval-modal').style.display = 'none';
        }

        function approveEvaluation() {
            if (!confirm('Are you sure you want to APPROVE this evaluation?')) return;
            submitApproval(1);
        }

        function rejectEvaluation() {
            if (!confirm('Are you sure you want to REJECT this evaluation?')) return;
            submitApproval(2);
        }

        async function submitApproval(approved_status) {
            try {
                const res = await fetch('api/update_evaluation_approval.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        evaluation_id: <?php echo (int)$evaluation_id; ?>,
                        approved: approved_status
                    })
                });
                const result = await res.json();
                if (result.success) {
                    const statusText = approved_status === 1 ? 'approved' : 'rejected';
                    showToast(`Evaluation ${statusText} successfully.`, 'success');
                    closeApprovalModal();
                    // Optionally hide the button after approval
                    const btn = document.getElementById('approve-btn');
                    if (btn) {
                        btn.disabled = true;
                        btn.textContent = approved_status === 1 ? '✓ Approved' : '✗ Rejected';
                    }
                } else {
                    showToast(result.message || 'Failed to submit approval', 'error');
                }
            } catch (e) {
                showToast('Error submitting approval: ' + e.message, 'error');
            }
        }

        // Close modal when clicking outside of it
        window.addEventListener('click', function(event) {
            const modal = document.getElementById('approval-modal');
            if (event.target === modal) {
                modal.style.display = 'none';
            }
        });
    </script>

    <!-- Approval Modal -->
    <div id="approval-modal" class="approval-modal">
        <div class="approval-modal-content">
            <h2>Evaluation Approval</h2>
            <p>Is this evaluation approved or not?</p>
            <div class="approval-buttons">
                <button class="approval-btn approval-btn-approved" onclick="approveEvaluation()">
                    <span class="approval-btn-icon">✓</span> Approve
                </button>
                <button class="approval-btn approval-btn-rejected" onclick="rejectEvaluation()">
                    <span class="approval-btn-icon">✗</span> Reject
                </button>
                <button class="approval-btn approval-btn-cancel" onclick="closeApprovalModal()">
                    Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Crop Modal -->
    <div id="crop-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h4>Crop Signature</h4>
                <span class="close" onclick="closeModal()">&times;</span>
            </div>
            <div class="modal-body">
                <img id="crop-image" style="max-width:100%;">
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveCroppedSignature()">Save</button>
            </div>
        </div>
    </div>
</body>
</html>
