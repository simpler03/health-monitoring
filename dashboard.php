<?php
/**
 * Dashboard Page
 * Health Performance Monitoring System
 */

require_once __DIR__ . '/config/BaseConfig.php';
require_once __DIR__ . '/config/Auth.php';
Auth::requireLogin();

require_once __DIR__ . '/config/Database.php';
require_once __DIR__ . '/config/EvaluationManager.php';
require_once __DIR__ . '/config/FacilityManager.php';
require_once __DIR__ . '/config/QueryHelper.php';

/**
 * Format percentage: show whole numbers without decimals, 2 decimals otherwise
 * Example: 10 => "10%", 22.6688 => "22.67%"
 */
function formatPercentage($value) {
    $rounded = round($value, 2);
    if ($rounded == intval($rounded)) {
        return intval($rounded) . '%';
    }
    return number_format($rounded, 2) . '%';
}

function abbreviateBuildingBlockName($name) {
    $abbreviations = [
        'Leadership and Governance' => 'L&G',
        'Regulation' => 'REG',
        'Health financing' => 'HF',
        'Human Resource for Health' => 'HRH',
        'Health Service Delivery' => 'HSD',
        'Information Communication and Technology' => 'ICT',
    ];

    return $abbreviations[$name] ?? $name;
}

function wrapChartLabel($label, $maxLineLength = 12) {
    if (mb_strlen($label) <= $maxLineLength) {
        return [$label];
    }

    $words = preg_split('/\s+/', trim($label));
    $lines = [];
    $currentLine = '';

    foreach ($words as $word) {
        $candidate = $currentLine === '' ? $word : $currentLine . ' ' . $word;
        if (mb_strlen($candidate) <= $maxLineLength) {
            $currentLine = $candidate;
            continue;
        }

        if ($currentLine !== '') {
            $lines[] = $currentLine;
        }
        $currentLine = $word;
    }

    if ($currentLine !== '') {
        $lines[] = $currentLine;
    }

    return array_slice($lines, 0, 2);
}

$user = $_SESSION;
$current_role = $user['role'] ?? 'viewer';

try {
    $database = new Database();
    $db = $database->connect();
    $evaluation_manager = new EvaluationManager($db);
    $facility_manager = new FacilityManager($db);
    $query_helper = new QueryHelper($db);

    // Get facilities based on user access
    if ($current_role === 'admin') {
        $facilities = $facility_manager->getAllFacilitiesForAdmin();
    } else {
        // For input and viewer, show facilities they created, edited, or are assigned to
        $facilities = $facility_manager->getFacilitiesForUser($user['user_id'], $user['facility_name'] ?? null);
    }
    
    // Get evaluations based on user access
    if ($current_role === 'admin') {
        $all_evaluations = $evaluation_manager->getEvaluations([]);
    } else {
        // For input and viewer, show evaluations for accessible facilities
        $all_evaluations = $evaluation_manager->getEvaluationsForUser($user['user_id'], $user['facility_name'] ?? null);
    }
    
    $recent_evaluations = array_slice($all_evaluations, 0, 10);

    // Counts by status
    $count_draft = count(array_filter($all_evaluations, fn($e) => $e['status'] === 'draft'));
    $count_in_progress = count(array_filter($all_evaluations, fn($e) => $e['status'] === 'in_progress'));
    $count_completed = count(array_filter($all_evaluations, fn($e) => $e['status'] === 'completed'));
    $count_approved = count(array_filter($all_evaluations, fn($e) => $e['status'] === 'approved'));

    $facilities_by_id = [];
    foreach ($facilities as $f) {
        $facilities_by_id[$f['id']] = $f;
    }
    
    // Facility performance overview: group evaluations by facility
    $by_facility = [];
    $facility_building_block_stats = []; // For detailed facility stats by building block
    $facility_details = []; // For facility-level drill-down details
    
    foreach ($all_evaluations as $e) {
        $facility_name = $e['facility_name'] ?? 'Unknown';
        $facility_id = $e['facility_id'];
        
        if (!isset($by_facility[$facility_name])) {
            $by_facility[$facility_name] = ['total' => 0, 'completed' => 0, 'draft' => 0];
            $facility_building_block_stats[$facility_name] = [];
            $facility_details[$facility_name] = [];
        }
        $by_facility[$facility_name]['total']++;
        if ($e['status'] === 'completed' || $e['status'] === 'approved') $by_facility[$facility_name]['completed']++;
        if ($e['status'] === 'draft') $by_facility[$facility_name]['draft']++;
        
        // Initialize facility tracking for this facility
        if (!isset($facility_details[$facility_name][$facility_id])) {
            $facility_details[$facility_name][$facility_id] = ['name' => $facility_name, 'building_blocks' => []];
        }
        
        // Get building block stats for this evaluation
        $indicators = $evaluation_manager->getIndicatorsForEvaluation($e['id']);
        $facility_bb_tracking = []; // Track which building blocks this facility complies with
        
        // First pass: Aggregate indicators by building block to calculate building block-level compliance
        $bb_aggregates = [];
        foreach ($indicators as $indicator) {
            $bb_name = $indicator['building_block_name'];
            $percentage_value = $indicator['percentage_value'] ?? 0;
            
            if (!isset($bb_aggregates[$bb_name])) {
                $bb_aggregates[$bb_name] = [
                    'complied' => 0,
                    'total' => 0,
                    'indicator_name' => $indicator['indicator_name'] ?? 'N/A',
                    'score' => round($percentage_value, 2)
                ];
            }
            
            $bb_aggregates[$bb_name]['total']++;
            if ($percentage_value >= 50) {
                $bb_aggregates[$bb_name]['complied']++;
            }
        }
        
        // Second pass: Determine building block status based on >= 50% compliance threshold
        foreach ($bb_aggregates as $bb_name => $aggregate) {
            $facility_key = $facility_name;
            
            if (!isset($facility_building_block_stats[$facility_key][$bb_name])) {
                $facility_building_block_stats[$facility_key][$bb_name] = [
                    'complied' => 0,
                    'not_complied' => 0,
                    'total' => 0,
                    'facilities_complied' => 0,
                    'facilities_total' => 0,
                    'facility_details' => []
                ];
            }
            
            $facility_building_block_stats[$facility_key][$bb_name]['total'] += $aggregate['total'];
            $facility_building_block_stats[$facility_key][$bb_name]['complied'] += $aggregate['complied'];
            $facility_building_block_stats[$facility_key][$bb_name]['not_complied'] = $facility_building_block_stats[$facility_key][$bb_name]['total'] - $facility_building_block_stats[$facility_key][$bb_name]['complied'];
            
            // Building block is complied if >= 50% of its indicators are complied
            $bb_compliance_pct = $aggregate['total'] > 0 ? ($aggregate['complied'] / $aggregate['total']) * 100 : 0;
            if ($bb_compliance_pct >= 50) {
                $facility_bb_tracking[$bb_name] = 'complied';
                // Store facility-level details
                if (!isset($facility_details[$facility_name][$facility_id]['building_blocks'][$bb_name])) {
                    $facility_details[$facility_name][$facility_id]['building_blocks'][$bb_name] = [
                        'status' => 'complied',
                        'indicator_name' => $aggregate['indicator_name'],
                        'score' => round($bb_compliance_pct, 2) . '%'
                    ];
                }
            } else {
                $facility_bb_tracking[$bb_name] = 'not_complied';
                // Store facility-level details
                if (!isset($facility_details[$facility_name][$facility_id]['building_blocks'][$bb_name])) {
                    $facility_details[$facility_name][$facility_id]['building_blocks'][$bb_name] = [
                        'status' => 'not_complied',
                        'indicator_name' => $aggregate['indicator_name'],
                        'score' => round($bb_compliance_pct, 2) . '%'
                    ];
                }
            }
        }
        
        // Third pass: Count facilities per building block (one per facility, not per indicator)
        foreach ($facility_bb_tracking as $bb_name => $status) {
            $facility_building_block_stats[$facility_name][$bb_name]['facilities_total']++;
            if ($status === 'complied') {
                $facility_building_block_stats[$facility_name][$bb_name]['facilities_complied']++;
            }
        }
    }
    $total_facilities = count($facilities);
    $completion_pct = $total_facilities > 0
        ? round((count(array_unique(array_column($all_evaluations, 'facility_id'))) / $total_facilities) * 100)
        : 0;

    // Extract filter options
    $available_years = [];
    $available_facilities = [];
    foreach ($all_evaluations as $eval) {
        $year = date('Y', strtotime($eval['evaluation_date']));
        $available_years[$year] = $year;
        
        $facility_name = $eval['facility_name'] ?? 'Unknown';
        $available_facilities[$facility_name] = $facility_name;
    }
    ksort($available_years);
    sort($available_facilities);

} catch (Exception $e) {
    error_log("Dashboard Error: " . $e->getMessage());
    $all_evaluations = [];
    $recent_evaluations = [];
    $facilities = [];
    $count_draft = $count_in_progress = $count_completed = $count_approved = 0;
    $by_facility = [];
    $completion_pct = 0;
    $total_facilities = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Facility-wide Local Health Monitoring Tool</title>
    <link rel="stylesheet" href="<?php echo asset('css/styles.css'); ?>">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
        }

        .logo {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 10px;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 12px 15px;
            border-radius: 4px;
            transition: background 0.3s;
            font-size: 14px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: rgba(255, 255, 255, 0.2);
        }

        .user-info {
            position: absolute;
            bottom: 20px;
            left: 20px;
            right: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.2);
            padding-top: 20px;
            font-size: 13px;
        }

        .user-info p {
            margin: 5px 0;
        }

        .logout-btn {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: none;
            padding: 8px 12px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            margin-top: 10px;
            width: 100%;
            transition: background 0.3s;
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Main Content */
        .main-content {
            margin-left: 260px;
            flex: 1;
            padding: 40px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 40px;
        }

        .header h1 {
            font-size: 32px;
            color: #333;
        }

        .header-actions {
            display: flex;
            gap: 10px;
        }

        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
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

        /* Apply Filters Button */
        #apply-filters {
            transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
            position: relative;
            overflow: hidden;
        }

        #apply-filters:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.5);
        }

        #apply-filters:active {
            transform: translateY(-1px);
        }

        #apply-filters span {
            transition: all 0.3s ease;
            display: inline-block;
        }

        #apply-filters:hover span {
            animation: spinIcon 0.6s ease-in-out;
        }

        @keyframes spinIcon {
            0% { transform: rotate(0deg) scale(1); }
            50% { transform: rotate(20deg) scale(1.1); }
            100% { transform: rotate(0deg) scale(1); }
        }

        /* Cards */
        .cards-container {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            margin-bottom: 40px;
            width: 100%;
        }

        .card {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transition: box-shadow 0.3s;
        }

        .card:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
        }

        .card-title {
            color: #666;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .card-value {
            font-size: 32px;
            font-weight: bold;
            color: #667eea;
        }

        /* Table */
        .section {
            background: white;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            margin-bottom: 39px;
        }

        .section-title {
            font-size: 18px;
            font-weight: bold;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #667eea;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }

        table th {
            background: #f5f5f5;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #ddd;
        }

        table td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        table tr:hover {
            background: #f9f9f9;
        }

        .badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .badge-completed {
            background: #e8f5e9;
            color: #2e7d32;
        }

        .badge-draft {
            background: #fff3e0;
            color: #e65100;
        }

        .badge-approved {
            background: #e3f2fd;
            color: #1565c0;
        }
        .badge-in_progress {
            background: #fff8e1;
            color: #f57c00;
        }

        .action-links {
            display: flex;
            gap: 10px;
        }

        .action-links a {
            color: #667eea;
            text-decoration: none;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            padding: 5px 10px;
            border-radius: 3px;
            transition: background 0.3s;
        }

        .action-links a:hover {
            background: #f0f0f0;
        }

        .empty-state {
            text-align: center;
            padding: 40px;
            color: #999;
        }

        .empty-state-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }

        .empty-state p {
            margin: 10px 0;
        }

        @media (max-width: 768px) {
            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
            }

            .main-content {
                margin-left: 0;
            }

            .header {
                flex-direction: column;
                align-items: flex-start;
            }

            .header-actions {
                width: 100%;
                flex-direction: column;
            }

            .header-actions button {
                width: 100%;
            }
        }
        .aline{
            display: flex;
            flex-direction: row;
            gap: 40px;
            justify-content: center;
        }

        /* ====== ANIMATIONS ====== */

        /* Card animations */
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .card {
            animation: slideUp 0.6s ease-out forwards;
        }

        .card:nth-child(1) { animation-delay: 0.1s; }
        .card:nth-child(2) { animation-delay: 0.2s; }
        .card:nth-child(3) { animation-delay: 0.3s; }
        .card:nth-child(4) { animation-delay: 0.4s; }
        .card:nth-child(5) { animation-delay: 0.5s; }
        .card:nth-child(6) { animation-delay: 0.6s; }

        /* Section animations */
        .section {
            animation: slideUp 0.7s ease-out;
        }

        /* Building block bar styles */
        /* remove animation entirely and rely solely on transition */
        .complied-bar, .non-complied-bar {
            transition: height 0.3s ease-out;
        }

        .complied-bar {
            animation-delay: 0.3s;
        }

        .non-complied-bar {
            animation-delay: 0.5s;
        }

        /* Province section fade in */
        .facility-section {
            animation: slideUp 0.6s ease-out;
        }

        /* Facility badge pulse animation */
        @keyframes fadeBounce {
            0%, 100% {
                opacity: 1;
                transform: scale(1);
            }
            50% {
                opacity: 0.8;
                transform: scale(1.05);
            }
        }

        .building-block span[style*="background: #e3f2fd"] {
            animation: fadeBounce 2s ease-in-out infinite;
        }

        /* Facility details smooth expand/collapse */
        .facility-details {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 20px;
        }

        .facility-details[style*="display: block"] {
            max-height: 2000px;
            opacity: 1;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .facility-details table tbody tr {
            animation: slideUp 0.4s ease-out;
        }

        .facility-details table tbody tr:nth-child(1) { animation-delay: 0.05s; }
        .facility-details table tbody tr:nth-child(2) { animation-delay: 0.1s; }
        .facility-details table tbody tr:nth-child(3) { animation-delay: 0.15s; }
        .facility-details table tbody tr:nth-child(4) { animation-delay: 0.2s; }
        .facility-details table tbody tr:nth-child(5) { animation-delay: 0.25s; }

        /* Toggle button animation */
        .toggle-facility-details {
            transition: all 0.3s ease;
        }

        .toggle-facility-details:hover {
            background: #e8e8e8;
            transform: translateX(5px);
        }

        .toggle-facility-details:active {
            transform: translateX(3px);
        }

        /* Building Block Details Styles */
        .bb-details {
            overflow: hidden;
            max-height: 0;
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 15px;
        }

        .bb-details[style*="display: block"] {
            max-height: 2000px;
            opacity: 1;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .bb-details table tbody tr {
            animation: slideUp 0.4s ease-out;
        }

        .bb-details table tbody tr:nth-child(1) { animation-delay: 0.05s; }
        .bb-details table tbody tr:nth-child(2) { animation-delay: 0.1s; }
        .bb-details table tbody tr:nth-child(3) { animation-delay: 0.15s; }
        .bb-details table tbody tr:nth-child(4) { animation-delay: 0.2s; }
        .bb-details table tbody tr:nth-child(5) { animation-delay: 0.25s; }

        /* Toggle button animation for building blocks */
        .toggle-bb-details {
            transition: all 0.3s ease;
        }

        .toggle-bb-details:hover {
            background: #e8e8e8;
            transform: translateX(5px);
        }

        .toggle-bb-details:active {
            transform: translateX(3px);
        }

        /* Building block interactive animations */
        .building-block {
            perspective: 1000px;
        }

        .building-block:hover .bb-bar {
            animation: barPulse 0.6s ease-in-out;
        }

        @keyframes barPulse {
            0%, 100% {
                transform: scaleY(1);
            }
            50% {
                transform: scaleY(1.05);
            }
        }

        /* Compliance badge smooth color transition */
        @keyframes colorPulse {
            0%, 100% {
                background: #e3f2fd;
            }
            50% {
                background: #bbdefb;
            }
        }

        .building-block:hover span[style*="background: #e3f2fd"] {
            animation: colorPulse 0.6s ease-in-out;
        }

        /* Chart container smooth transitions */
        .chart-container {
            transition: all 0.3s ease;
        }

        .chart-container.chart-container--centered {
            position: relative;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 20px;
            width: 100%;
            overflow-x: auto;
        }

        .chart-svg-shell {
            width: 100%;
            display: flex;
            justify-content: center;
        }

        .chart-svg-shell svg {
            display: block;
            width: min(100%, 1120px);
            height: auto;
            margin: 0 auto;
        }

        .chart-legend {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 30px;
            width: 100%;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .chart-legend {
                gap: 14px;
            }
        }

        /* Label fade-in animation */
        .block-label {
            animation: slideUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .building-block:nth-child(1) .block-label { animation-delay: 0.1s; }
        .building-block:nth-child(2) .block-label { animation-delay: 0.15s; }
        .building-block:nth-child(3) .block-label { animation-delay: 0.2s; }
        .building-block:nth-child(4) .block-label { animation-delay: 0.25s; }
        .building-block:nth-child(5) .block-label { animation-delay: 0.3s; }
        .building-block:nth-child(6) .block-label { animation-delay: 0.35s; }
        .building-block:nth-child(7) .block-label { animation-delay: 0.4s; }

        /* Page load stagger animation */
        .cards-container {
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Smooth table row hover animation */
        table tbody tr {
            transition: all 0.2s ease;
        }

        table tbody tr:hover {
            background: #f9f9f9;
            transform: scale(1.01);
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }

        /* Badge status animation */
        .badge {
            transition: all 0.3s ease;
        }

        .badge:hover {
            transform: scale(1.1);
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }

        /* Action links smooth animation */
        .action-links a {
            transition: all 0.2s ease;
            position: relative;
        }

        .action-links a:before {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: #667eea;
            transition: width 0.3s ease;
        }

        .action-links a:hover:before {
            width: 100%;
        }

        /* Facility row filtering animations */
        .facility-row {
            transition: opacity 0.3s ease, background-color 0.3s ease;
        }

        .facility-row:hover {
            background-color: #f5f5f5 !important;
        }

        /* Highlight visible filtered rows */
        .facility-row[style*="opacity: 1"] {
            animation: rowFadeIn 0.4s ease;
        }

        @keyframes rowFadeIn {
            from {
                opacity: 0;
                transform: translateY(-5px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Filter indicator message */
        .filter-indicator {
            display: inline-block;
            margin-left: 10px;
            padding: 4px 10px;
            background: #e3f2fd;
            color: #1565c0;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include __DIR__ . '/includes/sidebar.php'; ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="header">
                <h1>Welcome, <?php echo htmlspecialchars(explode(' ', $user['full_name'])[0]); ?>!</h1>
                <div class="header-actions">
                    <?php if ($current_role !== 'viewer'): ?>
                        <a href="new-evaluation.php" class="btn btn-primary">+ New Evaluation</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Statistics Cards - Section-level summaries & completion -->
            <div class="cards-container">
                <div class="card">
                    <div class="card-title">Total Evaluations</div>
                    <div class="card-value"><?php echo count($all_evaluations ?? []); ?></div>
                </div>
                <div class="card">
                    <div class="card-title">Completed</div>
                    <div class="card-value" style="color: #2e7d32;"><?php echo $count_completed + $count_approved; ?></div>
                </div>
                <div class="card">
                    <div class="card-title">Draft</div>
                    <div class="card-value" style="color: #e65100;"><?php echo $count_draft; ?></div>
                </div>
                <div class="card">
                    <div class="card-title">In Progress</div>
                    <div class="card-value" style="color: #f57c00;"><?php echo $count_in_progress; ?></div>
                </div>
                <div class="card">
                    <div class="card-title">Total Facilities</div>
                    <div class="card-value"><?php echo $total_facilities; ?></div>
                </div>
                <div class="card">
                    <div class="card-title">Completion (facilities with evaluation)</div>
                    <div class="card-value"><?php echo $completion_pct; ?>%</div>
                </div>
            </div>

            <div class="aline">

             <!-- Facility Summary Dashboard -->
            <?php if (!empty($facility_building_block_stats)): ?>
            <div class="section" style="width: 100%;">
                <div class="section-title">Facility Building Block Performance Summary</div>
                
                <!-- Filter Controls -->
                <div style="display: flex; gap: 20px; margin-bottom: 30px; flex-wrap: wrap; align-items: center;">
    
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <label for="year-filter" style="font-size: 14px; font-weight: 600; color: #333;">Filter by Year:</label>
                        <select id="year-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; min-width: 120px;">
                            <option value="all">All Years</option>
                            <?php foreach ($available_years as $year): ?>
                                <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; flex-direction: column; gap: 5px;">
                        <label for="facility-filter" style="font-size: 14px; font-weight: 600; color: #333;">Filter by Facility:</label>
                        <select id="facility-filter" style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; min-width: 200px;">
                            <option value="all">All Facilities</option>
                            <?php foreach ($facilities as $fac): ?>
                                <option value="<?php echo htmlspecialchars($fac['name']); ?>"><?php echo htmlspecialchars($fac['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 10px; align-items: flex-end;">
                        <button id="apply-filters" class="btn btn-primary" style="padding: 8px 20px; font-size: 14px; display: flex; align-items: center; gap: 6px;">
                            <span>🔍</span> Apply Filters
                        </button>
                        <button id="reset-filters" class="btn btn-secondary" style="padding: 8px 16px; font-size: 14px;">Reset Filters</button>
                    </div>
                </div>
                
                <?php 
                    // Create comprehensive data for dynamic filtering
                    $evaluation_data_for_js = [];
                    
                    // ensure every user-accessible facility has an entry (even if no evaluations yet)
                    foreach ($facilities as $fac) {
                        $name = $fac['name'];
                        if (!isset($evaluation_data_for_js[$name])) {
                            $evaluation_data_for_js[$name] = [];
                        }
                    }
                    foreach ($all_evaluations as $eval) {
                        $facility_name = $eval['facility_name'] ?? 'Unknown';
                        
                        if (!isset($evaluation_data_for_js[$facility_name])) {
                            $evaluation_data_for_js[$facility_name] = [];
                        }
                        
                        $indicators = $evaluation_manager->getIndicatorsForEvaluation($eval['id']);
                        
                        $evaluation_data_for_js[$facility_name][] = [
                            'id' => $eval['id'],
                            'facility_name' => $facility_name,
                            'facility_id' => $eval['facility_id'],
                            'evaluation_date' => $eval['evaluation_date'],
                            'year' => date('Y', strtotime($eval['evaluation_date'])),
                            'indicators' => $indicators
                        ];
                    }
                ?>
                    <script type="application/json" id="evaluation-data"><?php echo json_encode($evaluation_data_for_js); ?></script>
                
                <?php 
                    // prepare combined accumulated stats for "all" option
                    if (!empty($facility_building_block_stats)) {
                        $combined_stats = [];
                        // Accumulate complied and total counts across all facilities
                        foreach ($facility_building_block_stats as $fac_name => $stats_arr) {
                            foreach ($stats_arr as $bb => $data) {
                                if (!isset($combined_stats[$bb])) {
                                    $combined_stats[$bb] = ['total' => 0, 'complied' => 0, 'facilities_total' => 0, 'facilities_complied' => 0, 'facility_breakdown' => []];
                                }
                                // Accumulate totals and complied
                                $combined_stats[$bb]['total'] += $data['total'];
                                $combined_stats[$bb]['complied'] += $data['complied'];
                                
                                // Track per-facility complied count and color
                                $facility_color = '#2e7d32'; // default
                                foreach ($facilities as $fac) {
                                    if ($fac['name'] === $fac_name) {
                                        $facility_color = $fac['color_legend'] ?? '#2e7d32';
                                        break;
                                    }
                                }
                                $combined_stats[$bb]['facility_breakdown'][] = [
                                    'name' => $fac_name,
                                    'complied' => $data['complied'],
                                    'color' => $facility_color
                                ];
                                
                                // Count facilities
                                $combined_stats[$bb]['facilities_total']++;
                                if ($data['total'] > 0 && (($data['complied'] / $data['total']) * 100) >= 50) {
                                    $combined_stats[$bb]['facilities_complied']++;
                                }
                            }
                        }
                        // Calculate accumulated percentages
                        foreach ($combined_stats as $bb => $d) {
                            $accumulated_pct = $d['total'] > 0 ? round(($d['complied'] / $d['total']) * 100) : 0;
                            $facility_breakdown = $d['facility_breakdown'];
                            $combined_stats[$bb] = [
                                'total' => $d['total'],
                                'complied' => $d['complied'],
                                'not_complied' => $d['total'] - $d['complied'],
                                'facilities_total' => $d['facilities_total'],
                                'facilities_complied' => $d['facilities_complied'],
                                'facility_breakdown' => $facility_breakdown
                            ];
                        }
                        // Calculate overall percentages for legend
                        $overall_total = 0;
                        $overall_complied = 0;
                        foreach ($combined_stats as $stats) {
                            $overall_total += $stats['total'];
                            $overall_complied += $stats['complied'];
                        }
                        $overall_pct = $overall_total > 0 ? round(($overall_complied / $overall_total) * 100) : 0;
                        $overall_not_complied_pct = 100 - $overall_pct;
                        
                        // render combined section first
                        $all_years = array_keys($available_years);
                        $all_years_list = implode(',', $all_years);
                        $all_facilities_list = implode(',', array_keys($available_facilities));
                ?>
                    <div class="facility-section" data-facility="all" data-facilities="<?php echo htmlspecialchars($all_facilities_list); ?>" data-years="<?php echo htmlspecialchars($all_years_list); ?>" style="margin-bottom: 40px; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
                            <h3 style="margin: 0; color: #333; font-size: 16px; font-weight: 600;">All Facilities</h3>
                        </div>

                        <!-- Line Chart for All Facilities -->
                        <div class="chart-container chart-container--centered" id="all-facilities-chart">
                            <?php
                            // Prepare data for line chart
                            $chart_height = 480;
                            $chart_width = 1040;
                            $padding_left = 60;
                            $padding_right = 30;
                            $padding_top = 30;
                            $padding_bottom = 70;
                            $graph_width = $chart_width - $padding_left - $padding_right;
                            $graph_height = $chart_height - $padding_top - $padding_bottom;
                            
                            // Get all building blocks from combined_stats
                            $all_bb_names = array_keys($combined_stats);
                            
                            // Get facility colors
                            $facility_colors_map = [];
                            foreach ($facilities as $fac) {
                                $facility_colors_map[$fac['name']] = $fac['color_legend'] ?? '#2e7d32';
                            }
                            ?>
                            
                            <div class="chart-svg-shell">
                            <svg width="<?php echo $chart_width; ?>" height="<?php echo $chart_height; ?>" viewBox="0 0 <?php echo $chart_width; ?> <?php echo $chart_height; ?>" preserveAspectRatio="xMidYMid meet" style="background: #fafafa; border-radius: 8px;">
                                <!-- Grid Lines -->
                                <?php for ($i = 0; $i <= 5; $i++): 
                                    $y = $padding_top + ($graph_height / 5) * $i;
                                    $percentage = 100 - ($i * 20);
                                ?>
                                <line x1="<?php echo $padding_left; ?>" y1="<?php echo $y; ?>" x2="<?php echo $chart_width - $padding_right; ?>" y2="<?php echo $y; ?>" stroke="#e0e0e0" stroke-width="1"/>
                                <text x="<?php echo $padding_left - 10; ?>" y="<?php echo $y + 4; ?>" text-anchor="end" font-size="11" fill="#666"><?php echo $percentage; ?>%</text>
                                <?php endfor; ?>
                                
                                <!-- Y-axis -->
                                <line x1="<?php echo $padding_left; ?>" y1="<?php echo $padding_top; ?>" x2="<?php echo $padding_left; ?>" y2="<?php echo $padding_top + $graph_height; ?>" stroke="#333" stroke-width="2"/>
                                
                                <!-- X-axis -->
                                <line x1="<?php echo $padding_left; ?>" y1="<?php echo $padding_top + $graph_height; ?>" x2="<?php echo $chart_width - $padding_right; ?>" y2="<?php echo $padding_top + $graph_height; ?>" stroke="#333" stroke-width="2"/>
                                
                                <!-- Lines for each facility -->
                                <?php foreach ($facility_building_block_stats as $facility_name => $bb_stats): 
                                    $facility_color = $facility_colors_map[$facility_name] ?? '#2e7d32';
                                    $points = [];
                                    
                                    foreach ($all_bb_names as $index => $bb_name): 
                                        $stats = $bb_stats[$bb_name] ?? ['total' => 0, 'complied' => 0];
                                        $compliance_pct = $stats['total'] > 0 ? ($stats['complied'] / $stats['total']) * 100 : 0;
                                        
                                        $x = $padding_left + ($graph_width / (count($all_bb_names) - 1 ?: 1)) * $index;
                                        $y = $padding_top + $graph_height - ($compliance_pct / 100) * $graph_height;
                                        
                                        $points[] = "$x,$y";
                                    endforeach;
                                    
                                    if (count($points) > 1):
                                ?>
                                <!-- Line -->
                                <polyline points="<?php echo implode(' ', $points); ?>" fill="none" stroke="<?php echo $facility_color; ?>" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                
                                <!-- Data points -->
                                <?php 
                                $bb_index = 0;
                                foreach ($all_bb_names as $bb_name): 
                                    $stats = $bb_stats[$bb_name] ?? ['total' => 0, 'complied' => 0];
                                    $compliance_pct = $stats['total'] > 0 ? ($stats['complied'] / $stats['total']) * 100 : 0;
                                    
                                    $x = $padding_left + ($graph_width / (count($all_bb_names) - 1 ?: 1)) * $bb_index;
                                    $y = $padding_top + $graph_height - ($compliance_pct / 100) * $graph_height;
                                ?>
                                <circle cx="<?php echo $x; ?>" cy="<?php echo $y; ?>" r="5" fill="<?php echo $facility_color; ?>" stroke="white" stroke-width="2" class="data-point" data-facility="<?php echo htmlspecialchars($facility_name); ?>" data-block="<?php echo htmlspecialchars($bb_name); ?>" data-percentage="<?php echo number_format($compliance_pct, 2); ?>" style="cursor: pointer;"/>
                                <?php 
                                    $bb_index++;
                                endforeach; 
                                endif;
                                endforeach; ?>
                                
                                <!-- X-axis labels (Building Blocks) -->
                                <?php foreach ($all_bb_names as $index => $bb_name): 
                                    $x = $padding_left + ($graph_width / (count($all_bb_names) - 1 ?: 1)) * $index;
                                    $bb_label = abbreviateBuildingBlockName($bb_name);
                                    $bb_label_lines = wrapChartLabel($bb_label);
                                ?>
                                <text x="<?php echo $x; ?>" y="<?php echo $padding_top + $graph_height + 24; ?>" text-anchor="middle" font-size="12" fill="#333" font-weight="600">
                                    <title><?php echo htmlspecialchars($bb_name); ?></title>
                                    <?php foreach ($bb_label_lines as $lineIndex => $line): ?>
                                    <tspan x="<?php echo $x; ?>" dy="<?php echo $lineIndex === 0 ? 0 : 14; ?>"><?php echo htmlspecialchars($line); ?></tspan>
                                    <?php endforeach; ?>
                                </text>
                                <?php endforeach; ?>
                            </svg>
                            </div>
                            
                            <!-- Legend -->
                            <div class="chart-legend">
                                <?php foreach ($facility_building_block_stats as $facility_name => $bb_stats): 
                                    $facility_color = $facility_colors_map[$facility_name] ?? '#2e7d32';
                                ?>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <div style="width: 30px; height: 3px; background: <?php echo $facility_color; ?>; border-radius: 2px;"></div>
                                    <span style="font-size: 12px; color: #333; font-weight: 500;"><?php echo htmlspecialchars($facility_name); ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Tooltip -->
                            <div id="chart-tooltip-all" style="position: absolute; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 4px; font-size: 12px; pointer-events: none; display: none; z-index: 1000; white-space: nowrap;">
                                <div id="tooltip-content-all"></div>
                            </div>
                        </div>

                    </div>
                <?php }
                foreach ($facility_building_block_stats as $facility => $bb_stats): 
                    // Get facilities for this facility and their evaluation years
                    $facility_facilities = [];
                    $facility_years = [];
                    $facility_id = null;
                    $facility_color = '#2e7d32'; // default color
                    foreach ($all_evaluations as $eval) {
                        $eval_facility = $eval['facility_name'] ?? 'Unknown';
                        if ($eval_facility === $facility) {
                            $facility_name = $eval['facility_name'] ?? 'Unknown';
                            $year = date('Y', strtotime($eval['evaluation_date']));
                            $facility_facilities[$facility_name] = true;
                            $facility_years[$year] = true;
                            if (!$facility_id) {
                                $facility_id = $eval['facility_id'];
                            }
                        }
                    }
                    // Get facility color from database
                    if ($facility_id && isset($facilities_by_id[$facility_id])) {
                        $facility_color = $facilities_by_id[$facility_id]['color_legend'] ?? '#2e7d32';
                    }
                    $facilities_list = implode(',', array_keys($facility_facilities));
                    $years_list = implode(',', array_keys($facility_years));
                ?>
                    <div class="facility-section" data-facility="<?php echo htmlspecialchars($facility); ?>" data-facilities="<?php echo htmlspecialchars($facilities_list); ?>" data-years="<?php echo htmlspecialchars($years_list); ?>" style="margin-bottom: 40px; padding: 30px; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.08);">
                        <h3 style="margin-bottom: 25px; color: #333; font-size: 16px; font-weight: 600;">Facility: <?php echo htmlspecialchars($facility); ?></h3>
                        
                        <!-- Line Chart -->
                        <div class="chart-container" style="overflow-x: auto; display: flex; justify-content: center;">
                            <div style="position: relative; padding: 20px 0;">
                                <?php 
                                // Collect all building blocks and their compliance percentages
                                $bb_names = array_keys($bb_stats);
                                $chart_height = 400;
                                $chart_width = 900;
                                $padding_left = 60;
                                $padding_right = 30;
                                $padding_top = 30;
                                $padding_bottom = 80;
                                $graph_width = $chart_width - $padding_left - $padding_right;
                                $graph_height = $chart_height - $padding_top - $padding_bottom;
                                
                                // Get all facilities for this grouping with their colors
                                $facility_colors_map = [];
                                foreach ($facilities as $fac) {
                                    $facility_colors_map[$fac['name']] = $fac['color_legend'] ?? '#2e7d32';
                                }
                                
                                // For single facility view
                                $facilities_in_chart = [];
                                if ($facility === 'all') {
                                    // Show all facilities
                                    foreach ($facility_building_block_stats as $fac_name => $bb_data) {
                                        $facilities_in_chart[] = [
                                            'name' => $fac_name,
                                            'color' => $facility_colors_map[$fac_name] ?? '#2e7d32',
                                            'stats' => $bb_data
                                        ];
                                    }
                                } else {
                                    // Show only this facility
                                    $facilities_in_chart[] = [
                                        'name' => $facility,
                                        'color' => $facility_colors_map[$facility] ?? '#2e7d32',
                                        'stats' => $bb_stats
                                    ];
                                }
                                ?>
                                
                                <svg width="<?php echo $chart_width; ?>" height="<?php echo $chart_height; ?>" style="background: #fafafa; border-radius: 8px;">
                                    <!-- Grid Lines -->
                                    <?php for ($i = 0; $i <= 5; $i++): 
                                        $y = $padding_top + ($graph_height / 5) * $i;
                                        $percentage = 100 - ($i * 20);
                                    ?>
                                    <line x1="<?php echo $padding_left; ?>" y1="<?php echo $y; ?>" x2="<?php echo $chart_width - $padding_right; ?>" y2="<?php echo $y; ?>" stroke="#e0e0e0" stroke-width="1"/>
                                    <text x="<?php echo $padding_left - 10; ?>" y="<?php echo $y + 4; ?>" text-anchor="end" font-size="11" fill="#666"><?php echo $percentage; ?>%</text>
                                    <?php endfor; ?>
                                    
                                    <!-- Y-axis -->
                                    <line x1="<?php echo $padding_left; ?>" y1="<?php echo $padding_top; ?>" x2="<?php echo $padding_left; ?>" y2="<?php echo $padding_top + $graph_height; ?>" stroke="#333" stroke-width="2"/>
                                    
                                    <!-- X-axis -->
                                    <line x1="<?php echo $padding_left; ?>" y1="<?php echo $padding_top + $graph_height; ?>" x2="<?php echo $chart_width - $padding_right; ?>" y2="<?php echo $padding_top + $graph_height; ?>" stroke="#333" stroke-width="2"/>
                                    
                                    <!-- Lines for each facility -->
                                    <?php foreach ($facilities_in_chart as $fac_data): 
                                        $points = [];
                                        $bb_names_for_facility = array_keys($fac_data['stats']);
                                        
                                        foreach ($bb_names_for_facility as $index => $bb_name): 
                                            $stats = $fac_data['stats'][$bb_name];
                                            $compliance_pct = $stats['total'] > 0 ? ($stats['complied'] / $stats['total']) * 100 : 0;
                                            
                                            $x = $padding_left + ($graph_width / (count($bb_names_for_facility) - 1 ?: 1)) * $index;
                                            $y = $padding_top + $graph_height - ($compliance_pct / 100) * $graph_height;
                                            
                                            $points[] = "$x,$y";
                                        endforeach;
                                        
                                        if (count($points) > 1):
                                    ?>
                                    <!-- Line -->
                                    <polyline points="<?php echo implode(' ', $points); ?>" fill="none" stroke="<?php echo $fac_data['color']; ?>" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                    
                                    <!-- Data points -->
                                    <?php 
                                    $bb_index = 0;
                                    foreach ($bb_names_for_facility as $bb_name): 
                                        $stats = $fac_data['stats'][$bb_name];
                                        $compliance_pct = $stats['total'] > 0 ? ($stats['complied'] / $stats['total']) * 100 : 0;
                                        
                                        $x = $padding_left + ($graph_width / (count($bb_names_for_facility) - 1 ?: 1)) * $bb_index;
                                        $y = $padding_top + $graph_height - ($compliance_pct / 100) * $graph_height;
                                    ?>
                                    <circle cx="<?php echo $x; ?>" cy="<?php echo $y; ?>" r="5" fill="<?php echo $fac_data['color']; ?>" stroke="white" stroke-width="2" class="data-point" data-facility="<?php echo htmlspecialchars($fac_data['name']); ?>" data-block="<?php echo htmlspecialchars($bb_name); ?>" data-percentage="<?php echo number_format($compliance_pct, 2); ?>" style="cursor: pointer;"/>
                                    <?php 
                                        $bb_index++;
                                    endforeach; 
                                    endif;
                                    endforeach; ?>
                                    
                                    <!-- X-axis labels (Building Blocks) -->
                                    <?php 
                                    $bb_names_display = $facility === 'all' ? array_keys($combined_stats) : $bb_names;
                                    foreach ($bb_names_display as $index => $bb_name): 
                                        $x = $padding_left + ($graph_width / (count($bb_names_display) - 1 ?: 1)) * $index;
                                    ?>
                                    <text x="<?php echo $x; ?>" y="<?php echo $padding_top + $graph_height + 20; ?>" text-anchor="middle" font-size="10" fill="#333" font-weight="500" transform="rotate(45, <?php echo $x; ?>, <?php echo $padding_top + $graph_height + 20; ?>)"><?php echo htmlspecialchars($bb_name); ?></text>
                                    <?php endforeach; ?>
                                </svg>
                                
                                <!-- Legend -->
                                <div style="display: flex; gap: 30px; margin-top: 20px; justify-content: center; flex-wrap: wrap;">
                                    <?php foreach ($facilities_in_chart as $fac_data): ?>
                                    <div style="display: flex; align-items: center; gap: 8px;">
                                        <div style="width: 30px; height: 3px; background: <?php echo $fac_data['color']; ?>; border-radius: 2px;"></div>
                                        <span style="font-size: 12px; color: #333; font-weight: 500;"><?php echo htmlspecialchars($fac_data['name']); ?></span>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <!-- Tooltip -->
                                <div id="chart-tooltip" style="position: absolute; background: rgba(0,0,0,0.8); color: white; padding: 8px 12px; border-radius: 4px; font-size: 12px; pointer-events: none; display: none; z-index: 1000; white-space: nowrap;">
                                    <div id="tooltip-content"></div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Facility Details Section (Collapsible) -->
                        <div style="margin-top: 30px; border-top: 1px solid #eee; padding-top: 20px;">
                            <button class="toggle-facility-details" data-facility="<?php echo htmlspecialchars($facility); ?>" style="background: #f5f5f5; border: 1px solid #ddd; padding: 10px 15px; border-radius: 4px; cursor: pointer; font-weight: 600; color: #333; font-size: 14px; width: 100%; text-align: left;">
                                ▶ View Facility Details
                            </button>
                            <div class="facility-details" data-facility="<?php echo htmlspecialchars($facility); ?>" style="display: none; margin-top: 20px;">
                                <div style="overflow-x: auto;">
                                    <table style="width: 100%; border-collapse: collapse; font-size: 13px;">
                                        <thead>
                                            <tr style="background: #f5f5f5;">
                                                <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600;">Facility Name</th>
                                                <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600;">Building Block</th>
                                                <th style="padding: 10px; text-align: center; border-bottom: 2px solid #ddd; font-weight: 600;">Compliance</th>
                                                <th style="padding: 10px; text-align: left; border-bottom: 2px solid #ddd; font-weight: 600;">Indicator</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php 
                                            foreach ($facility_details[$facility] as $fac_id => $facility_data):
                                                $row_count = 0;
                                                foreach ($facility_data['building_blocks'] as $bb => $bb_data):
                                                    $row_count++;
                                                    $is_first_row = ($row_count === 1);
                                                    $status_badge_color = $bb_data['status'] === 'complied' ? '#e8f5e9' : '#ffebee';
                                                    $status_text_color = $bb_data['status'] === 'complied' ? '#2e7d32' : '#c62828';
                                                    $status_text = $bb_data['status'] === 'complied' ? '✓ Complied' : '✗ Not Complied';
                                            ?>
                                            <tr style="border-bottom: 1px solid #eee;" data-facility="<?php echo htmlspecialchars($facility_data['name']); ?>" class="facility-row">
                                                <?php if ($is_first_row): ?>
                                                    <td style="padding: 10px; font-weight: 600;" rowspan="<?php echo count($facility_data['building_blocks']); ?>"><?php echo htmlspecialchars($facility_data['name']); ?></td>
                                                <?php endif; ?>
                                                <td style="padding: 10px;"><?php echo htmlspecialchars($bb); ?></td>
                                                <td style="padding: 10px; text-align: center;">
                                                    <span style="background: <?php echo $status_badge_color; ?>; color: <?php echo $status_text_color; ?>; padding: 4px 8px; border-radius: 3px; font-weight: 600; font-size: 12px;">
                                                        <?php echo $status_text; ?>
                                                    </span>
                                                </td>
                                                <td style="padding: 10px; font-size: 12px; color: #666;"><?php echo htmlspecialchars($bb_data['indicator_name']); ?></td>
                                            </tr>
                                            <?php 
                                                endforeach;
                                            endforeach; 
                                            ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
            <!-- Facility performance overview -->
            <?php if (!empty($by_facility)): ?>
            <div class="section">
                <div class="section-title">Facility Performance Overview</div>
                <table>
                    <thead>
                        <tr>
                            <th>Facility</th>
                            <th>Total Evaluations</th>
                            <th>Completed</th>
                            <th>Draft</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($by_facility as $facility => $stats): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($facility); ?></td>
                            <td><?php echo $stats['total']; ?></td>
                            <td><?php echo $stats['completed']; ?></td>
                            <td><?php echo $stats['draft']; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

           

            <!-- Recent Evaluations -->
            <div class="section">
                <div class="section-title">Recent Evaluations</div>
                
                <?php if (count($recent_evaluations) > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Facility</th>
                                <th>Date</th>
                                <th>Period</th>
                                <th>Status</th>
                                <th>Created By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_evaluations as $eval): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($eval['facility_name']); ?></td>
                                    <td><?php echo date('M d, Y', strtotime($eval['evaluation_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($eval['evaluation_period']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $eval['status']; ?>">
                                            <?php echo ucfirst($eval['status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($eval['created_by_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <div class="action-links">
                                            <a href="evaluation-view.php?id=<?php echo $eval['id']; ?>">View</a>
                                            <?php if ($current_role !== 'viewer'): ?>
                                                <a href="evaluation-edit.php?id=<?php echo $eval['id']; ?>">Edit</a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <p>No evaluations yet</p>
                        <?php if ($current_role !== 'viewer'): ?>
                            <a href="new-evaluation.php" class="btn btn-primary" style="margin-top: 15px;">Create First Evaluation</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        // Load evaluation data for dynamic filtering
        const evaluationDataElement = document.getElementById('evaluation-data');
        const evaluationDataByFacility = evaluationDataElement ? JSON.parse(evaluationDataElement.textContent) : {};

        function recalculateGraphs() {
            const yearFilter = document.getElementById('year-filter');
            const facilityFilter = document.getElementById('facility-filter');
            
            const selectedYear = yearFilter ? yearFilter.value : 'all';
            const selectedFacility = facilityFilter ? facilityFilter.value : 'all';

            // special case: accumulate across all facilities
            if (selectedFacility === 'all') {
                const accumulatedStats = {};
                
                // Accumulate all building blocks across all facilities
                Object.keys(evaluationDataByFacility).forEach(fac => {
                    let evals = evaluationDataByFacility[fac];
                    if (selectedYear !== 'all') {
                        evals = evals.filter(e => e.year === selectedYear);
                    }
                    
                    // Process each evaluation's indicators
                    evals.forEach(ev => {
                        ev.indicators.forEach(indicator => {
                            const bbName = indicator.building_block_name;
                            if (!accumulatedStats[bbName]) {
                                accumulatedStats[bbName] = { compliedEvals: 0, totalEvals: 0 };
                            }
                            accumulatedStats[bbName].totalEvals++;
                            if ((indicator.percentage_value || 0) >= 50) {
                                accumulatedStats[bbName].compliedEvals++;
                            }
                        });
                    });
                });
                
                updateFacilityGraph('all', accumulatedStats);
                return;
            }

            // default: single facility
            Object.keys(evaluationDataByFacility).forEach(facility => {
                let filteredEvals = evaluationDataByFacility[facility];
                if (selectedYear !== 'all') {
                    filteredEvals = filteredEvals.filter(eval => eval.year === selectedYear);
                }
                if (selectedFacility !== 'all') {
                    filteredEvals = filteredEvals.filter(eval => eval.facility_name === selectedFacility);
                }
                
                const bbStats = {};
                
                // Get distinct indicators for each building block
                const bbDistinctIndicators = {};
                filteredEvals.forEach(ev => {
                    ev.indicators.forEach(indicator => {
                        const bbName = indicator.building_block_name;
                        if (!bbDistinctIndicators[bbName]) bbDistinctIndicators[bbName] = new Set();
                        bbDistinctIndicators[bbName].add(indicator.indicator_name);
                    });
                });
                
                // For each building block, calculate compliance for each distinct indicator
                Object.keys(bbDistinctIndicators).forEach(bbName => {
                    const distinctIndicators = Array.from(bbDistinctIndicators[bbName]);
                    let totalIndicatorCompliance = 0;
                    
                    distinctIndicators.forEach(indicatorName => {
                        let totalInstances = 0;
                        let compliedInstances = 0;
                        
                        filteredEvals.forEach(ev => {
                            ev.indicators.forEach(indicator => {
                                if (indicator.building_block_name === bbName && 
                                    indicator.indicator_name === indicatorName) {
                                    totalInstances++;
                                    if ((indicator.percentage_value || 0) >= 50) {
                                        compliedInstances++;
                                    }
                                }
                            });
                        });
                        
                        const compliancePct = totalInstances > 0 ? (compliedInstances / totalInstances) * 100 : 0;
                        totalIndicatorCompliance += compliancePct;
                    });
                    
                    const facilityCompliancePct = distinctIndicators.length > 0 ? 
                        totalIndicatorCompliance / distinctIndicators.length : 0;
                    
                    // For display: calculate total indicator count and complied count
                    let totalIndicatorCount = 0;
                    let actualCompliedCount = 0;
                    filteredEvals.forEach(ev => {
                        ev.indicators.forEach(indicator => {
                            if (indicator.building_block_name === bbName) {
                                totalIndicatorCount++;
                                if ((indicator.percentage_value || 0) >= 50) {
                                    actualCompliedCount++;
                                }
                            }
                        });
                    });
                        
                    bbStats[bbName] = { 
                        totalEvals: totalIndicatorCount, 
                        compliedEvals: actualCompliedCount
                    };
                });
                
                updateFacilityGraph(facility, bbStats);
            });
        }

        // Format percentage: show whole numbers without decimals, 2 decimals otherwise
        function formatPercentageJS(value) {
            const rounded = Math.round(value * 100) / 100;
            if (rounded === Math.floor(rounded)) {
                return Math.floor(rounded) + '%';
            }
            return rounded.toFixed(2) + '%';
        }

        function updateFacilityGraph(facility, bbStats) {
            const facilitySection = document.querySelector(`.facility-section[data-facility="${facility}"]`);
            if (!facilitySection) return;

            // Update data points in the line chart
            const dataPoints = facilitySection.querySelectorAll('.data-point');
            dataPoints.forEach(point => {
                const bbName = point.dataset.block;
                const pointFacility = point.dataset.facility;
                
                // Only update points for this facility or if it's the "all" view
                if (facility === 'all' || pointFacility === facility) {
                    const stats = bbStats[bbName];
                    
                    if (!stats || stats.totalEvals === 0) {
                        point.style.opacity = '0.3';
                        return;
                    }
                    
                    const compliancePct = stats.totalEvals > 0 ? (stats.compliedEvals / stats.totalEvals) * 100 : 0;
                    point.dataset.percentage = compliancePct.toFixed(2);
                    point.style.opacity = '1';
                }
            });
            
            // Note: For dynamic filtering with line charts, a full re-render would be needed
            // This is a simplified update that modifies existing points
            // For production use, consider re-rendering the entire SVG chart
        }

        document.addEventListener('DOMContentLoaded', function() {
            const yearFilter = document.getElementById('year-filter');
            const facilityFilter = document.getElementById('facility-filter');
            const resetButton = document.getElementById('reset-filters');
            const facilitySections = document.querySelectorAll('.facility-section');
            let initialRun = true; // skip first recalculation


            function filterFacilities() {
                const facilityFilter = document.getElementById('facility-filter');
                const yearFilter = document.getElementById('year-filter');
                
                const selectedFacility = facilityFilter ? facilityFilter.value : 'all';
                const selectedYear = yearFilter ? yearFilter.value : 'all';

                facilitySections.forEach(section => {
                    const sectionFacility = section.dataset.facility;
                    const years = section.dataset.years ? section.dataset.years.split(',') : [];
                    
                    let showSection = true;

                    // If "all facilities" is selected, only show the "all" section, hide individual facilities
                    if (selectedFacility === 'all' && sectionFacility !== 'all') {
                        showSection = false;
                    }

                    // Filter by facility (when not showing all)
                    if (selectedFacility !== 'all' && sectionFacility !== selectedFacility) {
                        showSection = false;
                    }

                    // Filter by year
                    if (selectedYear !== 'all' && !years.includes(selectedYear)) {
                        showSection = false;
                    }

                    // Smooth fade animation when showing/hiding
                    if (showSection) {
                        section.style.opacity = '0';
                        section.style.display = 'block';
                        setTimeout(() => {
                            section.style.transition = 'opacity 0.4s ease';
                            section.style.opacity = '1';
                        }, 10);

                        // Filter facility detail rows based on selected facility
                        const facilityRows = section.querySelectorAll('.facility-row');
                        facilityRows.forEach(row => {
                            const rowFacility = row.getAttribute('data-facility');
                            if (selectedFacility === 'all') {
                                // Show all facilities
                                row.style.display = '';
                                row.style.opacity = '1';
                            } else if (rowFacility === selectedFacility) {
                                // Show only selected facility
                                row.style.display = '';
                                row.style.opacity = '1';
                                row.style.transition = 'opacity 0.3s ease';
                            } else {
                                // Hide other facilities
                                row.style.opacity = '0';
                                row.style.transition = 'opacity 0.3s ease';
                                setTimeout(() => {
                                    row.style.display = 'none';
                                }, 300);
                            }
                        });
                    } else {
                        section.style.transition = 'opacity 0.3s ease';
                        section.style.opacity = '0';
                        setTimeout(() => {
                            section.style.display = 'none';
                        }, 300);
                    }
                });
                
                // Recalculate and update graphs (skip on first invocation)
                if (!initialRun) {
                    recalculateGraphs();
                }
                initialRun = false;
            }

            function resetFilters() {
                yearFilter.value = 'all';
                facilityFilter.value = 'all';
                // Reset all facility rows visibility
                document.querySelectorAll('.facility-row').forEach(row => {
                    row.style.display = '';
                    row.style.opacity = '1';
                });
                filterFacilities();
            }

            const applyButton = document.getElementById('apply-filters');
            if (applyButton) {
                applyButton.addEventListener('click', function() {
                    // Add button press animation
                    this.style.transform = 'scale(0.95)';
                    setTimeout(() => {
                        this.style.transform = 'scale(1)';
                        filterFacilities();
                    }, 100);
                });
            }

            yearFilter.addEventListener('change', filterFacilities);
            facilityFilter.addEventListener('change', filterFacilities);
            resetButton.addEventListener('click', resetFilters);

            // Initial filter application
            filterFacilities();

            // ====== LINE CHART TOOLTIP FUNCTIONALITY ======
            const tooltip = document.getElementById('chart-tooltip');
            const tooltipContent = document.getElementById('tooltip-content');
            
            if (tooltip && tooltipContent) {
                document.querySelectorAll('.data-point').forEach(point => {
                    point.addEventListener('mouseenter', function(e) {
                        const facility = this.dataset.facility;
                        const block = this.dataset.block;
                        const percentage = this.dataset.percentage;
                        
                        tooltipContent.innerHTML = `
                            <div style="font-weight: 600; margin-bottom: 4px;">${facility}</div>
                            <div style="font-size: 11px; margin-bottom: 2px;">Building Block: ${block}</div>
                            <div style="font-size: 11px;">Compliance: <strong>${percentage}%</strong></div>
                        `;
                        
                        tooltip.style.display = 'block';
                        
                        // Position tooltip
                        const rect = this.getBoundingClientRect();
                        const containerRect = this.closest('.chart-container').getBoundingClientRect();
                        
                        tooltip.style.left = (rect.left - containerRect.left + rect.width / 2) + 'px';
                        tooltip.style.top = (rect.top - containerRect.top - 60) + 'px';
                        tooltip.style.transform = 'translateX(-50%)';
                    });
                    
                    point.addEventListener('mouseleave', function() {
                        tooltip.style.display = 'none';
                    });
                });
            }
            
            // Tooltip for All Facilities chart
            const tooltipAll = document.getElementById('chart-tooltip-all');
            const tooltipContentAll = document.getElementById('tooltip-content-all');
            
            if (tooltipAll && tooltipContentAll) {
                document.querySelectorAll('#all-facilities-chart .data-point').forEach(point => {
                    point.addEventListener('mouseenter', function(e) {
                        const facility = this.dataset.facility;
                        const block = this.dataset.block;
                        const percentage = this.dataset.percentage;
                        
                        tooltipContentAll.innerHTML = `
                            <div style="font-weight: 600; margin-bottom: 4px;">${facility}</div>
                            <div style="font-size: 11px; margin-bottom: 2px;">Building Block: ${block}</div>
                            <div style="font-size: 11px;">Compliance: <strong>${percentage}%</strong></div>
                        `;
                        
                        tooltipAll.style.display = 'block';
                        
                        // Position tooltip
                        const rect = this.getBoundingClientRect();
                        const containerRect = this.closest('.chart-container').getBoundingClientRect();
                        
                        tooltipAll.style.left = (rect.left - containerRect.left + rect.width / 2) + 'px';
                        tooltipAll.style.top = (rect.top - containerRect.top - 60) + 'px';
                        tooltipAll.style.transform = 'translateX(-50%)';
                    });
                    
                    point.addEventListener('mouseleave', function() {
                        tooltipAll.style.display = 'none';
                    });
                });
            }

            // ====== FACILITY DETAILS TOGGLE FUNCTIONALITY ======
            document.querySelectorAll('.toggle-facility-details').forEach(button => {
                button.addEventListener('click', function() {
                    const facility = this.dataset.facility;
                    const detailsSection = document.querySelector('.facility-details[data-facility="' + facility + '"]');
                    const isOpen = detailsSection.style.display !== 'none';
                    
                    if (isOpen) {
                        // Close with smooth animation
                        detailsSection.style.maxHeight = '0';
                        detailsSection.style.opacity = '0';
                        detailsSection.style.marginTop = '0';
                        setTimeout(() => {
                            detailsSection.style.display = 'none';
                        }, 500);
                        this.textContent = '▶ View Facility Details';
                    } else {
                        // Open with smooth animation
                        detailsSection.style.display = 'block';
                        setTimeout(() => {
                            detailsSection.style.maxHeight = '2000px';
                            detailsSection.style.opacity = '1';
                            detailsSection.style.marginTop = '20px';
                        }, 10);
                        this.textContent = '▼ Hide Facility Details';
                    }
                });
            });

            // ====== BUILDING BLOCK DETAILS TOGGLE FUNCTIONALITY ======
            document.querySelectorAll('.toggle-bb-details').forEach(button => {
                button.addEventListener('click', function() {
                    const bb = this.dataset.bb;
                    const detailsSection = document.querySelector('.bb-details[data-bb="' + bb + '"]');
                    const isOpen = detailsSection.style.display !== 'none';
                    
                    if (isOpen) {
                        // Close with smooth animation
                        detailsSection.style.maxHeight = '0';
                        detailsSection.style.opacity = '0';
                        detailsSection.style.marginTop = '0';
                        setTimeout(() => {
                            detailsSection.style.display = 'none';
                        }, 500);
                        this.textContent = '▶ Show Details';
                    } else {
                        // Open with smooth animation
                        detailsSection.style.display = 'block';
                        setTimeout(() => {
                            detailsSection.style.maxHeight = '2000px';
                            detailsSection.style.opacity = '1';
                            detailsSection.style.marginTop = '15px';
                        }, 10);
                        this.textContent = '▼ Hide Details';
                    }
                });
            });

            // ====== FACILITIES LIST TOGGLE FUNCTIONALITY ======
            document.querySelectorAll('.toggle-facilities-list').forEach(button => {
                button.addEventListener('click', function() {
                    const bb = this.dataset.bb;
                    const facilitiesList = document.querySelector('.facilities-list-container[data-bb="' + bb + '"]');
                    const isOpen = facilitiesList.style.display !== 'none';
                    
                    if (isOpen) {
                        // Close with smooth animation
                        facilitiesList.style.maxHeight = '0';
                        facilitiesList.style.opacity = '0';
                        facilitiesList.style.marginTop = '0';
                        setTimeout(() => {
                            facilitiesList.style.display = 'none';
                        }, 500);
                        this.textContent = '▶ Show Facilities';
                    } else {
                        // Open with smooth animation
                        facilitiesList.style.display = 'flex';
                        setTimeout(() => {
                            facilitiesList.style.maxHeight = '2000px';
                            facilitiesList.style.opacity = '1';
                            facilitiesList.style.marginTop = '15px';
                        }, 10);
                        this.textContent = '▼ Hide Facilities';
                    }
                });
            });

            // ====== BUILDING BLOCK HOVER EFFECTS WITH ANIMATION ======
            document.querySelectorAll('.building-block').forEach((block, index) => {
                block.addEventListener('mouseenter', function() {
                    this.style.transform = 'scale(1.08) translateY(-5px)';
                    this.style.transition = 'transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1)';
                    const bar = this.querySelector('.bb-bar');
                    if (bar) {
                        bar.style.boxShadow = '0 8px 24px rgba(102, 126, 234, 0.6)';
                        bar.style.transition = 'all 0.3s ease';
                    }
                });
                block.addEventListener('mouseleave', function() {
                    this.style.transform = 'scale(1) translateY(0)';
                    const bar = this.querySelector('.bb-bar');
                    if (bar) {
                        bar.style.boxShadow = '0 1px 3px rgba(0,0,0,0.1)';
                    }
                });
                // Add click event to show modal with facility details or facilities chart
                const facilityAttr = block.getAttribute('data-facility');
                block.addEventListener('click', function() {
                    const facility = this.getAttribute('data-facility');
                    const bbName = this.getAttribute('data-block');
                    
                    // Create or reuse overlay
                    let overlay = document.getElementById('modal-overlay');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.id = 'modal-overlay';
                        overlay.style.position = 'fixed';
                        overlay.style.top = '0';
                        overlay.style.left = '0';
                        overlay.style.width = '100%';
                        overlay.style.height = '100%';
                        overlay.style.background = 'rgba(0, 0, 0, 0.7)';
                        overlay.style.zIndex = '9998';
                        overlay.style.display = 'none';
                        overlay.onclick = function() {
                            document.getElementById('facility-modal').style.display = 'none';
                            this.style.display = 'none';
                        };
                        document.body.appendChild(overlay);
                    }
                    overlay.style.display = 'block';
                    
                    // Create or reuse modal
                    let modal = document.getElementById('facility-modal');
                    if (!modal) {
                        modal = document.createElement('div');
                        modal.id = 'facility-modal';
                        modal.style.position = 'fixed';
                        modal.style.top = '50%';
                        modal.style.left = '50%';
                        modal.style.transform = 'translate(-50%, -50%)';
                        modal.style.background = '#fff';
                        modal.style.boxShadow = '0 8px 32px rgba(0,0,0,0.3)';
                        modal.style.borderRadius = '8px';
                        modal.style.padding = '28px';
                        modal.style.zIndex = '9999';
                        modal.style.minWidth = '400px';
                        modal.style.maxWidth = '90vw';
                        modal.style.maxHeight = '80vh';
                        modal.style.overflowY = 'auto';
                        document.body.appendChild(modal);
                    }
                    
                    let content = '<button style="position:absolute;top:12px;right:14px;font-size:24px;background:none;border:none;cursor:pointer;color:#666;font-weight:bold;" onclick="document.getElementById(\'facility-modal\').style.display=\'none\'; document.getElementById(\'modal-overlay\').style.display=\'none\';">×</button>';
                    
                    if (facility === 'all') {
                        // Show facilities chart for "all" view
                        content += '<h2 style="margin:0 0 20px 0; font-size:20px; color:#333; border-bottom:2px solid #667eea; padding-bottom:12px;">Facilities Compliance - ' + bbName + '</h2>';
                        
                        // Calculate complied counts for each facility
                        const facilitiesData = {};
                        Object.keys(evaluationDataByFacility).forEach(facilityName => {
                            let compliedCount = 0;
                            let totalCount = 0;
                            
                            const evals = evaluationDataByFacility[facilityName];
                            evals.forEach(ev => {
                                ev.indicators.forEach(indicator => {
                                    if (indicator.building_block_name === bbName) {
                                        totalCount++;
                                        if ((indicator.percentage_value || 0) >= 50) {
                                            compliedCount++;
                                        }
                                    }
                                });
                            });
                            
                            if (totalCount > 0) {
                                facilitiesData[facilityName] = {
                                    compliedCount: compliedCount,
                                    totalCount: totalCount,
                                    compliancePct: (compliedCount / totalCount) * 100
                                };
                            }
                        });
                        
                        // Sort facilities by compliance percentage (descending)
                        const sortedFacilities = Object.entries(facilitiesData)
                            .sort((a, b) => b[1].compliancePct - a[1].compliancePct);
                        
                        content += '<div style="margin-bottom:20px;">';
                        content += '<div style="font-size:12px; color:#666; font-weight:600; text-transform:uppercase; margin-bottom:16px;">Sorted by Compliance Percentage (Highest to Lowest)</div>';
                        
                        if (sortedFacilities.length > 0) {
                            sortedFacilities.forEach(([facilityName, data], index) => {
                                const barWidth = data.compliancePct;
                                
                                content += '<div style="margin-bottom:14px;">';
                                content += '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">';
                                content += '<div style="font-size:13px; color:#333; font-weight:500;">' + (index + 1) + '. ' + facilityName + '</div>';
                                content += '<div style="font-size:12px; color:#666; font-weight:600;">' + data.compliedCount + ' / ' + data.totalCount + ' (' + Math.round(data.compliancePct) + '%)</div>';
                                content += '</div>';
                                content += '<div style="width:100%; height:24px; background:#e0e0e0; border-radius:4px; overflow:hidden;">';
                                content += '<div style="height:100%; width:' + barWidth + '%; background:#81C784; transition:width 0.3s ease;"></div>';
                                content += '</div>';
                                content += '</div>';
                            });
                        } else {
                            content += '<div style="padding:12px; background:#f5f5f5; border-radius:4px; color:#666; font-size:13px;">';
                            content += 'No data available for this building block.';
                            content += '</div>';
                        }
                        
                        content += '</div>';
                    } else {
                        // Show individual facility details
                        // Get facility compliance percentage from data
                        const facilityEvals = evaluationDataByFacility[facility] || [];
                        const bbIndicators = [];
                        let totalEvaluations = facilityEvals.length;
                        
                        // Get distinct indicators for this building block
                        const distinctIndicators = new Set();
                        facilityEvals.forEach(eval => {
                            eval.indicators.forEach(indicator => {
                                if (indicator.building_block_name === bbName) {
                                    distinctIndicators.add(indicator.indicator_name);
                                }
                            });
                        });
                        
                        // For each distinct indicator, count all instances across evaluations
                        const indicatorCompliance = {};
                        Array.from(distinctIndicators).forEach(indicatorName => {
                            let totalInstances = 0;
                            let compliedInstances = 0;
                            
                            facilityEvals.forEach(eval => {
                                eval.indicators.forEach(indicator => {
                                    if (indicator.building_block_name === bbName && 
                                        indicator.indicator_name === indicatorName) {
                                        totalInstances++;
                                        if ((indicator.percentage_value || 0) >= 50) {
                                            compliedInstances++;
                                        }
                                    }
                                });
                            });
                            
                            const compliancePct = totalInstances > 0 ? (compliedInstances / totalInstances) * 100 : 0;
                            indicatorCompliance[indicatorName] = {
                                compliedInstances: compliedInstances,
                                totalInstances: totalInstances,
                                compliancePct: compliancePct,
                                complied: compliancePct >= 50
                            };
                            
                            // Collect indicators for display
                            bbIndicators.push({
                                name: indicatorName,
                                percentage: compliancePct,
                                complied: compliancePct >= 50,
                                details: `${compliedInstances}/${totalInstances} instances complied`
                            });
                        });
                        
                        // Calculate facility compliance for this block (average of indicator compliances)
                        const totalIndicatorCompliance = Object.values(indicatorCompliance)
                            .reduce((sum, ind) => sum + ind.compliancePct, 0);
                        const blockCompliancePct = distinctIndicators.size > 0 ? 
                            Math.round(totalIndicatorCompliance / distinctIndicators.size) : 0;
                        
                        content += '<h2 style="margin:0 0 20px 0; font-size:20px; color:#333; border-bottom:2px solid #667eea; padding-bottom:12px;">Building Block Details</h2>';
                        
                        // Building Block Name
                        content += '<div style="margin-bottom:16px;">';
                        content += '<div style="font-size:12px; color:#666; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Building Block</div>';
                        content += '<div style="font-size:16px; color:#333; font-weight:600;">' + bbName + '</div>';
                        content += '</div>';
                        
                        // Facility Name
                        content += '<div style="margin-bottom:16px;">';
                        content += '<div style="font-size:12px; color:#666; font-weight:600; text-transform:uppercase; margin-bottom:4px;">Facility</div>';
                        content += '<div style="font-size:15px; color:#333; font-weight:500;">' + facility + '</div>';
                        content += '</div>';
                        
                        // Facility Compliance
                        content += '<div style="margin-bottom:20px; padding:12px; background:#e3f2fd; border-left:4px solid #1565c0; border-radius:4px;">';
                        content += '<div style="font-size:12px; color:#1565c0; font-weight:600; margin-bottom:4px;">FACILITY COMPLIANCE</div>';
                        content += '<div style="font-size:24px; color:#1565c0; font-weight:700;">' + blockCompliancePct + '%</div>';
                        content += '<div style="font-size:11px; color:#666; margin-top:4px;">Average compliance across ' + distinctIndicators.size + ' indicators</div>';
                        content += '</div>';
                        
                        // How it's calculated
                        content += '<div style="margin-bottom:20px; padding:12px; background:#f5f5f5; border-radius:4px; border-left:4px solid #666;">';
                        content += '<div style="font-size:12px; color:#666; font-weight:600; margin-bottom:8px;">CALCULATION METHOD</div>';
                        content += '<div style="font-size:13px; color:#333; line-height:1.4;">';
                        content += 'Each indicator\'s compliance is calculated by counting complied instances ÷ total instances.<br>';
                        content += 'Facility compliance = Average of all indicator compliance percentages.';
                        content += '</div>';
                        content += '</div>';
                        
                        // Compliance Ratio
                        const compliedIndicators = Object.values(indicatorCompliance).filter(ind => ind.complied).length;
                        const totalIndicators = distinctIndicators.size;
                        
                        content += '<div style="margin-bottom:20px;">';
                        content += '<div style="font-size:12px; color:#666; font-weight:600; text-transform:uppercase; margin-bottom:10px;">Indicator Status Summary</div>';
                        content += '<div style="display:flex; gap:20px; margin-bottom:12px;">';
                        content += '<div style="flex:1; text-align:center; padding:12px; background:#e8f5e9; border-radius:4px;">';
                        content += '<div style="font-size:12px; color:#2e7d32; margin-bottom:4px;">Well-Performing</div>';
                        content += '<div style="font-size:20px; font-weight:700; color:#2e7d32;">' + compliedIndicators + '</div>';
                        content += '<div style="font-size:11px; color:#2e7d32; margin-top:4px;">indicators ≥50%</div>';
                        content += '</div>';
                        content += '<div style="flex:1; text-align:center; padding:12px; background:#ffebee; border-radius:4px;">';
                        content += '<div style="font-size:12px; color:#c62828; margin-bottom:4px;">Needs Improvement</div>';
                        content += '<div style="font-size:20px; font-weight:700; color:#c62828;">' + (totalIndicators - compliedIndicators) + '</div>';
                        content += '<div style="font-size:11px; color:#c62828; margin-top:4px;">indicators <50%</div>';
                        content += '</div>';
                        content += '</div>';
                        content += '</div>';
                        
                        // Key Performance Indicators
                        if (bbIndicators.length > 0) {
                            content += '<div style="margin-top:20px;">';
                            content += '<div style="font-size:12px; color:#666; font-weight:600; text-transform:uppercase; margin-bottom:12px;">Key Performance Indicators</div>';
                            content += '<div style="max-height:250px; overflow-y:auto; border:1px solid #e0e0e0; border-radius:4px;">';
                            
                            bbIndicators.forEach(kpi => {
                                const statusColor = kpi.complied ? '#2e7d32' : '#c62828';
                                const statusBg = kpi.complied ? '#e8f5e9' : '#ffebee';
                                const statusText = kpi.complied ? '✓' : '✗';
                                
                                content += '<div style="padding:10px; border-bottom:1px solid #f0f0f0; display:flex; justify-content:space-between; align-items:center;">';
                                content += '<div style="flex:1;">';
                                content += '<div style="font-size:13px; color:#333; line-height:1.4;">' + kpi.name + '</div>';
                                content += '<div style="font-size:11px; color:#666; margin-top:2px;">' + kpi.details + '</div>';
                                content += '</div>';
                                content += '<div style="display:flex; gap:8px; align-items:center;">';
                                content += '<span style="font-size:12px; color:#666; font-weight:600;">' + Math.round(kpi.percentage) + '%</span>';
                                content += '<span style="background:' + statusBg + '; color:' + statusColor + '; padding:2px 8px; border-radius:3px; font-weight:700; font-size:12px;">' + statusText + '</span>';
                                content += '</div>';
                                content += '</div>';
                            });
                            
                            content += '</div>';
                            content += '</div>';
                        } else {
                            content += '<div style="padding:12px; background:#f5f5f5; border-radius:4px; color:#666; font-size:13px;">';
                            content += 'No indicator data available for this building block.';
                            content += '</div>';
                        }
                    }
                    
                    modal.innerHTML = content;
                    modal.style.display = 'block';
                });
            });

            // ====== BUILDING BLOCK SORTING ======
            const bbSortSelect = document.getElementById('bb-sort');
            const bbContainer = document.querySelector('.building-blocks-container');
            
            if (bbSortSelect && bbContainer) {
                bbSortSelect.addEventListener('change', function() {
                    const sortValue = this.value;
                    const items = Array.from(bbContainer.querySelectorAll('.building-block-item'));
                    
                    items.sort((a, b) => {
                        const aName = a.dataset.bbName;
                        const bName = b.dataset.bbName;
                        const aPercentage = parseFloat(a.dataset.percentage);
                        const bPercentage = parseFloat(b.dataset.percentage);
                        const aEvalCount = parseInt(a.dataset.evaluationCount);
                        const bEvalCount = parseInt(b.dataset.evaluationCount);
                        const aStage = parseInt(a.dataset.stage);
                        const bStage = parseInt(b.dataset.stage);
                        
                        switch (sortValue) {
                            case 'percentage-desc':
                                return bPercentage - aPercentage; // Highest to lowest
                            case 'percentage-asc':
                                return aPercentage - bPercentage; // Lowest to highest
                            case 'name-asc':
                                return aName.localeCompare(bName);
                            case 'name-desc':
                                return bName.localeCompare(aName);
                            case 'eval-desc':
                                return bEvalCount - aEvalCount; // Highest to lowest
                            case 'eval-asc':
                                return aEvalCount - bEvalCount; // Lowest to highest
                            case 'stage-desc':
                                return bStage - aStage; // Highest stage first
                            case 'stage-asc':
                                return aStage - bStage; // Lowest stage first
                            default:
                                return bPercentage - aPercentage;
                        }
                    });
                    
                    // Reorder the DOM elements
                    items.forEach(item => {
                        bbContainer.appendChild(item);
                    });
                });
            }

            // ====== STAGGER ANIMATION FOR BUILDING BLOCKS ======
            // This creates a wave effect when the page loads
            document.querySelectorAll('.facility-section').forEach((section) => {
                const blocks = section.querySelectorAll('.complied-bar, .non-complied-bar');
                blocks.forEach((bar, index) => {
                    const delay = (index * 0.08) + 'ms';
                    bar.style.setProperty('--bar-height', bar.style.height);
                });
            });

            // ====== SMOOTH SCROLL ANIMATION FOR CARDS ======
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1 });

            document.querySelectorAll('.card, .section').forEach(element => {
                element.style.opacity = '0';
                element.style.transform = 'translateY(20px)';
                element.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
                observer.observe(element);
            });
        });
    </script>
</body>
</html>
